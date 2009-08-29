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
if (get_user_class() < UC_MODERATOR) stderr("Error", "Permission denied");

if ($_SERVER["REQUEST_METHOD"] == "POST")
    $ip = $_POST["ip"];
else
    $ip = $_GET["ip"];
if ($ip) {
    $nip = ip2long($ip);
    if ($nip == -1)
        stderr("Error", "Bad IP.");
    $res = mysql_query("SELECT * FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 0)
        stderr("Result", "The IP address <b>$ip</b> is not banned.");
    else {
        $banstable = "<table class=main border=0 cellspacing=0 cellpadding=5>\n" . "<tr><td class=colhead>First</td><td class=colhead>Last</td><td class=colhead>Comment</td></tr>\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $first = long2ip($arr["first"]);
            $last = long2ip($arr["last"]);
            $comment = safchars($arr["comment"]);
            $banstable .= "<tr><td>$first</td><td>$last</td><td>$comment</td></tr>\n";
        }
        $banstable .= "</table>\n";
        stderr("Result", "<table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded style='padding-right: 5px'><img src=\"{$pic_base_url}smilies/excl.gif\"></td><td class=embedded>The IP address <b>$ip</b> is banned:</td></tr></table><p>$banstable</p>");
    }
}
stdhead();

?>
<h1>Test IP address</h1>
<form method=post action=testip.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead>IP address</td><td><input type=text name=ip></td></tr>
<tr><td colspan=2 align=center><input type=submit class=btn value='OK'></td></tr>
</form>
</table>

<?php
stdfoot();

?>