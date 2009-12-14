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

if ($usergroups['canupload'] == 'no' OR $usergroups['canupload'] != 'yes' OR $CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no'){
stdmsg("Sorry...", "You are not authorized to upload torrents.  (See <a href=\"faq.php#up\">Uploading</a> in the FAQ.)", false);
stdfoot();
exit;
}

echo '<br />';

?>
<table>
<tr>
<td>
<form name="upload" method="post" action="takeupload.php" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_torrent_size?>" />
<p align="center"><font color="red" size="3"><?php echo $language['annurl'];?> <b><?= $announce_urls[0] ?></font></b><br />
<b>Only upload torrents you're going to seed!</b> Torrents won't be visible on the main page until you start seeding them.<br />
<b>Your torrent will automatically download once you press submit !</b></p>
<table>


<tr>
<td class="uptablahead" align="left" width="800" colspan="2"><?php echo $language['yoff'];?></td>
</tr>
<?php
// ==== offer dropdown for offer mod
$res = sql_query("SELECT id, name, allowed FROM offers WHERE userid = $CURUSER[id] ORDER BY name ASC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0) {
    $offer = "<select name=offer><option value=0>(select)</option>";
    while ($row = mysql_fetch_array($res)) {
        if ($row['allowed'] == 'allowed')
            $offer .= "<option value=\"" . $row["id"] . "\">" . safeChar($row["name"]) . "</option>";
    }
    $offer .= "</select>";
  
}

if (mysql_num_rows($res) <= 0) {
	$offer = $language['no_off'];
	}

?>
<tr>
<td class="uptd" width="30%" align="center"><b><?php echo $language['off'];?></b></td>
<td class="uptd" width="70%"><?=$offer?><br/><?php echo $language['off1'];?>
</td>
</tr>

<tr>
<td class="uptablahead" align="left" width="800" colspan="2"><?php echo $language['typegensel'];?></td>
</tr>
<?
$s = "<select name=\"type\" onChange=\"lejon('kat')\">\n<option value=\"0\">(Choose one)</option>\n";
$cats = genrelist();
foreach ($cats as $row)
$s .= "<option value=\"" . $row["id"] . "\">" . safeChar($row["name"]) . "</option>\n";
$s .= "</select>\n";
?>
<tr>
<td class="uptd" width="30%" align="center"><b><?php echo $language['typ'];?></b></td>
<td class="uptd" width="70%"><?=$s?></td>
</tr>

<tr>
<td class="uptd" align="center">
<b><?php echo $language['gen'];?></b>&nbsp;&nbsp;&nbsp;<br/><?php echo $language['opt'];?>&nbsp;&nbsp;&nbsp;
</td>
<td class="uptd" align="left">
<table><tr>
<input type="radio" name="genre" value="keep" checked="checked" />Dont touch it (Current: <?=$row["newgenre"]?>)<br />
<td style="border:none"><input type="radio" name="genre" value="movie" />Movie</td>
<td style="border:none"><input type="radio" name="genre" value="music" />Music</td>
<td style="border:none"><input type="radio" name="genre" value="game" />Game</td>
<td style="border:none"><input type="radio" name="genre" value="apps" />Apps</td>
<td style="border:none"><input type="radio" name="genre" value="" />None</td>
</tr>
<tr><td colspan="4" style="border:none">
<label style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 3px silver groove;">
<input type="hidden" class="DEPENDS ON genre BEING movie OR genre BEING music" /></label>
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
</table>

<div id="kat" style="display: none;">
<table>
<tr>
<td class="uptablahead" align="left" width="800" colspan="2"><?php echo $language['gttn'];?></td>
</tr>

<tr>
 <td class="uptd" width="30%" align="center" valign="top"><b><?php echo $language['tname'];?> </b></td>
   <td class="uptd" width="70%"><input id="tname" type="text" name="name" size="80" />
   <input type="button" value="Next step" onclick="if(document.getElementById('tname').value.length < 5){alert('Sorry, the name is too short (min is 5 chars)!');} else {feltoltott_torrentek(document.getElementById('tname').value,'kereses'); lejon('kat2');}" />
   <br /><?php echo $language['tname1'];?></b>
      <br />
   <div id="kereses">
   </div>
   <input type="button" value="Hide results" onclick="becsuk('kereses');" />&nbsp;
   <input type="button" value="Show results" onclick="lejon('kereses');" />
   <br />
 </td>
</tr></table>
</div>



<div id="kat2" style="display: none;">
<table><tr><td class="uptablahead" align="left" width="800" colspan="2"><?php echo $language['btnf'];?></td></tr>

<tr>
<td class="uptd" width="30%" align="center"><b>Torrent file </b></td>
<td class="uptd" width="70%"><input id="tfile" type="file" name="file" size="80" /></td>
</tr>

<tr>
<td class="uptd" width="30%" align="center"><b>NFO file </b></td>
<td class="uptd" width="70%"><input id="nfile" type="file" name="nfo" size="80" /><br />
<b><?php echo $language['nfo1'];?></b>
<br />
<input type="button" value="Next step" onclick="if(document.getElementById('tfile').value=='' | document.getElementById('nfile').value==''){alert('Sorry, required TORRENT and NFO file!');} else{lejon('kat3');}" />
</td></tr>
</table>
</div>
<br />

<div id="kat3" style="display: none;">
<table>
<tr>
<td class="uptablahead" align="left" width="800" colspan="2"><b><?php echo $language['tinf'];?></b></td>
</tr>

<tr>
<td class="uptd" width="15%" align="center" valign="top"><br /><b><?php echo $language['desc'];?></b> </td>
<td class="uptd" width="85%">
<?
textbbcode("upload","descr",($quote?(("[quote=".htmlspecialchars($arr["username"])."]".htmlspecialchars(unesc($arr["body"]))."[/quote]")):""));
?>
</td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['sdesc'];?></b></td>
<td class="uptd" width="85%"><input type="text" name="description" size="80" />
<br /><?php echo $language['sdesc1'];?>
</td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['asci'];?></b></td>
<td class="uptd" width="85%"><input type="checkbox" name="strip" value="strip" />
<br /><?php echo $language['asci1'];?>
</td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['imdb'];?></b></td>
<td class="uptd" width="85%"><input type="text" name="url" size="80" />
<br /><?php echo $language['imdb1'];?>
</td>
</tr>

<?php

//////////////Subtitles by putyn
require_once ROOT_PATH."cache/subs.php";
    $subs_list .= "<table border=\"0\"><tr>\n";
    $i = 0;
    foreach($subs as $s)
    {
      $subs_list .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
      $subs_list .= "<td style='padding-right: 5px'><input name=\"subs[]\" type=\"checkbox\" value=\"".$s["id"]."\" /> ".$s["name"]."</td>\n";
      ++$i;
    }
    $subs_list .= "</tr></table>\n";

?>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['subs'];?></b></td>
<td class="uptd" width="85%"><?=$subs_list?></td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['sam'];?></b></td>
<td class="uptd" width="85%"><input type="text" name="tube" size="80" /><br /><?php echo $language['sam1'];?></td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b>Bitbucket</b></td>
<td class="uptd" width="85%">
<iframe src="imgup.html" style="width:500px; height:48px; border:none" frameborder="0"></iframe>
<br /><?php echo $language['bitb'];?>
</td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['post'];?> </b></td>
<td class="uptd" width="85%"><input id="posterf" type="text" name="poster" size="82" /> 
<input type="button" value="Next step" onclick="if(document.upload.descr.value.length=='0' | document.getElementById('posterf').value==''){alert('Sorry , required DESCRIPTION and POSTER!');} else{lejon('kat4');}" />
<br />
</td>
</tr>

</table>
</div>
<a>
<br />
<div id="kat4" style="display: none;">
<table>
<tr>
<td class="uptablahead" align="left" width="800" colspan="2"><?php echo $language['oo'];?></td></tr>


<?php
//min class mod :)
	$fstaff = 3;
	$min_class = "<select name=\"minclass\"><option value=\"255\">all users</option>";
	for($i = 0;$i<$fstaff+1; $i++)
		$min_class .= "<option value=\"".$i."\">".($i == $fstaff ? "Staff" : get_user_class_name($i)."s")."</option>\n";
	$min_class .="</select>";
?>

<tr>
<td class="uptd" align="center"><b>Min class</b></td>
<td class="uptd"><?php echo $min_class; ?></td>
</tr>
<tr>
<?php
$so = "<select name=\"scene\">\n<option value=\"no\">Non-Scene</option>\n<option value=\"yes\">Scene</option>\n</select>\n";
?>
<td class="uptd" width="15%" align="center"><b><?php echo $language['rel'];?>: </b></td>
<td class="uptd" width="70%"><?=$so?><br /></td>
</tr>

<tr>
<?php
$sp = "<select name=\"request\">\n<option value=\"no\">no</option>\n<option value=\"yes\">yes</option>\n</select>\n";
?>
<td class="uptd" width="15%" align="center"><b><?php echo $language['req1'];?>: </b></td>
<td class="uptd" width="70%"><?=$sp?></td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['upper'];?>: </b></td>
<td class="uptd" width="70%"><input type="checkbox" name="uplver" value="yes" /><?php echo $language['upper1'];?></td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['vip'];?>: </b></td>
<td class="uptd" width="70%">
<input type='checkbox' name='vip'" . (($row["vip"] == "yes") ? "" : "") . " value='1' />
<?php echo $language['vip1'];?></td>
</tr>

<!--  allow comments?  -->
<?php

if (get_user_class() >= UC_UPLOADER && get_user_class() <= UC_CODER)
 { ?>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['com'];?>: </b></td>
<td class="uptd" width="70%"><input type="radio" name="allow_comments" value="yes" checked="checked" /> yes <input type="radio" name="allow_comments" value="No" /><?php echo $language['com1'];?></td>
</tr>
<?}?>

<!-- end allow comments -->

<!--  free upload or staff only torrent  -->

<?php
if (get_user_class() >= UC_MODERATOR) { ?>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['free'];?>: </b></td>
<td class="uptd" width="70%"><input type="radio" name="countstats" value="yes" checked="checked" /> yes <input type="radio" name="countstats" value="no" /><?php echo $language['free1'];?></td>
</tr>

<?}?>


<!--      end free upload   -->

<!--      multi upload and half download  -->

<?php if (get_user_class() >= UC_ADMINISTRATOR) {?>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['half'];?> </b></td>
<td class="uptd" width="70%"><input type='checkbox' name='half'" . ((isset($row["half"]) == "yes") ? "" : "") . " value='1' /><font color=green><b>&#189; download</font></b></td>
</tr>

<tr>
<td class="uptd" width="15%" align="center"><b><?php echo $language['multi'];?>: </b></td>
<td class="uptd" width="70%">
<input type="radio" name="multiplicator" value="0" checked="checked" />No Multiplicator
<input type="radio" name="multiplicator" value="2" />Upload x 2
<input type="radio" name="multiplicator" value="3" />Upload x 3
<input type="radio" name="multiplicator" value="4" />Upload x 4
<input type="radio" name="multiplicator" value="5" />Upload x 5</td>
</tr>
<?}?>

<!--     end multi upload   -->

<tr>
<td class="uptd" width="30%" align="center" valign="midle"><b>Accept rules.. </b></td>
<td class="uptd" width="70%">
<input id="szab" type="checkbox" onclick="ellenoriz();" />I have read the site rules page<br />
<input id="seed" type="checkbox" onclick="ellenoriz();" />I have read the upload rules 
</td></tr>
</table>
</div>

<br />
<div id="kat5" style="display: none;">
<table>
<tr>
<td class="uptablahead" align="left" width="800" colspan="2">Upload!!!</td>
</tr>
<tr>
<td class="uptd" align="center" colspan="2">
<input id="feltolt" type="submit" class="btn" value="Do It!" />
</td>
</tr>

</table>
</div>
<script type="text/javascript">
<!--
window.onload = function() {
    setupDependencies('upload');  //name of form(s). Seperate each with a comma (ie: 'weboptions', 'myotherform' )
  };
 -->
</script>

<script type="text/javascript" src="js/jquery_up.js"></script>
<script type="text/javascript" src="js/ellenorzesek.js"></script>

</form>
</td>
</tr>
</table>
<?php

stdfoot();

?>