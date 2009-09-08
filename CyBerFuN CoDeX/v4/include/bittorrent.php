<?php
$stime = array_sum(explode(' ', microtime())); // start execution time
$tstart = timer(); // Start timer
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
define('PUBLIC_ACCESS', true);
require_once ROOT_PATH."include/config.php"; 
require_once ROOT_PATH."include/ctracker.php"; 
require_once ROOT_PATH."include/vfunc.php"; 
require_once ROOT_PATH."include/cleanup.php"; //==
require_once ROOT_PATH."include/function_happyhour.php"; 
require_once ROOT_PATH."include/mood.php"; 
require_once ROOT_PATH."include/class.inputfilter_clean.php"; 
$myFilter = new InputFilter('$tags', '$attributes', 0, 0); // Invoke it////
///////////////sql error log path//////////////
$sql_error_log = './logs/sql_err_'.date("M_D_Y").'.log';
//////////////////////////////////////////////////////////////////////////
if (empty($mysql_user) && empty($mysql_pass))
    die("Site is down for maintenance, please check back again later... thanks<br />");
if ($erreport)
    error_reporting(E_ALL);
else
    error_reporting(E_ALL  & ~E_NOTICE);
if ($_SERVER["HTTP_HOST"] == "") // Root Based Installs Comment Out if in Sub-Dir
    $_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"]; // Comment out for Sub-Dir Installs
// $BASEURL = "http://" . $_SERVER["HTTP_HOST"];           // Comment out for Sub-Dir Installs
define('SQL_DEBUG', 1);
define('DEBUG_MODE', 1);
define ('IN_TRACKER', 'God! Your so sexy...');
// /////////////function safeChar/unsafeChar//==//
function unsafeChar($var)
{
    return str_replace(array("&gt;", "&lt;", "&quot;", "&amp;"), array(">", "<", "\"", "&"), $var);
}
function safeChar($var)
{
    return htmlspecialchars(unsafeChar($var));
}
function makeSafeText($arr)
{
    foreach ($arr as $k => $v) {
        if (is_array($v))
            $arr[$k] = makeSafeText($v);
        else
            $arr[$k] = safeChar($v);
    }
    return $arr;
}
// Makes the data safe
if (!defined('IN_ANNOUNCE')) {
    if (!empty($_GET)) $_GET = makeSafeText($_GET);
    if (!empty($_POST)) $_POST = makeSafeText($_POST);
    if (!empty($_COOKIE)) $_COOKIE = makeSafeText($_COOKIE);
}
// ///////Strip slashes by system//////////
function cleanquotes(&$in)
{
    if (is_array($in)) return array_walk($in, 'cleanquotes');
    return $in = stripslashes($in);
}
if (get_magic_quotes_gpc()) {
    array_walk($_GET, 'cleanquotes');
    array_walk($_POST, 'cleanquotes');
    array_walk($_COOKIE, 'cleanquotes');
    array_walk($_REQUEST, 'cleanquotes');
}
$add_set ='';
function local_user()
{
    return $_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"];
}

dbconn(false, false);

/**
* *** validip/getip courtesy of manolete <manolete@myway.com> ***
*/
// IP Validation
function validip($ip)
{
    if (!empty($ip) && $ip == long2ip(ip2long($ip))) {
        // reserved IANA IPv4 addresses
        // http://www.iana.org/assignments/ipv4-address-space
        $reserved_ips = array (
            array('0.0.0.0', '2.255.255.255'),
            array('10.0.0.0', '10.255.255.255'),
            array('127.0.0.0', '127.255.255.255'),
            array('169.254.0.0', '169.254.255.255'),
            array('172.16.0.0', '172.31.255.255'),
            array('192.0.2.0', '192.0.2.255'),
            array('192.168.0.0', '192.168.255.255'),
            array('255.255.255.0', '255.255.255.255')
            );

        foreach ($reserved_ips as $r) {
            $min = ip2long($r[0]);
            $max = ip2long($r[1]);
            if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
        }
        return true;
    } else return false;
}
// Patched function to detect REAL IP address if it's valid
function getip()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }
    }

    return $ip;
}

function dbconn($autoclean = false, $userlogin = true)
{
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;

    if (!@mysql_connect($mysql_host, $mysql_user, $mysql_pass)) {
        switch (mysql_errno()) {
            case 1040:
            case 2002:
                if ($_SERVER[REQUEST_METHOD] == "GET")
                    die("<html><head><meta http-equiv=refresh content=\"5 $_SERVER[REQUEST_URI]\"></head><body><table border=0 width=100% height=100%><tr><td><h3 align=center>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
                else
                    die("Too many users. Please press the Refresh button in your browser to retry.");
            default:
                die("[" . mysql_errno() . "] dbconn: mysql_connect: " . mysql_error());
        }
    }
    mysql_select_db($mysql_db)
    or die('dbconn: mysql_select_db: ' + mysql_error());

    if ($userlogin) userlogin();

    if ($autoclean)
        register_shutdown_function("autoclean");
}
function status_change($id)
{
    sql_query('UPDATE announcement_process SET status = 0 WHERE user_id = ' . sqlesc($id) . ' AND status = 1');
}
// ////////////////////////////////////////////////////////////////////
function maxcoder ()
{
    global $CURUSER;
    $lmaxclass = 7;
    $filename = ROOT_PATH . "settings/STAFFNAMES";
    $filename2 = ROOT_PATH . "settings/STAFFIDS";
    if ($CURUSER['class'] >= $lmaxclass) {
        $fp = fopen($filename, 'r');
        while (!feof($fp)) {
            $staffnames = fgets($fp);
            $results = explode(' ', $staffnames);
        }
        $added = sqlesc(get_date_time());
        if (!in_array($CURUSER['username'], $results, true)) { // /////== true for strict comparison - super class detection .. not in array = disable the fuckers and ban the ip
            sql_query("UPDATE users set enabled='no' WHERE id=$CURUSER[id]");
            $ban_ip = sqlesc(trim(ip2long($_SERVER['REMOTE_ADDR'])));
            $comment = sqlesc('Super User Hack Attempt');
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES ($added, '0', $ban_ip, $ban_ip, $comment)") or sqlerr(__FILE__, __LINE__);
            $subject = sqlesc("Alert Super User Has been Detected");
            $body = sqlesc("User " . $CURUSER["username"] . " has attempted to hack the tracker using a super class - the account has been disabled");
            auto_post($subject , $body);
            $msg = "Hack Attempt Detected - now go to ip bans in staff tools and cache the ban : Username: " . $CURUSER["username"] . " - UserID: " . $CURUSER["id"] . " - UserIP : " . getip();
            sql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg) VALUES(0, 0, '1', '" . get_date_time() . "', " . $subject . " , " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
            write_log($msg);
            fclose($fp);
            stderr("Access Denied!", "Ha Ha you retard - Did you honestly think you could pull that one off !");
        }
        fclose($fp);
    }
    define ('UC_STAFF', 4); ///////== Minumum Staff Level (4=UC_MODERATOR)
    if ($CURUSER['class'] >= UC_STAFF) {
        $fp2 = fopen($filename2, 'r');
        while (!feof($fp2)) {
            $staffids = fgets($fp2);
            $results2 = explode(' ', $staffids);
        }
        if (!in_array($CURUSER['id'], $results2, true)) { // ////== true for strict comparison if there not in the array disable the fuckers and ban the ip :)
            sql_query("UPDATE users set enabled='no' WHERE id=$CURUSER[id]");
            $ban_ip = sqlesc(trim(ip2long($_SERVER['REMOTE_ADDR'])));
            $comment = sqlesc('Unauthorized Staff Account Hack');
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES ($added, '0', $ban_ip, $ban_ip, $comment)") or sqlerr(__FILE__, __LINE__);
            $subject = sqlesc("Staff Account Hack Detected");
            $body = sqlesc("User " . $CURUSER["username"] . " has attempted to hack the tracker using an unauthorized account- the account has been disabled");
            auto_post($subject , $body);
            $msg = "Fake Account Detected now go to ip bans in staff tools and cache the ban : Username: " . $CURUSER["username"] . " - UserID: " . $CURUSER["id"] . " - UserIP : " . getip();
            sql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg) VALUES(0, 0, '1', '" . get_date_time() . "', " . $subject . " , " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
            write_log($msg);
            fclose($fp2);
            stderr("Access Denied!", "Sorry but your not an authorized staff member - nice try your banned !");
        }
        fclose($fp2);
    }
    return true;
}
// //////////////////Credits to Retro for the original code :)//////////////////////////////////////
// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0)
{
    if ($timestamp)
        return date("Y-m-d H:i:s", $timestamp);
    else
        return gmdate("Y-m-d H:i:s");
}
function logged_in()
{
    global $CURUSER;
    if (!$CURUSER)return false;
    return true;
    header("Location: login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
    exit();
}
function userlogin()
{
    global  $SITE_ONLINE;
    unset($GLOBALS["CURUSER"]);
    $dt = get_date_time();
    $ip = getip();
    $ipf = $_SERVER['REMOTE_ADDR'];
    $nip = ip2long($ip);
    $nip2 = ip2long($ipf);
    require_once ROOT_PATH . "cache/bans_cache.php";
    if (count($bans) > 0) {
        foreach($bans as $k) {
            if ($nip >= $k['first'] && $nip <= $k['last'] || $nip2 >= $k['first'] && $nip2 <= $k['last']) {
                header("HTTP/1.0 403 Forbidden");
                echo("<html><body><h1>403 Forbidden</h1>Unauthorized IP address.</body></html>\n");
                exit();
            }
        }
        unset($bans);
    }

    if (!$SITE_ONLINE || empty($_COOKIE["uid"]) || empty($_COOKIE["pass"]) || empty($_COOKIE["hashv"]))
        return;
    $id = 0 + $_COOKIE["uid"];
    if (!$id OR (strlen($_COOKIE["pass"]) != 32) OR ($_COOKIE["hashv"] != hashit($id, $_COOKIE["pass"])))
        return;
    // //////////////announcement mod by Retro/////////////////////////
    $res = sql_query("SELECT u.*, ann_main.subject AS curr_ann_subject, ann_main.body AS curr_ann_body " . "FROM users AS u " . "LEFT JOIN announcement_main AS ann_main " . "ON ann_main.main_id = u.curr_ann_id " . "WHERE u.id = $id AND u.enabled='yes' AND u.status = 'confirmed'") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_assoc($res);
    if (!$row)
        return;
    $sec = hash_pad($row["secret"]);
    if ($_COOKIE["pass"] !== md5($row["passhash"] . $_SERVER["REMOTE_ADDR"]))
        return;
    if ($row['logout']=='yes' && $row['last_access'] > $row['last_login']  && $row['last_access'] < time()- 900)
    {
    logoutcookie();	
    return;
    }
    if (($row['last_access'] != '0000-00-00 00:00:00') AND (strtotime($row['last_access']) < (strtotime($dt) - 300))/** 5 mins **/ || ($row['ip'] !== $ip) || ($row['ipf'] !== '' && $row['ipf'] !== $ipf)) 
    { 
    $add_set = (isset($add_set))?$add_set:'';
    sql_query("UPDATE users SET last_access=" . sqlesc($dt) . ", ip=" . sqlesc($ip) . $add_set . ", uptime=uptime+300 WHERE id=" . $row['id']); // or die(mysql_error());
    }
    if (($row['ip'] !== $ip) || ($row['ipf'] !== '' && $row['ipf'] !== $ipf))
    	 sql_query('INSERT INTO iplog (ip, userid, access) VALUES (' . sqlesc($ip) . ', ' . $row['id'] . ', \'' . $row['last_access'] . '\') on DUPLICATE KEY update access=values(access)');  
    // If curr_ann_id > 0 but curr_ann_body IS NULL, then force a refresh
    if (($row['curr_ann_id'] > 0) AND ($row['curr_ann_body'] == null)) {
        $row['curr_ann_id'] = 0;
        $row['curr_ann_last_check'] = '0000-00-00 00:00:00';
    }
    // If elapsed > 10 minutes, force a announcement refresh.
    if (($row['curr_ann_last_check'] != '0000-00-00 00:00:00') AND
            (strtotime($row['curr_ann_last_check']) < (strtotime($dt) - 300)))
        $row['curr_ann_last_check'] = '0000-00-00 00:00:00';

    if (($row['curr_ann_id'] == 0) AND ($row['curr_ann_last_check'] == '0000-00-00 00:00:00')) { // Force an immediate check...
        $query = sprintf('SELECT m.*,p.process_id FROM announcement_main AS m ' . 'LEFT JOIN announcement_process AS p ON m.main_id = p.main_id ' . 'AND p.user_id = %s ' . 'WHERE p.process_id IS NULL ' . 'OR p.status = 0 ' . 'ORDER BY m.main_id ASC ' . 'LIMIT 1',
            sqlesc($row['id']));

        $result = mysql_query($query);

        if (mysql_num_rows($result)) { // Main Result set exists
            $ann_row = mysql_fetch_array($result);

            $query = $ann_row['sql_query'];
            // Ensure it only selects...
            if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $query)) die();
            // The following line modifies the query to only return the current user
            // row if the existing query matches any attributes.
            $query .= ' AND u.id = ' . sqlesc($row['id']) . ' LIMIT 1';

            $result = mysql_query($query);

            if (mysql_num_rows($result)) { // Announcement valid for member
                $row['curr_ann_id'] = $ann_row['main_id'];
                // Create two row elements to hold announcement subject and body.
                $row['curr_ann_subject'] = $ann_row['subject'];
                $row['curr_ann_body'] = $ann_row['body'];
                // Create additional set for main UPDATE query.
                $add_set = ', curr_ann_id = ' . sqlesc($ann_row['main_id']);
                $status = 2;
            } else {
                // Announcement not valid for member...
                $add_set = ', curr_ann_last_check = ' . sqlesc($dt);
                $status = 1;
            }
            // Create or set status of process
            if ($ann_row['process_id'] === null) {
                // Insert Process result set status = 1 (Ignore)
                $query = sprintf('INSERT INTO announcement_process (main_id, ' . 'user_id, status) VALUES (%s, %s, %s)',
                    sqlesc($ann_row['main_id']),
                    sqlesc($row['id']),
                    sqlesc($status));
            } else {
                // Update Process result set status = 2 (Read)
                $query = sprintf('UPDATE announcement_process SET status = %s ' . 'WHERE process_id = %s',
                    sqlesc($status),
                    sqlesc($ann_row['process_id']));
            }
            mysql_query($query);
        } else {
            // No Main Result Set. Set last update to now...
            $add_set = ', curr_ann_last_check = ' . sqlesc($dt);
            //$add_set = ', curr_ann_last_check = '.sqlesc($dt).', curr_ann_id = curr_ann_id';
        }
        unset($result);
        unset($ann_row);
    }

    session_cache_limiter('private');
    session_start();
    if ((!isset($_SESSION['browsetime'])) || ($row['ip'] !== $ip))
    $_SESSION['browsetime'] = strtotime($row['last_access']);
    $row['ip'] = $ip;
    $GLOBALS["CURUSER"] = $row;
    if ($row['override_class'] < $row['class']) $row['class'] = $row['override_class']; // Override class and save in GLOBAL array below.
    $GLOBALS["CURUSER"] = $row;

}

function autoclean()
{
    global $autoclean_interval, $autoslowclean_interval, $s2autoslowclean_interval, $optimizedb_interval, $backupdb_interval, $autohitrun_interval;
    $now = gmtime();
    /* Better cleanup function with db-optimization dbbackup - //==added hitandrun==//   by x0r @ tbdev.net */
    $w00p = sql_query("SELECT arg, value_u FROM avps") or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_assoc($w00p)) {
        if ($row['arg'] == "lastcleantime" && ($row['value_u'] + $autoclean_interval) < $now) {
            sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastcleantime'") or sqlerr(__FILE__, __LINE__);
            docleanup();
        } else if ($row['arg'] == "lastslowcleantime" && ($row['value_u'] + $autoslowclean_interval) < $now) {
            sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastslowcleantime'") or sqlerr(__FILE__, __LINE__);
            doslowcleanup();
         } else if ($row['arg'] == "s2slowcleantime" && ($row['value_u'] + $s2autoslowclean_interval) < $now) {
            sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 's2slowcleantime'") or sqlerr(__FILE__, __LINE__);
            dos2slowcleanup();
        } else if ($row['arg'] == "lastoptimizedbtime" && ($row['value_u'] + $optimizedb_interval) < $now) {
            sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastoptimizedbtime'") or sqlerr(__FILE__, __LINE__);
            dooptimizedb();
        } else if ($row['arg'] == "lastbackupdbtime" && ($row['value_u'] + $backupdb_interval) < $now) {
            sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastbackupdbtime'") or sqlerr(__FILE__, __LINE__);
            dobackupdb();
        } else if ($row['arg'] == "lastautohitruntime" && ($row['value_u'] + $autohitrun_interval) < $now) {
            sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastautohitruntime'") or sqlerr(__FILE__, __LINE__);
            doautohitrun();
        }
    }
    mysql_free_result($w00p);
    return;
}

function unesc($x)
{
    if (get_magic_quotes_gpc())
        return stripslashes($x);
    return $x;
}

function prefixed($bytes)
{
    $prefixes = array("", "k", "M", "G", "T", "P", "E", "Z", "Y", "B", "Geop");
    $i = 0;
    $div = 1;
    while ($bytes / $div > 1024 && $i < count($prefixes)) {
        $i++;
        $div *= 1024;
    }

    return round($bytes / $div, 2) . " " . $prefixes[$i] . "B";
}

function deadtime()
{
    global $announce_interval;
    return time() - floor($announce_interval * 1.3);
}

function mkprettytime($s)
{
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec", "60:min", "24:hour", "0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        } else
            $v = $s;
        $t[$y[1]] = $v;
    }

    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    // if ($t["min"])
    return sprintf("%d:%02d", $t["min"], $t["sec"]);
    // return $t["sec"] . " secs";
}

function mkglobal($vars)
{
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = unesc($_GET[$v]);
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = unesc($_POST[$v]);
        else
            return 0;
    }
    return 1;
}

if (!function_exists("stripos")) {
    function stripos($str, $needle, $offset = 0)
    {
        return strpos(strtolower($str), strtolower($needle), $offset);
    }
}
function display_date_time($time)
{
    global $CURUSER;
    return date("Y-m-d H:i:s", strtotime($time) + (($CURUSER["timezone"] + $CURUSER["dst"]) * 60));
}
function cpfooter()
{
    $referring_url = $_SERVER['HTTP_REFERER'];
    echo("<table class=bottom width=100% border=0 cellspacing=0 cellpadding=0><tr valign=top>\n");
    echo("<td class=bottom align=center><p><br><a href=$referring_url>Return to whence you came</a></td>\n");
    echo("</tr></table>\n");
}
// /////////yuna scatari's sql query counter with percentage php and sql////
function sql_query($query)
{
    global $queries, $query_stat, $querytime;
    $queries++;
    $query_start_time = timer(); // Start time
    $result = mysql_query($query);
    $query_end_time = timer(); // End time
    $query_time = ($query_end_time - $query_start_time);
    $querytime = $querytime + $query_time;
    $query_time = substr($query_time, 0, 8);
    $query_stat[] = array("seconds" => $query_time, "query" => $query);
    return $result;
}

function timer()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

// //////////nv source torrent limit mod
function get_torrent_limits($userinfo)
{
    $limit = array("seeds" => -1, "leeches" => -1, "total" => -1);

    if ($userinfo["tlimitall"] == 0) {
        // Auto limit
        $ruleset = explode("|", $GLOBALS["TORRENT_RULES"]);
        $ratio = (($userinfo["downloaded"] > 0) ? ($userinfo["uploaded"] / $userinfo["downloaded"]) : (($userinfo["uploaded"] > 0) ? 1 : 0));
        $gigs = $userinfo["uploaded"] / 1073741824;

        $limit = array("seeds" => 0, "leeches" => 0, "total" => 0);
        foreach ($ruleset as $rule) {
            $rule_parts = explode(":", $rule);
            if ($ratio >= $rule_parts[0] && $gigs >= $rule_parts[1] && $limit["total"] <= $rule_parts[4]) {
                $limit["seeds"] = $rule_parts[2];
                $limit["leeches"] = $rule_parts[3];
                $limit["total"] = $rule_parts[4];
            }
        }
    } elseif ($userinfo["tlimitall"] > 0) {
        // Manual limit
        $limit["seeds"] = $userinfo["tlimitseeds"];
        $limit["leeches"] = $userinfo["tlimitleeches"];
        $limit["total"] = $userinfo["tlimitall"];
    }

    return $limit;
}
function tr($x, $y, $noesc = 0)
{
    if ($noesc)
        $a = $y;
    else {
        $a = htmlspecialchars($y);
        $a = str_replace("\n", "<br />\n", $a);
    }
    echo("<tr><td class=\"heading\" valign=\"top\" align=\"right\">$x</td><td valign=\"top\" align=left>$a</td></tr>\n");
}

function trala($x, $y, $noesc = 0)
{
    if ($noesc)
        $a = $y;
    echo("<tr><td class=\"heading\" valign=\"top\" align=\"right\">$x</td><td valign=\"top\" align=left>$a</td></tr>\n");
}

function validfilename($name)
{
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email)
{
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}
function add_s($i)
{
    return ($i == 1 ? "" : "s");
}
// ////////modified sqlesc //==putyn@tbdev
function sqlesc($x)
{
    if (get_magic_quotes_gpc())
        $x = stripslashes($x);
    return is_numeric($x) ? $x : "'" . mysql_real_escape_string(unsafeChar($x)) . "'";
}
// /////////////////////////////////////////////
function sqlwildcardesc($x)
{
    return str_replace(array("%", "_"), array("\\%", "\\_"), mysql_real_escape_string($x));
}

function urlparse($m)
{
    $t = $m[0];
    if (preg_match(',^\w+://,', $t))
        return "<a href=\"$t\">$t</a>";
    return "<a href=\"http://$t\">$t</a>";
}

function parsedescr($d, $html)
{
    if (!$html) {
        $d = htmlspecialchars($d);
        $d = str_replace("\n", "\n<br>", $d);
    }
    return $d;
}
function safe($var)
{
    return str_replace(array('&', '>', '<', '"', '\''), array('&amp;', '&gt;', '&lt;', '&quot;', '&#039;'), str_replace(array('&gt;', '&lt;', '&quot;', '&#039;', '&amp;'), array('>', '<', '"', '\'', '&'), $var));
}
function hashit($var, $addtext = "")
{
    return md5("Some " . $addtext . $var . $addtext . " sal7 mu55ie5 wat3r.@.");
}
// ///////////// Basic MySQL error handler//==tbsource alpha
function sqlerr($file = '', $line = '')
{
    global $sql_error_log, $CURUSER;

    $the_error = mysql_error();
    $the_error_no = mysql_errno();

    if (SQL_DEBUG == 0) {
        exit();
    } else if ($sql_error_log AND SQL_DEBUG == 1) {
        $_error_string = "\n===================================================";
        $_error_string .= "\n Date: " . date('r');
        $_error_string .= "\n Error Number: " . $the_error_no;
        $_error_string .= "\n Error: " . $the_error;
        $_error_string .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
        $_error_string .= "\n in file " . $file . " on line " . $line;
        $_error_string .= "\n URL:" . $_SERVER['REQUEST_URI'];
        $_error_string .= "\n Username: {$CURUSER['username']}[{$CURUSER['id']}]";

        if ($FH = @fopen($sql_error_log, 'a')) {
            @fwrite($FH, $_error_string);
            @fclose($FH);
        }

        echo "<html><head><title>MySQL Error</title>
					<style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
		    		   <blockquote><h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
		    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>
				  </body></html>";
    } else {
        $the_error = "\nSQL error: " . $the_error . "\n";
        $the_error .= "SQL error code: " . $the_error_no . "\n";
        $the_error .= "Date: " . date("l dS \of F Y h:i:s A");

        $out = "<html>\n<head>\n<title>MySQL Error</title>\n
	    		   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
	    		   <blockquote>\n<h1>MySQL Error</h1><b>There appears to be an error with the database.</b><br />
	    		   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
	    		   <br /><br /><b>Error Returned</b><br />
	    		   <form name='mysql'><textarea rows=\"15\" cols=\"60\">" . htmlentities($the_error, ENT_QUOTES) . "</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>";

        echo $out;
    }

    exit();
}

function getrow($id, $value, $arr)
{
    foreach($arr as $row)
    if ($row[$id] == $value)
        return $row;
    return false;
}
// ///////////////////////////////////////////////
function stdhead($title = "", $msgalert = true)
{
    global $CURUSER, $BASEURL, $onoff, $reason, $class_name, $class, $SITE_ONLINE, $FUNDS, $SITENAME, $php_file, $smilies, $privatesmilies, $customsmilies, $mood, $pic_base_url, $BASEURL, $CACHE, $mood, $free_for_all, $freetitle, $freemessage, $double_for_all, $doubletitle, $doublemessage , $page_find, $lang_off, $language, $config, $cat_ico_uri;
    // ////site on/off
    if ($onoff != 1) {
        $my_siteoff = 1;
        $my_siteopenfor = $class_name;
    }
    if (($onoff != 1) && (!$CURUSER)) {
        die("<title>Site Offline!</title>
<table width='100%' height='100%' bgcolor='orange' style='border: 8px inset #000000'><tr><td align='center'>
<h1 style='color: #000000;'>" . safeChar($reason) . "</h1>
<h1 style='color: #000000;'>
Please, try later...</h1>
<img border=0 class=embedded width='800' height='300' src=pic/404.jpg>
<br><center><form method='post' action='takesiteofflogin.php'>
<table border='1' cellspacing='1' id='table1' cellpadding='3' style='border-collapse: collapse'>
<tr><td colspan='2' align='center' bgcolor='orange'>
<font color='black'><u><b>Staff Access Only </b></u></font></td></tr>
<tr><td><font color='black'><b>Name:</b></font></td>
<td><input type='text' size=20 name='username'></td></tr><tr>
<td><font color='black'><b>Password:</b></font></td>
<td><input type='password' size=20 name='password'></td>
</tr><tr>
<td colspan='2' align='center'>
<input type='submit' value='Submit!'></td>
</tr></table>
</form></center>
</td></tr></table>");
    }
    if (($onoff != 1) and (($CURUSER["class"] < $class && ($CURUSER["id"] != 1)))) {
        die("<title>Site Offline!</title>
<table width='100%' height='100%' bgcolor='orange' style='border: 8px inset #000000'><tr><td align='center'>
<h1 style='color: #000000;'>" . safeChar($reason) . "</h1>
<h1 style='color: #000000;'>
Please, try later...</h1>
<img border=0 class=embedded width='800' height='300' src=pic/404.jpg>
</td></tr></table>");
    }
// ///////////end on/off
global $ss_uri, $CURUSER, $BASEURL, $SITE_ONLINE, $FUNDS, $SITENAME, $config, $php_file, $CACHE, $page_find, $lang_off, $language, $cat_ico_uri;
/** languages by pdq **/
$langs = array('Arabic', 'Danish', 'Nederlands', 'French', 'German', 'Greek', 'Hebrew', 'Hungarian', 'Latvian', 'Portuguese', 'Romanian',  'Swedish', 'Finnish', 'Italian', 'Spanish', 'English');
if (isset($_GET['lang']) && (in_array($_GET['lang'], $langs)))
{
    switch ($_GET['lang'])
    {
        case 'Arabic';
            $this_lang = 'Arabic';
            break;
        case 'Danish';
            $this_lang = 'Danish';
            break;
        case 'Nederlands';
            $this_lang = 'Nederlands';
            break;
        case 'French';
            $this_lang = 'French';
            break;
        case 'German';
            $this_lang = 'German';
            break;
        case 'Greek';
            $this_lang = 'Greek';
            break;
        case 'Hebrew';
            $this_lang = 'Hebrew';
            break;
        case 'Hungarian';
            $this_lang = 'Hungarian';
            break;
        case 'Latvian';
            $this_lang = 'Latvian';
            break;
        case 'Portuguese';
            $this_lang = 'Portuguese';
            break;
        case 'Romanian';
            $this_lang = 'Romanian';
            break;
        case 'Swedish';
            $this_lang = 'Swedish';
            break;
        case 'Finnish';
            $this_lang = 'Finnish';
            break;
        case 'Italian';
            $this_lang = 'Italian';
            break;
        case 'Spanish';
            $this_lang = 'Spanish';
            break;
        case 'English';
            $this_lang = 'English';
            break;
  }
  setcookie("language", $this_lang, time()+3600*24*1000, "/");
  $is_id =(isset($_GET['id'])?'?id='.$_GET['id']:'?lang=updated');
  header("Location: ".$is_id);
  }
  if (isset($_COOKIE['language']) && (!in_array($_COOKIE['language'], $langs)))
  setcookie('language', '', 0, '/');   

  $php_file = (isset($page_find)?'/'.$page_find.'/':'');

  $lang = (!isset($_COOKIE['language'])?'English':$_COOKIE['language']);
  if (!isset($lang_off))
  require_once ROOT_PATH.'languages/'.$page_find.'/'.$lang.'.php';
  else
  $language = '';
  /** end of languages mod **/
    if (!$SITE_ONLINE)
        die($language['server_down']);
    header("Content-Type: text/html; charset=".$language['charset']);
    header("Pragma: No-cache");
    //header("Expires: 300");
    header("Cache-Control: private");
    if ($title == "")
        $title = $SITENAME . (isset($_GET['tbv'])?" (" . TBVERSION . ")":'');
    else
        $title = $SITENAME . (isset($_GET['tbv'])?" (" . TBVERSION . ")":'') . " :: " . safeChar($title);
    include_once ("cache/stylesheets.php");
    if ($CURUSER) {
        $stylesheet = getrow('id', "{$CURUSER['stylesheet']}", $stylesheets);

        $ss_a = $stylesheet['uri'];
        if ($ss_a)
            $ss_uri = $ss_a;
    }
    if (!$ss_uri) {
        $stylesheet = getrow('id', '1', $stylesheets);

        //$ss_uri = $stylesheet['uri'];
$ss_uri = "NB-Revolt";
    }
    $GLOBALS["curentstyle"] = $ss_uri;
    /////////// cached by Bigjoos - Categorie Icon Set`s by ShadoW69   
    include_once ("cache/categorie_icons.php");
    if ($CURUSER) {
        $categorie_icon = getrow('id', "{$CURUSER['categorie_icon']}", $categorie_icons);

        $ss_ci = $categorie_icon['uri'];
        if ($ss_ci)
            $cat_ico_uri = $ss_ci;
    }
    if (!$cat_ico_uri) {
        $categorie_icon = getrow('id', '1', $categorie_icons);

        $cat_ico_uri = $categorie_icon['uri'];
    }
    $GLOBALS["currenticon"] = $cat_ico_uri;
 //cached by Bigjoos - Categorie Icon Set`s by ShadoW69
    require_once("themes/" . $ss_uri . "/template.php");
    require_once("themes/" . $ss_uri . "/stdhead.php");
} // stdhead
function stdfoot()
{
    global $CURUSER, $ss_uri;
    require_once("themes/" . $ss_uri . "/template.php");
    require_once("themes/" . $ss_uri . "/stdfoot.php");
}
// //////////////////////////
function genbark($x, $y)
{
    stdhead($y);
    echo("<h2>" . safeChar($y) . "</h2>\n");
    echo("<p>" . safeChar($x) . "</p>\n");
    stdfoot();
    exit();
}

function mksecret($length = 20)
{
    $set = array("a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G", "h", "H", "i", "I", "j", "J", "k", "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r", "R", "s", "S", "t", "T", "u", "U", "v", "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $str;
    for($i = 1; $i <= $length; $i++) {
        $ch = rand(0, count($set)-1);
        $str .= $set[$ch];
    }
    return $str;
}

function httperr($code = 404)
{
    header("HTTP/1.0 404 Not found");
    echo("<h1>Not Found</h1>\n");
    echo("<p>Sorry pal :(</p>\n");
    exit();
}

function gmtime()
{
    return strtotime(get_date_time());
}

function logincookie($id, $passhash, $updatedb = 1, $expires = 0x7fffffff)
{
    setcookie("uid", $id, $expires, "/");
    setcookie("pass", $passhash, $expires, "/");
    setcookie("hashv", hashit($id, $passhash), $expires, "/");

    if ($updatedb)
        sql_query("UPDATE users SET last_login = NOW() WHERE id = $id");
}

function logoutcookie()
{
    setcookie("uid", "", 0x7fffffff, "/");
    setcookie("pass", "", 0x7fffffff, "/");
    setcookie("hashv", "", 0x7fffffff, "/");
}

function loggedinorreturn()
{
    global $CURUSER;
    if (!$CURUSER) {
        header("Location: login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}
//////////////////pre times
/*
function ago($seconds)
{
    $day = date("j", $seconds)-1;
    $month = date("n", $seconds)-1;
    $year = date("Y", $seconds)-1970;
    $hour = date("G", $seconds)-1;
    $minute = (int) date("i", $seconds);
    $returnvalue = false;
    if ($year) {
        if ($year == 1) $return[] = "1 year";
        else $return[] = "$year years";
    }
    if ($month) {
        if ($month == 1) $return[] = "1 month";
        else $return[] = "$month months";
    }
    if ($day) {
        if ($day == 1) $return[] = "1 day";
        else $return[] = "$day days";
    }
    if ($hour) {
        if ($hour == 1) $return[] = "1 hour";
        else $return[] = "$hour hours";
    }
    if ($minute && $minute != 00) {
        if ($minute == 1) {
            $return[] = "1 minute";
        } else {
            $return[] = "$minute minutes";
        }
    }
    for($i = 0;$i < count($return);$i++) {
        if (!$returnvalue) {
            $returnvalue = $return[$i];
        } elseif ($i < count($return)-1) {
            $returnvalue .= ", " . $return[$i];
        } else {
            $returnvalue .= " and " . $return[$i];
        }
    }
    return $returnvalue;
}
function getpre($name, $type)
{
    $pre['regexp'] = "|<td>(.*)<td>(.*)<td>(.*)</table>|";
    $pre['url'] = "http://doopes.com/?cat=454647&lang=0&num=2&mode=0&from=&to=&exc=&inc=" . $name . "&opt=0";
    $pre['file'] = @file_get_contents($pre['url']);
    preg_match($pre['regexp'], $pre['file'], $pre['matches']);
    /**
    * Types:
    * 1 = Time
    * 2 = Category
    * 3 = Realesename
    *//*
    return $pre['matches'][$type];
}
*/
/////////////////////////////
?>
