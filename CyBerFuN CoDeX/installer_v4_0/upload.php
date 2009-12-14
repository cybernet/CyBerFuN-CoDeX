<?php
$page_find = 'upload';
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
stdhead("Upload");
//== Anti Flood
if (get_user_class() < UC_POWER_USER)  
{
$minutes = 30;
  $limit = 5;
  $res = mysql_query("SELECT COUNT(*) FROM torrents WHERE owner = $CURUSER[id] AND added > '".get_date_time(gmtime() - ($minutes * 60))."'") or sqlerr(__FILE__,__LINE__);
  $row = mysql_fetch_row($res);

  if ($row[0] > $limit)
    stderr("Flood", "You can't upload more than $limit torrents in $minutes minutes.");

}
if ($CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no'){
    stdmsg("Sorry...", "You are not authorized to upload torrents.  (See <a href=\"faq.php#up\">Uploading</a> in the FAQ.)", false);
    stdfoot();
    exit;
}
echo '<br />';
begin_main_frame();
begin_frame();

?>
<script type="text/javascript">
<!--
function enableb(){
        document.getElementById("doit").disabled = false;
}
function disableme() {
        document.getElementById("doit").disabled = true;
}
 -->
</script>

<div style="margin-top:-15px;margin-left:-10px;margin-right:-10px;text-align:center;" >
<h2><font size="+1" color="#CA0C26"><b><?php echo $language['search'];?></b></font></h2>
<br />
<form method=get action=browse.php target="_blank">
<input type="hidden" value=1 name="incldead" />
<INPUT name=search class=searchbox size=40 />
<input type=submit class=btn value='Search' onclick="enableb()"/>
</form>
</div>
<?php
end_frame();
end_main_frame();

?>
<br /><br />
<hr />
<form name=upload method=post action=takeupload.php enctype=multipart/form-data>
<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_torrent_size?>" />
<p><?php echo $language['annurl'];?> <b><?= $announce_urls[0] ?></b></p>
<table border="1" cellspacing="0" cellpadding="10">
<?php
// ==== offer dropdown for offer mod
$res = sql_query("SELECT id, name, allowed FROM offers WHERE userid = $CURUSER[id] ORDER BY name ASC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0) {
    $offer = "<select name=offer><option value=0>Your Offers</option>";
    while ($row = mysql_fetch_array($res)) {
        if ($row['allowed'] == 'allowed')
            $offer .= "<option value=\"" . $row["id"] . "\">" . safeChar($row["name"]) . "</option>";
    }
    $offer .= "</select>";
    tr("".$LANGUAGE['off']."", $offer . "<br/>".$language['off1']."" , 1);
}
tr("".$language['mus']."", ''.$language['mus1'].'', 1);
if (get_user_class() >= UC_POWER_USER) {
tr("".$language['mul']."", ''.$language['mul1'].'', 1);
}
tr("".$language['imdb']."", "<input type=text name=url size=80 /><br />".$language['imdb1']."\n", 1);
tr("".$language['post']."", "<input type=text value=$DEFAULTBASEURL/poster.jpg name=poster size=80 /><br/>".$language['post1']."\n", 1);
?>
<tr><td align="center" colspan="2"><iframe src="uploadbb.html" style="width:500px; height:48px; border:none" frameborder="0"></iframe>
<br/><font class="small">Note* the upload is handled by the bitbucket and the image will be hosted on the server</font>
</td></tr>
<?php
tr("".$language['sam']."", "<input type=\"text\" name=\"tube\" size=\"80\" /><br />".$language['sam1']."\n", 1);
tr("".$language['tfile']."", "<input type=file name=file size=80 />\n", 1);
tr("".$language['tname']."", "<input type=\"text\" name=\"name\" size=\"80\" /><br />".$language['tname1']."\n", 1);
if ($nforeq)
tr("".$language['nfo']."", "<input type=file name=nfo size=80><br>".$language['nfo1']."\n", 1);
else
tr("".$language['nfo']."", "<input type=file name=nfo size=80><br>".$language['nfo2']."\n", 1);
tr("".$language['desc']."", "<textarea name=\"descr\" rows=\"10\" cols=\"80\"></textarea>" . "<br/>".$language['desc1']."", 1);
tr("".$language['sdesc']."", "<input type=\"text\" name=\"description\" size=\"80\" /><b></b><br>".$language['sdesc1']."", 1);
tr("".$language['asci']."", "<input type=checkbox name=strip value=strip />".$language['asci1']."", 1);
//////////////Subtitles by putyn
require_once ROOT_PATH."cache/subs.php";
    $subs_list .= "<table border=\"1\"><tr>\n";
    $i = 0;
    foreach($subs as $s)
    {
      $subs_list .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
      $subs_list .= "<td style='padding-right: 5px'><input name=\"subs[]\" type=\"checkbox\" value=\"".$s["id"]."\" /> ".$s["name"]."</td>\n";
      ++$i;
    }
    $subs_list .= "</tr></table>\n";
tr("".$language['subs']."",$subs_list,1);
//min class mod :)
	$fstaff = 3;
	$min_class = "<select name=\"minclass\"><option value=\"255\">all users</option>";
	for($i = 0;$i<$fstaff+1; $i++)
		$min_class .= "<option value=\"".$i."\">".($i == $fstaff ? "Staff" : get_user_class_name($i)."s")."</option>\n";
	$min_class .="</select>";
?>

<tr><td align=right><b>Min class</b></td><td ><?php echo $min_class; ?></td><?php
?>
<script type="text/javascript">
window.onload = function() {
    setupDependencies('upload'); //name of form(s). Seperate each with a comma (ie: 'weboptions', 'myotherform' )
  };
</script>
<tr><td align=right><b><?php echo $language['genre'];?></b><br><?php echo $language['opt'];?></td><td align=left>
<table><tr><input type=radio name=genre value="keep" checked>Dont touch it (Current: <?=$row["newgenre"]?>)<br>
<td style="border:none"><input type="radio" name="genre" value="movie">Movie</td>
<td style="border:none"><input type="radio" name="genre" value="music">Music</td>
<td style="border:none"><input type="radio" name="genre" value="game">Game</td>
<td style="border:none"><input type="radio" name="genre" value="apps">Apps</td>
<td style="border:none"><input type="radio" name="genre" value="">None</td>
</tr>
<tr><td colspan=4 style="border:none">
<label style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 3px silver groove;">
<input type="hidden" class="DEPENDS ON genre BEING movie OR genre BEING music"></label>
<?php
        $movie = array ('Action', 'Comedy', 'Thriller', 'Adventure', 'Family', 'Adult', 'Sci-fi');
        for ($x = 0; $x < count ($movie); $x++) {
            echo "<label><input type=\"checkbox\" value=\"$movie[$x]\"  name=\"movie[]\" class=\"DEPENDS ON genre BEING movie\">$movie[$x]</label>";
        }
        $music = array ('Hip Hop', 'Rock', 'Pop', 'House', 'Techno', 'Commercial');
        for ($x = 0; $x < count ($music); $x++) {
            echo "<label><input type=\"checkbox\" value=\"$music[$x]\" name=\"music[]\" class=\"DEPENDS ON genre BEING music\">$music[$x]</label>";
        }
        $game = array ('Fps', 'Strategy', 'Adventure', '3rd Person', 'Acton');
        for ($x = 0; $x < count ($game); $x++) {
            echo "<label><input type=\"checkbox\" value=\"$game[$x]\" name=\"game[]\" class=\"DEPENDS ON genre BEING game\">$game[$x]</label>";
        }
        $apps = array ('Burning', 'Encoding', 'Anti-Virus', 'Office', 'Os', 'Misc', 'Image');
        for ($x = 0; $x < count ($apps); $x++) {
            echo "<label><input type=\"checkbox\" value=\"$apps[$x]\" name=\"apps[]\" class=\"DEPENDS ON genre BEING apps\">$apps[$x]</label>";
        }

        ?>
</td></tr></table>
</td></tr>
<?php
$s = "<select name=\"type\">\n<option value=\"0\">(choose one)</option>\n";
$cats = genrelist();
foreach ($cats as $row)
$s .= "<option value=\"" . $row["id"] . "\">" . safeChar($row["name"]) . "</option>\n";
$s .= "</select>\n";
tr("".$language['typ']."", $s, 1);
$so = "<select name=\"scene\">\n<option value=\"no\">Non-Scene</option>\n<option value=\"yes\">Scene</option>\n</select>\n";
tr("".$language['rel']."", $so, 1);
$sp = "<select name=\"request\">\n<option value=\"no\">No</option>\n<option value=\"yes\">Yes</option>\n</select>\n";
tr("".$language['req1']."", $sp, 1);
tr("".$language['upper']."", "<input type=checkbox name=uplver value=yes />".$language['upper1']."", 1);
tr("".$language['vip']."", "<input type='checkbox' name='vip'" . (($row["vip"] == "yes") ? " checked='checked'" : "") . " value='1' />".$language['vip1']." ", 1);
// === allow comments?
if (get_user_class() >= UC_MODERATOR && get_user_class() <= UC_CODER)
    tr("".$language['com']."", "<input type=\"radio\" name=\"allow_comments\" value=\"yes\" checked=\"checked\" /> Yes <input type=\"radio\" name=\"allow_comments\" value=\"No\" />".$language['com1']."<br />", 1);
// === end
//===hidden torrent?
if ($CURUSER["hiddentorrents"] == "yes"||get_user_class() >= UC_MODERATOR)
tr("Hidden torrent:", "<input type=radio name=hidden value=yes /> yes <input type=radio name=hidden value=no checked=checked /> no - regular torrent seen by all.", 1);
//===free upload or staff only torrent
if (get_user_class() >= UC_MODERATOR){      
tr("Staff Only:","<input type=radio name=staffonly value=yes /> yes <input type=radio name=staffonly value=no checked=checked /> no -  selecting yes here will hide this torrent from everyone except staff.",1);      
tr("".$language['free']."","<input type=radio name=countstats value=yes checked=checked /> yes <input type=radio name=countstats value=no />".$language['free']."</br> ",1);
}
//===end free upload
///////////////half
if (get_user_class() >= UC_ADMINISTRATOR)
tr("".$language['half']."", "<input type='checkbox' name='half'" . ((isset($row["half"]) == "yes") ? " checked='checked'" : "") . " value='1' /><font color=green><b>&#189; download</font></b>", 1);
if (get_user_class() >= UC_ADMINISTRATOR) {
    tr("".$language['multi']."",
        "<input type=radio name=multiplicator checked value=0 />No Multiplicator
<input type=radio name=multiplicator value=2 />Upload x 2
<input type=radio name=multiplicator value=3 />Upload x 3
<input type=radio name=multiplicator value=4 />Upload x 4
<input type=radio name=multiplicator value=5 />Upload x 5", 1);
}
$note = $CURUSER['class'] < UC_VIP?'<h3>'.$language['search1'].'</h3><img onload="disableme()" src="/pic/blank1.gif" width=1px />':'';

?>
<tr><td align="center" colspan="2"><input id="doit" type="submit" class=btn value="Do it!" /></td></tr>
</table>
</form>
<?php
echo $note;
stdfoot();

?>