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
stdhead("Peerlist");

if (get_user_class() < UC_MODERATOR) {
stdmsg("Sorry", "No permissions.");
stdfoot();
exit;
}

$count1 = number_format(get_row_count("peers"));
echo("<h2 align=center>Peerlist</h2>");
echo("<center><font class=small>We have $count1 peers</font></center><br>");
echo("<table width=737 border=1 cellspacing=0 cellpadding=10><tr><td class=text align=center>\n");
$res4 = sql_query("SELECT COUNT(*) FROM peers $limit") or sqlerr();
$row4 = mysql_fetch_array($res4);
$count = $row4[0];
$peersperpage = 15;
list($pagertop, $pagerbottom, $limit) = pager($peersperpage, $count, "viewpeers.php?");
echo("$pagertop");
$sql = "SELECT * FROM peers ORDER BY started DESC $limit";
$result = mysql_query($sql);
if( mysql_num_rows($result) != 0 ) {
echo'<table width=737 border=1 cellspacing=0 cellpadding=5 align=center>';
echo'<tr>';
echo'<td class=colhead align=center>User</td>';
echo'<td class=colhead align=center>Torrent</td>';
echo'<td class=colhead align=center>IP</td>';
echo'<td class=colhead align=center>Port</td>';
echo'<td class=colhead align=center>Upl.</td>';
echo'<td class=colhead align=center>Downl.</td>';
echo'<td class=colhead align=center>Peer-ID</td>';
echo'<td class=colhead align=center>Conn.</td>';
echo'<td class=colhead align=center>Seeding</td>';
echo'<td class=colhead align=center>Started</td>';
echo'<td class=colhead align=center>Last<br>Action</td>';
echo'<td class=colhead align=center>Prev.<br>Action</td>';
echo'<td class=colhead align=center>Upload<br>Offset</td>';
echo'<td class=colhead align=center>Download<br>Offset</td>';
echo'<td class=colhead align=center>To<br>Go</td>';
echo'</tr>';
while($row = mysql_fetch_assoc($result)) {
$sql1 = "SELECT * FROM users WHERE id = ".unsafeChar($row[userid])."";
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
echo'<tr>';
echo'<td><a href="userdetails.php?id=' . safeChar($row['userid']) . '">' . safeChar($row1['username']) . '</a></td>';
$sql2 = "SELECT * FROM torrents WHERE id = ".unsafeChar($row[torrent])."";
$result2 = mysql_query($sql2);
while ($row2 = mysql_fetch_assoc($result2)) {
$smallname =substr(safeChar($row2["name"]) , 0, 20);
if ($smallname != safeChar($row2["name"])) {
$smallname .= '...';
}
#$smallname = safechar($row2["name"]);
echo'<td><a href="details.php?id=' . safeChar($row['torrent']) . '">' . $smallname . '</td>';
echo'<td align=center>' . $row['ip'] . '</td>';
echo'<td align=center>' .safeChar($row['port']) . '</td>';
if ($row['uploaded'] < $row['downloaded'])
echo'<td align=center><font color=red>' . safeChar(prefixed($row['uploaded'])) . '</font></td>';
else
if ($row['uploaded'] == '0')
echo'<td align=center>' . safeChar(prefixed($row['uploaded'])) . '</td>';
else
echo'<td align=center><font color=green>' . safeChar(prefixed($row['uploaded'])) . '</font></td>';
echo'<td align=center>' . safeChar(prefixed($row['downloaded'])) . '</td>';
echo'<td align=center>' . $row['peer_id'] . '</td>';
if ($row['connectable'] == 'yes')
echo'<td align=center><font color=green>' . safeChar($row['connectable']) . '</font></td>';
else
echo'<td align=center><font color=red>' . safeChar($row['connectable']) . '</font></td>';
if ($row['seeder'] == 'yes')
echo'<td align=center><font color=green>' . safeChar($row['seeder']) . '</font></td>';
else
echo'<td align=center><font color=red>' . safeChar($row['seeder']) . '</font></td>';
echo'<td align=center>' . safeChar($row['started']) . '</td>';
echo'<td align=center>' . safeChar($row['last_action']) . '</td>';
echo'<td align=center>' . safeChar($row['prev_action']) . '</td>';
echo'<td align=center>' . safeChar(prefixed($row['uploadoffset'])) . '</td>';
echo'<td align=center>' . safeChar(prefixed($row['downloadoffset'])) . '</td>';
echo'<td align=center>' . safeChar(prefixed($row['to_go'])) . '</td>';
echo'</tr>';
}
}
}
echo'</table>';
echo("$pagerbottom");
}
else {
echo'Nothing here sad.gif';
}
echo("</td></tr></table>\n");

stdfoot();
?>