<?php
require "include/bittorrent.php";
require_once "include/user_functions.php";
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
parked();

if ($usergroups['canpm'] == 'no' OR $usergroups['canpm'] != 'yes' OR $CURUSER['sendpmpos'] == 'no') {
stderr("Sorry...", "You are not authorized to send any Private Messages - Please contact site Admin.)");
}

// === functions
// ===set MAX message amount for users... in out and other... and...
// === possibly merge this function with the one below and return an array :P
function maxbox($class)
{
    switch ($class) {
        case UC_CODER:
            $maxbox = 500;
            break;
        case UC_SYSOP:
            $maxbox = 400;
            break;
        case UC_ADMINISTRATOR:
            $maxbox = 100;
            break;
        case UC_MODERATOR:
            $maxbox = 50;
            break;
        case UC_UPLOADER:
            $maxbox = 20;
            break;
        case UC_VIP:
            $maxbox = 10;
            break;
        case UC_POWER_USER:
            $maxbox = 10;
            break;
        case UC_USER:
            $maxbox = 5;
            break;
    }
    return $maxbox;
}
// === takesendmessage... delete the other one \o/
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === first the draft stuff... if posting a new draft and not previewing it, enter it into DB :D
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_POST['draft'] == 'yes' && $_POST['buttonval'] == 'Save') {
    // === make sure they wrote something :P
    if (!$_POST['subject']) stderr('Error!', 'To save a message in your draft folder, it must have a subject!');
    if (!$_POST['body']) stderr('Error!', 'To save a message in your draft folder, it must have body text!');

    $body = sqlesc($_POST['body']);
    $subject = sqlesc(htmlspecialchars($_POST['subject']));
    $draft = sqlesc(($_POST['draft'] === 'yes' ? 'yes' : 'no'));

    sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, location, draft, unread, saved) VALUES(' . $CURUSER['id'] . ', ' . $CURUSER['id'] . ',' . sqlesc(get_date_time()) . ', ' . $body . ', ' . $subject . ', 0, ' . $draft . ',"no","yes")') or sqlerr(__FILE__, __LINE__);
    // === Check if messages was saved as draft
    if (mysql_affected_rows() === 0)
        stderr('Error', 'Message wasn\'t saved!');
    header('Location: /messages.php?action=viewdrafts&new_draft=1');
    die();
} //=== end save draft
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === now the basic takesendmessage scripts
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === check to see if it's a preview or a post
if (isset($_POST['buttonval']) && $_POST['buttonval'] == 'Send') {
    if ($CURUSER['pm_max'] == 0) stderr("System Message", "You PM rights have been revoked.");
    // Anti Flood Code
    // This code restricts PM sending to a set limit
    if (!($CURUSER['pm_count'] < $CURUSER['pm_max']))
        stderr('Notice', 'You have reached your PM limit. Please wait 15 minutes before retrying.');
    // === check to see they have everything or...
    $receiver = sqlesc(0 + $_POST['receiver']);
    $subject = sqlesc(htmlspecialchars($_POST['subject']));
    $body = sqlesc(trim($_POST['body']));
    $save = sqlesc(($_POST['save'] === 1 ? 'yes' : 'no'));
    $urgent = sqlesc((($_POST['urgent'] === 'yes' && $CURUSER['class'] >= UC_MODERATOR) ? 'yes' : 'no'));
    $returnto = htmlspecialchars($_POST['returnto']);
    // === get user info from DB
    $res_receiver = sql_query('SELECT acceptpms, notifs, email, class, username FROM users WHERE id=' . $receiver) or sqlerr(__FILE__, __LINE__);
    $arr_receiver = mysql_fetch_assoc($res_receiver);
    // === errors for missing / bad input
    if (!is_valid_id(0 + $_POST['receiver']) || !$arr_receiver) stderr('Error', 'Member not found!!!');
    // if ($_SERVER['REQUEST_METHOD'] != 'POST') stderr('Error', 'Method');
    if (!$_POST['body']) stderr('Error', 'No body text... Please enter something to send!');
    // === simple stop XSS
    if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) == false) stderr('Error', 'So, thou common dog, didst thou disgorge thy glutton bosom?');
    /*
//=== allow suspended users to PM / forward to staff only
	if ($CURUSER['suspended'] === 'yes')
	{
	$res = sql_query('SELECT id FROM users WHERE class >='.UC_MODERATOR) or sqlerr(__FILE__, __LINE__);
        $row = mysql_fetch_assoc($res);
        if (!in_array(0 + $_POST['receiver'], $row)) stderr('Error', 'Your account is suspended, you may only forward PMs to staff!');
	}
*/
  
    // === make sure they have space
    $res_count = sql_query('SELECT COUNT(*) FROM messages WHERE receiver = ' . $receiver . ' AND location = 1') or sqlerr(__FILE__, __LINE__);
    $arr_count = mysql_fetch_row($res_count);

    if ($arr_count[0] >= maxbox($arr_receiver['class']) && $CURUSER['class'] < UC_MODERATOR) stderr('Sorry', 'Members PM box is full.');
    // This code restricts PM sending to a set limit
    if (!($CURUSER['pm_count'] < $CURUSER['pm_max']))
        stderr('Notice', 'You have reached your PM limit. Please wait 15 minutes before retrying.');
    // === Make sure recipient wants this message
    if (get_user_class() < UC_MODERATOR) {
        switch (true) {
            case($arr_receiver['acceptpms'] == 'yes'):
                $res2 = sql_query('SELECT * FROM blocks WHERE userid=' . $receiver . ' AND blockid=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
                if (mysql_num_rows($res2) == 1) stderr('Refused', $arr_receiver['username'] . ' has blocked PMs from you.');
                break;
            case($arr_receiver['acceptpms'] == 'friends'):
                $res2 = sql_query('SELECT * FROM friends WHERE userid=' . $receiver . ' AND friendid=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
                if (mysql_num_rows($res2) != 1) stderr('Refused', $arr_receiver['username'] . ' only accepts PMs from members in their friends list.');
                break;
            case($arr_receiver['acceptpms'] == 'no'):
                stderr('Refused', $arr_receiver['username'] . ' does not accept PMs.');
                break;
        } //===end if acceps PMs
    } //=== end if staff
    // === ok all is well... post the message :D
    sql_query('INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location,urgent) VALUES(' . $CURUSER["id"] . ', ' . $CURUSER["id"] . ', ' . $receiver . ', ' . sqlesc(get_date_time()) . ', ' . $body . ', ' . $subject . ', ' . $save . ', 1,' . $urgent . ')') or sqlerr(__FILE__, __LINE__);
    // Update Last PM sent...
    sql_query("UPDATE users SET pm_count = pm_count + 1 WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    // === make sure it worked then...
    if (mysql_affected_rows() === 0)
        stderr('Error', 'Messages wasn\'t sent!');
    // === if they just have to know about it right away... send them an email (if selected if profile)
    if (strpos($arr_receiver['notifs'], '[pm]') !== false) {
        $username = $CURUSER['username'];
        $body = <<<EOD
You have received a PM from $username!

You can use the URL below to view the message (you may have to login).

$DEFAULTBASEURL/messages.php?action=viewmailbox

--
$SITENAME
EOD;
        @mail($user['email'], "You have received a PM from " . $username . "!",
            $body, "From: $SITEEMAIL", "-f$SITEEMAIL");
    }
    // === if returnto sent
    if ($returnto)
        header('Location: ' . $returnto);
    else
        header('Location: messages.php?action=viewmailbox&sent=1');
    die();
} //=== end of takesendmessage script
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === now the basic page for sendmessage
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === get info if any...
$receiver = ($_GET['receiver'] ? (0 + $_GET['receiver']) : (0 + $_POST['receiver']));
$replyto = ($_GET['replyto'] ? (0 + $_GET['replyto']) : (0 + $_POST['replyto']));
$returnto = htmlspecialchars($_POST['returnto']);
// === errors if bad data sent
if ($receiver == 0) stderr('Error', 'you can\'t PM the Sys-Bot... It won\'t write you back!');
if (!is_valid_id($receiver) || $replyto && !is_valid_id($replyto)) stderr('Error', 'No member with that ID!');
// === make sure there is a member to send this to
$res_member = sql_query('SELECT username FROM users WHERE id=' . ($receiver ? sqlesc($receiver) : ($replyto ? sqlesc($replyto) : ''))) or sqlerr(__FILE__, __LINE__);
$arr_member = mysql_fetch_assoc($res_member);
if (!$arr_member) stderr('Error', 'No member with that ID!');
// === if reply
if ($replyto) {
    // === make sure they should be replying to this PM...
    $res_old_message = sql_query('SELECT receiver,sender,subject,msg FROM messages WHERE id=' . sqlesc($replyto)) or sqlerr(__FILE__, __LINE__);
    $arr_old_message = mysql_fetch_assoc($res_old_message);
    if ($arr_old_message['receiver'] != $CURUSER['id']) stderr('Error', 'Slander, whose edge is sharper than the sword, whose tongue out venoms all the worms of Nile');

    $body .= "\n\n\n-------- $arr_member[username] wrote: --------\n$arr_old_message[msg]\n";
    $subject = 'Re: ' . htmlspecialchars($arr_old_message['subject']);
}
// === if preview or not replying
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
}
// === and finally print the basic page  :D
stdhead((($_POST['draft'] == 'yes' || $_GET['draft'] == 'yes') ? 'write draft' : 'Send message'), false);


echo'<h1>Message to <a class=altlink href=userdetails.php?id=' . ($receiver > 0 ? $receiver : $replyto) . '>' . $arr_member['username'] . '</a></h1>' . '<table border=0 cellspacing=5 cellpadding=5 width=600><tr><td class=colhead>Send message</td></tr><form name=compose method=post action=?>' . '<tr><td colspan="2" class=clearalt6><b>Subject:&nbsp;&nbsp;</b><input name="subject" type="text" size="120" value="' . $subject . '"></td></tr><tr><td colspan=2 class=clearalt6>';
textbbcode('compose', 'body', $body);
echo'</td></tr><tr><td align=center class=clearalt6>' . ($CURUSER['class'] >= UC_MODERATOR? '<b><font color=red>Mark as URGENT!</font></b> ' . '<input type=checkbox name=urgent value=yes ' . (($_POST['urgent'] && $_POST['urgent'] == 'yes') ? ' checked' : '') . '>&nbsp;' : '') . ' save as Draft? ' . '<input type=checkbox name=draft value=yes ' . (($_POST['draft'] == 'yes' || $_GET['draft'] == 'yes' || $_GET['draft'] == 1) ? ' checked' : '') . '>&nbsp;' . ($replyto ? 'Delete message you are replying to? ' . '<input type=checkbox name=delete value=yes ' . ($CURUSER['deletepms'] == 'yes' ? ' checked' : '') . '><input type=hidden name=origmsg value=' . $replyto . '>' : '') . 'Save message to Sentbox? <input type=checkbox name=save value=1' . ($CURUSER['savepms'] == 'yes' ? ' checked' : '') . '></td></tr>' . '<tr><td' . ($replyto ? ' colspan=2' : '') . ' align=center class=clearalt6> ' . '<input type=submit name=buttonval value=' . (($_POST['draft'] == 'yes' || $_GET['draft'] == 'yes' || $_GET['draft'] == 1) ? 'Save' : 'Send') . ' class=button><input type="button" class="button" value="Spell Check" onclick="openspell();"></td></tr></table>' . '<input type=hidden name=receiver value=' . ($receiver > 0 ? $receiver : $replyto) . '></form><br><br><br>';

stdfoot();

?>