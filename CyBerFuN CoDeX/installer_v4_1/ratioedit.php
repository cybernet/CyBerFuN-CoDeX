<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
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
    hacker_dork("Ratio Edit - Nosey Cunt !");

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") {
    if ($HTTP_POST_VARS["username"] == "" || $HTTP_POST_VARS["uploaded"] == "" || $HTTP_POST_VARS["downloaded"] == "")
        stderr("Error", "Missing form data.");
    $username = unsafeChar($HTTP_POST_VARS["username"]);
    $uploaded = unsafeChar($HTTP_POST_VARS["uploaded"]);
    $downloaded = unsafeChar($HTTP_POST_VARS["downloaded"]);

    sql_query("UPDATE users SET uploaded=$uploaded, downloaded=$downloaded WHERE username=$username") or sqlerr(__FILE__, __LINE__);
    write_log("Ratio edited", "$username had their ratio adjusted by $CURUSER[username] to $uploaded bytes uploaded and $downloaded bytes downloaded.");
    $res = sql_query("SELECT id FROM users WHERE username=$username");
    $arr = mysql_fetch_row($res);
    if (!$arr)
        stderr("Error", "Unable to update account.");
    header("Location: $BASEURL/userdetails.php?id=$arr[0]");
    die;
}
stdhead("Ratio Edit");

?>
<h1><?=$SITENAME?> Ratio Edit</h1>
<form method=post action=ratioedit.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead>User name</td><td><input type=text name=username size=40></td></tr>
<tr><td class=rowhead>Amount Uploaded</td><td><input type=uploaded name=uploaded size=40></td></tr>
<tr><td class=rowhead>Amount Download</td><td><input type=downloaded name=downloaded size=40></td></tr>
<tr><td colspan=2 align=center><input type=submit value="Okay" class=btn></td></tr>
</table>
</form>
<?php
stdfoot();

?>