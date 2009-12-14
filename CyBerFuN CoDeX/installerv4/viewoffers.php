<?php
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
parked();
function offcommenttable($rows)
{
    global $CURUSER, $HTTP_SERVER_VARS;
    begin_main_frame();
    begin_frame();
    $count = 0;
    $count2 = '';
    $id = '';
    foreach ($rows as $row) {
        // =======change colors
        if ($count2 == 0) {
            $count2 = $count2 + 1;
            $class = "clearalt6";
        } else {
            $count2 = 0;
            $class = "clearalt7";
        }
        print("<br>");
        begin_table(true);
        print("<tr><td class=colhead colspan=2><p class=sub><a name=comment_" . $row["id"] . ">#" . $row["id"] . "</a> by: ");
        if (isset($row["username"])) {
            $username = $row["username"];
            $ratres = sql_query("SELECT uploaded, downloaded from users where username='$username'");
            $rat = mysql_fetch_array($ratres);
            if ($rat["downloaded"] > 0) {
                $ratio = $rat['uploaded'] / $rat['downloaded'];
                $ratio = number_format($ratio, 3);
                $color = get_ratio_color($ratio);
                if ($color)
                    $ratio = "<font color=$color>$ratio</font>";
            } else
            if ($rat["uploaded"] > 0)
                $ratio = "Inf.";
            else
                $ratio = "---";

            $title = $row["title"];
            if ($title == "")
                $title = get_user_class_name($row["class"]);
            else
                $title = safechar($title);
            print("<a name=comm" . $row["id"] . " href=userdetails.php?id=" . $row["user"] . "><b>" .
                safechar($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ? "<img src=pic/star.gif alt='Donor'>" : "") . ($row["warned"] == "yes" ? "<img src=" . "pic/warned.gif alt=\"Warned\">" : "") . " ($title) (ratio: $ratio)\n");
        } else
            print("<a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a>\n");

        print(" at " . $row["added"] . " GMT&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
            ($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? "[ <a href=offcomment.php?action=edit&amp;cid=$row[id]>Edit</a> ] " : "") .
            (get_user_class() >= UC_MODERATOR ? "[ <a href=offcomment.php?action=delete&amp;cid=$row[id]>Delete</a> ]" : "") .
            // ($row["editedby"] && get_user_class() >= UC_MODERATOR ? "" : "") . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[ <a href=userdetails.php?id=" . $row["user"] . ">Profile</a> ] [ <a href=sendmessage.php?receiver=" . $row["user"] . ">PM</a> ] [ <form action=report.php?type=Offer&id=$rowid method=post> for breaking the rules <input class=button type=submit name=submit value=\"Report Offer\"></form> ]</p>\n");
            ($row["editedby"] && get_user_class() >= UC_MODERATOR ? "" : "") . " [ <a href=userdetails.php?id=" . $row["user"] . ">Profile</a> ] [ <a href=sendmessage.php?receiver=" . $row["user"] . ">PM</a> ] [ <a href=report.php?type=Offer_Comment&id=$row[id]>Report</a> ]</p>\n");
        $avatar = ($CURUSER["avatars"] == "yes" ? safechar($row["avatar"]) : "");
        if (!$avatar)
            $avatar = "pic/default_avatar.gif";

        $text = format_comment($row["text"]);
        if ($row["editedby"])
            $text .= "<p><font size=1 class=small>Edited by <a href=userdetails.php?id=$row[editedby]><b>$row[username]</b></a>  $row[editedat] GMT</font></p>\n";
        print("</td></tr><tr valign=top><td align=center width=150 class=$class><img width=150 src=$avatar></td><td class=$class>$text</td></tr>\n");
        end_table();
    }
    end_frame();
    end_main_frame();
    // stdfoot();
}

function bark($msg)
{
    stdhead("Offer Error");
    stdmsg("Error!", $msg);
    stdfoot();
    exit;
}

if (isset($_GET["category"])) {
    $categ = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    if (!is_valid_id($categ))
        stderr("Error", "I smell a rat!");
}

if (isset($_GET["id"])) {
    $id = 0 + htmlentities($_GET["id"]);
    if (ereg("^[0-9]+$", !$id))
        stderr("Error", "I smell a rat!");
}
// ==== add offer
if (isset($_GET["add_offer"])) {
    $add_offer = 0 + $_GET["add_offer"];
    if ($add_offer != '1')
        stderr("Error", "I smell a rat!");

    stdhead("Offer");
    begin_frame();
    print("<table border=1 width=800 cellspacing=0 cellpadding=5><tr><td class=colhead align=left>" . "Please search torrents before adding an Offer!</td></tr><tr><td align=left class=clearalt6><form method=get action=browse.php>" . "<input type=text name=search size=40 value=\"$searchstr\" />in <select name=cat> <option value=0>(all types)</option>");

    $cats = genrelist();
    $catdropdown = "";
    foreach ($cats as $cat) {
        $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
        if ($cat["id"] == $_GET["cat"])
            $catdropdown .= " selected=\"selected\"";
        $catdropdown .= ">" . safechar($cat["name"]) . "</option>\n";
    }

    $deadchkbox = "<input type=\"checkbox\" name=\"incldead\" value=\"1\"";
    if ($_GET["incldead"])
        $deadchkbox .= " checked=\"checked\"";
    $deadchkbox .= " /> including dead torrents\n";
    print(" " . $catdropdown . " </select> " . $deadchkbox . " <input type=submit value=Search! class=button /></form></td></tr></table><br>\n");

    print("<div align=Center><form action=" . $_SERVER["PHP_SELF"] . "?new_offer=1  name=compose method=post><br><br>" . "<table border=1 cellspacing=0 cellpadding=10><tr><td class=colhead align=center colspan=2>Offers are open to all users... a great ratio boost!</td><tr>\n");

    $s = "<select name=type>\n<option value=0>(Select)</option>\n";
    $cats = genrelist();
    foreach ($cats as $row)
    $s .= "<option value=" . $row["id"] . ">" . safechar($row["name"]) . "</option>\n";
    $s .= "</select>\n";
    print("<tr><td align=right class=clearalt6><b>Type:</b></td><td align=left class=clearalt6> $s</td></tr>" . "<tr><td align=right class=clearalt6><b>Title Offered:</b></td><td align=left class=clearalt6><input type=text name=name size=80 />" . "</td></tr><tr><td align=right class=clearalt6><b>Image or Photo:</b></td><td align=left class=clearalt6>" . "<input type=text name=picture size=80><br>(Link to the picture. Will be shown in description)</td></tr>" . "<tr><td align=right class=clearalt6><b>Description:</b></td><td align=left class=clearalt6>\n");
    print("<textarea name=body rows=10 cols=60></textarea></p>\n");
    print("</td></tr><tr><td align=center colspan=2 class=clearalt6><input type=submit class=button value=\"Add Offer!\" /></td></tr></table></form><br><br>\n");
    // ===list other offers
    $res = sql_query("SELECT users.username, offers.id, offers.userid, offers.name, offers.added, uploaded, downloaded, categories.image, categories.name as cat FROM offers inner join categories on offers.category = categories.id inner join users on offers.userid = users.id order by offers.id desc LIMIT 10") or sqlerr();
    $num = mysql_num_rows($res);

    print("<table border=1 width=800 cellspacing=0 cellpadding=5><tr><td class=colhead align=left width=50>Category</td>" . "<td class=colhead align=left width=425>Offer</td><td class=colhead align=center>Added</td>" . "<td class=colhead align=center width=125>Offered By</td></tr>\n");
    $count = '';
    for ($i = 0; $i < $num; ++$i) {
        // =======change colors
        if ($count == 0) {
            $count = $count + 1;
            $class = "clearalt6";
        } else {
            $count = 0;
            $class = "clearalt7";
        }
        // =======end
        $arr = mysql_fetch_assoc($res); {
            $addedby = "<td style='padding: 0px' align=center class=$class><b><a href=userdetails.php?id=$arr[userid]>$arr[username]</a></b></td>";
        }

        print("<tr><td align=center class=$class><img src=pic/$arr[image]></td><td align=left class=$class><a href=viewoffers.php?id=$arr[id]&off_details=1><b>$arr[name]</b></a></td>" . "<td align=center class=$class>$arr[added]</td>" . "$addedby</tr>\n");
    }
    print("<tr><td align=center colspan=4 class=clearalt8><form method=\"get\" action=viewoffers.php>" . "<input type=\"submit\" value=\"Show All\" class=button /></form></td></tr></table>\n");
    end_frame();
    stdfoot();
    die;
}
// === end add offer
// === take new offer
if (isset($_GET["new_offer"])) {
    $new_offer = 0 + $_GET["new_offer"];
    if ($new_offer != '1')
        stderr("Error", "I smell a rat!");

    $userid = 0 + $CURUSER["id"];
    if (ereg("^[0-9]+$", !$userid))
        stderr("Error", "I smell a rat!");

    $name = htmlentities($_POST["name"]);
    if ($name == "")
        bark("You must enter a name!");

    $cat = (0 + $_POST["type"]);
    if (!is_valid_id($cat))
        bark("You must select a category to put the offer in!");

    $descrmain = unesc($_POST["body"]);
    if (!$descrmain)
        bark("You must enter a description!");

    if (!empty($_POST['picture'])) {
        $picture = unesc($_POST["picture"]);
        if (!preg_match("/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $picture))
            stderr("Error", "Image MUST be in jpg, gif or png format.");
        $pic = "[img]" . $picture . "[/img]\n";
    }

    $descr = "$pic";
    $descr .= "$descrmain";

    $res = sql_query("SELECT name FROM offers WHERE name =" . sqlesc($_POST[name])) or sqlerr();
    $arr = mysql_fetch_assoc($res);
    if (!$arr['name']) {
        // ===add karma //=== uncomment if you use the mod
        sql_query("UPDATE users SET seedbonus = seedbonus+10.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);

        $ret = sql_query("INSERT INTO offers (userid, name, descr, category, added) VALUES (" .
            implode(",", array_map("sqlesc", array($CURUSER["id"], $name, $descr, 0 + $_POST["type"]))) . ", '" . get_date_time() . "')");
        if (!$ret) {
            if (mysql_errno() == 1062)
                bark("!!!");
            bark("mysql puked: " . mysql_error());
        }
        $id = mysql_insert_id();

        write_log("offer $name was added by " . $CURUSER[username]);

        header("Refresh: 0; url=viewoffers.php?id=$id&off_details=1");

        stdhead("Success!");
    } else {
        stdhead("Error!");
        print("<table width=600><tr><td class=colhead align=left><h1>Error!</h1></td></tr><tr><td class=clearalt6 align=left>" . "Offer allready exists! <br><br><a class=altlink href=viewoffers.php>View all offers</a><br><br></td></tr></table>");
    }
    stdfoot();
    die;
}
// ==end take new offer
// === offer details
if (isset($_GET["off_details"])) {
    $off_details = 0 + $_GET["off_details"];
    if ($off_details != '1')
        stderr("Error", "I smell a rat!");

    $id = 0 + $_GET["id"];

    $res = sql_query("SELECT * FROM offers WHERE id = $id") or sqlerr();
    $num = mysql_fetch_array($res);

    $s = $num["name"];

    stdhead("Offer Details for \"$s\"");

    begin_frame("Details for Offer:$s", true);
    print("<table width=80% border=1 cellspacing=0 cellpadding=5><tr><td align=center colspan=2 class=colhead>" . "<font size=\"+2\"><b>$s</b></font></td></tr>");
    if ($num["descr"]) {
        $off_bb = format_comment($num["descr"]);
        print("<tr><td align=left colspan=2 class=clearalt7 valign=top>$off_bb</td></tr>");
    }
    print("<tr><td align=right class=clearalt6><b>Added:</b></td><td align=left class=clearalt6>$num[added]</td></tr>");
    if ($num["allowed"] == "pending")
        print("<tr><td align=right class=clearalt6><b>Status:</b></td><td align=left class=clearalt6><b><font color=red>Pending</font></b></td></tr>");
    elseif ($num["allowed"] == "allowed")
        print("<tr><td align=right class=clearalt6><b>Status:</b></td><td align=left class=clearalt6><b><font color=green>Allowed</font></b></td></tr>");
    else
        print("<tr><td align=right class=clearalt6><b>Status:</b></td><td align=left class=clearalt6><b><font color=red>Denied</font></b></td></tr>");

    $cres = sql_query("SELECT username FROM users WHERE id=$num[userid]");
    if (mysql_num_rows($cres) == 1) {
        $carr = mysql_fetch_assoc($cres);
        $username = "$carr[username]";
    }

    if ($CURUSER["id"] == $num["userid"] || get_user_class() >= UC_MODERATOR) {
        $edit = "[ <a class=altlink href=" . $_SERVER["PHP_SELF"] . "?id=$id&edit_offer=1>Edit Offer</a> ]";
        $delete = "[ <a class=altlink href=" . $_SERVER["PHP_SELF"] . "?id=$id&del_offer=1&sure=0>Delete Offer</a> ]";
    }

    print("<tr><td align=right class=clearalt6><b>Offered by:</b></td><td align=left class=clearalt6>" . "<a class=altlink href=userdetails.php?id=$num[userid]>$username</a> $edit $delete</td></tr>");
    // === if you want to have a pending thing for uploaders use this next bit
    if (get_user_class() >= UC_MODERATOR && $num["allowed"] == "pending")
        print("<form method=post action=viewoffers.php?allow_offer=1><tr><td align=center class=clearalt6 colspan=2><table><tr><td align=center class=clearalt6><input type=hidden value=$id name=offerid>" . "<input class=button type=submit value=Allow></td></form><td align=center class=clearalt6><form method=post action=viewoffers.php?id=$id&finish_offer=1>" . "<input type=hidden value=$id name=finish><input class=button type=submit value=\"Let votes decide\"></form></td></tr></table></td></tr>");
    // if pending
    if ($num["allowed"] == "pending") {
        print("<tr><td align=right class=clearalt6><b>Vote:</b></td><td align=left class=clearalt6><b>" . "<a href=viewoffers.php?id=$id&vote=yeah><font color=green>For</font></a></b> - <b><a href=viewoffers.php?id=$id&vote=against>" . "<font color=red>Against</font></a></b></td></tr>");
    }
    // ===upload torrent message
    if ($num["allowed"] == "allowed" && $CURUSER["id"] != $num["userid"])
        print("<tr><td align=right class=clearalt6><b>Offer Allowed:</b></td><td align=left class=clearalt6>" . "If you voted for this offer, you will be PMed when it is upped!</td></tr>");
    if ($num["allowed"] == "allowed" && $CURUSER["id"] == $num["userid"]) {
        print("<tr><td align=right class=clearalt6><b>Offer Allowed:</b></td><td align=left class=clearalt6>" . "This offer has been allowed! Please upload it as soon as possible.</td></tr>");
    }
    // === if you DON'T want to have a pending thing for uploaders use this next bit	instead
    /*
if ($CURUSER["id"] != $num["userid"]){
print("<tr><td align=right class=clearalt6><b>Vote:</b></td><td align=left class=clearalt6><b>".
"<a href=viewoffers.php?id=$id&vote=yeah><font color=green>For</font></a></b> - <b><a href=viewoffers.php?id=$id&vote=against>".
"<font color=red>Against</font></a></b></td></tr>");
}
*/
    $zres = sql_query("SELECT COUNT(*) from offervotes where vote='yeah' and offerid=$id");
    $arr = mysql_fetch_row($zres);
    $za = $arr[0];
    $pres = sql_query("SELECT COUNT(*) from offervotes where vote='against' and offerid=$id");
    $arr2 = mysql_fetch_row($pres);
    $protiv = $arr2[0];
    // === in the following section, there is a line to report comment... either remove the link or change it to work with your report script :)
    print("<tr><td align=right class=clearalt6><b><a class=altlink href=viewoffers.php?id=$id&offer_vote=1>Votes</a></b></td><td align=left class=clearalt6>" . "<b>For:</b> $za  <b>Against:</b> $protiv</td><tr><td align=right class=clearalt6 valign=top><b>Report Offer:</b></td>" . "<td align=left class=clearalt6><form action=report.php?type=Offer&id=$id method=post> for breaking the rules <input class=button type=submit name=submit value=\"Report Offer\"></form>" . "<br><br></td></tr><tr><td class=embedded colspan=2><br><p><a name=startcomments></a></p>\n");

    $commentbar = "<p align=center><a class=index href=offcomment.php?action=add&amp;tid=$id>Add Comment</a></p>\n";
    $subres = sql_query("SELECT COUNT(*) FROM comments WHERE offer = $id");
    $subrow = mysql_fetch_array($subres);
    $count = $subrow[0];
    print("</td></tr></table>");
    if (!$count) {
        print("<h2>No comments</h2>\n");
    } else {
        list($pagertop, $pagerbottom, $limit) = pager(20, $count, "viewoffers.php?id=$id&off_details=1&", array('lastpagedefault' => 1));

        $subres = sql_query("SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, " . "username, title, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE offer = " . "$id ORDER BY comments.id $limit") or sqlerr(__FILE__, __LINE__);
        $allrows = array();
        while ($subrow = mysql_fetch_array($subres))
        $allrows[] = $subrow;

        print($commentbar);
        print($pagertop);

        offcommenttable($allrows);

        print($pagerbottom);
    }

    print($commentbar);
    end_frame();
    stdfoot();
    die;
}
// === end offer details
// === allow offer by staff
if (isset($_GET["allow_offer"])) {
    if (get_user_class() < UC_MODERATOR)
        stderr("Access denied!", "this is a mans job!");

    $allow_offer = 0 + $_GET["allow_offer"];
    if ($allow_offer != '1')
        stderr("Error", "I smell a rat!");
    // === to allow the offer  credit to S4NE for this next bit :)
    // if ($_POST["offerid"]){
    $offid = 0 + $_POST["offerid"];
    if (!is_valid_id($offid))
        stderr("Error", "I smell a rat!");

    $res = sql_query("SELECT users.username, offers.userid, offers.name FROM offers inner join users on offers.userid = users.id where offers.id = $offid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);

    $msg = "$CURUSER[username] has allowed you to upload [b][url=" . $BASEURL . "/viewoffers.php?id=$offid&off_details=1]" . $arr[name] . "[/url][/b]. You will find a new option on the upload page.";

    sql_query ("UPDATE offers SET allowed = 'allowed' WHERE id = $offid") or sqlerr(__FILE__, __LINE__);
    // ===use this line if you DO HAVE subject in your PM system
    sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject) VALUES(0, 0, $arr[userid], '" . get_date_time() . "', " . sqlesc($msg) . ", 'Your Offer has been allowed')") or sqlerr(__FILE__, __LINE__);
    // ===use this line if you DO NOT have subject in your PM system
    // sql_query("INSERT INTO messages (poster, sender, receiver, added, msg) VALUES(0, 0, $arr[userid], '" . get_date_time() . "', " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
    write_log("$CURUSER[username] allowed offer $arr[name]");
    header("Refresh: 0; url=$BASEURL/viewoffers.php?id=$offid&off_details=1");
}
// === end allow the offer
// === allow offer by vote
if (isset($_GET["finish_offer"])) {
    if (get_user_class() < UC_MODERATOR)
        stderr("Access denied!", "this is a mans job!");

    $finish_offer = 0 + $_GET["finish_offer"];
    if ($finish_offer != '1')
        stderr("Error", "I smell a rat!");

    $offid = 0 + $_POST["finish"];
    if (!is_valid_id($offid))
        stderr("Error", "I smell a rat!");

    $res = sql_query("SELECT users.username, offers.userid, offers.name FROM offers inner join users on offers.userid = users.id where offers.id = $offid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);

    $voteresyes = sql_query("SELECT COUNT(*) from offervotes where vote='yeah' and offerid=$offid");
    $arryes = mysql_fetch_row($voteresyes);
    $yes = $arryes[0];
    $voteresno = sql_query("SELECT COUNT(*) from offervotes where vote='against' and offerid=$offid");
    $arrno = mysql_fetch_row($voteresno);
    $no = $arrno[0];

    if ($yes == '0' && $no == '0')
        stderr("Sorry", "No votes yet... <a class=altlink href=viewoffers.php?id=$offid&off_details=1>Back to Offer details</a>");

    if ($yes >= $no) {
        $msg = "Your Offer has been voted on. you are allowed to upload [b][url=" . $BASEURL . "/viewoffers.php?id=$offid&off_details=1]" . $arr[name] . "[/url][/b]. You will find a new option on the upload page.";
        sql_query ("UPDATE offers SET allowed = 'allowed' WHERE id = $offid") or sqlerr(__FILE__, __LINE__);
    } else {
        $msg = "Your Offer has been voted on. You are not allowed to upload [b][url=" . $BASEURL . "/viewoffers.php?id=$offid&off_details=1]" . $arr[name] . "[/url][/b].. Your offer will be deleted.";
        sql_query ("UPDATE offers SET allowed = 'denied' WHERE id = $offid") or sqlerr(__FILE__, __LINE__);
    }
    // ===use this line if you DO HAVE subject in your PM system
    sql_query("INSERT INTO messages (poster, sender, subject, receiver, added, msg) VALUES(0, 0, 'Your offer $arr[name] has been voted on', $arr[userid], '" . get_date_time() . "', " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
    // ===use this line if you DO NOT subject in your PM system
    // sql_query("INSERT INTO messages (poster, sender, receiver, added, msg) VALUES(0, 0, $arr[userid], '" . get_date_time() . "', " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
    write_log("$CURUSER[username] closed poll $arr[name]");

    header("Refresh: 0; url=$BASEURL/viewoffers.php?id=$offid&off_details=1");
    die;
}
// ===end allow offer by vote
// === edit offer
if (isset($_GET["edit_offer"])) {
    $edit_offer = 0 + $_GET["edit_offer"];
    if ($edit_offer != '1')
        stderr("Error", "I smell a rat!");

    $id = 0 + $_GET["id"];

    $res = sql_query("SELECT *,UNIX_TIMESTAMP(added) as utadded FROM offers WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    $num = mysql_fetch_array($res);
    // $timezone = display_date_time($num["utadded"] , $CURUSER[tzoffset] );	 //=== use this line if you have timezone mod
    $timezone = get_date_time($num["utadded"]);

    $s = $num["name"];
    $id2 = $num["category"];

    if ($CURUSER["id"] != $num["userid"] && get_user_class() < UC_MODERATOR)
        stderr("Error!", "This is not your Offer to edit.");

    $offer = sqlesc($s);
    $body = safechar(unesc($num["descr"]));
    $res2 = sql_query("SELECT name FROM categories WHERE id=$id2")or sqlerr(__FILE__, __LINE__);
    $num2 = mysql_fetch_array($res2);
    $name = $num2["name"];
    $s2 = "<select name=\"category\"><option value=$id2> $name </option>\n";

    $cats = genrelist();

    foreach ($cats as $row)
    $s2 .= "<option value=\"" . $row["id"] . "\">" . safechar($row["name"]) . "</option>\n";
    $s2 .= "</select>\n";

    stdhead("Edit Offer");

    print("<form method=post name=compose action=" . $_SERVER["PHP_SELF"] . "?id=$id&take_off_edit=1>" . "<table border=1 width=800 cellspacing=0 cellpadding=5><tr><td class=colhead align=left colspan=2><h1>Edit Offer " . "<img src=pic/arrow_next.gif alt=\":\"> $s</h1></td><tr><tr><td align=right class=clearalt6><b>Title:</b></td>" . "<td align=left class=clearalt6><input type=text size=40 name=name value=$offer><b> Type:</b> $s2<br><tr>" . "<td align=right class=clearalt6 valign=top><b>Image:</b></td><td align=left class=clearalt6>" . "<input type=text name=picture size=80 value=''><br>(Direct link to image. NO TAG NEEDED! Will be shown in description)" . "<tr><td align=right class=clearalt6><b>Description:</b></td><td align=left class=clearalt6>\n");
    print("<textarea name=body rows=10 cols=60></textarea></p>\n");
    print("</td></tr><tr><td align=center  class=clearalt6 colspan=2><input type=submit value='Edit Offer' class=button></td></tr></form><br><br></table><br>\n");

    stdfoot();
    die;
}
// === end edit offer
// ==== take offer edit
if (isset($_GET["take_off_edit"])) {
    $take_off_edit = 0 + $_GET["take_off_edit"];
    if ($take_off_edit != '1')
        stderr("Error", "I smell a rat!");

    $id = 0 + $_GET["id"];

    $res = sql_query("SELECT userid FROM offers WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    $num = mysql_fetch_array($res);

    if ($CURUSER["id"] != $num["userid"] && get_user_class() < UC_MODERATOR)
        stderr("Error", "Access denied.");

    $name = htmlentities($_POST["name"]);
    $pic = '';

    if (!empty($_POST['picture'])) {
        $picture = unesc($_POST["picture"]);
        if (!preg_match("/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $picture))
            stderr("Error", "Image MUST be in jpg, gif or png format.");
        $pic = "[img]" . $picture . "[/img]\n";
    }
    $descr = "$pic";
    $descr .= unesc($_POST["body"]);
    if (!$descr)
        bark("You must enter a description!");
    $cat = (0 + $_POST["category"]);
    if (!is_valid_id($cat))
        bark("You must select a category to put the Offer in!");

    $name = sqlesc($name);
    $descr = sqlesc($descr);
    $cat = sqlesc($cat);

    sql_query("UPDATE offers SET category=$cat, name=$name, descr=$descr where id=$id");

    header("Refresh: 0; url=viewoffers.php?id=$id&off_details=1");
}
// ======end take offer edit
// === offer votes list
if (isset($_GET["offer_vote"])) {
    $offer_vote = 0 + $_GET["offer_vote"];
    if ($offer_vote != '1')
        stderr("Error", "I smell a rat!");

    $offerid = 0 + htmlentities($_GET[id]);

    $res2 = sql_query("select count(offervotes.offerid) from offervotes inner join users on offervotes.userid = users.id inner join offers on offervotes.offerid = offers.id WHERE offervotes.offerid =$offerid") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_array($res2);
    $count = $row[0];

    stdhead("Voters");

    $res2 = sql_query("select name from offers where id=$offerid");
    $arr2 = mysql_fetch_assoc($res2);

    print("<h2>Offer Vote Results <a class=altlink href=viewoffers.php?id=$offerid&off_details=1><b>$arr2[name]</b></a></h2>");

    $res = sql_query("select users.id as userid,users.username, users.downloaded,users.uploaded, offers.id as offerid, offers.name from offervotes inner join users on offervotes.userid = users.id inner join offers on offervotes.offerid = offers.id WHERE offervotes.offerid =$offerid $limit") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
        print("<p align=center><b>No votes yet</b></p>\n");
    else {
        $perpage = 25;

        list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?");
        echo $pagertop;
        print("<table border=1 cellspacing=0 cellpadding=5><tr><td class=colhead>User</td><td class=colhead align=left>Uploaded</td>" . "<td class=colhead align=left>Downloaded</td><td class=colhead align=left>Ratio</td><td class=colhead align=left>Vote</td>\n");

        while ($arr = mysql_fetch_assoc($res)) {
            // =======change colors
            if ($count2 == 0) {
                $count2 = $count2 + 1;
                $class = "clearalt6";
            } else {
                $count2 = 0;
                $class = "clearalt7";
            }

            $vres = sql_query("select vote from offervotes where offerid=$offerid and userid=$arr[userid]") or sqlerr(__FILE__, __LINE__);
            $vrow = mysql_fetch_array($vres);
            if ($vrow[vote] == 'yeah') $vote = "<b><font color=green>yeah</font></b>";
            elseif ($vrow[vote] == 'against') $vote = "<b><font color=red>Against</font></b>";

            if ($arr["downloaded"] > 0) {
                $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
                $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
            } else
            if ($arr["uploaded"] > 0)
                $ratio = "Inf.";
            else
                $ratio = "---";
            $uploaded = mksize($arr["uploaded"]);
            $joindate = "$arr[added] (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"])) . " ago)";
            $downloaded = mksize($arr["downloaded"]);
            if ($arr["enabled"] == 'no')
                $enabled = "<font color = red>No</font>";
            else
                $enabled = "<font color = green>Yes</font>";

            print("<tr><td class=$class><a class=altlink href=userdetails.php?id=$arr[userid]><b>$arr[username]</b></a></td>" . "<td align=left class=$class>$uploaded</td><td align=left class=$class>$downloaded</td>" . "<td align=left class=$class>$ratio</td><td align=left class=$class>$vote</td></tr>\n");
        }
        print("</table>\n");
    }

    echo $pagerbottom;

    stdfoot();
    die;
}
// === end offer votes list
// === offer votes
if (isset($_GET["vote"])) {
    $offerid = 0 + htmlentities($_GET["id"]);

    $vote = htmlentities($_GET["vote"]);
    if ($vote == 'yeah' || $vote == 'against') {
        $userid = 0 + $CURUSER["id"];
        $res = sql_query("SELECT * FROM offervotes WHERE offerid=$offerid and userid=$userid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res);
        $voted = $arr;

        if ($voted)
            stderr("You've already voted", "<p>You've already voted, max 1 vote per offer</p><p>Back to the <a class=altlink href=viewoffers.php?id=$offerid&off_details=1><b>offer details</b></a></p>");
        else {
            sql_query("UPDATE offers SET $vote = $vote + 1 WHERE id=$offerid") or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO offervotes (offerid, userid, vote) VALUES($offerid, $userid, '$vote')") or sqlerr(__FILE__, __LINE__);
            stdhead("Vote For Offer");
            print("<h2>Vote accepted</h2>");
            print("<p>Your vote have been accepted</p><p>Back to the <a class=altlink href=viewoffers.php?id=$offerid&off_details=1><b>offer details</b></a></p>");
            stdfoot();
            die;
        }
    } else
        stderr("Error", "I smell a rat!");
}
// === end offer votes
// === delete offer
if (isset($_GET["del_offer"])) {
    $del_offer = 0 + $_GET["del_offer"];
    if ($del_offer != '1')
        stderr("Error", "I smell a rat!");

    $offer = 0 + $_GET["id"];

    $userid = 0 + $CURUSER["id"];
    if (!is_valid_id($userid))
        stderr("Error", "I smell a rat!");

    $res = sql_query("SELECT * FROM offers WHERE id = $offer") or sqlerr(__FILE__, __LINE__);
    $num = mysql_fetch_array($res);

    $name = $num["name"];

    if ($userid != $num["userid"] && get_user_class() < UC_MODERATOR)
        stderr("Error", "This is not your Offer to delete!");

    if ($_GET["sure"]) {
        $sure = $_GET["sure"];
        if ($sure == '0' || $sure == '1')
            $sure = 0 + $_GET["sure"];
        else
            stderr("Error", "I smell a rat!");
    }

    if ($sure == 0)
        stderr("Delete Offer", "You`re about to delete this offer. Click\n <a class=altlink href=viewoffers.php?id=$offer&del_offer=1&sure=1>here</a>, if you`re sure.");
    elseif ($sure == 1) {
        sql_query("DELETE FROM offers WHERE id=$offer");
        sql_query("DELETE FROM offervotes WHERE offerid=$offer");
        sql_query("DELETE FROM comments WHERE offer=$offer");
        // ===add karma	//=== use this if you use the karma mod
        sql_query("UPDATE users SET seedbonus = seedbonus-10.0 WHERE id = $num[userid]") or sqlerr(__FILE__, __LINE__);
        // ===end
        if ($CURUSER["id"] != $num["userid"]) {
            $added = sqlesc(get_date_time());
            $userid = $num["userid"];
            $msg = sqlesc("Your offer [b]$num[name][/b] was deleted by[b] $CURUSER[username][/b] on $added....");
            // === if you do NOT have subject in your PMs use the next part
            // sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
            // === if you HAVE have subject in your PMs use the next part
            $subject = sqlesc("$num[name] was deleted.");
            sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES(0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
            write_log("Offer: $offer ($num[name]) was deleted by $CURUSER[username]");
            header("Refresh: 0; url=viewoffers.php");
            die;
        } else {
            write_log("Offer: $offer ($name) was deleted by $CURUSER[username]");
            header("Refresh: 0; url=viewoffers.php");
            die;
        }
    } else
        stderr("Error", "I smell a rat!");
}
// == end  delete offer
// === prolly not needed, but what the hell... basically stopping the page getting screwed up
if (isset($_GET["sort"])) {
    $sort = $_GET["sort"];
    if ($sort == 'cat' || $sort == 'name' || $sort == 'added' || $sort == 'comments' || $sort == 'yeah' || $sort == 'against')
        $sort = $_GET["sort"];
    else
        stderr("Error", "I smell a rat!");
}
// === end of prolly not needed, but what the hell :P
$categ = 0 + (isset($_GET["category"]));

if (isset($_GET["offerorid"])) {
    $offerorid = 0 + htmlentities($_GET["offerorid"]);
    if (ereg("^[0-9]+$", !$offerorid))
        stderr("Error", "I smell a rat!");
}

$search = safechar (isset($_GET["search"]));
$search = " AND offers.name like " . sqlesc('%' . $search . '%');
$sort = '';
if ($sort == "cat")
    $sort = " ORDER BY cat ";
else if ($sort == "name")
    $sort = " ORDER BY name";
else if ($sort == "added")
    $sort = " ORDER BY added ASC";
else if ($sort == "comments")
    $sort = " ORDER BY comments DESC";
else if ($sort == "yeah")
    $sort = " ORDER BY yeah DESC";
else if ($sort == "against")
    $sort = " ORDER BY against DESC";
else
    $sort = " ORDER BY added DESC";
$offerorid = '';
if ($offerorid <> null) {
    if (($categ <> null) && ($categ <> 0))
        $categ = "WHERE offers.category = " . $categ . " AND offers.userid = " . $offerorid;
    else
        $categ = "WHERE offers.userid = " . $offerorid;
} else if ($categ == 0)
    $categ = '';
else
    $categ = "WHERE offers.category = " . $categ;

$res = sql_query("SELECT count(offers.id) FROM offers inner join categories on offers.category = categories.id inner join users on offers.userid = users.id  $categ $search") or die(mysql_error());
$row = mysql_fetch_array($res);
$count = $row[0];

$perpage = 25;

list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?" . "category=" . (isset($_GET["category"])) . "&sort=" . (isset($_GET["sort"])) . "&");

$res = sql_query("SELECT users.downloaded, users.uploaded, users.username, offers.id, offers.userid, offers.name, offers.added, offers.yeah, offers.against, offers.allowed, categories.image, categories.name as cat FROM offers inner join categories on offers.category = categories.id inner join users on offers.userid = users.id  $categ $search $sort $limit") or sqlerr();
$num = mysql_num_rows($res);

stdhead("Offers");

begin_main_frame();

print("<div align=center><table border=1 width=600 cellspacing=0 cellpadding=5><tr><td class=colhead align=center><h1>" . "Offers Section</h1>\n</td></tr><tr><td class=clearalt6 align=center><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?add_offer=1>" . "Add offer</a> - <a class=altlink href=requests.php>View Requests</a><br><br></div><center>");

print("<div align=center><form method=get action=viewoffers.php><select name=category><option value=0>(Show All)</option>");

$cats = genrelist();
$catdropdown = "";
foreach ($cats as $cat) {
    $catdropdown .= "<option value=\"" . $cat["id"] . "\"";
    $catdropdown .= ">" . safechar($cat["name"]) . "</option>\n";
}
print("$catdropdown</select><input type=submit align=center value=\"view only selected\" class=button>" . "</form><br><form method=get action=viewoffers.php><b>Search offers: </b><input type=text size=40 name=search>" . "<input class=button type=submit align=center value=Search></form></td></tr></table><br /><br>");

echo $pagertop;

print("<table border=1 width=100% cellspacing=0 cellpadding=5>\n");
print("<tr><td class=colhead align=center><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?category=" . (isset($_GET["category"])) . "&sort=cat>Type</a></td>" . "<td class=colhead align=left><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?category=" . (isset($_GET["category"])) . "&sort=name>Title</a></td>" . "<td class=colhead align=center width=150><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?category=" . (isset($_GET["category"])) . "&sort=added>Added</a></td>" . "<td class=colhead align=center><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?category=" . (isset($_GET["category"])) . "&sort=comments>Comm.</a></td>" . "<td class=colhead align=center>Added by</td><td class=colhead align=center><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?category=" . (isset($_GET["category"])) . "&sort=yeah>For</a></td>" . "<td class=colhead align=center><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?category=" . (isset($_GET["category"])) . "&sort=against>Against</a></td></tr>\n");
$count2 = '';
for ($i = 0; $i < $num; ++$i) {
    // =======change colors
    if ($count2 == 0) {
        $count2 = $count2 + 1;
        $class = "clearalt6";
    } else {
        $count2 = 0;
        $class = "clearalt7";
    }
    $arr = mysql_fetch_assoc($res);
    if ($arr["downloaded"] > 0) {
        $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 2);
        $ratio = "<font color=" . get_ratio_color($ratio) . "><b>$ratio</b></font>";
    } else if ($arr["uploaded"] > 0)
        $ratio = "Inf.";
    else
        $ratio = "---";

    $addedby = "<td class=$class align=center><a class=altlink href=userdetails.php?id=$arr[userid]><b>$arr[username]</a></b> ($ratio)</td>";
    $rez = sql_query("select comments from offers where id=$arr[id]");
    $comm = mysql_fetch_array($rez);
    if ($comm["comments"] == 0)
        $comment = "0";
    else
        $comment = "<a href=viewoffers.php?id=$arr[id]&off_details=1#startcomments><b>$comm[comments]</b></a>";
    // ==== if you want allow deny for offers use this next bit
    if ($arr["allowed"] == 'allowed')
        $allowed = "<br>[ <b><font color=green>Allowed</font></b> ]";
    elseif ($arr["allowed"] == 'denied')
        $allowed = "<br>[ <b><font color=red>Denied</font></b> ]";
    else
        $allowed = "<br>[ <b><font color=orange>Pending</font></b> ]";
    // ===end
    if ($arr["yeah"] == 0) $zvote = "$arr[yeah]";
    else $zvote = "<b><a href=viewoffers.php?id=$arr[id]&offer_vote=1>$arr[yeah]</a></b>";
    if ($arr["against"] == 0) $pvote = "$arr[against]";
    else $pvote = "<b><a href=viewoffers.php?id=$arr[id]&offer_vote=1>$arr[against]</a></b>";
    print("<tr><td align=center class=$class><img src=pic/$arr[image]></td><td align=left class=$class><a class=altlink href=" . $_SERVER["PHP_SELF"] . "?id=$arr[id]&off_details=1><b>$arr[name]</b></a>$allowed</td>" . "<td align=center class=$class>$arr[added]</td><td align=center class=$class>$comment</td>$addedby<td align=center class=$class>$zvote</td><td align=center class=$class>$pvote</td></tr>\n");
}

print("</table>\n");
echo $pagerbottom;
print("</center>\n");
end_main_frame();
stdfoot();
die;

?>