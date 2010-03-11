<?php
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
require_once ("include/user_functions.php");
dbconn();
maxcoder();

$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res))
	$arr_config[$arr['name']] = $arr['value'];
	
if (!$arr_config["enable"])
stderr("Sorry", "Lottery is disabled.");

$user_class = get_user_class();
$class_allowed = array_map('trim', @explode('|', $arr_config["class_allowed"]));
if (!in_array($user_class, $class_allowed))
{
	stderr("Sorry", "This class level isn't allowed in this lottery.");
}

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

$ticket_amount_display = $arr_config['ticket_amount'];

if ($arr_config["ticket_amount_type"] == seedbonus)
$arr_config['ticket_amount'] = $arr_config['ticket_amount'];
$size = $arr_config['ticket_amount'];

if ($arr_config["ticket_amount_type"] == seedbonus)
$arr_config['prize_fund'] = 1000 * $arr_config['prize_fund'];
$prize_fund = $arr_config['prize_fund'];
	

$ratioerr = "<font color=\"red\"><b>You must have  at least $arr_config[ticket_amount] $arr_config[ticket_amount_type] points in order to buy a ticket !</b></font>";

stdhead("Tickets Page");

$total = mysql_num_rows(sql_query("SELECT * FROM tickets"));
if ($arr_config["use_prize_fund"])
	$pot = $prize_fund;
else
	$pot = $total * $size;

$res = sql_query("SELECT * FROM tickets WHERE user=". $CURUSER['id'] ." ORDER BY id ASC");
$me = mysql_num_rows($res);
while ($myrow = mysql_fetch_assoc($res))
	$ticketnumbers .= $myrow['id'];
	
$purchaseable = $arr_config['user_tickets'] - $me;
	
if ($me >= $arr_config["user_tickets"])
	$purchaseable = 0;
	
if (get_date_time() > $arr_config['end_date'])
	$purchaseable = 0;

print("<br>\n");

?>


<table border=1 width=600 cellspacing=0 cellpadding=5>
<tr><td class=tabletitle width=600 align=center>Lottery</td></tr>
<tr><td align=left class=tableb>
<ul>
<li>Tickets are non-refundable</li>
<li>Each ticket costs <?php echo $ticket_amount_display . ' ' . $arr_config['ticket_amount_type']; ?> points which is taken from your seedbonus total amount</li>
<li>Purchaseable shows how many tickets you can afford</li>
<li>You can only buy up to your purchaseable amount.</li>
<li>The competiton will end: <?php echo  $arr_config["end_date"]; ?></li>
<li>There will be <?php echo  $arr_config['total_winners']; ?> winners who will be picked at random</li>
<li>Winner(s) will get <?php echo  ($pot/$arr_config['total_winners']); ?> added to their seedbonus total amount</li>
<li>The Winners will be announced once the lottery has closed and posted on the home page.</li>
<?php
if (!$arr_config["use_prize_fund"])
{
?>
<li>The more tickets that are sold the bigger the pot will be !</li>
<?php
}
?>
<li>View purchased tickets:<a class=altlink href=viewtickets.php>Here</a> </li>
</ul>
Good Luck!
<hr>
<table align=center width=40% class=frame border=1 cellspacing=0 cellpadding=10><tr><td align=center>
<table width=100% class=tableb class=main border=1 cellspacing=0 cellpadding=5>
<tr>
<td class=tableb>Total Pot</td>
<td class=tableb><?php echo  ($pot); ?></td>
</tr>
<tr>
<td class=tableb>Total Tickets Purchased</td>
<td class=tableb align=right><?php echo  $total; ?> Tickets</td>
</tr>
<tr>
<td class=tableb>Tickets Purchased by You</td>
<td class=tableb align=right><?php echo  $me; ?> Tickets</td>

</tr>
<tr>
<td class=tableb>Purchaseable</td>
<td class=tableb align=right><?php echo  $purchaseable; ?> Tickets</td>
</tr>
</table>
</table>
<hr>
<?php
if ($purchaseable > 0)
{
?>
<center>
<form method="post" action="purchasetickets.php">
Purchase <input type="text" name="number"> Tickets <input type="submit" value="Purchase">
</form>
</center>
<?php
}
else if (get_date_time() > $arr_config['end_date'])
{
?>
<center><h1><font color = "red">Lottery is closed!</font></h1>
<?php
}
?>
</td></tr></table>

<?php 
print("<br>\n");

stdfoot();
die;

?>