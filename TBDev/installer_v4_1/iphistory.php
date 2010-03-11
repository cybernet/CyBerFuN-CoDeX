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
if (get_user_class() < UC_MODERATOR)
    stderr("Error", "No Access");

function gethostbyaddr_timeout($ip, $timeout = 2)
{
    $host = `host -W $timeout $ip`;
    if (preg_match('`in-addr.arpa domain name pointer (.*)\.\n$`i', $host, $matches))
        $host = $matches[1];
    else
        $host = $ip;
    return $host;
}

$userid = 0 + $_GET["id"];
if (!is_valid_id($userid)) stderr("Error", "Invalid ID");

$res = mysql_query("SELECT username, class FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    stderr("Error", "User not found");

$arr = mysql_fetch_array($res);
$username = $arr["username"];
$page = 0 + $_GET["page"];
$perpage = 10;

$countrows = number_format(get_row_count("iplog", "WHERE userid =$userid")) + 1;
$order = $_GET['order'];

list($pagertop, $pagerbottom, $limit) = pager($perpage, $countrows, "iphistory.php?id=$userid&order=$order&");

if ($order == "ip")
    $orderby = "ip DESC, access";
else
    $orderby = "access DESC";

$query = "SELECT u.id, u.ip AS ip, last_access AS access FROM users as u WHERE u.id = $userid
UNION SELECT u.id, iplog.ip as ip, iplog.access as access FROM users AS u
RIGHT JOIN iplog on u.id = iplog.userid WHERE u.id = $userid ORDER BY $orderby $limit";

$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

stdhead("IP History Log for $username");
begin_main_frame();

$resip = mysql_query("SELECT * FROM iplog WHERE userid = '$userid' GROUP BY ip ORDER BY access DESC") or sqlerr(__FILE__, __LINE__);
//begin_frame()
?>
<tr>
  <td align="center" class="bottom" colspan="3"><h4>Historical IP addresses used by <a href=userdetails.php?id='.$userid.'><b><?=$username;
?></b></a></h4></td>
</tr>
<?php $ipcount = mysql_num_rows($resip);
?>
<tr>
  <td align="center" class="bottom" colspan="3">Total Unique IP Addresses User Has Logged In With <?=$ipcount;
?></td>
</tr>
<tr>
  <td align="center" class="colhead" width="30%">Last Access Time</td>
  <td align="center" class="colhead" width="30%">IP Address</td>
  <td align="center" class="colhead" width="30%">ISP Host Name</td>
</tr>
<?php while ($iphistory = mysql_fetch_array($resip)) {
    $host = gethostbyaddr_timeout($iphistory['ip']);

    ?>
<tr>
<td align="center"><?=$iphistory['access'];
    ?></td>
<td align="center"><?=$iphistory['ip'];
    ?></td>
<td align="center"><?=$host;
    ?></td>
</tr>
<?php } ;
// if ($countrows > $perpage)
// echo $pagertop;
//begin_table();
print("<tr>\n
<br><br><td class=colhead><a class=colhead href=\"" . $_SERVER['PHP_SELF'] . "?id=$userid&order=access\">Last access</a></td>\n
<td class=colhead><a class=colhead href=\"" . $_SERVER['PHP_SELF'] . "?id=$userid&order=ip\">IP</a></td>\n
<td class=colhead>Hostname</td>\n
</tr>\n");
while ($arr = mysql_fetch_array($res)) {
    $addr = "";
    $ipshow = "";
    if ($arr["ip"]) {
        $ip = $arr["ip"];
        $dom = @gethostbyaddr_timeout($arr["ip"]);
        if ($dom == $arr["ip"] || @gethostbyname($dom) != $arr["ip"])
            $addr = "";
        else
            $addr = $dom;

        $queryc = "SELECT COUNT(*) FROM
(
SELECT u.id FROM users AS u WHERE u.ip = " . sqlesc($ip) . "
UNION SELECT u.id FROM users AS u RIGHT JOIN iplog ON u.id = iplog.userid WHERE iplog.ip = " . sqlesc($ip) . "
GROUP BY u.id
) AS ipsearch";
        $resip = mysql_query($queryc) or sqlerr(__FILE__, __LINE__);
        $arrip = mysql_fetch_row($resip);
        $ipcount = $arrip[0];

        $nip = ip2long($ip);
        $banres = mysql_query("SELECT COUNT(*) FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
        $banarr = mysql_fetch_row($banres);
        if ($banarr[0] == 0)
            if ($ipcount > 1)
                $ipshow = "<b><a href=ipsearch.php?ip=" . $arr['ip'] . ">" . $arr['ip'] . "</a></b>";
            else
                $ipshow = "<a href=ipsearch.php?ip=" . $arr['ip'] . ">" . $arr['ip'] . "</a>";
            else
                $ipshow = "<a href='/testip.php?ip=" . $arr['ip'] . "'><font color='#FF0000'><b>" . $arr['ip'] . "</b></font></a>";
        }
        $date = $arr["access"];
        print("<tr><td>$date</td>\n");
        print("<td>$ipshow</td>\n");
        print("<td>$addr</td>\n");
    }

    //end_table();

    if ($countrows > $perpage)
        echo $pagerbottom;
    //end_frame();
    end_main_frame();
    stdfoot();

    ?>