<?php
require ("include/bittorrent.php");
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
if (get_user_class() < UC_SYSOP)
    hacker_dork("Site-Check - Nosey Cunt !");

stdhead();
begin_frame("Tracker Stats");
print("<br /><br />\n<b>Tracker Info</b><br>\n");
// check torrents' folder
if (file_exists($torrent_dir)) {
    if (is_writable($torrent_dir))
        print("<br />\nTorrent's folder $torrent_dir <span style=\"color:blue; font-weight: bold;\">ok</span><br />\n");
    else
        print("<br />\nTorrent's folder $torrent_dir is <span style=\"color:#FF0000; font-weight: bold;\">ERROR</span><br />\n");
} else
    print("<br />\nTorrent's folder $torrent_dir <span style=\"color:#FF0000; font-weight: bold;\">ERROR NOT FOUND!</span><br />\n");

if (file_exists("include/bittorrent.php")) {
    if (is_writable("include/bittorrent.php.php"))
        print("<br />\ninclude/bittorrent.php is <span style=\"color:#FF0000; font-weight: bold;\">ERROR, change cmos 644</span> (can write tracker's configuration change)<br />\n");

    else
        print("<br />\ninclude/bittorrent.php <span style=\"color:blue; font-weight: bold;\">ok</span><br />\n");
} else // never go here, if not exist got error before...
    print("<br />\nconfig.php file <span style=\"color:#FF0000; font-weight: bold;\">ERROR NOT FOUND!</span><br />\n");

print("<br /><br />\n<b>Database Info</b><br>\n");
print("<br />\n<table border=\"0\">\n<tr><td>PHP version:</td><td>" . phpversion() . "</td></tr>");
$sqlver = mysql_fetch_row(mysql_query("SELECT VERSION()"));
print("\n<tr><td>MYSQL version:</td><td>$sqlver[0]</td></tr>");
$sqlver = mysql_stat();
$sqlver = explode(' ', $sqlver);
print("\n<tr><td valign=\"top\" rowspan=\"" . (count($sqlver) + 1) . "\">MYSQL stats : </td>\n");
for ($i = 0;$i < count($sqlver);$i++)
print(($i == 0?"":"<tr>") . "<td>$sqlver[$i]</td></tr>\n");
print("\n</table><br />\n</div>");
print("<br />");
end_frame();
stdfoot();

?>