<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
dbconn(false);
stdhead("Request to add TempIP Gateway");
?>
<center>
<font color=red><b>A T T E N T I O N !</b></font><br>
 Login is CASE sensitive. <u>N</u>ame is not the same as <u>n</u>ame.  <br><b>Every</b> login attempt is monitored by staff (Every IP that visits this page is monitored).<br> failed attempts are alerted immediately to staff through various methods.<br> The password used here is not the same as your login password.<p>  </center>
<form method="post" action="takesecureip.php"><table cellspacing=0 cellpadding=3 border=0><tr><td>
<table border="0" cellspacing="2" cellpadding=5>
<tr><td><b>Staff Name:</b></td><td align=left><input type="text" size=40 name="staffname" /></td></tr>
<tr><td><b>Special Password:</b></td><td align=left><input type="password" size=40 name="secrettop" /></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Log in!" class=btn></td></tr>
</font></td></tr></table></td></tr>
</table></td></tr></table>
