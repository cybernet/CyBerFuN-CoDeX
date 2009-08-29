<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
// The following line may need to be changed to UC_MODERATOR if you don't have Forum Moderators
if ($CURUSER['class'] < UC_MODERATOR) die(); // No acces to below this rank
if ($CURUSER['override_class'] != 255) die(); // No access to an overridden user class either - just in case

if ($_GET['action'] == 'editclass') { // Process the querystring - No security checks are done as a temporary class higher
        // than the actual class mean absoluetly nothing.
        $newclass = 0 + $_GET['class'];

    mysql_query("UPDATE users SET override_class = " . sqlesc($newclass) . " WHERE id = " . $CURUSER['id']); // Set temporary class

    header("Location: " . $DEFAULTBASEURL . "/userdetails.php?id=" . $CURUSER['id']);
    die();
}
// HTML Code to allow changes to current class
stdhead("Set override class for " . $CURUSER["username"]);

?>
<br>
<font size=4><b>Allows you to change your user class on the fly.</b></font>
<br>
<form method=get action='setclass.php'>
    <input type=hidden name='action' value='editclass'>

    <table width=150 border=2 cellspacing=5 cellpadding=5>
        <tr><td>Class</td><td align=left><select name=class>
        <?php $maxclass = get_user_class() - 1;
for ($i = 0; $i <= $maxclass; ++$i)
if (trim(get_user_class_name($i)) != "") print("<option value=$i" . ">" . get_user_class_name($i) . "\n");

?>
        </select></td></tr>
        </td></tr>
        <tr><td colspan=3 align=center><input type=submit class=btn value='Okay'></td></tr>
    </table>
</form>
<br>

<?php
stdfoot();

?>