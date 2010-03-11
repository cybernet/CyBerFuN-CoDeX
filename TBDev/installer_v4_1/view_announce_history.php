<?php
ob_start("ob_gzhandler");
require ("include/bittorrent.php");
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
$action = (isset($_GET['action']) ? $_GET['action'] : '');

stdhead();

?>
<table class=main width=750 border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>
<h2 align=center><font size=6>Announcement History</font></h2>
<?php

$query = sprintf('SELECT m.main_id, m.subject, m.body FROM announcement_main AS m ' . 'LEFT JOIN announcement_process AS p ' . 'ON m.main_id = p.main_id AND p.user_id = %s ' . 'WHERE p.status = 2',
    unsafeChar($CURUSER['id']));

$result = mysql_query($query);

$ann_list = array();

while ($x = mysql_fetch_array($result)) $ann_list[] = $x ;
unset($x);
unset($result);
reset($ann_list);

if ($action == 'read_announce') {
    $id = 0 + (isset($_GET['id']) ? $_GET['id'] : 0);

    if (!is_int($id)) {
        stdmsg('Error', 'Invalid ID');
        stdfoot();
        die();
    }

    foreach($ann_list AS $x)
    if ($x[0] == $id)
        list(, $subject, $body) = $x;

    if (empty($subject) OR empty($body)) {
        stdmsg('Error', 'ID does not exist');
        stdfoot();
        die();
    }
    begin_table();

    ?>
<table width="100%" border="0" cellpadding="4" cellspacing="0">
<tr>
<td width="50%" bgcolor="orange">Subject: <b><?php print(safe($subject));
    ?></b></td>
</tr>
<tr>
<td colspan="2" bgcolor="white"><?php print(format_comment($body));
    ?></td>
</tr>
<tr>
<td>
<a href='<?php print($_SERVER['PHP_SELF']);
    ?>'>Back</a>
</td>
</tr>
</table>
<?php
    end_table();
    stdfoot();
    die();
}
begin_table();

?>
<table align=center width='30%' border="0" cellpadding="4" cellspacing="0">
<tr>
<td align=center bgcolor="orange"><b>Subject</b></TD>
</tr>
<?php

foreach($ann_list AS $x)
print('<tr><td align=center><a href=?action=read_announce&id=' . $x[0] . '>' . safe($x[1]) . '</a></td></tr>' . "\n");

?></tr></td>
</table>
<?php
end_table();
stdfoot();

?>