<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
function bark($msg)
{
    stdhead();
    stdmsg("Update Has Failed !", $msg);
    stdfoot();
    exit;
}
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (isset($_POST["nowarned"]) && ($_POST["nowarned"] == "nowarned")) {
    if (get_user_class() < UC_MODERATOR)
        stderr("Sorry", "Access denied."); {
        if (empty($_POST["usernw"]) && empty($_POST["desact"]) && empty($_POST["delete"]))
            bark("You Must Select A User To Edit.");

        if (!empty($_POST["usernw"])) {
            $msg = sqlesc("Your Warning Has Been Removed By: " . $CURUSER['username'] . ".");
            $added = sqlesc(get_date_time());
            $userid = implode(", ", $_POST[usernw]);
            // mysql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES (0, $userid, $msg, $added)") or sqlerr(__FILE__, __LINE__);
            $r = mysql_query("SELECT modcomment FROM users WHERE id IN (" . implode(", ", $_POST[usernw]) . ")")or sqlerr(__FILE__, __LINE__);
            $user = mysql_fetch_array($r);
            $exmodcomment = $user["modcomment"];
            $modcomment = gmdate("Y-m-d") . " - Warning Removed By " . $CURUSER['username'] . ".\n" . $modcomment . $exmodcomment;
            mysql_query("UPDATE users SET modcomment=" . sqlesc($modcomment) . " WHERE id IN (" . implode(", ", $_POST[usernw]) . ")") or sqlerr(__FILE__, __LINE__);

            $do = "UPDATE users SET warned='no', warneduntil='0000-00-00 00:00:00' WHERE id IN (" . implode(", ", $_POST[usernw]) . ")";
            $res = mysql_query($do);
        }

        if (!empty($_POST["desact"])) {
            $do = "UPDATE users SET enabled='no' WHERE id IN (" . implode(", ", $_POST['desact']) . ")";
            $res = mysql_query($do);
        }
    }
}
header("Refresh: 0; url=warned.php");

?>