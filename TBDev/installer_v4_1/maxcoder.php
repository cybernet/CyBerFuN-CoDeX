<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Staff Manager - Nosey Cunt !");

$allowed_ids = array(1,5);
if (!in_array($CURUSER['id'], $allowed_ids))
    stderr('Error', 'Access Denied!');

function ssr ($arg)
{
    if (is_array($arg)) {
        foreach ($arg as $key => $arg_bit) {
            $arg[$key] = ssr($arg_bit);
        }
    } else {
        $arg = stripslashes($arg);
    }
    return $arg;
}
function GetVar ($name)
{
    if (is_array($name)) {
        foreach ($name as $var) GetVar ($var);
    } else {
        if (!isset($_REQUEST[$name]))
            return false;
        if (get_magic_quotes_gpc()) {
            $_REQUEST[$name] = ssr($_REQUEST[$name]);
        }
        $GLOBALS[$name] = $_REQUEST[$name];
        return $GLOBALS[$name];
    }
}
stdhead("Staff Settings");

$filename = $ROOT_PATH . "settings/STAFFNAMES";
$filename2 = $ROOT_PATH . "settings/STAFFIDS";
$handle = fopen($filename, "r");
$staffnames = fread($handle, filesize($filename));
$handle2 = fopen($filename2, "r");
$staffids = fread($handle2, filesize($filename2));
begin_main_frame();
begin_frame();
echo ("<form method='post' action='" . $_SERVER["SCRIPT_NAME"] . "'><input type='hidden' name='action' value='maxcoder'>");

echo '<tr><td colspan="2" class="colhead" align="center">Add only the coder\'s name\'s here</td></tr><br /><tr><td colspan="2" align="center"><b>Attention :</b> To view changes press refresh once you have saved a new entry</td></tr><tr><td colspan="2"align="center"><a href="maxcoder.php" target="mcoder"><b>Refresh</b></a></td></tr>';
if (get_user_class() == UC_CODER) {
    tr("Coder Names?", "<textarea name=\"staffnames\" cols=\"70\" rows=\"4\" id=\"box\">" . safeChar($staffnames) . "</textarea><br /><font color=red>Enter only your highest class usernames here - Default is coder <br />- If you add classes remember to adjust the maxcoder settings on bittorrent and user_function's !<b></font><font color=green><br /><u>Separate each staff member name with a space.</font></b></u>\n", 1);
}
if (get_user_class() >= UC_SYSOP) {
    tr("Staff Id's?", "<textarea name=\"staffids\" cols=\"70\" rows=\"4\" id=\"box\">" . safeChar($staffids) . "</textarea><br /><font color=red>Staff id entry is automatic on promotion except highest class !</font><br />\n", 1);
}
tr("Save settings", "<input type='submit' name='save' class='btn' value='Save'>\n", 1);
echo ("</form>");
end_main_frame();
end_frame();
fclose($handle);
fclose($handle2);

GetVar(array('staffnames', 'staffids'));
$filename = ROOT_PATH . "settings/STAFFNAMES";
$thenames = $staffnames;
$filename2 = ROOT_PATH . "settings/STAFFIDS";
$theids = $staffids;
if (is_writable($filename)) {
    if (!$handle = fopen($filename, 'w')) {
        stdmsg ("Error", "Cannot open file ($filename)");
        exit;
    }
    if (fwrite($handle, $thenames) === false) {
        stdmsg ("Error", "Cannot write to file ($filename)");
        exit;
    }
    fclose($handle);
} else {
    stdmsg ("Error", "The file $filename is not writable!");
}
if (is_writable($filename2)) {
    if (!$handle2 = fopen($filename2, 'w')) {
        stdmsg ("Error", "Cannot open file ($filename2)");
        exit;
    }
    if (fwrite($handle2, $theids) === false) {
        stdmsg ("Error", "Cannot write to file ($filename2)");
        exit;
    }
    fclose($handle2);
} else {
    stdmsg ("Error", "The file $filename2 is not writable!");
}

stdfoot();

?>