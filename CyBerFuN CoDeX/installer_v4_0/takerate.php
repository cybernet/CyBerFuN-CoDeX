<?php
require_once("include/bittorrent.php");
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
function bark($msg)
{
    genbark($msg, "Rating failed!");
}

if (!isset($CURUSER))
    bark("Must be logged in to vote");
// === topic ratings
if (isset($_GET["rate_me"])) {
    if ($_GET["rate_me"])
        $rate_me = (int)$_GET['rate_me'];
    if ($rate_me <= 0 || $rate_me > 5)
        bark("invalid rating number");

    $topic_id = (int)$_GET['topic_id'];
    if (!is_valid_id($topic_id))
        stderr("Error", "invalid topic id!");

    $res = mysql_query("SELECT topic, user FROM ratings WHERE topic =" . unsafeChar($topic_id) . " AND user =" . unsafeChar($CURUSER["id"]) . "");
    $row = mysql_fetch_array($res);
    if ($row["topic"] >= 1)
        bark("You have already rated this topic.");
    if ($row["topic"] == 0)
        $res = sql_query("UPDATE ratings SET rating = $rate_me WHERE topic =" . unsafeChar($topic_id) . " AND user =" . unsafeChar($CURUSER["id"]) . "");
    if (!$row)
        $res = sql_query("INSERT INTO ratings (topic, user, rating, added) VALUES (" . unsafeChar($topic_id) . ", " . unsafeChar($CURUSER["id"]) . ", $rate_me, NOW())");
    sql_query("UPDATE topics SET numratings = numratings + 1, ratingsum = ratingsum + $rate_me WHERE id = " . unsafeChar($topic_id) . "");
    // ===add karma
    sql_query("UPDATE users SET seedbonus = seedbonus+5.0 WHERE id =" . unsafeChar($CURUSER["id"]) . "") or sqlerr(__FILE__, __LINE__);
    // ===end
    $refererto = str_replace ('&amp;', '&', safeChar($_SERVER["HTTP_REFERER"]));
    $referer = ($_SERVER["HTTP_REFERER"] ? $refererto : "/forums.php?action=viewtopic&topicid=$topic_id");
    header("Refresh: 0; url=$referer");
    die;
}

if (!mkglobal("rating:id"))
    bark("missing form data");

$id = 0 + $id;
if (!$id)
    bark("invalid id");

$rating = 0 + $rating;
if ($rating <= 0 || $rating > 5)
    bark("invalid rating");

$res = sql_query("SELECT owner FROM torrents WHERE id = " . unsafeChar($id) . "");
$row = mysql_fetch_array($res);
if (!$row)
    bark("no such torrent");
// if ($row["owner"] == $CURUSER["id"])
// bark("You can't vote on your own torrents.");
$res = sql_query("INSERT INTO ratings (torrent, user, rating, added) VALUES ($id, " . unsafeChar($CURUSER["id"]) . ", $rating, NOW())");
if (!$res) {
    if (mysql_errno() == 1062)
        bark("You have already rated this torrent.");
    else
        bark(mysql_error());
}

sql_query("UPDATE torrents SET numratings = numratings + 1, ratingsum = ratingsum + $rating WHERE id = " . unsafeChar($id) . "");
// ===add karma
sql_query("UPDATE users SET seedbonus = seedbonus+5.0 WHERE id = " . unsafeChar($CURUSER["id"]) . "") or sqlerr(__FILE__, __LINE__);
// ===end
header("Refresh: 0; url=details.php?id=$id&rated=1");

?>