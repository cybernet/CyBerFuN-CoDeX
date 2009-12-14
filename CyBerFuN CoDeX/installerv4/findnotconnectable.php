<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Error", "Access denied.");

if ($_GET['action'] == "list") {
    $res2 = mysql_query("SELECT userid, seeder, torrent, agent FROM peers WHERE connectable='no' ORDER BY userid DESC") or sqlerr();

    stdhead("Peers that are unconnectable");
    print("<a href=findnotconnectable.php?action=sendpm><h3>Send All not connectable Users A PM</h3></a>");
    print("<a href=findnotconnectable.php><h3>View the Log (Check this before PMing users)</h3></a>");
    print("<h1>Peers that are Not Connectable</h1>");
    print("This is only users that are active on the torrents right now.");

    print("<br><font color=red>*</font> means the user is seeding.<p>");
    $result = mysql_query("select distinct userid from peers where connectable = 'no'");
    $count = mysql_num_rows($result);
    print ("$count unique users that are not connectable.");
    @mysql_free_result($result);

    if (mysql_num_rows($res2) == 0)
        print("<p align=center><b>All Peers Are Connectable!</b></p>\n");
    else {
        print("<table border=1 cellspacing=0 cellpadding=5>\n");
        print("<tr><td class=colhead>UserName</td><td class=colhead>Torrent</td><td class=colhead>Client</td></tr>\n");
        while ($arr2 = mysql_fetch_assoc($res2)) {
            $r2 = mysql_query("SELECT username FROM users WHERE id=$arr2[userid]") or sqlerr();
            $a2 = mysql_fetch_assoc($r2);
            print("<tr><td><a href=userdetails.php?id=$arr2[userid]>$a2[username]</a></td><td align=left><a href=details.php?id=$arr2[torrent]&dllist=1#seeders>$arr2[torrent]");
            if ($arr2[seeder] == 'yes')
                print("<font color=red>*</font>");
            print("</a></td><td align=left>$arr2[agent]</td></tr>\n");
        }
        print("</table>\n");
    }
}

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") {
    $dt = sqlesc(get_date_time());
    $msg = $_POST['msg'];
    if (!$msg)
        stderr("Error", "Please Type In Some Text");

    $query = mysql_query("SELECT distinct userid FROM peers WHERE connectable='no'");
    while ($dat = mysql_fetch_assoc($query)) {
        mysql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES (0,$dat[userid] , '" . get_date_time() . "', " . sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
    }
    mysql_query("INSERT INTO notconnectablepmlog ( user , date ) VALUES ( $CURUSER[id], $dt)") or sqlerr(__FILE__, __LINE__);
    header("Refresh: 0; url=findnotconnectable.php");
    hit_end();
}

if ($_GET['action'] == "sendpm") {
    stdhead("Peers that are unconnectable");

    ?>
<table class=main width=750 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
<div align=center>
<h1>Mass Message to All Non Connectable Users</a></h1>
<form method=post action=findnotconnectable.php>
<?php

    if ($_GET["returnto"] || $_SERVER["HTTP_REFERER"]) {

        ?>
<input type=hidden name=returnto value=<?=$_GET["returnto"] ? $_GET["returnto"] : $_SERVER["HTTP_REFERER"]?>>
<?php
    }
    // default message
    $body = "The tracker has determined that you are firewalled or NATed and cannot accept incoming connections. \n\nThis means that other peers in the swarm will be unable to connect to you, only you to them. Even worse, if two peers are both in this state they will not be able to connect at all. This has obviously a detrimental effect on the overall speed. \n\nThe way to solve the problem involves opening the ports used for incoming connections (the same range you defined in your client) on the firewall and/or configuring your NAT server to use a basic form of NAT for that range instead of NAPT (the actual process differs widely between different router models. Check your router documentation and/or support forum. You will also find lots of information on the subject at PortForward). \n\nAlso if you need help please come into our IRC chat room or post in the forums your problems. We are always glad to help out.\n\nThank You";

    ?>
<table cellspacing=0 cellpadding=5>
<tr>
<td>Send Mass Messege To All Non Connectable Users<br>
<table style="border: 0" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="border: 0">&nbsp;</td>
<td style="border: 0">&nbsp;</td>
</tr>
</table>
</td>
</tr>
<tr><td><textarea name=msg cols=120 rows=15><?=$body?></textarea></td></tr>
<tr>
<tr><td colspan=2 align=center><input type=submit value="Send" class=btn></td></tr>
</table>
<input type=hidden name=receiver value=<?=$receiver?>>
</form>

</div></td></tr></table>
<br>
NOTE: No HTML Code Allowed. (NO HTML)
<?php
}
if ($_GET['action'] == "") {
    stdhead("Unconnectable Peers Mass PM Log");
    $getlog = mysql_query("SELECT * FROM `notconnectablepmlog` LIMIT 10");
    print("<h1>Unconnectable Peers Mass PM Log</h1>");
    print("<a href=findnotconnectable.php?action=sendpm><h3>Send All not connectable Users A PM</h3></a>");
    print("<a href=findnotconnectable.php?action=list><h3>List Unconnectable Users</h3></a>");
    print("<br>Please dont use the mass PM too often. we dont want to spam the users, just let them know they are unconnectable.<p>");
    print("<br>Every week would be ok.<p>");
    print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=colhead>By User</td><td class=colhead>Date</td><td class=colhead>elapsed</td></tr>");
    while ($arr2 = mysql_fetch_assoc($getlog)) {
        $r2 = mysql_query("SELECT username FROM users WHERE id=$arr2[user]") or sqlerr();
        $a2 = mysql_fetch_assoc($r2);
        $elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr2[date]));
        print("<tr><td class=colhead><a href=userdetails.php?id=$arr2[user]>$a2[username]</a></td><td class=colhead>$arr2[date]</td><td>$elapsed ago</td></tr>");
    }
    print("</table>");
}

stdfoot();

?>