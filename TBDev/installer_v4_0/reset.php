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
// Reset Lost Password ACTION
if ($CURUSER['class'] < UC_MODERATOR)
    stderr('Error', 'Permission denied, you\'re not a member of staff.');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $secret = mksecret();
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newpassword = "";
    for($i = 0;$i < 10;$i++)
    $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];
    $passhash = md5($secret . $newpassword . $secret);
    $res = mysql_query('UPDATE users SET secret=' . sqlesc($secret) . ', passhash=' . sqlesc($passhash) . ' WHERE username=' . sqlesc($username) . ' AND class<' . $CURUSER['class']) or sqlerr();
    if (mysql_affected_rows() != 1)
        stderr('Error', 'Password not updated. User not found or higher/equal class to yourself');
    write_log('passwordreset', 'Password reset for ' . $username . ' by ' . $CURUSER['username']);
    stderr('Success', 'The password for account <b>' . $username . '</b> is now <b>' . $newpassword . '</b>.');
}
stdhead("Reset User's Lost Password");

?>
<h1>Reset User's Lost Password</h1>
<table border=1 cellspacing=0 cellpadding=5>
<form method=post action=reset.php>
<tr><td class="rowhead">User name</td><td><input size=40 name=username></td></tr>

<tr><td colspan=2><input type=submit class=btn value='reset'></td></tr>
</form>
</table>
<?php
stdfoot();

?>