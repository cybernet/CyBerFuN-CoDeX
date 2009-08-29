<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
stdhead();

if ($_GET["addsl"] == "1") {
    $tid = 0 + $_GET["tid"];
    $uid = 0 + $_GET["userid"];
    if (get_user_class() < UC_MODERATOR)
        stderr(_("Error"), _("Permission denied."));
    $slr = mysql_query("SELECT sl_warned FROM snatched WHERE id='" . $uid . "'")or sqlerr(__FILE__, __LINE__);
    $sla = mysql_fetch_assoc($slr);
    if ($sla["sl_warned"] == "yes") {
        echo "<b><font color=red size=3><b>Looks like its already done,youre to slow!<br /><a href=snatchleave.php>back to the List</a></b></font>";
        stdfoot();
        die();
    } else {
        mysql_query("UPDATE snatched SET sl_warned='yes' WHERE torrentid='" . $tid . "' AND userid='" . $uid . "'")or sqlerr(__FILE__, __LINE__);
        echo "<b><font color=red size=3>Successfully added to the list</b></font>";
    }
} elseif ($_GET["remsl"] == "1") {
    $tid = 0 + $_GET["tid"];
    $uid = 0 + $_GET["userid"];
    if (get_user_class() < UC_MODERATOR)
        stderr(_("Error"), _("Permission denied."));
    $slr = mysql_query("SELECT sl_warned FROM snatched WHERE torrentid='" . $tid . "' AND userid='" . $uid . "'")or sqlerr(__FILE__, __LINE__);
    $sla = mysql_fetch_assoc($slr);
    if ($sla["sl_warned"] == "no") {
        echo "<b><font color=red size=3><b>Looks like its already done,youre to slow!<br /><a href=snatchleave.php>back to the List</a></b></font>";
        stdfoot();
        die();
    } else {
        mysql_query("UPDATE snatched SET sl_warned='no' WHERE torrentid='" . $tid . "' AND userid='" . $uid . "'")or sqlerr(__FILE__, __LINE__);
        echo "<b><font color=red size=3>Successfully removed from the List</b></font>";
    }
}
//$scp = 172800;
$scp = 86400;
$cpdt = sqlesc(get_date_time(gmtime() - $scp));

if ($_GET["done"] == "yes")
    $add = "AND snatched.sl_warned='yes'";
elseif ($_GET["done"] == "no")
    $add = "AND snatched.sl_warned='no'";
else
    $add = "";
$res = mysql_query("SELECT snatched.userid, snatched.torrentid, snatched.uploaded, snatched.downloaded, snatched.last_action, snatched.seedtime, snatched.sl_warned, users.username, users.immun, torrents.size, torrents.name FROM snatched JOIN users ON snatched.userid = users.id JOIN torrents ON torrents.id = snatched.torrentid WHERE snatched.finished='yes' AND snatched.seedtime < 43200 AND snatched.uploaded < (torrents.size / 2) AND snatched.seeder='no' AND users.enabled='yes' AND snatched.complete_date < $cpdt AND users.immun='no' $add AND users.class < " . UC_CODER . " ORDER BY users.id") or sqlerr(__FILE__, __LINE__);
echo "<table><tr><h1>Users are not seeding and have not reached Fileratio/Minimum site seedtime :</h1></tr>";
echo "" . ($_GET["done"] == "no"?"<table><tr style=\"border:none;\"><td style=\"border:none;\"><a href=\"" . $_SERVER['PHP_SELF'] . "?done=yes\">already h&r warned members</a></td></tr></table><br />":"<table><tr style=\"border:none;\"><td style=\"border:none;\"><a href=\"" . $_SERVER['PHP_SELF'] . "?done=no\">not warned h&r users</a></td></tr></table><br />") . "";
if (mysql_num_rows($res) == 0)
    echo "<table><tr><td class=colhead><font size=3>Cant believe,the list is empty!</font></td></tr></table>";
else {
    echo "<table><tr><td class=colhead>Member</td><td class=colhead>Torrent</td><td class=colhead>Fileratio</td><td class=colhead>Seedtime</td><td class=colhead>last active</td><td class=colhead>Status</td></tr>";
    while ($row = mysql_fetch_assoc($res)) {
        echo "<tr><td><a href=\"javascript:ajaxpage('inpageuser.php?id=" . $row["userid"] . "&tid=" . $row["torrentid"] . "', 'contentarea');\">" . $row["username"] . "</a><td><a href=details.php?id=" . $row["torrentid"] . ">" . CutName($row["name"], 45) . "</td><td>" . number_format($row["uploaded"] / $row["size"], 3) . "</td><td>" . mkprettytime($row["seedtime"]) . "</td><td>" . date("d.m.Y H:i:s", strtotime($row["last_action"])) . "</td><td>" . ($row["sl_warned"] == "no"?"<a href=\"" . $_SERVER['PHP_SELF'] . "?addsl=1&tid=" . $row["torrentid"] . "&userid=" . $row["userid"] . "&done=no\">Warn</a>":"Warn <a href=\"" . $_SERVER['PHP_SELF'] . "?remsl=1&tid=" . $row["torrentid"] . "&userid=" . $row["userid"] . "\">[R]</a>") . "</td></tr>";
    }
    echo "</table>";

    echo "<div id=\"contentarea\"></div>";
}

stdfoot();

?>