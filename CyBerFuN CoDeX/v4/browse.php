<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require_once("include/function_torrenttable.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
getpage();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
$page_find ='browse';
// get torrents in cat with one query
$r_cat = sql_query("select category , count(id) as tn from torrents group by category");
while ($a_cat = mysql_fetch_row($r_cat)) {
    $catcount[$a_cat[0]]['count'] = $a_cat[1];
}
if ($CURUSER['update_new'] != 'no' && $_GET['clear_new'])
    $_SESSION['browsetime'] = gmtime();
include 'include/cache/categories.php';
$cats = $categories;
// //////////sort start///////////
$searchstr = (isset($_GET['search']) ? unesc($_GET['search']) : '');
$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr))
    unset($cleansearchstr);
if (isset($_GET['sort']) && isset($_GET['type'])) {
    $column = '';
    $ascdesc = '';

    switch ($_GET['sort']) {
        case '1': $column = "name";
            break;
        case '2': $column = "numfiles";
            break;
        case '3': $column = "comments";
            break;
        case '4': $column = "added";
            break;
        case '6': $column = "size";
            break;
        case '7': $column = "times_completed";
            break;
        case '8': $column = "seeders";
            break;
        case '9': $column = "leechers";
            break;
        case '10': $column = "owner";
            break;
        default: $column = "id";
            break;
    }

    switch ($_GET['type']) {
        case 'asc': $ascdesc = "ASC";
            $linkascdesc = "asc";
            break;
        case 'desc': $ascdesc = "DESC";
            $linkascdesc = "desc";
            break;
        default: $ascdesc = "DESC";
            $linkascdesc = "desc";
            break;
    }

    $orderby = "ORDER BY torrents." . $column . " " . $ascdesc;
    $pagerlink = "sort=" . intval($_GET['sort']) . "&type=" . $linkascdesc . "&";
} else {
    $orderby = "ORDER BY torrents.sticky ASC, torrents.id DESC";
    $pagerlink = "";
}

$addparam = "";
$wherea = array();
$wherecatina = array();

$incldead = (isset($_GET["incldead"]) ? 0 + $_GET["incldead"] : 0);
if ($incldead == 1) {
    $addparam .= "incldead=1&amp;";
    if (!isset($CURUSER) || get_user_class() < UC_ADMINISTRATOR)
        $wherea[] = "visible = 'yes'";
} 
elseif ($_GET["incldead"] == 2)
{
$addparam .= "incldead=2&amp;";
$wherea[] = "visible = 'no'";
}

elseif ($_GET["incldead"] == 3)
{
$addparam .= "incldead=3&amp;";
$wherea[] = "sticky = 'yes'";
$wherea[] = "hidden = 'yes'";
$wherea[] = "visible = 'yes'";
}
else
$wherea[] = "visible = 'yes'";

$category = (isset($_GET["cat"]) ? 0 + $_GET["cat"] : '');

$all = (isset($_GET["all"]) ? 0 + $_GET["all"] : 0);

$blah = (isset($_GET["blah"]) ? 0 + $_GET["blah"] : 0);
if (!$all)
    if (!$_GET && $CURUSER["notifs"]) {
        $all = true;
        foreach ($cats as $cat) {
            $all &= $cat["id"];
            if (strpos($CURUSER["notifs"], "[cat" . $cat["id"] . "]") !== false) {
                $wherecatina[] = $cat["id"];
                $addparam .= "c" . $cat["id"] . "=1&amp;";
            }
        }
    } elseif ($category) {
        if (!is_valid_id($category))
            stderr("Error", "Invalid category ID.");
        $wherecatina[] = $category;
        $addparam .= "cat=$category&amp;";
    } else {
        $all = true;
        foreach ($cats as $cat) {
            $caat = (isset($_GET["c$cat[id]"]) ? 0 + $_GET["c$cat[id]"] : '');
            $all &= $caat;
            if ($caat) {
                $wherecatina[] = $cat["id"];
                $addparam .= "c" . $cat["id"] . "=1&amp;";
            }
        }
    }

    if ($all) {
        $wherecatina = array();
        if ($blah == 1) {
            $addparam .= "blah=1&amp;";
            $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
        } elseif ($blah == 2) {
            $addparam .= "blah=2&amp;";
            $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
        }
        $addparam = "";
    }

    if (count($wherecatina) > 1)
        $wherecatin = implode(",", $wherecatina);
    elseif (count($wherecatina) == 1)
        $wherea[] = "category = $wherecatina[0]";

    $wherebase = $wherea;

    if (isset($cleansearchstr)) {
        if ($blah == 0) {
            $wherea[] = "torrents.name LIKE (" . sqlesc($searchstr) . ")";
        } elseif ($blah == 1) {
            $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
        } elseif ($blah == 2) {
            $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")";
        }
        $addparam .= "search=" . urlencode($searchstr) . "&";
    }
    $wherecatin = "";
    $where = implode(" AND ", $wherea);
    if ($wherecatin)
        $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";
    //===hidden torrents
    if ($CURUSER["hiddentorrents"] == "no" && get_user_class() < UC_MODERATOR)
    $where .= ($where ? " AND " : "") . "hidden='no'";
    $onlyhidden= 0 + $_GET["onlyhidden"];
    if ($onlyhidden == 1){
    if ($CURUSER["hiddentorrents"] == "yes" || get_user_class() >= UC_MODERATOR){
    $where .= ($where ? " AND " : "") . "hidden='yes'";
    }
    }
    //===staff only torrents
    if (get_user_class() < UC_MODERATOR)
    $where .= ($where ? " AND " : "") . "staffonly='no'";
    //===end hidden and staff
    if ($where != "")
        $where = "WHERE $where";

    $res = sql_query("SELECT COUNT(*) FROM torrents $where") or die(mysql_error());
    $row = mysql_fetch_array($res, MYSQL_NUM);
    $count = $row[0];

    if (!$count && isset($cleansearchstr)) {
        $wherea = $wherebase;
        $orderby = "ORDER BY id DESC";
        $searcha = explode(" ", $cleansearchstr);
        $sc = 0;
        foreach ($searcha as $searchss) {
            if (strlen($searchss) <= 1)
                continue;
            $sc++;
            if ($sc > 5)
                break;
            $ssa = array();
            if ($blah == 0) {
                foreach (array("torrents.name") as $sss)
                $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
                $wherea[] = "(" . implode(" OR ", $ssa) . ")";
            } elseif ($blah == 1) {
                foreach (array("search_text", "ori_descr") as $sss)
                $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
                $wherea[] = "(" . implode(" OR ", $ssa) . ")";
            } elseif ($blah == 2) {
                foreach (array("search_text", "ori_descr") as $sss)
                $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
                $wherea[] = "(" . implode(" OR ", $ssa) . ")";
            }
        }
        if ($sc) {
            $where = implode(" AND ", $wherea);
            if ($where != "")
                $where = "WHERE $where";
            $res = sql_query("SELECT COUNT(*) FROM torrents $where");
            $row = mysql_fetch_array($res, MYSQL_NUM);
            $count = $row[0];
        }
    }

    $torrentsperpage = $CURUSER["torrentsperpage"];
    if (!$torrentsperpage)
        $torrentsperpage = 15;

    if ($count) {
        if ($addparam != "") {
            if ($pagerlink != "") {
                if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
                    $addparam = $addparam . "&" . $pagerlink;
                } else {
                    $addparam = $addparam . $pagerlink;
                }
            }
        } else {
            $addparam = $pagerlink;
        }

        list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);

        $query = "SELECT torrents.id,torrents.uclass,torrents.descr,torrents.poster,torrents.category, torrents.subs, torrents.leechers, torrents.seeders, torrents.request, torrents.scene, torrents.nuked, torrents.nukereason, torrents.newgenre, torrents.checked_by, torrents.afterpre, torrents.countstats, torrents.half, torrents.name, torrents.vip, torrents.sticky, torrents.times_completed, torrents.size, torrents.added, torrents.comments, torrents.numfiles, torrents.filename, torrents.description, torrents.multiplicator, torrents.anonymous, torrents.owner," . "freeslots.free AS freeslot, freeslots.doubleup AS doubleslot, users.username, b.id as bookmark,  (SELECT count(id) FROM peers where torrent=torrents.id AND webseeder = 'yes') as pweb FROM torrents LEFT JOIN freeslots ON (torrents.id=freeslots.torrentid AND freeslots.userid=" . $CURUSER["id"] . ") LEFT JOIN users ON torrents.owner = users.id LEFT JOIN bookmarks as b ON torrents.id=b.torrentid AND b.userid=" . $CURUSER["id"] . " $where AND (torrents.minclass = 255 OR torrents.minclass <= ".$CURUSER["class"].") $orderby $limit";

        $res = sql_query($query) or die(mysql_error());
        while ($record = mysql_fetch_assoc($res)) {
            $records[] = $record;
            $tid[] = $record["id"];
        }
        // update for progress bar mod
        if (count($tid) > 0) {
            $r_prog = sql_query("SELECT p.to_go, t.size,t.id FROM peers as p LEFT JOIN torrents as t ON p.torrent=t.id WHERE t.id IN (" . join(",", $tid) . ") GROUP BY p.id");
            while ($a = mysql_fetch_assoc($r_prog))
            $progress[$a["id"]][] = array("to_go" => $a["to_go"], "size" => $a["size"]);
        }
        // end
    } else
        unset($res);
    if (isset($cleansearchstr))
        stdhead("Search Results For \"$searchstr\"");
    else

        stdhead();
?>
<script type="text/javascript">
function auto(){
if (document.getElementById) {
var form = document.getElementById('search_form');
form.setAttribute("autocomplete", "off");
}
}
</script>
<form method="get" action="browse.php" id="search_form">
<table width="100%" style="margin-top:10px;" border="0" cellspacing="0" cellpadding="0" align="center" ><tr><td class="colhead" valign="middle" style="text-align:center;padding-top:6px;padding-bottom:5px;">
Search:
<input type="text" id="searchinput" name="search"  style="width: 240px;" ondblclick="suggest(event.keyCode,this.value);" onkeyup="suggest(event.keyCode,this.value);" onkeypress="return noenter(event.keyCode);" value="<?= safeChar($searchstr) ?>" />
by
<select name=blah>
<option value="0"><?php echo $language['strn'];?></option>
<option value="1"<?php print($blah == 1 ? " selected" : "");
    ?>><?php echo $language['strd'];?></option>
<option value="2"<?php print($blah == 2 ? " selected" : "");
    ?>><?php echo $language['strb'];?></option>
</select>
in
<select name=incldead>
<option value="0"><?php echo $language['stra'];?></option>
<option value="1"<?php print(!isset($_GET["incldead"]) == 1 ? " selected" : "");
    ?>><?php echo $language['str3'];?></option>
<option value="2"<?php print(!isset($_GET["incldead"]) == 2 ? " selected" : "");
    ?>><?php echo $language['str4'];?></option>
<?php
if ($CURUSER["hiddentorrents"] == "yes" || get_user_class() >= UC_MODERATOR){
?>
<option value="3"<? print($_GET["incldead"] == 3 ? " selected" : ""); ?>>Hidden</option>
<?}?>
            </select>


<?php
    if ($CURUSER["imagecats"] == "no") {

        ?>
in<select name="cat">
<option value="0"><?php echo $language['str12'];?></option>
<?php
        $catdropdown = "";
        foreach ($cats as $cat) {
            $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
            if ($cat["id"] == $_GET["cat"])
                $catdropdown .= " selected=\"selected\"";
            $catdropdown .= ">" . safeChar($cat["name"]) . "&nbsp;(" . (0 + $catcount[$cat["id"]]["count"]) . ")</option>\n";
        }

        ?>
<?= $catdropdown ?>
</select>
<?php }
    ?>
<input type="submit" value="Search!" />

<script language="JavaScript" src="suggest.js" type="text/javascript"></script>
<div id="suggcontainer" style="text-align: center; width: 520px; display: none;">
<div id="suggestions" style="cursor: default; position: absolute; background-color: #ff0000; border: 1px solid #777777;"></div>
</div><img src="pic/legend.png" width="16" height="16" border="0" onmouseover="Tip('[Highlight colors = <font color=\'#00AB3F\'>Seeding </font>|<font color=\'#b22222\'> Leeching</font> | <font color=\'#336666\'>Free</font> |<font color=\'#FF6600\'> Scene</font> |<font color=\'#777777\'> Request</font> | <font color=\'#FF0000\'>Nuked</font> | <font color=\'#FDD017\'>Sticky</font> ]');" onmouseout="UnTip();" />
</td></tr>
<?php
    if ($CURUSER["imagecats"] == "yes") {
        print("<tr><td valign=\"middle\" style=\"text-align:center;padding-top:6px;padding-bottom:5px;\">");
        print("<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" ><tr>");
        foreach ($cats as $cat) {
            $catsperrow = 5;
            $catz = ($CURUSER['imagecats'] == 'yes' ? "<img border=0 src=\"" . $pic_base_url . safeChar($cat['image']) . "\" title=\"There are " . (0 + $catcount[$cat["id"]]["count"]) . " in this category\"/>" : safeChar($cat['name']));
            print(($i && $i % $catsperrow == 0) ? "</tr><tr>" : "");
            print("<td align=center class=bottom style=\"padding-bottom: 2px;padding-left: 7px;border:none;\"><input name=c$cat[id] type=\"checkbox\" " . (in_array($cat['id'], $wherecatina) ? "checked " : "") . "value=1 /><a class=catlink href=browse.php?cat=$cat[id]>" . $catz . "</a></td>\n");
            $i++;
        }
        print("</tr></table>");
        print("</td></tr>");
    }

    ?>
</table>

</form>



<?php
    if (isset($cleansearchstr))
        print("<h2>" .$language['str1'] . " \"" . safeChar($searchstr) . "\"</h2>\n");
    if ($CURUSER['update_new'] != 'no') {
        // === if you want a button
        echo'<a href="?clear_new=1"><input type=submit value="clear new tag" class=button></a>';
        // === if you want a link
        // echo'<p><a href="?clear_new=1">clear new tag</a></p>';
    }
    if ($count) {
        print($pagertop);

        torrenttable($records);

        print($pagerbottom);
    } else {
        if (isset($cleansearchstr)) {
            print("<h2>" . $language['str20'] . "</h2>\n");
            print("<p>" . $language['str16'] . "</p>\n");
        } else {
            print("<h2>" . $language['str19'] . "</h2>\n");
            print("<p>" . $language['str17'] . "</p>\n");
        }
    }
    if ($CURUSER['update_new'] != 'yes')
        $_SESSION['browsetime'] = gmtime();
    stdfoot();

    ?>