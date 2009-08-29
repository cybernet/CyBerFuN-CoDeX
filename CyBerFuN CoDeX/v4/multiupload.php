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
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
stdhead("Multi-Upload");
if ($CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no'){
    stdmsg("Sorry...", "You are not authorized to upload torrents.  (See <a href=\"faq.php#up\">Uploading</a> in the FAQ.)", false);
    stdfoot();
    exit;
}

?>
<div align=Center>
<form enctype="multipart/form-data" action="takemultiupload.php" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_torrent_size?>" />
<p>The tracker's announce url is <b><?= $announce_urls[0] ?></b></p>
<p><strong><font color=#FF0000>REMEMBER: You MUST add an NFO for all the torrents AND redownload all 5 .torrent file's!</font></strong></p>
<table border="1" cellspacing="0" cellpadding="20">
<?php
$cats = genrelist();
foreach ($cats as $row)
$s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";
$s .= "</select>\n";

print("<table class=message  cellspacing=0 cellpadding=5>\n");

tr("Torrent #1", " &nbsp;&nbsp;&nbsp;File: <input type=file accept=image/jpeg name=file1 size=50>\n <br> <br>&nbsp;&nbsp;&nbsp;NFO:<input type=file name=nfo1 size=50><br><br>&nbsp;&nbsp;Type:<select name=\"type1\">\n<option value=\"0\">(choose type)</option>$s <br><br> \n", 1);
print("</td></tr>\n");

tr("Torrent #2", " &nbsp;&nbsp;&nbsp;File: <input type=file name=file2 size=50>\n <br> <br>&nbsp;&nbsp;&nbsp;NFO:<input type=file name=nfo2 size=50><br><br>&nbsp;&nbsp;Type:<select name=\"type2\">\n<option value=\"0\">(choose type)</option>$s <br><br> \n", 1);
print("</td></tr>\n");

tr("Torrent #3", " &nbsp;&nbsp;&nbsp;File: <input type=file name=file3 size=50>\n <br> <br>&nbsp;&nbsp;&nbsp;NFO:<input type=file name=nfo3 size=50><br><br>&nbsp;&nbsp;Type:<select name=\"type3\">\n<option value=\"0\">(choose type)</option>$s <br><br> \n", 1);
print("</td></tr>\n");

tr("Torrent #4", " &nbsp;&nbsp;&nbsp;File: <input type=file name=file4 size=50>\n <br> <br>&nbsp;&nbsp;&nbsp;NFO:<input type=file name=nfo4 size=50><br><br>&nbsp;&nbsp;Type:<select name=\"type4\">\n<option value=\"0\">(choose type)</option>$s <br><br> \n", 1);
print("</td></tr>\n");

tr("Torrent #5", " &nbsp;&nbsp;&nbsp;File: <input type=file name=file5 size=50>\n <br> <br>&nbsp;&nbsp;&nbsp;NFO:<input type=file name=nfo5 size=50><br><br>&nbsp;&nbsp;Type:<select name=\"type5\">\n<option value=\"0\">(choose type)</option>$s <br><br> \n", 1);
print("</td></tr>\n");

print("<tr><td class=rowhead style='padding: 3px'>Settings</td><td>");
print("These settings will apply to all above torrents &nbsp;<br><br>Please note: Torrent names are taken from their .torrent filenames. <br>Use descriptive names in .torrent files<br> <br> If you missed to specify a torrent category type it will use the one from below <br>  <br><select name=\"alltype\">\n<option value=\"0\">(choose type)</option>\n $s <br/><input type=checkbox name=fromnfo>Take the description from its respective NFO <font color=red>(currently not supported)</font>");
print("<br/><input type=checkbox name=custom>Custom message <br/> <textarea name=description  rows=6 cols=60>See NFO</textarea> ");
print("</td></tr>\n");

?>
<tr><td align="center" colspan="2"><input type="submit" class=btn value="Do it!" /></td></tr>
</table>
</form>
<?php
stdfoot();

?>
