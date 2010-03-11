<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
stdhead("Upload");

if ($usergroups['canupload'] == 'no' OR $usergroups['canupload'] != 'yes' OR $CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no'){
stdmsg("Sorry...", "You are not authorized to upload torrents.  (See <a href=\"faq.php#up\">Uploading</a> in the FAQ.)", false);
stdfoot();
exit;
}

?>
<div align=Center>
<form name=upload enctype="multipart/form-data" action="takeupload2.php" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_torrent_size?>" />
<p>The tracker's announce url is <b><?= $announce_urls[0] ?></b></p>
<p><b>Only upload torrents you're going to seed!</b> Uploaded torrents won't be visible on the main page until you start seeding them.</p>
<p><b>Your torrent will automatically download once you press submit !</b></p>
<?
echo'<table class=message cellspacing=0 cellpadding=5>';


//==== offer dropdown for offer mod
$res = mysql_query("SELECT id, name, allowed FROM offers WHERE userid = $CURUSER[id] ORDER BY name ASC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0) {
$offer = "<select name=offer><option value=0>Your Offers</option>";
while($row = mysql_fetch_array($res)) {
if ($row['allowed'] == 'allowed')
$offer .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>";
}
$offer .= "</select>";
tr("Offer", $offer."<br> If you are uploading one of your offers please select it here so the voters will be notified." , 1);
}



tr("Torrent file", "<input type=file name=file size=80>\n", 1);
echo'<tr><td class=rowhead>'.
'<table align=right><tr><td class=rowhead><b>Non-Audio file</b></td></tr>'.
'<tr><td class=rowhead><input name="filetype" type="radio" value="2" checked></td></tr>'.
'</table></td>'.
'<td align=center>'.
'<table align=center cellpadding=5><tr>'.
'<td class=embedded>Torrent name </td><td class=embedded><input type="text" name="name" size="80" /><br>(Taken from filename if not specified. <b>Please use descriptive names.</b>)</td>'.
'</tr></table><br>'.
'<table align=center><tr>'.
'<td class=embedded> NFO file </td><td class=embedded><input type="file" name="nfo" size="60"></td>'.
'</tr></table>'.
'</td></tr>'.
'<tr><td class=rowhead>'.
'<table align=right><tr><td class=rowhead><b>Audio file</b></td></tr>'.
'<tr><td class=rowhead><input name="filetype" type="radio" value="1" ></td></tr>'.
'</table></td>'.
'<td align=center>'.
'<table align=center cellpadding=5><tr>'.
'<td class=embedded>Artist </td><td class=embedded><input type="text" size="40" name="artist"></td>'.
'<td class=embedded> Album </td><td class=embedded><input type="text" size="40" name="album"></td>'.
'</tr></table><br>'.
'<table align=center cellpadding=5><tr>'.
'<td class=embedded>Year </td><td class=embedded><input type="text" size="20" name="year"></td>'.
'<td class=embedded> Format </td><td class=embedded><input type="text" size="20" name="format"></td>'.
'<td class=embedded> Bitrate </td><td class=embedded><input type="text" size="10" name="bitrate"></td>'.
'</tr></table><br>'.
'<table align=center><tr>'.
'<td class=embedded> NFO file </td><td class=embedded><input type="file" name="nfo2" size=60><br></td>'.
'</tr></table>'.
'</td></tr>'.
'<tr><td class=rowhead>Description</td><td>'.
'<textarea name="descr" cols="60" rows="5"></textarea></td></tr>';
tr("Show uploader", "<input type=checkbox name=uplver value=yes>Don't show my username in 'Uploaded By' field in browse.", 1);
tr("Strip ASCII", "<input type=checkbox name=strip value=strip unchecked />   <a href=\"http://en.wikipedia.org/wiki/ASCII_art\" target=\"_blank\">what is this ?</a>", 1);
$s = "<select name=\"type\">\n<option value=\"0\">(choose one)</option>\n";

$cats = genrelist();
foreach ($cats as $row)
$s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";

$s .= "</select>\n";
tr("Type", $s, 1);

echo'<tr><td align="center" colspan="2"><input type="submit" class=btn value="Do it!" /></td></tr></table></form>';

stdfoot();
?>