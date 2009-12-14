<?php
// View the users possibilities by ALex2005 for TBDEV.net\\
include("include/bittorrent.php");
// include("include/user_functions.php");
include("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR || get_user_class() > UC_CODER) {
    stderr("Error", "Access denied!");
}

stdhead("Possibilities of users");
begin_main_frame("Possibilities of users", true);
// sorting by MarkoStamcar
$count_get = 0;

foreach ($_GET as $get_name => $get_value) {
    $get_name = mysql_escape_string(strip_tags(str_replace(array("\"", "'"), array("", ""), $get_name)));

    $get_value = mysql_escape_string(strip_tags(str_replace(array("\"", "'"), array("", ""), $get_value)));

    if ($get_name != "sort" && $get_name != "type") {
        if ($count_get > 0) {
            $oldlink = $oldlink . "&" . $get_name . "=" . $get_value;
        } else {
            $oldlink = $oldlink . $get_name . "=" . $get_value;
        }
        $count_get++;
    }
}

if ($count_get > 0) {
    $oldlink = $oldlink . "&";
}

if ($_GET['sort'] == "1") {
    if ($_GET['type'] == "desc") {
        $link1 = "asc";
    } else {
        $link1 = "desc";
    }
}

if ($_GET['sort'] == "2") {
    if ($_GET['type'] == "desc") {
        $link2 = "asc";
    } else {
        $link2 = "desc";
    }
}

if ($_GET['sort'] == "3") {
    if ($_GET['type'] == "desc") {
        $link3 = "asc";
    } else {
        $link3 = "desc";
    }
}

if ($_GET['sort'] == "4") {
    if ($_GET['type'] == "desc") {
        $link4 = "asc";
    } else {
        $link4 = "desc";
    }
}

if ($_GET['sort'] == "5") {
    if ($_GET['type'] == "desc") {
        $link5 = "asc";
    } else {
        $link5 = "desc";
    }
}

if ($_GET['sort'] == "6") {
    if ($_GET['type'] == "desc") {
        $link6 = "asc";
    } else {
        $link6 = "desc";
    }
}

if ($_GET['sort'] == "7") {
    if ($_GET['type'] == "desc") {
        $link7 = "asc";
    } else {
        $link7 = "desc";
    }
}

if ($_GET['sort'] == "8") {
    if ($_GET['type'] == "desc") {
        $link8 = "asc";
    } else {
        $link8 = "desc";
    }
}

if ($_GET['sort'] == "9") {
    if ($_GET['type'] == "desc") {
        $link9 = "asc";
    } else {
        $link9 = "desc";
    }
}

if ($_GET['sort'] == "10") {
    if ($_GET['type'] == "desc") {
        $link10 = "asc";
    } else {
        $link10 = "desc";
    }
}

if ($_GET['sort'] == "11") {
    if ($_GET['type'] == "desc") {
        $link11 = "asc";
    } else {
        $link11 = "desc";
    }
}

if ($_GET['sort'] == "12") {
    if ($_GET['type'] == "desc") {
        $link12 = "asc";
    } else {
        $link12 = "desc";
    }
}

if ($_GET['sort'] == "13") {
    if ($_GET['type'] == "desc") {
        $link13 = "asc";
    } else {
        $link13 = "desc";
    }
}

if ($link1 == "") {
    $link1 = "asc";
}
if ($link2 == "") {
    $link2 = "desc";
}
if ($link3 == "") {
    $link3 = "desc";
}

if ($link4 == "") {
    $link4 = "desc";
}

if ($link5 == "") {
    $link5 = "desc";
}

if ($link6 == "") {
    $link6 = "desc";
}

if ($link7 == "") {
    $link7 = "desc";
}

if ($link8 == "") {
    $link8 = "desc";
}

if ($link9 == "") {
    $link9 = "desc";
}

if ($link10 == "") {
    $link10 = "desc";
}

if ($link11 == "") {
    $link11 = "desc";
}

if ($link12 == "") {
    $link12 = "desc";
}

if ($link13 == "") {
    $link13 = "desc";
}

if ($_GET['sort'] && $_GET['type']) {
    $column = '';
    $ascdesc = '';

    switch ($_GET['sort']) {
        case '1': $column = 'username';
            break;
        case '2': $column = 'last_access';
            break;
        case '3': $column = 'class';
            break;
        case '4': $column = 'downloadpos';
            break;
        case '5': $column = 'uploadpos';
            break;
        case '6': $column = 'forumpost';
            break;
        case '7': $column = 'ttablehl';
            break;
        case '8': $column = 'invite_on';
            break;
        case '9': $column = 'tohp';
            break;
        case '10': $column = 'imagecats';
            break;
        case '11': $column = 'tenpercent';
            break;
        case '12': $column = 'chatpost';
            break;
        case '13': $column = 'seedbonus';
            break;
        default: $column = 'username';
            break;
    }

    switch ($_GET['type']) {
        case 'asc': $ascdesc = 'ASC';
            $linkascdesc = 'asc';
            break;
        case 'desc': $ascdesc = 'DESC';
            $linkascdesc = 'desc';
            break;
        default: $ascdesc = 'DESC';
            $linkascdesc = 'desc';
            break;
    }

    $orderby = "ORDER BY " . $column . " " . $ascdesc;
    $pagerlink = "sort=" . intval($_GET['sort']) . "&type=" . $linkascdesc . "&";
} else {
    $orderby = "ORDER BY username ASC";
    $pagerlink = "";
}

if ($count) {
    if ($addparam != "") {
        if ($pagerlink != "") {
            if ($addparam{strlen($addparam)-1} != ";") { // & = &amp;
                $addparam = $addparam . "&" . $pagerlink;
            } else {
                $addparam = $addparam . $pagerlink;
            }
        }
    } else {
        $addparam = $pagerlink;
    }
}
// end
$res = mysql_query("SELECT id, username, last_access, class, uploadpos, forumpost, downloadpos, ttablehl, tohp, imagecats, invite_on, donor, leechwarn, warned, tenpercent, chatpost, seedbonus FROM users WHERE uploadpos='no' OR forumpost='no' OR downloadpos='no' OR ttablehl='no' OR tohp='yes' OR imagecats='yes' OR invite_on='no' OR tenpercent='yes' OR chatpost='no' $orderby") or sqlerr(__FILE__, __LINE__);
$num = mysql_num_rows($res);

?>
<br><table border=0 width='100%' cellspacing=0 cellpadding=5>
<tr align=center>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=1&type=<?=$link1;
?>" title="Order by">Username</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=2&type=<?=$link2;
?>" title="Order by">Last&nbsp;seen</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=3&type=<?=$link3;
?>" title="Order by">Class</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=4&type=<?=$link4;
?>" title="Order by">Download</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=5&type=<?=$link5;
?>" title="Order by">Upload</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=6&type=<?=$link6;
?>" title="Order by">Post</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=7&type=<?=$link7;
?>" title="Order by">Highlight</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=8&type=<?=$link8;
?>" title="Order by">Invite</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=9&type=<?=$link9;
?>" title="Order by">Torrent Marquee</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=10&type=<?=$link10;
?>" title="Order by">Image cats</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=11&type=<?=$link11;
?>" title="Order by">10 %</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=12&type=<?=$link12;
?>" title="Order by">Shout</a></td>
<td class=colhead width='1%'><a href="userspos.php?<?=$oldlink;
?>sort=13&type=<?=$link13;
?>" title="Order by">SeedBonus</a></td>
</tr>
<?php
for ($i = 1; $i <= $num; $i++) {
    $arr = mysql_fetch_assoc($res);
    $last_access = substr($arr['last_access'], 0, 10);
    $class = get_user_class_name($arr["class"]);

    $download = $arr['downloadpos'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $upload = $arr['uploadpos'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $post = $arr['forumpost'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $ttablehl = $arr['ttablehl'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $invite_on = $arr['invite_on'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $tohp = $arr['tohp'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $imagecats = $arr['imagecats'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $tenpercent = $arr['tenpercent'] == 'yes' ? "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>";
    $chatpost = $arr['chatpost'] == 'no' ? "<font color=red><b>No</b></font>" : "<font color=green><b>Yes</b></font>";
    $seedbonus = ($arr["seedbonus"]);
    echo
    "<tr>
<td align=center><a href=/userdetails.php?id=" . $arr['id'] . "><b>" . $arr['username'] . "</b></a>" .
    ($arr['donor'] == "yes" ? "<img src=$pic_base_url/star.gif alt=Donor title=Donor>" : "") .
    ($arr['leechwarn'] == "yes" ? "<img src=$pic_base_url/leechwarn.gif alt=\"Leech Warned\" title=\"Leech Warned\">" : "") .
    ($arr['warned'] == "yes" ? "<img src=$pic_base_url/warned.gif alt=Warned title=Warned>" : "") . "</td>
<td align=center>$last_access</td>
<td align=center>$class</td>
<td align=center>$download</td>
<td align=center>$upload</td>
<td align=center>$post</td>
<td align=center>$ttablehl</td>
<td align=center>$invite_on</td>
<td align=center>$tohp</td>
<td align=center>$imagecats</td>
<td align=center>$tenpercent</td>
<td align=center>$chatpost</td>
<td align=center>" . safechar($seedbonus) . " </td>
</tr>\n";
}
echo "</table><p>$pagemenu<br>$browsemenu</p>";

end_main_frame();
stdfoot();

?>
