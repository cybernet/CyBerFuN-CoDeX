<?php
require "include/bittorrent.php";
require "include/bbcode_functions.php";
require_once ("include/user_functions.php");

$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res))
$arr_config[$arr['name']] = $arr['value'];
$endday = $arr_config['end_date'];
if (!$arr_config["enable"])
stderr("Sorry", "Lottery is disabled.");
dbconn();
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

$minbonus = $size; //Minimum Bonus Required to Buy Ticket!

stdhead("Tickets Page");

if (get_user_class() < 0)
{
print("<h1>Sorry</h2><p>You must be Registered to request, see the <a href=faq.php><b>FAQ</b></a> for information on different user classes</p>");
die();
}

if (get_date_time() > $arr_config['end_date'])
{
	print ("Sorry I cannot sell you any tickets!");
	die();
}

$res = sql_query("SELECT seedbonus FROM users WHERE id = $CURUSER[id]") or die(mysql_error());
$result = mysql_fetch_assoc($res);

$res2 = sql_query("SELECT COUNT(id) AS tickets FROM tickets WHERE user = $CURUSER[id]") or die(mysql_error());
$result2 = mysql_fetch_assoc($res2);

$purchaseable = $arr_config['user_tickets'];
if (($result2['tickets'] + $_REQUEST['number']) > $purchaseable || $_REQUEST['number'] < 1 )
{
print("<table class=frame width=737 cellspacing=0 cellpadding=5><tr><td><table class=main width=100% cellspacing=0 cellpadding=5><tr><td class=colhead align=left>ERROR</td></tr><tr><td>The max number of tickets you can purchase is $purchaseable<br></td></tr></table></td></tr></table>");
stdfoot();
die;
}

if (($minbonus * $_REQUEST['number']) > $result["seedbonus"] )
{
print("<table class=frame width=737 cellspacing=0 cellpadding=5><tr><td><table class=main width=100% cellspacing=0 cellpadding=5><tr><td class=colhead align=left>ERROR</td></tr><tr><td>You do not have enough seedbonus points to buy a ticket<br></td></tr></table></td></tr></table>");
stdfoot();
die;
}
$seedbonus = $result["seedbonus"] - ($minbonus * $_REQUEST['number']);
sql_query("UPDATE users SET seedbonus=$seedbonus WHERE id=". $CURUSER["id"]) or die(mysql_error());
$tickets = $_REQUEST['number'];
for ($i = 0; $i < $tickets; $i++)
sql_query("INSERT INTO tickets(user) VALUES($CURUSER[id])");
$me = mysql_num_rows(sql_query("SELECT * FROM tickets WHERE user=" . $CURUSER["id"]));
print("<br>\n");

?>
<table border=1 width=600 cellspacing=0 cellpadding=5>
<tr><td class=tabletitle width=600 align=center>Lottery</td></tr>
<tr><td class=tableb align=left>
You just purchased <?php echo  $_REQUEST["number"]; ?> ticket<? if ($_REQUEST["number"] > 1) echo "s"; ?>!<br>
Your new total is <?php echo  $me; ?>!<br>
Your new seedbonus total is <?php echo  ($seedbonus); ?>!<br><br>
<a href=tickets.php>Go Back</a>
</td></tr></table>

<?php
print("<br>\n");

stdfoot();
die;

?>