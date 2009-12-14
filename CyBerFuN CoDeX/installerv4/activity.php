<?

// CyBerFuN.Ro
// By CyBerNe7
//            //
// http://cyberfun.ro/
// http://xlist.ro/

// ==nvsource script
// == slight updates for Tbdev Installer
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Activity Charts - Nosey Cunt !");

function get_count($inactive_time)
{
    $arr = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS cnt FROM users WHERE UNIX_TIMESTAMP(`last_access`)<" . $inactive_time));
    return $arr["cnt"];
}

function stat_row($time, $count)
{
    global $usercount;

    echo '<tr><td class="tablea">', $time, "</td>\n";
    echo '<td class="tableb" nowrap><img src="pic/bar.gif" height="9" width="';
    echo (int)((float)$count / (float)$usercount * 600), '"> ', $count, '</td></tr>', "\n";
}

stdhead("User Activity Chart");
begin_frame("User Activity Chart", false);
begin_table(true);
echo '<tr><td class="tablecat">Inactive since</td><td class="tablecat">Number (percentage of total users)</td></tr>', "\n";

$curtime = time();
$uarr = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS cnt FROM users"));
$usercount = $uarr["cnt"];
// 24h, stdl. --> 24x
for ($I = 0; $I < 24; $I++) {
    stat_row($I . "h", get_count($curtime - ($I * 3600)));
}
// 48 Hours (2d), 3 stdl. --> 8x
for ($I = 24; $I < 48; $I += 3) {
    stat_row($I . "h", get_count($curtime - ($I * 3600)));
}
// 72 Hours (3d), 6 stdl. --> 4x
for ($I = 48; $I < 72; $I += 6) {
    stat_row($I . "h", get_count($curtime - ($I * 3600)));
}
// 96 Hours (4d), 12 stdl. --> 2x
for ($I = 72; $I < 96; $I += 12) {
    stat_row($I . "h", get_count($curtime - ($I * 3600)));
}
// 5-7d, 24stdl. --> 3x
for ($I = 4; $I < 7; $I++) {
    stat_row($I . "d", get_count($curtime - ($I * 3600 * 24)));
}
// 1-6 weeks, wtl.
for ($I = 1; $I < 7; $I++) {
    stat_row($I . "w", get_count($curtime - ($I * 3600 * 24 * 7)));
}

end_table();
end_frame();
stdfoot();

?>
