<?php
require_once("include/bittorrent.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
$id = (isset($_GET["id"]) ? 0 + $_GET["id"] : "0");
if ($id == "0")
    stderr("Err", "I dont think so!");

$ur = mysql_query("SELECT username from users WHERE id=$id");
$user = mysql_fetch_array($ur) or stderr("Error", "No user found");

$count = get_row_count("happylog", "WHERE userid=$id ");
$perpage = 30;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, "happylog.php?id=$id&amp;");
$res = mysql_query("SELECT h.userid, h.torrentid, h.date, h.multi , t.name FROM happylog as h LEFT JOIN torrents AS t on t.id=h.torrentid WHERE h.userid=$id ORDER BY h.date DESC $limit ") or sqlerr();

stdhead("Happy hour log for " . $user["username"] . "");
begin_main_frame();
begin_frame("Happy hour log for " . $user["username"] . "");

if (mysql_num_rows($res) > 0) {
    print("$pagertop");
    print("<table class=main border=1 cellspacing=0 cellpadding=5><tr><td class=colhead style=\"width:100%\">Torrent Name</td><td class=colhead>Multiplier</td><td class=colhead nowrap=\"nowrap\">Date started</td></tr>");
    while ($arr = mysql_fetch_assoc($res)) {
        print ("<tr><td><a href=\"details.php?id=" . $arr["torrentid"] . "\">" . $arr["name"] . "</a></td><td>" . $arr["multi"] . "</td><td nowrap=\"nowrap\">" . date("Y-m-d H:i" , $arr["date"]) . "</td></tr>");
    }
    print("</table");
} else {
    print("No torrents downloaded in happy hour! ");
}
end_frame();
end_main_frame();
stdfoot();

?>