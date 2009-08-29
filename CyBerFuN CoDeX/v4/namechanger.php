<?php
require ("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
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
    hacker_dork("Name Changer - Nosey Cunt !");

$act = unesc($_GET['act']);
if (isset($act) && $act == 'change') {
    $uid = $uid = (int)$_POST["uid"];
    $uname = sqlesc($_POST["uname"]);

    if ($_POST["uname"] == "" || $_POST["uid"] == "")
        stderr("Error", "UserName or ID missing");

    $change = sql_query("UPDATE users SET username=$uname WHERE id=$uid") or sqlerr(__FILE__, __LINE__);
    // mysql_affected_rows($change);
    $added = sqlesc(get_date_time());
    $changed = sqlesc("Your Username Has Been Changed To $uname");

    if (!$change) {
        if (mysql_errno() == 1062)
            bark("Username already exists!");
        bark("borked");
    }

    sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $uid, $changed, $added)") or sqlerr(__FILE__, __LINE__);
    // header('Location: '.$BASEURL.'/userdetails.php?id='.$uid);
}

stdhead("UserName Changer");

?>
<h1>Change UserName</h1>
<form method=post action="namechanger.php?act=change">
<table border=1 cellspacing=0 cellpadding=3>
<tr><td class=rowhead>ID: </td><td><input type=text name=uid size=10></td></tr>
<tr><td class=rowhead>New Username: </td><td><input type=uploaded name=uname size=20></td></tr>
<tr><td colspan=2 align=center>If You Are Sure Then: <input type=submit value="Change Name!" class=btn></td></tr>
</table>
</form>
<?php

stdfoot();

?>
