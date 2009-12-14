<?php
// new friends.php - by pdq
require_once ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
$userid = isset($_GET['id']) ? (int)$_GET['id'] : $CURUSER['id'];
$action = isset($_GET["action"]) ?$_GET["action"] : '';

if (!$userid)
    $userid = $CURUSER['id'];

if (!is_valid_id($userid))
    stderr("Error", "Invalid ID.");

if ($userid != $CURUSER["id"])
    stderr("Error", "Access denied.");

$res = mysql_query("SELECT * FROM users WHERE id=$userid AND enabled='yes'") or sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_assoc($res) or stderr("Error", "No user with that ID.");
$username = $user['username'];
// action: add -------------------------------------------------------------
if ($action == 'add') {
    $targetid = (int)$_GET['targetid'];
    $type = $_GET['type'];

    if (!is_valid_id($targetid))
        stderr("Error", "Invalid ID.");

    if ($type == 'friend') {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
        $confirmed = 'confirmed';
    } elseif ($type == 'block') {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    } else
        stderr("Error", "Unknown type.");

    if ($type == 'friend') {
        $r = mysql_query("SELECT id, confirmed FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
        $q = mysql_fetch_assoc($r);
        $subject = sqlesc("New Friend Request!");
        $body = sqlesc("[url=$BASEURL/userdetails.php?id=$userid][b]This person[/b][/url] has added you to their Friends List. See all Friend Requests [url=$BASEURL/friends.php#pending][b]Here[/b][/url]\n ");
        mysql_query("INSERT INTO messages (sender, receiver, added, subject, msg) VALUES (0, $targetid, '" . get_date_time() . "', $subject, $body)") or sqlerr(__FILE__, __LINE__);
        // mysql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES (0, $targetid, '".get_date_time()."', $body)") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($r) == 1)
            stderr("Error", "User ID is already in your " . htmlentities($table_is) . " list.");
        mysql_query("INSERT INTO $table_is VALUES (0, $userid, $targetid, 'no')") or sqlerr(__FILE__, __LINE__);

        stderr("Request Added!", "The user will be informed of your Friend Request, you will be informed via PM upon confirmation.<br/ ><br/ ><a href=$BASEURL/friends.php?id=$userid#$frag><b>Go to your Friends List</b></a>", false);
        die;
    }
    if ($type == 'block') {
        $r = mysql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($r) == 1)
            stderr("Error", "User ID is already in your " . htmlentities($table_is) . " list.");

        mysql_query("INSERT INTO $table_is VALUES (0, $userid, $targetid)") or sqlerr(__FILE__, __LINE__);
        header("Location: $BASEURL/friends.php?id=$userid#$frag");

        die;
    }
}
// action: confirm ----------------------------------------------------------
if ($action == 'confirm') {
    $targetid = (int)$_GET['targetid'];
    $sure = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
    $type = isset($_GET['type']) ? ($_GET['type'] == 'friend' ? 'friend' : 'block') : stderr('Error', 'LoL');

    if (!is_valid_id($targetid))
        stderr("Error", "Invalid ID.");

    $hash = md5(' s5me mud ' . $CURUSER['id'] . $targetid . $type . 'confirm' . 'muddys the wat3r');
    if (!$sure)
        stderr("Confirm Friend", "Do you really want to confirm this person? Click\n" . "<a href=?id=$userid&action=confirm&type=$type&targetid=$targetid&sure=1&h=$hash><b>here</b></a> if you are sure.", false);
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    if ($type == 'friend') {

        mysql_query("INSERT INTO friends VALUES (0, $userid, $targetid, 'yes') ON DUPLICATE KEY UPDATE userid=$userid");
        mysql_query("UPDATE friends SET confirmed = 'yes' WHERE userid=$targetid AND friendid=$CURUSER[id]");

        $subject = sqlesc("You have a new friend!");
        $body = sqlesc("[url=$BASEURL/userdetails.php?id=$userid][b]This person[/b][/url] has just confirmed your Friendship Request. See your Friends  [url=$BASEURL/friends.php][b]Here[/b][/url]\n ");
        mysql_query("INSERT INTO messages (sender, receiver, added, subject, msg) VALUES (0, $targetid, '" . get_date_time() . "', $subject, $body)") or sqlerr(__FILE__, __LINE__);
        // mysql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES (0, $targetid, '".get_date_time()."', $body)") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
            stderr("Error", "That friend is already confirmed.");
        $frag = "friends";
    }
}
// action: delete pending ----------------------------------------------------------
elseif ($action == 'delpending') {
    $targetid = (int)$_GET['targetid'];
    $sure = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
    $type = htmlentities($_GET['type']);

    if (!is_valid_id($targetid))
        stderr("Error", "Invalid ID.");

    $hash = md5(' s5me mud ' . $CURUSER['id'] . $targetid . $type . 'confirm' . 'muddys the wat3r');
    if (!$sure)
        stderr("Delete $type Request", "Do you really want to delete this friend request? Click\n" . "<a href=?id=$userid&action=delpending&type=$type&targetid=$targetid&sure=1&h=$hash><b>here</b></a> if you are sure.", false);
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    if ($type == 'friend') {
        mysql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);

        if (mysql_affected_rows() == 0)
            stderr("Error", "No friend request found with ID");
        $frag = "friends";
    }
}
// action: delete ----------------------------------------------------------
elseif ($action == 'delete') {
    $targetid = (int)$_GET['targetid'];
    $sure = isset($_GET['sure']) ? htmlentities($_GET['sure']) : false;
    $type = htmlentities($_GET['type']);

    if (!is_valid_id($targetid))
        stderr("Error", "Invalid ID.");

    $hash = md5(' s5me mud ' . $CURUSER['id'] . $targetid . $type . 'confirm' . 'muddys the wat3r');
    if (!$sure)
        stderr("Delete $type", "Do you really want to delete a $type? Click\n" . "<a href=?id=$userid&action=delete&type=$type&targetid=$targetid&sure=1&h=$hash><b>here</b></a> if you are sure.", false);
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    if ($type == 'friend') {
        mysql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
        mysql_query("DELETE FROM friends WHERE userid=$targetid AND friendid=$userid") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
            stderr("Error", "No friend found with that ID");
        $frag = "friends";
    } elseif ($type == 'block') {
        mysql_query("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
            stderr("Error", "No block found with that ID");
        $frag = "blocks";
    } else
        stderr("Error", "Unknown type.");

    header("Location: $BASEURL/friends.php?id=$userid#$frag");
    die;
}
// main body  -----------------------------------------------------------------
stdhead("Personal lists for " . $user['username']);

$donor = ($user["donor"] == "yes") ? "<img src=" . $pic_base_url . "starbig.gif alt='Donor' style='margin-left: 4pt'>" : '';
$warned = ($user["warned"] == "yes") ? "<img src=" . $pic_base_url . "warnedbig.gif alt='Warned' style='margin-left: 4pt'>" : '';

$country = '';
include 'include/cache/countries.php';
foreach ($countries as $country)
if ($country['id'] == $user['country']) {
    $country = "<td class=embedded><img src=\"" . $pic_base_url . "flag/{$country['flagpic']}\" alt=\"" . htmlspecialchars($country['name']) . "\" style='margin-left: 8Pt'></td>";
    break;
}

/*
$country = '';
$res = mysql_query("SELECT name,flagpic FROM countries WHERE id=".$user['country']." LIMIT 1") or sqlerr();
if (mysql_num_rows($res) == 1)
{
  $arr = mysql_fetch_assoc($res);
	$country = "<td class=embedded><img src=\"".$pic_base_url."flag/".$arr['flagpic']."\" alt=\"". htmlspecialchars($arr['name']) ."\" style='margin-left: 8pt'></td>";
}*/
$pm_pic = "<img src=" . $pic_base_url . "button_pm.gif alt='Send PM' border=0>";

echo("<p><table class=main border=0 cellspacing=0 cellpadding=0>" . "<tr><td class=embedded><h1 style='margin:0px'> Personal lists for $user[username]</h1>$donor$warned$country</td></tr></table></p>\n");
echo '<h3><a href=' . $DEFAULTBASEURL . '/userfriends.php>My Friends Page</a></h3>';
echo("<table class=main width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");

echo("<h2 align=left><a name=\"friends\">Friends list</a></h2>\n");

echo("<table width=100% border=1 cellspacing=0 cellpadding=5><tr><td>");

$i = 0;
$res = mysql_query("SELECT f.friendid as id, u.username AS name, u.class, u.avatar, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=$userid AND f.confirmed='yes' ORDER BY name") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    $friends = "<em>Your friends list is empty.</em>";
else
    while ($friend = mysql_fetch_assoc($res)) {
    $dt = gmtime() - 180;

    $online = ($friend["last_access"] >= '' . get_date_time($dt) . '' ? '&nbsp;<img src=' . $pic_base_url . 'user_online.gif border=0 alt=Online>' : '<img src=' . $pic_base_url . 'user_offline.gif border=0 alt=Offline>');
    $title = htmlspecialchars($friend["title"]);
    if (!$title)
        $title = get_user_class_name($friend["class"]);
    $body1 = "<a href=userdetails.php?id=" . $friend['id'] . "><b>" . $friend['name'] . "</b></a>" .
    get_user_icons($friend) . " ($title) $online<br /><br />last seen on " . $friend['last_access'] . "<br />(" . get_elapsed_time(sql_timestamp_to_unix_timestamp($friend['last_access'])) . " ago)";
    $body2 = "<br /><a href=friends.php?id=$userid&action=delete&type=friend&targetid=" . $friend['id'] . ">Remove</a>" . "<br /><br /><a href=sendmessage.php?receiver=" . $friend['id'] . ">" . $pm_pic . "</a>";
    $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($friend["avatar"]) : "");
    // if (!$avatar)
    // $avatar = "".$pic_base_url."default_avatar.gif";
    if ($i % 2 == 0)
        echo("<table width=100% style='padding: 0px'><tr><td class=bottom style='padding: 5px' width=50% align=center>");
    else
        echo("<td class=bottom style='padding: 5px' width=50% align=center>");
    echo("<table class=main width=100% height=75px>");
    echo("<tr valign=top><td width=75 align=center style='padding: 0px'>" .
        ($avatar ? "<div style='width:75px;height:75px;overflow: hidden'><img width=75px src=\"$avatar\"></div>" : "") . "</td><td>\n");
    echo("<table class=main>");
    echo("<tr><td class=embedded style='padding: 5px' width=80%>$body1</td>\n");
    echo("<td class=embedded style='padding: 5px' width=20%>$body2</td></tr>\n");
    echo("</table>");
    echo("</td></tr>");
    echo("</td></tr></table>\n");
    if ($i % 2 == 1)
        echo("</td></tr></table>\n");
    else
        echo("</td>\n");
    $i++;
}
if ($i % 2 == 1)
    echo("<td class=bottom width=50%>&nbsp;</td></tr></table>\n");
if (isset($friends))
    echo($friends);
echo("</td></tr></table>\n");
echo("<br /><br />");
echo("<h2 align=left><a name=\"pending\">Pending Friends list</a></h2>\n");

echo("<table width=100% border=1 cellspacing=0 cellpadding=5><tr><td>");

$i = 0;
$res = mysql_query("SELECT f.userid as id, u.username AS name, u.class, u.avatar, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.userid = u.id WHERE friendid=$CURUSER[id] AND f.confirmed='no' AND NOT f.userid IN (SELECT blockid FROM blocks WHERE blockid=f.userid) ORDER BY name") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    $friends = "<em>You have no pending friends.</em>";
else
    while ($friend = mysql_fetch_assoc($res)) {
    $dt = gmtime() - 180;
    $online = ($friend["last_access"] >= '' . get_date_time($dt) . '' ? '&nbsp;<img src=' . $pic_base_url . 'user_online.gif border=0 alt=Online>' : '<img src=' . $pic_base_url . 'user_offline.gif border=0 alt=Offline>');

    $title = htmlspecialchars($friend["title"]);
    if (!$title)
        $title = get_user_class_name($friend["class"]);
    $body1 = "<a href=userdetails.php?id=" . $friend['id'] . "><b>" . $friend['name'] . "</b></a>" .
    get_user_icons($friend) . " ($title) $online<br /><br />last seen on " . $friend['last_access'] . "<br />(" . get_elapsed_time(sql_timestamp_to_unix_timestamp($friend['last_access'])) . " ago)";
    $body2 = "<br /><a href=friends.php?id=$userid&action=confirm&type=friend&targetid=" . $friend['id'] . ">Confirm</a>" . "<br /><a href=friends.php?action=add&type=block&targetid=" . $friend['id'] . ">Block</a>" . "<br /><br /><a href=sendmessage.php?receiver=" . $friend['id'] . ">" . $pm_pic . "</a>";
    $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($friend["avatar"]) : "");
    // if (!$avatar)
    // $avatar = "".$pic_base_url."default_avatar.gif";
    if ($i % 2 == 0)
        echo("<table width=100% style='padding: 0px'><tr><td class=bottom style='padding: 5px' width=50% align=center>");
    else
        echo("<td class=bottom style='padding: 5px' width=50% align=center>");
    echo("<table class=main width=100% height=75px>");
    echo("<tr valign=top><td width=75 align=center style='padding: 0px'>" .
        ($avatar ? "<div style='width:75px;height:75px;overflow: hidden'><img width=75px src=\"$avatar\"></div>" : "") . "</td><td>\n");
    echo("<table class=main>");
    echo("<tr><td class=embedded style='padding: 5px' width=80%>$body1</td>\n");
    echo("<td class=embedded style='padding: 5px' width=20%>$body2</td></tr>\n");
    echo("</table>");
    echo("</td></tr>");
    echo("</td></tr></table>\n");
    if ($i % 2 == 1)
        echo("</td></tr></table>\n");
    else
        echo("</td>\n");
    $i++;
}
if ($i % 2 == 1)
    echo("<td class=bottom width=50%>&nbsp;</td></tr></table>\n");
echo($friends);
echo("</td></tr></table>\n");

$res = mysql_query("SELECT f.friendid as id, u.username AS name, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=$userid AND f.confirmed='no' ORDER BY name") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) == 0)
    $friendreqs = "<em>Your requests list is empty.</em>";
else {
    $i = 0;
    $friendreqs = "<table width=100% cellspacing=0 cellpadding=0>";
    while ($friend = mysql_fetch_assoc($res)) {
        if ($i % 6 == 0)
            $friendreqs .= "<tr>";
        $friendreqs .= "<td style='border: none; padding: 4px; spacing: 0px;'>[<font class=small><a href=friends.php?id=$userid&action=delpending&type=friend&targetid=" . $friend['id'] . ">D</a></font>] <a href=userdetails.php?id=" . $friend['id'] . "><b>" . $friend['name'] . "</b></a>" .
        get_user_icons($friend) . "</td>";
        if ($i % 6 == 5)
            $friendreqs .= "</tr>";
        $i++;
    }
    $friendreqs .= "</table>";
}
echo("<br /><br />");
echo("<table class=main width=100% border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>");
echo("<h2 align=left><a name=\"friendreqs\">Friends awaiting confirmation</a></h2></td></tr>");
echo("<tr><td style='padding: 10px;background-color: '>");
echo("$friendreqs\n");
echo("</td></tr></table>\n");

$res = mysql_query("SELECT b.blockid as id, u.username AS name, u.donor, u.warned, u.enabled, u.last_access FROM blocks AS b LEFT JOIN users as u ON b.blockid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    $blocks = "<em>Your blocked users list is empty.</em>";
else {
    $i = 0;
    $blocks = "<table width=100% cellspacing=0 cellpadding=0>";
    while ($block = mysql_fetch_assoc($res)) {
        if ($i % 6 == 0)
            $blocks .= "<tr>";
        $blocks .= "<td style='border: none; padding: 4px; spacing: 0px;'>[<font class=small><a href=friends.php?id=$userid&action=delete&type=block&targetid=" . $block['id'] . ">D</a></font>] <a href=userdetails.php?id=" . $block['id'] . "><b>" . $block['name'] . "</b></a>" .
        get_user_icons($block) . "</td>";
        if ($i % 6 == 5)
            $blocks .= "</tr>";
        $i++;
    }
    $blocks .= "</table>";
}

echo("<br /><br />");
echo("<table class=main width=100% border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>");
echo("<h2 align=left><a name=\"blocks\">Blocked users list</a></h2></td></tr>");
echo("<tr><td style='padding: 10px;background-color: '>");
echo("$blocks\n");
echo("</td></tr></table>\n");
echo("</td></tr></table>\n");
echo("<p><a href=users.php><b>Find User/Browse User List</b></a></p>");
stdfoot();

?>