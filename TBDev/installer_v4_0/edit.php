<?php
$page_find = 'edit';
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
if (!mkglobal("id"))
    die();
$id = 0 + $id;
if (!$id)
    die();
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
$res = mysql_query("SELECT * FROM torrents WHERE id =".unsafeChar($id)."");
$row = mysql_fetch_assoc($res);
if (!$row)
    die();

stdhead("Edit torrent \"" . $row["name"] . "\"");

if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR)) {
    echo("<h1>Can't edit this torrent</h1>\n");
    echo("<p>You're not the rightful owner, or you're not <a href=\"login.php?returnto=" . urlencode(substr($_SERVER["REQUEST_URI"], 1)) . "&amp;nowarn=1\">logged in</a> properly.</p>\n");
} else {
    echo("<form name=edit method=post action=takeedit.php enctype=multipart/form-data>\n");
    echo("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
    if (isset($_GET["returnto"]))
        echo("<input type=\"hidden\" name=\"returnto\" value=\"" . safeChar($_GET["returnto"]) . "\" />\n");
    echo("<table border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n");
    tr("".$language['url']."", "<input type=text name=url size=80 value='" . safeChar($row["url"]) . "'>", 1);
    tr("".$language['poster']."", "<input type=text name=poster size=80 value='" . safeChar($row["poster"]) . "'><br>".$language['poster1']."\n", 1);
    tr("".$language['trail']."", "<input type=text name=tube size=80 value='" . safeChar($row["tube"]) . "'><br>".$language['sam1']."\n", 1);
    tr("".$language['name']."", "<input type=\"text\" name=\"name\" value=\"" . safechar($row["name"]) . "\" size=\"80\" />", 1);
    tr("".$language['sdesc']."", "<input type =\"text\" name=\"description\" size=\"80\" value=\"" .SafeChar($row["description"]). "\"><br>".$language['sdesc1']."", 1);
    tr("".$language['nfo']."", "<input type=radio name=nfoaction value='keep' checked>Keep current<br>" . "<input type=radio name=nfoaction value='update'>Update:<br><input type=file name=nfo size=80>", 1);
    if ((strpos($row["ori_descr"], "<") === false) || (strpos($row["ori_descr"], "&lt;") !== false))
        $c = "";
    else
        $c = " checked";
    tr("".$language['desc']."", "<textarea name=\"descr\" rows=\"10\" cols=\"80\">" . safeChar($row["ori_descr"]) . "</textarea><br>".$language['desc1']."", 1);
    $s = "<select name=\"type\">\n";

    if (get_user_class() >= UC_MODERATOR)
    $cats = genrelist();
    else
    $cats = genrelist2();
    foreach ($cats as $subrow) {
        $s .= "<option value=\"" . $subrow["id"] . "\"";
        if ($subrow["id"] == $row["category"])
            $s .= " selected=\"selected\"";
        $s .= ">" . safechar($subrow["name"]) . "</option>\n";
    }

    $s .= "</select>\n";
    tr("".$language['type']."", $s, 1);
    $so = "<select name=\"scene\">\n<option value=\"no\"" . ($row["scene"] == "no" ? " selected" : "") . ">Non-Scene</option>\n<option value=\"yes\"" . ($row["scene"] == "yes" ? " selected" : "") . ">Scene</option>\n</select>\n";
    tr("".$language['rel']."", $so, 1);
    $sp = "<select name=\"request\">\n<option value=\"no\"" . ($row["request"] == "no" ? " selected" : "") . ">No</option>\n<option value=\"yes\"" . ($row["request"] == "yes" ? " selected" : "") . ">Yes</option>\n</select>\n";
    tr("".$language['req']."", $sp, 1);
    tr("".$language['vis']."", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "") . " value=\"1\" /> Visible on main page<br /><table border=0 cellspacing=0 cellpadding=0 width=420><tr><td class=embedded>".$language['vis1']."</td></tr></table>", 1);
    tr("".$language['annon']."", "<input type=\"checkbox\" name=\"anonymous\"" . (($row["anonymous"] == "yes") ? " checked=\"checked\"" : "") . " value=\"1\" />".$language['anon1']."", 1);
    ////////////////subtitles by putyn
    include ROOT_PATH.'cache/subs.php';
    $subs_list .= "<table border=\"1\"><tr>\n";
    $i = 0;
    foreach($subs as $s)
    {    
      $subs_list .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
      $subs_list .= "<td style='padding-right: 5px'><input name=\"subs[]\" " . (strpos($row["subs"], $s["id"]) !== false ? " checked" : "") . "  type=\"checkbox\" value=\"".$s["id"]."\" /> ".$s["name"]."</td>\n";
      ++$i;
    }
    $subs_list .= "</tr></table>\n";
    tr("".$language['subs']."",$subs_list,1);
    //////////////////////////
    // if(get_user_class() > UC_MODERATOR)
    // tr("Banned", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Banned", 1);
    if (get_user_class() >= UC_ADMINISTRATOR)
        tr("".$language['half']."", "<input type='checkbox' name='half'" . (($row["half"] == "yes") ? " checked='checked'" : "") . " value='1' /><font color=green><b>&#189; download</font></b>", 1);
    tr("".$language['rec']."", "<input type=radio name=recommended" . ($row["recommended"] == "yes" ? " checked" : "") . " value=yes>Yes!<input type=radio name=recommended" . ($row["recommended"] == "no" ? " checked" : "") . " value=no>No!<br><font class=small size=1>".$language['rec1']."</font>", 1);
    // ===allow comments?
    if (get_user_class() >= UC_MODERATOR && get_user_class() <= UC_CODER) {
    if ($row["allow_comments"] == "yes")
    $messc = " Comments are allowed for everyone on this torrent!";
    else
    $messc = " Only staff members are able to comment on this torrent!";
    ?>
    <tr><td align="right"><font color="red">*</font><b><?php echo $language['comm'];?></b></td>
    <td><select name="allow_comments">
    <option value="<?=SafeChar($row["allow_comments"])?>"><?=SafeChar($row["allow_comments"])?></option>
    <option value="yes"> Yes </option><option value="no"> No </option></select> <?=$messc?></td></tr>
    <?php
    }
    // ===end
    //===hidden torrent?
    if ($CURUSER["hiddentorrents"] == "yes" || get_user_class() >= UC_MODERATOR){
    if ($row["hidden"] == "yes")
    $mess = " yes this torrent is hidden!";
    else
    $mess = " no - regular torrent seen by all.";     
    ?>
    <tr><td align="right"><font color="red">*</font><b>Hidden torrent:</b></td>
    <td><select name="hidden">
    <option value="<?=SafeChar($row[hidden])?>"><?=SafeChar($row[hidden])?></option>
    <option value="yes"> yes </option><option value="no"> no </option></select> <?=$mess?></td></tr>
    <?php
    }    
    //===free upload or staff only torrent
    if (get_user_class() >= UC_MODERATOR){
    if ($row["staffonly"] == "yes")
    $mess2 = " this torrent is seen ONLY by staff!";
    else
    $mess2 = " no - regular torrent seen by all.";     
    ?>
    <tr><td align="right"><font color="red">*</font><b>Staff torrent:</b></td>
    <td><select name="staffonly">
    <option value="<?=SafeChar($row[staffonly])?>"><?=SafeChar($row[staffonly])?></option>
    <option value="yes"> yes </option><option value="no"> no </option></select> <?=$mess2?></td></tr>
    <?php 
    if ($row["countstats"] == "yes")
    $mess3 = " yes - this is a normal torrent!";
    else
    $mess3 = " no - this is a FREE torrent!";     
    ?>
    <tr><td align="right"><font color="red">*</font><b><?php echo $language['free'];?></b></td>
    <td><select name="countstats">
    <option value="<?=SafeChar($row[countstats])?>"><?=SafeChar($row[countstats])?></option>
    <option value="yes"> yes </option><option value="no"> no </option></select> <?=$mess3?></td></tr>
    <?php
    }
    //===end free upload / staff stuff
        if (get_user_class() >= UC_UPLOADER)
            tr("".$language['vip']."", "<input type='checkbox' name='vip'" . (($row["vip"] == "yes") ? " checked='checked'" : "") . " value='1' /> If this one is checked, only VIPs can download this torrent", 1);
        if (get_user_class() > UC_MODERATOR)
            tr("".$language['sticky']."", "<input type='checkbox' name='sticky'" . (($row["sticky"] == "yes") ? " checked='checked'" : "") . " value='yes' />Set sticky this torrent!", 1);
        if (get_user_class() >= UC_ADMINISTRATOR) {
            tr("".$language['multi']."",
                "<input type=radio name=multiplicator" . (($row["multiplicator"] == "0") ? " checked='checked'" : "") . " value=0>No Multiplicator
    <input type=radio name=multiplicator " . (($row["multiplicator"] == "2") ? " checked='checked'" : "") . " value=2>Upload x 2
    <input type=radio name=multiplicator " . (($row["multiplicator"] == "3") ? " checked='checked'" : "") . " value=3>Upload x 3
    <input type=radio name=multiplicator " . (($row["multiplicator"] == "4") ? " checked='checked'" : "") . " value=4>Upload x 4
    <input type=radio name=multiplicator " . (($row["multiplicator"] == "5") ? " checked='checked'" : "") . " value=5>Upload x 5" , 1);
        }
        tr("".$language['nuked']."", "<input type=radio name=nuked" . ($row["nuked"] == "yes" ? " checked" : "") . " value=yes>Yes <input type=radio name=nuked" . ($row["nuked"] == "no" ? " checked" : "") . " value=no>No <input type=radio name=nuked" . ($row["nuked"] == "unnuked" ? " checked" : "") . " value=unnuked>Unnuked", 1);
        tr("".$language['nuker']."", "<input type=\"text\" name=\"nukereason\" value=\"" . safechar($row["nukereason"]) . "\" size=\"80\" />", 1);

        ?>
<script type="text/javascript">
window.onload = function() {
    setupDependencies('edit'); //name of form(s). Seperate each with a comma (ie: 'weboptions', 'myotherform' )
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
        echo("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value='Edit it!' style='height: 25px; width: 100px'> <input type=reset value='Revert changes' style='height: 25px; width: 100px'></td></tr>\n");
        echo("</table>\n");
        echo("</form>\n");
        echo("<p>\n");
        echo("<form method=\"post\" action=\"delete.php\">\n");
        echo("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
        echo("<tr><td class=embedded style='background-color: #000000;padding-bottom: 5px' colspan=\"2\"><b>Delete torrent.</b> Reason:</td></tr>");
        echo("<td><input name=\"reasontype\" type=\"radio\" value=\"1\">&nbsp;Dead </td><td> 0 seeders, 0 leechers = 0 peers total</td></tr>\n");
        echo("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"2\">&nbsp;Dupe</td><td><input type=\"text\" size=\"40\" name=\"reason[]\"></td></tr>\n");
        echo("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"3\">&nbsp;Nuked</td><td><input type=\"text\" size=\"40\" name=\"reason[]\"></td></tr>\n");
        echo("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"4\">&nbsp;$BASEURL rules</td><td><input type=\"text\" size=\"40\" name=\"reason[]\">(req)</td></tr>");
        echo("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"5\" checked>&nbsp;Other:</td><td><input type=\"text\" size=\"40\" name=\"reason[]\">(req)</td></tr>\n");
        echo("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
        if (isset($_GET["returnto"]))
            echo("<input type=\"hidden\" name=\"returnto\" value=\"" . safeChar($_GET["returnto"]) . "\" />\n");
        echo("<td colspan=\"2\" align=\"center\"><input type=submit value='Delete it!' style='height: 25px'></td></tr>\n");
        echo("</table>");
        echo("</form>\n");
        echo("</p>\n");
    }

    stdfoot();

    ?>
