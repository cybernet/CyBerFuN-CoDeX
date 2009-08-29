<?php
// ==modified sortable by event site log - color coding by event added
// ==credits to original coder hellix :)
require ("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

function puke($text = "w00t")
{
    stderr("w00t", $text);
}

if (get_user_class() < UC_MODERATOR)
    stderr("Error", "Permission denied!");

function get_typ_name($typ)
{
    switch ($typ) {
        case "torrentupload": return "Torrent uploaded";
        case "torrentedit": return "Torrent Edited";
        case "torrentdelete": return "Torrent Delete";
        case "autowarn": return "System Warnings";
        case "autodewarn": return "System Warnings Removed";
        case "customsmile": return "System Removed CustomSmilies";
        case "autoban": return "System Ban  - Because of Bad Ratio";
        case "promotion": return "Promotion";
        case "demotion": return "Demotion";
        case "addwarn": return "Warning issued";
        case "remwarn": return "Warning removed";
        case "accenabled": return "Account Enabled";
        case "accdisabled": return "Account Disabled";
        case "accdeleted": return "Account deleted";
        case "passwordreset": return "Password reset";
        case "ratioedit": return "Ratio edit";
        case "newmember": return "New member";
        case "autoclean": return "Auto cleanups";
        case "slowautoclean": return "Slow Auto cleanups";
        case "autohitrun": return "Auto Hit And Run cleanups";
        case "autobackupdb": return "Auto BackUps";
        case "autooptimizedb": return "Auto Optimizations";
        case "autoremdon": return "Donor Expired";
        case "autoremvip": return "Vip Expired";
        case "staffaction": return "Staff panel edited";
        case "shoutcom": return "Shoutbox commands used";
        case "userdelete": return "User deleted";
    }
}

$timerange = array(3600 => "1 Hour",
    3 * 3600 => "3 Hours",
    6 * 3600 => "6 Hours",
    9 * 3600 => "9 Hours",
    12 * 3600 => "12 Hours",
    18 * 3600 => "18 Hours",
    24 * 3600 => "1 Day",
    2 * 24 * 3600 => "2 Days",
    3 * 24 * 3600 => "3 Days",
    4 * 24 * 3600 => "4 Days",
    5 * 24 * 3600 => "5 Days",
    6 * 24 * 3600 => "6 Days",
    7 * 24 * 3600 => "1 Week",
    14 * 24 * 3600 => "2 Weeks"
    );

$types = array('torrentupload', 'torrentedit', 'torrentdelete', 'promotion', 'demotion', 'addwarn', 'remwarn', 'accenabled', 'accdisabled', 'accdeleted', 'passwordreset', 'ratioedit', 'newmember', 'autoclean', 'slowautoclean', 'autohitrun', 'autobackupdb', 'autooptimizedb', 'autowarn', 'autodewarn', 'autoremvip', 'autoremdon', 'customsmile','staffaction','shoutcom','userdelete');
// Delete log items older than two weeks
$secs = 14 * 24 * 3600;
stdhead("Site log");
sql_query("DELETE FROM sitelog WHERE " . time() . " - UNIX_TIMESTAMP(added) > $secs") or sqlerr(__FILE__, __LINE__);
$where = "WHERE ";
$typelist = Array();

if (isset($_GET["types"])) {
    foreach ($_GET["types"] as $type) {
        $typelist[] = sqlesc($type);
    }
    $where .= "type IN (" . implode(",", $typelist) . ") AND ";
}

if (isset($_GET["timerange"]))
    $where .= time() . "-UNIX_TIMESTAMP(added)<" . intval($_GET["timerange"]);
else {
    $where .= time() . " - UNIX_TIMESTAMP(added) < 432000";
    $_GET["timerange"] = 432000;
}

echo("<form action=\"log.php\" method=\"get\"><a name=\"log\"></a><center>");
begin_table();
echo("<tr>\n");

$I = 0;

foreach ($types as $type) {
    if ($I == 4) {
        $I = 0;
        echo("</tr><tr>\n");
    }
    echo("<td class=\"tablea\"><input type=\"checkbox\" name=\"types[]\" value=\"$type\"");
    if (in_array(sqlesc($type), $typelist))
        echo(" checked=checked");

    echo("> <a href=\"log.php?timerange=" . intval($_GET["timerange"]) . "&amp;types[]=$type&amp;filter=1#log\">" . get_typ_name($type) . "</a></td>\n");
    $I++;
}

if ($I < 4)
    echo "<td colspan=\"" . (5 - $I) . "\"  class=\"tablea\">&nbsp;</td>\n";
echo("</tr><tr><td class=\"tablea\" align=\"center\"><a href=\"log.php?timerange=" . intval($_GET["timerange"]) . "&amp;filter=1#log\">Show all</a></td><td class=\"tablea\" colspan=\"2\" align=\"center\">Time: <select name=\"timerange\" size=\"1\">\n");
foreach ($timerange as $range => $desc) {
    echo "<option value=\"$range\"";
    if (intval($_GET["timerange"]) == $range)
        echo " selected=\"selected\"";
    echo ">$desc</option>\n";
}

echo("</select></td><td class=\"tablea\" align=\"center\"><input type=\"submit\" name=\"filter\" value=\"Filters\"></td></tr></table></form><br>");

if (isset($_GET["filter"])) {
    $res = sql_query("SELECT type, added, txt FROM sitelog $where ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
        echo("<b>There are no events with the desired types.</b>\n");
    else {
        echo("<b>There were " . mysql_num_rows($res) . " Events with the desired types.</b>\n");
        begin_table(true);
        echo("<tr><td class=tablecat align=left>Date</td><td class=tablecat align=left>Time</td><td class=tablecat align=left>Type</td><td class=tablecat align=left>Event</td></tr>\n");
        while ($arr = mysql_fetch_assoc($res)) {
            $color = 'grey';
            if (strpos($arr['txt'], 'done')) $color = "#dedede";
            if (strpos($arr['txt'], 'Uploaded by')) $color = "#2e8b21";
            if (strpos($arr['txt'], 'Auto cleanup')) $color = "#ff0000";
            if (strpos($arr['txt'], 'Auto hit and run clean')) $color = "green";
            if (strpos($arr['txt'], 'Auto Back Up')) $color = "lightblue";
            if (strpos($arr['txt'], 'Delayed cleanup')) $color = "orange";
            if (strpos($arr['txt'], 'Auto Optimization')) $color = "teal";
            if (strpos($arr['txt'], 'from User to Power User')) $color = "purple";
            if (strpos($arr['txt'], 'from Power User to User')) $color = "yellow";
            if (strpos($arr['txt'], 'was created')) $color = "#CC9966";
            if (strpos($arr['txt'], 'was invited by')) $color = "#CC9966";
            if (strpos($arr['txt'], 'System enabled download')) $color = "lightgreen";
            if (strpos($arr['txt'], 'System warned and disabed download')) $color = "lightblue";
            if (strpos($arr['txt'], 'System Removed Warning')) $color = "lime";
            if (strpos($arr['txt'], 'System removed auto leech Warning')) $color = "pink";
            if (strpos($arr['txt'], 'System applied auto leech Warning')) $color = "darkred";
            if (strpos($arr['txt'], 'Password reset.')) $color = "#CC9966";
            if (strpos($arr['txt'], 'was deleted by')) $color = "#CC6666";
            if (strpos($arr['txt'], 'Deleted')) $color = "#CC6666";
            if (strpos($arr['txt'], 'was updated by')) $color = "#1dab20";
            if (strpos($arr['txt'], 'Edited by')) $color = "blue";
            if (strpos($arr['txt'], 'Shoutbox command used')) $color = "orange";
            if (strpos($arr['txt'], 'Staff panel edited')) $color = "orange";
                 
            $type = get_typ_name($arr["type"]);
            $date = substr($arr['added'], 0, strpos($arr['added'], " "));
            $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
            echo("<tr><td class=tableb style=background-color:$color>$date</td><td class=tablea style=background-color:$color>$time</td><td class=tableb align=left nowrap=nowrap style=background-color:$color>$type</td><td class=tablea align=left style=background-color:$color>" . $arr['txt'] . "</td></tr>\n");
        }
        echo("</table>");
    }
    echo("<p>All times are local.</p>\n");
}
stdfoot();

?>