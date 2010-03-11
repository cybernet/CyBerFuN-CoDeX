<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/commenttable.php");
require_once("include/bbcode_functions.php");
$action = isset($_GET["action"]) ?$_GET["action"] : '';
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
parked(); //=== uncomment if you use the parked mod

if ($action == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $offid = 0 + $_POST["tid"];
        if (!is_valid_id($offid))
            stderr("Error", "Wrong ID");

        $res = mysql_query("SELECT name FROM offers WHERE id = $offid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_array($res);
        if (!$arr)
            stderr("Error", "No offer with that ID");

        $text = trim($_POST["body"]);
        if (!$text)
            stderr("Error", "Don't leave any fields blank!");

        mysql_query("INSERT INTO comments (user, offer, added, text, ori_text) VALUES (" . $CURUSER["id"] . ",$offid, '" . get_date_time() . "', " . sqlesc($text) . "," . sqlesc($text) . ")");

        $newid = mysql_insert_id();

        mysql_query("UPDATE offers SET comments = comments + 1 WHERE id = $offid");

        header("Refresh: 0; url=viewoffers.php?id=$offid&off_details=1&viewcomm=$newid#comm$newid");
        stdfoot();
        die;
    }

    $offid = 0 + $_GET["tid"];
    if (!is_valid_id($offid))
        stderr("Error", "Wrong ID.");

    $res = mysql_query("SELECT name FROM offers WHERE id = $offid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Error", "Wrong ID.");

    stdhead("Add comment to \"" . $arr["name"] . "\"");
    begin_main_frame();
    echo("<form method=post name=compose action=offcomment.php?action=add><input type=hidden name=tid value=$offid/>" . "<table border=1 cellspacing=0 cellpadding=10><tr><td class=colhead align=center colspan=2><b>Comment on Offer: " . "" . safechar($arr["name"]) . "</b></td><tr><tr><td align=right class=clearalt6><b>comment:</b></td>" . "<td align=left class=clearalt6>\n");
    textbbcode("compose", "body", "$body");
    echo("</td></tr><tr><td align=center colspan=2 class=clearalt6><input type=submit value='" . Okay . "' class=button></td></tr><br><br>\n");

    $res = mysql_query("SELECT comments.id, text, UNIX_TIMESTAMP(comments.added) as utadded, UNIX_TIMESTAMP(editedat) as uteditedat, comments.added, username, users.id as user, users.class, users.avatar FROM comments LEFT JOIN users ON comments.user = users.id WHERE offer = $offid ORDER BY comments.id DESC LIMIT 5");
    $allrows = array();
    while ($row = mysql_fetch_array($res))
    $allrows[] = $row;

    if (count($allrows))
        commenttable($allrows);
    end_main_frame();
    stdfoot();
    die;
} elseif ($action == "edit") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid))
        stderr("Error", "Wrong ID");

    $res = mysql_query("SELECT * FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Error", "Wrong ID");

    if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
        stderr("Error", "this is not your comment to edit.");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $text = $_POST["body"];
        $returnto = safechar($_POST["returnto"]);

        if ($text == "")
            stderr("Error", "Don't leave any fields blank!");

        $text = sqlesc($text);

        $editedat = sqlesc(get_date_time());

        mysql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);

        header("Refresh: 0; url=viewoffers.php?id=$arr[offer]&off_details=1&viewcomm=$commentid#comm$commentid");
        stdfoot();
        die;
    }

    stdhead("Edit comment");
    begin_main_frame();
    echo("<form method=post name=compose action=offcomment.php?action=edit&cid=$commentid>" . "<input type=hidden name=returnto value=\"" . $_SERVER["HTTP_REFERER"] . "\" /><input type=hidden name=cid value=$commentid />" . "<p align=center><table border=1 cellspacing=1><tr><td align=center colspan=2 class=colhead><font size=\"+2\"><b>edit comment</b>" . "</font></td></tr><tr><td align=center class=clearalt6>\n");
    $body = $arr['text'];
    textbbcode("compose", "body", safechar(unesc($body)));
    echo("</td></tr><tr><td align=center colspan=2 class=clearalt6><p><input type=submit class=button value=Edit! /></p></form></td></tr><br></table><br><br>\n");
    end_main_frame();
    stdfoot();
    die;
} elseif ($action == "delete") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Access denied.");

    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID");

    $sure = $_GET["sure"];

    if (!$sure) {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("Delete comment", "You`re about to delete this comment. Click\n" . "<a href=?action=delete&cid=$commentid&sure=1" .
            ($referer ? "&returnto=" . urlencode($referer) : "") . ">here</a>, if you`re sure.");
    }

    $res = mysql_query("SELECT offer FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if ($arr)
        $offid = $arr["offer"];

    mysql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    if ($offid && mysql_affected_rows() > 0)
        mysql_query("UPDATE offers SET comments = comments - 1 WHERE id = $offid");

    header("Refresh: 0; url=viewoffers.php?id=$offid&off_details=1");
    die;
} elseif ($action == "vieworiginal") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Access denied.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID");

    $res = mysql_query("SELECT c.*, t.name FROM comments AS c JOIN offers AS t ON c.offer = t.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Error", "Invalid ID");

    stdhead("Original");
    begin_main_frame();
    echo("<h1>Original content of comment #$commentid</h1><p>\n");
    echo("<table width=500 border=1 cellspacing=0 cellpadding=5>");
    echo("<tr><td class=comment>\n");
    echo safechar($arr["ori_text"]);
    echo("</td></tr></table>\n");

    $returnto = $_SERVER["HTTP_REFERER"];

    if ($returnto)
        echo("<p><font size=small>(<a href=$returnto>Back</a>)</font></p>\n");
    end_main_frame();
    stdfoot();
    die;
} else
    stderr("Error", "Unknown action");

die;
stdfoot();
?>