<?php
$page_find = "details";
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require ("imdb/imdb.class.php");
require_once("include/function_torrenttable.php");
require_once("include/commenttable.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
getpage();

dbconn(false);
maxcoder();

if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

$id = 0 + $_GET["id"];

if (!isset($id) || !$id)
    die();
$minvotes = '1';

?>
<script type="text/javascript" language="JavaScript">
var temppeers = '';
function hidepeers()
{
  document.getElementById("nopeerlist").innerHTML = '<a href="javascript:peerlist(<?php echo $id; ?>, 1);" class="sublink">[See full list]</a>';
  document.getElementById("peerlist").innerHTML = temppeers;
}
function peerlist(id, what) {
  temppeers = document.getElementById("peerlist").innerHTML;
  document.getElementById("peerlist").innerHTML = '<img src="pic/loading.gif" width="16" height="16">';
  document.getElementById("nopeerlist").innerHTML = '<a href=\"javascript:hidepeers();\">[Hide list]</a>';
	var url = 'ajax_peerlist.php?id=' + escape(id);
	try {
		request = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
				try {
					request = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e2) {
						request = false;
								}
				}
if (!request && typeof XMLHttpRequest != 'undefined') {
  request = new XMLHttpRequest();
}
	request.open("GET", url, true);
	global_content = what;
	request.onreadystatechange = peersgo;
	request.send(null);
}
function peersgo() {
  if (request.readyState == 4) {
	  if (request.status == 200) {
		var response = request.responseText;
		var peerbox = document.getElementById("peerlist");
			fetch(peerbox);
      peerbox.innerHTML = response;
      if(global_content == 1)
      document.location.href = '#seeders';
      else
      document.location.href = '#leechers';
	  }
  }
}


var tmpfiler = '';

function hidefile()
{
  document.getElementById("hidefile").innerHTML = '<a href="javascript:filelist();" class="sublink">[See full list]</a>';
  document.getElementById("filelist").innerHTML = tmpfiler ;
}

function filelist() {

  tmpfiler = document.getElementById("filelist").innerHTML;
  document.getElementById("filelist").innerHTML = '<img src="pic/loading.gif" width="16" height="16">';
  document.getElementById("hidefile").innerHTML = '<a href=\"javascript:hidefile();\">[Hide list]</a>';
	var url = 'ajax_filelist.php?id=' + escape(<?php echo $id; ?>);
	try {
		request = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
				try {
					request = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e2) {
						request = false;
								}
				}

if (!request && typeof XMLHttpRequest != 'undefined') {
  request = new XMLHttpRequest();
}
	request.open("GET", url, true);
	//global_content = id;
	request.onreadystatechange = filego;
	request.send(null);
}

function filego() {
  if (request.readyState == 4) {
	  if (request.status == 200) {
	  var filelist = document.getElementById("filelist");
	  fetch(filelist);
		var response = request.responseText;
      filelist.innerHTML = response;
	  }
  }
}

</script>
<?php

$res = sql_query("SELECT torrents.minclass,torrents.seeders, torrents.banned, torrents.nuked, torrents.nukereason, torrents.newgenre, torrents.checked_by, torrents.leechers, torrents.info_hash, torrents.filename, torrents.points, LENGTH(torrents.nfo) AS nfosz, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(torrents.last_action) AS lastseed, torrents.numratings, torrents.name, torrents.description, IF(torrents.numratings < $minvotes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.owner, torrents.save_as, torrents.allow_comments, torrents.descr, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.tube, torrents.type, torrents.numfiles, torrents.vip, torrents.url, torrents.hidden, torrents.staffonly, torrents.countstats, torrents.anonymous, torrents.poster, freeslots.free AS freeslot, freeslots.doubleup AS doubleslot, freeslots.addedfree AS addedfree, freeslots.addedup AS addedup, freeslots.torrentid AS slotid, freeslots.userid AS slotuid, categories.name AS cat_name, categories.id AS cat_id, users.username FROM torrents LEFT JOIN freeslots ON (torrents.id=freeslots.torrentid AND freeslots.userid={$CURUSER["id"]}) LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);

	if($row["minclass"] != 255 && $row["minclass"] > $CURUSER["class"])
		die("This is not for you");
		
$owned = $moderator = 0;
if (get_user_class() >= UC_MODERATOR)
    $owned = $moderator = 1;
elseif ($CURUSER["id"] == $row["owner"])
    $owned = 1;
if ($row["vip"] == "yes" && get_user_class() < UC_VIP)
    stderr("VIP Access Required", "You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you more Vip-Only Torrents!");

if (!$row || ($row["banned"] == "yes" && !$moderator))
    stderr("Error", "No torrent with ID.");
else {
    if ($_GET["hit"]) {
        sql_query("UPDATE torrents SET views = views + 1 WHERE id = $id");
        if ($_GET["tocomm"])
            header("Location: $BASEURL/details.php?id=$id&page=0#startcomments");
        elseif ($_GET["filelist"])
            header("Location: $BASEURL/details.php?id=$id&filelist=1#filelist");
        elseif ($_GET["toseeders"])
            header("Location: $BASEURL/details.php?id=$id&dllist=1#seeders");
        elseif ($_GET["todlers"])
            header("Location: $BASEURL/details.php?id=$id&dllist=1#leechers");
        else
            header("Location: $BASEURL/details.php?id=$id");
        exit();
    }

    if (!isset($_GET["page"])) {
        stdhead("Details for torrent \"" . $row["name"] . "\"");

        if ($CURUSER["id"] == $row["owner"] || get_user_class() >= UC_MODERATOR)
            $owned = 1;
        else
            $owned = 0;

        $spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        // === free download?
        if ($row["countstats"] == "no")
            echo("<h1><img src=pic/cat_free.gif alt=FREE> This Torrent Is Currently Set To Free! <img src=pic/cat_free.gif alt=FREE></h1>\n");
        //===hide hidden & staff torrents
        if ($row["hidden"] == "yes") {
        if ($CURUSER["hiddentorrents"] == "no"){      
        begin_frame("Error!",true);
        echo("<p>No torrent with that ID. It may have been removed.<br><br></p>\n");
        end_frame();
        stdfoot();
        die;
        }
        }
        if ($row["staffonly"] == "yes") {
        if (get_user_class() < UC_MODERATOR) {      
        begin_frame("Error!",true);
        echo("<p>No torrent with that ID. It may have been removed.<br><br></p>\n");
        end_frame();
        stdfoot();
        die;
        }
        }
        //=== end
        if ($_GET["uploaded"]) {
            echo("<h2>Successfully uploaded!</h2>\n");
            echo("<p><b>Please wait - Your torrent will download automatically </b> <b>Note : that the torrent won't be visible until you start seeding! </b></p>\n");
            echo("<meta http-equiv=\"refresh\" content=\"1;url=download.php/$id/" . rawurlencode($row["filename"]) . "\"/>");
        } elseif ($_GET["edited"]) {
            echo("<h2>Successfully edited!</h2>\n");
            if (isset($_GET["returnto"]))
                echo("<p><b>Go back to <a href=\"" . safeChar("{$BASEURL}/{$_GET['returnto']}") . "\">whence you came</a>.</b></p>\n");
        } elseif (isset($_GET["searched"])) {
            echo("<h2>Your search for \"" . safeChar($_GET["searched"]) . "\" gave a single result:</h2>\n");
        } elseif ($_GET["rated"])
            echo("<h2>Rating added!</h2>\n");
        // start torrent check mod
        if ($CURUSER['class'] >= UC_MODERATOR) {
            if (isset($_GET["checked"]) && $_GET["checked"] == 1) {
                sql_query("UPDATE torrents SET checked_by = " . sqlesc($CURUSER['username']) . " WHERE id =$id LIMIT 1");
                write_log("torrentedit", "Torrent <a href=$BASEURL/details.php?id=$id>($row[name])</a> was checked by $CURUSER[username]");
                header("Location: $BASEURL/details.php?id=$id&checked=done#Success");
            } elseif (isset($_GET["rechecked"]) && $_GET["rechecked"] == 1) {
                sql_query("UPDATE torrents SET checked_by = " . sqlesc($CURUSER['username']) . " WHERE id =$id LIMIT 1");
                write_log("torrentedit", "Torrent <a href=$BASEURL/details.php?id=$id>($row[name])</a> was re-checked by $CURUSER[username]");
                header("Location: $BASEURL/details.php?id=$id&rechecked=done#Success");
            } elseif (isset($_GET["clearchecked"]) && $_GET["clearchecked"] == 1) {
                sql_query("UPDATE torrents SET checked_by = '' WHERE id =$id LIMIT 1");
                write_log("torrentedit", "Torrent <a href=$BASEURL/details.php?id=$id>($row[name])</a> was un-checked by $CURUSER[username]");
                header("Location: $BASEURL/details.php?id=$id&clearchecked=done#Success");
            }
            if (isset($_GET["checked"]) && $_GET["checked"] == 'done') {

                ?>
<h2><a name='Success'>Successfully checked <?php echo $CURUSER['username']?>!</a></h2>
<?php
            }
            if (isset($_GET["rechecked"]) && $_GET["rechecked"] == 'done') {

                ?>
<h2><a name='Success'>Successfully re-checked <?php echo $CURUSER['username']?>!</a></h2>
<?php
            }
            if (isset($_GET["clearchecked"]) && $_GET["clearchecked"] == 'done') {

                ?>
<h2><a name='Success'>Successfully un-checked <?php echo $CURUSER['username']?>!</a></h2>
<?php
            }
        }
        // end
        $s = $row["name"];
        $descrs = $row["description"];
        echo("<h1>$s</h1>\n");
        echo("<table width=750 border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");

        $url = "edit.php?id=" . $row["id"];
        if (isset($_GET["returnto"])) {
            $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
            $url .= $addthis;
            $keepget .= $addthis;
        }
        $editlink = "a href=\"$url\" class=\"sublink\"";
        // $s = "<b>" . safeChar($row["name"]) . "</b>";
        // if ($owned)
        // $s .= " $spacer<$editlink>[Edit torrent]</a>";
        // tr("Name", $s, 1);
        ///////////hidden or staff only ?
        if ($row["hidden"] == "yes")    
        echo("<tr><td colspan=2 align=center><b><font size=\"+1\"><a class=altlink href=rules.php#hidden>This Torrent Is Hidden!</a></font></b></td></tr>");
        if ($row["staffonly"] == "yes")
        echo("<tr><td colspan=2 align=center><b><font size=\"+1\"><a class=altlink href=#>This Torrent Is Staff Only!</a></font></b></td></tr>");
        //////////////////////
        // / freeleech/doubleseed slots
        $clr = '#EAFF08'; /// font color
        $duration = "+14 days"; /// slots in use get deleted in 14 days
        $freeimg = '<img src="' . $pic_base_url . 'freedownload.gif" border=0"/>';
        $doubleimg = '<img src="' . $pic_base_url . 'doubleseed.gif" border=0"/>';
        $addedup = strtotime($row["addedup"]);
        $expires = strtotime("$duration", $addedup);
        $addup = date('F j, Y', $expires);
        $addedfree = strtotime($row["addedfree"]);
        $expires2 = strtotime("$duration", $addedfree);
        $addfree = date('F j, Y', $expires2);
        $iq = strtotime(get_date_time($iq));
        $xp = strtotime("$duration", $iq);
        $idk = date('F j, Y', $xp);
        $pq = $row["slotid"] == $id && $row["slotuid"] == $CURUSER["id"];
        $frees = $row["freeslot"];
        $doubleup = $row["doubleslot"];
        if ($pq && $frees == 'yes' && $doubleup == 'no') {
            echo '<tr><td align=right class=rowhead>Slots</td><td align=left>' . $freeimg . '  <b><font color="' . $clr . '">Freeleech Slot In Use!</font></b> (only upload stats are recorded) - Expires:  12:01AM ' . $addfree . '</td></tr>';
            $freeslot = ($CURUSER['freeslots'] >= "1" ? "  <b>Use:</b> <a class=\"index\" href=\"doubleseed.php/" . $id . "/" . rawurlencode($row['filename']) . "\" rel=balloon2 onClick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color=" . $clr . "><b>Doubleseed Slot</a></font></b> - " . safeChar($CURUSER[freeslots]) . " Slots Remaining. " : "");
        } elseif ($pq && $frees == 'no' && $doubleup == 'yes') {
            echo '<tr><td align=right class=rowhead>Slots</td><td align=left>' . $doubleimg . '  <b><font color="' . $clr . '">Doubleseed Slot In Use!</font></b> (upload stats x2) - Expires: 12:01AM ' . $addup . '</td></tr>';
            $freeslot = ($CURUSER['freeslots'] >= "1" ? "  <b>Use:</b> <a class=\"index\" href=\"downloadfree.php/" . $id . "/" . rawurlencode($row['filename']) . "\" rel=balloon1 onClick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color=" . $clr . "><b>Freeleech Slot</a></font></b> - " . safeChar($CURUSER[freeslots]) . " Slots Remaining. " : "");
        } elseif ($pq && $doubleup == 'yes' && $frees == 'yes') {
            echo '<tr><td align=right class=rowhead>Slots</td><td align=left>' . $freeimg . ' ' . $doubleimg . '  <b><font color="' . $clr . '">Freeleech and Doubleseed Slots In Use!</font></b> (upload stats x2 and no download stats are recorded)<p>Freeleech Expires: 12:01AM ' . $addfree . ' and Doubleseed Expires: 12:01AM ' . $addup . '</p></td></tr>';
        } else
            $freeslot = ($CURUSER['freeslots'] >= "1" ? "  <b>Use:</b> <a class=\"index\" href=\"downloadfree.php/" . $id . "/" . rawurlencode($row['filename']) . "\" rel=balloon1 onClick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color=" . $clr . "><b>Freeleech Slot</a></font></b>   <b>Use:</b> <a class=\"index\" href=\"doubleseed.php/" . $id . "/" . rawurlencode($row['filename']) . "\" rel=balloon2  onClick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color=" . $clr . "><b>Doubleseed Slot</a></font></b> - " . safeChar($CURUSER['freeslots']) . " Slots Remaining. " : "");

        ?>
<div id="balloon1" class="balloonstyle">
Once chosen this torrent will be Freeleech <?php echo $freeimg?> until <?php echo $idk?> and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.</div>
<div id="balloon2" class="balloonstyle">
Once chosen this torrent will be Doubleseed <?php echo $doubleimg?> until <?php echo $idk?> and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.</div>
<?php
        if ($CURUSER["id"] == $row["owner"]) $CURUSER["downloadpos"] = "yes";
        if ($CURUSER["downloadpos"] != "no") {
            $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
            $percentage = ($ratio * 100);
            echo("<tr><td class=rowhead width=1%>Download</td><td width=99% align=left>");
            if (get_user_class() >= UC_VIP) {
                echo("<a class=\"index\" href=\"download.php/$id/" . rawurlencode($row["filename"]) . "\">" . safeChar($row["filename"]) . "</a>" . $freeslot . "");
            } else {
                $usid = $CURUSER["id"];
                $rs = sql_query("SELECT * FROM users WHERE id='$usid'") or sqlerr();
                $ar = mysql_fetch_assoc($rs);
                $gigs = $ar["downloaded"] / (1024 * 1024 * 1024);
                if (($gigs > "10") && ($ratio <= 0.3 and (!$owned || 0) and ($CURUSER["downloaded"] <> 0))) {
                    echo("<p align=\"center\">");
                    echo("<font color=red><b><u>Download Privileges Removed Please Restart A Old Torrent To Improve Your Ratio!!</font><border=\"1\" cellpadding=\"10\" cellspacing=\"10\"></u></b>");
                    echo("<p><font color=green><b>Your ratio is $ratio</b></font> - meaning that you have only uploaded ");
                    echo("$percentage % ");
                    echo("of the amount you downloaded<p>It's important to maintain a good ");
                    echo("ratio because it helps to make downloads faster for all members </p>");
                    echo("<p><font color=red><b>Tip: </b></font>You can improve your ratio by leaving your torrent ");
                    echo("running after the download completes.<p>You must maintain a minimum ");
                    echo("ratio of 0.3 or your download privileges will be removed<p align=\"center\">");
                    echo("</td></tr>");
                } else
                if ($ratio <= 0.6 and (!$owned || 0) and ($CURUSER["downloaded"] <> 0)) {
                    echo("<p align=\"center\">");
                    echo("<font color=red><b><u>Pay  Attention To Your Ratio</font><border=\"1\" cellpadding=\"10\" cellspacing=\"10\"></u></b>");
                    echo("<p><font color=green><b>Your ratio is $ratio</b></font> - meaning that you have only uploaded ");
                    echo("$percentage % ");
                    echo("of the amount you downloaded<p>It's important to maintain a good ");
                    echo("ratio because it helps to make downloads faster for all members</p>");
                    echo("<p><font color=red><b>Tip: </b></font>You can improve your ratio by leaving your torrent ");
                    echo("running after the download completes.<p>You must maintain a minimum ");
                    echo(" ratio of 0.3 or your download privileges will be removed<p align=\"center\">");
                    echo("<a class=\"index\" href=\"download.php/$id/" . rawurlencode($row["filename"]) . "\">");
                    echo("<font color=green>Click Here To Continue With Your Download</a></font>");
                    echo("</td></tr>");
                } else {
                    echo("<a class=\"index\" href=\"download.php/$id/" . rawurlencode($row["filename"]) . "\">" . safeChar($row["filename"]) . "</a>" . $freeslot . "");
                }
                echo("<td></tr>");
            }

            if ($CURUSER["id"] == $row["owner"]) $CURUSER["downloadpos"] = "yes";
            if ($CURUSER["downloadpos"] != "no") {
                $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
                $percentage = ($ratio * 100);
                echo("<tr><td class=rowhead width=1%>Download Zip</td><td width=99% align=left>");
                if (get_user_class() >= UC_VIP) {
                    echo("<a class=\"index\" href=\"download_zip.php/$id/" . rawurlencode($row["filename"]) . "\">" . safeChar($row["filename"]) . "</a>" . $freeslot . "");
                } else {
                    $usid = $CURUSER["id"];
                    $rs = sql_query("SELECT * FROM users WHERE id='$usid'") or sqlerr();
                    $ar = mysql_fetch_assoc($rs);
                    $gigs = $ar["downloaded"] / (1024 * 1024 * 1024);
                    if (($gigs > "10") && ($ratio <= 0.3 and (!$owned || 0) and ($CURUSER["downloaded"] <> 0))) {
                        echo("<p align=\"center\">");
                        echo("<font color=red><b><u>Download Privileges Removed Please Restart A Old Torrent To Improve Your Ratio!!</font><border=\"1\" cellpadding=\"10\" cellspacing=\"10\"></u></b>");
                        echo("<p><font color=green><b>Your ratio is $ratio</b></font> - meaning that you have only uploaded ");
                        echo("$percentage % ");
                        echo("of the amount you downloaded<p>It's important to maintain a good ");
                        echo("ratio because it helps to make downloads faster for all members </p>");
                        echo("<p><font color=red><b>Tip: </b></font>You can improve your ratio by leaving your torrent ");
                        echo("running after the download completes.<p>You must maintain a minimum ");
                        echo("ratio of 0.3 or your download privileges will be removed<p align=\"center\">");
                        echo("</td></tr>");
                    } else
                    if ($ratio <= 0.6 and (!$owned || 0) and ($CURUSER["downloaded"] <> 0)) {
                        echo("<p align=\"center\">");
                        echo("<font color=red><b><u>PAY ATTENTION TO YOUR RATIO</font><border=\"1\" cellpadding=\"10\" cellspacing=\"10\"></u></b>");
                        echo("<p><font color=green><b>Your ratio is $ratio</b></font> - meaning that you have only uploaded ");
                        echo("$percentage % ");
                        echo("of the amount you downloaded<p>It's important to maintain a good ");
                        echo("ratio because it helps to make downloads faster for all members</p>");
                        echo("<p><font color=red><b>Tip: </b></font>You can improve your ratio by leaving your torrent ");
                        echo("running after the download completes.<p>You must maintain a minimum ");
                        echo(" ratio of 0.3 or your download privileges will be removed<p align=\"center\">");
                        echo("<a class=\"index\" href=\"download.php/$id/" . rawurlencode($row["filename"]) . "\">");
                        echo("<font color=green>Click Here To Continue With Your Download</a></font>");
                        echo("</td></tr>");
                    } else {
                        echo("<a class=\"index\" href=\"download_zip.php/$id/" . rawurlencode($row["filename"]) . "\">" . htmlspecialchars($row["filename"]) . "</a>" . $freeslot . "");
                    }
                    echo("<td></tr>");
                }
            }
            if (get_user_class() >= UC_MODERATOR){
                echo("<tr><td class=rowhead width=10>Download For Dump sites</td><td width=99% align=left><a class=\"index\" href=\"downloaddump.php/$id/" . rawurlencode($row["filename"]) . "\">" . safeChar($row["filename"]) . "</a></td></tr>");
            }
            //////////Ratio after download by pdq
$downl = ($CURUSER["downloaded"] + $row["size"]);
  $sr = $CURUSER["uploaded"] / $downl;
switch (true)
{
    case ($sr >= 4):
        $s = "w00t";
        break;
    case ($sr >= 2):
        $s = "grin";
        break;
    case ($sr >= 1):
        $s = "smile1";
        break;
    case ($sr >= 0.5):
        $s = "noexpression";
        break;
    case ($sr >= 0.25):
        $s = "sad";
        break;
        case ($sr > 0.00):
        $s = "cry";
        break;
    default;
        $s = "w00t";
        break;
}
$sr = floor($sr * 1000) / 1000;
$sr = "<font color='".get_ratio_color($sr)."'>".number_format($sr, 3).
"</font>&nbsp;&nbsp;<img src=\"pic/smilies/{$s}.gif\" alt='' />";

if ($row['countstats'] == 'no'/* || $potfree*/ || $free_for_all)
{
?>
<tr><td align='right' class='heading'>Ratio After Download</td><td><del><?php echo $sr;?>
&nbsp;&nbsp;Your new ratio if you download this torrent.</del> <b><font size="" color="#FF0000">[FREE]</font></b>
(only upload stats are recorded)</td></tr>
<?php
}
else
{    
?>
<tr><td align='right' class='heading'>Ratio After Download</td><td><?php echo $sr;?>
&nbsp;&nbsp;Your new ratio if you download this torrent.</td></tr>
<?php
}

            // / Mod by dokty - tbdev.net
            $blasd = sql_query("SELECT points FROM coins WHERE torrentid=$id AND userid=" . unsafeChar($CURUSER["id"]));
            $sdsa = mysql_fetch_assoc($blasd) or $sdsa["points"] = 0;
            tr("Points", "<b>In total " . safeChar($row["points"]) . " Points given to this torrent of which " . safeChar($sdsa["points"]) . " from you.<br /><br />By clicking on the coins you can give points to the uploader of this torrent.</b><br /><br /><a href=coins.php?id=$id&points=10><img src=pic/10coin.jpg border=0></a>&nbsp;&nbsp;<a href=coins.php?id=$id&points=20><img src=pic/20coin.jpg border=0></a>&nbsp;&nbsp;<a href=coins.php?id=$id&points=50><img src=pic/50coin.jpg border=0></a>&nbsp;&nbsp;<a href=coins.php?id=$id&points=100><img src=pic/100coin.jpg border=0></a>&nbsp;&nbsp;<a href=coins.php?id=$id&points=200><img src=pic/200coin.gif border=0></a>&nbsp;&nbsp;<a href=coins.php?id=$id&points=500><img src=pic/500coin.gif border=0></a>&nbsp;&nbsp;<a href=coins.php?id=$id&points=1000><img src=pic/1000coin.gif border=0></a>", 1);
            // //////////end modified bonus points for uploader///////
            function hex_esc($matches)
            {
                return sprintf("%02x", ord($matches[0]));
            }
            tr("Info hash", preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])));
        } else {
            tr("Download", "You are not allowed to download");
        }
        //////////////////poster mod
        if (!empty($row["poster"]))                                                                                                                                                                           
        tr("".$language['pos']."", "<a href=\"javascript: klappe_news('a3')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica3".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka3\" style=\"display: none;\"><br><a href='" . safeChar($row["poster"]) . "' rel='lightbox' title='" . CutName(safeChar($row["name"]), 35) . "'><img src='" . safeChar($row["poster"]) . "' border=0 width=150></a></div>", 1);
		    else
        tr("".$language['pos']."", "<a href=\"javascript: klappe_news('a3')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica3".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka3\" style=\"display: none;\"><br>Poster Not Available</div>", 1);       
        ///////////////youtube sample//////////////
        if (!empty($row["tube"]))
            tr("".$language['sam']."", "<a href=\"javascript: klappe_news('a2')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica2" . $array['id'] . "\" alt=\"[Hide/Show]\"></a><div id=\"ka2\" style=\"display: none;\"><br><embed src='" . str_replace("watch?v=", "v/", htmlspecialchars($row["tube"])) . "' type=\"application/x-shockwave-flash\" width=\"500\" height=\"410\"></embed></div>", 1);
        else
            tr("".$language['sam']."", "<a href=\"javascript: klappe_news('a2')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica2" . $array['id'] . "\" alt=\"[Hide/Show]\"></a><div id=\"ka2\" style=\"display: none;\"><br>Sample Not Available</div>", 1);
        //////////////////description////////////////
        if ($dtype)
            tr("".$language['desc']."", "<a href=\"javascript: klappe_news('a1')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica1".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka1\" style=\"display: none;\"><br>".format_comment($row["descr"])."</div>", 1);
        else
            tr("".$language['desc']."", "<a href=\"javascript: klappe_news('a1')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica1".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka1\" style=\"display: none;\"><br>".format_urls($row["descr"])."</div>", 1);
        //////////////////small description////  
        if (!empty($row["description"]))                                                                                                                                                                           
        tr("".$language['sdesc']."", "<a href=\"javascript: klappe_news('a5')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica5".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka5\" style=\"display: none;\"><br>$descrs</div>", 1);
		    else
        tr("".$language['sdesc']."", "<a href=\"javascript: klappe_news('a5')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica5".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka5\" style=\"display: none;\"><br>Small Description Not Available</div>", 1);         
        //Auto view nfo
        if (empty($row["descr"])) {
            $r = sql_query("SELECT name,nfo FROM torrents WHERE id=$id") or sqlerr();
            $a = mysql_fetch_assoc($r) or die("Puke");
            $nfo = safeChar($a["nfo"]);
            echo("<h1>NFO for <a href=details.php?id=$id>$a[name]</a></h1>\n");
            echo("<tr><td valign=top alighn=center><b>Description</b></td><td class=text>\n");
            echo("<pre><font face='MS Linedraw' size=2 style='font-size: 10pt; line-height: 10pt'>" . format_urls($nfo) . "</font></pre>\n");
            echo("</td></tr>\n");
        }
        //view nfo
        if (get_user_class() >= UC_POWER_USER && $row["nfosz"] > 0)
            echo("<tr><td class=rowhead>NFO</td><td align=left><a href=viewnfo.php?id=$row[id]><b>View NFO</b></a> (" .
                prefixed($row["nfosz"]) . ")</td></tr>\n");
              ////////////////Auto Imdb
              if (($row["url"] != "")AND(strpos($row["url"], imdb))AND(strpos($row["url"], title)))
              {
              $thenumbers = ltrim(strrchr($row["url"],'tt'),'tt');
              $thenumbers = ereg_replace("[^A-Za-z0-9]", "", $thenumbers);
              $movie = new imdb ($thenumbers);
              $movieid = $thenumbers;
              $movie->setid ($movieid);
              $country = $movie->country ();
              $director = $movie->director();
              $write = $movie->writing();
              $produce = $movie->producer();
              $cast = $movie->cast();
              $plot = $movie->plot ();
              $compose = $movie->composer();
              $gen = $movie->genres();
              
              if (($photo_url = $movie->photo_localurl() ) != FALSE) {
              $smallth = '<img src="'.$photo_url.'">';
              }

              if (!empty($row["url"])) 
              $autodata .= "<a href=\"javascript: klappe_news('a4')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica4" . $array['id'] . "\" alt=\"[Hide/Show]\"></a>&nbsp;Imdb Info<div id=\"ka4\" style=\"display: none;\"><font color=\"green\" size=\"3\">Information:</font><br />\n";
              $autodata .= "<br />\n";
              $autodata .= "<strong><font color=\"yellow\"> Title: </font></strong>" . "".$movie->title ()."<br />\n";
              $autodata .= "<strong><font color=\"red\"> Also known as: </font></strong>";

              foreach ( $movie->alsoknow() as $ak){
              $autodata .= "".$ak["title"]."" . "".$ak["year"].""  . "".$ak["country"]."" . " (" . "".$ak["comment"]."" . ")" . ", ";
              }
              $autodata .= "<br />\n<strong><font color=\"red\"> Year: </font></strong>" . "".$movie->year ()."<br />\n";
              $autodata .= "<strong><font color=\"red\"> Runtime: </font></strong>" . "".$movie->runtime ()."" . " mins<br />\n";
              $autodata .= "<strong><font color=\"red\"> Votes: </font></strong>" . "".$movie->votes ()."<br />\n";
              $autodata .= "<strong><font color=\"red\"> Rating: </font></strong>" . "".$movie->rating ()."<br />\n";
              $autodata .= "<strong><font color=\"red\"> Language: </font></strong>" . "".$movie->language ()."<br />\n";
              $autodata .= "<strong><font color=\"red\"> Country: </font></strong>";
                      
              for ($i = 0; $i + 1 < count ($country); $i++) {
              $autodata .="$country[$i], ";
              }
              $autodata .= "$country[$i]";
              $autodata .= "<br />\n<strong><font color=\"red\"> All Genres: </font></strong>";
              for ($i = 0; $i + 1 < count($gen); $i++) {
              $autodata .= "$gen[$i], ";
              }
              $autodata .= "$gen[$i]";
              $autodata .= "<br />\n<strong><font color=\"red\"> Tagline: </font></strong>" . "".$movie->tagline ()."<br />\n";
              $autodata .= "<strong><font color=\"red\"> Director: </font></strong>";

              for ($i = 0; $i < count ($director); $i++) {
              $autodata .= "<a target=\"_blank\" href=\"http://us.imdb.com/Name?" . "".$director[$i]["imdb"]."" ."\">" . "".$director[$i]["name"]."" . "</a> ";
              }
        
              $autodata .= "<br />\n<strong><font color=\"red\"> Writing By: </font></strong>";
              for ($i = 0; $i < count ($write); $i++) {
              $autodata .= "<a target=\"_blank\" href=\"http://us.imdb.com/Name?" . "".$write[$i]["imdb"]."" ."\">" . "".$write[$i]["name"]."" . "</a> ";
              }
        
              $autodata .= "<br />\n<strong><font color=\"red\"> Produced By: </font></strong>";
              for ($i = 0; $i < count ($produce); $i++) {
              $autodata .= "<a target=\"_blank\" href=\"http://us.imdb.com/Name?" . "".$produce[$i]["imdb"]."" ." \">" . "".$produce[$i]["name"]."" . "</a> ";
              }
              
              $autodata .= "<br />\n<strong><font color=\"red\"> Music: </font></strong>";              
              for ($i = 0; $i < count($compose); $i++) {
              $autodata .= "<a target=\"_blank\" href=\"http://us.imdb.com/Name?" . "".$compose[$i]["imdb"]."" ." \">" . "".$compose[$i]["name"]."" . "</a> ";     
              }

              $autodata .= "<br /><br />\n\n<br />\n";
              $autodata .= "<font color=\"green\" size=\"3\"> Description:</font><br />\n";
              for ($i = 0; $i < count ($plot); $i++) {
              $autodata .= "<br />\n<font color=\"red\">•</font> ";
              $autodata .= "$plot[$i]";
              }      
    
              $autodata .= "<br /><br />\n\n<br />\n";
              $autodata .= "<font color=\"green\" size=\"3\"> Cast:</font><br />\n";
              $autodata .= "<br />\n";

              for ($i = 0; $i < count ($cast); $i++) {
              if ($i > 9) {
                break;
              }
              $autodata .= "<font color=\"red\">•</font> " . "<a target=\"_blank\" href=\"http://us.imdb.com/Name?" . "".$cast[$i]["imdb"]."" ."\">" . "".$cast[$i]["name"]."" . "</a> " . " as <strong><font color=\"red\">" . "".$cast[$i]["role"]."" . " </font></strong><br />\n";              
              }
              trala("$smallth",$autodata,1);
              }
              //end auto imdb
        if ($row["visible"] == "no")
            tr("Visible", "<b>no</b> (dead)", 1);
        if ($moderator)
            tr("Banned", $row["banned"]);
        if ($row["nuked"] == "yes")
            tr("Nuked", $row["nukereason"]);
        elseif ($row["nuked"] == "unnuked")
            tr("Un-nuked", $row["nukereason"]);
        else
        if ($row["nuked"] == "no");
        if (isset($row["cat_name"]))
            tr("" . $language['dt33'] . "", $row["cat_name"]);
        else
            tr("" . $language['dt33'] . "", "(none selected)");
        tr("Genre", $row["newgenre"], 1);
        tr("Last&nbsp;seeder", "Last activity " . safeChar(mkprettytime($row["lastseed"])) . " ago");
        tr("" . $language['dt36'] . "", prefixed($row["size"]) . " (" . safeChar(number_format($row["size"])) . " bytes)");

        $s = "";
        $s .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td valign=\"top\" class=embedded>";
        if (!isset($row["rating"])) {
            if ($minvotes > 1) {
                $s .= "none yet (needs at least $minvotes votes and has got ";
                if ($row["numratings"])
                    $s .= "only " . $row["numratings"];
                else
                    $s .= "none";
                $s .= ")";
            } else
                $s .= "No votes yet";
        } else {
            $rpic = ratingpic($row["rating"]);
            if (!isset($rpic))
                $s .= "invalid?";
            else
                $s .= "$rpic (" . $row["rating"] . " out of 5 with " . $row["numratings"] . " vote(s) total)";
        }
        $s .= "\n";
        $s .= "</td><td class=embedded>$spacer</td><td valign=\"top\" class=embedded>";
        if (!isset($CURUSER))
            $s .= "(<a href=\"login.php?returnto=" . urlencode(substr($_SERVER["REQUEST_URI"], 1)) . "&amp;nowarn=1\">Log in</a> to rate it)";
        else {
            $ratings = array(5 => "Kewl!",
                4 => "Pretty good",
                3 => "Decent",
                2 => "Pretty bad",
                1 => "Sucks!",
                );
            if (!$owned || $moderator) {
                $xres = sql_query("SELECT rating, added FROM ratings WHERE torrent = $id AND user = " . $CURUSER["id"]);
                $xrow = mysql_fetch_assoc($xres);
                if ($xrow)
                    $s .= "(you rated this torrent as \"" . $xrow["rating"] . " - " . $ratings[$xrow["rating"]] . "\")";
                else {
                    $s .= "<form method=\"post\" action=\"takerate.php\"><input type=\"hidden\" name=\"id\" value=\"$id\" />\n";
                    $s .= "<select name=\"rating\">\n";
                    $s .= "<option value=\"0\">(add rating)</option>\n";
                    foreach ($ratings as $k => $v) {
                        $s .= "<option value=\"$k\">$k - $v</option>\n";
                    }
                    $s .= "</select>\n";
                    $s .= "<input type=\"submit\" value=\"Vote!\" />";
                    $s .= "</form>\n";
                }
            }
        }
        $s .= "</td></tr></table>";
        tr("" . $language['dtrate2'] . "", $s, 1);
        if (get_user_class() >= UC_MODERATOR) {
            if (!$_GET["ratings"])
                tr("" . $language['dtrate1'] . "<br /><a href=\"details.php?id=$id&amp;ratings=1$keepget#ratings\" class=\"sublink\">[" . $language['dt47'] . "]</a>", $row["numratings"] . " ratings", 1);
            else {
                tr("" . $language['dtrate1'] . "", $row["numratings"] . " ratings", 1);
                $s = "<table class=main border=\"1\" cellspacing=0 cellpadding=\"5\">\n";
                $ratings = sql_query("SELECT r.rating, r.added, u.username,u.id
FROM ratings AS r
INNER JOIN users AS u ON r.user = u.id
WHERE r.torrent =$id
ORDER BY u.username DESC");
                $s .= "<tr><td class=colhead>User</td><td class=colhead align=right>rate</td><td class=colhead align=right>Date</td></tr>\n";
                while ($r_row = mysql_fetch_assoc($ratings)) {
                    $s .= "<tr><td><a href=userdetails.php?id=" . $r_row["id"] . ">" . htmlspecialchars($r_row["username"]) . "</a></td><td align=\"right\">" . $r_row["rating"] . "</td><td align=\"right\">" . date("d-m-Y", strtotime($r_row["added"])) . "</td></tr>\n";
                }
                $s .= "</table>\n";
                tr("<a name=\"filelist\">" . $language['dtrate'] . "</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">[" . $language['dt52'] . "]</a>", $s, 1);
            }
        }
        ////////////// Similar Torrents mod /////////////////////  
   $searchname = substr($row['name'], 0, 6);
   $query1 = str_replace(" ",".",sqlesc("%".$searchname."%"));
   $query2 = str_replace("."," ",sqlesc("%".$searchname."%"));
   $r = sql_query("SELECT id, name, size, added, seeders, leechers, category FROM torrents WHERE name LIKE {$query1} AND id <> '$id' OR name LIKE {$query2} AND id <> '$id' ORDER BY name") or sqlerr();
   if (mysql_num_rows($r) > 0)
   {
   $torrents = "<table width=100% class=main border=1 cellspacing=0 cellpadding=5>\n" .
   "<tr><td class=colhead width=20>Type</td><td class=colhead>Name</td><td class=colhead align=center>Size</td><td class=colhead align=center>Added</td><td class=colhead align=center>Seeders</td><td class=colhead align=center>Leechers</td></tr>\n";
   while ($a = mysql_fetch_assoc($r))
   {
   $r2 = sql_query("SELECT name, image FROM categories WHERE id=$a[category]") or sqlerr(__FILE__, __LINE__);
   $a2 = mysql_fetch_assoc($r2);
   $cat = "<img src=\"/pic/$a2[image]\" alt=\"$a2[name]\">";
   $name = $a["name"];
   $torrents .= "<tr><td style='padding: 0px; border: none' width=40px>$cat</td><td><a href=details.php?id=" . $a["id"] . "&hit=1><b>" . safeChar($name) . "</b></a></td><td style='padding: 1px' align=center>". prefixed($a[size]) ."</td><td style='padding: 1px' align=center>$a[added]</td><td style='padding: 1px' align=center>$a[seeders]</td><td style='padding: 1px' align=center>$a[leechers]</td></tr>\n";
   }
   $torrents .= "</table>";
   tr("".$language['sim']."", "<a href=\"javascript: klappe_news('a8')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica8".$array['id']."\" alt=\"[Hide/Show]\"></a><div id=\"ka8\" style=\"display: none;\"><br>$torrents</div>", 1);
   }
   /////////////////////////////////////////////////////////
        // ///////////Vote For FreeLeech////////
        if ($CURUSER["class"] < UC_VIP) {
            $ratio1 = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
            if ($ratio1 < 0.55) $wait1 = 5;
            elseif ($ratio1 < 0.45) $wait1 = 10;
            elseif ($ratio1 < 0.35) $wait1 = 15;
            elseif ($ratio1 < 0.25) $wait1 = 20;
            elseif ($ratio1 < 0.15) $wait1 = 25;
            else $wait1 = 0;
        }
        $elapsed1 = floor((time() - strtotime($row["added"])) / 3600);
        $torrentid = 0 + $row["id"];
        $freepoll_sql = sql_query("SELECT userid FROM freepoll where torrentid=" . unsafeChar($torrentid) . "");
        $freepoll_all = mysql_numrows($freepoll_sql);
        if ($freepoll_all) {
            while ($rows_t = mysql_fetch_array($freepoll_sql)) {
                $freepoll_userid = $rows_t["userid"];
                $user_sql = sql_query("SELECT id, username FROM users where id=" . unsafeChar($freepoll_userid) . "");
                $rows_a = mysql_fetch_array($user_sql);
                $username_t = $rows_a["username"];
                $freepollby1 = $freepollby1 . "<a href='userdetails.php?id=$freepoll_userid'>$username_t</a>, ";
            }
            $t_userid = 0 + $CURUSER["id"];
            $tsqlcount = sql_query("SELECT COUNT(*) as tcount FROM freepoll where torrentid=" . unsafeChar($torrentid) . "");
            $tass = mysql_fetch_assoc($tsqlcount);
            $freepollcount = $tass["tcount"];
            $tsql = sql_query("SELECT COUNT(*) FROM freepoll where torrentid=" . unsafeChar($torrentid) . " and userid=" . unsafeChar($t_userid) . "");
            $trows = mysql_fetch_array($tsql);
            $t_ab = $trows[0];
            if ($t_ab == "0") {
                $freepollby = $freepollby . " <form action=\"freepoll.php\" method=\"post\">
    <br />
    <input type=\"submit\" name=\"submit\" value=\"Vote\">
    <input type=\"hidden\" name=\"torrentid\" value=\"$torrentid\">
    </form>";
            } else {
                $t_userid == $row["owner"];
                $freepollby = $freepollby . " <form action=\"freepoll.php\" method=\"post\">
    <br />
    <input type=\"submit\" name=\"submit\" value=\"Already voted\" disabled>
    <input type=\"hidden\" name=\"torrentid\" value=\"$torrentid\">
    </form>";
            }
        } else {
            $freepollcount = "0";
            $freepollby = "
    <form action=\"freepoll.php\" method=\"post\">
    <br />
    <input type=\"submit\" name=\"submit\" value=\"Vote\">
    <input type=\"hidden\" name=\"torrentid\" value=\"$torrentid\">
    </form>
    ";
        }
        $votesrequired = "15";
        $count = $votesrequired - $freepollcount;
        if ($row["countstats"] == 'yes') {
            tr("<b>" . $language['dtfp7'] . "</b>", "" . safechar($freepollcount) . " " . $language['dtfp8'] . " " . safeChar($count) . " " . $language['dtfp9'] . "", 1);
        }
        if ($elapsed < $wait AND ($row["countstats"]) == 'yes')
            if ($t_ab == "0" AND ($row["countstats"]) == 'yes') {
                if ($freepollcount < $votesrequired)
                    echo("<tr><td class=rowhead><div align='right'>" . $language['dtfp7'] . "</div></td><td align=left>$freepollby");
            } else
                echo("<tr><td class=rowhead><div align='right'>" . $language['dtfp4'] . "</div></td><td align=left>" . $language['dtfp5'] . " <b><a href=rules.php><font color=red>" . number_format($wait1 - $elapsed1) . " " . $language['dtfp6'] . "</font></b></a>!");
            elseif ($row["countstats"] == 'yes')
                echo("<tr><td class=rowhead><div align='right'>" . $language['dtfp3'] . "</div></td><td align=left>$freepollby");
            $tid = $row["id"];
            if ($freepollcount == $votesrequired || $row["countstats"] == 'no') {
                echo("<tr><td class=rowhead><div align='right'>" . $language['dtfp'] . "</div></td><td align=left>" . $language['dtfp2'] . "");
                sql_query("UPDATE torrents SET countstats = 'no' WHERE torrents.id=" . unsafeChar($tid) . "") or sqlerr(__FILE__, __LINE__);
            }
            if ($freepollcount < $votesrequired AND $row["countstats"] == 'yes')
                echo("<tr><td class=rowhead><div align='right'>" . $language['dtfp'] . "</div></td><td align=left>" . $language['dtfp1'] . "");
            // /////////////////end vote for freeleech//////////////////////
            $doubles = ($double_for_all ? '<tr><td align=right class=rowhead>Doubleseed</td>
    <td align=left><img src=' . $pic_base_url . 'doubleseed.gif title=Doubleseed alt=Doubleseed />
    <b><font size="2" color="#FF0000">Double Seed Torrent</font></b> <small><b>(upload stats count double)</b></small></td></tr>' : '');
            echo $doubles;
            $free = ($free_for_all ? '<tr><td align=right class=rowhead>free leech</td>
    <td align=left><img src=' . $pic_base_url . 'freedownload.gif title=freeleech alt=freeleech />
    <b><font size="2" color="#FF0000">free Leech Torrent</font></b> <small><b>(Only upload stats count!)</b></small></td></tr>' : '');
            echo $free;
            tr("" . $language['dt37'] . "", $row["added"]);
            tr("" . $language['dt38'] . "", $row["views"]);
            tr("" . $language['dt39'] . "", $row["hits"]);
            if (get_user_class() >= UC_MODERATOR) {
                tr("" . $language['dt40'] . "", ($row["times_completed"] > 0 ? "<a href=snatches.php?id=$id>" . safeChar($row["times_completed"]) . " time(s)</a>" : "0 times"), 1);
            } else
                tr("" . $language['dt40'] . "", ($row["times_completed"] > 0 ? "" . safeChar($row["times_completed"]) . " time(s)</a>" : "0 times"), 1);
            // Totaltraffic mod
            $data = sql_query("SELECT (t.size * t.times_completed + SUM(p.downloaded) + t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.uploaded > '0' AND p.downloaded > '0' AND p.torrent = '$id' AND times_completed > 0 GROUP BY t.id ORDER BY added ASC LIMIT 15") or sqlerr(__FILE__, __LINE__);
            $a = mysql_fetch_assoc($data);
            $data = prefixed($a["data"]) . "";
            tr("" . $language['dttraffic'] . "", $data);
            // Progressbar Mod
            $seedersProgressbar = array();
            $leechersProgressbar = array();
            $resProgressbar = sql_query("SELECT p.seeder, p.to_go, t.size FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.torrent = '$id'") or sqlerr();
            $progressPerTorrent = 0;
            $iProgressbar = 0;
            while ($rowProgressbar = mysql_fetch_array($resProgressbar)) {
                $progressPerTorrent += sprintf("%.2f", 100 * (1 - ($rowProgressbar["to_go"] / $rowProgressbar["size"])));
                $iProgressbar++;
            }
            if ($iProgressbar == 0)
                $iProgressbar = 1;
            $progressTotal = sprintf("%.2f", $progressPerTorrent / $iProgressbar);
            tr("" . $language['dtprog'] . "", get_percent_completed_image(floor($progressTotal)) . " (" . round($progressTotal) . "%)", 1);
            // Progressbar Mod End
            $keepget = "";
            if ($row['anonymous'] == 'yes') {
                if (get_user_class() < UC_UPLOADER)
                    $uprow = "<i>Anonymous</i>";
                else
                    $uprow = "<i>Anonymous</i> (<a href=userdetails.php?id=$row[owner]><b>$row[username]</b></a>)";
            } else {
                $uprow = (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . "><b>" . safeChar($row["username"]) . "</b></a>") : "<i>(unknown)</i>");
            }
            if ($owned)
                $uprow .= " $spacer<$editlink><b>[" . $language['dt44'] . "]</b></a>";
            tr("" . $language['dt45'] . "", $uprow, 1);
            // start torrent mod check
            if ($CURUSER['class'] >= UC_MODERATOR) {
                if (!empty($row['checked_by'])) {
                    $checked_by = sql_query("SELECT id FROM users WHERE username='$row[checked_by]'");
                    $checked = mysql_fetch_array($checked_by);

                    ?>
                                 <tr><td class='rowhead'><?php echo $language['dtcheck']; ?></td><td align='left'><a href='userdetails.php?id=<?php echo $checked['id']?>'><strong><?php echo $row['checked_by']?></strong></a>&nbsp;
                                 <img src='<?php echo $pic_base_url?>mod.gif' width='15' border='0' alt='Checked' title='Checked - by <?php echo safe($row['checked_by'])?>' />
                                 <a href='details.php?id=<?php echo $row['id']?>&amp;rechecked=1'><small><em><strong>[<?php echo $language['dtcheck1']; ?>]</strong></em></small></a> &nbsp;<a href='details.php?id=<?php echo $row['id']?>&amp;clearchecked=1'><small><em><strong>[<?php echo $language['dtcheck2']; ?>]</strong></em></small></a> &nbsp;<?php echo $language['dtcheck4']; ?></td></tr>
                                 <?php
                } else {

                    ?>
                                 <tr><td class='rowhead'><?php echo $language['dtcheck']; ?></td><td align='left'><font color='#ff0000'><strong><?php echo $language['dtcheck5']; ?></strong></font>&nbsp;<a href='details.php?id=<?php echo $row['id']?>&amp;checked=1'><small><em><strong>[<?php echo $language['dtcheck3']; ?>]</strong></em></small></a> &nbsp;<?php echo $language['dtcheck4']; ?></td></tr>
                                 <?php
                }
            }
            // //////////////////// torrent check end - pdq
            $bookmarks = get_row_count("bookmarks", "WHERE torrentid=" . $id . " AND private ='no'");
            if ($bookmarks > 0)
                tr("" . $language['dtbook'] . "", "<a href=\"viewbookmarks.php?id=" . $id . "\">$bookmarks" . ($bookmarks == 1 ? " " . $language['dttime'] . "</a>" : " " . $language['dttimes'] . "</a>"), 1);
            else
                tr("" . $language['dtbook'] . "", "" . $language['dtnot'] . "");

            if ($row["type"] == "multi") {
                if (!$_GET["filelist"])
         	  tr("File list<br /><div id=\"hidefile\" class=\"sublink\"><a href=\"javascript:filelist();\" class=\"sublink\">[See full list]</a>","<div id=\"filelist\">". $row["numfiles"] . " <b>File(s)</b></div>",$s,  1);
            }
            if (!$_GET["dllist"]) {
                tr("" . $language['report'] . "", "<form action=report.php?type=Torrent&id=$id method=post><input class=button type=submit name=submit value=\"" . $language['report1'] . "\"> for breaking the <a href=rules.php>rules</a></form>", 1);
                ////////////////////////ajax peer list///////////        
                tr("<a name=\"seeders\"></a>Peer(s)<br /><div id=\"nopeerlist\" class=\"sublink\"><a href=\"javascript:peerlist($id, 1)\" class=\"sublink\">[View peer list]</a>","<b><div id=\"peerlist\">". $row["seeders"] . " seeder(s), " . $row["leechers"] . " leecher(s) = " . ($row["seeders"] + $row["leechers"]) . " peer(s) total.</b></div>", 1);
                if ($row["seeders"] == 0) {
                echo("<form method=post action=takereseed.php?reseedid=$id><tr><td align=center class=clearalt4 colspan=2><table><tr><td align=center class=clearalt4>" . "<input class=button type=submit value='Request Reseed'></td></form></td></tr></table></td></tr>");
                }
                }
            if (get_user_class() >= UC_ADMINISTRATOR) {
                $filename = "include/banned_clients.txt";
                if (filesize($filename) == 0 || !file_exists($filename))
                    $banned_clients = array();
                else {
                    $handle = fopen($filename, "r");
                    $banned_clients = unserialize(fread($handle, filesize($filename)));
                    fclose($handle);
                }
                if (!empty($banned_clients))
                    echo("<tr><td class=rowhead>" .$language['dtbc'] . "</td><td align=left><a href='client_clearban.php?returnto=" . urlencode("details.php?id=" . $row["id"]) . "'><b>" . $language['dtbc1'] . "</b></a></td></tr>");
            }
            echo("</table></p>\n");
        } else {
            stdhead("" . $language['dt60'] . " \"" . safeChar($row["name"]) . "\"");
            echo("<h1>" . $language['dt61'] . "<a href=details.php?id=$id>" . safeChar($row["name"]) . "</a></h1>\n");
        }
        if ($row["allow_comments"] == "yes" || get_user_class() >= UC_MODERATOR && get_user_class() <= UC_CODER) {
            echo("<p><a name=\"startcomments\"></a></p>\n");
//===hide hidden and staff torrent comments===//
if ($row["hidden"] == "yes") {
$commentbar = "<p align=center><a class=altlink href=$BASEURL/hiddencomment.php?action=add&amp;tid=$id>Add a hidden comment</a></p>\n";
$subres = mysql_query("SELECT COUNT(*) FROM comments WHERE hidden = $id");
$subrow = mysql_fetch_array($subres);
$count = $subrow[0];

if (!$count) {
echo("<h3>No comments yet</h3>\n");
}
else {
list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array(lastpagedefault => 1));

$subres = mysql_query("SELECT comments.id, text, user, comments.added, UNIX_TIMESTAMP(comments.added) as utadded, UNIX_TIMESTAMP(editedat) as uteditedat, editedby, editedat, avatar, warned, ".
"username, title, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE hidden = " .
"$id ORDER BY comments.id $limit") or sqlerr(__FILE__, __LINE__);
$allrows = array();
while ($subrow = mysql_fetch_array($subres))
$allrows[] = $subrow;

echo($commentbar);

echo($pagertop);

echo("comments can not be seen be regular users.<br>");

hiddencommenttable($allrows);

echo($pagerbottom);
}

echo($commentbar);
}
//===staff
else if ($row["staffonly"] == "yes") {
$commentbar = "<p align=center><a class=altlink href=$BASEURL/staffcomment.php?action=add&amp;tid=$id>Add a staff comment</a></p>\n";
$subres = mysql_query("SELECT COUNT(*) FROM comments WHERE staff = $id");
$subrow = mysql_fetch_array($subres);
$count = $subrow[0];

if (!$count) {
echo("<h3>No comments yet</h3>\n");
}
else {
list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array(lastpagedefault => 1));

$subres = mysql_query("SELECT comments.id, text, user, comments.added, UNIX_TIMESTAMP(comments.added) as utadded, UNIX_TIMESTAMP(editedat) as uteditedat, editedby, editedat, avatar, warned, ".
"username, title, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE staff = " .
"$id ORDER BY comments.id $limit") or sqlerr(__FILE__, __LINE__);
$allrows = array();
while ($subrow = mysql_fetch_array($subres))
$allrows[] = $subrow;

echo($commentbar);

echo($pagertop);

echo("comments can only be seen by staff.<br>");

staffcommenttable($allrows);

echo($pagerbottom);
}

echo($commentbar);
}
} else {
            echo("<table style=\"margin-top: 2px;\" cellpadding=\"5\" width=\"100%\">");
            echo("<tr><td class=colhead align=\"left\" colspan=\"2\">  <a name=comments>&nbsp;</a><b>" . $language['dtcdisable'] . "</b></td></tr>");
            echo("</table>");
            stdfoot();
            die();
}
        $postallowed = 1;
        if ($CURUSER['comment_max'] == 0) $postallowed = 0;
        if ($postallowed AND (!($CURUSER['comment_count'] < $CURUSER['comment_max']))) $postallowed = 2;

        switch ($postallowed) {
            case 0:
                $commentbar = "<p align=center>" . $language['dtrevoked'] . "</p>\n";
                break;
            case 1:
                $commentbar = "<p align=center><a class=index href=comment.php?action=add&tid=$id>".$language['dt65']."</a></p>\n <a class=index href=takethankyou.php?id=$id> <img src=" . $pic_base_url . "thankyou.gif border=0></a></p>";
                break;
            case 2:
                $commentbar = "<p align=center>" . $language['dtnocom'] . "</p>\n";
            default:
                die('Contact Administrator');
                break;
        }

        $subres = sql_query("SELECT COUNT(*) FROM comments WHERE torrent = " . unsafeChar($id) . "");
        $subrow = mysql_fetch_array($subres);
        $count = $subrow[0];

        $tures = sql_query("SELECT id,username FROM users,thanks WHERE users.id = thanks.uid AND thanks.torid = " . unsafeChar($id) . "");

        begin_main_frame();
        end_main_frame();

        if (!$count) {
            echo("<h2>" . $language['dt64'] . "</h2>\n");
        } else {
            list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array("lastpagedefault" => 1));

            $subres = sql_query("SELECT comments.id, text, user, comments.added, comments.anonymous, editedby, editedat, avatar, warned, " . "username, title, class, signature, signatures, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = " . "$id ORDER BY comments.id $limit") or sqlerr(__FILE__, __LINE__);
            $allrows = array();
            while ($subrow = mysql_fetch_assoc($subres))
            $allrows[] = $subrow;

            echo($commentbar);
            echo($pagertop);

            commenttable($allrows);

            echo($pagerbottom);
        }
echo($commentbar);
}
?>
<script type="text/javascript">
var url = window.location.href;
var pos = url.indexOf('#seeders');
var pos2 = url.indexOf('#leechers');
if(pos > -1)
{
peerlist(<?php echo $id; ?>, 1);
}
else if(pos2 > -1)
{
peerlist(<?php echo $id; ?>, 2);
}
//ajax peers fadebox 
var kvar = 100;
var object;
function fetch(obj)
{
  obj.style.opacity = kvar/100;
  obj.style.filter = 'alpha(opacity = ' + kvar + ')';
  kvar = 0;
  object = obj;
  startFade();
}
function startFade()
{
  object.style.opacity = kvar/100;
  object.style.filter = 'alpha(opacity = ' + kvar + ')';  
  kvar += 5;
  if(kvar < 95)
    setTimeout("startFade()", 15);
}
//end ajax peers fadebox

</script>
<?php
stdfoot();
?>
