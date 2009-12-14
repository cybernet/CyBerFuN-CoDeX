<?php
include ('include/bittorrent.php');
include ('include/user_functions.php');
require_once ("cacher.php");
dbconn(true);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Cache Sheets - Nosey Cunt !");

$fileinformation = array ('table' => 'stylesheets',
    'arrayname' => 'stylesheets',
    'filename' => 'stylesheets'
    );

query_wphpfile ($fileinformation);

?>