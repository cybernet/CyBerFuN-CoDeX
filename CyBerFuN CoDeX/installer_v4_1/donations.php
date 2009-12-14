<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once "include/bbcode_functions.php";
dbconn(false);
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_SYSOP)
    stderr("Sorry", "Access denied!");

if (isset($_GET["total_donors"])) {
    $total_donors = 0 + $_GET["total_donors"];
    if ($total_donors != '1')
        stderr("Error", "I smell a rat!");

    $res = mysql_query("SELECT COUNT(*) FROM users WHERE total_donated != '0.00'") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_array($res);
    $count = $row[0];
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, "donations.php?");

    if (mysql_num_rows($res) == 0)
        stderr("Sorry", "no donors found!");

    $users = number_format(get_row_count("users", "WHERE total_donated != '0.00'"));
    stdhead("Donor List:: All Donations");
    begin_frame("Donor List: All Donations [ $users ]", true);
    $res = mysql_query("SELECT id,username,email,added,donated,total_donated FROM users WHERE total_donated != '0.00' ORDER BY id DESC $limit") or sqlerr(__FILE__, __LINE__);
}
// ===end total donors
else {
    $res = mysql_query("SELECT COUNT(*) FROM users WHERE donor='yes'") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_array($res);
    $count = $row[0];
    list($pagertop, $pagerbottom, $limit) = pager(25, $count, "donations.php?");

    if (mysql_num_rows($res) == 0)
        stderr("Sorry", "no donors found!");

    $users = number_format(get_row_count("users", "WHERE donor='yes'"));
    stdhead("Donor List:: Current Donors");
    begin_frame("Donor List: Current Donors [ $users ]", true);
    $res = mysql_query("SELECT id,username,email,added,donated,total_donated FROM users WHERE donor='yes' ORDER BY id DESC $limit") or sqlerr(__FILE__, __LINE__);
}

begin_table();
echo"<p align=center><a class=altlink href=donations.php>Current Donors</a> || <a class=altlink href=donations.php?total_donors=1>All Donations</a></p>";
echo $pagertop;

echo "<tr><td class=colhead>ID</td><td class=colhead align=left>Username</td><td class=colhead align=left>e-mail</td>" . "<td class=colhead align=left>Joined</td><td class=colhead align=left>Donor Until?</td><td class=colhead align=left>" . "Current</td><td class=colhead align=left>Total</td><td class=colhead align=left>PM</td></tr>";

while ($arr = @mysql_fetch_assoc($res)) {
    // =======change colors
    if ($count2 == 0) {
        $count2 = $count2 + 1;
        $class = "clearalt7";
    } else {
        $count2 = 0;
        $class = "clearalt6";
    }
    // =======end
    echo "<tr><td valign=bottom class=$class><a class=altlink href=userdetails.php?id=" . safeChar($arr[id]) . ">" . safeChar($arr[id]) . "</a></td>" . "<td align=left valign=bottom class=$class><b><a class=altlink href=userdetails.php?id=" . safeChar($arr[id]) . ">" . safeChar($arr[username]) . "</b>" . "</td><td align=left valign=bottom class=$class><a class=altlink href=mailto:" . safeChar($arr[email]) . ">" . safeChar($arr[email]) . "</a>" . "</td><td align=left valign=bottom class=$class><font size=\"-3\">" . safeChar($arr[added]) . "</font></a>" . "</td><td align=left valign=bottom class=$class>";

    $r = @mysql_query("SELECT donoruntil FROM users WHERE id=" . sqlesc($arr[id]) . "") or sqlerr();
    $user = mysql_fetch_array($r);
    $donoruntil = $user['donoruntil'];
    if ($donoruntil == '0000-00-00 00:00:00')
        echo "n/a";
    else
        echo "<font size=\"-3\"><p>$donoruntil [ " . mkprettytime(strtotime($donoruntil) - gmtime()) . " ] to go...</font></p>";

    echo "</td><td align=left valign=bottom class=$class><b>£" . safeChar($arr[donated]) . "</b></td>" . "<td align=left valign=bottom class=$class><b>£" . safeChar($arr[total_donated]) . "</b></td>" . "<td align=left valign=bottom class=$class><b><a class=altlink href=sendmessage.php?receiver=" . safeChar($arr[id]) . ">PM</a></b></td></tr>";
}
end_table();
end_frame();
echo $pagerbottom;

stdfoot();
die;

?>