<?php
require ("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Failed Logins - Nosey Cunt !");

$action = (isset($_GET['action']) ? $_GET['action'] : 'showlist');
$id = (isset($_GET['id']) ? $_GET['id'] : '');
function check ($id)
{
    if (!is_valid_id($id))
        return stderr("Error", "Invalid ID");
    else
        return true;
}
function safe_query ($query, $id)
{
    $query = sprintf("$query WHERE id ='%s'",
        mysql_real_escape_string($id));
    $result = sql_query($query);
    if (!$result)
        return sqlerr(__FILE__, __LINE__);
    else
        redirect('maxlogin.php');
}
function redirect($url)
{
    if (!headers_sent())
        header("Location : $url");
    else
        echo "<script language=\"JavaScript\">window.location.href = '$url';</script>";
    exit;
}
if ($action == 'showlist') {
    stdhead ("Max. Login Attemps - Show List");
    echo("<table border=1 cellspacing=0 cellpadding=5 width=737>\n");
    $res = sql_query("SELECT * FROM  loginattempts ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 0)
        echo("<tr><td colspan=2><b>Nothing found</b></td></tr>\n");
    else {
        echo("<tr><td class=colhead>ID</td><td class=colhead align=left>Ip Address</td><td class=colhead align=left>Action Time</td>" . "<td class=colhead align=left>Attempts</td><td class=colhead align=left>Status</td></tr>\n");

        while ($arr = mysql_fetch_assoc($res)) {
            $r2 = sql_query("SELECT id,username FROM users WHERE ip=" . sqlesc($arr[ip])) or sqlerr(__FILE__, __LINE__);
            $a2 = mysql_fetch_assoc($r2);
            echo("<tr><td align=>$arr[id]</td><td align=left>$arr[ip] " . ($a2[id] ? "<a href=userdetails.php?id=$a2[id]>" : "") . " " . ($a2[username] ? "($a2[username])</a>" : "") . "</td><td align=left>$arr[added]</td><td align=left>$arr[attempts]</td><td align=left>" . ($arr[banned] == "yes" ? "<font color=red><b>banned</b></font> <a href=maxlogin.php?action=unban&id=$arr[id]><font color=green>[<b>unban</b>]</font></a>" : "<font color=green><b>not banned</b></font> <a href=maxlogin.php?action=ban&id=$arr[id]><font color=red>[<b>ban</b>]</font></a>") . "  <a OnClick=\"return confirm('Are you wish to delete this attempt?');\" href=maxlogin.php?action=delete&id=$arr[id]>[<b>delete</b>]</a></td></tr>\n");
        }
    }
    echo("</table>\n");
} elseif ($action == 'ban') {
    check($id);
    stdhead ("Max. Login Attemps - BAN");
    safe_query("UPDATE loginattempts SET banned = 'yes'", $id);
} elseif ($action == 'unban') {
    check($id);
    stdhead ("Max. Login Attemps - UNBAN");
    safe_query("UPDATE loginattempts SET banned = 'no'", $id);
} elseif ($action == 'delete') {
    check($id);
    stdhead ("Max. Login Attemps - DELETE");
    safe_query("DELETE FROM loginattempts", $id);
} else
    stderr("Error", "Invalid Action");

stdfoot();

?>