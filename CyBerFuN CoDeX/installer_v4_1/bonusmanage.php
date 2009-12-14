<?php
require_once("include/bittorrent.php");
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
if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Bonus Manage - Nosey Cunt !");

$res = sql_query("SELECT * FROM bonus");

if (isset($_POST["id"]) || isset($_POST["points"]) || isset($_POST["description"]) || isset($_POST["enabled"])) {
    $id = 0 + $_POST["id"];
    $points = 0 + $_POST["bonuspoints"];
    $descr = mysql_real_escape_string($_POST["description"]);
    $enabled = "yes";

    if ($_POST["enabled"] == '') {
        $enabled = "no";
    }
    $sql = "UPDATE bonus SET points = '$points', enabled = '$enabled', description = '$descr'WHERE id = '$id'";

    switch ($id) {
        case 1:
            makeithappen($sql);
            break;
        case 2:
            makeithappen($sql);
            break;
        case 3:
            makeithappen($sql);
            break;
        case 4:
            makeithappen($sql);
            break;
        case 5:
            makeithappen($sql);
            break;
        case 6:
            makeithappen($sql);
            break;
        case 7:
            makeithappen($sql);
            break;
        case 8:
            makeithappen($sql);
            break;
        case 9:
            makeithappen($sql);
            break;
        case 10:
            makeithappen($sql);
            break;
        case 11:
            makeithappen($sql);
            break;
        case 12:
            makeithappen($sql);
            break;
        case 13:
            makeithappen($sql);
            break;
        case 14:
            makeithappen($sql);
            break;
        case 15:
            makeithappen($sql);
            break;
        case 16:
            makeithappen($sql);
            break;
        case 17:
            makeithappen($sql);
            break;
        case 18:
            makeithappen($sql);
            break;
        case 19:
            makeithappen($sql);
            break;
        case 20:
            makeithappen($sql);
            break;
        case 21:
            makeithappen($sql);
            break;
        case 22:
            makeithappen($sql);
            break;
        case 23:
            makeithappen($sql);
            break;
        case 24:
            makeithappen($sql);
            break;
    }
}

stdhead("Bonus management");

print("<h1> Welcome to the Bonus Management page</h1>");

print("<table border=2 cellpadding=5>");
print("<tr>");
print("<td class=colhead>ID </td>");
print("<td class=colhead>ENABLED </td>");
print("<td class=colhead>BONUS NAME </td>");
print("<td class=colhead>POINTS </td>");
print("<td class=colhead>DESCRIPTION </td>");
print("<td class=colhead>TYPE </td>");
print("<td class=colhead>QUANTITY </td>");
print("<td class=colhead></td>");
print("</tr>");

while ($arr = mysql_fetch_assoc($res)) {
    print("<tr>");
    print("<form method=post action=bonusmanage.php>");
    print("<td><input name=id type=hidden value=" . $arr["id"] . ">$arr[id]</td>");
    print("<td><input name=enabled type=checkbox" . ($arr["enabled"] == "yes" ? " checked" : "") . "></td>");
    print("<td>$arr[bonusname]</td>");
    print("<td><input type=text name=bonuspoints value=" . $arr["points"] . " size=4></td>");
    print("<td><textarea name=description wrap=virtual rows=4 cols=60>" . $arr["description"] . "</textarea></td>");
    print("<td>$arr[art]</td>");
    print("<td>" . (($arr["art"] == "traffic" || $arr["art"] == "gift_1" || $arr["art"] == "gift_2") ? ($arr["menge"] / 1024 / 1024 / 1024) . " GB" : $arr["menge"]) . "</td>");
    print("<td ALIGN=CENTER><input type=submit value=SUBMIT> </td>");
    print("</form>");

    print("</tr>");
}
print("</table>");

stdfoot();

?><?php

function makeithappen($sql)
{
    $done = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if ($done) {
        header("Location: $BASEURL/bonusmanage.php");
    } else {
        stderr("Opps", "Something went wrong with the sql query");
    }
}

?>