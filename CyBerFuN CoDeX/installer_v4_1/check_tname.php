<?php
header("Content-type: text/html; charset=utf-8");
require_once("include/bittorrent.php");
dbconn(false);
maxcoder();

$torrent_nev = (trim($_GET['torrent_nev']));
$torrent_nev = "%$torrent_nev%";

$torrentt = mysql_query("SELECT `name` FROM `torrents` WHERE `name` LIKE " . sqlesc($torrent_nev) . "");
if(mysql_num_rows($torrentt)==0)
print "<br /><center><font color=red><b><h1>Nothing Found !</h1></b></font></center>";
else{
print "<br /><center><font color=red><b>Please check that the content is not the same thing! </b></font></center><br>";
print "<ul>";
while($torrentek = mysql_fetch_array($torrentt)){
print " <font color=green><li> " . $torrentek['name'] . "</font>";
}
print "</ul>";
print "<center><blink><font color=red><strong>There is a similarity in names !!!</strong></font></blink></center><br />";
}
?>