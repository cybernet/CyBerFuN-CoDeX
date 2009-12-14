<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
// Change the following lines to reflect your own server attributes
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

$query = sprintf('UPDATE users SET curr_ann_id = 0, curr_ann_last_check = \'0000-00-00 00:00:00\' ' . 'WHERE id = %s AND curr_ann_id != 0',
    unsafechar($CURUSER['id']));

mysql_query($query);

header("Location: $DEFAULTBASEURL/index.php");

?>