<?php
// *****************************************************************
// Version: 1.1
// *****************************************************************

// Filename: client_clearban.php
// Parent:   peers.php
// Requires: functions.php
// Author:   Petr1fied
// Date:     2007-06-17
// Updated:  2007-07-01

// Usage:
// - Removes bans on BitTorrent Clients.

// ####### HISTORY ################################################

// 1.0 2007-06-17 - Petr1fied - Intital development.
// 1.1 2007-07-01 - Petr1fied - Ported to TBDev and changed to be
// SQL free.

// *****************************************************************
require_once ("include/bittorrent.php");
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
stdhead('Clear Client Ban');

if (get_user_class() < UC_ADMINISTRATOR) {
    stdmsg(ERROR, "You're not authorised to view this page");
    stdfoot();
    exit;
} else {
    $filename = "include/banned_clients.txt";
    if (filesize($filename) == 0 || !file_exists($filename))
        $banned_clients = array();
    else {
        $handle = fopen($filename, "r");
        $banned_clients = unserialize(fread($handle, filesize($filename)));
        fclose($handle);
    }
    (isset($_GET["id"]) ? $id = intval($_GET["id"]) : $id = "");
    (isset($_GET["returnto"]) ? $url = urldecode($_GET["returnto"]) : $url = $_SERVER["PHP_SELF"]);
    (isset($_POST["confirm"]) ? $confirm = $_POST["confirm"] : $confirm = "");

    if ($id == "" && !empty($banned_clients)) {

        ?>
        <p align='center'><font size=3><strong><u>Currently banned clients</u></strong></font></p>
        <table align='center' width=70%>
          <tr>
            <td class='header' align='center'><strong>Client</strong></td>
            <td class='header' align='center'><strong>User Agent</strong></td>
            <td class='header' align='center'><strong>peer_id</strong></td>
            <td class='header' align='center'><strong>peer_id ascii</strong></td>
            <td class='header' align='center'><strong>Ban Reason</strong></td>
            <td class='header' align='center'><strong>Remove Ban?</strong></td>
          </tr>
          <?php
        foreach($banned_clients as $k => $v) {

            ?>
              <tr>
                <td class='lista' align='center'><?=$v["client_name"]?></td>
                <td class='lista' align='center'><?=$v["user_agent"]?></td>
                <td class='lista' align='center'><?=$v["peer_id"]?></td>
                <td class='lista' align='center'><?=$v["peer_id_ascii"]?></td>
                <td class='lista' align='center'><?=stripslashes($v["reason"])?></td>
                <td class='lista' align='center'><a href='client_clearban.php?id=<?=$k?>&amp;returnto=<?=urlencode($url)?>'><img src='pic/smilies/thumbsup.gif' border='0' alt='Remove Ban?'></a></td>
              </tr>
              <?php
        }

        ?>
          <tr>
            <td class='block'colspan='6'><strong>&nbsp;</strong></td>
          </tr>
        </table>
        <?php
        stdfoot();
        exit();
    }

    if ($_POST["confirm"]) {
        if ($confirm == "Yes") {
            unset($banned_clients[$id]);
            $data = serialize($banned_clients);

            $fd = fopen($filename, "w") or die("Can't update $filename, please CHMOD it to 777");
            fwrite($fd, $data) or die("Can't save file");
            fclose($fd);

            stdmsg("Success", "This client has been removed from the banned list");
            print("<center><a href='$url'>Return</a></center>");
            stdfoot();
            exit();
        } else
            redirect($url);
    }
    $row = $banned_clients[$id];
    if ($row) {

        ?>
        <p align='center'>By visiting this page you are indicating that you wish to
        remove the ban on the following client:</p>
        <form method='post' name='action'>
        <table align='center' width=70%>
          <tr>
            <td class='header' align='center'><strong>Client</strong></td>
            <td class='header' align='center'><strong>User Agent</strong></td>
            <td class='header' align='center'><strong>peer_id</strong></td>
            <td class='header' align='center'><strong>peer_id ascii</strong></td>
            <td class='header' align='center'><strong>Ban Reason</strong></td>
          </tr>
          <tr>
            <td class='lista' align='center'><?=$row["client_name"]?></td>
            <td class='lista' align='center'><?=$row["user_agent"]?></td>
            <td class='lista' align='center'><?=$row["peer_id"]?></td>
            <td class='lista' align='center'><?=$row["peer_id_ascii"]?></td>
            <td class='lista' align='center'><?=stripslashes($row["reason"])?></td>
          </tr>
          <tr>
            <td class='block'colspan='5'><strong>&nbsp;</strong></td>
          </tr>
        </table>
        <p align='center'>Are you sure you want to do this? (you will receive no further confirmation).</p>
        <center>
        <input type='submit' name='confirm' value='Yes'>&nbsp;<input type='submit' name='confirm' value='No'>
        <center></form><br />
        <?php
    } else {
        stdmsg("Error", "No clients are banned at the moment");
        print("<center><a href='$url'>Return</a></center>");
        stdfoot();
        exit();
    }
}
stdfoot();

?>