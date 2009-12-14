<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Reports - Nosey Cunt !");
// === cute solved in thing taked from helpdesk mod
function round_time($ts)
{
    $mins = floor($ts / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    $t = "";
    if ($weeks > 0)
        return "$weeks week" . ($weeks > 1 ? "s" : "");
    if ($days > 0)
        return "$days day" . ($days > 1 ? "s" : "");
    if ($hours > 0)
        return "$hours hour" . ($hours > 1 ? "s" : "");
    if ($mins > 0)
        return "$mins min" . ($mins > 1 ? "s" : "");
    return "< 1 min";
}
// === now all reports just use a single var $id and a type thanks dokty... again :P
if ($_GET["id"]) {
    $id = ($_GET["id"] ? $_GET["id"] : $_POST["id"]);
    if (!is_valid_id($id))
        stderr("Error", "Bad ID!");
}
if ($_GET["type"]) {
    $type = ($_GET["type"] ? $_GET["type"] : $_POST["type"]);
    $typesallowed = array("User", "Comment", "Request_Comment", "Offer_Comment", "Request", "Offer", "Torrent", "Hit_And_Run", "Post");
    if (!in_array($type, $typesallowed))
        stderr("Error", "Bad report type!");
}
// === Let's deal with this damn report :P
if ((isset($_GET["deal_with_report"])) || (isset($_POST["deal_with_report"]))) {
    if (!is_valid_id($_POST['id']))
        stderr("Error", "I smell a rat!");
    $how_delt_with = "how_delt_with = " . sqlesc($_POST["how_delt_with"]);
    $when_delt_with = "when_delt_with = " . sqlesc(get_date_time());
    sql_query ("UPDATE reports SET delt_with = 1, $how_delt_with, $when_delt_with , who_delt_with_it = $CURUSER[id] WHERE delt_with!=1 AND id = $_POST[id]") or sqlerr(__FILE__, __LINE__);
    //unset($_SESSION['r_added']);
}
// === end deal_with_report
// === main reports page
stdhead("Active Reports");
echo("<table width=600><tr><td class=colhead><h1>Active Reports</h1></td></tr><tr><td class=clearalt6 align=center>");
// === if get delete
if ((isset($_GET["delete"])) && (get_user_class() == UC_SYSOP)) {
    $res = sql_query("DELETE FROM reports WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    echo("<h1>Report Deleted!</h1>\n");
}
// === get the count make the page
$res = sql_query("SELECT count(id) FROM reports") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_array($res);
$count = $row[0];
$perpage = 25;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?type=$type&");

if ($count == '0')
    echo"<p align=center><b>No Reports, they are all playing nice!</b></p>";
else {
    echo($pagertop);
    echo"<table width=650><tr><td class=colhead2 align=left valign=top>Added</td><td class=colhead2 align=left valign=top>Reported by</td>" . "<td class=colhead2 align=left valign=top>Reporting What</td><td class=colhead2 align=left valign=top>Type</td><td class=colhead2 align=left valign=top>Reason</td>" . "<td class=colhead2 align=center valign=top>Dealt With</td><td class=colhead2 align=center valign=top>Deal With It</td>" . "" . (get_user_class() == UC_SYSOP ? "<td class=colhead2 align=center valign=top>Delete</td>" : "") . "</tr><form method=post action=reports.php?deal_with_report=1>";
    // === get the info
    $res_info = sql_query("SELECT reports.id, reports.reported_by, reports.reporting_what, reports.reporting_type, reports.reason, reports.who_delt_with_it, reports.delt_with, reports.added, reports.how_delt_with, reports.when_delt_with, reports.2nd_value, users.username FROM reports INNER JOIN users on reports.reported_by = users.id $where ORDER BY id desc $limit");

    while ($arr_info = mysql_fetch_assoc($res_info)) {
        // =======change colors thanks Jaits
        $count2 = (++$count2) % 2;
        $class = 'clearalt' . ($count2 == 0?'6':'7');
        // =======end
        // === cute solved in thing taked from helpdesk mod by nuerher
        $added = $arr_info["added"];
        $solved_date = $arr_info["when_delt_with"];

        if ($solved_date == "0000-00-00 00:00:00") {
            $solved_in = " [N/A]";
            $solved_color = "pink";
        } else {
            $solved_in_wtf = sql_timestamp_to_unix_timestamp($arr_info["when_delt_with"]) - sql_timestamp_to_unix_timestamp($arr_info["added"]);
            $solved_in = "&nbsp;[" . round_time($solved_in_wtf) . "]";

            if ($solved_in_wtf > 4 * 3600)
                $solved_color = "red";
            else if ($solved_in_wtf > 2 * 3600)
                $solved_color = "yellow";
            else if ($solved_in_wtf <= 3600)
                $solved_color = "green";
        }
        // === has it been delt with yet?
        if ($arr_info["delt_with"]) {
            $res_who = sql_query("SELECT username FROM users WHERE id=$arr_info[who_delt_with_it]");
            $arr_who = mysql_fetch_assoc($res_who);
            $dealtwith = "<font color=" . $solved_color . "><b>Yes -</b> </font> by: <a class=altlink href=userdetails.php?id=$arr_info[who_delt_with_it]><b>$arr_who[username]</b></a><br> in: <font color=" . $solved_color . ">" . $solved_in . "</font>";
            $checkbox = "<input type=radio name=id value=" . $arr_info['id'] . " disabled />";
        } else {
            $dealtwith = "<font color=red><b>No</b></font>";
            $checkbox = "<input type=radio name=id value=" . $arr_info['id'] . " />";
        }
        // === make a link to the reported thing
        if ($arr_info["reporting_type"] != "") {
            switch ($arr_info["reporting_type"]) {
                case "User":
                    $res_who2 = sql_query("SELECT username FROM users WHERE id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=userdetails.php?id=$arr_info[reporting_what]><b>$arr_who2[username]</b></a>";
                    break;
                case "Comment":
                    $res_who2 = sql_query("SELECT comments.user, users.username, torrents.id FROM comments, users, torrents WHERE comments.user = users.id AND comments.id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=details.php?id=$arr_who2[id]&viewcomm=$arr_info[reporting_what]#comm$arr_info[reporting_what]><b>$arr_who2[username]</b></a>";
                    break;
                case "Request_Comment":
                    $res_who2 = sql_query("SELECT comments.request, comments.user, users.username FROM comments, users WHERE comments.user = users.id AND comments.id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=viewrequests.php?id=$arr_who2[request]&req_details=1&viewcomm=$arr_info[reporting_what]#comm$arr_info[reporting_what]><b>$arr_who2[username]</b></a>";
                    break;
                case "Offer_Comment":
                    $res_who2 = sql_query("SELECT comments.offer, comments.user, users.username FROM comments, users WHERE comments.user = users.id AND comments.id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=viewoffers.php?id=$arr_who2[offer]&off_details=1&viewcomm=$arr_info[reporting_what]#comm$arr_info[reporting_what]><b>$arr_who2[username]</b></a>";
                    break;
                case "Request":
                    $res_who2 = sql_query("SELECT request FROM requests WHERE id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=viewrequests.php?id=$arr_info[reporting_what]&req_details=1><b>" . safechar($arr_who2['request']) . "</b></a>";
                    break;
                case "Offer":
                    $res_who2 = sql_query("SELECT name FROM offers WHERE id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=viewoffers.php?id=$arr_info[reporting_what]&off_details=1><b>" . safechar($arr_who2['name']) . "</b></a>";
                    break;
                case "Torrent":
                    $res_who2 = sql_query("SELECT name FROM torrents WHERE id = $arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<a class=altlink href=details.php?id=$arr_info[reporting_what]><b>" . safechar($arr_who2['name']) . "</b></a>";
                    break;
                case "Hit_And_Run":
                    $res_who2 = sql_query("SELECT users.username, torrents.name, r.2nd_value FROM users, torrents LEFT JOIN reports AS r ON r.2nd_value = torrents.id WHERE users.id=$arr_info[reporting_what]");
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<b>user:</b> <a class=altlink href=userdetails.php?id=" . $arr_info['reporting_what'] . "&completed=1><b>" . $arr_who2['username'] . "</b></a><br>hit and run on:<br> <a class=altlink href=details.php?id=" . $arr_info['2nd_value'] . "&page2=0#snatched><b>" . safechar($arr_who2['name']) . "</b></a>";
                    break;
                case "Post":
                    $res_who2 = sql_query("SELECT subject FROM topics WHERE id = " . $arr_info['2nd_value']);
                    $arr_who2 = mysql_fetch_assoc($res_who2);
                    $link_to_thing = "<b>post:</b> <a class=altlink href=forums.php?action=viewtopic&topicid=" . $arr_info['2nd_value'] . "&page=last#" . $arr_info['reporting_what'] . "><b>" . safechar($arr_who2['subject']) . "</b></a>";
                    break;
            }
        }

        echo"<tr><td align=left valign=top class=$class>" . $arr_info['added'] . "</td><td align=left valign=top class=$class><a class=altlink href=userdetails.php?id=" . $arr_info['reported_by'] . ">" . "<b>" . $arr_info['username'] . "</b></a></td><td align=left valign=top class=$class>$link_to_thing</td><td align=left valign=top class=$class><b>" . str_replace("_" , " ", $arr_info["reporting_type"]) . "</b>" . "</td><td align=left valign=top class=$class>" . safechar($arr_info['reason']) . "</td><td align=center valign=top class=$class>$dealtwith $delt_link</td><td align=center valign=middle class=$class>$checkbox</td>" . (get_user_class() == UC_SYSOP ? "<td align=center valign=middle class=$class><a class=altlink href=reports.php?id=" . $arr_info['id'] . "&delete=1><font color=red>Delete</font></a></td>" : "") . "</tr>\n";
        // ===how was it delt with?
        if ($arr_info['how_delt_with'])
            echo"<tr><td colspan=" . (get_user_class() == UC_SYSOP ? "8" : "7") . " class=$class align=left><b>Delt with by " . $arr_who['username'] . ":</b> on: " . $arr_info['when_delt_with'] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr_info['when_delt_with'])) . " ago)</td></tr><tr><td colspan=" . (get_user_class() == UC_SYSOP ? "8" : "7") . " class=$class align=left>" . safechar($arr_info['how_delt_with']) . "<br><br></td></tr>";
    }
}
echo"</table>";
if ($count > '0') {
    // === deal with it
    echo"<br><br><p align=center><b>How $CURUSER[username] Delt with this report:</b> [ required ] </p><textarea name=how_delt_with cols=70 rows=5 ></textarea><br><br>" . "<input type=submit class=button value=Confirm><br><br></form></td></tr></table>";
} //=== end if count
stdfoot();
die;

?>