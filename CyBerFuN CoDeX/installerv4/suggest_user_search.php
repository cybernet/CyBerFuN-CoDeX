<?php
require("include/bittorrent.php");
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
if (strlen($_GET['q']) > 2) {
	$q = str_replace(" ",".",sqlesc("%".$_GET['q']."%"));
	$q2 = str_replace("."," ",sqlesc("%".$_GET['q']."%"));
	$result = sql_query("SELECT username FROM users WHERE username LIKE {$q} OR username LIKE {$q2} ORDER BY username DESC LIMIT 0,10;");
	if (mysql_numrows($result) > 0) {
		for ($i = 0; $i < mysql_numrows($result); $i++) {
			$username = mysql_result($result,$i,"username");
			$username = trim(str_replace("\t","",$username));
			print $username;
			if ($i != mysql_numrows($result)-1) {
				print "\r\n";
			}
		}
	}
}
?>