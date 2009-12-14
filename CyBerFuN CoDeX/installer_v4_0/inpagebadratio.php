<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
header('Content-type: text/html; charset=ISO-8859-1');
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
function bark($msg)
{
    stdhead("Error");
    stdmsg("Error", $msg);
    stdfoot();
    exit;
}

$id = 0 + $_GET["id"];
if (!is_valid_id($id))
    bark("Bad ID $id.");

$r = @sql_query("SELECT * FROM users WHERE id=$id") or sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_array($r) or bark("No User with this ID.");
if ($user["status"] == "pending") die;

if (get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class()) {
    echo("<form method=\"post\" action=\"inpageratioedit.php\">\n");
    echo("<input type=\"hidden\" name=\"action\" value=\"edituser\">\n");
    echo("<input type=\"hidden\" name=\"userid\" value=\"$id\">\n");
    echo("<input type=\"hidden\" name=\"class\" value=\"$user[class]\">\n");
    echo("<input type=\"hidden\" name=\"returnto\" value=\"badratio.php?done=no\">\n");
    echo("<br /><table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");

    echo("<tr><td class=colhead colspan=3 align=center>Quick-Edit <a target=_blank href=userdetails.php?id=" . $user["id"] . ">" . $user["username"] . "</a></td></tr>");

    ?>

<?php
    if ($user["immun"] == "no") {
        $modcomment = safeChar($user["modcomment"]);
        if ($user["downloaded"] > 0) {
            $uratio = $user["uploaded"] / $user["downloaded"];
            $uratio = number_format($uratio, 3);
        }
        $timeto = get_date_time(gmtime() + 14 * 86400);
        $frist = get_date_time(gmtime() + 8 * 86400);
        $bookmcomment = "" . safeChar($user["bookmcomment"]) . "";
        $enabled = $user["enabled"] == 'yes';
        echo("<form action=\"\" target=bookmcomment name=bookmcomment><tr><td class=rowhead>Add to Bookmarks?</td><td colspan=2 class=tablea align=left><input type=radio name=addbookmark value=yes" . ($user["addbookmark"] == "yes" ? " checked" : "") . ">Yes - One to watch<input type=radio onClick=\"fuellen(this.form,'text1','Bad Ratio (" . $uratio . ") Time until " . date("d.m.Y", strtotime($timeto)) . "')\" name=addbookmark value=ratio" . ($user["addbookmark"] == "ratio" ? " checked" : "") . ">Yes - Bad Ratio <input type=radio onClick=\"fuellen(this.form,'text1','" . $bookmcomment . " / Time until because Ratio ($uratio) extended to " . date("d.m.Y", strtotime($frist)) . " ')\" name=addbookmark value=frist>Extend time until <input type=radio name=addbookmark onClick=\"fuellen(this.form,'text1','')\" value=no" . ($user["addbookmark"] == "no" ? " checked" : "") . ">No</td></tr>\n");
        echo("<tr><td class=rowhead>Bookmark Reason:</td><td class=tablea colspan=2 align=left><textarea cols=90 rows=6 name=bookmcomment>$bookmcomment</textarea></td></tr>\n");
        echo("<tr><td class=rowhead>Teamcomment:</td><td colspan=2><textarea cols=90 rows=4 readonly>" . $modcomment . "</textarea></td></tr>");
        echo("<tr><td class=rowhead>Warnstatus</td><td align=left colspan=2>" . $user["warns"] . "%</td></tr>\n");
        echo("<tr><td class=\"rowhead\" rowspan=\"2\">Enabled</td><td colspan=\"2\" align=\"left\"><input name=\"enabled\" onClick=\"fuellen2(this.form,'text1','')\" value=\"yes\" type=\"radio\"" . ($enabled ? " checked" : "") . ">Yes <input name=\"enabled\" onClick=\"fuellen2(this.form,'text1','Bad Ratio (" . $uratio . ") ')\" value=\"no\" type=\"radio\"" . (!$enabled ? " checked" : "") . ">No</td></tr>\n");
        echo("<tr><td colspan=\"2\" align=\"left\">Disable Reason:&nbsp;<input type=\"text\" name=\"disreason\" size=\"60\" /></td></tr>");

        echo("<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" class=\"btn\" value=\"OK\"></td></tr>\n");

        echo("</table>\n");
        echo("</form>\n");

        echo("<br><table><tr><td class=colhead colspan=2 align=center>Depending on the action the member will receive either:</td></tr>");
        echo("<tr><td>Bad ratio warning</td>");
        echo("<td>Bad ratio warning period extended</td></tr>");
        echo("</table>");
    } else {
        if ($user["immun"] == "yes")
            $whynot = "This Member is immune";
        elseif ($user["addbookmark"] == "ratio")
            $whynot = "Already bookmarked";

        echo("<tr><td colspan=\"3\" align=\"center\">" . $whynot . "</td></tr></table>");
    }
}
