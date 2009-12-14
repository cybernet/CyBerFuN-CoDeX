<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
// ////////////snatches moddified by Bigjoos/////
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR)
    stderr("Error", "Permission denied.");
$id = 0 + $_GET["id"];

if (!is_valid_id($id))
    stderr("Error", "It appears that you have entered an invalid id.");

$res = mysql_query("SELECT id, name FROM torrents WHERE id = $id") or sqlerr();
$arr = mysql_fetch_assoc($res);

if (!$arr)
    stderr("Error", "It appears that there is no torrent with that id.");

function get_snatched_color($st)
{
    $secs = $st;
    $mins = floor($st / 60);
    $hours = floor($mins / 60);
    $days = floor($hours / 24);
    $week = floor($days / 7);
    $month = floor($week / 4);

    if ($month > 0) {
        $week_elapsed = floor(($st - ($month * 4 * 7 * 24 * 60 * 60)) / (7 * 24 * 60 * 60));
        $days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=lime><b>$month months.<br>$week_elapsed W. $days_elapsed D.</b></font>";
    }
    if ($week > 0) {
        $days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=lime><b>$week W. $days_elapsed D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
    }
    if ($days > 2) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=lime><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
    }
    if ($days > 1) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=green><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
    }

    if ($days > 0) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=#CCFFCC><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></font>";
    }
    if ($hours > 12) {
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=yellow><b>$hours:$mins_elapsed:$secs_elapsed</b></font>";
    }
    if ($hours > 0) {
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=red><b>$hours:$mins_elapsed:$secs_elapsed</b></font>";
    }
    if ($mins > 0) {
        $secs_elapsed = floor($st - $mins * 60);
        return "<font color=red><b>0:$mins:$secs_elapsed</b></font>";
    }
    if ($secs > 0) {
        return "<font color=red><b>0:0:$secs</b></font>";
    }
    return "<font color=red><b>None<br>Reported</b></font>";
}

$res = mysql_query("SELECT COUNT(*) FROM snatched WHERE torrentid =".unsafeChar($id)."") or sqlerr();
$row = mysql_fetch_row($res);
$count = $row[0];
$perpage = 20;

if (!$count)
    stderr("No snatches", "It appears that there are currently no snatches for the torrent <a href=details.php?id=$arr[id]>$arr[name]</a>.");

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "?id=$id&");

stdhead("Snatches");
echo("<h1>Snatches : <a href=details.php?id=$arr[id]>$arr[name]</a></h1>\n");
echo("<h2>Currently $row[0] snatch" . ($row[0] == 1 ? "" : "es") . "</h2>\n");
if ($count > $perpage)
    echo("$pagertop");
echo("<table border=0 cellspacing=0 cellpadding=3>\n");
echo("<tr>\n");
echo("<td class=tabletorrent align=left>Name</td>\n");
echo("<td class=tabletorrent align=left>Id</td>\n");
echo("<td class=tabletorrent align=center>Con.</td>\n");
echo("<td class=tabletorrent align=right>Upload</td>\n");
echo("<td class=tabletorrent align=right>Uspeed</td>\n");
echo("<td class=tabletorrent align=right>Download</td>\n");
echo("<td class=tabletorrent align=right>Dspeed</td>\n");
echo("<td class=tabletorrent align=right>Ratio</td>\n");
echo("<td class=tabletorrent align=right>complete</td>\n");
echo("<td class=tabletorrent align=right>HitnRun</td>\n");
echo("<td class=tabletorrent align=right>Marked</td>\n");
echo("<td class=tabletorrent align=right>Seedtime</td>\n");
echo("<td class=tabletorrent align=right>Leechtime</td>\n");
echo("<td class=tabletorrent align=center>Last action</td>\n");
echo("<td class=tabletorrent align=center>Completed At</td>\n");
echo("<td class=tabletorrent align=center>Port</td>\n");
echo("<td class=tabletorrent align=center>seeding</td>\n");
echo("<td class=tabletorrent align=center>Announced</td>\n");
echo("</tr>\n");

$res = mysql_query("SELECT s.*, size, username, parked, warned, enabled, donor, timesann, hit_and_run, mark_of_cain FROM snatched AS s INNER JOIN users ON s.userid = users.id INNER JOIN torrents ON s.torrentid = torrents.id WHERE torrentid =".unsafeChar($id)." ORDER BY complete_date DESC $limit") or sqlerr();
while ($arr = mysql_fetch_assoc($res)) {
    $upspeed = ($arr["upspeed"] > 0 ? prefixed($arr["upspeed"]) : ($arr["seedtime"] > 0 ? prefixed($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) : prefixed(0)));
    $downspeed = ($arr["downspeed"] > 0 ? prefixed($arr["downspeed"]) : ($arr["leechtime"] > 0 ? prefixed($arr["downloaded"] / $arr["leechtime"]) : prefixed(0)));
    $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
    $completed = sprintf("%.2f%%", 100 * (1 - ($arr["to_go"] / $arr["size"])));
    $res9 = mysql_query("SELECT seeder FROM peers WHERE torrent=$_GET[id] AND userid=$arr[userid]");
    $arr9 = mysql_fetch_assoc($res9);
    echo("<tr>\n");
        echo("<td align=left><a href=userdetails.php?id=$arr[userid]>$arr[username]</a>" . get_user_icons($arr) . "</td>\n");
    echo("<td align=right>" . safeChar($arr["id"]) . "</td>\n");
    echo("<td align=center>" . ($arr["connectable"] == "yes" ? "<img src=/pic/online.gif>" : "<img src=/pic/offline.gif>") . "</td>\n");
    echo("<td align=right>" . prefixed($arr["uploaded"]) . "</td>\n");
    echo("<td align=right>$upspeed/s</td>\n");
    echo("<td align=right>" . prefixed($arr["downloaded"]) . "</td>\n");
    echo("<td align=right>$downspeed/s</td>\n");
    echo("<td align=right>$ratio</td>\n");
    echo("<td align=right>$completed</td>\n");
    echo("<td align=right>".safeChar($arr["hit_and_run"])."</td>\n");
    echo("<td align=right>".safeChar($arr["mark_of_cain"])."</td>\n");
    echo("<td align=right><center><b>" . get_snatched_color($arr["seedtime"]) . "</b></center></td>\n");
    echo("<td align=right>" . mkprettytime($arr["leechtime"]) . "</td>\n");
    echo("<td align=center>$arr[last_action]</td>\n");
    echo("<td align=center>" . safeChar($arr["complete_date"] == "0000-00-00 00:00:00" ? "Not Complete Yet" : $arr["complete_date"]) . "</td>\n");
    echo("<td align=center>" . safeChar($arr[port]) . "</td>\n");
    echo("<td align=center>" . ($arr9["seeder"] == "yes" ? "<img src=" . $pic_base_url . "online.gif border=0 alt=\"active Seeder\">" : "<img src=" . $pic_base_url . "offline.gif border=0 alt=\"Not seeding!\">") . "</td>\n");
    echo("<td align=right>" . safeChar($arr["timesann"]) . "</td>\n");
    echo("</tr>\n");
}
echo("</table>\n");
if ($count > $perpage)
    echo("$pagerbottom");
stdfoot();

?>