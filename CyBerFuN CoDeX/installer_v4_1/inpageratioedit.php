<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
dbconn(false);
header("Content-Type: text/html; charset=".$language['charset']);
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
    puke("Access denied");

$action = (isset($_POST["action"]) && $_POST["action"] == "edituser"?$_POST["action"]:'');

if ($action == "edituser") {
    $userid = $_POST["userid"];
    $class = 0 + $_POST["class"];
    if (!is_valid_id($userid) || !is_valid_user_class($class))
        stderr("Error", "Member Identification failed.");
    // check target user class
    $res = sql_query("SELECT immun, warns, username, class, modcomment FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or puke("Error MySQL: " . mysql_error());
    $editedusername = $arr["username"];

    $enabled = $_POST["enabled"];
    $disresaon = $_POST["disreason"];
    $bookmcom = $_POST["bookmcomment"];
    if ($_POST["addbookmark"] == "frist")
        $addbookm = "ratio";
    else
        $addbookm = $_POST["addbookmark"];
    $subject = sqlesc("Bad Ratio!");
    if ($_POST["addbookmark"] == "frist")
        $msg = sqlesc("Message with extended time");
    else
        $msg = ($addbookm == "ratio"?sqlesc("Staff bookmark added"):sqlesc("Staff bookmark removed"));
    $added = sqlesc(get_date_time());
    if ($enabled == "no") {
        $updateset[] = "enabled = " . sqlesc($enabled);
        $updateset[] = "addbookmark = 'no'";
        //write_log("Mitglied $editedusername wurde wg Bad Ratio deaktiviert von $CURUSER[username]\n","99B200","admin");
    } else {
        sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
        $updateset[] = "addbookmark = " . sqlesc($addbookm);
        $updateset[] = "bookmcomment = " . sqlesc($bookmcom);
        //write_log("Mitglied $editedusername wurde wg Bad Ratio gebookmarkt von $CURUSER[username]\n","99B200","user");
    }

    sql_query("UPDATE users SET " . implode(", ", $updateset) . " WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    // write_log("Das Profil von <a target=_blank href=userdetails.php?id=$userid>$editusername</a> wurde editiert von $CURUSER[username]","","admin");
    $returnto = htmlentities($_POST["returnto"]);
    header("Refresh: 0; $BASEURL/$returnto");
    die;
}
puke("Error,redirect dont worked,please use <a href=" . $BASEURL . "/" . $returnto . ">this Link</a>");

?>