<?php
require_once ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_CODER)
    hacker_dork("Cache Sheets - Nosey Cunt !");

stdhead("System Overview");

echo "<a href='system_view.php?phpinfo=1'>CLICK</a><br />";

if (isset($_GET['phpinfo']) AND $_GET['phpinfo']) {
    @ob_start();
    phpinfo();
    $parsed = @ob_get_contents();
    @ob_end_clean();

    preg_match("#<body>(.*)</body>#is" , $parsed, $match1);

    $php_body = $match1[1];
    // PREVENT WRAP: Most cookies
    $php_body = str_replace("; " , ";<br />" , $php_body);
    // PREVENT WRAP: Very long string cookies
    $php_body = str_replace("%3B", "<br />" , $php_body);
    // PREVENT WRAP: Serialized array string cookies
    $php_body = str_replace(";i:", ";<br />i:" , $php_body);
    // PREVENT WRAP: LS_COLORS env
    $php_body = str_replace(":*.", "<br />:*." , $php_body);
    // PREVENT WRAP: PATH env
    $php_body = str_replace("bin:/", "bin<br />:/" , $php_body);
    // PREVENT WRAP: Cookie %2C split
    $php_body = str_replace("%2C", "%2C<br />" , $php_body);
    // PREVENT WRAP: Cookie , split
    $php_body = preg_replace("#,(\d+),#", ",<br />\\1," , $php_body);

    $php_style = "<style type='text/css'>
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left; }
.center th { text-align: center; }
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold;}
.h {background-color: #9999cc; font-weight: bold;}
.v {background-color: #cccccc; white-space: normal;}
</style>\n";

    $html = $php_style . $php_body;
    echo $html;
    stdfoot();
    exit();
}

$html = array();
function sql_get_version()
{
    $query = mysql_query("SELECT VERSION() AS version");

    if (! $row = mysql_fetch_assoc($query)) {
        unset($row);
        $query = mysql_query("SHOW VARIABLES LIKE 'version'");
        $row = mysql_fetch_row($query);
        $row['version'] = $row[1];
    }

    $true_version = $row['version'];
    $tmp = explode('.', preg_replace("#[^\d\.]#", "\\1", $row['version']));

    $mysql_version = sprintf('%d%02d%02d', $tmp[0], $tmp[1], $tmp[2]);
    return $mysql_version . " (" . $true_version . ")";
}

$php_version = phpversion() . " (" . @php_sapi_name() . ") ( <a href='{$BASEURL}/system_view.php?phpinfo=1'>PHP INFO</a> )";
$server_software = php_uname();
// print $php_version ." ".$server_software;
$load_limit = "--";
$server_load_found = 0;
$using_cache = 0;

$avp = @mysql_query("SELECT value_s FROM avps WHERE arg = 'loadlimit'");
if (false !== $row = mysql_fetch_assoc($avp)) {
    $loadinfo = explode("-", $row['value_s']);

    if (intval($loadinfo[1]) > (time() - 20)) {
        $server_load_found = 1;
        $using_cache = 1;
        $load_limit = $loadinfo[0];
    }
}

if (!$server_load_found) {
    if (@file_exists('/proc/loadavg')) {
        if ($fh = @fopen('/proc/loadavg', 'r')) {
            $data = @fread($fh, 6);
            @fclose($fh);

            $load_avg = explode(" ", $data);

            $load_limit = trim($load_avg[0]);
        }
    } else if (strstr(strtolower(PHP_OS), 'win')) {
        $serverstats = @shell_exec("typeperf \"Processor(_Total)\% Processor Time\" -sc 1");

        if ($serverstats) {
            $server_reply = explode("\n", str_replace("\r", "", $serverstats));
            $serverstats = array_slice($server_reply, 2, 1);

            $statline = explode(",", str_replace('"', '', $serverstats[0]));

            $load_limit = round($statline[1], 4);
        }
    } else {
        if ($serverstats = @exec("uptime")) {
            preg_match("/(?:averages)?\: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $serverstats, $load);

            $load_limit = $load[1];
        }
    }

    if ($load_limit) {
        @mysql_query("UPDATE avps SET value_s = '" . $load_limit . "-" . time() . "' WHERE arg = 'loadlimit'");
    }
}

$total_memory = $avail_memory = "--";

if (strstr(strtolower(PHP_OS), 'win')) {
    $mem = @shell_exec('systeminfo');

    if ($mem) {
        $server_reply = explode("\n", str_replace("\r", "", $mem));

        if (count($server_reply)) {
            foreach($server_reply as $info) {
                if (strstr($info, "Total Physical Memory")) {
                    $total_memory = trim(str_replace(":", "", strrchr($info, ":")));
                }

                if (strstr($info, "Available Physical Memory")) {
                    $avail_memory = trim(str_replace(":", "", strrchr($info, ":")));
                }
            }
        }
    }
} else {
    $mem = @shell_exec("free -m");
    $server_reply = explode("\n", str_replace("\r", "", $mem));
    $mem = array_slice($server_reply, 1, 1);
    $mem = preg_split("#\s+#", $mem[0]);

    $total_memory = $mem[1] . ' MB';
    $avail_memory = $mem[3] . ' MB';
}

$disabled_functions = @ini_get('disable_functions') ? str_replace(",", ", ", @ini_get('disable_functions')) : "<i>no information</i>";

if (strstr(strtolower(PHP_OS), 'win')) {
    $tasks = @shell_exec("tasklist");
    $tasks = str_replace(" ", " ", $tasks);
} else {
    $tasks = @shell_exec("top -b -n 1");
    $tasks = str_replace(" ", " ", $tasks);
}

if (!$tasks) {
    $tasks = "<i>Unable to obtain process information</i>";
} else {
    $tasks = "<pre>" . $tasks . "</pre>";
}

$load_limit = $load_limit . " (From Cache: " . ($using_cache == 1 ? "<span style='color:green;font-weight:bold;'>True)</span>" : "<span style='color:red;font-weight:bold;'>False)</span>");
$html[] = array('MySQL Version' , sql_get_version());
$html[] = array("PHP Version", $php_version);
$html[] = array("Safe Mode", @ini_get('safe_mode') == 1 ? "<span style='color:red;font-weight:bold;'>ON</span>" : "<span style='color:green;font-weight:bold;'>OFF</span>");
$html[] = array("Disabled PHP Functions", $disabled_functions);
$html[] = array("Server Software", $server_software);
$html[] = array("Current Server Load", $load_limit);
$html[] = array("Total Server Memory", $total_memory);
$html[] = array("Available Physical Memory", $avail_memory);
$html[] = array("System Processes", $tasks);

echo '<table>';
foreach($html as $key => $value) {
    echo '<tr><td>' . $value[0] . '</td><td>' . $value[1] . '</td></tr>';
}
echo '</table>';
stdfoot();

?>