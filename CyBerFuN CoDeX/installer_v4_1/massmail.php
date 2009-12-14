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

stdhead("Mass Mail");

if (safeChar($_POST['message']) != "")
{
$num=0;
foreach( array_keys($_POST) as $x)
{ 
if (substr($x,0,3) == "UC_")
{  
$querystring .= " OR class = ".constant($x);  
$classnames .= substr($x,3).", ";  $num++; 
}
} 
$res = mysql_query("SELECT id, username, email FROM users WHERE id = 1".
$querystring) or sqlerr(__FILE__, __LINE__); 
$from_email = "noreply@installerv4.net"; //==Change this to your site email 
$subject = substr(safeChar($_POST["subject"]), 0, 80); 
if ($subject == "") $subject = "(No subject)"; 
$subject = "Fw: $subject";  
$msg = safeChar($_POST["message"]); 
while($arr = mysql_fetch_array($res))
{ 
$to = $arr["email"]; 
$message = $msg;
$success = mail($to, $subject, $message, "From: noreply@installerv4.net", "-f noreply@installerv4.net"); //==Change this to your site email 
}
print("<b>Mass Mail successfully sent.</b><br>");
}
?>
<h1 class=embedded><img src=/pic/email.gif></h1>				
<h1>Mass E@Mail</h1><form method=post action="massmail.php"><table border=1 cellspacing=0 cellpadding=5 class="tablea" ><tr><td colspan=2 class="menu_clear_staff" height=23><div align=left><b>E@Mail by class (Which you select :-P):</b></div></td></tr><tr><td colspan=2>
<?php
echo "<table class=tabela cellpadding=0 cellspacing=0 width=100%>";
$numclasses=0;
$constants = get_defined_constants ();
foreach( array_keys($constants) as $x)
{
if (substr($x,0,3) == "UC_"){
echo "<td><input name=\"".$x."\" type=\"checkbox\" value=1>".substr($x,3)."</td>";
if ($numclasses==5)
echo "<tr></tr><tr></tr>";
$numclasses++;
}
}
echo "</table>";
?>
<input type="hidden" name="numclasses" value="
<?php 
echo $numclasses; 
?>"/></td></tr><tr><td class="tablea">Subject</td><td><input type=text size=88 name="subject"></textarea></td></tr><tr><td class="tablea">Message</td><td><textarea cols=88 rows=10 name="message"></textarea></td></tr><tr><td align="center" colspan=2><input type="submit" value="Okay" class="btn" /></td></tr></table></form>
<?php 
stdfoot(); 
?>