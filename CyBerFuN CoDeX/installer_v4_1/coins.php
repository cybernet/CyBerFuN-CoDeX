<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
// / Mod by dokty - tbdev.net
// //////updated gift array by Bigjoos
$id = 0 + $_GET["id"];
$points = 0 + $_GET["points"];
if (!is_valid_id($id) || !is_valid_id($points))
    die();

$pointscangive = array("10", "20", "50", "100", "200", "500", "1000");
if (!in_array($points, $pointscangive))
    stderr("Error", "You can't give that amount of points!!!");

$sdsa = sql_query("SELECT 1 FROM coins WHERE torrentid=" . sqlesc($id) . " AND userid =" . sqlesc($CURUSER["id"])) or die();
$asdd = mysql_fetch_array($sdsa);
if ($asdd)
    stderr("Error", "You already gave points to this torrent.");

$res = sql_query("SELECT owner,name FROM torrents WHERE id = " . sqlesc($id)) or die();
$row = mysql_fetch_assoc($res) or stderr("Error", "Torrent was not found");
$userid = $row["owner"];

if ($userid == $CURUSER["id"])
    stderr("Error", "You can't give your self points!");

if ($CURUSER["seedbonus"] < $points)
    stderr("Error", "You dont have enough points");

sql_query("INSERT INTO coins (userid, torrentid, points) VALUES (" . sqlesc($CURUSER["id"]) . ", " . sqlesc($id) . ", " . sqlesc($points) . ")") or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE users SET seedbonus=seedbonus+" . $points . " WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE users SET seedbonus=seedbonus-" . $points . " WHERE id=" . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE torrents SET points=points+" . $points . " WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$msg = sqlesc("You have been given " . $points . " points by " . $CURUSER["username"] . " for torrent [url=" . $BASEURL . "/details.php?id=" . $id . "]" . $row["name"] . "[/url].");
sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES(0, $userid, $msg, " . sqlesc(get_date_time()) . ", 'You have been given a gift')") or sqlerr(__FILE__, __LINE__);
stderr("Done", "Successfully gave points to this torrent.");

?>
