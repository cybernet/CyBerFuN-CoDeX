<?php
require ("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
////////////////////////////////
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();

if (get_user_class() < UC_ADMINISTRATOR)
hacker_dork("FreeLeech - Nosey Cunt !");

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST")
{
$countstats = sqlesc($HTTP_POST_VARS["countstats"]);
$size = sqlesc($HTTP_POST_VARS["size"]);

$resfree = sql_query("UPDATE torrents SET countstats = $countstats WHERE size >= $size" ) or sqlerr(__FILE__, __LINE__);

if (!$resfree)
stderr("Error - wtf.. you cant post nothing dude !", "");
else
header("Refresh: 2; url=".$_SERVER["PHP_SELF"]);
stderr("Success !", "");
}

stdhead("Set torrents status");

?>
<form method=post action=free.php>

<table cellspacing=0 cellpadding=5>
<tr>
<td><b><center>Set FreeLeech Status<br></center></b><br>
<table style="border: 0" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="border: 0">Free Leech</td>
<td style="border: 0" width="20"><input type="radio" name="countstats" value="no">
</td></tr><tr>
<td style="border: 0">Not Free Leech</td>
<td style="border: 0" width="20"><input type="radio" name="countstats" value="yes">
</td></tr><tr><br>
<td style="border: 0"><br><br>Select !<br><br></td>
<td style="border: 0" width="20">
</td></tr><tr>
<td style="border: 0">All Torrents</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="1">
</td></tr><tr>
<td style="border: 0">Bigger than 1 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="1073741824">
</td></tr><tr>
<td style="border: 0">Bigger then 2 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="2147483648">
</td></tr><tr>
<td style="border: 0">Bigger than 3 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="3221225472">
</td></tr><tr>
<td style="border: 0">Bigger than 4 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="4294967296">
</td></tr><tr>
<td style="border: 0">Bigger than 5 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="5368709120">
</td></tr><tr>
<td style="border: 0">Bigger than 6 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="6442450944">
</td></tr><tr>
<td style="border: 0">Bigger than 7 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="7516192768">
</td></tr><tr>
<td style="border: 0">Bigger than 8 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="8589934592">
</td></tr><tr>
<td style="border: 0">Bigger than 9 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="9663676416">
</td></tr><tr>
<td style="border: 0">Bigger than 10 GB</td>
<td style="border: 0" width="20"><input type="radio" name="size" value="10737418240">
</td></tr><tr>
</td></tr><tr>
</table>
</td>
<tr><td colspan=2 align=center><input type=submit value="Submit" class=btn></td></tr>
</form>
</table>

<?php 
stdfoot(); 
?>