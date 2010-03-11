<?php
ob_start("ob_gzhandler");
require ("include/bittorrent.php");
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
parked(); //=== comment out if you don't want to use the parked mod tongue.gif

$userid = 0 + $CURUSER["id"];
if (!is_valid_id($userid)) stderr("Error", "Invalid ID");

if (get_user_class() < UC_USER || ($CURUSER["id"] != $userid && get_user_class() < UC_MODERATOR))
    stderr("Error", "Permission denied");
// === subscribe to thread
if ($_GET["subscribe"]) {
    $subscribe = 0 + $_GET["subscribe"];
    if ($subscribe != '1')
        stderr("Error", "I smell a rat!");

    if (!isset($_GET[topicid]))
        stderr("Error", "No forum selected!");

    if ($_GET["topicid"]) {
        $topicid = 0 + safechar($_GET["topicid"]);
        if (ereg("^[0-9]+$", !$topicid))
            stderr("Error", "Bad Topic Id!");
    }

    if ((get_row_count("subscriptions", "WHERE userid=$CURUSER[id] AND topicid = $topicid")) > 0)
        stderr("Error", "Already subscribed to thread number <b> $topicid</b><br><br>Click <a href=forums.php?action=viewtopic&topicid=$topicid><b>HERE</b></a> to go back to the thread. Or click <a href=subscriptions.php><b>HERE</b></a> to view your subscriptions.");

    sql_query("INSERT INTO subscriptions (userid, topicid) VALUES ($CURUSER[id], $topicid)") or sqlerr(__FILE__, __LINE__);

    $res = sql_query("SELECT subject FROM `topics` WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr("Error", "Bad forum id!");
    $forumname = $arr["subject"];
    stderr("Sucksex", "Successfully subscribed to thread <b>$forumname</b><br><br>Click <a href=forums.php?action=viewtopic&topicid=$topicid><b>HERE</b></a> to go back to the thread. Or click <a href=subscriptions.php><b>HERE</b></a> to view your subscriptions.");
}
// === end subscribe to thread
// === Action: Delete subscription
if ($_GET["delete"]) {
    if (!isset($_POST[deletesubscription]))
        stderr("Error", "Nothing selected");

    $checked = $_POST['deletesubscription'];
    foreach ($checked as $delete) {
        sql_query ("DELETE FROM subscriptions WHERE userid = $CURUSER[id] AND topicid=" . sqlesc($delete));
    }

    header("Refresh: 0; url=$DEFAULTBASEURL/subscriptions.php?deleted=1");
}
// ===end
$res = sql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) == 1) {
    $arr = mysql_fetch_assoc($res);

    $subject = "<a class=altlink href=userdetails.php?id=$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
} else
    $subject = "unknown[$userid]";

$where_is = "p.userid = $userid AND f.minclassread <= " . $CURUSER['class'];
$order_is = "t.id DESC";
$from_is = "subscriptions AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";
$select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.topicid";
$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

stdhead("Subscriptions");
echo("<h4>Subscribed Forums for $subject</h4><p align=center>To be notified via PM when there is a new post, go to your <a class=altlink href=my.php>profile</a> and set <b><i>PM on Subscriptions</i></b> to yes</p>\n");

if ($_GET["deleted"]) {
    print ("<h1>subscription(s) Deleted</h1>");
}
// ------ Print table
begin_main_frame();

begin_frame();

if (mysql_num_rows($res) == 0)
    print("<p align=center><font size=\"+2\"><b>No Subscriptions Found</b></font></p><p>You are not yet subscribed to any forums...</p><p>To subscribe to a forum at <b>$SITENAME</b>, click the <b><i>Subscribe to this Forum</i></b> link at the top of the thread page.</p>");

while ($arr = mysql_fetch_assoc($res)) {
    $topicid = $arr["t_id"];

    $topicname = $arr["subject"];

    $forumid = $arr["f_id"];

    $forumname = $arr["name"];

    $newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;

    $order_is = "p.id DESC";
    $from_is = "posts AS p LEFT JOIN topics as t ON p.topicid = t.id LEFT JOIN forums AS f ON t.forumid = f.id";
    $select_is = "t.id, p.*";
    $where_is = "t.id = $topicid AND f.minclassread <= " . $CURUSER['class'];
    $queryposts = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";
    $res2 = sql_query($queryposts) or sqlerr(__FILE__, __LINE__);
    $arr2 = mysql_fetch_assoc($res2);

    $postid = $arr2["id"];

    $posterid = $arr2["userid"];

    $queryuser = sql_query("SELECT username FROM users WHERE id=$arr2[userid]");
    $res3 = mysql_fetch_assoc($queryuser);

    $added = $arr2["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr2["added"]))) . " ago)";

    ?>
<STYLE TYPE="text/css" MEDIA=screen>
td.clearalt6 {
background: #777777;
padding: 5px;
border: 0px;
border: hidden;
}
td.clearalt7 {
padding: 5px;
background: #555555;
border: 0px;
border: hidden;
}
</STYLE>
<?php
    // =======change colors
    if ($count2 == 0) {
        $count2 = $count2 + 1;
        $class = "clearalt7";
    } else {
        $count2 = 0;
        $class = "clearalt6";
    }
    // =======end
    print("<p class=sub><table border=0 cellspacing=0 cellpadding=0 width=737><tr><td class=colhead width=737>
" . ($newposts ? " <b><font color=red>NEW REPLY!</font></b>" : "") . "<br><b>Forum: </b>
<a class=altlink href=/forums.php?action=viewforum&forumid=$forumid>$forumname</a>
<b>Topic: </b>
<a class=altlink href=/forums.php?action=viewtopic&topicid=$topicid>$topicname</a>
<b>Post: </b>
#<a class=altlink href=/forums.php?action=viewtopic&topicid=$topicid&page=p$postid#$postid>$postid</a><br>
<b>Last Post By:</b> <a class=altlink href=userdetails.php?id=$posterid><b>$res3[username]</a> added:</b> $added </td>
<td class=colhead2 align=right width=20%>");
    // === delete subscription
    if ($_GET[check] == "yes")
        echo("<INPUT type=checkbox checked name=deletesubscription[] id=deletesubscription value=$topicid> ");
    else
        echo("<INPUT type=checkbox name=deletesubscription[] id=deletesubscription value=$topicid> ");
    // === end
    print("<b>un-subscribe</b></td></tr></table></p>\n");

    begin_table(true);

    $body = format_comment($arr2["body"]);

    if (is_valid_id($arr['editedby'])) {
        $subres = sql_query("SELECT username FROM users WHERE id=$arr[editedby]");
        if (mysql_num_rows($subres) == 1) {
            $subrow = mysql_fetch_assoc($subres);
            $body .= "<p><font size=1 class=small>Last edited by <a href=userdetails.php?id=$arr[editedby]><b>$subrow[username]</b></a> at $arr[editedat] GMT</font></p>\n";
        }
    }
    // print("<tr valign=top><td class=$class>" . CutName($body, 300) . "</td></tr>\n");
    print("<tr valign=top><td class=$class>$body</td></tr>\n"); // use this line if you don't want to cut the post

    end_table();
}

?>
<br><table width=737><tr><td align=right class=colhead><h1></h1>
<A class=altlink href="subscriptions.php?action=<?php echo $_GET[action];
?>&box=<?php echo $_GET[box];
?>&check=yes">select all</A> -
<A class=altlink href="subscriptions.php?action=<?php echo $_GET[action];
?>&box=<?php echo $_GET[box];
?>&check=no">un-select all</A>
<INPUT class=button type="submit" name="delete" value="Delete"> selected</td></tr></table> </form>
<?php

end_frame();

end_main_frame();

stdfoot();

die;

?>