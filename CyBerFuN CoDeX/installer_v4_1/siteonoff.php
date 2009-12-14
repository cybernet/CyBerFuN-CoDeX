<?php 
require ("include/bittorrent.php"); 
//require ("include/user_functions.php"); 
require ("include/bbcode_functions.php"); 
dbconn();
maxcoder(); 
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_SYSOP) { 
stdhead("Error!"); 
$iduser= ($CURUSER["id"]); $addusername = $CURUSER['username']; $link_touser = "<a target='_blank' href='userdetails.php?id=$iduser'>$addusername</a>";
write_log("$link_touser is have message:<br>Permission denied! (Open/Close site).","FF9900","error");
stdmsg('Error',"Permission denied!", error); 
stdfoot(); 
die(); 
} 

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") 
{ 
if (!$_POST["reason"]){ 
stdhead("Error!"); 
stdmsg("Error", "You must add reason!", error); 
stdfoot(); 
die; 
} 

$reason = sqlesc("".$_POST['reason']."");
$class = 0 + $_POST["class"]; 
$onoff = 0 + $_POST["onoff"]; 
$cname = $class; 


switch ($cname) { 
case '0': 
$cname = "just for User"; 
break; 
case '1': 
$cname = "just for Power User"; 
break; 
case '2': 
$cname = "just for VIP"; 
break; 
case '3': 
$cname = "just for Uploader"; 
break; 
case '4': 
$cname = "just for Moderator"; 
break; 
case '5': 
$cname = "just for Administrator"; 
break; 
case '6': 
$cname = "just for SysOp"; 
break; 
case '7': 
$cname = "just for Coder"; 
break;  
default: 
$cname = "for all"; 
} 

$class_name = sqlesc("$cname"); 

sql_query("UPDATE siteonline SET onoff =$onoff, reason =$reason, class =$class, class_name = $class_name") or sqlerr(__FILE__, __LINE__);

header("Location: $DEFAULTBASEURL/siteonoff.php"); 

} 

stdhead("Open / Close site"); 

$res = sql_query("SELECT * FROM siteonline") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_array($res); // 

if ($row["onoff"] !=1){ 
$table = ("<td colspan='2' class=myhighlight style='padding:4px; background-color: #FF0000; color:#FFFFFF'>&nbsp; 
<b>Site&nbsp;Closed For Rountine Maintenance</b>!&nbsp;Class access:&nbsp;<b>".$row['class']."</b>&nbsp;(Access&nbsp;".$row['class_name'].").&nbsp; 
Your class:&nbsp;<b>".$CURUSER['class']."</b>.</td>"); 
} 
else { 
$table = ("<td colspan='2' class=myhighlight style='padding:4px; background-color: #EAFFD5; color:#008000'>&nbsp; 
<b>Site&nbsp;Is Online</b>!&nbsp;Access to all classes.&nbsp; 
Your class:&nbsp;<b>".$CURUSER['class']."</b>.</td>"); 
} 

?> 
<form method="POST" action="siteonoff.php"> 
<table border="1" cellspacing="0" cellpadding="0" style="border-collapse: collapse"> 
<tr> 
<td class=colhead><center><font size='3'>::&nbsp;&nbsp;Open site&nbsp;&nbsp;:&nbsp;&nbsp;Close site&nbsp;&nbsp;::</font></center></td> 
</tr><tr><td><table border="0" cellspacing="1"> 
<tr><td class=embedded> 
<table border="0" cellspacing="2"><tr><?=$table?></tr><tr> 
<td class=embedded colspan="2" height="3"></td></tr><tr> 
<td class=colhead>&nbsp;Message for site offline :</td> 
<td class=colhead></td></tr><tr> 
<td class=embedded valign="top"> 
<textarea rows="9" name="reason" cols="60"><?=($row["reason"])?></textarea></td> 
<td class=embedded align="left" valign="top"> 
<table border="0" cellspacing="1" id="table1" align="left"> 
<tr><td class=embedded height="2" colspan="2"></td></tr> 
<tr><td class=colhead colspan="2">&nbsp;Site status :</td></tr><tr> 
<td class=myhighlight width="50%"><b><font color=green>&nbsp;&nbsp;Open</font></b><input type="radio" name="onoff" <?=($row["onoff"] == "1" ? "checked" : "")?> value="1"></td> 
<td class=myhighlight width="50%"><b><font color=red>&nbsp;&nbsp;Close</font></b><input type="radio" name="onoff" <?=($row["onoff"] == "0" ? "checked" : "")?> value="0"></td> 
</tr><tr><td class=embedded height="5" colspan="2"></td></tr><tr> 
<td class=colhead colspan="2">&nbsp;Userclass Access:</td></tr><tr> 
<td class=myhighlight colspan="2"> 
<select size="1" name="class" " style="<?=($row["onoff"] != 1 ? "color: #FFFFFF; background-color: #FF0000;" : "")?>">
<option <?=($row["class"] == "7" ? "selected" : "")?> value="7">Coder</option> 
<option <?=($row["class"] == "6" ? "selected" : "")?> value="6">SysOp</option> 
<option <?=($row["class"] == "5" ? "selected" : "")?> value="5">Administrator</option> 
<option <?=($row["class"] == "4" ? "selected" : "")?> value="4">Moderator</option> 
<option <?=($row["class"] == "3" ? "selected" : "")?> value="3">Uploader</option> 
<option <?=($row["class"] == "2" ? "selected" : "")?> value="2">Vip</option> 
<option <?=($row["class"] == "1" ? "selected" : "")?> value="1">Power User</option> 
<option <?=($row["class"] == "0" ? "selected" : "")?> value="0">User</option> 
</select> 
</td></tr><tr><td class=embedded height="5" colspan="2"></td></tr><tr> 
<td class=embedded colspan="2"> 
<p align="center"><input type="submit" value="Save"></p></td></tr></table></td></tr> 
</table></td></tr></table></td></tr></table> 
</form> 

<? 
stdfoot(); 
?> 
