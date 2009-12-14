<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();	
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_ADMINISTRATOR)
hacker_dork("Secure Ip - Nosey Cunt !");

// in the case part add staff names exactly as they are on site
//example
//case 'Admin':
//case 'System':
// and so on
switch ($_POST['staffname'])
{
                case'Mindless':
                case'System':

$name = safeChar($_POST['staffname']);
$pass = safeChar($_POST['secrettop']);

break;

default:
$naughtyboy = getip();
$name = safeChar($_POST['staffname']);
$msg = "Someone is trying to login through the Staff login page with the name $name and ip $naughtyboy";
$subject = "ALERT Failed staff login attempt";
// change id to your id to recieve a pm if someone tried to login with failed name or just comment it out
mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES (0, 1, '" . get_date_time() . "', " . sqlesc($msg) . ", 0)") or sqlerr(__FILE__, __LINE__);
stderr("Error", "WARNING ! You're not a staff member");
die();
break;
}

//Just keep adding the elseif and validpass until all staff have been added..

if ($_POST['staffname'] == "Mindless")
$validpass = "embassy1";
elseif ($_POST['staffname'] == "System")
$validpass = "richmond1";
else
die();

if ($validpass != $pass){
$letsgetinfo = mysql_query("SELECT tries FROM secureiptable WHERE username=".sqlesc($name))or sqlerr(__FILE__, __LINE__);
$assignme = mysql_fetch_assoc($letsgetinfo);
$chances = $assignme["tries"];

/*
$msg = '$name was disabled after 3 tries!!!';
$subject = "Staff login ban!";    */

if ($chances == 1){
@mysql_query("UPDATE secureiptable SET tries='2' WHERE username=".sqlesc($name));
stderr("Error", "Wrong Pin #, You got 2 tries left before having your account disabled!<br/><br/><input type=\"button\" value=\" Go Back \" onclick=\"history.back()\">", false);
}
elseif ($chances == 2){
@mysql_query("UPDATE secureiptable SET tries='3' WHERE username=".sqlesc($name));
stderr("Error", "Wrong Pin #, You got 1 try left before having your account disabled!<br/><br/><input type=\"button\" value=\" Go Back \" onclick=\"history.back()\">", false);
}
elseif ($chances == 3){
$banthisip = getip();
$first = ip2long($banthisip);
$last = ip2long($banthisip);
$added = sqlesc(get_date_time());
$msg = sqlesc("disabled after 3 tries");
mysql_query("UPDATE users SET enabled='no' WHERE username=".sqlesc($name));
mysql_query("DELETE FROM secureiptable WHERE username=".sqlesc($name));
mysql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES($added, 14, $first, $last, $msg)") or sqlerr(__FILE__, __LINE__);
// change id to your id to recieve a pm if someone was banned or just comment it out
mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster, subject) VALUES (0, 1, '" . get_date_time() . "', " . sqlesc($msg) . ", 0, ". sqlesc($subject) . ")");
die();
}else{
$iptrack = getip();
$trackingyou = ip2long($iptrack);
mysql_query("INSERT INTO secureiptable VALUES(0, ".sqlesc($name).", ".sqlesc($trackingyou).", 1, '" . get_date_time() . "', 0)")or sqlerr(__FILE__, __LINE__);
stderr("Error", "Wrong Pin #, You got 3 tries left before having your account disabled!<br/><br/><input type=\"button\" value=\" Go Back \" onclick=\"history.back()\">", false);
}
}

// end of 3 failed code
$tempip = getip();


$first1 = trim($tempip);
$last1 = trim($tempip);
$first = ip2long($first1);
$last = ip2long($last1);

$doubleip = mysql_query("SELECT * FROM ipsecureip WHERE $first >= first AND $last <= last") or sqlerr(__FILE__, __LINE__);
   if (mysql_num_rows($doubleip) > 0){
   stderr("Error", "This IP is already in there no need to re-add");
   die;
   }
   else

stdhead("Request to add TempIP Gateway");

?>
<p><table border=0 class=main cellspacing=0 cellpadding=3><tr>
<td class=embedded><img src=/pic/email.gif>&nbsp;&nbsp;&nbsp;</td>
<td class=embedded><font size=2><b><?=$name;?> would you like to add IP <font color="red"><b><?=$tempip;?></b></font> to the staff ip list for 12 hours ?</b></font></td>
</tr></table></p>
<table border=1 cellspacing=0 cellpadding=5>
<form method=post action=conipadd.php>
<input name="verify" type="radio" value="yes" checked><b>Yes&nbsp;&nbsp; <input name="verify" type="radio" value="no" checked> No</b><br><br>
<input type="hidden" name="staffname" value=<?=$name;?>>
<input type="submit" value="Do It!" class=btn>
 <br>
 <?php
 stdfoot();
 ?>