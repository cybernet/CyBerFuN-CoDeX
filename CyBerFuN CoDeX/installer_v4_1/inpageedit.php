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
function puke($text = "You have forgotten here someting?")
{
    stderr("Error", $text);
}

if (get_user_class() < UC_MODERATOR)
    puke("Access Denied");

$action = (isset($_POST["action"]) && $_POST["action"] == "edituser"?$_POST["action"]:'');

if ($action == "edituser") {
    $userid = $_POST["userid"];
    $tid = $_POST["torrent"];
    $modcomm = safeChar($_POST["modcomm"]);
    $percwarn = $_POST["warns"];
    $whywarned = $_POST["whywarn"];
    $class = 0 + $_POST["class"];
    if (!is_valid_id($userid) || !is_valid_user_class($class))
        stderr("Error", "cant see which member this should be.");
    // check target user class
    $res = sql_query("SELECT immun, warns, dlremoveuntil, whywarned, enabled, username, class, modcomment FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or puke("MySQL: " . mysql_error());
    $editedusername = $arr["username"];
    $warncomment = $arr["whywarned"];
    $curdownloadpos = $arr["downloadpos"];
    $nowdlremoved = $arr["dlremoveuntil"];
    $curpercwarn = $arr["warns"];
    if ($_POST["warns"] == $arr["warns"])
        $downloadpos = $_POST["downloadpos"];

    if (get_user_class() == UC_SYSOP)
        $modcomment = $_POST["modcomment"];
    else
        $modcomment = $arr["modcomment"];

    if ($percwarn != $curpercwarn) {
        if (!isset($_POST["whywarn"]) || empty($_POST["whywarn"]))
            puke("You have to enter a reason for warn adjustment!");

        if ($percwarn > $curpercwarn) {
            if ($percwarn == "30")
                $dlremovetime = 3;
            elseif ($percwarn == "60")
                $dlremovetime = 6;
            elseif ($percwarn == "90")
                $dlremovetime = 9;
            if ($percwarn == "30" || $percwarn == "60" || $percwarn == "90") {
                if ($nowdlremoved != "0000-00-00 00:00:00")
                    $dlremoveuntil = get_date_time(strtotime($nowdlremoved) + $dlremovetime * 86400);
                else
                    $dlremoveuntil = get_date_time(gmtime() + $dlremovetime * 86400);
            } else
                $dlremoveuntil = $nowdlremoved;

            if ($dlremoveuntil != "0000-00-00 00:00:00")
                $downloadpos = "no";
            else
                $downloadpos = "yes";

            $newpercwarn = ($curpercwarn + 10);
            $subject = sqlesc("Warnlevel higher");
            $warncomm = "" . date("d.m.Y") . " - Warn level set to " . $newpercwarn . " % by " . $CURUSER['username'] . " Reason: " . $_POST["whywarn"] . " \n " . $warncomment . "";
            $msg = sqlesc("Warnlevel set to " . $newpercwarn . " % because: " . $_POST["whywarn"] . "\n " . ($percwarn == 30 || $percwarn == 60 || $percwarn == 90?"Also your Download rights are disabled until " . date("d.m.Y - H:i:s", strtotime($dlremoveuntil)) . " ":"") . "\n\nYou dont know what H&R is?\nTake a look to the FAQ!");
            $added = sqlesc(get_date_time());
            $lastwarned = date("d.m.Y");
            sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
            $updateset[] = "lastwarned = '$lastwarned'";
            $updateset[] = "whywarned = " . sqlesc($warncomm);
            $updateset[] = "dlremoveuntil = " . sqlesc($dlremoveuntil);
            $updateset[] = "warns = " . sqlesc($newpercwarn);
            $updateset[] = "downloadpos = " . sqlesc($downloadpos);
            //write_log("User $editedusername warn level adjusted to " . $percwarn . " % by $CURUSER[username]\n", "99B200", "user");
        } elseif ($percwarn < $curpercwarn) {
            $downloadpos = "yes";
            $newpercwarn = ($curpercwarn - 10);
            $subject = sqlesc("Warnlevel lower");
            $warncomm = "" . date("d.m.Y") . " - Warnlevel set to " . $newpercwarn . " % by " . $CURUSER['username'] . " Reason: " . $_POST["whywarn"] . " \n " . $warncomment . "";
            $msg = sqlesc("Warnlevel set to " . $newpercwarn . " % and your DL rights are enabled.");
            $added = sqlesc(get_date_time());

            sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
            $updateset[] = "warns = " . sqlesc($newpercwarn);
            $updateset[] = "whywarned = " . sqlesc($warncomm);
            $updateset[] = "dlremoveuntil = '0000-00-00 00:00:00'";
            $updateset[] = "downloadpos = " . sqlesc($downloadpos);
            //write_log("User $editedusername warn level adjusted to " . $percwarn . " % by $CURUSER[username]\n", "99B200", "user");
        }
    }

    sql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("UPDATE snatched SET sl_warned = 'yes' WHERE torrent = $tid AND userid = '$userid'") or sqlerr(__FILE__, __LINE__);

    $returnto = htmlentities($_POST["returnto"]);
    header("Refresh: 0; $DEFAULTBASEURL/$returnto");
    die;
}
puke("Error,redirect dont work, please use <a href=" . $BASEURL . "/" . $returnto . ">this Link</a>");

?>