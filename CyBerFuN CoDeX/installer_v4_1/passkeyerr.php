<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Passkey Cheaters- Nosey Cunt !");
stdhead("Passkey errors!");
begin_main_frame();
begin_frame("Cheating Users?", false);

/* Passkey errors by x0r @ TBDEV.net */
// Get errors
$res = mysql_query("SELECT * FROM passkeyerr ORDER BY lastchange DESC LIMIT 100") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res)) {
    $res2 = mysql_query("SELECT id, username, enabled FROM users WHERE ip = " . sqlesc($arr["ip"]) . " LIMIT 5") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res2) > 0 || $arr["times"] > 100) {
        echo("<strong><a href=\"usersearch.php?ip=$arr[ip]\" class=\"altlink\">$arr[ip]</a></strong><br />");
        while ($arr2 = mysql_fetch_assoc($res2))
        echo("User w. IP: <a href=\"userdetails.php?id=$arr2[id]\" class=\"altlink\">$arr2[username]</a>" . ($arr2["enabled"] == "no" ? "<img src=\"pic/disabled.gif\" alt=\"This account is disabled\" style=\"margin-left: 2px\" />" : ($arr2["warned"] == "yes" ? "<a href=rules.php#warning class=altlink><img src=\"pic/warned.gif\" alt=\"Warned\" border=\"0\" /></a>" : "")) . "<br />");
        @mysql_free_result($res2);

        echo("Offences: $arr[times]<br />");
        echo("Last change: " . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["lastchange"])) . " ago ($arr[lastchange])<br />");
        echo("Added: " . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"])) . " ago ($arr[added])<br />");
        echo("First passkey: $arr[firstpasskey]<br />");
        echo("Last passkey: $arr[lastpasskey]<br />");
        echo("First reason: $arr[firstreason]<br />");
        echo("Last reason: $arr[lastreason]<br /><br />");
    }
}
@mysql_free_result($res);

end_frame();
end_main_frame();
stdfoot();

?>