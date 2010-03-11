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
    stderr("Error", "No Access");

stdhead("Duplicate IP users");
begin_frame("Duplicate IP users:", true);
begin_table();

if (get_user_class() >= UC_MODERATOR) {
    $res = sql_query("SELECT count(*) AS dupl, ip FROM users WHERE ip <> '' AND ip <> '127.0.0.0' GROUP BY ip HAVING dupl > 1 ORDER BY dupl DESC, ip") or sqlerr();
    print("<tr align=center><td class=colhead width=90>User</td>
<td class=colhead width=70>Email</td>
<td class=colhead width=70>Registered</td>
<td class=colhead width=75>Last access</td>
<td class=colhead width=70>Downloaded</td>
<td class=colhead width=70>Uploaded</td>
<td class=colhead width=45>Ratio</td>
<td class=colhead width=125>IP</td>
<td class=colhead width=40>Peer</td></tr>\n");
    $uc = 0;
    while ($ras = mysql_fetch_assoc($res)) {
        if ($ras["dupl"] <= 1)
            break;
        $dis_res = mysql_fetch_row(sql_query("SELECT count(*) FROM users WHERE ip = " . sqlesc($ras["ip"]) . " AND enabled = 'yes'"));
        if ($dis_res[0] > 0) {
            if ($ip <> $ras['ip']) {
                $ros = sql_query("SELECT id, username, email, added, last_access, downloaded, uploaded, ip, warned, donor, enabled FROM users WHERE ip='" . $ras['ip'] . "' ORDER BY id") or sqlerr();
                $num2 = mysql_num_rows($ros);
                if ($num2 > 1) {
                    $uc++;
                    while ($arr = mysql_fetch_assoc($ros)) {
                        if ($arr['added'] == '0000-00-00 00:00:00')
                            $arr['added'] = '-';
                        if ($arr['last_access'] == '0000-00-00 00:00:00')
                            $arr['last_access'] = '-';
                        if ($arr["downloaded"] != 0)
                            $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
                        else
                            $ratio = "---";

                        $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
                        $uploaded = prefixed($arr["uploaded"]);
                        $downloaded = prefixed($arr["downloaded"]);
                        $added = substr($arr['added'], 0, 10);
                        $last_access = substr($arr['last_access'], 0, 10);
                        if ($uc % 2 == 0)
                            $utc = "";
                        else
                            $utc = " bgcolor=\"purple\"";
                        $peer_res = sql_query("SELECT count(*) FROM peers WHERE ip = " . sqlesc($ras['ip']) . " AND userid = " . $arr['id']);
                        $peer_row = mysql_fetch_row($peer_res);
                        print("<tr$utc><td align=left><b><a href='userdetails.php?id=" . $arr['id'] . "'>" . $arr['username'] . "</b></a>" . get_user_icons($arr) . "</td>
<td align=center>$arr[email]</td>
<td align=center>$added</td>
<td align=center>$last_access</td>
<td align=center>$downloaded</td>
<td align=center>$uploaded</td>
<td align=center>$ratio</td>
<td align=center>$arr[ip]</td>\n<td align=center>" .
                            ($peer_row[0] ? "Yes" : "No") . "</td></tr>\n");
                        $ip = $arr["ip"];
                    }
                }
            }
        }
    }
} else {
    print("<br><table width=60% border=1 cellspacing=0 cellpadding=9><tr><td align=center>");
    print("<h2>Sorry, only for Team</h2></table></td></tr>");
}
end_frame();
end_table();

stdfoot();

?>