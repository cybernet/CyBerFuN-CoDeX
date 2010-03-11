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
stdhead("Reset Shoutbox");
if (get_user_class() < UC_SYSOP) {
    print("Access Denied!");
    exit;
}
if (isset($_GET['yes']) && ($_GET['yes'] == 1)) {
    sql_query("DELETE FROM shoutbox") or sqlerr(__FILE__, __LINE__);
    sql_query("INSERT INTO shoutbox (userid, username, date, text) VALUES(2, 'System', UNIX_TIMESTAMP(NOW()), '" . unsafeChar($SITENAME) . ")");
    echo "Threads in Shoutbox have been erased!";
} else {
    echo "Are you sure to empty the shoutbox? <a href='resetshoutbox.php?yes=1'>yes</a>";
}
stdfoot();

?>
