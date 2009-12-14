<?php
// ///////////////////////////////////ip to country/////////////////////////////////
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Ip To Country - Nosey Cunt !");

function i2c_realip ()
{
    $ip = false;
    if (!empty ($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!empty ($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift ($ips, $ip);
            $ip = false;
        }

        $i = 0;
        while ($i < count ($ips)) {
            if (!preg_match ('/^(?:10|172\\.(?:1[6-9]|2\\d|3[01])|192\\.168)\\./', $ips[$i])) {
                if (version_compare (phpversion (), '5.0.0', '>=')) {
                    if (ip2long ($ips[$i]) != false) {
                        $ip = $ips[$i];
                        break;
                    }
                }

                if (ip2long ($ips[$i]) != 0 - 1) {
                    $ip = $ips[$i];
                    break;
                }
            }

            ++$i;
        }
    }

    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

function do_post_request ($url, $data, $optional_headers = null)
{
    $params = array ('http' => array ('method' => 'POST', 'content' => $data));
    if ($optional_headers !== null) {
        $params['http']['header'] = $optional_headers;
    }

    $ctx = stream_context_create ($params);
    $fp = @fopen ($url, 'rb', false, $ctx);
    if (!$fp) {
        exit ('' . 'Problem with ' . $url . ', ' . $php_errormsg);
    }

    $response = @stream_get_contents ($fp);
    if ($response === false) {
        exit ('' . 'Problem reading data from ' . $url . ', ' . $php_errormsg);
    }

    return $response;
}

$do = (isset ($_POST['do']) ? htmlspecialchars ($_POST['do']) : (isset ($_GET['do']) ? htmlspecialchars ($_GET['do']) : 1));
stdhead ('Ip to Country');
$errormessage = '';
if ($do == 2) {
    $ip = ((isset ($_POST['ip_address']) AND !empty ($_POST['ip_address'])) ? $_POST['ip_address'] : ((isset ($_GET['ip_address']) AND !empty ($_GET['ip_address'])) ? $_GET['ip_address'] : i2c_realip ()));
    $post_data = array ();
    $post_data['ip_address'] = $ip;
    if ((function_exists ('curl_init') AND $ch = curl_init ())) {
        curl_setopt ($ch, CURLOPT_URL, 'http://ip-to-country.webhosting.info/node/view/36');
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $postResult = curl_exec ($ch);
        if (curl_errno ($ch)) {
            exit (curl_error ($ch));
        }

        curl_close ($ch);
    } else {
        $postResult = do_post_request ('http://ip-to-country.webhosting.info/node/view/36', 'ip_address=' . $ip);
    }

    begin_frame('Search Result');
    if (empty ($errormessage)) {
        $regex = '' . '#<b>' . $ip . '</b>(.*).<br><br><img src=(.*)>#U';
        preg_match_all ($regex, $postResult, $result, PREG_SET_ORDER);
        echo '<tr><td align=center>IP Address <b>' . htmlspecialchars($ip) . '</b>' . $result[0][1] . '.<br><br><img src="http://ip-to-country.webhosting.info/' . $result[0][2] . '"></td></tr>';
    } else {
        echo '<tr><td>' . $errormessage . '</td></tr>';
    }

    end_frame();
    echo '<br>';
}

begin_frame('Ip to Country');
echo '
<tr><td align=center>
<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '">
<input type="hidden" name="act" value="iptocountry">
<input type="hidden" name="do" value="2">
IP Address: <input name="ip_address" type="text" value="' . htmlspecialchars($ip) . '">
<input value="Find Country" name="submit" type="submit" onclick="tb_show(\'loading-layer\')">
</td></tr></form>';
end_frame();
stdfoot();

?>