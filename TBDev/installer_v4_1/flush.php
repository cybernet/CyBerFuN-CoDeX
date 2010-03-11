<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
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
    stderr("Permission Denied", "Only Staff can flush ghost torrents. Contact a Moderator if you wish to have yours cleared.");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!is_valid_id($id))
    stderr("Error", "Invalid ID.");

if (get_user_class() >= UC_MODERATOR || $CURUSER['id'] == $id) {
    $deadtime = deadtime();
    $dt = gmtime();
    $dt = sqlesc(get_date_time($dt));
    $res = sql_query("SELECT username FROM users WHERE id= $id") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    $username = $arr['username'];

    sql_query("DELETE FROM peers WHERE userid=" . $id);
    $effected = mysql_affected_rows();
    // === write to flush log
    write_flush_log("User " . $username . " just flushed torrents at " . $dt . ". $effected torrents where sucessfully cleaned.");

    stderr('Success', "$effected ghost torrent" . ($effected ? 's' : '') . 'where sucessfully cleaned. You may now restart your torrents, The tracker has been updated, and your ghost torrents where sucessfully flushed. please remember to put the seat down.');
} else
    stderr("Error", "You can only clean your own ghost torrents.");

?>