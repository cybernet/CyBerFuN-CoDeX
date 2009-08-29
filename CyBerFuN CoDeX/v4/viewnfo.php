<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

$id = 0 + $_GET["id"];
if (get_user_class() < UC_POWER_USER || !is_valid_id($id))
  die;

$r = mysql_query("SELECT name,nfo FROM torrents WHERE id=$id") or sqlerr();
$a = mysql_fetch_assoc($r) or die("Puke");
$nfo = htmlspecialchars($a["nfo"]);
stdhead();
print("<h1>NFO for <a href=details.php?id=$id>$a[name]</a></h1>\n");
print("<table border=1 cellspacing=0 cellpadding=5><tr><td class=text>\n");
print("<pre><font face='MS Linedraw' size=2 style='font-size: 10pt; line-height: 10pt'>" . format_urls($nfo) . "</font></pre>\n");print("</td></tr></table>\n");
print("<p align=center>For best visual result, install the " .
  "<a href=ftp://$_SERVER[HTTP_HOST]/misc/linedraw.ttf>MS Linedraw</a> font!</p>\n");
stdfoot();
?>