<?php
require ("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_SYSOP)
hacker_dork("Mass Mail - Nosey Cunt !");

$class = 0 + $_POST["class"];
$or = 0 + $_POST["or"];

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST")
{
$res = mysql_query("SELECT id, username, email FROM users WHERE class $or $class") or sqlerr(__FILE__, __LINE__);

      $from_email = "noreply@installerv4.net"; //site email

$subject = substr(trim($HTTP_POST_VARS["subject"]), 0, 80);
if ($subject == "") $subject = "(no subject)";
$subject = "Fw: $subject";

$message1 = trim($HTTP_POST_VARS["message"]);
if ($message1 == "") stderr("Error", "Empty message!");


      while($arr=mysql_fetch_array($res)){
     
      $to = $arr["email"];
     

$message = "Message received from InstallerV4 on " . gmdate("Y-m-d H:i:s") . " GMT.\n" .
"---------------------------------------------------------------------\n\n" .
$message1 . "\n\n" .
"---------------------------------------------------------------------\n$SITENAME\n";

$success = mail($to, $subject, $message, "Od: $from_email", "-f$SITEEMAIL");
     
      }
     
     
if ($success)
stderr("Success", "Messages sent.");
else
stderr("Error", "Try again.");

}

stdhead("Mass E-mail Gateway");
?>

<p><table border=0 class=main cellspacing=0 cellpadding=0><tr>
<td class=embedded><img src=/pic/email.gif></td>
<td class=embedded style='padding-left: 10px'><font size=3><b>Send mass e-mail to all members</b></font></td>
</tr></table></p>
<table border=1 cellspacing=0 cellpadding=5>
<form method=post action=massmail.php>
<!--<tr><td class=rowhead>Your name</td><td><input type=text name=from size=80></td></tr>-->
<?
if (get_user_class() == UC_MODERATOR && $CURUSER["class"] > UC_POWER_USER)
printf("<input type=hidden name=class value=$CURUSER[class]\n");
else
{
print("<tr><td class=rowhead>Classe</td><td colspan=2 align=left><select name=or><option value='<'><<option value='>'>><option value='='>=<option value='<='><=<option value='>='>>=</select><select name=class>\n");
if (get_user_class() == UC_MODERATOR)
$maxclass = UC_POWER_USER;
else
$maxclass = get_user_class() - 1;
for ($i = 0; $i <= $maxclass; ++$i)
print("<option value=$i" . ($CURUSER["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");
print("</select></td></tr>\n");
}
?>

<!--<tr><td class=rowhead>Your e-mail</td><td><input type=text name=from_email size=80></td></tr>-->
<tr><td class=rowhead>Subject</td><td><input type=text name=subject size=80></td></tr>
<tr><td class=rowhead>Body</td><td><textarea name=message cols=80 rows=20></textarea></td></tr>
<tr><td colspan=2 align=center><input type=submit value="Send" class=btn></td></tr>
</form>
</table>

<?
stdfoot();
?>
