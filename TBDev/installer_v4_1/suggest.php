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
if (strlen($_GET['q']) > 3) {
    header("Content-Type: text/html; charset=iso-8859-1");
    $q = str_replace(" ",".",sqlesc("%".$_GET['q']."%"));
    $q2 = str_replace("."," ",sqlesc("%".$_GET['q']."%"));
    $result = mysql_query("SELECT name FROM torrents WHERE name LIKE {$q} OR name LIKE {$q2} ORDER BY id DESC LIMIT 0,10;");
    if (mysql_numrows($result) > 0) {
        for ($i = 0; $i < mysql_numrows($result); $i++) {
            $name = mysql_result($result,$i,"name");
   $name = trim(str_replace("\t","",$name));

               print $name;
            if ($i != mysql_numrows($result)-1) {
                print "\r\n";
            }
        }
    }
}

?>