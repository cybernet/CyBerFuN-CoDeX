<?php
require_once("include/bittorrent.php");
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
$fileid = (int)$_GET['fileid'];

$res = sql_query("SELECT * FROM attachmentdownloads WHERE fileid=" . unsafeChar($fileid)) or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == "0")
    die("Nothing found!");
else {
    stdhead();
    print("<html><head><link rel=\"stylesheet\" href=\"/themes/default/default.css\" type=\"text/css\" media=\"screen\" /></head><body>\n");
    print("<table border=1 width=100% cellspacing=0 cellpadding=2>\n");
    print("<tr align=center><td class=colhead align=center>File ID</td>
 <td class=colhead align=center>Filename</td>
 <td class=colhead align=center>Downloaded from</td>
 <td class=colhead align=center>Downloads</td>
 <td class=colhead align=center>Date</td></tr>\n");
    while ($arr = mysql_fetch_assoc($res)) {
        print("<tr><td align=center>$arr[fileid]</td><td align=center>" . safeChar($arr[filename]) . "</td><td align=center><a href=\"#\" onclick=\"opener.location=('userdetails.php?id=$arr[userid]'); self.close();\">$arr[username]</a></td><td align=center>$arr[downloads]</td><td align=center>$arr[date]</td></tr>");
    }
    $res = sql_query("SELECT downloads FROM attachments WHERE id=" . unsafeChar($fileid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    print("<tr><td colspan=5><div class=error><font color=blue>Total Downloads: $arr[downloads]</font></div></td</tr>");
    print("</table></body></html>\n");
}
stdfoot();

?>
