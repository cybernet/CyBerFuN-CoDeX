<?php
require "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
dbconn(false);
maxcoder();
if (!logged_in() OR $CURUSER['class'] < UC_ADMINISTRATOR) {
    header("HTTP/1.0 404 Not Found");
    // Change the following message to match the 404 message found on your server.
    print("<html><h1>Not Found</h1><p>The requested URL {$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // The expiry days.
    $days = array(
        array(7, '7 Days'),
        array(14, '14 Days'),
        array(21, '21 Days'),
        array(28, '28 Days'),
        array(56, '2 Months'));
    // usersearch POST data...
    $n_pms = (isset($_POST['n_pms']) ? $_POST['n_pms'] : 0);
    $ann_query = (isset($_POST['ann_query']) ? trim($_POST['ann_query']) : '');
    $ann_hash = (isset($_POST['ann_hash']) ? trim($_POST['ann_hash']) : '');

    if (hashit($ann_query, $n_pms) != $ann_hash) die(); // Validate POST...

    if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $ann_query)) stderr('Error', 'Misformed Query');
    if (!$n_pms) stderr('Error', 'No recipients');
    // Preview POST data ...
    $body = trim((isset($_POST['msg']) ? $_POST['msg'] : ''));
    $subject = trim((isset($_POST['subject']) ? $_POST['subject'] : ''));
    $expiry = 0 + (isset($_POST['expiry']) ? $_POST['expiry'] : 0);

    if ((isset($_POST['buttonval']) AND $_POST['buttonval'] == 'Submit')) {
        // Check values before inserting into row...
        if (empty($body)) stderr('Error', 'No body to announcement');
        if (empty($subject)) stderr('Error', 'No subject to announcement');

        unset($flag);
        reset($days);
        foreach($days as $x)
        if ($expiry == $x[0]) $flag = 1;

        if (!isset($flag)) stderr('Error', 'Invalid expiry selection');

        $expires = get_date_time((strtotime(get_date_time()) + (86400 * $expiry))); // 86400 seconds in one day.
        $created = get_date_time();

        $query = sprintf('INSERT INTO announcement_main ' . '(owner_id, created, expires, sql_query, subject, body) ' . 'VALUES (%s, %s, %s, %s, %s, %s)',
            sqlesc($CURUSER['id']),
            sqlesc($created),
            sqlesc($expires),
            sqlesc($ann_query),
            sqlesc($subject),
            sqlesc($body));

        mysql_query($query);

        if (mysql_affected_rows())
            stderr('Success', 'Announcement was successfully created');

        stderr('Error', 'Contact an administrator');
    }

    stdhead("Create Announcement", false);

    ?>
<table class=main width=750 border=0 cellspacing=0 cellpadding=0>
<tr><td class=embedded><div align=center>
<h1>Create Announcement for <?php print($n_pms);
    ?> user<?php print(($n_pms > 1 ? 's': ''));
    ?>!</h1>
<form name=compose method=post action=new_announcement.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr>
<td colspan="2"><b>Subject: </b>
<input name="subject" type="text" size="76" value='<?php print(safe($subject));
    ?>' ></td>
</tr>
<tr><td colspan="2"><div align="center">
<?php textbbcode("compose", "msg", $body);
    ?>
<!-- The following line is for backward compatability with scripts that don't
have the textbbcode() function installed in the global.php script -->
<!--<textarea name=msg cols=80 rows=15><?php print(safe($body));
    ?></textarea>-->

</div></td></tr>
<tr><td colspan="2" align=center>

<select name="expiry">

<?php
    reset($days);
    foreach($days as $x)
    print('<option value="' . $x[0] . '"' . (($expiry == $x[0] ? ' selected' : '')) . '>' . $x[1] . '</option>');

    ?>
</select>

<input type=submit name='buttonval' value="Preview" class=btn>
<input type=submit name='buttonval' value="Submit" class=btn>
</td></tr></table>
<input type=hidden name=n_pms value="<?php print($n_pms);
    ?>">
<input type=hidden name=ann_query value="<?php print($ann_query);
    ?>">
<input type=hidden name=ann_hash value="<?php print($ann_hash);
    ?>">
</form><br><br>
</div></td></tr></table>

<?php

    if ($body) {
        $newtime = (strtotime(get_date_time()) + (86400 * $expiry));

        ?>
<table width=700 class=main border=0 cellspacing=1 cellpadding=1><tr><td class=embedded>
<tr><td bgcolor=#663366 align=center valign=center><h2><font color=white>Announcement:
<?php print(safe($subject));
        ?></h2></td></tr>
<tr><td class=text>
<?php
        print(format_comment($body) . '<br /><hr />' . 'Expires: ' . get_date_time($newtime));

        ?>
</td></tr></table>
<?php
    }
} else { // Shouldn't be here
    header("HTTP/1.0 404 Not Found");
    print("<html><h1>Not Found</h1><p>The requested URL {$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
stdfoot();

?>