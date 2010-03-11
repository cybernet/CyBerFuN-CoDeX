<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
header("Content-Type: text/html; charset=".$language['charset']);

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
$tid = 0 + $_GET["tid"];
$s = @sql_query("SELECT snatched.uploaded, snatched.downloaded, snatched.sl_warned, snatched.seedtime, torrents.name, torrents.size, torrents.id FROM snatched JOIN torrents ON torrents.id = snatched.torrentid WHERE torrents.id=$tid AND snatched.userid=$id") or sqlerr(__FILE__, __LINE__);
$tor = mysql_fetch_array($s) or bark("No Snatch with this ID.");

$shared = number_format($tor["uploaded"] / ($tor["downloaded"] > 0?$tor["downloaded"]:$tor["size"]), 2);

if (!is_valid_id($id))
    bark("Invalid ID");

$r = @sql_query("SELECT * FROM users WHERE id=$id") or sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_array($r) or bark("No User with this ID.");
if ($user["status"] == "pending") die;

if (get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class()) {
    echo("<form method=\"post\" action=\"inpageedit.php\">\n");
    echo("<input type=\"hidden\" name=\"action\" value=\"edituser\">\n");
    echo("<input type=\"hidden\" name=\"userid\" value=\"$id\">\n");
    echo("<input type=\"hidden\" name=\"torrent\" value=\"$tid\">\n");
    echo("<input type=\"hidden\" name=\"returnto\" value=\"snatchleave.php?done=no\">\n");
    echo("<br /><table class=\"main\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
    echo("<tr><td class=colhead colspan=3 align=center>Quick-Edit <a target=_blank href=userdetails.php?id=" . $user["id"] . ">" . $user["username"] . "</a></td></tr>");

    if ($user["immun"] == "yes" && $tor["sl_warned"] == "no") {
        $modcomment = safeChar($user["modcomment"]);
        echo("<tr><td class=rowhead>Modcomment:</td><td colspan=2><textarea cols=90 rows=4 readonly>" . $modcomment . "</textarea></td></tr>");
        echo("<tr><td class=rowhead>Warnstatus</td><td align=left colspan=2>
" . ($user["warns"] > 0?"<input type=radio name=warns value=" . ($user["warns"] - 10) . "%>" . ($user["warns"] - 10) . "%":"") . "
<input type=radio name=warns value=" . $user["warns"] . "><font color=blue>" . $user["warns"] . " (actually Warnstatus)</font>
<input type=radio name=warns checked value=" . ($user["warns"] + 10) . ">" . ($user["warns"] + 10) . "%</td></tr>\n");
        echo("<tr><td class=rowhead>Reason of Warnadjustment:</td><td class=tablea colspan=2 align=left><textarea cols=90 rows=6 name=whywarn>H&R on " . $tor["name"] . " \nFileratio: " . $shared . " \nSeedtime: " . mkprettytime($tor["seedtime"]) . "</textarea></td></tr>\n");
        echo("<tr><td class=rowhead>Earlier Warns:</td><td colspan=2><textarea cols=90 rows=4 readonly>" . $user["whywarned"] . "</textarea></td></tr>");
        $realdlremoved = ($user['dlremoveuntil'] != "0000-00-00 00:00:00"?date("d.m.Y - H:i:s", strtotime($user['dlremoveuntil'])):"Not yet");
        echo("<tr><td class=rowhead>DL disabled until</td><td colspan=2>" . $realdlremoved . "</td></tr>\n");

        echo("<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" class=\"btn\" value=\"OK\"></td></tr>\n");
        echo("</table>\n");
        echo("</form>\n");
    } else {
        if ($user["immun"] == "yes")
            $whynot = "This User is immune";
        elseif ($tor["sl_warned"] == "yes")
            $whynot = "The Member is already warned for this H&R";

        echo("<tr><td colspan=\"3\" align=\"center\">" . $whynot . "</td></tr></table>");
    }
}
