<?php
ob_start("ob_gzhandler");
require_once ("include/bittorrent.php");
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
//optimized, secured, added options, fixed some typos by Alex2005 for TBDEV.NET\\
if (get_user_class() < UC_SYSOP)
hacker_dork("Db Admin - Nosey Cunt !");

$postperpage = 0+$_GET["postperpage"];
$returnto = $_POST["returnto"];

if(isset($_POST["delmp"])) {
$do="DELETE FROM messages WHERE id IN (" . implode(", ", $_POST['delmp']) . ")";
$res=sql_query($do);
if ($returnto) {
header("Location: ".safechar($returnto));
die;
}else{
header ("Refresh: 0; url=/msgspy.php");
stderr("Success" , "The messages where successfully deleted!");
}
}

//===start page===//
stdhead("Administrative message overview"); ?>
<script language="Javascript" type="text/javascript">
<!-- Begin
var checkflag = "false";
var marked_row = new Array;
function check(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
}else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
}
}
// End -->
</script>
<?
//===START*PAGER===//
$res2 = sql_query("SELECT COUNT(*) FROM messages $where");
$row = mysql_fetch_array($res2);
$count = $row[0];
if ($postperpage != 0)
$perpage = $postperpage;
else
$perpage = 10;
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER["PHP_SELF"] ."?postperpage=$perpage&" );
echo $pagertop;
//===END*PAGER===//
//===Message*amount*selector===//

//query optimized by Alex2005 for TBDEV.NET\\
$res = sql_query("SELECT msg.receiver, msg.subject, msg.sender, msg.unread, msg.msg, msg.added, msg.id, u1.username AS u1_username, u2.username AS u2_username FROM messages AS msg LEFT JOIN users AS u1 ON u1.id=msg.receiver LEFT JOIN users AS u2 ON u2.id=msg.sender ORDER BY msg.id DESC $limit") or sqlerr(__FILE__, __LINE__);

begin_main_frame("Administrative message overview");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
//===select how many
print("<tr>");
print("<form method=\"GET\" action=\"/msgspy.php?\">");
//print("<input type=\"hidden\" name=\"postperpage\" value=\"postperpage\">\n");
print("<td class=\"colhead\" colspan=\"3\" align=\"left\"><b>How many messages to show per site: </b><select name=\"postperpage\">\n");
print("<option value=$postperpage>$postperpage</option>\n");
print("<option value=10>10</option>\n"); //==Set*to*value*you*need===//
print("<option value=25>25</option>\n"); //==Set*to*value*you*need===//
print("<option value=50>50</option>\n"); //==Set*to*value*you*need===//
print("<option value=150>150</option>\n"); //==Set*to*value*you*need===//
print("<option value=300>300</option>\n"); //==Set*to*value*you*need===//
print("<option value=500>500</option>\n"); //==Set*to*value*you*need===//
print("<option value=1000>1000</option>\n"); //==Set*to*value*you*need===//
print("<option value=1500>1500</option>\n"); //==Set*to*value*you*need===//
print("<input type=\"submit\" value=\"Set to it!\" /></td><td class=\"colhead\" colspan=\"2\">Times are in GMT.</td></form></tr>");
print("<form method=\"post\" name=\"form\" action=\"/msgspy.php\">");
print("<input type=\"hidden\" name=\"returnto\" value=\"msgspy.php?postperpage=$perpage&page=\">\n");
print("<tr><td class=\"colhead\" align=\"left\" width='1%'>Info</td>".
"<td class=\"colhead\" align='left' width='1%'>Subject</td>".
"<td class=\"colhead\" align=\"left\">Text</td><td class=\"colhead\" align=\"left\" width='1%'>Date</td><td class=\"colhead\" width='1%'>Del</td></tr>\n");
while ($arr = mysql_fetch_assoc($res))
{
$receiver = "<a href=userdetails.php?id=" . $arr["receiver"] . "><b>" . $arr["u1_username"] . "</b></a>";
if($arr["sender"] != 0)
$sender = "<a href=userdetails.php?id=" . $arr["sender"] . "><b>" . $arr["u2_username"] . "</b></a>";
else
$sender = "<font color=red><b>System</b></font>";
$msg = format_comment($arr["msg"]);

//if you have timezone mod, uncomment this code an comment the next one
//$added = display_date_time($arr["added"]);
$added = $arr["added"];

print("<tr><td align=\"left\"><b>Sender:</b>&nbsp;&nbsp;&nbsp;&nbsp;$sender<br><b>Reciever:</b>&nbsp;$receiver<br><b>Read</b>&nbsp;&nbsp;&nbsp;&nbsp;".($arr["unread"] != "yes" ? "<b><font color=lightgreen>Yes</font></b>" : "<b><font color=red>No</font></b>")."</td>".
"<td align=left>".format_comment($arr['subject'])."</td>".
"<td align=left>$msg</td><td align=left>$added</td><td align=center><input type=\"checkbox\" name=\"delmp[]\" title=\"Mark\" value=\"" . $arr['id'] . "\" /></td></tr>\n");
}
print("<td colspan=\"4\" align=\"right\" class=\"colhead\">Mark&nbsp;all&nbsp;Messages </td><td width=\"2%\" class=\"colhead\">");
?>
<input type=checkbox title='Mark All' value='Mark All' onClick="this.value=check(document.form.elements);">
<?
print("</td></tr>");
print("<tr><td colspan=\"5\" align=\"center\"><input type=\"submit\" value=\"Delete selected messages!\" /></td></tr></form>");
end_main_frame();
print($pagerbottom);
print("</table>");
stdfoot();
//===end page//
?>