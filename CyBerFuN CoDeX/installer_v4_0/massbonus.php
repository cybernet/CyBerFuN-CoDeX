<?php
require_once('include/bittorrent.php');
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
hacker_dork("Mass-Bonus - Nosey Cunt !");

if ($_POST['doit'] == 'yes') {
sql_query("UPDATE users SET seedbonus = seedbonus + 500");
header("Location: /staff.php");
}

stdhead('Mass Bonus');
?>

<h2>Give all users 500 seedbonus points ?</h2>
<font size=1>Are you sure you want to give all users 500 extra seedbonus points?</font><br /><br />

<form action="massbonus.php" method="post">
<table border=1 cellspacing=0 cellpadding=5><tr><td class="rowhead">
<input type = "hidden" name = "doit" value = "yes" />
<input type="submit" value="Yes" />
</td></tr></table>
</form>

<? stdfoot(); ?>