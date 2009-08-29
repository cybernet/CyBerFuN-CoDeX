<?php
/**
* PM system based on the wonderful work of Tux... but finally I found it was so re-written and different I
* thought it should have it's own' little section...
* added:
* search, drafts, avatars, max boxes, max messages, urgent (for staff), security, and much more lol...
*
* all credit and kudos to Tux for coming up with the great idea of this PM system,
* and credit to the various coders at TBDev who's code I may have pinched,
* as well as my mom, who without her, I'd not be typing this...
*
* btw... sendmessage.php was a mess... so I re-did it to work with this code...
* and takesendmessage.php should just be deleted... no need for it...
*
* cheers,
* snuggs
*/
ob_start('ob_gzhandler');
require_once('include/bittorrent.php');
require_once('include/bbcode_functions.php');
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
// Define constants
define('PM_DELETED', 0); // Message was deleted
define('PM_INBOX', 1); // Message located in Inbox for reciever
define('PM_SENTBOX', -1); // GET value for sent box
// === functions
// ===set MAX message amount for users... in out and other... and...
// === possibly merge this function with the one below and return an array :P
function maxbox($class)
{
    switch ($class) {
        case UC_CODER:
            $maxbox = 400;
            break;
        case UC_SYSOP:
            $maxbox = 300;
            break;
        case UC_ADMINISTRATOR:
            $maxbox = 200;
            break;
        case UC_MODERATOR:
            $maxbox = 200;
            break;
        case UC_UPLOADER:
            $maxbox = 200;
            break;
        case UC_VIP:
            $maxbox = 100;
            break;
        case UC_POWER_USER:
            $maxbox = 100;
            break;
        case UC_USER:
            $maxbox = 50;
            break;
    }
    return $maxbox;
}
// ===set MAX amount of custom boxes for users... (not including inbox, sentbox) AND amount of drafts a member can save...
function maxboxes($class)
{
    switch ($class) {
        case UC_CODER:
            $maxboxes = 50;
            break;
        case UC_SYSOP:
            $maxboxes = 40;
            break;
        case UC_ADMINISTRATOR:
            $maxboxes = 30;
            break;
        case UC_MODERATOR:
            $maxboxes = 20;
            break;
        case UC_UPLOADER:
            $maxboxes = 20;
            break;
        case UC_VIP:
            $maxboxes = 8;
            break;
        case UC_POWER_USER:
            $maxboxes = 6;
            break;
        case UC_USER:
            $maxboxes = 5;
            break;
    }
    return $maxboxes;
}
// === safe input only (this is just the valid username function from signup... you could just add it to global and use it for both...
function safe_box_name($box_name)
{
    // == only safe characters are allowed in PM box names
    $allowedchars = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';

    for ($i = 0; $i < strlen($box_name); ++$i)

    if (strpos($allowedchars, $box_name[$i]) === false)

        return false;

    return true;
}
// === get all PM boxes
function get_all_boxes()
{
    global $CURUSER;
    $res = sql_query('SELECT boxnumber,name FROM pmboxes WHERE userid=' . $CURUSER['id'] . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);
    $get_all_boxes .= '<select name=box><option value=1>Inbox</option><option value="-1">Sentbox</option>';
    while ($row = mysql_fetch_assoc($res))
    $get_all_boxes .= '<option value=' . $row['boxnumber'] . '>' . htmlspecialchars($row['name']) . '</option>';
    $get_all_boxes .= '</select>';
    return $get_all_boxes;
}
// === insert jump to box
function insertJumpTo($selected = 0)
{
    global $CURUSER;
    $insertJumpTo .= '<form action=? method=get><input type=hidden name=action value=viewmailbox><b>Jump to:</b> <select name=box ONCHANGE="location = this.options[this.selectedIndex].value;"><option value=?action=viewmailbox&box=1 ' . ($mailbox_name == 'Inbox' ? 'selected=selected' : '') . '>Inbox</option><option value=?action=viewmailbox&box=-1 ' . ($mailbox_name == 'Sentbox' ? 'selected=selected' : '') . '>Sentbox</option>';
    $res = sql_query('SELECT boxnumber,name FROM pmboxes WHERE userid=' . $CURUSER['id'] . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_assoc($res))
    $insertJumpTo .= '<option value=?action=viewmailbox&box=' . $row['boxnumber'] . ' ' . ($row['boxnumber'] == (0 + $_GET['box']) ? 'selected=selected' : '') . '>' . $row['name'] . '</option>';
    $insertJumpTo .= '<option value=?action=viewdrafts ' . ($mailbox_name == 'Drafts' ? 'selected=selected' : '') . '>Drafts</option></select></form>';
    return $insertJumpTo;
}
// === add some javascript to make mass deleting / moving fun and painless :P
?>
<script language = "Javascript">
<!--

var form='messages'

function SetChecked(val,chkName) {
dml=document.forms[form];
len = dml.elements.length;
var i=0;
for( i=0 ; i<len ; i++) {
if (dml.elements[i].name==chkName) {
dml.elements[i].checked=val;
}
}
}

// -->
</script>
<?php
// === change number of PMs per page on the fly
if ($_GET['change_pm_number']) {
    $num_of_pms_per_page_ssti = 0 + $_GET['change_pm_number'];
    if (!is_valid_id($num_of_pms_per_page_ssti)) stderr('Error', 'Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.');
    sql_query("UPDATE users SET pms_per_page = " . sqlesc($num_of_pms_per_page_ssti) . " WHERE id = " . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
    if ($_GET['edit_mail_boxes'])
        header('Location: ?action=editmailboxes&pm=1');
    else
        header('Location: ?action=viewmailbox&pm=1&box=' . (0 + $_GET['box']));
    die();
}
// === show small avatar drop down thingie / change on the fly
if ($_GET['show_pm_avatar']) {
    $show_pm_avatar = htmlspecialchars($_GET['show_pm_avatar']);
    if ($show_pm_avatar !== 'yes' && $show_pm_avatar !== 'no') stderr('Error', 'There is neither honesty, manhood or good fellowship in thee.');
    sql_query("UPDATE users SET show_pm_avatar = " . sqlesc($show_pm_avatar) . " WHERE id = " . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
    if ($_GET['edit_mail_boxes'])
        header('Location: ?action=editmailboxes&avatar=1');
    else
        header('Location: ?action=viewmailbox&avatar=1&box=' . (0 + $_GET['box']));
    die();
}
// === some get stuff to display messages
$h1_thingie .= ($_GET['deleted1'] ? '<h1 align=center>Message deleted!</h1>' : '');
$h1_thingie .= ($_GET['avatar'] ? '<h1 align=center>Avatars settings changed!</h1>' : '');
$h1_thingie .= ($_GET['pm'] ? '<h1 align=center>PMs per page settings changed!</h1>' : '');
$h1_thingie .= ($_GET['singlemove'] ? '<h1 align=center>Message moved!</h1>' : '');
$h1_thingie .= ($_GET['multi_move'] ? '<h1 align=center>Messages moved!</h1>' : '');
$h1_thingie .= ($_GET['multi_delete'] ? '<h1 align=center>Messages deleted!</h1>' : '');
$h1_thingie .= ($_GET['forwarded'] ? '<h1 align=center>Message forwarded!</h1>' : '');
$h1_thingie .= ($_GET['boxes'] ? '<h1 align=center>boxes added!</h1>' : '');
$h1_thingie .= ($_GET['name'] ? '<h1 align=center>box names updated!</h1>' : '');
$h1_thingie .= ($_GET['new_draft'] ? '<h1 align=center>draft saved!</h1>' : '');
$h1_thingie .= ($_GET['sent'] ? '<h1 align=center>message sent!</h1>' : '');
// === get action and check to see if it's ok...
$possible_actions = array('viewmailbox', 'viewdrafts', 'use_draft', 'send_draft', 'viewmessage', 'move', 'forward', 'forward_pm', 'editmailboxes', 'editmailboxes2', 'delete', 'search', 'viewinbox', 'move_or_delete_multi');
$action = ($_GET['action'] ? htmlspecialchars($_GET['action']) : ($_POST['action'] ? htmlspecialchars($_POST['action']) : 'viewmailbox'));
if (!in_array($action, $possible_actions)) stderr('Error', 'A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.');
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === View listing of Messages in mail box
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'viewmailbox') {
    // === set-up and get needed vars for display
    $page = 0 + $_GET['page'];
    $perpage = $CURUSER['pms_per_page'];
    $mailbox = ($_GET['box'] ? (0 + $_GET['box']) : 1);
    // === get orderby and check to see if it's ok...
    $good_order_by = array('username', 'added', 'date', 'subject', 'id');
    $order_by = ($_GET['order_by'] ? htmlspecialchars($_GET['order_by']) : 'id');
    if (!in_array($order_by, $good_order_by)) stderr('Error', 'Tempt not too much the hatred of my spirit, for I am sick when I do look on thee.');

    if ($mailbox > 1) {
        // == get name of PM box if not in or out
        $res_box_name = sql_query('SELECT name FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' AND boxnumber=' . sqlesc($mailbox) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $arr_box_name = mysql_fetch_assoc($res_box_name);
        $other_box_info = '<font color=red>***</font><b>please note:</b> you have a max of <b>' . maxbox($CURUSER['class']) . '</b> PMs for all mail boxes that are not either <b>inbox</b> or <b>sentbox</b>.';
        if (mysql_num_rows($res) === 0) stderr('Error', 'Invalid Mailbox');
    }
    // ==== get count from PM boxs & get image & % box full
    $res_count = sql_query("SELECT COUNT(*) FROM messages WHERE " . ($mailbox === PM_INBOX ? "receiver=" . $CURUSER['id'] . " AND location = 1" : ($mailbox === PM_SENTBOX ? "sender=" . $CURUSER['id'] . " AND (saved = 'yes' || unread= 'yes')" : "receiver=" . sqlesc($CURUSER['id']) . " AND location=" . sqlesc($mailbox)))) or sqlerr(__FILE__, __LINE__);
    $arr_count = mysql_fetch_row($res_count);
    $messages = $arr_count[0];
    $filled = (($messages / maxbox($CURUSER['class'])) * 100);
    $mailbox_pic = get_percent_inbox_image(round($filled), $maxpic);
    $num_messages = number_format($filled, 0);
    // === get mailbox name
    $mailbox_name = ($mailbox === PM_INBOX ? 'Inbox' : ($mailbox === PM_SENTBOX ? 'Sentbox' : htmlspecialchars($arr_box_name['name'])));
    // === Start Page
    stdhead($mailbox_name);
    // === make pager
    $pages = floor($messages / $perpage);
    if ($pages * $perpage < $messages)
        ++$pages;

    $page = ($page < 1 ? 1 : $page);
    $page = ($page > $pages ? $pages : $page);

    for ($i = 1; $i <= $pages; ++$i)
    $page_num .= ($i == $page ? ' <b>' . $i . '</b> ' : ' <a class=altlink href=?action=viewmailbox&box=' . $mailbox . $q . '&page=' . $i . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '><b>' . $i . '</b></a> ');
    $menu = ($page == 1? ' <p align=center><b><img src=pic/arrow_prev.gif =alt="&lt;&lt;"> Prev</b> ' : ' <p align=center><a class=altlink href=?action=viewmailbox&box=' . $mailbox . $q . '&page=' . ($page - 1) . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '><b><img src=pic/arrow_prev.gif =alt="&lt;&lt;"> Prev</b></a>') . '&nbsp;&nbsp;&nbsp;' . $page_num . '&nbsp;&nbsp;&nbsp;' . ($page == $pages ? '<b>Next <img src=pic/arrow_next.gif =alt="&gt;&gt;"></b> ' : ' <a class=altlink href=?action=viewmailbox&box=' . $mailbox . $q . '&page=' . ($page + 1) . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '><b>Next <img src=pic/arrow_next.gif =alt="&gt;&gt;"></b></a></p>');

    $offset = ($page * $perpage) - $perpage;

    $LIMIT = ($messages > 0 ? "LIMIT $offset,$perpage" : '');
    // === change ASC to DESC and back for sort by
    $desc_asc = ($_GET['ASC'] ? '&DESC=1' : '&ASC=1');
    // === let's make the table
    echo '<table width=95%><tr><td colspan=5 class=colhead align=center>' . $other_box_note . '<font size=1>' . $messages . ' / ' . maxbox($CURUSER['class']) . '</font>&nbsp;&nbsp;&nbsp;&nbsp;<font size=4>' . $mailbox_name . '</font>' . '&nbsp;&nbsp;&nbsp;&nbsp;<font size=1>[ ' . $num_messages . '% full ]</font><br>' . $mailbox_pic . '</td></tr><tr><td colspan=5 class=clearalt6 align=right>' . insertJumpTo($mailbox) . ($pages > 1 ? $menu : '') . '<p align=center>' . $other_box_info . '<br>' . $h1_thingie . '<br></p><form action=?action=move_or_delete_multi method=post name=messages onSubmit="return ValidateForm(this,\'pm\')"><input type=hidden name=action value=move_or_delete_multi>' . '<tr><td width=1% class=colhead>&nbsp;&nbsp;</td><td class=colhead><a class=altlink href=?action=viewmailbox&box=' . $mailbox . $q . ($page > 1 ? '&page=' . $page : '') . '&order_by=subject' . $desc_asc . ' title="order by subject ' . ($_GET['DESC'] ? 'ascending' : 'descending') . '">Subject</a> </td><td width=35% class=colhead><a class=altlink href=?action=viewmailbox&box=' . $mailbox . $q . ($page > 1 ? '&page=' . $page : '') . '&order_by=username' . $desc_asc . ' title="order by member name ' . ($_GET['DESC'] ? 'ascending' : 'descending') . '">' . ($mailbox === PM_SENTBOX ? 'Sent to' : 'Sender') . '</a></td><td width=1% class=colhead><a class=altlink href=?action=viewmailbox&box=' . $mailbox . $q . ($page > 1 ? '&page=' . $page : '') . '&order_by=date' . $desc_asc . ' title="order by date ' . ($_GET['DESC'] ? 'ascending' : 'descending') . '">Date</td><td width=1% class=colhead>&nbsp;&nbsp;</td></tr>';
    // === get message info we need to display then all nice and tidy like \o/
    $res = sql_query("SELECT m.id,m.sender,m.receiver,m.added,m.subject,m.unread,m.urgent,u.avatar,u.username,u.id AS user_id FROM messages AS m LEFT JOIN users AS u ON u.id=m." . ($mailbox === PM_SENTBOX ? 'receiver' : 'sender') . " WHERE " . ($mailbox === PM_INBOX ? "receiver=" . $CURUSER['id'] . " AND location = 1" : ($mailbox === PM_SENTBOX ? "sender=" . $CURUSER['id'] . " AND (saved = 'yes' || unread= 'yes')" : "receiver=" . sqlesc($CURUSER['id']) . " AND location=" . sqlesc($mailbox))) . " ORDER BY $order_by " . ($_GET['ASC'] ? 'ASC ' : ($_GET['DESC'] ? 'DESC ' : 'DESC ')) . $LIMIT) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) === 0)
        echo'<td colspan=5 align=center class=clearalt7><b>No Messages. in ' . $mailbox_name . '</b></td>';
    else {
        while ($row = mysql_fetch_assoc($res)) {
            // =======change colors
            $count = (++$count) % 2;
            $class = 'clearalt' . ($count == 0?6:7);
            $class2 = 'clearalt' . ($count == 0?7:6);
            // === if not system, see if they are a friend yet?
            if ($row['user_id'] !== 0) {
                $res_friend = sql_query("SELECT * FROM friends WHERE userid=" . $CURUSER['id'] . " AND friendid=" . sqlesc($row['user_id'])) or sqlerr(__FILE__, __LINE__);
                $friend = mysql_num_rows($res_friend);
                $friends = ($friend ? '&nbsp;[ <a class=altlink href=friends.php?action=delete&type=friend&targetid=' . $row['user_id'] . '><font size="-3">remove from friends</font></a> ]' : '&nbsp;[ <a class=altlink href=friends.php?action=add&type=friend&targetid=' . $row['user_id'] . '><font size="-3">add to friends</font></a> ]');
            }
            // === avatar stuff
            $avatar = ($CURUSER['show_pm_avatar'] === 'yes' ? (!$row['avatar'] ? '<img width=30 src=pic/default_avatar.gif align=middle> ' : '<img width=30 src=' . htmlspecialchars($row['avatar']) . ' align=middle> ') : '');
            // === print the damn thing :P
            echo($row['unread'] === 'yes' ? '<tr><td class=' . $class . '><img src=pic/inbox_full.gif alt=Unread></td>' : '<tr><td class=' . $class . '><img src=pic/outbox.gif alt=Read></td>') . '<td class=' . $class . '>' . '<a class=altlink href=?action=viewmessage&id=' . $row['id'] . '>' . ($row['subject'] !== '' ? htmlspecialchars($row['subject']) : 'No Subject') . '</a> ' . ($row['unread'] === 'yes' ? '&nbsp;&nbsp;&nbsp;<font color=red>[ un-read ]</font>' : '') . ($row['urgent'] === 'yes' ? '&nbsp;&nbsp;&nbsp;<font color=red><b>URGENT!</b></font>' : '') . '</td>' . '<td class=' . $class . '>' . $avatar . ($row['user_id'] == 0 ? 'System' : '<b><a class=altlink href=userdetails.php?id=' . $row['user_id'] . '>' . $row['username'] . '</a></b>' . $friends) . '</td><td nowrap class=' . $class . '>' . $row['added'] . '</td>' . ($_GET['check'] === 'yes' ? '<td class=' . $class . '><input type=checkbox name="pm[]" value=' . $row['id'] . ' /></td></tr>' : '<td class=' . $class . '><input type=checkbox name="pm[]" value=' . $row['id'] . ' /></td></tr>');
        }
    }
    // === per page drop down ONCHANGE="location = this.options[this.selectedIndex].value;"
    $per_page_drop_down .= '<form><select name=amount_per_page ONCHANGE="location = this.options[this.selectedIndex].value;">';

    $iii = 20;

    while ($iii <= (maxbox($CURUSER['class']) > 200 ? 200 : maxbox($CURUSER['class']))) {
        $per_page_drop_down .= '<option value="?action=viewmailbox&box=' . $mailbox . $q . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '&change_pm_number=' . $iii . '" ' . ($perpage == $iii ? ' selected' : '') . '>' . $iii . ' PMs per page</option>';

        $iii = $iii + 10;
    }

    $per_page_drop_down .= '</select><input type=hidden name=box value="' . $mailbox . '"></form>';
    // === avatars on off ONCHANGE="location = this.options[this.selectedIndex].value;"
    $show_pm_avatar_drop_down = '<form><select name=show_pm_avatar ONCHANGE="location = this.options[this.selectedIndex].value;"><option value="?action=viewmailbox&box=' . $mailbox . $q . ($page > 1 ? '&page=' . $page : '') . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '&show_pm_avatar=yes" ' . ($CURUSER['show_pm_avatar'] === 'yes' ? ' selected' : '') . '>show avatars on PM list</option><option value="?action=viewmailbox&box=' . $mailbox . $q . ($page > 1 ? '&page=' . $page : '') . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '&show_pm_avatar=no" ' . ($CURUSER['show_pm_avatar'] === 'no' ? ' selected' : '') . '>don\'t show avatars on PM list</option></select><input type=hidden name=box value="' . $mailbox . '"></form>';
    // === the bottom
    echo'<tr><td colspan=5 align=right class=' . $class2 . '><a class=altlink href="javascript:SetChecked(1,\'pm[]\')"> select all</a> - <a class=altlink href="javascript:SetChecked(0,\'pm[]\')">un-select all</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '<input class=button type=submit name=move value="Move to"> ' . get_all_boxes() . ' or ' . '<input class=button type=submit name=delete value=Delete> selected messages.</td></tr></table></form><div align=left>' . '<img src=pic/inbox_full.gif alt=Unread /> Unread Messages.<br><img src=pic/outbox.gif alt=Read /> Read Messages.</div>' . ($pages > 1 ? $menu : '') . '<br><div align=center><table><tr><td>' . $per_page_drop_down . '</td><td>' . $show_pm_avatar_drop_down . '</td></tr></table><br>' . '<a class=altlink href=?action=search>Search Messages</a> || <a class=altlink href=?action=editmailboxes>Mailbox Manager / PM settings</a> || <a class=altlink href=/sendmessage.php?receiver=' . $CURUSER['id'] . '&draft=1>write new Draft</a></div>';
    stdfoot();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === View Drafts page list
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'viewdrafts') {
    // === set-up and get needed vars for display
    $page = 0 + $_GET['page'];
    $perpage = $CURUSER['pms_per_page'];
    // === get orderby and check to see if it's ok...
    $good_order_by = array('username', 'added', 'date', 'subject', 'id');
    $order_by = ($_GET['order_by'] ? htmlspecialchars($_GET['order_by']) : 'id');
    if (!in_array($order_by, $good_order_by)) stderr('Error', 'Tempt not too much the hatred of my spirit, for I am sick when I do look on thee.');
    // ==== get count from drafts box
    $res_count = sql_query('SELECT COUNT(*) FROM messages WHERE draft=\'yes\' AND sender=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
    $arr_count = mysql_fetch_row($res_count);
    $messages = $arr_count[0];
    $filled = (($messages / maxbox($CURUSER['class'])) * 100);
    $mailbox_pic = get_percent_inbox_image(round($filled), $maxpic);
    $num_messages = number_format($filled, 0);
    // === get mailbox name
    $mailbox_name = $CURUSER['username'] . ' Drafts';
    // === Start Page
    stdhead($mailbox_name);
    // === make pager
    $pages = floor($messages / $perpage);
    if ($pages * $perpage < $messages)
        ++$pages;

    $page = ($page < 1 ? 1 : $page);
    $page = ($page > $pages ? $pages : $page);

    for ($i = 1; $i <= $pages; ++$i)
    $page_num .= ($i == $page ? ' <b>' . $i . '</b> ' : ' <a class=altlink href=?action=viewdrafts&page=' . $i . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '><b>' . $i . '</b></a> ');
    $menu = ($page == 1? ' <p align=center><b><img src=pic/arrow_prev.gif =alt="&lt;&lt;"> Prev</b> ' : ' <p align=center><a class=altlink href=?action=viewdrafts&page=' . ($page - 1) . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '><b><img src=pic/arrow_prev.gif =alt="&lt;&lt;"> Prev</b></a>') . '&nbsp;&nbsp;&nbsp;' . $page_num . '&nbsp;&nbsp;&nbsp;' . ($page == $pages ? '<b>Next <img src=pic/arrow_next.gif =alt="&gt;&gt;"></b> ' : ' <a class=altlink href=?action=viewdrafts&page=' . ($page + 1) . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '><b>Next <img src=pic/arrow_next.gif =alt="&gt;&gt;"></b></a></p>');

    $offset = ($page * $perpage) - $perpage;

    $LIMIT = ($messages > 0 ? "LIMIT $offset,$perpage" : '');
    // === change ASC to DESC and back for sort by
    $desc_asc = ($_GET['ASC'] ? '&DESC=1' : '&ASC=1');
    // === let's make the table
    echo '<table width=95%><tr><td colspan=5 class=colhead align=center><font size=1>' . $messages . ' / ' . maxboxes($CURUSER['class']) . '</font>&nbsp;&nbsp;&nbsp;&nbsp;<font size=4>' . $mailbox_name . '</font>&nbsp;&nbsp;&nbsp;&nbsp;<font size=1>[ ' . $num_messages . '% full ]</font><br>' . $mailbox_pic . '</td></tr><tr><td colspan=5 class=clearalt6 align=right>' . insertJumpTo($mailbox) . ($pages > 1 ? $menu : '') . '<p align=center><br>' . $h1_thingie . '<br></p><form action=?action=move_or_delete_multi method=post name=messages onSubmit="return ValidateForm(this,\'pm\')"><input type=hidden name=action value=move_or_delete_multi><input type=hidden name=draft_section value=1>' . '<tr><td width=1% class=colhead>&nbsp;&nbsp;</td><td class=colhead><a class=altlink href=?action=viewdrafts' . ($page > 1 ? '&page=' . $page : '') . '&order_by=subject' . $desc_asc . ' title="order by subject ' . ($_GET['DESC'] ? 'ascending' : 'descending') . '">Subject</a> </td><td width=1% class=colhead><a class=altlink href=?action=viewdrafts' . ($page > 1 ? '&page=' . $page : '') . '&order_by=added' . $desc_asc . ' title="order by date ' . ($_GET['DESC'] ? 'ascending' : 'descending') . '">Date</td><td width=1% class=colhead>&nbsp;&nbsp;</td></tr>';
    // === get draft info to list it
    $res = sql_query("SELECT id,added,subject FROM messages WHERE sender=" . $CURUSER['id'] . " AND draft = 'yes' ORDER BY " . $order_by . " " . ($_GET['ASC'] ? 'ASC ' : ($_GET['DESC'] ? 'DESC ' : 'DESC ')) . $LIMIT) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) === 0)
        echo'<td colspan=5 align=center class=clearalt7><b>No messages in Drafts.</b><br><a class=altlink href=/sendmessage.php?receiver=' . $CURUSER['id'] . '&draft=1>write new draft</a></td>';
    else {
        while ($row = mysql_fetch_assoc($res)) {
            // =======change colors
            $count = (++$count) % 2;
            $class = 'clearalt' . ($count == 0?6:7);
            $class2 = 'clearalt' . ($count == 0?7:6);
            // === print the damn thing :P
            echo'<tr><td class=' . $class . '><img src=pic/outbox.gif alt=Read></td><td class=' . $class . '>' . '<a class=altlink href=?action=viewmessage&id=' . $row['id'] . '>' . ($row['subject'] !== '' ? htmlspecialchars($row['subject']) : 'No Subject') . '</a> [ <a class=altlink href=sendmessage.php?receiver=' . $CURUSER['id'] . '&draft=1&edit_draft=' . $row['id'] . ' title="edit this draft">e</a> ]</td>' . '<td nowrap class=' . $class . '>' . $row['added'] . '</td>' . ($_GET['check'] === 'yes' ? '<td class=' . $class . '><input type=checkbox name="pm[]" value=' . $row['id'] . ' /></td></tr>' : '<td class=' . $class . '><input type=checkbox name="pm[]" value=' . $row['id'] . ' /></td></tr>');
        }
    }
    // === per page drop down ONCHANGE="location = this.options[this.selectedIndex].value;"
    $per_page_drop_down .= '<form><select name=amount_per_page ONCHANGE="location = this.options[this.selectedIndex].value;">';

    $iii = 20;

    while ($iii <= (maxbox($CURUSER['class']) > 200 ? 200 : maxbox($CURUSER['class']))) {
        $per_page_drop_down .= '<option value="?action=viewdrafts' . ($_GET['order_by'] ? '&order_by=' . $order_by : '') . ($_GET['DESC'] ? '&DESC=1' : ($_GET['ASC'] ? '&ASC=1' : '')) . '&change_pm_number=' . $iii . '" ' . ($perpage == $iii ? ' selected' : '') . '>' . $iii . ' PMs per page</option>';

        $iii = $iii + 10;
    }

    $per_page_drop_down .= '</select></form>';
    // === the bottom
    echo'<tr><td colspan=5 align=right class=' . $class2 . '><a class=altlink href="javascript:SetChecked(1,\'pm[]\')"> select all</a> - <a class=altlink href="javascript:SetChecked(0,\'pm[]\')">un-select all</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '<input class=button type=submit name=delete value=Delete> selected messages.</td></tr></table></form>' . ($pages > 1 ? $menu : '') . '<br><div align=center><table><tr><td>' . $per_page_drop_down . '</td><td></td></tr></table><br><a class=altlink href=?action=search>Search Messages</a> || <a class=altlink href=?action=editmailboxes>PM box Manager / PM settings</a> || <a class=altlink href=/sendmessage.php?receiver=' . $CURUSER['id'] . '&draft=1>write new Draft</a></div>';
    stdfoot();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === view single message
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'viewmessage') {

	unset($_SESSION['timeunread']);
    $pm_id = 0 + $_GET['id'];
    if (!$pm_id) stderr('Error', 'There\'s many a man hath more hair than wit.');
    // === Get the message
    $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id) . ' AND (receiver=' . $CURUSER['id'] . ' OR (sender=' . $CURUSER['id'] . ' AND (saved = \'yes\' || unread= \'yes\'))) LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $message = mysql_fetch_assoc($res);

    if (!$res) stderr('Error', 'You do not have permission to view this message.');
    // === get user stuff
    $res_user_stuff = sql_query('SELECT username,id,avatar FROM users WHERE id=' . ($message['sender'] === $CURUSER['id'] ? sqlesc($message['receiver']) : sqlesc($message['sender']))) or sqlerr(__FILE__, __LINE__);
    $arr_user_stuff = mysql_fetch_assoc($res_user_stuff);
    $id = ($message['sender'] === $CURUSER['id'] ? $message['receiver'] : $message['sender']);
    // === Mark message read
    sql_query("UPDATE messages SET unread='no' WHERE id=" . sqlesc($pm_id) . " AND receiver=" . $CURUSER['id'] . " LIMIT 1") or sqlerr(__FILE__, __LINE__);
    // === get if friend
    $res_friend = sql_query('SELECT id FROM friends WHERE userid=' . $CURUSER['id'] . ' AND friendid=' . $id) or sqlerr(__FILE__, __LINE__);
    $friend = mysql_fetch_assoc($res_friend);
    // === avatar stuff
    $avatar = ($CURUSER['show_pm_avatar'] === 'yes' ? (!$row['avatar'] ? '<img width=30 src=pic/default_avatar.gif align=middle> ' : '<img width=30 src=' . htmlspecialchars($row['avatar']) . ' align=middle> ') : '');
    // === Display the fuckin message already!
    stdhead('PM ' . htmlspecialchars($subject));

    echo($message['draft'] === 'yes' ? '<h1>This is a draft</h1>' : '<br>') . '<table><tr><td colspan=3 class=colhead align=center><H1>subject: <b>' . ($message['subject'] !== '' ? htmlspecialchars($message['subject']) : 'No Subject') . '</b></H1></td><tr><td colspan=3 class=clearalt7><b>'
     . ($message['sender'] === $CURUSER['id'] ? 'To' : 'From') . ': ' . ($arr_user_stuff['username'] === 0 ? 'System' : ($message['sender'] === $CURUSER['id'] ? '<a class=altlink href=userdetails.php?id=' . $message['receiver'] . '>' . $arr_user_stuff['username'] . '</a>' : '<a class=altlink href=userdetails.php?id=' . $message['sender'] . '>' . $arr_user_stuff['username'] . '</a>')) . ' &nbsp;&nbsp;</b>' . (($friend > 0) ? '&nbsp;[ <a class=altlink href=friends.php?action=delete&type=friend&targetid=' . $id . '><font size="-4">remove from friends</font></a> ]' : ($id > 0 ? '&nbsp;[ <a class=altlink href=friends.php?action=add&type=friend&targetid=' . $id . '><font size="-4">add to friends</font></a> ]' : '')) . '&nbsp;' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>sent on:</b> ' . $message['added'] . '&nbsp;&nbsp;' . (($message['sender'] === $CURUSER['id'] && $message['unread'] == 'yes') ? '<font color=red><b>[ Un-read ]</b></font>' : '') . ($message['urgent'] === 'yes' ? '&nbsp;&nbsp;&nbsp;<blink><font color=red><b>URGENT!</b></font></blink>' : '') . '</td>' . '</tr><tr><td width=83 rowspan=2 align=center valign=top class=clearalt7>' . $avatar . '</td><td colspan=2 class=clearalt6>' . format_comment($message['msg']) . '</td></tr><tr><td class=clearalt7><form action=?action=move method=post>' . '<input type=hidden name=id value=' . $pm_id . '> <b>Move to:</b> ' . get_all_boxes() . ' ' . ' <input class=button type=submit value=Move></form></td><td align=right class=clearalt7>[ <a class=altlink href=?action=delete&id=' . $pm_id . '>Delete</a> ] ' . (($id < 1 || $message['sender'] === $CURUSER['id']) ? '' : ' [ <a class=altlink href=sendmessage.php?receiver=' . $message['sender'] . '&replyto=' . $pm_id . '>Reply</a> ]')
     . ($message['draft'] === 'yes' ? ' [ <a class=altlink href=?action=use_draft&id=' . $pm_id . '>use draft</a> ] [ <a class=altlink href=sendmessage.php?receiver=' . $CURUSER['id'] . '&draft=1&edit_draft=' . $pm_id . '>edit draft</a> ]' : ' [ <a class=altlink href=?action=forward&id=' . $pm_id . '>Forward PM</a> ]') . '</td></tr></table>';
    stdfoot();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === use draft part one... bring up the draft and let them edit it and select a username to send it to
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'use_draft') {
    $pm_id = 0 + $_GET['id'];
    // === Get the info
    $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $message = mysql_fetch_assoc($res);

    if (mysql_num_rows($res) === 0) stderr('Error', 'She hath more hair than wit, and more faults than hairs, and more wealth than faults.');
    // === print out the page
    stdhead('Use Draft');

    echo'<br><table><tr><td class=colhead colspan=2>using draft: ' . htmlspecialchars($message['subject']) . '<form name=compose action=?action=send_draft method=post><input type=hidden name=id value=' . $pm_id . '></td></tr>' . '<tr><td class=clearalt6 valign=top align=right><b>To:</b></td><td class=clearalt6 valign=top><input type=text name=to value="Enter Username" size=83></td></tr>' . '<tr><td class=clearalt6 valign=top align=right><b>Subject:</b></td><td class=clearalt6 valign=top><input type=text name=subject value="' . htmlspecialchars($message['subject']) . '" size=83></td></tr>' . '<tr><td class=clearalt6 valign=top align=right><b>Body:</b></td><td class=clearalt6 valign=top align=right>';
    textbbcode('compose', 'body', $message['msg']);
    echo'</td></tr><tr><td colspan=2 align=center class=clearalt6>' . ($CURUSER['class'] >= UC_MODERATOR? '<b><font color=red>Mark as URGENT!</font></b> <input type=checkbox name=urgent value=yes>&nbsp' : '') . '
Save Message <input type=checkbox name=save value=1> <input class=button type=submit value=send></td></tr></table></form>';

    stdfoot();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === use draft part two... find user to send it to and send it :D
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'send_draft') {
    $pm_id = 0 + $_POST['id'];
    // === Try finding a user with specified name
    $res_username = sql_query('SELECT id, class, acceptpms FROM users WHERE LOWER(username)=LOWER(' . sqlesc(htmlspecialchars($_POST['to'])) . ') LIMIT 1');
    $to_username = mysql_fetch_array($res_username);

    if (mysql_num_rows($res_username) === 0) stderr('Error', 'Sorry, there is no user with that username.');
    // === make sure the reciever has space in their box
    $res_count = sql_query('SELECT COUNT(*) FROM messages WHERE receiver = ' . sqlesc($to_username['id']) . ' AND location = 1') or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res_count) > (maxbox($to_username['class']) * 3) && $CURUSER['class'] < UC_MODERATOR) stderr('Sorry', 'Members mailbox is full.');
    /*
//=== allow suspended users to PM staff only
if ($CURUSER['suspended'] === 'yes'){
$res = sql_query("SELECT id FROM users WHERE class >= ".UC_MODERATOR) or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);
if (!in_array($to, $row)) stderr('Error', 'Your account is suspended, you may only send PMs to staff!');
}
*/
    // === Other then from staff, Make sure recipient wants this message...
    if ($CURUSER['class'] < UC_MODERATOR) {
        // === first if they have PMs turned off
        if ($to_username['acceptpms'] === 'no' && $CURUSER['class'] < UC_MODERATOR) stderr('Error', 'This user dosen\'t accept PMs.');
        // === if this member has blocked the sender
        $res2 = sql_query('SELECT * FROM blocks WHERE userid=' . sqlesc($to_username['id']) . ' AND blockid=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($res2) === 1) stderr('Refused', 'This user has blocked PMs from you.');
        // === finally if they only allow PMs from friends
        if ($to_username['acceptpms'] === 'friends') {
            $res2 = sql_query('SELECT * FROM friends WHERE userid=' . sqlesc($to_username['id']) . ' AND friendid=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
            if (mysql_num_rows($res2) !== 1) stderr('Refused', 'This user only accepts PMs from users in his friends list.');
        }
    }
    // === ok... all is good... let's get the info and send it :D
    $save = (0 + $_POST['save'] == 1 ? 'yes' : 'no');
    $subject = htmlspecialchars($_POST['subject']);
    $body = $_POST['body'];

    sql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg, location, saved) VALUES(" . $CURUSER['id'] . ", " . $CURUSER['id'] . ", " . sqlesc($to_username['id']) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($subject) . "," . sqlesc($body) . ", " . sqlesc(PM_INBOX) . ", " . sqlesc($save) . ")") or sqlerr(__FILE__, __LINE__);
    // === Check if message was sent
    if (mysql_affected_rows() === 0)
        stderr('Error', 'Messages wasn\'t sent!');
    header("Location: ?action=viewmailbox&sent=1");
    die();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === move single PM
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'move') {
    @sql_query('UPDATE messages SET location= ' . sqlesc(0 + $_POST['box']) . ' WHERE id=' . sqlesc(0 + $_POST['id']) . ' AND receiver=' . $CURUSER['id']);
    if (@mysql_affected_rows() === 0) stderr('Error', 'Message could not be moved! <a class=altlink href=' . $BASEURL . '/messages.php?action=viewmessage&id=' . (0 + $_POST['id']) . '>BACK</a> to message.');
    header('Location: ?action=viewmailbox&singlemove=1&box=' . (0 + $_POST['box']));
    die();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === delete single PM
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'delete') {
    $id = sqlesc(0 + $_GET['id']);
    // === Delete a single message first make sure it's not an unread urgent staff message
    $res = sql_query('SELECT receiver,sender,urgent,unread,saved,location FROM messages WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
    $message = mysql_fetch_assoc($res);
    // === make sure they aren't deleting a staff message...
    if ($message['receiver'] == $CURUSER['id'] && $message['urgent'] == 'yes' && $message['unread'] == 'yes') stderr('Error', 'You MUST read this message before you delete it!!! <a class=altlink href=' . $BASEURL . '/messages.php?action=viewmessage&id=' . (0 + $_GET['id']) . '>BACK</a> to message.');
    // === make sure message isn't saved before deleting it, or just update location
    if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no' || $message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED)
        sql_query('DELETE FROM messages WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
    elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes')
        sql_query('UPDATE messages SET location=0, unread=\'no\' WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
    // === just update if it's not deleted
    elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED)
        sql_query('UPDATE messages SET saved=\'no\' WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
    // === see if it worked :D
    if (mysql_affected_rows() === 0) stderr('Error', 'Message could not be deleted! <a class=altlink href=' . $BASEURL . '/messages.php?action=viewmessage&id=' . $id . '>BACK</a> to message.');
    header('Location: ?action=viewmailbox&deleted1=1');
    die();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === delete OR move multiple PMs
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'move_or_delete_multi') {
    // === move
    $pm_id = 0 + $_POST['id'];
    $pm_box = 0 + $_POST['box'];
    $pm_messages = $_POST['pm'];
    if ($_POST['move']) {
        @sql_query("UPDATE messages SET  location=" . sqlesc($pm_box) . " WHERE id IN (" . implode(", ", array_map("sqlesc", $pm_messages)) . ') AND receiver=' . $CURUSER['id']); //=== Move multiple messages
        // === Check if messages were moved
        if (@mysql_affected_rows() === 0)
            stderr('Error', 'Messages couldn\'t be moved!');
        header('Location: ?action=viewmailbox&multi_move=1&box=' . $pm_box);
        die();
    }
    // === delete
    if ($_POST['delete']) {
        // === Delete multiple messages
        foreach ($pm_messages as $id) {
            $res = sql_query("SELECT * FROM messages WHERE id=" . sqlesc((int) $id));
            $message = mysql_fetch_assoc($res);
            // === make sure they aren't deleting a staff message...
            if ($message['receiver'] == $CURUSER['id'] && $message['urgent'] == 'yes' && $message['unread'] == 'yes') stderr('Error', 'You MUST read this message before you delete it!!! <a class=altlink href=' . $BASEURL . '/messages.php?action=viewmessage&id=' . (0 + $_GET['id']) . '>BACK</a> to message.');
            // === make sure message isn't saved before deleting it, or just update location
            if ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'no' || $message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED)
                sql_query('DELETE FROM messages WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
            elseif ($message['receiver'] == $CURUSER['id'] && $message['saved'] == 'yes')
                sql_query('UPDATE messages SET location=0, unread=\'no\' WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
            // === just update if it's not deleted
            elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED)
                sql_query('UPDATE messages SET saved=\'no\' WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
        }
        // === Check if messages were deleted
        if (mysql_affected_rows() === 0)
            stderr('Error', 'Messages couldn\'t be deleted!');
        if ($_POST['draft_section'])
            header('Location: ?action=viewdrafts&multi_delete=1');
        else
            header('Location: ?action=viewmailbox&multi_delete=1&box=' . $pm_box);
        die();
    }
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === forward PMs part one (make the page to forward etc.)
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'forward') {
    $pm_id = 0 + $_GET['id'];
    // === Get the info
    $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $message = mysql_fetch_assoc($res);

    if ($message['receiver'] !== $CURUSER['id'] && $message['sender'] !== $CURUSER['id'] || mysql_num_rows($res) === 0) stderr('Error', 'Come, you are a tedious fool.');
    // === if not from curuser then get who from
    if ($message['sender'] !== $CURUSER['id']) {
        $res_forward = sql_query('SELECT username FROM users WHERE id=' . sqlesc($message['sender'])) or sqlerr(__FILE__, __LINE__);
        $arr_forward = mysql_fetch_assoc($res_forward);
        $forwarded_username = ($message['sender'] === 0 ? 'System' : (mysql_num_rows($res_forward) === 0 ? 'Un-known' : $arr_forward['username']));
    } else
        $forwarded_username = $CURUSER['username'];
    // === print out the forwarding page
    stdhead('Forward PM');

    echo'<h1>Fwd: ' . htmlspecialchars($message['subject']) . '</h1><form action=?action=forward_pm method=post><input type=hidden name=id value=' . $pm_id . '>' . '<table><tr><td class=colhead valign=top align=left colspan=2><h1>forward message <img src=pic/arrow_next.gif alt=":">Fwd: ' . htmlspecialchars($message['subject']) . '</h1></td></tr>' . '<tr><td class=clearalt7 valign=top align=right><b>To:</b></td><td class=clearalt6 valign=top><input type=text name=to value="Enter Username" size=83></td>' . '</tr><tr><td class=clearalt7 valign=top align=right><b>Orignal<BR>Receiver:</b></td><td class=clearalt6 valign=top><b>' . $forwarded_username . '</b></td>' . '</tr><tr><td class=clearalt7 valign=top align=right><b>From:</b></td><td class=clearalt6 valign=top><b>' . $CURUSER['username'] . '</b></td></tr><tr>' . '<td class=clearalt7 valign=top align=right><b>Subject:</b></td><td class=clearalt6 valign=top><input type=text name=subject value="Fwd: ' . htmlspecialchars($message['subject']) . '" size=83></td></tr><tr>' . '<td class=clearalt7 valign=top align=right><b>Message:</b></td><td class=clearalt6 valign=top><textarea name=msg cols=80 rows=8></textarea><br>' . '-------- Original Message from ' . $forwarded_username . ': --------<BR>' . format_comment($message['msg']) . '</td>' . '</tr><tr><td colspan=2 align=center class=clearalt7>' . ($CURUSER['class'] >= UC_MODERATOR? '<b><font color=red>Mark as URGENT!</font></b> <input type=checkbox name=urgent value=yes>&nbsp' : '') . '
Save Message <input type=checkbox name=save value=1><input type=hidden name=first_from value="' . $forwarded_username . '">&nbsp;' . '<input class=button type=submit value=Forward></td></tr></table></form>';

    stdfoot();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === forward PMs part two (send off PM, check stuff and finish)
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'forward_pm') {
    $pm_id = 0 + $_POST['id'];
    // === make sure they "should" be forwarding this PM
    $res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $message = mysql_fetch_assoc($res);

    if (mysql_num_rows($res) === 0) stderr('Error', 'Message Not Found!');

    if ($message['receiver'] !== $CURUSER['id'] && $message['sender'] !== $CURUSER['id']) stderr('Error', 'He be as good a gentleman as the devil is, as Lucifer and Beelzebub himself.');
    // === Try finding a user with specified name
    $res_username = sql_query('SELECT id, class, acceptpms FROM users WHERE LOWER(username)=LOWER(' . sqlesc(htmlspecialchars($_POST['to'])) . ') LIMIT 1');
    $to_username = mysql_fetch_array($res_username);

    if (mysql_num_rows($res_username) === 0) stderr('Error', 'Sorry, there is no user with that username.');
    // === make sure the reciever has space in their box
    $res_count = sql_query('SELECT COUNT(*) FROM messages WHERE receiver = ' . sqlesc($to_username['id']) . ' AND location = 1') or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res_count) > (maxbox($to_username['class']) * 3) && $CURUSER['class'] < UC_MODERATOR) stderr('Sorry', 'Members mailbox is full.');
    /*
//=== allow suspended users to PM / forward to staff only
if ($CURUSER['suspended'] === 'yes'){
$res = sql_query("SELECT id FROM users WHERE class >= ".UC_MODERATOR) or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);
if (!in_array($to, $row)) stderr('Error', 'Your account is suspended, you may only forward PMs to staff!');
}
*/
    // === Other then from staff, Make sure recipient wants this message...
    if ($CURUSER['class'] < UC_MODERATOR) {
        // === first if they have PMs turned off
        if ($to_username['acceptpms'] === 'no' && $CURUSER['class'] < UC_MODERATOR) stderr('Error', 'This user dosen\'t accept PMs.');
        // === if this member has blocked the sender
        $res2 = sql_query('SELECT * FROM blocks WHERE userid=' . sqlesc($to_username['id']) . ' AND blockid=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($res2) === 1) stderr('Refused', 'This user has blocked PMs from you.');
        // === finally if they only allow PMs from friends
        if ($to_username['acceptpms'] === 'friends') {
            $res2 = sql_query('SELECT * FROM friends WHERE userid=' . sqlesc($to_username['id']) . ' AND friendid=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
            if (mysql_num_rows($res2) !== 1) stderr('Refused', 'This user only accepts PMs from users in his friends list.');
        }
    }
    // === ok... all is good... let's get the info and send it :D
    $save = (0 + $_POST['save'] == 1 ? 'yes' : 'no');
    $subject = htmlspecialchars($_POST['subject']);
    $body = "\n\n" . $_POST['msg'] . "\n\n-------- Original Message from " . htmlspecialchars($_POST['first_from']) . ":: \"" . htmlspecialchars($message['subject']) . "\" -------------------------------------\n" . $message['msg'] . "\n";

    sql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg, location, saved) VALUES(" . $CURUSER['id'] . ", " . $CURUSER['id'] . ", " . sqlesc($to_username['id']) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($subject) . "," . sqlesc($body) . ", " . sqlesc(PM_INBOX) . ", " . sqlesc($save) . ")") or sqlerr(__FILE__, __LINE__);
    // === Check if message was forwarded
    if (mysql_affected_rows() === 0)
        stderr('Error', 'Messages couldn\'t be deleted!');
    header("Location: ?action=viewmailbox&forwarded=1");
    die();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === editing mail boxes! part one (options)
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'editmailboxes') {
    $res = sql_query('SELECT * FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' ORDER BY name ASC') or sqlerr(__FILE__, __LINE__);
    // === get all PM boxes for editing
    while ($row = mysql_fetch_assoc($res)) {
        // ==== get count from PM boxes
        $res_count = sql_query('SELECT COUNT(*) FROM messages WHERE  location =' . (0 + $row['boxnumber']) . ' AND receiver=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
        $arr_count = mysql_fetch_row($res_count);
        $messages = $arr_count[0];
        $all_my_boxes .= '<tr><td class=clearalt6 align=right width=110>Box # ' . ($row['boxnumber'] -1) . ' <b>' . htmlspecialchars($row['name']) . ':</b> </td><td class=clearalt6 align=left><input type=text name=edit' . (0 + $row['id']) . ' value="' . htmlspecialchars($row['name']) . '" size=40 maxlength=14> [ contains ' . $messages . ' messages ]</td></tr>';
    }

    $all_my_boxes .= ($all_my_boxes == '' ? '<tr><td class=clearalt6 colspan=2 align=left><b>There are currently no PM boxes to edit.</b><br></td></tr>' : '<tr><td class=clearalt6 colspan=2 align=left>You may edit the names of your PM boxes here.<br>If you wish to delete 1 or more PM boxes, remove the name from the text field leaving it blank.</td></tr><tr><td class=clearalt6 align=left width colspan=2><b>Please note!!!</b> if you delete the name of one or more boxes,  all messages in that directory will be sent to your inbox!!!<li>If you wish to delete the messages as well, you can do that from the main page.</li></td></tr><tr><td class=clearalt6 align=center width colspan=2><input class=button type=submit value=Edit></td></tr>');
    // === per page drop down
    $iii = 20;
    while ($iii <= (maxbox($CURUSER['class']) > 200 ? 200 : maxbox($CURUSER['class']))) {
        $per_page_drop_down .= '<option value=' . $iii . ' ' . ($CURUSER['pms_per_page'] == $iii ? ' selected' : '') . '>' . $iii . ' PMs per page</option>';
        $iii = $iii + 10;
    }
    // === make up page
    stdhead('Editing PM boxes');

    echo '<font size="+2">Editing PM boxes</font>' . $h1_thingie . '<table><tr><td class=colhead2 colspan=2 align=left>Add PM boxes</td></tr><tr>' . '<td class=clearalt6 colspan=2 align=left>As a ' . get_user_class_name($CURUSER['class']) . ' you may have up to ' . maxboxes($CURUSER['class']) . ' PM box' . (maxboxes($CURUSER['class']) !== 1 ? 'es' : '') . ' other then your in, sent and draft boxes.<br>' . 'Currently you have ' . mysql_num_rows($res) . ' custom box' . (mysql_num_rows($res) !== 1 ? 'es' : '') . ' <br>You may add up to ' . (maxboxes($CURUSER['class']) - mysql_num_rows($res)) . ' more extra mailboxes.<br>' . '<br><b>The following characters can be used: </b> a-z, A-Z, 1-9, - and _ [ all other characters will be ignored ]<br></td></tr>' . '<form action=?action=editmailboxes2&action2=add method=post>';
    // === make loop for oh let's say 5 boxes...
    for ($ii = 1;$ii < 6;$ii++)
    echo'<tr><td class=clearalt6 align=right width=110><b>add ' . $ii . ' more box' . ($ii !== 1 ? 'es' : '') . ':</b> </td><td class=clearalt6 align=left><input type=text name="new[]" size=40 maxlength=14></td></tr>';

    echo'<tr><td class=clearalt6 colspan=2 align=left><br>only fill in add as many boxes that you would like to add and click <input class=button type=submit value=Add> [ blank entries will be ignored ]</form><br><br></td></tr>' . '<tr><td class=colhead2 colspan=2 align=left>Edit / Delete PM boxes</td></tr>' . '<form action=?action=editmailboxes2 method=post><input type=hidden name=action2 value=edit_boxes>' . $all_my_boxes . '</form>' . '<tr><td class=colhead2 colspan=2 align=left>PM settings</td></tr><tr><td class=clearalt6 colspan=2 align=left><b>Set the default number of messages to be viewed per page.</b>' . '<form action=? method=get> <select name=change_pm_number>' . $per_page_drop_down . '</select> please select how many PMs you would like to see per page, and click change.' . '<input class=button type=submit value=change><input type=hidden name=edit_mail_boxes value=1></form><br><b>Show avatars on PM list?</b>' . '<form action=? method=get><select name=show_pm_avatar><option value=yes ' . ($CURUSER['show_pm_avatar'] === 'yes' ? ' selected' : '') . '>show avatars on PM list</option>' . '<option value=no ' . ($CURUSER['show_pm_avatar'] === 'no' ? ' selected' : '') . '>don\'t show avatars on PM list</option></select> please select if you would like to see avatars in your PM listings and click ' . '<input type=hidden name=edit_mail_boxes value=1><input class=button type=submit value=change></form><br><br></td></tr></table>';

    stdfoot();
}
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === editing mail boxes! part two (doing it)
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'editmailboxes2') {
    $action2 = ($_GET['action2'] == 'add' ? 'add' : ($_POST['action2'] == 'edit_boxes' ? 'edit_boxes' : ''));

    if (!$action2) stderr('Error', 'His wit\'s as thick as a Tewkesbury mustard.');
    // === add more boxes...
    if ($action2 === 'add') {
        // === make sure they posted something...
        if ($_POST['new'] === '')
            stderr('Error', 'to add new PM boxes you MUST enter at least one PM box name!');
        // === Get current highest box number
        $res = sql_query("SELECT MAX(boxnumber) AS top_box FROM pmboxes WHERE userid=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $box_arr = mysql_fetch_assoc($res);
        $box = ($box_arr['top_box'] < 3 ? 2 : $box_arr['top_box']);
        // === let's add the new boxes to the DB
        $new_box = $_POST['new'];

        foreach ($new_box as $key => $add_it) {
            if (safe_box_name($add_it) && $add_it !== '') {
                $name = sqlesc(htmlspecialchars($add_it));
                sql_query("INSERT INTO pmboxes (userid, name, boxnumber) VALUES (" . sqlesc($CURUSER['id']) . ", " . $name . ", $box)") or sqlerr(__FILE__, __LINE__);
            }
            ++$box;
            $worked = '&boxes=1';
        }
        // === redirect back with messages :P
        header('Location: ?action=editmailboxes' . $worked);
        die();
    }
    // === edit boxes
    if ($action2 === 'edit_boxes') {
        // === get info
        $res = sql_query('SELECT * FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) === 0) stderr(' Error', 'No Mailboxes to edit');

        while ($row = mysql_fetch_assoc($res)) {
            // === if name different AND safe, update it
            if (safe_box_name($_POST['edit' . $row['id']]) && $_POST['edit' . $row['id']] !== '' && $_POST['edit' . $row['id']] !== $row['name']) {
                $name = sqlesc(htmlspecialchars($_POST['edit' . $row['id']]));
                sql_query('UPDATE pmboxes SET name=' . $name . ' WHERE id=' . sqlesc($row['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $worked = '&name=1';
            }
            // === if name is empty, delete the box(es) and send the PMs back to the inbox..
            if ($_POST['edit' . $row['id']] == '') {
                // === get messages to move
                $remove_messages_res = sql_query('SELECT id FROM messages WHERE location=' . sqlesc($row['boxnumber']) . '  AND receiver=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                // == move the messages to the inbox
                while ($remove_messages_arr = mysql_fetch_assoc($remove_messages_res))
                sql_query('UPDATE messages SET location=1 WHERE id=' . sqlesc($remove_messages_arr['id'])) or sqlerr(__FILE__, __LINE__);
                // == delete the box
                sql_query('DELETE FROM pmboxes WHERE id=' . sqlesc($row['id']) . '  LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $deleted = '&box_delete=1';
            }
        }
        // === redirect back with messages :P
        header('Location: ?action=editmailboxes' . $deleted . $worked);
        die();
    } //=== end if edit
} //=== end action
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// === search functions
// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($action === 'search') {
    // === get post / get stuff
    $keywords = htmlspecialchars($_POST['keywords']);
    $member = htmlspecialchars($_POST['member']);
    $mailbox = ($_POST['box'] ? (0 + $_POST['box']) : 1);
    $all_boxes = (0 + $_POST['all_boxes']);
    $sender_reciever = ($mailbox >= 1 ? 'sender' : 'receiver');
    // == query stuff
    $what_in_out = ($mailbox >= 1 ? "AND receiver=" . sqlesc($CURUSER['id']) . " AND saved='yes'" : "AND sender=" . sqlesc($CURUSER['id']) . " AND saved='yes'");
    $location = ($all_boxes ? 'AND location != 0' : 'AND location = ' . sqlesc($mailbox));
    $limit = ($_POST['limit'] ? (0 + $_POST['limit']) : 25);
    $desc_asc = (($_POST['ASC'] == 1) ? 'ASC' : 'DESC');
    // === search in
    $subject = (0 + $_POST['subject']);
    $text = (0 + $_POST['text']);
    // === get sort and check to see if it's ok...
    $possible_sort = array('added', 'subject', 'sender', 'receiver', 'relevance');
    $sort = ($_GET['sort'] ? htmlspecialchars($_GET['sort']) : ($_POST['sort'] ? htmlspecialchars($_POST['sort']) : 'relevance'));
    $sort = htmlspecialchars($_POST['sort']);
    // === Try finding a user with specified name
    if ($member) {
        $res_username = sql_query('SELECT username,id FROM users WHERE LOWER(username)=LOWER(' . sqlesc($member) . ') LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $arr_username = mysql_fetch_array($res_username);
        if (mysql_num_rows($res_username) === 0) stderr('Error', 'Sorry, there is no member with that username.');
        // === if searching by member...
        $and_member = ($mailbox >= 1 ? " AND sender=" . sqlesc($arr_username['id']) . " AND saved='yes' " : " AND receiver=" . sqlesc($arr_username['id']) . " AND saved='yes' ");
        $the_username = '<a class=altlink href=userdetails.php?id=' . $arr_username['id'] . '>' . htmlspecialchars($arr_username['username']) . '</a>';
    }
    // === get all boxes
    $res = sql_query('SELECT boxnumber,name FROM pmboxes WHERE userid=' . $CURUSER['id'] . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);
    while ($row = mysql_fetch_assoc($res))
    $get_all_boxes .= '<option value=' . $row['boxnumber'] . ' ' . ($row['boxnumber'] == $mailbox ? 'selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
    // === make up page
    stdhead('Search Messages');

    echo'<br><form action=?action=search method=post><table width=90%><tr><td class=colhead align=center colspan=2><h1>Search Messages</h1></td></tr>' . '<tr><td class=clearalt6 align=right><b>search terms:</b></td><td align=left class=clearalt6><input type=text size=40 name=keywords value="' . $keywords . '"> [ words to search for. common words are ignored ]</td></tr>' . '<tr><td class=clearalt6 align=right><b>search box:</b></td><td align=left class=clearalt6><select name=box><option value=1 ' . ($mailbox == PM_INBOX ? 'selected' : '') . '>Inbox</option><option value="-1" ' . ($mailbox == PM_SENTBOX ? 'selected' : '') . '>Sentbox</option>' . $get_all_boxes . '</select></td></tr>' . '<tr><td class=clearalt6 align=right> or <b>search all boxes:</b></td><td align=left class=clearalt6><input name=all_boxes type=checkbox value=1 ' . ($all_boxes == 1 ? ' checked' : '') . '> [ if checked the above box selection will be ignored ]</td></tr>' . '<tr><td class=clearalt6 align=right><b>by member:</b></td><td align=left class=clearalt6><input type=text size=15 name=member value="' . $member . '"> [ search messages by this member only ]</td></tr>' . '<tr><td class=clearalt6 align=right><b>search in:</b></td><td align=left class=clearalt6><input name=subject type=checkbox value=1 ' . ($subject == 1 ? ' checked' : '') . '> subject [ default ] <input name=text type=checkbox value=1 ' . ($text === 1 ? ' checked' : '') . '> message text [ select one or both. if none selected, both are assumed ]</td></tr>' . '<tr><td class=clearalt6 align=right><b>sort by:</b></td><td class=clearalt6><select name=sort><option value=relevance' . ($sort === 'relevance' ? ' selected' : '') . '>relevance</option><option value=subject' . ($sort === 'subject' ? ' selected' : '') . '>subject</option><option value=added ' . ($sort === 'added' ? ' selected' : '') . '>added</option><option value=' . $sender_reciever . ($sort === $sender_reciever ? ' selected' : '') . '>member</option></select><input name=ASC type=radio value=1 ' . (($_POST['ASC'] == 1) ? ' checked' : '') . '> Ascending <input name=ASC type=radio value=2 ' . (($_POST['ASC'] == 2 || !$_POST['ASC']) ? ' checked' : '') . '> Descending</td></tr>' . '<tr><td class=clearalt6 align=right><b>show:</b></td><td align=left class=clearalt6><select name=limit><option value=25' . (($limit == 25 || !$limit) ? ' selected' : '') . '>first 25 results</option><option value=50' . ($limit == 50 ? ' selected' : '') . '>first 50 results</option><option value=75' . ($limit == 75 ? ' selected' : '') . '>first 75 results</option><option value=100' . ($limit == 100 ? ' selected' : '') . '>first 100 results</option><option value=150' . ($limit == 150 ? ' selected' : '') . '>first 150 results</option><option value=200' . ($limit == 200 ? ' selected' : '') . '>first 200 results</option><option value=1000' . ($limit == 1000 ? ' selected' : '') . '>all results</option></select></td></tr>' .
    ($limit < 100 ?'<tr><td class=clearalt6 align=right><b>display as:</b></td><td align=left class=clearalt6><input name=as_list_post type=radio value=1 ' . ((0 + $_POST['as_list_post'] == 1 || !$_POST['as_list_post']) ? ' checked' : '') . '> <b>list </b><input name=as_list_post type=radio value=2 ' . ((0 + $_POST['as_list_post'] == 2) ? ' checked' : '') . '> <b> message</b></td></tr>' : '') . '<tr><td colspan=2 align=center class=clearalt6><input type=submit value=search class=button></td></tr></table></form>';
    // === do the search and print page :)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // === remove common words first. add more if you like...
        $remove_me = array('a', 'the', 'and', 'to', 'for', 'by');
        $search = preg_replace('/\b(' . implode('|', $remove_me) . ')\b/', '', $keywords);
        // === do the search!
        switch (true) {
            // === if only member name is entered and no search string... get all messages by that member
            case (!$keywords && $member):
                $res_search = sql_query("SELECT * FROM messages WHERE sender=" . sqlesc($arr_username['id']) . " AND saved='yes' $location AND receiver=" . sqlesc($CURUSER['id']) . " ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT " . sqlesc($limit)) or sqlerr(__FILE__, __LINE__);
                break;
            // === if just subject
            case ($subject && !$text):
                $res_search = sql_query("SELECT *, MATCH(subject) AGAINST('$search' IN BOOLEAN MODE) AS relevance FROM messages WHERE ( MATCH(subject) AGAINST ('$search' IN BOOLEAN MODE) ) $and_member $location $what_in_out ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT " . sqlesc($limit)) or sqlerr(__FILE__, __LINE__);
                break;
            // === if just message
            case (!$subject && $text):
                $res_search = sql_query("SELECT *, MATCH(msg) AGAINST('$search' IN BOOLEAN MODE) AS relevance FROM messages WHERE ( MATCH(msg) AGAINST ('$search' IN BOOLEAN MODE) ) $and_member $location $what_in_out ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT " . sqlesc($limit)) or sqlerr(__FILE__, __LINE__);;
                break;
            // === if subject and message
            case ($subject && $text || !$subject && !$text):
                $res_search = sql_query("SELECT *, ( (1.3 * (MATCH(subject) AGAINST ('$search' IN BOOLEAN MODE))) + (0.6 * (MATCH(msg) AGAINST ('$search' IN BOOLEAN MODE)))) AS relevance FROM messages WHERE ( MATCH(subject,msg) AGAINST ('$search' IN BOOLEAN MODE) ) $and_member $location $what_in_out ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT " . sqlesc($limit)) or sqlerr(__FILE__, __LINE__);
                break;
        }

        $num_resault = mysql_num_rows($res_search);
        // === show the search resaults \o/o\o/o\o/
        echo'<h1>your search for ' . ($keywords ? '"' . $keywords . '"' : ($member ? 'member ' . htmlspecialchars($arr_username['username']) . '\'s PMs' : '')) . '</h1><p align=center>' . ($num_resault < $limit ? 'returned' : 'showing first') . ' <b>' . $num_resault . '</b> match' . ($num_resault === 1 ? '' : 'es') . '! ' . ($num_resault === 0 ? ' better luck next time...' : '') . '</p>';
        // === let's make the table
        echo($num_resault > 0 ?'<table width=95%>' . ((0 + $_POST['as_list_post'] == 2) ? '' : '<tr><td colspan=5 class=colhead align=center><font size=4>' . $mailbox_name . '</font></td></tr>' . '<tr><td width=1% class=colhead>&nbsp;&nbsp;</td><td class=colhead>Subject </td><td width=35% class=colhead>' . ($mailbox === PM_SENTBOX ? 'Sent to' : 'Sender') . '</td><td width=1% class=colhead> Date</td></tr>') : '');

        while ($row = mysql_fetch_assoc($res_search)) {
            // =======change colors
            $count = (++$count) % 2;
            $class = 'clearalt' . ($count == 0?6:7);
            $class2 = 'clearalt' . ($count == 0?7:6);
            // === if not searching one member...
            if (!$member) {
                $res_username = sql_query('SELECT username,id FROM users WHERE id=' . sqlesc($row[$sender_reciever]) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_username = mysql_fetch_array($res_username);
                $the_username = '<a class=altlink href=userdetails.php?id=' . $arr_username['id'] . '>' . htmlspecialchars($arr_username['username']) . '</a>';
            }
            // === if searching all boxes...
            $arr_box = ($row['location'] == 1 ? 'Inbox' : ($row['location'] < 1 ? 'Sentbox' : ''));
            if ($all_boxes && $arr_box === '') {
                $res_box_name = sql_query('SELECT name FROM pmboxes WHERE userid=' . $CURUSER['id'] . ' AND boxnumber=' . sqlesc($row['location'])) or sqlerr(__FILE__, __LINE__);
                $arr_box_name = mysql_fetch_assoc($res_box_name);
                $arr_box = $arr_box_name['name'];
            }
            // ==== highlight search terms... from Jaits search forums mod
            $body = str_ireplace($keywords, '<font style=\'background-color:yellow;font-weight:bold;color:black;\'>' . $keywords . '</font>', format_comment($row['msg']));
            $subject = str_ireplace($keywords, '<font style=\'background-color:yellow;font-weight:bold;color:black;\'>' . $keywords . '</font>', htmlspecialchars($row['subject']));
            // === print the damn thing :P
            // === if it's as a list or as posts...
            echo((0 + $_POST['as_list_post'] == 2) ? '<tr><td class=colhead colspan=4>message from: ' . ($row[$sender_reciever] == 0 ? 'System' : '<b>' . $the_username . '</b>') . '</td></tr><tr><td class=' . $class2 . ' colspan=4><b>subject:</b> <a class=altlink href=?action=viewmessage&id=' . $row['id'] . '>' . ($row['subject'] !== '' ? $subject : 'No Subject') . '</a> ' . ($all_boxes ? '[ found in ' . $arr_box . ' ]' : '') . ' at: ' . $row['added'] . ' GMT (' . (get_elapsed_time(sql_timestamp_to_unix_timestamp($row['added']))) . ' ago)</td></tr><tr><td class=' . $class . ' colspan=4>' . $body . '</td></tr>' : '<tr><td class=' . $class . '><img src=pic/outbox.gif alt=Read></td><td class=' . $class . '><a class=altlink href=?action=viewmessage&id=' . $row['id'] . '>' . ($row['subject'] !== '' ? $subject : 'No Subject') . '</a> ' . ($all_boxes ? '[ found in ' . $arr_box . ' ]' : '') . '</td>' . '<td class=' . $class . '>' . ($row[$sender_reciever] == 0 ? 'System' : '<b>' . $the_username . '</b>') . '</td><td nowrap class=' . $class . '>' . $row['added'] . ' GMT (' . (get_elapsed_time(sql_timestamp_to_unix_timestamp($row['added']))) . ' ago) </td></tr>');
        }
    }
    // === the bottom
    echo($num_resault > 0 ?'</table>' : '') . '<br><a class=altlink href=?action=search>Search Messages</a> || <a class=altlink href=?action=editmailboxes>Mailbox Manager / PM settings</a> || <a class=altlink href=/sendmessage.php?receiver=' . $CURUSER['id'] . '&draft=1>write new Draft</a></div>';

    stdfoot();
}

die;

?>