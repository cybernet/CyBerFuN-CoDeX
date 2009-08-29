<?php
require_once("include/bittorrent.php");
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
parked();

if (get_user_class() < UC_MODERATOR)
    hacker_dork("Flush Log - Nosey Cunt !");
// delete items older than a month
$secs = 96 * 60 * 60;
stdhead("Flush log");
sql_query("DELETE FROM flush_log WHERE " . gmtime() . " - UNIX_TIMESTAMP(added) > $secs") or sqlerr(__FILE__, __LINE__);
$res = sql_query("SELECT added, txt FROM flush_log ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);
echo'<h1>Flushed log</h1>';
if (mysql_num_rows($res) == 0)
    echo'<b>Nobody Flushed</b>';
else {
    echo'<table border=1 cellspacing=0 cellpadding=5><tr><td class=colhead2 align=left>Date</td><td class=colhead2 align=left>Time</td><td class=colhead2 align=left>Who Flushed</td></tr>';
    while ($arr = mysql_fetch_assoc($res)) {
        // =======change colors
        $count = (++$count) % 2;
        $class = 'clearalt' . ($count == 0?'6':'7');

        $date = substr($arr['added'], 0, strpos($arr['added'], " "));
        $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
        echo"<tr><td class=$class>$date</td><td class=$class>$time</td><td align=left class=$class>$arr[txt]</td></tr>\n";
    }
    echo'</table>';
}
echo'<p>Times are in GMT.</p>';
stdfoot();
die;

?>