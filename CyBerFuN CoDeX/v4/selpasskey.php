<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();	
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_ADMINISTRATOR)
hacker_dork("Passkey Checker - Nosey Cunt !");
stdhead();
function hex2bin($hexdata) {

   for ($i=0;$i<strlen($hexdata);$i+=2) {
     $bindata.=chr(hexdec(substr($hexdata,$i,2)));
   }

   return $bindata;
}

$res = mysql_query("SELECT * FROM `users` WHERE `passkey`=".sqlesc(hex2bin($_GET["passkey"])));
if (mysql_num_rows($res)) {
    $udata = mysql_fetch_assoc($res);
    echo "User ".$udata["username"]." <a href=\"userdetails.php?id=".$udata["id"]."\">( ".$udata["id"]." )</a>";
} else
    echo "Nothing found :(";

stdfoot();
?>
