<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    echo("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();
// ////////changelog//special thanxs to snuggs and pdq///////
if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Error", "Permission denied.");
$action = isset($_GET["action"]) ?$_GET["action"] : '';
// //////////////////Delete changelog//////////////////////////////////////////////////////
if ($action == 'delete') {
    $changelogid = (int)$_GET['changelogid'];
    // $sure = (int)$_GET['sure'];
    isset($_GET['sure']) && $sure = safeChar($_GET['sure']);
    if (!is_valid_id($changelogid))
        stderr("Error", "Invalid ID.");
    $hash = md5('the salt to' . $changelogid . 'add' . 'mu55y');
    if (!$sure)
        stderr("Confirm Delete", "Do you really want to delete this changelog entry? Click\n" . "<a href=?changelogid=$changelogid&action=delete&sure=1&h=$hash>here</a> if you are sure.", false);
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');
    function deletechangelogid($changelogid)
    {
        global $CURUSER;
        sql_query("DELETE FROM changelog WHERE id = $changelogid AND userid = $CURUSER[id]");
        @unlink("cache/changelog.html");
        @unlink("cache/changelogstaff.html");
    }
    deletechangelogid($changelogid);
    stdhead();
    echo '<h2>changelog entry deleted!</h2>';
    stdfoot();
    die;
}
// ////////////////Add changelog////////////////////////////////////////////////////////
if ($action == 'add') {
    $body = $_POST["body"];
    $sticky = $_POST["sticky"];
    if (!$body)
        stderr("Error", "The changelog item cannot be empty!");
    $title = htmlentities($_POST['title']);
    if (!$title)
        stderr("Error", "The changelog title cannot be empty!");
    $added = isset($_POST["added"]) ?$_POST["added"] : '';
    if (!$added)
        $added = get_date_time();
    sql_query("INSERT INTO changelog (userid, added, body, title, sticky) VALUES (" . $CURUSER['id'] . "," . sqlesc($added) . ", " . sqlesc($body) . ", " . sqlesc($title) . ", " . sqlesc($sticky) . ")") or sqlerr(__FILE__, __LINE__);
    mysql_affected_rows() == 1 ?$warning = "changelog entry was added successfully." : stderr("oopss", "Something's wrong !! .");
    @unlink("cache/changelog.html");
    @unlink("cache/changelogstaff.html");
}
// ///////////////Edit/change changelog////////////////////////////////////////////////////////
if ($action == 'edit') {
    $changelogid = (int)$_GET["changelogid"];
    if (!is_valid_id($changelogid))
        stderr("Error", "Invalid changelog item ID.");
    $res = sql_query("SELECT * FROM changelog WHERE id=" . sqlesc($changelogid)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) != 1)
        stderr("Error", "No changelog item with that ID .");
    $arr = mysql_fetch_array($res);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $body = $_POST['body'];
        $sticky = $_POST['sticky'];
        if ($body == "")
            stderr("Error", "Body cannot be empty!");
        // === just making title safer
        $title = htmlentities($_POST['title']);
        if ($title == "")
            stderr("Error", "Title cannot be empty!");
        $body = sqlesc($body);
        $sticky = sqlesc($sticky);
        $editedat = sqlesc(get_date_time());
        sql_query("UPDATE changelog SET body=$body, sticky=$sticky, title=" . sqlesc($title) . " WHERE id=$changelogid") or sqlerr(__FILE__, __LINE__);
        @unlink("cache/changelog.html");
        @unlink("cache/changelogstaff.html");
        $returnto = htmlentities($_POST['returnto']);
        if ($returnto != "")
            header("Location: $returnto");
        else
            $warning = "changelog item was edited successfully.";
    } else {
        $returnto = htmlentities($_GET['returnto']);
        stdhead("Site changelog");
        echo("<h1>Edit changelog Item</h1>\n");
        echo("<form method=post name=\"compose\" action=?action=edit&changelogid=$changelogid>\n");
        echo("<table border=1 cellspacing=0 cellpadding=5>\n");
        echo("<input type=hidden name=returnto value=$returnto>\n");
        echo("<tr><td><input type=text name=title value=\"" . safeChar($arr['title']) . "\"></td></tr>\n");
        echo("<tr><td align=left style='padding: 0px'>");
        echo("<tr><td style='padding: 0px'><textarea name=body cols=145 rows=5 style='border: 0px'>" . safeChar($arr["body"]) . "</textarea></td></tr>\n");
        echo("<tr><td class=\"rowhead\">Sticky</td><td style='padding: 10px'><input type=radio " . ($arr["sticky"] == "yes" ? " checked" : "") . " name=sticky value=yes>Yes<input name=sticky type=radio value=no " . ($arr["sticky"] == "no" ? " checked" : "") . " >No</td></tr>\n");
        echo("<tr><td colspan=\"2\" align=center><input type='button' class='btn' value='Spell Check' onclick='openspell();'><input type=submit value='Okay' class=btn></td></tr>\n");
        echo("</table>\n");
        echo("</form>\n");
        stdfoot();
        die;
    }
}
// ///////////////last and final Actions////////////////////////////////////////////
stdhead("Site changelog");
echo("<h1>Submit changelog Item</h1>");
if ($warning)
    echo("<p><font size=-3>($warning)</font></p>");
echo("<form method=post name=\"compose\" action=?action=add>");
echo("<table border=1 cellspacing=0 cellpadding=5>\n");
echo("<tr><td><input type=text name=title value=\"" . safeChar($arr['title']) . "\"></td></tr>\n");
echo("<tr><td align=left style='padding: 0px'>");
textbbcode("compose", "body", ($quote?(("[quote=" . safeChar($arr["username"]) . "]" . safeChar(unesc($arr["body"])) . "[/quote]")):""));
echo("<tr><td class=\"rowhead\">Sticky</td><td style='padding: 10px'><input type=radio checked name=sticky value=yes>Y<input name=sticky type=radio value=no>N</td></tr>\n");
echo("<tr><td colspan=\"2\" class=\"rowhead\"><input type='button' class='btn' value='Spell Check' onclick='openspell();'><input type=submit value='Okay' class=btn></td></tr>\n");
echo("</table></form><br><br>\n");
$res = sql_query("SELECT * FROM changelog ORDER BY sticky, added DESC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0) {
    begin_main_frame();
    begin_frame();
    while ($arr = mysql_fetch_array($res)) {
        $changelogid = $arr["id"];
        $body = $arr["body"];
        $title = $arr["title"];
        $userid = $arr["userid"];
        $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . " ago)";
        $res2 = sql_query("SELECT username, donor FROM users WHERE id =$userid") or sqlerr(__FILE__, __LINE__);
        $arr2 = mysql_fetch_array($res2);
        $postername = $arr2["username"];
        if ($postername == "")
            $by = "unknown[$userid]";
        else
            $by = "<a href=userdetails.php?id=$userid><b>$postername</b></a>" .
            ($arr2["donor"] == "yes" ? "<img src=pic/star.gif alt='Donor'>" : "");
        echo("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");
        echo("$added&nbsp;---&nbsp;by&nbsp$by");
        echo(" - [<a href=?action=edit&changelogid=$changelogid><b>Edit</b></a>]");
        echo(" - [<a href=?action=delete&changelogid=$changelogid><b>Delete</b></a>]");
        echo("</td></tr></table></p>\n");
        begin_table(true);
        echo("<tr valign=top><td class=comment><b>" . htmlentities($title) . "</b><br>" . format_comment($body) . "</td></tr>\n");
        end_table();
    }
    end_frame();
    end_main_frame();
} else
    stdmsg("Sorry", "No recent changelog entrys available!");
stdfoot();
die;

?>