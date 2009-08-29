<?php
require_once('include/bittorrent.php');
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR)
hacker_dork("Mass-Free - Nosey Cunt !");
    
    stdhead("Show all torrent(s) CountStats status");
?>
<?php
if(isset($_GET['setall'])) {
	$sql = "UPDATE torrents SET countstats = 'no'";
	$update = mysql_query($sql) or die('Error!...');
}
if(isset($_GET['unsetall'])) {
	$sql = "UPDATE torrents SET countstats = 'yes'";
	$update = mysql_query($sql) or die('Error!...');
}
if(isset($_GET['update'])) {
	if(isset($_POST['countstats'])) {
		foreach($_POST['countstats'] as $item) {
			if($item !== null) {
				$sql = "UPDATE torrents SET countstats = 'no' WHERE id='{$item}'";
				$update = mysql_query($sql) or die('Error!...');
			}
		}
	}
	if(isset($_POST['countstats'])) {
		foreach($_POST['countstats'] as $item) {
			if($item !== null) {
				$sql = "UPDATE torrents SET countstats = 'yes' WHERE id='{$item}'";
				$update = mysql_query($sql) or die('Error!...');
			}
		}
	}
}
$count1 = number_format(get_row_count("torrents"));
print("<h2 align=center>Full Torrent List</h2>");
print("<center><font class=small>We currently have " . SafeChar($count1) . " Torrents</font></center>");
begin_main_frame("Full site torrent list",true,5);
$res1 = mysql_query("SELECT COUNT(*) FROM torrents $limit") or sqlerr();
$row1 = mysql_fetch_array($res1);
$count = $row1[0];
$torrentsperpage = 30;
list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "massfree.php?");
print("$pagertop");
$sql = "SELECT countstats, name FROM torrents $limit";
$result = mysql_query($sql) or die('Nothing found..');
if( mysql_num_rows($result) != 0 )
{
?>
<center>
<?
print'<form action="?update" method=post>';
print'<table width=600 border=1 cellspacing=0 cellpadding=5 align=center>';
print'<tr>';
print'<td class=colhead align=center>Count Stats</td>';
print'<td class=colhead align=center>Torrent Name</td>';
print'</tr>';

while( $row = mysql_fetch_assoc($result) )
{
print'<tr>';
if($row['countstats'] == 'yes') print'<td><span style="color:#00FF00;">Yes</span></td>';
else 
print'<td><span style="color:#FF0000;">No</span></td>';

print'<td><a href="details.php?id=' . $row['id'] . '&hit=1">' . $row['name'] . '</a></td>';
print'</tr>';
}
print'<tr>';
print'<td class=colhead align=center colspan=2>Freeleech Actions</td>';
print'<td class=colhead align=center><a href="?setall">Countstats Yes?</a></td>';
print'<td class=colhead align=center><a href="?unsetall">Countstats No?</a></td>';
print'</tr>';
print '</table>';
print("$pagerbottom");
?>
</form>
</center>
<?
}
else
{
print '=)';

}
print("</td></tr></table>\n");
stdfoot(); 
?>