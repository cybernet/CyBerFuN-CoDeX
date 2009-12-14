<?php
require ("include/bittorrent.php");
require ("include/user_functions.php");
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

if ($CURUSER["lotteryban"] == 'yes')
{
stdmsg("Sorry...", "You are Banned from using the lottery.  (See site staff for the reason why !");
stdfoot();
exit;
}

$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res))
	$arr_config[$arr['name']] = $arr['value'];

$endday = $arr_config['end_date'];

if (!$arr_config["enable"])
stderr("Sorry", "Lottery is disabled.");
stdhead("Tickets Sold");
print("Lottery Ends: <b>" . $endday . "</b><br /><br />");

?>
<table border="1" width="600" cellpadding="5">
<tr>
<td class="tabletitle">#</td>
<td class="tabletitle">Username</td>
<td class="tabletitle">Number of tickets</td>
<td class="tabletitle">Seedbonus</td>
</tr>
<?php
$sql = sql_query("SELECT user FROM tickets") or die (mysql_error());
while ($myrow = mysql_fetch_assoc($sql))
$user[] = $myrow["user"];
$user = array_values(array_unique($user));
for ($i = 0; $i < sizeof($user); $i++)
{
$tickets[] = mysql_num_rows(sql_query("SELECT * FROM tickets WHERE user=$user[$i]"));
$username[] = end(mysql_fetch_row(sql_query("SELECT username FROM users WHERE id=$user[$i]")));
$id[] = end(mysql_fetch_row(sql_query("SELECT id FROM users WHERE id=$user[$i]")));
$seedbonus[] = (end(mysql_fetch_row(sql_query("SELECT seedbonus FROM users WHERE id=$user[$i]"))));
echo "<tr><td class=tableb>" . ($i + 1) . "</td><td class=tableb><a href=userdetails.php?id=$id[$i]>$username[$i]</a></td><td class=tableb>$tickets[$i]</td><td class=tableb>$seedbonus[$i]</td></tr>";
}
?>
</table>
<?php
// Start Lottery Block  //
$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res))
$arr_config[$arr['name']] = $arr['value'];
$filel = "$CACHE/lottery.txt";
$expire = 5*6; // 50 minutes
if (file_exists($filel) && (time() - $expire
   < filemtime($filel))) {
$a=unserialize(file_get_contents($filel));
$endday = $a[1];
$lottogo = $a[2];
$who_won = $a[3];
$lottery_winners = $a[4];
} else {
$endday = $arr_config['end_date'];
$lottogo = ("<font color=red> (" . mkprettytime(strtotime($endday) - time()) . " to go)</font>");
$who_won = explode("|", $arr_config['lottery_winners']);
$who_won = array_unique($who_won);
$lottery_winners = '';
foreach ($who_won AS $winner)
{
    $username = '';
    $res2 = sql_query("SELECT id, username FROM users WHERE id = '" . $winner . "'") or sqlerr(__FILE__, __LINE__);
    while ($arr2 = mysql_fetch_assoc($res2))
    {
        $username = '<a href="userdetails.php?id='. $arr2['id'] .'">'. $arr2['username'] .'</a>';
        $lottery_winners .= (!$lottery_winners) ? $username : ', '. $username;                        
        break;
    }
}

if (count($who_won) > 1)
$winners = 'Winners';
else
$winners = 'Winner';

if (count($who_won) > 1)
$each = ' (Each)';
else
$each = '';
$stats1 = array(1 => "$endday", "$lottogo", "$who_won", "$lottery_winners");
$stats2 = serialize($stats1);
$fh = fopen($filel, "w");
fwrite($fh,$stats2);
fclose($fh);
}
    begin_frame("Lottery");?>
        <table width="100%" border="0" cellspacing="1" cellpadding="1" align="center">        
            <tr><td class="rowhead">Last Winner(s) </td><td class="rowhead" align="middle"><?php echo $lottery_winners?></td></tr>
            <tr><td class="rowhead">Last Amount Won<?php echo $each?></td><td class="rowhead" align="middle"><?php echo($arr_config['lottery_winners_amount'])?></td></tr>
            <tr><td class="rowhead">Date Of Last Lottery</td><td class="rowhead" align="middle"><?php echo $endday?></td></tr>
            <tr><td class="rowhead">Days remaining of current lottery</td><td class="rowhead" align="middle"><?php echo $lottogo ?></td></tr>
            </table>
            <?php
              end_frame();?>
<?php

stdfoot();
die;
?>
