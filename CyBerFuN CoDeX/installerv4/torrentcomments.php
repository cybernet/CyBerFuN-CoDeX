<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR)
    stderr("Error", "Access denied.");

stdhead();

$limit = 150;

if ($_GET["amount"]) {
    if (intval($_GET["amount"]) != $_GET["amount"]) {
        stderr("Error", "Amount wasn't an integer.");
    }

    $limit = 0 + $_GET["amount"];

    if ($limit > 999)
        $limit = 1000;

    if ($limit < 10)
        $limit = 10;
}

print("<p align=\"left\">Showing " . $limit . " latest comments.</p>\n");

$subres = mysql_query("SELECT comments.id, torrent, text, user, comments.added , editedby, editedat, avatar, warned, " . "username, title, class FROM comments LEFT JOIN users ON comments.user = users.id " . " ORDER BY comments.id DESC limit 0," . $limit) or sqlerr(__FILE__, __LINE__);
$allrows = array();
while ($subrow = mysql_fetch_array($subres))
$allrows[] = $subrow;

commenttable_new($allrows);
stdfoot();

function commenttable_new($rows)
{
    global $CURUSER, $HTTP_SERVER_VARS;
    begin_main_frame();
    begin_frame();
    $count = 0;
    foreach ($rows as $row) {
        $subres = mysql_query("SELECT name from torrents where id=" . unsafeChar($row["torrent"])) or sqlerr(__FILE__, __LINE__);
        $subrow = mysql_fetch_array($subres);
        print("<br /><a href=\"details.php?id=" . safeChar($row["torrent"]) . "\">" . safeChar($subrow["name"]) . "</a><br />\n");
        print("<p class=sub>#" . $row["id"] . " by ");
        if (isset($row["username"])) {
            print("<a name=comm" . $row["id"] . " href=userdetails.php?id=" . safeChar($row["user"]) . "><b>" . safechar($row["username"]) . "</b></a>" . ($row["warned"] == "yes" ? "<img src=" . "pic/warned.gif alt=\"Warned\">" : ""));
        } else {
            print("<a name=\"comm" . safeChar($row["id"]) . "\"><i>(orphaned)</i></a>\n");
        }
        print(" at " . safeChar($row["added"]) . " GMT" . "- [<a href=comment.php?action=edit&cid=$row[id]>Edit</a>]" . "- [<a href=deletecomment.php?id=$row[id]>Delete</a>]</p>\n");
        $avatar = ($CURUSER["avatars"] == "yes" ? safechar($row["avatar"]) : "");
        if (!$avatar) {
            $avatar = "pic/default_avatar.gif";
        }

        begin_table(true);
        print("<tr valign=top>\n");
        print("<td align=center width=150 style='padding: 0px'><img width=150 src=$avatar></td>\n");
        print("<td class=text>" . format_comment($row["text"]) . "</td>\n");
        print("</tr>\n");
        end_table();
    }
    end_frame();
    end_main_frame();
}

?>