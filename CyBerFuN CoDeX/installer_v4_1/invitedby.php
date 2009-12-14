<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
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
    hacker_dork("Invitedby - Nosey Cunt !");
stdhead();

begin_frame("Invited Users");
// ///////// by rulzmaker /////////////
$res2 = sql_query("SELECT COUNT(*) FROM users WHERE invitedby > 0");
$row = mysql_fetch_array($res2);
$count = $row[0];
$perpage = 50;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] . "?");
echo $pagertop;
// ///////// by rulzmaker /////////////
echo '<table width="640" border="0" align="center" cellpadding="2" cellspacing="0">';
echo "<tr><td class=colhead align=left>User</td><td class=colhead>Invited by</td><td class=colhead>Ratio</td><td class=colhead>IP</td><td class=colhead>Date Joined</td><td class=colhead>Last Access</td><td class=colhead>Download</td><td class=colhead>Upload</td></tr>";

$result = sql_query ("SELECT * FROM users WHERE " . unsafeChar(invitedby) . " > 0 AND status = 'confirmed' ORDER BY added DESC $limit");
if ($row = mysql_fetch_array($result)) {
    do {
        if ($row["uploaded"] == "0") {
            $ratio = "inf";
        } elseif ($row["downloaded"] == "0") {
            $ratio = "inf";
        } else {
            $ratio = number_format($row["uploaded"] / $row["downloaded"], 3);
            $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
        }
        $invitedby = sql_query("SELECT username FROM users WHERE id=$row[invitedby]");
        $invitedby2 = mysql_fetch_array($invitedby);

        echo "<tr><td><a href=userdetails.php?id=" . $row["id"] . "><b>" . $row["username"] . "</b></a></td><td><a href=userdetails.php?id=" . $row["invitedby"] . ">" . $invitedby2["username"] . "</a></td><td><strong>" . $ratio . "</strong></td><td>" . $row["ip"] . "</td><td>" . $row["added"] . "</td><td>" . $row["last_access"] . "</td><td>" . prefixed($row["downloaded"]) . "</td><td>" . prefixed($row["uploaded"]) . "</td></tr>";
    } while ($row = mysql_fetch_array($result));
} else {
    print "<tr><td>Sorry, no records were found!</td></tr>";
}
echo "</table>";
print($pagerbottom);
end_frame();
stdfoot();

?>