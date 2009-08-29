<?php
// /////////////Updated mytorrents.php by Bigjoos////////////
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
stdhead("" . safechar($CURUSER["username"]) . "'s Completed torrent's ");

$rescount = sql_query("SELECT COUNT(*) FROM torrents WHERE owner = " . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
$rowcount = mysql_fetch_row($rescount);
$count = $rowcount[0];
$mytorrentsperpage = 15;
list($pagertop, $pagerbottom, $limit) = pager($mytorrentsperpage, $count, "mytorrents.php?");
$res = sql_query("SELECT * FROM torrents WHERE owner =" . $CURUSER["id"] . " $limit") or sqlerr(__FILE__, __LINE__);
while ($r = mysql_fetch_assoc($res)) {
    $rows[] = $r;
    $tid[] = $r["id"];
}
if (count($tid) > 0) {
    // update for progress bar mod
    $r_prog = sql_query("SELECT p.to_go, t.size,t.id FROM peers as p LEFT JOIN torrents as t ON p.torrent=t.id WHERE t.id IN (" . join(",", $tid) . ") GROUP BY p.id");
    while ($a = mysql_fetch_assoc($r_prog))
    $progress[$a["id"]][] = array("to_go" => $a["to_go"], "size" => $a["size"]);
    // end
}
// echo_r ($progress);
// echo_r($tid);
if (count($rows) > 0) {
    echo("$pagerbottom");
    echo("<table width=80% border=0 cellspacing=0 cellpadding=3 align=center>");
    echo("<tr>");
    echo("<td class=colhead align=center>Cat.</td>");
    echo("<td class=colhead align=center>Torrentname</td>");
    echo("<td class=colhead align=center>Visible</td>");
    echo("<td class=colhead align=center>Free</td>");
    echo("<td class=colhead align=center>Edit</td>");
    echo("<td class=colhead align=center>Files</td>");
    echo("<td class=colhead align=center>Comm.</td>");
    echo("<td class=colhead align=center>Views</td>");
    echo("<td class=colhead align=center>Hits</td>");
    echo("<td class=colhead align=center>Added</td>");
    echo("<td class=colhead align=center>Last Act.</td>");
    echo("<td class=colhead align=center>Size</td>");
    echo("<td class=colhead align=center>Progress</td>");
    echo("<td class=colhead align=center>Snatched</td>");
    echo("<td class=colhead align=center>Seeders</td>");
    echo("<td class=colhead align=center>Leechers</td>");
    echo("</tr>");
    // While ($row = mysql_fetch_assoc($res)) {
    foreach ($rows as $row) {
        echo("<tr>");

        include 'include/cache/categories.php';
        foreach ($categories as $cat) {
            if ($row["category"] == $cat["id"])
                echo("<td width=5%><img src=\"pic/" . $cat["image"] . "\" border=\"0\" title=\"category " . $cat["name"] . "\" /></td>");
        }
        // // smallname mytorrents
        $smallname = substr(safechar($row["name"]) , 0, 40);
        if ($smallname != safechar($row["name"])) {
            $smallname .= '...';
        }
        // $smallname = safechar($row["name"]);
        // // smallname mytorrents end
        echo("<td><a href=details.php?id=" . $row[id] . "><b>" . safeChar($smallname) . "</b></a></td>");
        // // colored yes/no for visible
        $visible = ($row["visible"]) == 'yes' ? "<font color=green>Yes</font>" : "<font color=red>No</font>";
        // // colored yes/no for visible end
        echo("<td align=center>" . $visible . "</td>");
        // // colored yes/no for golden torrents
        $countstats = ($row["countstats"]) == 'no' ? "<font color=green>Yes</font>" : "<font color=red>No</font>" ;
        // // colored yes/no for golden torrents end
        echo("<td align=center>" . ($countstats) . "</td>");
        echo("<td align=center><a href=edit.php?id=" . safeChar($row["id"]) . ">Edit</a></td>");
        echo("<td align=center><a href=details.php?id=" . safeChar($row["id"]) . "&filelist=1#filelist>" . safeChar($row["numfiles"]) . "</a></td>");
        echo("<td align=center><a href=details.php?id=" . safeChar($row["id"]) . "&page=0#startcomments>" . safeChar($row["comments"]) . "</a></td>");
        echo("<td align=center>" . safeChar($row["views"]) . "</td>");
        echo("<td align=center>" . safeChar($row["hits"]) . "</td>");
        echo("<td align=center>" . safeChar($row["added"]) . "</td>");
        echo("<td align=center>" . safeChar($row["last_action"]) . "</td>");
        echo("<td align=center>" . safeChar(prefixed($row["size"])) . "</td>");
        // // Progress Bar
        $seedersProgressbar = array();
        $leechersProgressbar = array();
        // $resProgressbar = mysql_query("SELECT p.seeder, p.to_go, t.size FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE  p.torrent = ". unsafeChar($row[id]) ."") or sqlerr(__FILE__, __LINE__);
        $progressPerTorrent = 0;
        $iProgressbar = 0;
        // while ($rowProgressbar = mysql_fetch_array($resProgressbar)) {
        foreach($progress[$row["id"]] as $rowProgressbar) {
            $progressPerTorrent += sprintf("%.2f", 100 * (1 - ($rowProgressbar["to_go"] / $rowProgressbar["size"])));
            $iProgressbar++;
        }
        if ($iProgressbar == 0)
            $iProgressbar = 1;
        $progressTotal = sprintf("%.2f", $progressPerTorrent / $iProgressbar);
        $picProgress = get_percent_completed_image(floor($progressTotal)) . " <br />(" . round($progressTotal) . "%)";
        // // End Progress Bar
        echo("<td align=center>" . $picProgress . "</td>");
        // // red color by 0 times complete
        if ($row["times_completed"] == '0')
            $times_completed = "<font color=red>" . safeChar($row["times_completed"]) . " x</font>";
        elseif ($row["times_completed"] < '2')
            $times_completed = "<font color=darkred>" . safeChar($row["times_completed"]) . " x</font>";
        elseif ($row["times_completed"] < '5')
            $times_completed = "<font color=green>" . safeChar($row["times_completed"]) . " x</font>";
        else
            $times_completed = "<font color=#FFFFFF>" . safeChar($row["times_completed"]) . " x</font>";
        // // red color by 0 seeders end
        echo("<td align=center><a href=snatches.php?id=" . $row["id"] . ">" . $times_completed . "</a></td>");
        // // red color by 0 times complete
        if ($row["seeders"] == '0')
            $seeders = "<font color=red>" . safeChar($row["seeders"]) . "</font>";
        elseif ($row["seeders"] < '2')
            $seeders = "<font color=darkred>" . safeChar($row["seeders"]) . "</font>";
        elseif ($row["seeders"] < '5')
            $seeders = "<font color=green>" . safeChar($row["seeders"]) . "</font>";
        else
            $seeders = "<font color=#FFFFFF>" . safeChar($row["seeders"]) . "</font>";
        // // red color by 0 seeders end
        echo("<td align=center><a href=details.php?id=" . $row["id"] . "&dllist=1#seeders>" . $seeders . "</a></td>");
        echo("<td align=center><a href=details.php?id=" . $row["id"] . "&dllist=1#leechers>" . $row["leechers"] . "</a></td>");
        echo("</tr>");
    }
    echo("</table>");
    echo("$pagertop");
} else {
    echo("<center>Nothings here!</center>");
}

stdfoot();

?>
