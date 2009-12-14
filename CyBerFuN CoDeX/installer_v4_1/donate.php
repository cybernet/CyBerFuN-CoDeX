<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn(false);
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();
stdhead("$SITENAME donations");

begin_main_frame();

$nick = ($CURUSER ? $CURUSER["username"] : ("Guest" . rand(1000, 9999)));

///======= note! you may have to turn on IPN at paypal to get your user back to the site... notify_url does not always work

$amount .= "<select name=amount><option value=\"0\">Please select donation amount</option>";
$i = "5";
while($i <= 200){
$amount .= "<option value=".$i.">Donation of £".$i.".00 GBP</option>";
//$i = $i + 5;
$i = ($i < 100 ? $i = $i + 5 : $i = $i + 10);
}
$amount .= "</select>";
?>
<SCRIPT LANGUAGE="JavaScript">
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=740,height=380,left = 340,top = 280');");
}
</script>
<div align="center"><p><br>
<table width="80%" border="0" align="center">
	<tr><td align="center" valign="middle" class=colhead><h1><?=$SITENAME?></h1></td></tr>
	<tr><td align="center" valign="middle" class=embedded>
	<br></b><br>
	<p align=center><b>Select Donation amount, and click the PayPal button to play casino!</b><br>
	</p>

<!-- form goes here -->
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="Bigjoos1@hotmail.co.uk"> <!-- change to your paypal email -->
<input type="hidden" name="item_name" value="( <?=$nick;?> donation )">
<input type="hidden" name="item_number" value="1">
<input type="hidden" name="no_note" value="1">
<p align=center>
<b>Donate:</b> <?=$amount?><br><br>
<input type="hidden" name="currency_code" value="GBP">
<input type="hidden" name="tax" value="0">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="custom" value="<? echo $CURUSER['id'] ?>">
<input type="hidden" name="notify_url" value="http://codexinstaller.net/scene.php"> <!-- link to your paypal.php script, change to another name ;)  -->
<input type="hidden" name="return" value="http://codexinstaller.net/scene.php"> <!-- link to your paypal.php script, change to another name ;)  -->
<input type="image" align="middle" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</p>
<!-- form ends here -->
</form>
<br><p><b><u>The donation process is fully automated</u></b>:<br>
However, once you have completed your donation at the PayPal site, you <b>MUST</b> click the <b>return to merchant button,</b> <u>your account will <b> be credited once you pm Mindless with your transaction details</b></u></p>
<p><u><b>Please note</b></u> - all donations go towards running the site. Remember, we run this site out of love for the community on a volenteer basis. The actual costs include:
<br><ul>
    	<li>Domain Name registration. [yearly]</li>
    	<li>Server . [ram - cpu - HD etc]</li>
    	<li>Site Seedbox</li>
    	</ul>
<center><b>Thank you for your support!</b></center></p>
<p align="center">Processed through <?=$SITENAME?>'s Secure & Reliable Paypal Payment Portal<br>
<img src="pic/paypal/visa.gif" alt="visa"> <img src="pic/paypal/mastercard.gif" alt="mastercard"> <img src="pic/paypal/amex.gif" alt="amex"> <img src="pic/paypal/discover.gif" alt="discover"> <img src="pic/paypal/echeck.gif" alt="echeck"> or  <img src="pic/paypal/paypal.gif" alt="paypal"><br>
A PayPal account is not required for Credit Card payments.  [ <a href="javascript:popUp('popup_paypal_cc_help.php')">more info</a> ]<br><br></p>
</td></tr></table></div>
<?
end_main_frame();
stdfoot();
die();
?>