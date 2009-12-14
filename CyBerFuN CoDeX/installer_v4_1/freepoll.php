<?php
require ("include/bittorrent.php");
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
$userid = 0 + $CURUSER["id"];
$torrentid = 0 + $_POST["torrentid"];

if ((!$torrentid))
    header("Location: browse.php");
else
    $checkfreepoll = mysql_query("SELECT userid FROM freepoll WHERE torrentid=" . unsafeChar($torrentid) . " AND userid=" . unsafeChar($userid) . "");
$trows = mysql_fetch_row($checkfreepoll);
if ($trows[0] > 0) {
    header("Location: details.php?id=$torrentid&poll=0");
} else {
    $res = mysql_query("INSERT INTO freepoll (torrentid, userid) VALUES (" . unsafeChar($torrentid) . ", " . unsafeChar($userid) . ")");
    header("Location: details.php?id=$torrentid");
}

?>
