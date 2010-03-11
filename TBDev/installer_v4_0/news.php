<?php
require ("include/bittorrent.php");
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
parked();
// ////////Secured updated news//special thanxs to snuggs and pdq///////
if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Error", "Permission denied.");
$action = isset($_GET["action"]) ?$_GET["action"] : '';
// //////////////////Delete news//////////////////////////////////////////////////////
if ($action == 'delete') {
    $newsid = (int)$_GET['newsid'];
    isset($_GET['sure']) && $sure = safeChar($_GET['sure']);
    if (!is_valid_id($newsid))
        stderr("Error", "Invalid ID.");
    $hash = md5('the salt to' . $newsid . 'add' . 'mu55y');
    if (!$sure)
        stderr("Confirm Delete", "Do you really want to delete this news entry? Click\n" . "<a href=?newsid=$newsid&action=delete&sure=1&h=$hash>here</a> if you are sure.", false);
    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');
    function deletenewsid($newsid)
    {
        global $CURUSER;
        mysql_query("DELETE FROM news WHERE id = $newsid AND userid = $CURUSER[id]");
        @unlink("cache/news.html");
        @unlink("cache/newsstaff.html");
    }
    deletenewsid($newsid);
    stdhead();
    echo '<h2>News entry deleted!</h2>';
    stdfoot();
    die;
}
// ////////////////Add news////////////////////////////////////////////////////////
if ($action == 'add') {
    $body = $_POST["body"];
    $sticky = $_POST["sticky"];
    if (!$body)
        stderr("Error", "The news item cannot be empty!");
    $title = htmlentities($_POST['title']);
    if (!$title)
        stderr("Error", "The news title cannot be empty!");
    $added = isset($_POST["added"]) ?$_POST["added"] : '';
    if (!$added)
        $added = get_date_time();
    mysql_query("INSERT INTO news (userid, added, body, title, sticky) VALUES (" . $CURUSER['id'] . "," . sqlesc($added) . ", " . sqlesc($body) . ", " . sqlesc($title) . ", " . sqlesc($sticky) . ")") or sqlerr(__FILE__, __LINE__);
    mysql_affected_rows() == 1 ?$warning = "News entry was added successfully." : stderr("oopss", "Something's wrong !! .");
    @unlink("cache/news.html");
    @unlink("cache/newsstaff.html");
}
// ///////////////Edit/change news////////////////////////////////////////////////////////
if ($action == 'edit') {
    $newsid = (int)$_GET["newsid"];
    if (!is_valid_id($newsid))
        stderr("Error", "Invalid news item ID.");
    $res = mysql_query("SELECT * FROM news WHERE id=" . sqlesc($newsid)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) != 1)
        stderr("Error", "No news item with that ID .");
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
        mysql_query("UPDATE news SET body=$body, sticky=$sticky, title=" . sqlesc($title) . " WHERE id=$newsid") or sqlerr(__FILE__, __LINE__);
        @unlink("cache/news.html");
        @unlink("cache/newsstaff.html");
        $returnto = htmlentities($_POST['returnto']);
        if ($returnto != "")
            header("Location: $returnto");
        else
            $warning = "News item was edited successfully.";
    } else {
        $returnto = htmlentities($_GET['returnto']);
        stdhead("Site News");
        echo("<h1>Edit News Item</h1>\n");
        echo("<form method=post action=?action=edit&newsid=$newsid>\n");
        echo("<table border=1 cellspacing=0 cellpadding=5>\n");
        echo("<input type=hidden name=returnto value=$returnto>\n");
        echo("<tr><td><input type=text name=title value=\"" . safeChar($arr['title']) . "\"></td></tr>\n");
        echo("<tr><td align=left style='padding: 0px'>");
        echo("<tr><td style='padding: 0px'><textarea name=body cols=145 rows=5 style='border: 0px'>" . safeChar($arr["body"]) . "</textarea></td></tr>\n");
        echo("<tr><td class=\"rowhead\">Sticky</td><td style='padding: 10px'><input type=radio " . ($arr["sticky"] == "yes" ? " checked" : "") . " name=sticky value=yes>Yes<input name=sticky type=radio value=no " . ($arr["sticky"] == "no" ? " checked" : "") . " >No</td></tr>\n");
        echo("<tr><td colspan=\"2\" align=center><input type=submit value='Okay' class=btn></td></tr>\n");
        echo("</table>\n");
        echo("</form>\n");
        stdfoot();
        die;
    }
}
// ///////////////last and final Actions////////////////////////////////////////////
stdhead("Site News");
echo("<h1>Submit News Item</h1>");
if ($warning)
    echo("<p><font size=-3>($warning)</font></p>");
echo("<form method=post name=\"compose\" action=?action=add>");
echo("<table border=1 cellspacing=0 cellpadding=5>\n");
echo("<tr><td><input type=text name=title value=\"" . safeChar($arr['title']) . "\"></td></tr>\n");
echo("<tr><td align=left style='padding: 0px'>");
textbbcode("compose", "body", ($quote?(("[quote=" . safeChar($arr["username"]) . "]" . safeChar(unesc($arr["body"])) . "[/quote]")):""));
echo("<tr><td class=\"rowhead\">Sticky</td><td style='padding: 10px'><input type=radio checked name=sticky value=yes>Y<input name=sticky type=radio value=no>N</td></tr>\n");
echo("<tr><td colspan=\"2\" class=\"rowhead\"><input type=submit value='Okay' class=btn></td></tr>\n");
echo("</table></form><br><br>\n");
$res = mysql_query("SELECT * FROM news ORDER BY sticky, added DESC") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) > 0) {
    begin_main_frame();
    begin_frame();
    while ($arr = mysql_fetch_array($res)) {
        $newsid = $arr["id"];
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
        echo(" - [<a href=?action=edit&newsid=$newsid><b>Edit</b></a>]");
        echo(" - [<a href=?action=delete&newsid=$newsid><b>Delete</b></a>]");
        echo("</td></tr></table></p>\n");
        begin_table(true);
        echo("<tr valign=top><td class=comment><b>" . htmlentities($title) . "</b><br>" . format_comment($body) . "</td></tr>\n");
        end_table();
    }
    end_frame();
    end_main_frame();
} else
    stdmsg("Sorry", "No recent news entrys available!");
stdfoot();
die;

?>