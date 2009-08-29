<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Hack-Log - Nosey Cunt !");

stdhead("Xss Hack Log");
echo"<h2>Xss Hack Log</h2>";
echo"<table width=70% border=0 cellspacing=0 cellpadding=5 align=center><td class=colhead align=right>Line</td><td class=colhead align=center>Error</td></tr>";
$lines = file("logs/hacklog.txt");
foreach ($lines as $line_num => $line) {
    echo"<tr bgcolor=" . ($line_num % 2 == 0 ? "green" : "gray") . "><td align=right>#<b>{$line_num}</b></td><td align=left>" . safeChar($line) . "</td></tr>";
}
echo"</table>";
stdfoot();

?>
