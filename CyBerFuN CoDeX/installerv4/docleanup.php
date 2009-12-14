<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() != UC_CODER)
    hacker_dork("DoCleanUp - TuT TuT... Cheating are we ??");
function calctime($val)
{
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    $secs = $val - ($mins * 60);

    return $days . " Days, " . $hours . " Hours, " . $mins . " Minutes, " . $secs . " Seconds";
}
if (!function_exists('memory_get_usage')) {
    function memory_get_usage()
    {
        // If its Windows
        // Tested on Win XP Pro SP2. Should work on Win 2003 Server too
        // Doesn't work for 2000
        // If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memo...usage.php#54642
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $output = array();
                exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);

                return preg_replace('/[\D]/', '', $output[5]) * 1024;
            }
        } else {
            // We now assume the OS is UNIX
            // Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
            // This should work on most UNIX systems
            $pid = getmypid();
            exec("ps -eo%mem,rss,pid | grep $pid", $output);
            $output = explode(" ", $output[0]);
            // rss is given in 1024 byte units
            return $output[1] * 1024;
        }
    }
}

stdhead("Cleanup Page");
begin_main_frame('Cleanups');
begin_table();

?><tr><td class="colhead">Cleanup Name</td><td class="colhead">Last Run</td><td class="colhead">Runs every</td><td class="colhead">Scheduled to run</td></tr><?php
$res = mysql_query("SELECT arg, value_u FROM avps");
while ($arr = mysql_fetch_assoc($res)) {
    switch ($arr['arg']) {
        case 'lastcleantime': $arg = $autoclean_interval;
            break;
        case 'lastslowcleantime': $arg = $autoslowclean_interval;
            break;
         case 's2slowcleantime': $arg = $s2autoslowclean_interval;
            break;
        case 'lastoptimizedbtime': $arg = $optimizedb_interval;
            break;
        case 'lastbackupdbtime': $arg = $backupdb_interval;
            break;
        case 'lastautohitruntime': $arg = $autohitrun_interval;
            break;
    }

    echo'<tr>' . '<td>' . $arr['arg'] . '</td>' . '<td>' . get_date_time($arr['value_u']) . ' (' . get_elapsed_time(sql_timestamp_to_unix_timestamp(get_date_time($arr['value_u']))) . ' ago)</td>' . '<td>' . calctime($arg) . '</td>' . '<td>' . calctime($arr['value_u'] - (gmtime() - $arg)) . '</td>' . '</tr>';
}
end_table();

?><br><form action="<?=$_SERVER["SCRIPT_NAME"]?>" method="post">
<table align="center"><tr><td class="chs">
<input type="checkbox" name="docleanup">Do Cleanup
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="doslowcleanup">Do Slow Cleanup
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="dos2slowcleanup">Do Slow Cleanup2
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="dooptimization">Do Optimization
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="dobackupdb">Do Auto Back Up
&nbsp;&nbsp;&nbsp;&nbsp;
<input type="checkbox" name="doautohitrun">Do Hit and Run
<br><br><center><input type="submit" value="Submit"></center>
</td></tr></table>
</form><?php
$now = gmtime();
if ($_POST['docleanup']) {
    require_once("include/cleanup.php");
    sql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastcleantime'") or sqlerr(__FILE__, __LINE__);
    docleanup();
    echo "<br><center><h1>Cleanup Done</h1></center>";
}

if ($_POST['doslowcleanup']) {
    require_once("include/cleanup.php");
    sql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastslowcleantime'") or sqlerr(__FILE__, __LINE__);
    doslowcleanup();
    echo "<br><center><h1>Slow Cleanup Done</h1></center>";
}

if ($_POST['dos2slowcleanup']) {
    require_once("include/cleanup.php");
    sql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 's2slowcleantime'") or sqlerr(__FILE__, __LINE__);
    dos2slowcleanup();
    echo "<br><center><h1>Stage 2 Slow Cleanup Done</h1></center>";
}

if ($_POST['dooptimization']) {
    require_once("include/cleanup.php");
    sql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastoptimizedbtime'") or sqlerr(__FILE__, __LINE__);
    dooptimizedb();
    echo "<br><center><h1>Optimization Done</h1></center>";
}

if ($_POST['dobackupdb']) {
    require_once("include/cleanup.php");
    sql_query("UPDATE avps SET value_u = '$now' WHERE arg = 'lastbackupdbtime'") or sqlerr(__FILE__, __LINE__);
    dobackupdb();
    echo "<br><center><h1>Auto Back Up Done</h1></center>";
}

if ($_POST['doautohitrun']) {
    require_once("include/cleanup.php");
    sql_query("UPDATE avps SET value_u = " . sqlesc($now) . " WHERE arg = 'lastautohitruntime'") or sqlerr(__FILE__, __LINE__);
    doautohitrun();
    echo "<br><center><h1>Hit and run clean Done</h1></center>";
}
echo("Memory usage:" . memory_get_usage() . "<br /><br />");
end_main_frame();
stdfoot();

?>