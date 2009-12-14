<?php
// ////////resetratio updated by Bigjoos///////////////////////////
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
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
if (get_user_class() < UC_SYSOP)
    hacker_dork("Reset Ratio - Nosey Cunt !");
stdhead("Torrent Reset");
begin_table();
?>
<?php
?>
<tr>
<td class="colhead" align="center"><font size=\"1\">Username</font></td>
<td class="colhead" align="center"><font size=\"1\">Site DL Total</font></td>
<td class="colhead" align="center"><font size=\"1\">Torrent DL Total</font></td>
<td class="colhead" align="center"><font size=\"1\">New DL Total</font></td>
</tr>
<?php
if (isset($_GET["torrentid"])) {

$torrentid = (int) $_GET["torrentid"];
$query = "SELECT * FROM snatched WHERE torrentid =".sqlesc($torrentid)."" or sqlerr(__FILE__, __LINE__);
$result = sql_query($query);
$num = mysql_num_rows($result);
$zerofix = $num - 1;
$i = 0;
while ($i <= $zerofix) {
$userid = mysql_result($result, $i, "userid");

$res = sql_query("SELECT * FROM users WHERE id =".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_array($res);

$res2 = sql_query("SELECT * FROM snatched WHERE torrentid =".sqlesc($torrentid)." and userid=".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
$arr2 = mysql_fetch_array($res2);
$arr4 = sql_query("SELECT name FROM torrents WHERE id =".sqlesc($torrentid)."") or sqlerr(__FILE__, __LINE__);
$fetched_result4 = mysql_fetch_array($arr4);
$torrentname = $fetched_result4['name'];
$userdownloaded = $arr["downloaded"];
$downloaded = $arr2['downloaded'];

$newdownloaded = ($userdownloaded - $downloaded);
$mkdown = prefixed($downloaded);
$mkuserdown = prefixed($userdownloaded);
$mknewdown = prefixed($newdownloaded);
$username = $arr['username'];
$added = sqlesc(get_date_time());
if ($username) {
$msg = sqlesc("Dear $username,\n
Since we encountered a problem with torrent : $torrentname\nwe decided to give back the data that you downloaded.\n
You keep the data that you uploaded.\n
Your total site download was $mkuserdown and has been reduced by $mkdown.\n
Your New Site total is $mknewdown.\n
The torrent will be replaced with a correct version as soon as possible.\n
We hope to have been of service.\n
Kind Regards,\nThe $SITENAME Team");
$subject = sqlesc("Torrent Ratio Reset");
sql_query("UPDATE users SET downloaded='$newdownloaded' WHERE id='$userid'");
sql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES('0','$userid', $added, $msg,'0',$subject)") or sqlerr(__FILE__, __LINE__);
sql_query("DELETE FROM snatched WHERE torrentid = $torrentid");
write_log("Torrent $torrentname was deleted by NUKE Script and users Re-Paid Download");
begin_main_frame();
?>
<td colspan="4" style="text-align:center;"><?php echo $username; ?></td>
<td><?php echo $mkuserdown; ?></td>
<td><?php echo $mkdown; ?></td>
<td><?php echo $mknewdown; ?></td>
</tr>
<?php
}
$i++;
end_main_frame();
}
}
elseif (isset($_GET["torrent"])) {
$torrent = (int) $_GET["torrent"];
$res = sql_query("SELECT * FROM torrents WHERE id =".sqlesc($torrent)."") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_array($res);
$name = $arr['name'];

print("<form method=GET action='resetratio.php'><table border=0 width=500 id=table1><tr><td colspan=2 align=center><input type=hidden name=torrentid value=$torrent><br>Check the data you entered!!<br>If you don't know what this page is for, go back !!!<br><br></td></tr><tr><td width=30% align=right>Torrent ID :</td><td>$torrent</td></tr><tr><td width=30% align=right>Torrent name :</td><td>$name</td></tr><tr><td width=30% align=left><br><a href='resetratio.php'><input type=button value='No !' name=R></a><br><br></td><td align=right><br><input type=submit value='Yes !' name=S><br><br></td></tr></table></form>");

}
else {
?>
<form method="GET" action="resetratio.php">
<table border="0" width="350" id="table1">
<tr>
<td colspan="2"><br>Insert the torrentID of which you want to reset the data .<br>
If you don't know what this page does, do't use it!!!<br><br></td>
</tr>
<tr>
<td width="25%" align="right">Torrent ID :</td>
<td width="40%"><input type="text" name="torrent" size="20"></td>
</tr>
<tr>
<td width="25%"> </td>
<td width="40%">
<input type="submit" value="Next Step" name="S"></td>
</tr>
</table>
</form>
<?
}
stdfoot();
?>