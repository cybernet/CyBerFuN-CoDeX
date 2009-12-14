<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
require_once ("include/commenttable.php");
$action = isset($_GET["action"]) ?$_GET["action"] : '';
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

    if ( $row["allow_comments"] == 'no' ) {
        stdmsg( "Sorry...", "You are not allowed to comment on this torrent" );
        exit;
    }

if ($action == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Anti Flood Code
        if (!($CURUSER['comment_count'] < $CURUSER['comment_max']))
            stderr('Notice', 'You have reached your Comment limit. Please wait 15 minutes before retrying.');

        $torrentid = 0 + $_POST["tid"];
        if (!is_valid_id($torrentid))
            stderr("Error", "Invalid ID.");

        $res = sql_query("SELECT name, owner, anonymous FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_array($res);
        if (!$arr)
            stderr("Error", "No torrent with ID.");

        $text = trim($_POST["text"]);
        if (!$text)
            stderr("Error", "Comment body cannot be empty!");

        if ($CURUSER['id'] == $arr['owner'] && $arr['anonymous'] == 'yes') {
            $anon = "'yes'";
        } else {
            $anon = "'no'";
        }
        sql_query("INSERT INTO comments (user, torrent, added, text, ori_text, anonymous) VALUES (" . $CURUSER["id"] . ",$torrentid, '" . get_date_time() . "', " . sqlesc($text) . "," . sqlesc($text) . ", $anon)");

        $newid = mysql_insert_id();

        sql_query("UPDATE torrents SET comments = comments + 1 WHERE id = $torrentid");
        // ===add karma
        sql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
        // ===end
        // Update Last comment sent...
        mysql_query("UPDATE users SET comment_count = comment_count + 1 WHERE id = " .
            sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        // --- pm if new comment mod---//
        $ras = sql_query("SELECT commentpm FROM users WHERE id = $arr[owner]") or sqlerr(__FILE__, __LINE__);
        $arg = mysql_fetch_array($ras);

        if ($arg['commentpm'] == 'yes') {
            $added = sqlesc(get_date_time());
            $subby = sqlesc("Someone has left a comment on your torrent");
            $notifs = sqlesc("You have received a comment on your torrent [url=$DEFAULTBASEURL/details.php?id=$torrentid] " . $arr['name'] . "[/url].");
            sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) VALUES(0, " . $arr['owner'] . ", $subby, $notifs, $added)") or sqlerr(__FILE__, __LINE__);
        }
        // ---end---//
        header("Location: details.php?id=$torrentid&viewcomm=$newid#comm$newid");
        die;
    }

    $torrentid = 0 + $_GET["tid"];
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");

    $res = sql_query("SELECT name FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Error", "No torrent with ID.");

    stdhead("Add a comment to \"" . $arr["name"] . "\"");

    print("<h1>Add a comment to \"" . safechar($arr["name"]) . "\"</h1>\n");
    print("<p><form name=comment method=\"post\" action=\"comment.php?action=add\">\n");
    print("<input type=\"hidden\" name=\"tid\" value=\"$torrentid\"/>\n");
    print("<table width=600 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=rowhead style='padding: 3px'></td><td align=center style='padding: 3px'>");
    textbbcode("comment", "text", ($quote?(("[quote=" . safechar($arr["username"]) . "]" . safechar(unesc($arr["body"])) . "[/quote]")):""));
    print("</td></table>\n");
    //end_frame();
    print("<center><p><input type=submit class=btn value='add'> <input type=reset class=btn value='reset'></p></center></form></br>\n");

    $res = sql_query("SELECT comments.id, text, comments.added, comments.anonymous, username, users.id as user, users.avatar FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = $torrentid ORDER BY comments.id DESC LIMIT 5");

    $allrows = array();
    while ($row = mysql_fetch_array($res))
    $allrows[] = $row;

    if (count($allrows)) {
        print("<h2>Most recent comments, in reverse order</h2>\n");
        commenttable($allrows);
    }
    stdfoot();
    die();
} elseif ($action == "edit") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $res = sql_query("SELECT c.*, t.name FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
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

        sql_query("UPDATE comments SET text=$text, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);

        if ($returnto)
            header("Location: $returnto");
        else
            header("Location: $BASEURL/"); // change later ----------------------
        die;
    }

    stdhead("Edit comment to \"" . safechar($arr["name"]) . "\"");
    begin_table();
    print("<h1>Edit comment to \"" . safechar($arr["name"]) . "\"</h1><p>\n");
    print("<form method=\"post\" name=\"compose\"action=\"comment.php?action=edit&cid=$commentid\">\n");
    print("<input type=\"hidden\" name=\"returnto\" value=\"" . $_SERVER["HTTP_REFERER"] . "\" />\n");
    print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
    print("<p align=center><table border=1 cellspacing=1>\n");
    print("<tr><td align=center>\n");
    textbbcode("compose", "text", safechar(unesc($arr["text"])));
    print("<tr><td align=center colspan=2><input type=submit value='" . Okay . "' class=btn></td></tr>\n");
    end_table();
    stdfoot();
    die;
} elseif ($action == "delete") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $sure = $_GET["sure"];

    if (!$sure) {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href=?action=delete&cid=$commentid&sure=1" .
            ($referer ? "&returnto=" . urlencode($referer) : "") . ">here</a> if you are sure.");
    }

    $res = sql_query("SELECT torrent FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if ($arr)
        $torrentid = $arr["torrent"];

    sql_query("DELETE FROM comments WHERE id=$commentid") or sqlerr(__FILE__, __LINE__);
    if ($torrentid && mysql_affected_rows() > 0)
        sql_query("UPDATE torrents SET comments = comments - 1 WHERE id = $torrentid");
    // ===Remove karma
    sql_query("UPDATE users SET seedbonus = seedbonus-3.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
    // ===end
    $returnto = $_GET["returnto"];

    if ($returnto)
        header("Location: $returnto");
    else
        header("Location: $BASEURL/"); // change later ----------------------
    die;
} elseif ($action == "vieworiginal") {
    if (get_user_class() < UC_MODERATOR)
        stderr("Error", "Permission denied.");

    $commentid = 0 + $_GET["cid"];

    if (!is_valid_id($commentid))
        stderr("Error", "Invalid ID.");

    $res = sql_query("SELECT c.*, t.name FROM comments AS c LEFT JOIN torrents AS t ON c.torrent = t.id WHERE c.id=$commentid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_array($res);
    if (!$arr)
        stderr("Error", "Invalid ID $commentid.");

    stdhead("Original comment");
    print("<h1>Original contents of comment #$commentid</h1><p>\n");
    print("<table width=500 border=1 cellspacing=0 cellpadding=5>");
    print("<tr><td class=comment>\n");
    echo safechar($arr["ori_text"]);
    print("</td></tr></table>\n");

    $returnto = $_SERVER["HTTP_REFERER"];
    // $returnto = "details.php?id=$torrentid&amp;viewcomm=$commentid#$commentid";
    if ($returnto)
        print("<p><font size=small>(<a href=$returnto>back</a>)</font></p>\n");

    stdfoot();
    die;
} else
    stderr("Error", "Unknown action");

die;

?>
