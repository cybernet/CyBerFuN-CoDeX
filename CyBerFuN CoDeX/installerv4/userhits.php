<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
$id = 0 + $_GET["id"];

if (!is_valid_id($id) || $CURUSER["id"] <> $id && get_user_class() < UC_MODERATOR)
    $id = $CURUSER["id"];

$res = mysql_query("SELECT COUNT(*) FROM userhits WHERE hitid = ".unsafeChar($id)."") or sqlerr();
$row = mysql_fetch_row($res);
$count = $row[0];
$perpage = 100;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "?id=$id&");

if (!$count)
    stderr("No views", "This user has had no profile views yet.");

$res = mysql_query("SELECT username FROM users WHERE id = ".unsafeChar($id)."") or sqlerr(); // remove 'hits' if you do NOT use the cleanup code
$user = mysql_fetch_assoc($res);

stdhead("Profile views of ".safeChar($user['username'])."");
print("<h1>Profile views of <a href=\"userdetails.php?id=$id\">".safeChar($user['username'])."</a></h1>\n");
print("<h2>In total ".safeChar($count)." views</h2>\n"); // replace $user[hits] with $count if you do NOT use the cleanup code
if ($count > $perpage)
    print("$pagertop");
print("<table border=0 cellspacing=0 cellpadding=5>\n");
print("<tr><td class=colhead>Nr.</td><td class=colhead>Username</td><td class=colhead>Viewed at</td></tr>\n");

$res = mysql_query("SELECT uh.*, username, users.id as uid FROM userhits uh LEFT JOIN users ON uh.userid = users.id WHERE hitid =".unsafeChar($id)." ORDER BY uh.id DESC") or sqlerr();
while ($arr = mysql_fetch_assoc($res))
print("<tr><td>" . number_format($arr["number"]) . "</td><td><b><a href=\"userdetails.php?id=$arr[uid]\">".safeChar($arr['username'])."</a></b></td><td>".safeChar($arr['added'])."</td></tr>\n");

print("</table>\n");
if ($count > $perpage)
    print("$pagerbottom");
stdfoot();

?>