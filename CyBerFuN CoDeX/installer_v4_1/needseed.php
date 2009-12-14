<?php
ob_start("ob_gzhandler");
require ("include/bittorrent.php");
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

$needed = $_GET['needed'];

if ($needed == "leechers") {
    stdhead("Seeders in need");

    begin_main_frame();
    begin_frame("Seeders in need &nbsp;-&nbsp; [<a href=\"?needed=seeders\" class=\"altlink\">Torrents Needing Seeds</a>]");

    $maxdt = sqlesc(get_date_time(gmtime() - 86400 * 7)); // 7 days
    $res = sql_query("SELECT peers.userid, users.username, peers.torrent, users.uploaded / users.downloaded AS uratio, peers.ip FROM peers JOIN users ON peers.userid = users.id WHERE peers.seeder = 'yes' AND users.downloaded > '1024' AND users.added < $maxdt ORDER BY users.uploaded / users.downloaded ASC LIMIT 20");
    print("<table class=main border=1 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=\"colhead\">User</td><td class=\"colhead\">Torrent</td><td class=\"colhead\">Peers</a></td></tr>\n");
    while ($arr = mysql_fetch_assoc($res)) {
        $torr = mysql_query("SELECT name, leechers, seeders FROM torrents WHERE id = " . $arr['torrent']);
        $torr = mysql_fetch_assoc($torr);
        $userip = explode(".", $arr['ip']);
        $userip = "$userip[0].$userip[1].x.x";
        $torrname = $torr["name"];
        if (strlen($torrname) > 40)
            $torrname = substr($torrname, 0, 40) . "...";
        $uratio = "<font color=\"" . get_ratio_color($arr['uratio']) . "\">" . $arr['uratio'] . "</font>";
        $peers = $torr['seeders'] . " seeder" . ($torr['seeders'] > 1 ? "s" : "") . ", " . $torr['leechers'] . " leecher" . ($torr['leechers'] > 1 ? "s" : "");

        print("<tr><td><a href=\"userdetails.php?id=" . $arr['userid'] . "\">" . $arr['username'] . "</a> (" . $uratio . ")</td><td><a href=\"details.php?id=" . $arr['torrent'] . "\" alt=\"" . $torr['name'] . "\" title=\"" . $torr['name'] . "\">" . $torrname . "</td><td>" . $peers . "</td></tr>\n");
    }
    print("</table>\n");

    end_frame();
    end_main_frame();

    stdfoot();
} else {
    stdhead("Torrents Needing Seeds");

    begin_main_frame();
    begin_frame("[<a href=\"?needed=leechers\" class=\"altlink\">Seeders In Need</a>] &nbsp;-&nbsp; Torrents Needing Seeds");

    $res = sql_query("SELECT id, name, seeders, leechers, added FROM torrents WHERE leechers >= 0 AND seeders = 0 ORDER BY leechers DESC LIMIT 40");
    if (mysql_num_rows($res) > 0) {
        print("<table class=main border=1 cellspacing=0 cellpadding=5>\n");
        print("<tr><td class=\"colhead\">Torrent</td><td class=\"colhead\">Seeders</a></td><td class=\"colhead\">Leechers</td></tr>\n");
        while ($arr = mysql_fetch_assoc($res)) {
            $torrname = htmlspecialchars($arr['name']);
            if (strlen($torrname) > 55)
                $torrname = substr($torrname, 0, 55) . "...";
            $ttl = (28 * 24) - floor((gmtime() - sql_timestamp_to_unix_timestamp($arr["added"])) / 3600);

            print("<tr><td><a href=\"details.php?id=" . $arr['id'] . "&hit=1\" alt=\"" . $arr['name'] . "\" title=\"" . $arr['name'] . "\">" . $torrname . "</td><td><font color=\"red\">" . $arr['seeders'] . "</a></td><td>" . number_format($arr['leechers']) . "</td></tr>\n");
        }
        print("</table>\n");
    } else
        print("-- There are no torrents needing seeds right now --\n");

    end_frame();
    end_main_frame();

    stdfoot();
}

?>
