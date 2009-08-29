<?php
// sharemarks.php - by pdq
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
function sharetable($res, $variant = "index")
{
    global $pic_base_url, $CURUSER;

    $wait = 0;
    if ($CURUSER["class"] < UC_VIP) {
        $gigs = $CURUSER["uploaded"] / (1024 * 1024 * 1024);
        $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);
        if ($ratio < 0.5 || $gigs < 5) $wait = 48;
        elseif ($ratio < 0.65 || $gigs < 6.5) $wait = 24;
        elseif ($ratio < 0.8 || $gigs < 8) $wait = 12;
        elseif ($ratio < 0.95 || $gigs < 9.5) $wait = 6;
        else $wait = 0;
    }

    ?>
<p align="center">Icon Legend :
<img alt="Delete Bookmark" src="pic/plus.gif" border="none">
= Delete Bookmark |
<img alt="Download Bookmark" by="" src="pic/download.gif">
= Download Torrent |
<img alt="Bookmark Torrent" src="pic/bookmark.gif" border="none">
= Bookmark Torrent</p>
<table border="1" cellspacing=0 cellpadding=5>
<tr>

<td class="colhead" align="center">Type</td>
<td class="colhead" align=left>Name</td>
<?php
    echo ($variant == 'index' ? '<td class=colhead align=center>Download</td><td class=colhead align="right">' : '') . 'Bookmark</td>';

    if ($wait) {
        print("<td class=\"colhead\" align=\"center\">Wait</td>\n");
    }

    if ($variant == "mytorrents") {
        print("<td class=\"colhead\" align=\"center\">Edit</td>\n");
        print("<td class=\"colhead\" align=\"center\">Visible</td>\n");
    }

    ?>
<td class="colhead" align=right>Files</td>
<td class="colhead" align=right>Comm.</td>
<!--<td class="colhead" align="center">Rating</td>-->
<td class="colhead" align="center">Added</td>
<td class="colhead" align="center">TTL</td>
<td class="colhead" align="center">Size</td>
<!--
<td class="colhead" align=right>Views</td>
<td class="colhead" align=right>Hits</td>
-->
<td class="colhead" align="center">Snatched</td>
<td class="colhead" align=right>Seeders</td>
<td class="colhead" align=right>Leechers</td>
<?php

    if ($variant == "index")
        print("<td class=\"colhead\" align=center>Upped&nbsp;by</td>\n");

    print("</tr>\n");

    while ($row = mysql_fetch_assoc($res)) {
        $id = $row["id"];
        print("<tr>\n");

        print("<td align=center style='padding: 0px'>");
        if (isset($row["cat_name"])) {
            print("<a href=\"browse.php?cat=" . $row["category"] . "\">");
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                print("<img border=\"0\" src=\"{$pic_base_url}caticons/{$row['cat_pic']}\" alt=\"{$row['cat_name']}\" />");
            else
                print($row["cat_name"]);
            print("</a>");
        } else
            print("-");
        print("</td>\n");

        $dispname = safechar($row["name"]);
        print("<td align=left><a href=\"details.php?");
        if ($variant == "mytorrents")
            print("returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;");
        print("id=$id");
        if ($variant == "index")
            print("&amp;hit=1");
        print("\"><b>$dispname</b></a>\n");

        echo ($variant == 'index' ? '<td align=center><a href=download.php/' . $id . '/' . rawurlencode($row['filename']) . '><img src="' . $pic_base_url . 'download.gif" border="0" alt="Download Bookmark!" title="Download Bookmark!"></a></td>' : '');

        $bm = mysql_query("SELECT * FROM bookmarks WHERE torrentid=$id && userid=$CURUSER[id]");
        $bms = mysql_fetch_assoc($bm);
        $bookmarked = (empty($bms)?'<a href=\'bookmark.php?torrent=' . $id . '&action=add\'><img src=\'' . $pic_base_url . 'bookmark.gif\' border=\'0\' alt=\'Bookmark it!\' title=\'Bookmark it!\'></a>':'<a href="bookmark.php?torrent=' . $id . '&action=delete"><img src=\'' . $pic_base_url . 'plus.gif\' border=\'0\' alt=\'Delete Bookmark!\' title=\'Delete Bookmark!\'></a>');

        echo ($variant == 'index' ? '<td align=center>' . $bookmarked . '</td>' : '');

        if ($wait) {
            $elapsed = floor((gmtime() - strtotime($row["added"])) / 3600);
            if ($elapsed < $wait) {
                $color = dechex(floor(127 * ($wait - $elapsed) / 48 + 128) * 65536);
                print("<td align=center><nobr><a href=\"faq.php#dl8\"><font color=\"$color\">" . number_format($wait - $elapsed) . " h</font></a></nobr></td>\n");
            } else
                print("<td align=center><nobr>None</nobr></td>\n");
        }

        /*
if ($row["nfoav"] && get_user_class() >= UC_POWER_USER)
print("<a href=viewnfo.php?id=$row[id]><img src=\"{$pic_base_url}viewnfo.gif" border=0 alt='View NFO'></a>\n");

else */if ($variant == "mytorrents")
            print("<td align=\"center\"><a href=\"edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\">edit</a>\n");
        print("</td>\n");
        if ($variant == "mytorrents") {
            print("<td align=\"right\">");
            if ($row["visible"] == "no")
                print("<b>no</b>");
            else
                print("yes");
            print("</td>\n");
        }

        if ($row["type"] == "single")
            print("<td align=\"right\">" . $row["numfiles"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><b><a href=\"filelist.php?id=$id\">" . $row["numfiles"] . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a href=\"filelist.php?id=$id\">" . $row["numfiles"] . "</a></b></td>\n");
        }

        if (!$row["comments"])
            print("<td align=\"right\">" . $row["comments"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;hit=1&amp;tocomm=1\">" . $row["comments"] . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></b></td>\n");
        }

        /*
print("<td align=\"center\">");
if (!isset($row["rating"]))
print("---");
else {
$rating = round($row["rating"] * 2) / 2;
$rating = ratingpic($row["rating"]);
if (!isset($rating))
print("---");
else
print($rating);
}
print("</td>\n");
*/
        print("<td align=center><nobr>" . str_replace(" ", "<br />", $row["added"]) . "</nobr></td>\n");
        $ttl = (28 * 24) - floor((gmtime() - sql_timestamp_to_unix_timestamp($row["added"])) / 3600);
        if ($ttl == 1) $ttl .= "<br />hour";
        else $ttl .= "<br />hours";
        print("<td align=center>$ttl</td>\n");
        print("<td align=center>" . str_replace(" ", "<br />", mksize($row["size"])) . "</td>\n");
        // print("<td align=\"right\">" . $row["views"] . "</td>\n");
        // print("<td align=\"right\">" . $row["hits"] . "</td>\n");
        $_s = "";
        if ($row["times_completed"] != 1)
            $_s = "s";
        print("<td align=center>" . number_format($row["times_completed"]) . "<br />time$_s</td>\n");

        if ($row["seeders"]) {
            if ($variant == "index") {
                if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"];
                else $ratio = 1;
                print("<td align=right><b><a href=peerlist.php?id=$id#seeders><font color=" .
                    get_slr_color($ratio) . ">" . $row["seeders"] . "</font></a></b></td>\n");
            } else
                print("<td align=\"right\"><b><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"peerlist.php?id=$id#seeders\">" . $row["seeders"] . "</a></b></td>\n");
        } else
            print("<td align=\"right\"><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></td>\n");

        if ($row["leechers"]) {
            if ($variant == "index")
                print("<td align=right><b><a href=peerlist.php?id=$id#leechers>" .
                    number_format($row["leechers"]) . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"peerlist.php?id=$id#leechers\">" . $row["leechers"] . "</a></b></td>\n");
        } else
            print("<td align=\"right\">0</td>\n");

        if ($variant == "index")
            print("<td align=center>" . (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . "><b>" . safechar($row["username"]) . "</b></a>") : "<i>(unknown)</i>") . "</td>\n");

        print("</tr>\n");
    }

    print("</table>\n");
    // return $rows;
}
// GO
$userid = isset($_GET['id']) ? (int)$_GET['id'] : '';

if (!is_valid_id($userid))
    stderr("Error", "Invalid ID.");

$res = mysql_query("SELECT id, username FROM users WHERE id = $userid") or sqlerr();
$arr = mysql_fetch_array($res);

stdhead("Sharemarks for " . $arr['username']);
echo '<h1>Sharemarks for <a href=userdetails.php?id=$userid><b>' . $arr['username'] . '<b></a></h2>';

$res = mysql_query("SELECT COUNT(id) FROM bookmarks WHERE userid = $userid");
$row = mysql_fetch_array($res);
$count = $row[0];

$torrentsperpage = $CURUSER["torrentsperpage"];
if (!$torrentsperpage)
    $torrentsperpage = 25;

if ($count) {
    // $pager = pager($torrentsperpage, $count, "bookmarks.php?");//TB
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, "bookmarks.php?");
    /*//TB
$query = "SELECT bookmarks.id as bookmarkid, users.username,users.id as owner, torrents.id, torrents.name, torrents.type, torrents.comments, torrents.leechers, torrents.seeders, ROUND(torrents.ratingsum / torrents.numratings) AS rating, categories.name AS cat_name, categories.image AS cat_pic, torrents.save_as, torrents.numfiles, torrents.added, torrents.filename, torrents.size, torrents.views, torrents.visible, torrents.hits, torrents.times_completed, torrents.category FROM bookmarks LEFT JOIN torrents ON bookmarks.torrentid = torrents.id LEFT JOIN users on torrents.owner = users.id LEFT JOIN categories ON torrents.category = categories.id WHERE bookmarks.userid = $userid AND bookmarks.private = 'no' ORDER BY torrents.id DESC {$pager['limit']}";
$res = mysql_query($query) or sqlerr();
*/
    $res = mysql_query("SELECT bookmarks.id as bookmarkid, users.username,users.id as owner, torrents.id, torrents.name, torrents.type, torrents.comments, torrents.leechers, torrents.seeders, ROUND(torrents.ratingsum / torrents.numratings) AS rating, categories.name AS cat_name, categories.image AS cat_pic, torrents.save_as, torrents.numfiles, torrents.added, torrents.filename, torrents.size, torrents.views, torrents.visible, torrents.hits, torrents.times_completed, torrents.category FROM bookmarks LEFT JOIN torrents ON bookmarks.torrentid = torrents.id LEFT JOIN users on torrents.owner = users.id LEFT JOIN categories ON torrents.category = categories.id WHERE bookmarks.userid = $userid AND bookmarks.private = 'no' ORDER BY torrents.id DESC $limit") or sqlerr();
}

if ($count) {
    print($pagertop);
    sharetable($res, "index", true);
    print($pagerbottom);

    /*//TB
print($pager['pagertop']);
sharetable($res, "index", TRUE);
print($pager['pagerbottom']);
*/
}

stdfoot();

?>