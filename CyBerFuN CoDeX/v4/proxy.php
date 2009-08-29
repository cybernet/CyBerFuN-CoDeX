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
if (get_user_class() < UC_CODER)
    hacker_dork("Proxys?- Nosey Cunt !");
stdhead("Possible Proxy User List");
print"<big><b>Possible Proxy Users</b></big><p>";
print"<b>Lists User That Have Different Ip On the Tracker</b><p>";
print"<table border=1 cellpadding=2 cellspacing=1>\n";
print"<tr style='font-weight:bold'><td>userid</td><td>Host</td><td>Joined</td><td>Logged IP</td><td>Tracked IP</td><td>Warned</td></tr>\n";
$res = mysql_query("SELECT DISTINCT ip,userid FROM peers") or sqlerr();
while ($row = mysql_fetch_array($res)) {
    $userip = $row["ip"];
    $userid = $row["userid"];
    $longip = ip2long($userip);
    $upper = $longip + 167772160;
    $lower = $longip-167772160;
    $res3 = mysql_query("SELECT username,ip,added,warned,class,donor FROM users WHERE id=".unsafeChar($userid)." and ip<>".unsafeChar($userip)."") or sqlerr();
    $active = mysql_num_rows($res3);
    if ($active == 1) {
        $row2 = mysql_fetch_array($res3);
        $ip = $row2["ip"];
        $name = $row2["username"];
        $joindate = $row2["added"];
        $warned = $row2["warned"];
        $class = $row2["class"];
        $donor = $row2["donor"];
        $ip2 = $ip;
        $dom = @gethostbyaddr($ip);
        if ($dom == $ip || @gethostbyname($dom) != $ip)
            $addr = $ip2;
        else {
            $dom = strtoupper($dom);
            $domparts = explode(".", $dom);
            $domain = $domparts[count($domparts) - 2];
            if ($domain == "COM" || $domain == "CO" || $domain == "NET" || $domain == "NE" || $domain == "ORG" || $domain == "OR")
                $l = 2;
            else
                $l = 1;
            $addr = "($dom)";
        }
        $longcip = ip2long($ip);
        If ($longcip <= $lower or $longcip >= $upper) {
            print"<tr><td><a href=userdetails.php?id=" . $row["userid"] . ">" . $row2["username"] . "</a></td>";
            print"<td align=right>$addr</td><td align=right>$joindate</td><td align=right>$ip</td><td align=right>$userip</td><td align=right>$warned</td></tr>\n";
            $username = $row["userid"];
            $pwsecs = 7 * 86400;
            $pwdt = sqlesc(get_date_time(gmtime() + $pwsecs));
            // $msg = sqlesc("Possible Proxy Server : You Have Been Seen to be downloading on a different IP to what you logged into the site with. The use of Proxy servers is not allowed on the site unless you have a good reason to do so. Most times it's to gain access to the site when banned or for Dupe accounts?PLEASE CONTACT STAFF TO HAVE WARNING REMOVED\n.");
            // mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES(0, $userid, NOW(), $msg, 0)") or sqlerr(__FILE__, __LINE__);
            $modcomment = gmdate("Y-m-d") . " - Possible Proxy : Logged IP = $ip Tracked IP = $userip.\n.";
            $modcom = sqlesc($modcomment);
            // mysql_query("UPDATE users SET warned = 'yes', warneduntil = $pwdt,modcomment = CONCAT($modcom,modcomment) WHERE id = $userid and class<5 and donor='no'") or sqlerr(__FILE__, __LINE__);
            write_log("Possable Proxy $name($userid) Logged IP = $ip Tracked IP = $userip");
        }
    }
}
print"</table>";
stdfoot();

?>