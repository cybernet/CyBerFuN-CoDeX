<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
/**
* ---------------------------------------------------------------------------- *
* H E L P D E S K *
* (for TB source) *
* ---------------------------------------------------------------------------- *
* written by nuerher[at]gmail.com *
*/
// === updated, re-written a bit, stuff added, made safe etc... v2
// === updated again sept 17th 2008 added pager, select / deselect delete function, more stuff... v2.1
// === make sure we get what action we expect
$all_actions = array('problems', 'cleanuphd', 'solve');

$action = (($_GET['action'] && in_array($_GET['action'], $all_actions)) ? htmlspecialchars($_GET['action']) : (($_POST['action'] && in_array($_POST['action'], $all_actions)) ? htmlspecialchars($_POST['action']) : ''));
// === delete multi HD for staff
if ($_GET['staff_delete'] && get_user_class() >= UC_SYSOP) {
    $staff_delete = ((0 + $_GET['staff_delete']) == 1 ? (0 + $_GET['staff_delete']) : stderr('Error', 'I smell a rat!'));

    if (empty($_POST['delhd']))
        stderr('Error', 'Don\'t leave any fields blank! You MUST select at least one item to delete.');

    sql_query("DELETE FROM helpdesk WHERE id IN (" . implode(", ", $_POST[delhd]) . ")");
    $del_num = mysql_affected_rows();
    header('Refresh: 0; url=/helpdesk.php?action=problems&deleted=' . $del_num);
    die();
}
function round_time($ts)
{
    $mins = floor($ts / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    $t = '';

    switch (true) {
        case ($weeks > 0):
            return $weeks . ' week' . ($weeks > 1 ? 's' : '');
            break;
        case ($days > 0):
            return $days . ' day' . ($days > 1 ? 's' : '');
            break;
        case ($hours > 0):
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
            break;
        case ($mins > 0):
            return $mins . ' min' . ($mins > 1 ? 's' : '');
            break;
        case ($mins < 1):
            return '< 1 min';
            break;
    }
}

$msg_problem = $_POST['msg_problem'];
$msg_answer = $_POST['body'];
$id = 0 + $_POST['id'];
$addedbyid = 0 + $_POST['addedbyid'];
$title = htmlentities($_POST['title']);
$solve = htmlentities($_GET['solve']);
// === action: cleanuphd
if ($action === 'cleanuphd' && get_user_class() >= UC_MODERATOR) {
    sql_query("DELETE FROM helpdesk WHERE solved='yes' OR solved='ignored'") or sqlerr(__FILE__, __LINE__);
    $action = 'problems';
}
// === action: problems
if ($action === 'problems') {
    if (get_user_class() < UC_MODERATOR)
        stderr('Sorry...', 'You are not authorized to enter this section.');
    $id = 0 + $_GET['id'];
    // === make Problems page for staff
    stdhead('HELP DESK - Problems');
    begin_main_frame();
    // begin_frame('Problems');
    $problem_count = sql_query("select count(id) as problems from helpdesk WHERE solved = 'no'");
    $they_bitchin_again = mysql_fetch_assoc($problem_count);
    $problems = $they_bitchin_again['problems'];

    echo($problems > 0 ? '<p align=center><b><font color=red>' . $problems . '</font> un-solved problem' . ($problems !== 1 ? 's' : '') . ' </b></a><br></p>' : '');
    // === if get $id, view problem selected ELSE list problems
    if (is_valid_id($id)) {
        $res = sql_query("SELECT * FROM helpdesk WHERE id =" . $id) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_array($res);

        $biatch_rez = sql_query("SELECT username FROM users WHERE id =" . $arr['added_by']) or sqlerr(__FILE__, __LINE__);
        $biatch_arr = mysql_fetch_assoc($biatch_rez);

        $solved_by_rez = sql_query("SELECT username FROM users WHERE id =" . $arr['solved_by']) or sqlerr(__FILE__, __LINE__);
        $solved_by_arr = mysql_fetch_assoc($solved_by_rez);

        echo'<table align=center border=1 cellpadding=5 cellspacing=0>' . '<tr><td align=center colspan=2 class=colhead>' . htmlentities($arr['title']) . '</td></tr>' . '<tr><td align=right class=clearalt6><b>Added</b></td><td align=left class=clearalt6>On&nbsp;<b>' . $arr['added'] . '</b>' . '&nbsp;by&nbsp;<a class=altlink href=userdetails.php?id=' . $arr['added_by'] . '><b>' . ($biatch_arr['username'] == '' ? 'Deleted member' : $biatch_arr['username']) . '</b></a></td></tr>';

        if ($arr['solved'] === 'yes')
            echo'<tr><td align=right class=clearalt6><b>Problem</b></td><td align=left class=clearalt6><textarea name=msg_problem cols=80 rows=15 READONLY style="background-color:#332831; color:#f5f0c1;">' . $arr['msg_problem'] . '</textarea></td></tr>' . '<tr><td align=right class=colhead><b>Solved</b></td><td align=left class=colhead><font color=green><b>Yes</b></font>&nbsp;on&nbsp;<b>' . $arr['solved_date'] . '</b>&nbsp;by&nbsp;<a class=altlink href=userdetails.php?id=' . $arr['solved_by'] . '><b>' . ($solved_by_arr['username'] == '' ? 'Deleted Staff' : $solved_by_arr['username']) . '</b></a></td></tr>' . '<tr><td align=right class=clearalt7><b>Answer</b></td><td align=left class=clearalt7><textarea name=msg_answer cols=80 rows=15 READONLY style="background-color:#241a28; color:#f5f0c1;">' . $arr['msg_answer'] . '</textarea></td></tr></table>';

        if ($arr['solved'] === 'ignored')
            echo'<tr><td align=right class=clearalt6><b>Problem</b></td><td align=left class=clearalt6><textarea name=msg_problem cols=80 rows=15>' . $arr['msg_problem'] . '</textarea></td></tr>' . '<tr><td align=right class=colhead><b>Solved</b></td><td align=left class=colhead><font color=orange><b>Ignored</b></font>&nbsp;on&nbsp;<b>' . $arr['solved_date'] . '</b>&nbsp;by&nbsp;<a class=altlink href=userdetails.php?id=' . $arr['solved_by'] . '><b>' . ($solved_by_arr['username'] == '' ? 'Deleted Staff' : $solved_by_arr['username']) . '</b></a></td></tr>' . '</table>';

        if ($arr['solved'] === 'no') {
            // === Standard HelpDesk Replies
            $hd_reply['1'] = array('Answer Is In The FAQ', 'First Read The FAQ!!! Your question is answered in the FAQ!');
            $hd_reply['2'] = array('Answer Is In The Forums', 'Search the FORUMS!!! Your question has been answered in the FORUMS!');
            $hd_reply['3'] = array('Allowed / Banned clients', 'A list of Allowed AND banned clients can be found listed HERE in the FAQ!');
            $hd_reply['4'] = array('Stats Not Updating / Counting', 'Sometimes there is a delay in Stats updating ' . $SITENAME . '\'s stats are generally updated every ' . mkprettytime($autoclean_interval) . ' min. however sometimes the site is slower to respond... Give it a while, and the site will catch up.');
            $hd_reply['5'] = array('Die n00b', 'Die n00b! Such a thing is known even by my grandma!');

            $hd_answer = $_POST['hd_answer'];
            $body = ($_POST['hd_answer'] !== '' ? $hd_reply[$hd_answer][1] : '');
            $addedbyid = 0 + $arr['added_by'];

            echo'<form method=post name=compose action=helpdesk.php><tr><td align=right class=clearalt6><b>Problem</b></td><td align=left class=clearalt6><textarea name=msg_problem cols=80 rows=15 READONLY style="background-color:#332831; color:#f5f0c1;">' . $arr['msg_problem'] . '</textarea></td></tr>' . '<tr><td align=right class=colhead><b>Solved</b></td><td align=center class=colhead align=left><font color=red><b>No</b></font><tr><td align=center class=clearalt7><b>Answer:</b></td><td align=center class=clearalt7>';
            textbbcode('compose', 'body', $body);
            echo'<input type=hidden name=id value=' . $id . '><input type=hidden name=addedbyid value=' . $addedbyid . '></td></tr>' . '<tr><td colspan=2 align=center class=clearalt7> <script language="javascript" src="spellmessage.js"></script> ' . '<input type=button class=button value="Spell Check" onclick="return openspell(1);"> <input type=submit value="Answer question" class=button> ' . ' <a class=altlink2 href=helpdesk.php?action=solve&pid=' . $id . '&solved=ignored><input type=submit value="Ignore question" class=button></a></form></td></tr>' . '<tr><td align=center colspan=2 class=colhead><form method=post action=helpdesk.php?action=problems&id=' . $id . '><b>General Help Desk Replies:</b> <select name=hd_answer>';
            // === add the standerd answers drop down
            for ($i = 1; $i <= count($hd_reply); $i++) {
                echo "<option value=$i " . ($hd_answer == $i?"selected":"") . ">" . $hd_reply[$i][0] . "</option>\n";
            }
            echo '</select> <input type=submit value="Use the answer" class=button></form></td></tr></table>';
        }
    }
    // == else list all problems at the help desk for staff...
    else {
        // === add some javascript to make mass deleting fun and painless :P
        ?>
<script language = "Javascript">
<!--
var form='helpdesk'
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
        // === message if delete success
        echo($_GET['deleted'] ? ((0 + $_GET['deleted'] > 0) ? '<h1 align=center>Success! ' . (0 + $_GET['deleted']) . ' Selected items deleted</h1>' : '') : '');

        $res_count = sql_query("SELECT id FROM helpdesk") or sqlerr(__FILE__, __LINE__);
        $count = mysql_num_rows($res_count);
        // === change 20 to default amount per page...
        $perpage = (($_GET['per_page'] && 0 + $_GET['per_page'] > 0) ? (0 + $_GET['per_page']) : 20);
        // === per page drop down ONCHANGE="location = this.options[this.selectedIndex].value;"
        $per_page_drop_down .= '<form><select name=amount_per_page ONCHANGE="location = this.options[this.selectedIndex].value;">';

        $i = 20;
        while ($i <= 200) {
            $per_page_drop_down .= '<option value="?action=problems&per_page=' . $i . '" ' . ($perpage == $i ? ' selected' : '') . '>' . $i . ' items per page</option>';
            $i = $i + 10;
        }

        $per_page_drop_down .= '</select></form>';
        list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, '?action=problems' . (($_GET['per_page'] && 0 + $_GET['per_page'] > 0) ? '&per_page=' . (0 + $_GET['per_page']) : '') . '&');

        echo'<p align=center>' . $pagertop . '</p><p align=center>' . $per_page_drop_down . '</p>';
        echo'<table align=center border=1 cellpadding=5 cellspacing=0><td class=colhead align=center>Added</td><td class=colhead align=center>Added by</td><td class=colhead align=center>Problem</td><td class=colhead align=center>Solved - by</td><td class=colhead align=center>Solved in*</td>' . (get_user_class() == UC_SYSOP ? '<td class=colhead align=center>Delete</td>' : '') . '</tr>' . '<form method=post name=helpdesk action=?staff_delete=1 onSubmit="return ValidateForm(this,\'delhd\')">';

        $res = sql_query("SELECT added, solved_date, added_by, id, title, solved, solved_by FROM helpdesk ORDER BY added DESC $limit") or sqlerr(__FILE__, __LINE__);
        while ($arr = mysql_fetch_array($res)) {
            $biatch_rez = sql_query("SELECT username FROM users WHERE id =" . $arr['added_by']) or sqlerr(__FILE__, __LINE__);
            $biatch_arr = mysql_fetch_assoc($biatch_rez);

            $solved_by_rez = sql_query("SELECT username FROM users WHERE id =" . $arr['solved_by']) or sqlerr(__FILE__, __LINE__);
            $solved_by_arr = mysql_fetch_assoc($solved_by_rez);
            // === if problem solved print info, if not hell, print that too :-O
            $solved_in_wtf = sql_timestamp_to_unix_timestamp($arr['solved_date']) - sql_timestamp_to_unix_timestamp($arr['added']);
            $solved_in = ($arr['solved_date'] == '0000-00-00 00:00:00' ? ' [ N/A ]' : ' [ ' . round_time($solved_in_wtf) . ' ]');

            switch (true) {
                case ($arr['solved_date'] == '0000-00-00 00:00:00'):
                    $solved_color = 'pink';
                    break;
                case ($solved_in_wtf > 2 * 3600):
                    $solved_color = 'red';
                    break;
                case ($solved_in_wtf > 3600):
                    $solved_color = 'orange';
                    break;
                case ($solved_in_wtf <= 1800):
                    $solved_color = 'green';
                    break;
            }
            // =======change colors
            $count = (++$count) % 2;
            $class = 'clearalt' . ($count == 0?6:7);

            echo'<tr><td class=' . $class . '>' . $arr['added'] . '</td><td class=' . $class . '><a class=altlink href=userdetails.php?id=' . $arr['added_by'] . '>' . ($biatch_arr['username'] == '' ? 'Deleted member' : $biatch_arr['username']) . '</a></td><td class=' . $class . '><a class=altlink href=helpdesk.php?action=problems&id=' . $arr['id'] . '><b>' . htmlentities($arr['title']) . '</b></a></td>';

            switch (true) {
                case ($arr['solved'] === 'yes'):
                    echo'<td class=' . $class . '><font color=green><b>Yes</b></font>&nbsp;-&nbsp;<a class=altlink href=userdetails.php?id=' . $arr['solved_by'] . '>' . ($solved_by_arr['username'] == '' ? 'Deleted Staff' : $solved_by_arr['username']) . '</a></td>';
                    break;
                case ($arr['solved'] === 'ignored'):
                    echo'<td class=' . $class . '><font color=orange><b>Ignored</b></font>&nbsp;-&nbsp;<a class=altlink href=userdetails.php?id=' . $arr['solved_by'] . '>' . ($solved_by_arr['username'] == '' ? 'Deleted Staff' : $solved_by_arr['username']) . '</a></td>';
                    break;
                case ($arr['solved'] === 'no'):
                    echo'<td class=' . $class . '><font color=red><b>No</b></font>&nbsp;-&nbsp;N/A</td>';
                    break;
            }

            echo'<td class=' . $class . '><font color=' . $solved_color . '>' . $solved_in . '</font></td>' . (get_user_class() == UC_SYSOP ? '<td class=' . $class . ' align=center><input type=checkbox name="delhd[]" value=' . $arr['id'] . ' /></td>' : '') . '</tr>';

            echo'<tr><td align=center class=' . $class . ' colspan=' . (get_user_class() == UC_SYSOP ? 6 : 5) . '>';
        }

        echo(!$res ? '<b><p align=center><img src=pic/smilies/violin.gif> no problems at the moment. <img src=pic/smilies/violin.gif></p></b>' : '<p><a class=altlink href="javascript:SetChecked(1,\'delhd[]\')"> select all</a> - <a class=altlink href="javascript:SetChecked(0,\'delhd[]\')">un-select all</a> <input type=submit value="Delete Selected" class=button></form></p><p><form method=get action=?><input type=hidden name=action value=cleanuphd> <input type=submit value="Delete solved or ignored problems" class=button></form></p>');
        echo'<br><br><font color=green>[ xx ]</font> - great, <font color=pink>[ xx ]</font> - ok, <font color=red>[ xx ]</font> - bad </tr></table>';
    }
    // end_frame();
    end_main_frame();
    stdfoot();
    exit;
}
// Main FILE
stdhead('Help Desk');
begin_main_frame();

if ($action === 'solve') {
    $pid = 0 + $_GET['pid'];
    if ($_GET['solved'] === 'ignored' && get_user_class() >= UC_MODERATOR)
        sql_query("UPDATE helpdesk SET solved='ignored', solved_by=" . sqlesc($CURUSER['id']) . ", solved_date =" . sqlesc(get_date_time()) . " WHERE id=" . $pid) or sqlerr(__FILE__, __LINE__);
        //unset($_SESSION['h_added']);
    stdmsg('Help desk', '<b>Problem ID:</b> ' . $pid . ' <b>Ignored by: ' . $CURUSER['username'] . '</b> STATUS: <b>SOLVED</b><br>');

    end_main_frame();
    stdfoot();
    exit;
}

if (($msg_answer !== '') && ($id != 0) && (get_user_class() >= UC_MODERATOR)) {
    $added_by_rez = sql_query("SELECT username FROM users WHERE id =" . $addedbyid) or sqlerr(__FILE__, __LINE__);
    $added_by_arr = mysql_fetch_assoc($added_by_rez);
    $msg = sqlesc("Answer from Help Desk\n
Question by : " . $added_by_arr['username'] . "
" . $msg_problem . "[hr]
Answer by: " . $CURUSER['username'] . " \n" . $msg_answer . "\n\ncheers,\n" . $SITENAME . " Staff");

    sql_query("UPDATE helpdesk SET solved='yes', solved_by=" . sqlesc($CURUSER['id']) . ", solved_date = " . sqlesc(get_date_time()) . ", msg_answer = " . sqlesc($msg_answer) . " WHERE id=" . $id) or sqlerr(__FILE__, __LINE__);
    //unset($_SESSION['h_added']);
    sql_query("INSERT INTO messages (sender, subject, receiver, added, msg, poster,unread) VALUES(" . sqlesc($CURUSER['id']) . ", 'Help Desk Question Answered', " . sqlesc($addedbyid) . ", " . sqlesc(get_date_time()) . ", $msg, " . sqlesc($CURUSER['id']) . ",'yes')");
    stdmsg('Help desk', '<b>Problem ID:</b> ' . $id . ' <b>Solved by: ' . $CURUSER['username'] . '</b> STATUS: <b>SOLVED</b><br>');
    end_main_frame();
    stdfoot();
    exit;
}
if (($msg_problem !== '') && ($title !== '')) {
    sql_query("INSERT INTO helpdesk (title, msg_problem, added, added_by) VALUES (" . sqlesc($title) . ", " . sqlesc($msg_problem) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($CURUSER['id']) . ")") or sqlerr(__FILE__, __LINE__);
    stdmsg('Help desk', 'Message sent! The first available Staff member will reply.');
    end_main_frame();
    stdfoot();
    exit;
}
// === main help desk (member adds question)
echo(get_user_class() >= UC_MODERATOR ? '<center><h1><img src=pic/arrow_next.gif> <a class=altlink href=helpdesk.php?action=problems>Help Desk Questions</a> <img src=pic/arrow_prev.gif></h1></center><br/>' : '');
echo'<table border=0 align=center cellspacing=0 width=95%>' . '<tr><td align=center class=colhead colspan=2><h1>Help Desk</h1></td></tr><tr><td align=center class=clearalt6 colspan=2>' . 'Before using <b>Help Desk</b> make sure to read the <a class=altlink href=faq.php><b>FAQ</b></a> ' . 'and search <a class=altlink href=forums.php><b>Forums</b></a> first.<br>All questions that are answered there will be Ignored.<br/></td></tr>' . '<tr><td class=clearalt6 align=right><form method=post name=compose action=helpdesk.php?action=help><b>Title:</b></td><td class=clearalt6><input type=text size=73 maxlength=60 name=title></td></tr>' . '<tr><td class=clearalt6 align=right><b>Question: </b></td><td class=clearalt6><textarea name=msg_problem cols=80 rows=5></textarea></td></tr>' . '<tr><td align=center class=clearalt6 colspan=2><input type=submit value="Help me!" class=button></td></tr></table></form>';
// echo '</table>';
end_main_frame();
stdfoot();

?>

