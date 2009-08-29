<?php
include("include/bittorrent.php");
include("include/class.chmod.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Chmod Manage - Nosey Cunt !");

stdhead('Chmod');
begin_main_frame();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $folderName = (isset($_POST['folder_name']) ? (preg_match('/,/', $_POST['folder_name']) ? explode(',', $_POST['folder_name']) : $_POST['folder_name']) : '');

    if (empty($folderName)) {
        stdmsg('Error...', 'Folder name cannot be empty.');
        end_main_frame();
        stdfoot();
    }

    $POSTs = array('owner_read', 'owner_write', 'owner_execute', 'group_read', 'group_write', 'group_execute', 'public_read', 'public_write', 'public_execute');
    foreach ($POSTs as $POST)
    $$POST = (isset($_POST[$POST]) ? true : false);

    $ownerModes = array($owner_read, $owner_write, $owner_execute);
    $groupModes = array($group_read, $group_write, $group_execute);
    $publicModes = array($public_read, $public_write, $public_execute);

    $chmod = new Chmod($folderName, $ownerModes, $groupModes, $publicModes);

    $result = $chmod->setChmod();

    echo '<table cellpadding="5" align="center" width="100%"><tr><td class="text">';

    function echo_result($result)
    {
        echo ($result[1] ? 'Successfully ' . ($result[0] == 'mkdir' ? 'created folder and ' : '') . 'chmod-ed ' : 'Failed to ' . ($result[0] == 'mkdir' ? 'created folder and ' : '') . 'chmod ') . ($result[0] == 'chmod' ? 'folder ' : '') . 'to ' . $result[2] . ' ' . $_SERVER['DOCUMENT_ROOT'] . '/' . $result[3] . '.<br />';
    }

    if (!is_array($result[0]))
        echo_result($result);
    else
        foreach($result as $n => $result)
        echo_result($result);

    echo '</td></tr></table>';

    ?><br /><?php
}

?><form method="post" action="<?php echo $_SERVER['PHP_SELF'];
?>">
<table cellpadding="5" align="center">
    <tr>
        <td colspan="2" align="center" class="colhead">Chmod</td>
    </tr>

    <tr>
        <td class="rowhead">Folder name</td>
        <td><?php echo $_SERVER['DOCUMENT_ROOT'] . '/';
?><input type="text" name="folder_name" /><br /><font class="small">To chmod multiple folders, separate with a "," no spaces.</font></td>
    </tr>

    <tr>
        <td colspan="2">
            <table cellpadding="5" width="100%">
                <tr align="center">
                    <td class="colhead">Owner</td><td class="colhead">Group</td><td class="colhead">Public</td>
                </tr>
                <tr>
                    <td><input type="checkbox" name="owner_read" /> Read<br />
                        <input type="checkbox" name="owner_write" /> Write<br />
                        <input type="checkbox" name="owner_execute" /> Execute</td>

                    <td><input type="checkbox" name="group_read" /> Read<br />
                        <input type="checkbox" name="group_write" /> Write<br />
                        <input type="checkbox" name="group_execute" /> Execute</td>

                    <td><input type="checkbox" name="public_read" /> Read<br />
                        <input type="checkbox" name="public_write" /> Write<br />
                        <input type="checkbox" name="public_execute" /> Execute</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="2" align="center"><input type="submit" value="Submit" />&nbsp;|&nbsp;<input type="reset" value="Reset" /></td>
    </tr>
</table>
</form><?php

end_main_frame();
stdfoot();

?>