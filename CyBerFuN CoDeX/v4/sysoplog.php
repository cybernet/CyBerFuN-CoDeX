<?php
require ("include/bittorrent.php");
// require ("include/user_functions.php");
require ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
// delete items older than 4 weeks
$secs = 240 * 240 * 240;
if (get_user_class() < UC_SYSOP)
    stderr("Error", "Only SYSOP + can view the logs");
stdhead("Sysop log");
mysql_query("DELETE FROM infolog WHERE " . gmtime() . " - UNIX_TIMESTAMP(added) > $secs") or sqlerr(__FILE__, __LINE__);
mysql_query("REPAIR TABLE infolog");
mysql_query("OPTIMIZE TABLE infolog");
$res = mysql_query("SELECT COUNT(*) FROM infolog");
$row = mysql_fetch_array($res);
$count = $row[0];
/*
$res = mysql_query("SELECT COUNT(*) FROM infolog");
$row = mysql_fetch_array($res);
$count = $row[0];
*/
$perpage = 30;

list($pagertop, $pagerbottom, $limit) = pager(20, $count, "sysoplog.php?");

$res = mysql_query("SELECT added, txt FROM infolog ORDER BY added DESC $limit") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)

    print("<b>Log is empty</b>\n");
else {
    echo $pagertop;

    print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=tabletitle align=left>Date</td><td class=tabletitle align=left>Time</td><td class=tabletitle align=left>Event</td></tr>\n");
    while ($arr = mysql_fetch_assoc($res)) {
        $color = '#ececec';
        if (strpos($arr['txt'], 'was created')) $color = "#ececec";
        if (strpos($arr['txt'], 'was deleted by')) $color = "#ececec";
        if (strpos($arr['txt'], 'was updated by')) $color = "#ececec";
        if (strpos($arr['txt'], 'was edited by')) $color = "#ececec";
        $date = substr($arr['added'], 0, strpos($arr['added'], " "));
        $time = substr($arr['added'], strpos($arr['added'], " ") + 1);

        print("<tr class=tableb><td style=background-color:$color><font color=black>$date</td><td style=background-color:$color><font color=black>$time</td><td style=background-color:$color align=left><font color=black>" . $arr['txt'] . "</font></font></font></td></tr>\n");
    }
    print("</table>");
}
echo $pagerbottom;

print("<p>Times are in GMT.</p>\n");
stdfoot();

?>