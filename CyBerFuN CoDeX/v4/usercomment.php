<?php
// usercomments.php - by pdq - based on comments.php, duh :P
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
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


if ($action == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userid = 0 + $_POST["userid"];
        if (!is_valid_id($userid))
            stderr("Error", "Invalid ID.");

        $res = mysql_query("SELECT username FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_array($res, MYSQL_NUM);
        if (!$arr)
            stderr("Error", "No user with that ID.");

        $text = trim($_POST["text"]);
        if (!$text)
            stderr("Error", "Comment body cannot be empty!");

        mysql_query("INSERT INTO usercomments (user, userid, added, text, ori_text) VALUES (" . $CURUSER["id"] . ",$userid, '" . get_date_time() . "', " . sqlesc($text) . "," . sqlesc($text) . ")");

        $newid = mysql_insert_id();

        mysql_query("UPDATE users SET comments = comments + 1 WHERE id = $userid");

        header("Refresh: 0; url=userfriends.php?id=$userid&viewcomm=$newid#comm$newid");
        die;
    }

    $userid = 0 + $_GET["userid"];
    if (!is_valid_id($userid))
        stderr("Error", "Invalid ID.");

    $res = mysql_query("SELECT username FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    if (!$arr)
        stderr("Error", "No user with that ID.");

    stdhead("Add a comment for \"" . $arr["username"] . "\"");

    print("<h1>Add a comment for \"" . htmlspecialchars($arr["username"]) . "\"</h1>\n");
    print("<p><form method=\"post\" action=\"usercomment.php?action=add\">\n");
    print("<input type=\"hidden\" name=\"userid\" value=\"$userid\"/>\n");
    print("<textarea name=\"text\" rows=\"10\" cols=\"60\"></textarea></p>\n");
    print("<p><input type=\"submit\" class=btn value=\"Do it!\" /></p></form>\n");

    $res = mysql_query("SELECT usercomments.id, text, usercomments.added, username, users.id as user, users.avatar FROM usercomments LEFT JOIN users ON usercomments.user = users.id WHERE user = $userid ORDER BY usercomments.id DESC LIMIT 5");

    $allrows = array();
    while ($row = mysql_fetch_assoc($res))
    $allrows[] = $row;

    if (count($allrows)) {
        print("<h2>Most recent comments, in reverse order</h2>\n");
        commenttable($allrows);
    }

    stdfoot();
    die;
} elseif ($action == "edit") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $res = mysql_query("SELECT c.*, u.username FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    if (!$arr)
        stderr("Error", "Invalid ID.");

    if ($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $text = $_POST["text"];
        $returnto = $_POST["returnto"];

        if ($text == "")
            stderr("Error", "Comment body cannot be empty!");

        $text = sqlesc($text);

        $editedat = sqlesc(get_date_time());

        mysql_query("UPDATE usercomments SET text=$text, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
            header("Location: " . htmlspecialchars($returnto) . "");
        else
            header("Location: $BASEURL/"); // change later ----------------------
        die;
    }

    stdhead("Edit comment for \"" . $arr["username"] . "\"");

    print("<h1>Edit comment for \"" . htmlspecialchars($arr["username"]) . "\"</h1><p>\n");
    print("<form method=\"post\" action=\"usercomment.php?action=edit&amp;cid=$commentid\">\n");
    print("<input type=\"hidden\" name=\"returnto\" value=\"" . $_SERVER["HTTP_REFERER"] . "\" />\n");
    print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
    print("<textarea name=\"text\" rows=\"10\" cols=\"60\">" . htmlspecialchars($arr["text"]) . "</textarea></p>\n");
    print("<p><input type=\"submit\" class=btn value=\"Do it!\" /></p></form>\n");

    stdfoot();
    die;
} elseif ($action == "delete") {
    if ($arr['id'] != $CURUSER['id']) {
        if (get_user_class() < UC_MODERATOR)
            stderr("Error", "Permission denied.");
    }
    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $sure = isset($_GET["sure"]) ? (int)$_GET["sure"] : false;

    if (!$sure) {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href=usercomment.php?action=delete&cid=$commentid&sure=1" .
            ($referer ? "&returnto=" . urlencode($referer) : "") . ">here</a> if you are sure.");
    }

    $res = mysql_query("SELECT userid FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    if ($arr)
        $userid = $arr["userid"];

    mysql_query("DELETE FROM usercomments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    if ($userid && mysql_affected_rows() > 0)
        mysql_query("UPDATE users SET comments = comments - 1 WHERE id = $userid");

    $returnto = $_GET["returnto"];

    if ($returnto)
        header("Location: " . htmlspecialchars($returnto) . "");
    else
        header("Location: $BASEURL/"); // change later ----------------------
    die;
} elseif ($action == "vieworiginal") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $res = mysql_query("SELECT c.*, u.username FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    if (!$arr)
        stderr("Error", "Invalid ID");

    stdhead("Original comment");
    print("<h1>Original contents of comment #$commentid</h1><p>\n");
    print("<table width=500 border=1 cellspacing=0 cellpadding=5>");
    print("<tr><td class=comment>\n");
    echo htmlspecialchars($arr["ori_text"]);
    print("</td></tr></table>\n");

    $returnto = $_SERVER["HTTP_REFERER"];
    // $returnto = "userfriends.php?id=$userid&amp;viewcomm=$commentid#$commentid";
    if ($returnto)
        print("<p><font size=small>(<a href=$returnto>back</a>)</font></p>\n");

    stdfoot();
    die;
} else
    stderr("Error", "Unknown action");

die;

?>