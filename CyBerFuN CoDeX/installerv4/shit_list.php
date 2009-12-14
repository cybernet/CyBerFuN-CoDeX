<?php
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
parked(); //=== uncomment if using parked mod

if (get_user_class() < UC_MODERATOR) // === change to minimum staff level
    stderr('Error', 'I smell a rat!');
// === check if action is sent (either $_POST or $_GET) if so make sure it's what you want it to be
$action = ($_POST['action'] ? $_POST['action'] : ($_GET['action'] ? $_GET['action'] : ''));
$good_stuff = array('new', 'add', 'delete');
$action = (($action && in_array($action, $good_stuff, true)) ? $action : '');
// === action: new
if ($action == 'new') {
    $shit_list_id = 0 + $_GET['shit_list_id'];
    $return_to = safechar($_GET['return_to']);

    if (!is_valid_id($shit_list_id))
        stderr('Error', 'Invalid ID');

    $res_name = mysql_query("SELECT username FROM users WHERE id=" . sqlesc($shit_list_id)) or sqlerr(__FILE__, __LINE__);
    $arr_name = mysql_fetch_assoc($res_name);

    $check_if_there = mysql_query("SELECT suspect FROM shit_list WHERE userid=$CURUSER[id] AND suspect=" . sqlesc($shit_list_id)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($check_if_there) == 1)
        stderr('Error', 'The member ' . $arr_name['username'] . ' is already on your shit list!');

    $level_of_shittyness .= '<select name=shittyness><option value=0>level of shittyness</option>';
    $i = 1;
    while ($i <= 10) {
        $level_of_shittyness .= '<option value=' . $i . '>' . $i . ' out of 10</option>';
        $i = $i + 1;
    }
    $level_of_shittyness .= '</select>';

    stdhead('New shit list entry');

    echo'<h1><img src=pic/smilies/shit.gif>Add ' . $arr_name['username'] . ' to your Shit List<img src=pic/smilies/shit.gif></h1><form method=post action=?action=add><p><table><tr><td class=colhead colspan=2>new</td></tr>' . '<tr><td align=right valign=top><b>Shittyness:</b></td><td align=left valign=top>' . $level_of_shittyness . ' just how shitty is this member?<br><br>' . '<img src=pic/smilies/shit.gif><img src=pic/smilies/shit.gif><img src=pic/smilies/shit.gif><img src=pic/smilies/shit.gif><img src=pic/smilies/shit.gif><img src=pic/smilies/shit.gif><img src=pic/smilies/shit.gif>' . ' out of 10, 1 being not so shitty, 10 being really shitty... Please select one.</td></tr>' . '<tr><td align=right valign=top><b>Reason:</b></td><td align=left valign=top><textarea cols=60 rows=5 name=text></textarea></td></tr>' . '<input type=hidden name=shit_list_id value=' . $shit_list_id . '><input type=hidden name=return_to value=' . $return_to . '>' . '<tr><td align=center colspan=2><input class=button type=submit value="add this shit bag!"></td></tr></form></table>';

    stdfoot();
    die();
}
// === action: add
if ($action == 'add') {
    $shit_list_id = 0 + $_POST['shit_list_id'];
    $shittyness = 0 + $_POST['shittyness'];
    $return_to = safechar($_POST['return_to']);

    if (!is_valid_id($shit_list_id) || !is_valid_id($shittyness))
        stderr('Error', 'Invalid ID');

    $check_if_there = mysql_query("SELECT suspect FROM shit_list WHERE userid=$CURUSER[id] AND suspect=" . sqlesc($shit_list_id)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($check_if_there) == 1)
        stderr('Error', 'That user is already on your shit list.');

    mysql_query("INSERT INTO shit_list VALUES ($CURUSER[id]," . sqlesc($shit_list_id) . ", " . sqlesc($shittyness) . ", " . sqlesc(get_date_time()) . ", " . sqlesc($_POST['text']) . ")") or sqlerr(__FILE__, __LINE__);

    $message = '<h1>Success! Member added to your personal shitlist!</h1><br><a class=altlink href=' . $return_to . '>go back to where you were?</a><br>';
}
// === action: delete
elseif ($action == 'delete') {
    $shit_list_id = 0 + $_GET['shit_list_id'];
    $sure = 0 + $_GET['sure'];

    if (!is_valid_id($shit_list_id))
        stderr("Error", "WTF?");

    $res_name = mysql_query("SELECT username FROM users WHERE id=" . sqlesc($shit_list_id)) or sqlerr(__FILE__, __LINE__);
    $arr_name = mysql_fetch_assoc($res_name);

    if (!$sure)
        stderr('Delete ' . $arr_name['username'] . ' from shit list', 'Do you really want to delete <b>' . $arr_name['username'] . '</b> from your shit list? <br><a class=altlink href=?action=delete&shit_list_id=' . $shit_list_id . '&sure=1>here</a> if you are sure.');

    mysql_query("DELETE FROM shit_list WHERE userid=$CURUSER[id] AND suspect=" . sqlesc($shit_list_id)) or sqlerr(__FILE__, __LINE__);
    if (mysql_affected_rows() == 0)
        stderr('Error', 'No member found to delete!');

    $message = '<h1>Success! <b>' . $arr_name['username'] . '</b> deleted from your shit list!</h1>';
}
// === default page
stdhead("Shit list for " . $CURUSER['username']);

echo $message . '<br><table width=750 border=0 cellspacing=0 cellpadding=0><tr><td class=colhead align=center valigh=top><h1>Shit List for ' . $CURUSER['username'] . '</h1>' . '<img src=pic/smilies/shit.gif alt=shit> shittiest at the top <img src=pic/smilies/shit.gif alt=shit></td></tr>' . '<tr><td class=embedded><table width=750 border=1 cellspacing=0 cellpadding=5><tr><td>';

$i = 0;

$res = mysql_query("SELECT s.suspect as id, s.text, s.shittyness, s.added AS shit_list_added, u.username AS name, u.added, u.class, u.avatar, u.donor, u.warned, u.enabled, u.last_access FROM shit_list AS s LEFT JOIN users as u ON s.suspect = u.id WHERE userid=$CURUSER[id] ORDER BY shittyness DESC") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) == 0)
    $shit_list = '<p align=center>Your shit list is empty.</p>';
else
    while ($shit_list = mysql_fetch_array($res)) {
    $class_name = get_user_class_name($shit_list['class']);

    $poop = 1;
    while ($poop <= $shit_list['shittyness']):
    $shit .= ' <img src=pic/smilies/shit.gif title="' . $shit_list['shittyness'] . ' out of 10 on the sittyness scale">';
    $poop++;
    endwhile;

    $main = '<a class=altlink href=userdetails.php?id=' . $shit_list['id'] . '><b>' . $shit_list['name'] . '</b></a>' .

    get_user_icons($shit_list) . '<b> [ ' . $class_name . ' ]</b> ' . $shit . '<br><b>joined:</b> ' . $shit_list['added'] . ' [ ' . get_elapsed_time(sql_timestamp_to_unix_timestamp($shit_list['added'])) . ' ago ]' . '<br><b>added to shit list:</b> ' . $shit_list['shit_list_added'] . ' [ ' . get_elapsed_time(sql_timestamp_to_unix_timestamp($shit_list['shit_list_added'])) . ' ago ]<br>' . '<b>last seen:</b> ' . display_date_time($shit_list['last_access']) . ' [ ' . get_elapsed_time(sql_timestamp_to_unix_timestamp($shit_list['last_access'])) . ' ago ]<hr>' . format_comment($shit_list['text']);

    $buttons = '<br><a class=altlink href=?action=delete&shit_list_id=' . $shit_list['id'] . '>remove</a>' . '<br><br><a class=altlink href=sendmessage.php?receiver=' . $shit_list['id'] . '>PM</a>';

    $avatar = (!$shit_list['avatar'] ? 'pic/default_avatar.gif' : safechar($shit_list['avatar']));
    $avatar = ($CURUSER['avatars'] == 'yes' ? $avatar : '');

    echo(($i % 2 == 0) ? '<table width=100% hight=100%><tr><td width=50% align=center>' : '<td width=50% align=center hight=100%>');

    echo'<table width=100% hight=100%><tr valign=top><td width=80 align=center valign=top>' .
    ($avatar ? '<img width=80px src=' . $avatar . '>' : '') . '</td><td><table width=420 hight=100%><tr><td class=embedded>' . $main . '</td>' . '<td class=embedded valign=top align=center>' . $buttons . '</td></tr></table></td></tr></td></tr></table>';

    echo(($i % 2 == 1) ? '</td></tr></table>' : '</td>');

    $shit = '';
    $i++;
}

echo(($i % 2 == 1) ? '<td width=50%>&nbsp;</td></tr></table>' : '') . $shit_list . '</td></tr></table>' . '</td></tr></table><p align=center><a class=altlink href=users.php><b>Find User/Browse User List</b></a></p>';
stdfoot();

?>