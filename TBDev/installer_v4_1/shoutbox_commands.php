<?php
require "include/bittorrent.php";
require "include/bbcode_functions.php";
require "include/user_functions.php";
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if ( get_user_class() < UC_MODERATOR )
    hacker_dork( "ShoutBox Commands - Nosey Cunt !" );

?>
<html><head>
<title>Staff ShoutBox Commands</title>
<link rel="stylesheet" href="themes/green/green.css" type="text/css">
<script type="text/javascript">
function command(command,form,text){
window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+command+" ";
window.opener.document.forms[form].elements[text].focus();
window.close();
}
</script>
</head>

<br><br><center><table cellpadding="10">
<tr>
	<td class="shoutcomm" colspan="10" align="center"><strong><font color=#FF0000>ShoutBox Commands - Click on the command box to use them :)</a></font></strong></td>
	</tr>
    <div id="shoutcomm">
	<tr>
	<td align="center"><b>EMPTY deletes all your text within the shoutbox with no chance of recover. Use it like /EMPTY [username here without the brackets]</b></td></td>
	<td align="center"><b>GAG disables the members chatbox rights - Use it like /GAG [username here without the brackets]</b></td>
	<td align="center"><b>UNGAG enables the members chatbox rights - Use it like /UNGAG [username here without the brackets]</b></td>
	<td align="center"><b>DISABLE will disable the members account - Use it like /DISABLE [username here without the brackets]</b></td>
	<td align="center"><b>ENABLE will enable the members account - Use it like /ENABLE [username here without the brackets]</b></td>
	</tr>
	<tr>
	<td align="center"><b><input type="text" size="20" value="/EMPTY" onclick="command('/EMPTY','shbox','shbox_text')"></b></td>
	<td align="center"><b><input type="text" size="20" value="/GAG" onclick="command('/GAG','shbox','shbox_text')"></b></td>
	<td align="center"><b><input type="text" size="20" value="/UNGAG" onclick="command('/UNGAG','shbox','shbox_text')"></b></td>
	<td align="center"><b><input type="text" size="20" value="/DISABLE" onclick="command('/DISABLE','shbox','shbox_text')"></b></td>
	<td align="center"><b><input type="text" size="20" value="/ENABLE" onclick="command('/ENABLE','shbox','shbox_text')"></b></td>
	</tr>
	<tr>
	<td align="center"><b>WARN will give a user a warning for not following the rules of the shoutbox. Use it like /WARN [username here without the brackets]</b></td></td>
	<td align="center"><b>UNWARN will remove a user  warning. Use it like /UNWARN [username here without the brackets]</b></td>
	</tr>
	<tr>
	<td align="center"><b><input type="text" size="20" value="/WARN" onclick="command('/WARN','shbox','shbox_text')"></b></td>
	<td align="center"><b><input type="text" size="20" value="/UNWARN" onclick="command('/UNWARN','shbox','shbox_text')"></b></td>
	</tr>
	<tr>
	[ <a href="javascript:window.close();">Close This Window</a> ]
	</tr>
	</div>
	</table><br />
<?php
die();

?>