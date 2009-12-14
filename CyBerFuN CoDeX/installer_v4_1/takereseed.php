<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

stdhead("Reseed request");

begin_main_frame();

$reseedid = 0 + $_GET["reseedid"];

$res = mysql_query("SELECT snatched.userid, snatched.torrentid, users.id FROM snatched inner join users on snatched.userid = users.id  AND snatched.torrentid = $reseedid") or sqlerr();
$pn_msg = "User " . $CURUSER["username"] . " asked for a reseed on torrent $BASEURL/details.php?id=" . $reseedid . " !\nThank You!";
while ($row = mysql_fetch_assoc($res)) {
    mysql_query("INSERT INTO messages (poster, sender, receiver, added, msg) VALUES(0, 0, $row[userid], '" . get_date_time() . "', " . sqlesc($pn_msg) . ")") or sqlerr(__FILE__, __LINE__);
}
print("<img src=" . $pic_base_url . "smilies/mad-grin.gif alt='it worked' title='WooHoo - success' style='margin-left: 4pt' /> Success pm's have been sent");
end_main_frame();
stdfoot();

?>