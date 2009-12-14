<?php
// if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
require_once("include/user_functions.php");
function readMore($text, $char, $link)
{
    return (strlen($text) > $char ? substr(safeChar($text), 0, $char-1) . "...<br/><a href=$link>Read more...</a>": safeChar($text));
}

function torrenttable($records, $variant = "index")
{
    global $pic_base_url, $DEFAULTBASEURL, $config, $php_file, $page_find, $lang_off, $language, $CURUSER, $ss_uri, $waiton, $wait1, $wait2, $wait3, $wait4, $oldtorrents, $progress, $cat_ico_uri;
	
	$q = sql_query("select count(id) as num, YEAR(added) as year, MONTH(added) as month , DAY(added) as day FROM torrents  group by year,month,day ORDER BY day,month,year DESC") or print("error");
	while($a= mysql_fetch_assoc($q))
		$split[$a["year"].$a["month"].$a["day"]] = $a["num"];
		
    if ((bool)$waiton) {
        if ($CURUSER["class"] < UC_VIP) {
            $gigs = $CURUSER["uploaded"] / (1024 * 1024 * 1024);
            $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
            if ($ratio < 0.5 || $gigs < 5) $wait = $wait1;
            elseif ($ratio < 0.65 || $gigs < 6.5) $wait = $wait2;
            elseif ($ratio < 0.8 || $gigs < 8) $wait = $wait3;
            elseif ($ratio < 0.95 || $gigs < 9.5) $wait = $wait4;
            else $wait = 0;
        }
    }
    if (get_user_class() >= UC_MODERATOR) {
        echo("<form method=post action=deltorrent.php?mode=delete>");
    }

    ?>
<table border="1" cellspacing=0 cellpadding=5 onMouseover="changeto(event, '#1E1E2A')" onMouseout="changeback(event, 'black')">
<tr>
<?php
    // sorting by MarkoStamcar // modified by xuzo :))
    $oldlink = '';
    $count_get = 0;
    $char = '';
    if (!isset($wait))
        $wait = 0;
    $description = '';
    $preres = '';
    $type = '';
    $sort = '';
    $row = '';
    foreach ($_GET as $get_name => $get_value) {
        $get_name = mysql_escape_string(strip_tags(str_replace(array("\"", "'"), array("", ""), $get_name)));

        $get_value = mysql_escape_string(strip_tags(str_replace(array("\"", "'"), array("", ""), $get_value)));

        if ($get_name != "sort" && $get_name != "type") {
            if ($count_get > 0) {
                $oldlink = $oldlink . "&amp;" . $get_name . "=" . $get_value;
            } else {
                $oldlink = ($oldlink) . $get_name . "=" . $get_value;
            }
            $count_get++;
        }
    }

    if ($count_get > 0) {
        $oldlink = $oldlink . "&amp;";
    }

    if (isset($_GET["sort"]) && $_GET["sort"] == "1") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link1 = "asc";
        } else {
            $link1 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "2") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link2 = "asc";
        } else {
            $link2 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "3") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link3 = "asc";
        } else {
            $link3 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "4") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link4 = "asc";
        } else {
            $link4 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "5") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link5 = "asc";
        } else {
            $link5 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "6") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link6 = "asc";
        } else {
            $link6 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "7") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link7 = "asc";
        } else {
            $link7 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "8") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link8 = "asc";
        } else {
            $link8 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "9") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link9 = "asc";
        } else {
            $link9 = "desc";
        }
    }
    if (isset($_GET["sort"]) && $_GET["sort"] == "10") {
        if (isset($_GET["type"]) && $_GET["type"] == "desc") {
            $link10 = "asc";
        } else {
            $link10 = "desc";
        }
    }

    if (empty($link1)) {
        $link1 = "asc";
    } // for torrent name
    if (empty($link2)) {
        $link2 = "desc";
    }
    if (empty($link3)) {
        $link3 = "desc";
    }
    if (empty($link4)) {
        $link4 = "desc";
    }
    if (empty($link5)) {
        $link5 = "desc";
    }
    if (empty($link6)) {
        $link6 = "desc";
    }
    if (empty($link7)) {
        $link7 = "desc";
    }
    if (empty($link8)) {
        $link8 = "desc";
    }
    if (empty($link9)) {
        $link9 = "desc";
    }
    if (empty($link10)) {
        $link10 = "desc";
    }

    ?>
<td class="colhead" align="center"><?php echo $language['type'];?></td>
<td class="colhead" align="left"><a href="browse.php?<?php echo $oldlink;
    ?>sort=1&amp;type=<?php echo $link1;
    ?>"><?php echo $language['name'];?></a></td>
    <td class="colhead" align="left"><?php echo $language['subs'];?></td>
    <?php
    echo ($variant == 'index' ? '<td class=colhead align=center><a href="bookmarks.php"><img src="' . $pic_base_url . 'bookmark.gif"  border="0" alt="Bookmark" title="Bookmark" /></a></td>' : '');

    if ((bool)$waiton) {
        print("<td class=\"colhead\" align=\"center\">".$language['wait']."</td>\n");
    }
    if ($oldtorrents) {
        ?>
    <td class="colhead" align="center"><a href="browse.php?<?php echo $oldlink;
        ?>sort=4&amp;type=<?php echo $link4;
        ?>">&nbsp;&nbsp;<img src=pic/added.gif border=0 alt=TTL /></a></td>
    <?php
    }
    ?>
    <td class="colhead" align="left"><a href="browse.php?<?php echo $oldlink;
    ?>sort=2&amp;type=<?php echo $link2;
    ?>">&nbsp;&nbsp;&nbsp;<img src=pic/files.gif border=0 alt=Files /></a></td>
    <td class="colhead" align="left"><a href="browse.php?<?php echo $oldlink;
    ?>sort=3&amp;type=<?php echo $link3;
    ?>"><img src=pic/comments.gif border=0 alt=Comments /></a></td>
<td class="colhead" align="center"><img src="pic/download.gif" border=0 alt=download /></td>
<td class="colhead" align="center"><?php echo $language['prog'];?></td>
<td class="colhead" align="center"><a href="browse.php?<?php echo $oldlink;
    ?>sort=6&amp;type=<?php echo $link6;
    ?>"><?php echo $language['size'];?></a></td>
<td class="colhead" align="center"><a href="browse.php?<?php echo $oldlink;
    ?>sort=7&amp;type=<?php echo $link7;
    ?>">&nbsp;&nbsp;<img src=pic/top2.gif border=0 alt=Snatched /></a></td>
<td class="colhead" align="center"><a href="browse.php?<?php echo $oldlink;
    ?>sort=8&amp;type=<?php echo $link8;
    ?>">&nbsp;&nbsp;<img src=pic/arrowup2.gif border="0" alt=Seeders />&nbsp;&nbsp;</a></td>
<td class="colhead" align="center"><a href="browse.php?<?php echo $oldlink;
    ?>sort=9&amp;type=<?php echo $link9;
    ?>">&nbsp;&nbsp;<img src=pic/arrowdown2.gif border="0" alt=Leechers />&nbsp;&nbsp;</a></td>
<?php
    if ($variant == "index")
        echo("<td class=\"colhead\" align=\"center\"><a href=\"browse.php?{$oldlink}sort=9&amp;type={$link9}\"><img border=0 src=\"/pic/upper.gif\" alt=\"Upped By\" /></a></td>\n");
    if (get_user_class() >= UC_MODERATOR) {
        echo("<td class=\"colhead\" align=center>" .$language['delete']. "</td>\n");
    }
    echo("</tr>\n");

    foreach ($records as $row) {
        // while ($row = mysql_fetch_assoc($res)) {
        if (($CURUSER['split'] == "yes") && ($_SERVER["REQUEST_URI"] == "/browse.php") && !isset($_GET["page"])) {
            /**
            *
            * @author StarionTurbo
            * @copyright 2007
            * @modname Show torrents by day
            * @version v1.0
            */
            /**
            * * Make some date varibles *
            */
            $day_added = $row['added'];
            $day_show = strtotime($day_added);
            $thisdate = date('Y-m-d', $day_show);
            $thisdate2 = date("Ynj", $day_show);
            /**
            * * If date already exist, disable $cleandate varible *
            */
            // if($thisdate==$prevdate){
            if (isset($prevdate) && $thisdate == $prevdate) {
                $cleandate = '';
                /**
                * * If date does not exist, make some varibles *
                */
            } else {
				$num = isset($split[$thisdate2]) ? $split[$thisdate2] : 0;
                $day_added = 'Upped on ' . date('l, j. M', strtotime($row['added'])); // You can change this to something else
                $cleandate = "<tr><td colspan=\"15\"><b>$day_added (".$num." torrent".($num > 1 ? "s" : "").")</b></td></tr>\n"; // This also...
            }
            /**
            * * Prevent that "torrents added..." wont appear again with the same date *
            */
            $prevdate = $thisdate;
            $man = array('Jan' => 'January',
                'Feb' => 'February',
                'Mar' => 'March',
                'Apr' => 'April',
                'May' => 'May',
                'Jun' => 'June',
                'Jul' => 'July',
                'Aug' => 'August',
                'Sep' => 'September',
                'Oct' => 'October',
                'Nov' => 'November',
                'Dec' => 'December'
                );
            foreach($man as $eng => $ger) {
                $cleandate = str_replace($eng, $ger, $cleandate);
            }
            $dag = array('Mon' => 'Monday',
                'Tues' => 'Tuesday',
                'Wednes' => 'Wednesday',
                'Thurs' => 'Thursday',
                'Fri' => 'Friday',
                'Satur' => 'Saturday',
                'Sun' => 'Sunday'
                );
            foreach($dag as $eng => $ger) {
                $cleandate = str_replace($eng . 'day', $ger . '', $cleandate);
            }
            /**
            * * If torrents not listed by added date *
            */
            if ($row["sticky"] == "no") // delete this line if you dont have sticky torrents or you want to display the addate for them also
            if (!isset($_GET['sort']) && (!isset($_GET['d']))) {
            echo $cleandate . "\n";
            }
            } //ends the condition
            // ///standard sticky torrent hlight////////
            /*
            $id = $row["id"];
            if ($row["sticky"] == "yes"){
            echo("<tr class=highlight>\n");
            } else {
            echo("<tr>\n");
            }*/
           // ////End Sticky only highlight/////////////////
            // /////highlight torrenttable////////////////
            $id = $row['id'];
            if ($CURUSER["ttablehl"] != "yes") {
                echo'<tr>';
            } else {
                $countstatsclr = ($CURUSER['stylesheet'] == "1"?"teal":"") . ($CURUSER['stylesheet'] == "2"?"teal":"") . ($CURUSER['stylesheet'] == "3"?"teal":"") . ($CURUSER['stylesheet'] == "4"?"teal":"") . ($CURUSER['stylesheet'] == "5"?"teal":""). ($CURUSER['stylesheet'] == "6"?"teal":""). ($CURUSER['stylesheet'] == "7"?"teal":"");
                $nukedclr = ($CURUSER['stylesheet'] == "1"?"red":"") . ($CURUSER['stylesheet'] == "2"?"red":"") . ($CURUSER['stylesheet'] == "3"?"red":"") . ($CURUSER['stylesheet'] == "4"?"red":"") . ($CURUSER['stylesheet'] == "5"?"red":""). ($CURUSER['stylesheet'] == "6"?"red":""). ($CURUSER['stylesheet'] == "7"?"red":"");
                $sceneclr = ($CURUSER['stylesheet'] == "1"?"orange":"") . ($CURUSER['stylesheet'] == "2"?"orange":"") . ($CURUSER['stylesheet'] == "3"?"orange":"") . ($CURUSER['stylesheet'] == "4"?"orange":"") . ($CURUSER['stylesheet'] == "5"?"orange":""). ($CURUSER['stylesheet'] == "6"?"orange":""). ($CURUSER['stylesheet'] == "7"?"orange":"");
                $requestclr = ($CURUSER['stylesheet'] == "1"?"#777777":"") . ($CURUSER['stylesheet'] == "2"?"#777777":"") . ($CURUSER['stylesheet'] == "3"?"#777777":"") . ($CURUSER['stylesheet'] == "4"?"#777777":"") . ($CURUSER['stylesheet'] == "5"?"#777777":""). ($CURUSER['stylesheet'] == "6"?"#777777":""). ($CURUSER['stylesheet'] == "7"?"#777777":"");
                $stickyclr = ($CURUSER['stylesheet'] == "1"?"gold":"") . ($CURUSER['stylesheet'] == "2"?"gold":"") . ($CURUSER['stylesheet'] == "3"?"gold":"") . ($CURUSER['stylesheet'] == "4"?"gold":"") . ($CURUSER['stylesheet'] == "5"?"gold":""). ($CURUSER['stylesheet'] == "6"?"gold":""). ($CURUSER['stylesheet'] == "7"?"gold":"");
                $hl = ($row['countstats'] == "no" && $row['nuked'] == "no"?$countstatsclr:"") . ($row['scene'] == "yes" && $row['request'] == "no" && $row['nuked'] == "no"?$sceneclr:"") . ($row['request'] == "yes" && $row['scene'] == "no" && $row['nuked'] == "no"?$requestclr:"") . ($row['sticky'] == "yes"?$stickyclr:"") . ($row['nuked'] == "yes"?$nukedclr:"");
                // //comment out to use gif indicate for seeding/leeching lower//////
                $req = sql_query("SELECT torrent, seeder FROM peers WHERE userid=$CURUSER[id] AND torrent=$id") or sqlerr();
                if (mysql_num_rows($req) > 0)
                    $peerid = mysql_fetch_assoc($req);
                if ($peerid['seeder'] == 'yes' && $peerid['torrent'] == $id)
                    $hl = '#00AB3F';
                if ($peerid['seeder'] == 'no' && $peerid['torrent'] == $id)
                    $hl = '#b22222 ';
                $bgc = "bgcolor=" . $hl . "";
                echo'<tr ' . $bgc . '>';
            }
            // //////////////////end highlight torrenttable - comment out to use standard or gif indicator code lower/////////
            echo("<td align=center style='padding: 0px'>");
            // cached category icons
            include 'include/cache/categories.php';
            foreach ($categories as $cat) {
            if ($cat["id"] == $row["category"])
            echo("<a href=\"browse.php?cat=" . $cat["id"] . "\"><img src=\"pic/caticons/$cat_ico_uri/" . $cat["image"] . "\" border=\"0\" title=\"category " . $cat["name"] . "\" /></a>");
            }
            echo("</td>\n");
            // end cat icon cache
            // ///////added under torrent name - uncomment out to use////
            //$added = "$row[added] (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($row["added"])) . " ago)";
            // ////////////////////////////////////end added///////////
            $genre = safeChar($row["newgenre"]);
            $nukereason = safeChar($row["nukereason"]);
            $scene = ($row["scene"] == "yes" ? "&nbsp;<img src='pic/scene.gif' border=0 title='Scene' alt='Scene'/>" : "");
            $request = ($row["request"] == "yes" ? "&nbsp;<img src='pic/request.gif' border=0 title='Request' alt='Request'/>" : "");
            $nuked = ($row["nuked"] == "yes" ? "&nbsp;<img src='pic/nuked.gif' border=0 title='nuked' alt='Nuked'/>" : "");
            $newtag = ((sql_timestamp_to_unix_timestamp($row['added']) >= $_SESSION['browsetime'])? '&nbsp;<img src=' . $pic_base_url . 'new.gif alt=NEW!>' : '');
            $viponly = ($row["vip"] == "yes" ? "<img src='pic/star.gif' border=0 title='Vip Torrent' />" : "");
            // ///////freeslot in use on browse//////////
            $freeimg = '<img src="/pic/freedownload.gif" border=0"/>';
            $doubleimg = '<img src="/pic/doubleseed.gif" border=0"/>';
            $isdlfree = ($row['doubleslot'] == 'yes' ? ' ' . $doubleimg . ' slot in use' : '');
            $isdouble = ($row['freeslot'] == 'yes' ? ' ' . $freeimg . ' slot in use' : '');
            $uclass = '';
            // torrent name
            $dispname = ($CURUSER["view_uclass"] == 'no' ? safeChar($row["name"]) : "<font color=\"#" . get_user_class_color($row["uclass"]) . "\">" . safeChar($row["name"]) . "</font>");
            // checked mod by pdq
            $checked = ((!empty($row['checked_by']) && $CURUSER['class'] >= UC_MODERATOR) ? "&nbsp;<img src='" . $pic_base_url . "mod.gif' width='15' border='0' title='Checked - by " . safeChar($row['checked_by']) . "' />" : "");
            $sticky = ($row["sticky"] == "yes" ? "<img src='pic/sticky.gif' border='0' alt='sticky' title='Sticky'>" : "");
            $countstats = ($row["countstats"] == "no" ? "<img src='pic/freedownload.gif' border='0' alt='Free' title='Free Torrent'>" : "");
            $half = ($row["half"] == "yes" ? "<img src='pic/halfdownload.png' border='0' alt='Half Leech' title='Half Leech'>" : "");
            // ///
            ///////////small description
            if (!empty($row['description'])) {
            $description = "(" . safeChar($row["description"]) . ")";
            }
            else {
            $description = "";
            }
            // ////////////////////////////////////////////////////////////////////////
            // ////////////////////////////////////////////////////////////////////////
            if ($row["poster"])
                $poster = "<img src=" . $row["poster"] . " width=150 border=0 />";
            if ($row["descr"])
                $descr = ereg_replace("\"", "&quot;", readMore($row["descr"], 350, "details.php?id=" . $row["id"] . "&amp;hit=1"));
            // userclass color mod ==end
            $dispname = ereg_replace('\.', ' ', $dispname);
            echo("<td align=left><a href=details.php?id=$id onmouseover=\"Tip('$poster');\" onmouseout=\"UnTip();\"><b>" . CutName($dispname, $char) . "</b></a>&nbsp;<a href=\"javascript:klappe_descr('descr" . $row["id"] . "');\" ><img src=\"/pic/plus.gif\" border=\"0\" title=\"Show torrent info in this page\"/></a>&nbsp;$sticky&nbsp;$request&nbsp;$scene&nbsp;$nuked<br />$nukereason&nbsp;$newtag&nbsp;$viponly&nbsp;$countstats&nbsp;$half&nbsp;$description\n");
            // //////////multiplicator///           
            if ($row["multiplicator"] == "2")
                $multiplicator = "&nbsp;<img src=\"pic/multi2.gif\" title=\"X2 Upload\">&nbsp;";
            elseif ($row["multiplicator"] == "3")
                $multiplicator = "&nbsp;<img src=\"pic/multi3.gif\" title=\"X3 Upload\">&nbsp;";
            elseif ($row["multiplicator"] == "4")
                $multiplicator = "&nbsp;<img src=\"pic/multi4.gif\" title=\"X4 Upload\">&nbsp;";
            elseif ($row["multiplicator"] == "5")
                $multiplicator = "&nbsp;<img src=\"pic/multi5.gif\" title=\"X5 Upload\">&nbsp;";
            if ($row["multiplicator"] != "0")
                echo("" . $multiplicator . "");

            if ($row["pweb"] > 0)
                echo("<img border=0 src=pic/seeder.gif onmouseover=\"Tip('web seeded by " . $row["pweb"] . " users');\" onmouseout=\"UnTip();\"/>");
            // ////torrent added/genre/checked////
            //echo ($added);
            echo ($genre);
            echo $checked;
            echo ($isdlfree . '' . $isdouble);
            /////////////////subtitles
            $movie_cat = array("3","5","10","11"); //add here your movie category
            print("<td align=\"center\" nowrap=\"nowrap\" >\n");
            if (in_array($row["category"], $movie_cat) && !empty($row["subs"]) )
            {
            $subs_array = explode(",",$row["subs"]);
            include 'cache/subs.php';
            foreach ($subs_array as $k => $sid) {
            foreach ($subs as $sub){
            if ($sub["id"] == $sid)
            print("<img border=\"0\" width=\"16px\" style=\"padding:3px;\"src=\"".$sub["pic"]."\" alt=\"".$sub["name"]."\" title=\"".$sub["name"]."\" />");
            }
            }
            }else
            echo("---");
            echo("</td>");
            ///////////////////end subs/////////
            //////////////////bookmarks/////////
            $bookmarked = (!isset($row["bookmark"])?'<a href=\'bookmark.php?torrent=' . $id . '&amp;action=add\'><img src=\'' . $pic_base_url . 'bookmark.gif\' border=\'0\' alt=\'Bookmark it!\' title=\'Bookmark it!\' /></a>':'<a href="bookmark.php?torrent=' . $id . '&amp;action=delete"><img src=\'' . $pic_base_url . 'plus2.gif\' border=\'0\' alt=\'Delete Bookmark!\' title=\'Delete Bookmark!\' /></a>');
            echo ($variant == 'index' ? '<td align=right>' . $bookmarked . '</td>' : '');
            // == wait times on/off from admincp
            if ((bool)$waiton) {
                if ((int)$wait > 0) {
                    $elapsed = floor((gmtime() - strtotime($row["added"])) / 3600);
                    if ($elapsed < $wait) {
                        $color = dechex(floor(127 * ($wait - $elapsed) / 48 + 128) * 65536);
                        print("<td align=center nowrap=\"nowrap\"><a href=\"faq.php#dl8\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></td>\n");
                    } else
                        print("<td align=center>None</td>\n");
                } else
                    print("<td align=center>None</td>\n");
            }
            /////////////////ttl on/off from admincp
            if ($oldtorrents) {
                $ttl = (28 * 24) - floor((gmtime() - sql_timestamp_to_unix_timestamp($row["added"])) / 3600);
                if ($ttl == 1) $ttl .= "<br />hour";
                else $ttl .= "<br />hours";
                echo'<td align=center>' . $ttl . '</td>';
            }
            /////////////////////////////////
            if (isset($row['type']) && ($row['type'] == "single"))
                // if ($row["type"] == "single")
                echo("<td align=\"right\">" . $row["numfiles"] . "</td>\n");
            else {
             if ($variant == "index")
                    echo("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;hit=1\">" . $row["numfiles"] . "</a></b></td>\n");
            }
            //////////////////////////////////////////        
            if (!$row["comments"])
                echo("<td align=\"right\">" . $row["comments"] . "</td>\n");
            else {
                if ($variant == "index")
                    echo("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;hit=1&amp;tocomm=1\">" . $row["comments"] . "</a></b></td>\n");
                else
                    echo("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></b></td>\n");
            }
            // ////Hide the quick download if download disabled/////
            if ($CURUSER["downloadpos"] == 'no') {
                echo("<td class=embedded><img src=" . $pic_base_url . "downloadpos.gif alt='no download' style='margin-left: 4pt' /></td>\n");
            } else
            if ($CURUSER["downloadpos"] == 'yes') {
                echo("<td align=\"center\"><a href=\"/download.php/$id/" . rawurlencode($row["filename"]) . "\"><img src=pic/download.gif border=0 alt=Download /></a></td>\n");
            }
            // Progressbar Mod
            // /comment out to remove indicator on browse//////
            $seedersProgressbar = array();
            $leechersProgressbar = array();
            $progressPerTorrent = 0;
            $iProgressbar = 0;
            if (isset($progress[$row["id"]])) {
                foreach($progress[$row["id"]] as $rowProgressbar) {
                    $progressPerTorrent += sprintf("%.2f", 100 * (1 - ($rowProgressbar["to_go"] / $rowProgressbar["size"])));
                    $iProgressbar++;
                }
            }
            if ($iProgressbar == 0)
                $iProgressbar = 1;
            $progressTotal = sprintf("%.2f", $progressPerTorrent / $iProgressbar);
            $picProgress = get_percent_completed_image(floor($progressTotal)) . "<br/>(" . round($progressTotal) . "%)";
            echo("<td align=center>$picProgress</td>\n");
            // End Progress Bar mod//////////////////////////
            echo("<td align=center>" . str_replace(" ", "<br/>", prefixed($row["size"])) . "</td>\n");
            $_s = "";
            if ($row["times_completed"] != 1)
                $_s = "s";
            if (get_user_class() >= UC_MODERATOR) {
                echo("<td align=center>" . ($row["times_completed"] > 0 ? "<a href=snatches.php?id=$id>" . safeChar(number_format($row["times_completed"])) . "<br/>time$_s</a>" : "0 times") . "</td>\n");
            } else
                echo("<td align=center>" . ($row["times_completed"] > 0 ?"" . safeChar(number_format($row["times_completed"])) . "<br/>time$_s</a>" : "0 times") . "</td>\n");

            if ($row["seeders"]) {
                if ($variant == "index") {
                    if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"];
                    else $ratio = 1;
                    echo("<td align=right><b><a href=details.php?id=$id&amp;hit=1#seeders><font color=" .
                        get_slr_color($ratio) . ">" . $row["seeders"] . "</font></a></b></td>\n");
                } else
                    echo("<td align=\"right\"><b><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"details.php?id=$id#seeders\">" . $row["seeders"] . "</a></b></td>\n");
            } else
                echo("<td align=\"right\"><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></td>\n");
            $peerlink ='';
            if ($row["leechers"]) {
                if ($variant == "index")
                    echo("<td align=right><b><a href=details.php?id=$id&amp;hit=1&amp;#leechers>" .
                        number_format($row["leechers"]) . ($peerlink ? "</a>" : "") . "</b></td>\n");
                else
                    echo("<td align=\"right\"><b><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"details.php?id=$id#leechers\">" . $row["leechers"] . "</a></b></td>\n");
            } else
                echo("<td align=\"right\">0</td>\n");
            // //Anonymous and delete torrent begin
            if ($variant == "index") {
                if ($row["anonymous"] == "yes") {
                    echo("<td align=center><i>Anonymous</i></td>\n");
                    if (get_user_class() >= UC_MODERATOR) {
                        echo("<td align=\"center\" bgcolor=\"#FF0000\"><input type=\"checkbox\" name=\"delete[]\" value=\"" . safeChar($id) . "\" /></td>\n");
                    }
                } else {
                    if ($variant == "index")
                         
                        if ($CURUSER["view_uclass"] == 'yes')
                            echo("<td align=center>" . (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . "><font color=\"#" . get_user_class_color($row["uclass"]) . "\">" . safeChar($row["username"]) . "</font></a>") : "<i>(unknown)</i>") . "</td>\n");
                        else
                            echo("<td align=center>" . (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . "><b>" . safechar($row["username"]) . "</b></a>") : "<i>(unknown)</i>") . "</td>\n");
                        // ///////modified Delete torrent with anonymous uploader
                        if (get_user_class() >= UC_MODERATOR) {
                            echo("<td align=\"center\" bgcolor=\"#FF0000\"><input type=\"checkbox\" name=\"delete[]\" value=\"" . safeChar($id) . "\" /></td>\n");
                        }
                    }
                }
                echo("</tr>\n");

                echo("<tr id=\"kdescr" . $row["id"] . "\"style=\"display:none;\"><td width=\"90%\"  colspan=\"" . (get_user_class() >= UC_MODERATOR ? "15" : "13") . "\">" . $descr . "</td></tr>\n");
            }
            if (get_user_class() >= UC_MODERATOR) {
                echo("<tr ><td align=\"center\" colspan=16><input type=submit value=Delete /></td></tr>\n");
            }
            echo("</table></form>\n");
        }
        // ////end annonymous/delete torrent////

        ?>