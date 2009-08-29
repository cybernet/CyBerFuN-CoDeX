<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
// * Cleanup snatchlist by x0r TBDEV *//==//clean thanks created from clean snatchlist by x0r
// ==modified to clean old thank you's
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Clean thanks file");

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

stdhead("Cleanup old thank you's");
begin_main_frame();
begin_frame("Cleaned old thankyou's", false);

$sres = mysql_query("SELECT DISTINCT torid FROM thanks") or sqlerr(__FILE__, __LINE__);
while ($sarr = mysql_fetch_assoc($sres)) {
    $ures = mysql_query("SELECT id FROM torrents WHERE id = $sarr[torid]") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($ures) == 0)
        mysql_query("DELETE FROM thanks WHERE torid = $sarr[torid]") or sqlerr(__FILE__, __LINE__);
    @mysql_free_result($ures);
}
@mysql_free_result($sres);
write_log("thanks_table_cleaned", "Thanks Table Cleaned by " . $CURUSER["username"]);

print("Memory usage:" . memory_get_usage() . "<br /><br />");

end_frame();
end_main_frame();
stdfoot();

?>
