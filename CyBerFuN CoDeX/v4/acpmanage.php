<?

// CyBerFuN.Ro
// By CyBerNe7
//            //
// http://cyberfun.ro/
// http://xlist.ro/

include("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();

if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if (get_user_class() <= UC_MODERATOR)
    stderr("Sorry", "Access denied!");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ids = $_POST["ids"];

    if (!isset($ids)) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    foreach ($ids as $id)
    if (!is_valid_id($id))
        stderr('Error...', 'Invalid ID!');

    $do = safeChar($_POST['do']);

    if ($do == 'enabled')
        sql_query("UPDATE users SET enabled = 'yes' WHERE ID IN(" . join(', ', $ids) . ") AND enabled = 'no'");
    elseif ($do == 'confirm')
        sql_query("UPDATE users SET status = 'confirmed' WHERE ID IN(" . join(', ', $ids) . ") AND status = 'pending'");
    elseif ($do == 'delete')
        sql_query("DELETE FROM users WHERE ID IN(" . join(', ', $ids) . ")");
    else {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
$disabled = number_format(get_row_count("users", "WHERE enabled='no'"));
$pending = number_format(get_row_count("users", "WHERE status='pending'"));

$count = number_format(get_row_count("users", "WHERE enabled='no' OR status='pending' ORDER BY username DESC"));
$perpage = '25';
list($pagertop, $pagerbottom, $limit) = pager($perpage, $count, $_SERVER['PHP_SELF'] . "?");

$res = mysql_query("SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE enabled='no' OR status='pending' ORDER BY username DESC");

stdhead("ACP Manager");
begin_main_frame("Disabled Users: [$disabled] | Pending Users: [$pending]");

?><script language="Javascript" type="text/javascript">eval(function(p,a,c,k,e,r){e=function(c){return c.toString(a)};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('6 2="3";6 d=b 9;f e(a){c(2=="3"){4(1=0;1<a.5;1++){a[1].7=8}2="8"}g{4(1=0;1<a.5;1++){a[1].7=3}2="3"}};',17,17,'|i|checkflag|false|for|length|var|checked|true|Array||new|if|marked_row|check|function|else'.split('|'),0,{}))</script><?php

if (mysql_num_rows($res) != 0) {
    echo $pagertop;
    begin_table('', true);

    ?><form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="viewusers">
	<tr align="center">
	<td class="colhead"><input style="margin:0" type="checkbox" title='Mark All' value='Mark All' onClick="this.value=check(form);"></td>
	<td class="colhead">Username</td>
	<td class="colhead">Registered</td>
	<td class="colhead"><nobr>Last access</td>
	<td class="colhead"><nobr>Class</td>
	<td class="colhead">Downloaded</td>
	<td class="colhead">UpLoaded</td>
	<td class="colhead">Ratio</td>
	<td class="colhead">Status</td>
	<td class="colhead"><nobr>Enabled</td>
	</tr><?php
    while ($arr = mysql_fetch_assoc($res)) {
        if ($arr["downloaded"] > 0)
            $ratio = "<font color=" . get_ratio_color(number_format($arr["uploaded"] / $arr["downloaded"], 3)) . ">$ratio</font>";
        elseif ($arr["uploaded"] > 0)
            $ratio = 'Inf.';
        else
            $ratio = "---";

        $uploaded = prefixed($arr["uploaded"]);
        $downloaded = prefixed($arr["downloaded"]);
        $added = ($arr['added'] != '0000-00-00 00:00:00' ? substr($arr['added'], 0, 10) : '-');
        $last_access = ($arr['last_access'] != '0000-00-00 00:00:00' ? substr($arr['last_access'], 0, 10) : '-');
        $class = get_user_class_name($arr["class"]);

        echo
        "<tr align='center'><td><input type=\"checkbox\" name=\"ids[]\" value=\"{$arr['id']}\"></td><td><a href=/userdetails.php?id={$arr['id']}><b>{$arr['username']}</b></a>" . ($arr["donor"] == "yes" ? "<img src=pic/star.gif border=0 alt='Donor'>" : "") . ($arr["warned"] == "yes" ? "<img src=pic/warned.gif border=0 alt='Warned'>" : "") . "</td>
		<td><nobr>$added<br />(<font class='small'>" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"])) . " ago</font>)</td>
		<td><nobr>$last_access<br />(<font class='small'>" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["last_access"])) . " ago</font>)</td>
		<td>$class</td>
		<td>$downloaded</td>
		<td>$uploaded</td>
		<td>$ratio</td>
		<td>" . $arr['status'] . "</td>
		<td>" . $arr['enabled'] . "</td>
		</tr>\n";
    }
    echo "<tr><td colspan=10 align='center'><select name='do'><option value='enabled' disabled selected>What to do?</option><option value='enabled'>Enable selected</option><option value='confirm'>Confirm selected</option><option value='delete'>Delete selected</option></select><input type='submit' value='Submit'></td></tr></form>";
    end_table();
    echo $pagerbottom;
} else
    stdmsg('Sorry', 'Nothing found!');

end_main_frame();
stdfoot();
?>
