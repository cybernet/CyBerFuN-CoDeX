<?php
//////////Moddified Snatched Torrents By Bigjoos//////////////
include("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR)
stderr("Sorry", "No Permissions.");
function get_snatched_color($st)
{
$secs = $st;
$mins = floor($st / 60);
$hours = floor($mins / 60);
$days = floor($hours / 24);
$week = floor($days / 7);
$month = floor($week / 4);
if ($month > 0) {
$week_elapsed = floor(($st - ($month * 4 * 7 * 24 * 60 * 60)) / (7 * 24 * 60 * 60));
$days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=lime><b>$month months.<br>$week_elapsed W. $days_elapsed D.</b></font>";
}
if ($week > 0) {
$days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=lime><b>$week W. $days_elapsed D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($days > 2) {
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=lime><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($days > 1) {
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=green><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($days > 0) {
$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=#CCFFCC><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($hours > 12) {
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=yellow><b>$hours:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($hours > 0) {
$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
$secs_elapsed = floor($st - $mins * 60);
return "<font color=red><b>$hours:$mins_elapsed:$secs_elapsed</b></font>";
}
if ($mins > 0) {
$secs_elapsed = floor($st - $mins * 60);
return "<font color=red><b>0:$mins:$secs_elapsed</b></font>";
}
if ($secs > 0) {
return "<font color=red><b>0:0:$secs</b></font>";
}
return "<font color=red><b>None<br>Reported</b></font>";
}
stdhead("Modified Snatched Torrents");
$count1 = number_format(get_row_count("snatched", "WHERE complete_date != '0000-00-00 00:00:00'"));
print("<h2 align=center>All snatched Torrents</h2>");
print("<center><font class=small>We crrently have $count1 snatched Torrents</font></center>");
begin_main_frame();
$res1 = mysql_query("SELECT COUNT(*) FROM snatched $limit") or sqlerr();
$row1 = mysql_fetch_array($res1);
$count = $row1[0];
$snatchedperpage = 50;
list($pagertop, $pagerbottom, $limit) = pager($snatchedperpage, $count, "snatched_torrents.php?");
print("$pagertop");
$sql = "SELECT sn.userid, sn.torrentid, sn.timesann, sn.hit_and_run, sn.mark_of_cain, sn.uploaded, sn.downloaded, sn.start_date, sn.complete_date, sn.seeder, sn.leechtime, sn.seedtime, u.username, t.name ".
"FROM snatched AS sn ".
"LEFT JOIN users AS u ON u.id=sn.userid ".
"LEFT JOIN torrents AS t ON t.id=sn.torrentid WHERE complete_date != '0000-00-00 00:00:00'".
"ORDER BY sn.complete_date DESC $limit";
$result = mysql_query($sql) or print(mysql_error());
if( mysql_num_rows($result) != 0 ) {
?>
<table width=100% border=1 cellspacing=0 cellpadding=5 align=center>
<tr>
<td class=tabletorrent align=center width="1%">Name</td>
<td class=tabletorrent align=center width="1%">Torrent Name</td>
<td class=tabletorrent align=center width="1%">HitnRun</td>
<td class=tabletorrent align=center width="1%">Marked</td>
<td class=tabletorrent align=center width="1%">Announced</td>
<td class=tabletorrent align=center width="1%">Upload</td>
<td class=tabletorrent align=center width="1%">Download</td>
<td class=tabletorrent align=center width="1%">Seedtime</td>
<td class=tabletorrent align=center width="1%">leechtime</td>
<td class=tabletorrent align=center width="1%">Start Date</td>
<td class=tabletorrent align=center width="1%">End Date</td>
<td class=tabletorrent align=center width="1%">Seeding</td>
<td class=tabletorrent align=center width="1%">Delete</td>
</tr>
<?php
while($row = mysql_fetch_assoc($result)) {
echo '<tr>'.
'<td><a href="/userdetails.php?id=' . $row['userid'] . '"><b>' . $row['username'] . '</b></a></td>';
$smallname =substr(safechar($row["name"]) , 0, 25);
if ($smallname != safechar($row["name"])) {
$smallname .= '...';
}
echo '<td align=center><a href="/details.php?id=' . $row['torrentid'] . '"><b>' . $smallname . '</b></a></td>',
'<td align=center><b>' . ($row['hit_and_run']) . '</b></td>'.
'<td align=center><b>' . ($row['mark_of_cain']) . '</b></td>'.
'<td align=center><b>' . ($row['timesann']) . '</b></td>'.
'<td align=center><b>' . prefixed($row['uploaded']) . '</b></td>'.
'<td align=center><b>' . prefixed($row['downloaded']) . '</b></td>'.
'<td align=center><b>'.get_snatched_color($row["seedtime"]). '</b></td>'.
'<td align=center>' . mkprettytime($row["leechtime"]) . '</td>'.
'<td align=center><nobr><b>' . $row['start_date'] . '</b></td>';
if ($row['complete_date'] > 0)
echo '<td align=center><nobr><b>' . "" . get_elapsed_time(sql_timestamp_to_unix_timestamp($row[complete_date])) . " ago" . '</b></td>';
else
echo '<td align=center><nobr><b><font color=red>Not Completed</font></b></td>';
echo '<td align=center><b>'.($row['seeder'] == 'yes' ? "<img src=/pic/online.gif>" : "<img src=/pic/offline.gif>") . '</b></td>'.
'</td>';
//}
if (get_user_class() >= UC_SYSOP)
{
print("<td align=center><font size=\"-2\">[<a class=altlink href=delsnatch.php?action=delete&id=" . $row['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "><b>Delete</b></a>]</font></td></tr>");
}
else
print("<td align=center><b><font size=\"-2\">[Not Allowed]</font></b></td></tr>");
}
print '</table>';
}
else
print 'Nothing here :(';
print("$pagerbottom");
end_main_frame();
stdfoot();
?>