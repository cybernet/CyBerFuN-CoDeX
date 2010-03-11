<?php
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
require_once ("include/user_functions.php");
require_once ("include/function_bonus.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
//moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

if (get_user_class() < UC_MODERATOR)
stderr("Sorry", "Access denied.");

stdhead("Lottery");

begin_main_frame();

if ($_POST['lottery'] == 'config')
{
	
	$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
	while ($arr = mysql_fetch_assoc($res))
	{
		$name = $arr['name'];
		
		if ($name != 'class_allowed')
			$new_data = $_POST[$name];
		else
			$new_data = implode("|", $_POST['class_allowed']);
		if (($name != 'lottery_winners') && ($name != 'lottery_winners_amount') && ($name != 'lottery_winners_time'))
			sql_query("UPDATE lottery_config SET value = '$new_data' WHERE name = '$name'") or sqlerr(__FILE__, __LINE__);
	}
stdmsg("Settings Changed", "Settings were successfully change.<br /><br /><a href=lottery_config.php>Go Back</a>");
stdfoot();
die();
}

?>
<form action="lottery_config.php" method=post>

<?php

print("<table width=\"100%\">\n");

$res = sql_query("SELECT * FROM lottery_config") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res))
	$arr_config[$arr['name']] = $arr['value'];
	
if (!$arr_config["enable"])
	begin_frame("Lottery Configuration:", true);
	
if ($arr_config["enable"])
{
	begin_frame("Lottery Enabled", true);
	print("Lottery is currently enabled, so this configuration page is closed.<br /><br />Classes playing in this lottery, are: ");
	$class = explode("|", $arr_config['class_allowed']);
	$classes_playing = '';
	for ($x = 0; $x < count($class); $x++)
	{
		$classes_playing = (!$classes_playing) ? get_user_class_name($class[$x]) : ', ' . get_user_class_name($class[$x]);	
		print($classes_playing);
	}
	die;
}
	
if ($arr_config["ticket_amount_type"] != 'seedbonus')
	$selected = ' selected';

	
$use_prize_fund_yes = ($arr_config['use_prize_fund']) ? 'checked="checked"' : '';
$use_prize_fund_no 	= (!$arr_config['use_prize_fund']) ? 'checked="checked"' : '';

$enable_yes = ($arr_config['enable'] == 1) ? 'checked="checked"' : '';
$enable_no 	= ($arr_config['enable'] == 0) ? 'checked="checked"' : '';

	print("<tr><td width=50% class=\"tableb\" align=left>Enable The Lottery</td><td class=\"tableb\" align=left>Yes <input class=\"tableb\" type=radio name=enable value=\"1\" $enable_yes /> No <input class=\"tableb\" type=radio name=enable value=\"0\" $enable_no /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Use Prize Fund (No, uses default pot of all users)</td><td class=\"tableb\" align=left>Yes <input class=\"tableb\" type=radio name=use_prize_fund value=\"1\" $use_prize_fund_yes /> No <input class=\"tableb\" type=radio name=use_prize_fund value=\"0\" $use_prize_fund_no /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Prize Fund</td><td class=\"tableb\" align=left><input type=text name=prize_fund value=\"$arr_config[prize_fund]\" /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Ticket Amount</td><td class=\"tableb\" align=left><input type=text name=ticket_amount value=\"$arr_config[ticket_amount]\" /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Ticket Amount Type</td><td class=\"tableb\" align=left><select name=ticket_amount_type><option value=\"seedbonus\"$selected>seedbonus</option></select></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Amount Of Tickets Allowed</td><td class=\"tableb\" align=left><input type=text name=user_tickets value=\"$arr_config[user_tickets]\" /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Classes Allowed</td><td class=\"tableb\" align=left><ul class=\"checklist\">");
		
	$maxclass = UC_CODER + 1;	
	for ($i = 0; $i < $maxclass; $i++) 
	{
		$class_allowed 	= array_map('trim', @explode('|', $arr_config["class_allowed"]));
		if (in_array($i, $class_allowed))
		{
			if ($c = get_user_class_name($i))
				print("<li><label for=\"$i\"><input id=\"$i\" name=\"class_allowed[]\" type=\"checkbox\" checked=\"checked\" value=\"$i\" />$c</label></li>\n");
		}
		else
		{
			if ($c = get_user_class_name($i))
				print("<li><label for=\"$i\"><input id=\"$i\" name=\"class_allowed[]\" type=\"checkbox\" value=\"$i\" />$c</label></li>\n");
		}
	} 
	
	print("</ul></td><tr><td width=50% class=\"tableb\" align=left>Total Winners</td><td class=\"tableb\" align=left><input type=text name=total_winners value=\"$arr_config[total_winners]\" /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Current Date/Time</td><td class=\"tableb\" align=left>" . get_date_time()  . "</td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>Start Date</td><td class=\"tableb\" align=left><input type=text name=start_date value=\"$arr_config[start_date]\" /></td></tr>");
	print("<tr><td width=50% class=\"tableb\" align=left>End Date</td><td class=\"tableb\" align=left><input type=text name=end_date value=\"$arr_config[end_date]\" /></td></tr>");
if (get_user_class() >= UC_MODERATOR) 
{
?>
	<tr>
	  <td class="tableb" colspan="4" align="center">
		<input type="hidden" name="lottery" value="config"><input type="submit" name="submit" value="Apply Changes">
	  </td>
	</tr>
	</table></form>
<?php
}

end_frame();
end_main_frame();
stdfoot();
die;

?>
