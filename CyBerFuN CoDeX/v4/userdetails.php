<?php
$page_find = 'userdetails';
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
ini_set("display_errors", 0); 
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
function bark($msg)
{
    stdhead();
    stdmsg("Error", $msg);
    stdfoot();
    exit;
}

$speed = array('1' => '64kbps', '2' => '96kbps', '3' => '128kbps', '4' =>
    '150kbps', '5' => '256kbps', '6' => '512kbps', '7' => '768kbps', '8' => '1Mbps',
    '9' => '1.5Mbps', '10' => '2Mbps', '11' => '3Mbps', '12' => '4Mbps', '13' =>
    '5Mbps', '14' => '6Mbps', '15' => '7Mbps', '16' => '8Mbps', '17' => '9Mbps',
    '18' => '10Mbps', '19' => '48Mbps', '20' => '100Mbit');
function snatchtable($res)
{
    $table = "<table class=main border=1 cellspacing=0 cellpadding=5>
<tr>
<td class=colhead>Category</td>
<td class=colhead>Torrent</td>
<td class=colhead>S.id</td>
<td class=colhead>Up.</td>
<td class=colhead>Rate</td>
<td class=colhead>Downl.</td>
<td class=colhead>Rate</td>
<td class=colhead>Ratio</td>
<td class=colhead>Activity</td>
<td class=colhead>Finished</td>
</tr>";

    while ($arr = mysql_fetch_assoc($res)) {
        $upspeed = ($arr["upspeed"] > 0 ? prefixed($arr["upspeed"]) : ($arr["seedtime"] >
            0 ? prefixed($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) :
            prefixed(0)));
        $downspeed = ($arr["downspeed"] > 0 ? prefixed($arr["downspeed"]) : ($arr["leechtime"] >
            0 ? prefixed($arr["downloaded"] / $arr["leechtime"]) : prefixed(0)));
        $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"],
            3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
        $id = $arr["id"];
        $table .= "<tr>
<td style='padding: 0px'><img src='pic/" . safeChar($arr["catimg"]) . "' alt='" .
            safeChar($arr["catname"]) . "' width=42 height=42></td>
<td><a href=details.php?id=$arr[torrentid]><b>" . (strlen($arr["name"]) > 50 ?
            substr($arr["name"], 0, 50 - 3) . "..." : $arr["name"]) . "</b></a></td>
<td>" . ($arr["id"]) . "</td>
<td>" . prefixed($arr["uploaded"]) . "</td>
<td>$upspeed/s</td>
<td>" . prefixed($arr["downloaded"]) . "</td>
<td>$downspeed/s</td>
<td>$ratio</td>
<td>" . mkprettytime($arr["seedtime"] + $arr["leechtime"]) . "</td>
<td>" . ($arr["complete_date"] <> "0000-00-00 00:00:00" ?
            "<font color=green><b>Yes</b></font>" : "<font color=red><b>No</b></font>") .
            "</td>
</tr>\n";
    }
    $table .= "</table>\n";

    return $table;
}

function maketable($res)
{
    global $CURUSER;
    $ret = "<table class=main border=1 cellspacing=0 cellpadding=5>" .
        "<tr><td class=colhead align=center>Type</td><td class=colhead>Name</td><td class=colhead align=center>Size</td><td class=colhead align=right>Se.</td><td class=colhead align=right>Le.</td><td class=colhead align=center>Upl.</td>\n" .
        "<td class=colhead align=center>Downl.</td><td class=colhead align=center>Ratio</td></tr>\n";
    while ($arr = mysql_fetch_assoc($res)) {
        $ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded'] / $CURUSER['downloaded'] :
            0;
        $ratio = number_format($ratio, 3);

        $color = get_ratio_color($ratio);

        if ($color)
            $ratio = "<font color=$color>$ratio</font>";
        $catimage = safeChar($arr["image"]);
        $catname = safeChar($arr["catname"]);
        $size = str_replace(" ", "<br>", prefixed($arr["size"]));
        $uploaded = str_replace(" ", "<br>", prefixed($arr["uploaded"]));
        $downloaded = str_replace(" ", "<br>", prefixed($arr["downloaded"]));
        $seeders = number_format($arr["seeders"]);
        $leechers = number_format($arr["leechers"]);
        $ret .= "<tr><td style='padding: 0px'><img src=\"pic/$catimage\" alt=\"$catname\" width=42 height=42></td>\n" .
            "<td><a href=details.php?id=$arr[torrent]&amp;hit=1><b>" . safeChar($arr["torrentname"]) .
            "</b></a></td><td align=center>$size</td><td align=right>$seeders</td><td align=right>$leechers</td><td align=center>$uploaded</td>\n" .
            "<td align=center>$downloaded</td><td align=center>$ratio</td></tr>\n";
    }
    $ret .= "</table>\n";
    return $ret;
}
function usercommenttable($rows)
{
    global $CURUSER, $pic_base_url, $userid;
    begin_main_frame();
    begin_frame();
    $count = 0;
    foreach ($rows as $row) {
        echo ("<p class=sub>#" . $row["id"] . " by ");
        if (isset($row["username"])) {
            $title = $row["title"];
            if ($title == "")
                $title = get_user_class_name($row["class"]);
            else
                $title = safeChar($title);
            echo ("<a name=comm" . $row["id"] . " href=userdetails.php?id=" . $row["user"] .
                "><b>" . safeChar($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ?
                "<img src=\"{$pic_base_url}star.gif\" alt='Donor'>" : "") . ($row["warned"] ==
                "yes" ? "<img src=" . "\"{$pic_base_url}warned.gif\" alt=\"Warned\">" : "") .
                " ($title)\n");
        } else
            echo ("<a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a>\n");

        echo (" at " . $row["added"] . " GMT" . ($userid == $CURUSER["id"] || $row["user"] ==
            $CURUSER["id"] || get_user_class() >= UC_MODERATOR ?
            "- [<a href=usercomment.php?action=edit&amp;cid=$row[id]>Edit</a>]" : "") . ($userid ==
            $CURUSER["id"] || get_user_class() >= UC_MODERATOR ?
            "- [<a href=usercomment.php?action=delete&amp;cid=$row[id]>Delete</a>]" : "") .
            ($row["editedby"] && get_user_class() >= UC_MODERATOR ?
            "- [<a href=usercomment.php?action=vieworiginal&amp;cid=$row[id]>View original</a>]" :
            "") . "</p>\n");
        $avatar = ($CURUSER["avatars"] == "yes" ? safeChar($row["avatar"]) : "");
        $text = format_comment($row["text"]);
        if ($row["editedby"])
            $text .= "<p><font size=1 class=small>Last edited by <a href=userdetails.php?id=$row[editedby]><b>$row[username]</b></a> at $row[editedat] GMT</font></p>\n";
        begin_table(true);
        echo ("<tr valign=top>\n");
        echo ("<td align=center width=150 style='padding: 0px'><img width=150 src=\"{$avatar}\"></td>\n");
        echo ("<td class=text>$text</td>\n");
        echo ("</tr>\n");
        end_table();
    }
    end_frame();
    end_main_frame();
}

if (isset($_GET['id']) && ($id = 0 + $_GET['id']) > 0) {
    if (!is_valid_id($id))
        bark("Bad ID $id.");
    else
        $where = "id=$id";
} elseif (isset($_GET['username']) && ($username = $_GET['username']) != "") {
    if (!validusername($username))
        bark("Invalid Username");
    else
        $where = "username=" . unsafeChar($username);
} else
    bark("Unknown User");

$r = sql_query("SELECT port,agent FROM peers WHERE id=".unsafeChar($CURUSER["id"])." LIMIT 1") or sqlerr(__file__, __line__);
if(mysql_num_rows($r) == 1)
{
	$a = mysql_fetch_assoc($r);
	if(($a["port"] != $user["port"]) || $a["agent"] != $user["agent"])
	sql_query("UPDATE users set port=".$a["port"]." , agent=".unsafeChar($a["agent"])." WHERE id=".$user["id"]."") or sqlerr(__file__, __line__);
}

$r2 = @sql_query("SELECT *, UNIX_TIMESTAMP(added) as addednix FROM users WHERE id=$id") or sqlerr(__FILE__, __LINE__);
$user2 = mysql_fetch_array($r2) or bark("No user with ID $id.");

$r = @sql_query("SELECT * FROM users WHERE $where LIMIT 1") or sqlerr();
$user = mysql_fetch_assoc($r) or bark("Unknown User");
$id = (int)$user['id'];
// $res = sql_query("SELECT COUNT(*) FROM userhits WHERE hitid = ".unsafeChar($id)."") or sqlerr(); // *1
// $row = mysql_fetch_row($res); // *1
// $userhits = $row[0]; // *1
if (!$_GET["hit"] && $CURUSER["id"] <> $user["id"]) {
    //$res = sql_query("SELECT added FROM userhits WHERE userid = $CURUSER[id] AND hitid = $id LIMIT 1") or
       // sqlerr(); // *3
    $res = sql_query("SELECT added FROM userhits WHERE userid = $CURUSER[id] AND hitid = ".unsafeChar($id)." LIMIT 1") or
        sqlerr(); // *3
    $row = mysql_fetch_row($res); // *3
    if ($row[0] > get_date_time(gmtime() - 3600)) { // *3
        header("Location: $BASEURL$_SERVER[REQUEST_URI]&hit=1"); // *3
    } else { // *3
        // $hitnumber = $userhits + 1; // *1
        $hitnumber = $user["hits"] + 1; // *2
        sql_query("UPDATE users SET hits = hits + 1 WHERE id = ".unsafeChar($id)."") or sqlerr(); // *2
        sql_query("INSERT INTO userhits (userid, hitid, number, added) VALUES(".unsafeChar($CURUSER[id]).", ".unsafeChar($id).", ".unsafeChar($hitnumber).", '" .unsafeChar(get_date_time()) . "')") or sqlerr();
        header("Location: $BASEURL$_SERVER[REQUEST_URI]&hit=1");
    } // *3
}
// *1 = comment this out if you use the cleanup code
// *2 = comment this out if you do NOT use the cleanup code
// *3 = comment this out if you do NOT want hits to be added only once every hour
if ($user["status"] == "pending")
    die;
$r = sql_query("SELECT id, name, seeders, leechers, category FROM torrents WHERE owner=".unsafeChar($id)." ORDER BY name") or
    sqlerr();
if (mysql_num_rows($r) > 0) {
    $torrents = "<table class=main border=1 cellspacing=0 cellpadding=5>\n" .
        "<tr><td class=colhead>Type</td><td class=colhead>Name</td><td class=colhead>Seeders</td><td class=colhead>Leechers</td></tr>\n";
    while ($a = mysql_fetch_assoc($r)) {
        $r2 = sql_query("SELECT name, image FROM categories WHERE id=".unsafeChar($a[category])."") or
            sqlerr(__file__, __line__);
        $a2 = mysql_fetch_assoc($r2);
        $cat = "<img src=\"/pic/$a2[image]\" alt=\"$a2[name]\">";
        $torrents .= "<tr><td style='padding: 0px'>$cat</td><td><a href=details.php?id=" .
            $a["id"] . "&hit=1><b>" . safeChar($a["name"]) . "</b></a></td>" .
            "<td align=right>$a[seeders]</td><td align=right>$a[leechers]</td></tr>\n";
    }
    $torrents .= "</table>";
}
// ////////////////////
function gethostbyaddr_timeout($ip, $timeout = 2)
{
    $host = `host -W $timeout $ip`;
    if (preg_match('`in-addr.arpa domain name pointer (.*)\.\n$`i', $host, $matches))
        $host = $matches[1];
    else
        $host = $ip;
    return $host;
}
// //////////////
// ///////////////
if ($user['ip'] && (get_user_class() >= UC_MODERATOR || $user['id'] == $CURUSER['id'])) {
    $ip = $user['ip'];
    $dom = gethostbyaddr_timeout($ip);
    if ($dom == $ip)
        $addr = '<a href="whois.php?query=' . $ip . '">' . $ip . '</a>';
    else {
        $dom = htmlentities($dom, ENT_QUOTES);
        $addr = '<a href="whois.php?query=' . $ip . '">' . $ip . '</a> (' . $dom . ')';
    }
}
// /////////////////////////////
    $joindate = ($user['added'] == '0000-00-00 00:00:00' ? 'N/A' : $user['added'] . ' (' . get_elapsed_time(sql_timestamp_to_unix_timestamp($user['added'])) . ' ago)');
    $lastseen = ($user['last_access'] == '0000-00-00 00:00:00' ? 'Never' : $user['last_access'] . ' (' . get_elapsed_time(sql_timestamp_to_unix_timestamp($user['last_access'])) . ' ago)');
    $lapsetime = ((($lapsetime = time() - sql_timestamp_to_unix_timestamp($user["last_login"])) /
        3600) % 24) . ' Hours ' . (($lapsetime / 60) % 60) . ' minutes ' . ($lapsetime %
        60) . ' seconds ';
    $onlinetime = "$user[last_login] (" . get_date_time(sql_timestamp_to_unix_timestamp
        ($user["last_access"])) . ")";

$res = sql_query("SELECT COUNT(*) FROM comments WHERE user=" . unsafeChar($user["id"])) or
    sqlerr();
$arr3 = mysql_fetch_row($res);
$torrentcomments = $arr3[0];
$res = sql_query("SELECT COUNT(*) FROM posts WHERE userid=" . unsafeChar($user["id"])) or
    sqlerr();
$arr3 = mysql_fetch_row($res);
$forumposts = $arr3[0];

$country = '';
include 'include/cache/countries.php';
foreach ($countries as $country)
    if ($country["id"] == $user["country"]) {
        $country = "<td class=embedded><img src=\"{$pic_base_url}flag/{$country['flagpic']}\" alt=\"" .
            $country['name'] . "\" style='margin-left: 8pt'></td>";
        break;
    }
$ipto = sql_query("SELECT COUNT(id) FROM `users` AS iplist WHERE `ip` = '" .
    unsafeChar($user["ip"]) . "'") or sqlerr(__file__, __line__);
$row12 = mysql_fetch_row($ipto);
$ipuse = $row12[0];
$ip = '';
if ($ipuse == 1) {
    $use = "";
} else
    if (get_user_class() >= UC_MODERATOR) {        {
            $ipcheck = $user["ip"];
            $use = "<b>(<font color=red>Warning :</font> <a href=whois.php?ip=$ipcheck>This IP is used by $ipuse users!</a>)</b>";
        }
    }

$whoisurl = "redir.php?url=http://www.ripe.net/perl/whois?form_type=simple&full_query_string=&searchtext=$ip";
$warned = '';
if ($user["warned"] == "yes")
    $warned = "<td class=embedded><img src=" . $pic_base_url .
        "warned.gif alt='Warned' title=\"Warned\"/ style='margin-left: 4pt'></td>";
$donor = '';
if ($user["donor"] == "yes")
    $donor = "<td class=embedded><img src=" . $pic_base_url .
        "star.gif alt='Donor' title=\"Donor\"/ style='margin-left: 4pt'></td>";
$chatpost = '';
if ($user["chatpost"] == "no")
    $chatpost = "<td class=embedded><img src=" . $pic_base_url .
        "chatpos.gif alt='no chat' title=\"Chat Disabled\"/ style='margin-left: 4pt'></td>";
$downloadpos = '';
if ($user["downloadpos"] == "no")
    $downloadpos = "<td class=embedded><img src=" . $pic_base_url .
        "downloadpos.gif alt='no download' title=\"Download Disabled\"/ style='margin-left: 4pt'></td>";
$forumpost = '';
if ($user["forumpost"] == "no")
    $forumpost = "<td class=embedded><img src=" . $pic_base_url .
        "forumpost.gif alt='no posting' title=\"Posting Disabled\"/ style='margin-left: 4pt'></td>";
$uploadpos = '';
if ($user["uploadpos"] == "no")
    $uploadpos = "<td class=embedded><img src=" . $pic_base_url .
        "uploadpos.gif alt='no uploads' title=\"Upload Disabled\"/ style='margin-left: 4pt'></td>";
$parked = '';
if ($user["parked"] == "yes")
    $parked = "<td class=embedded><img src=" . $pic_base_url .
        "parked.gif alt='Account Parked' title=\"User Parked\"/ style='margin-left: 4pt'></td>";
$anonymous = '';
if ($user["anonymous"] == "yes")
    $anonymous = "<td class=embedded><img src=" . $pic_base_url .
        "anonymous.gif alt='Anonymous User' title=\"User Anonymous\"/ style='margin-left: 4pt'></td>";
$gender = '';
if ($user["gender"] == "Male")
    $gender = "<td class=embedded><img src=" . $pic_base_url .
        "male.gif alt='Male'  style='margin-left: 4pt'></td>";
if ($user["gender"] == "Female")
    $gender = "<td class=embedded><img src=" . $pic_base_url .
        "female.gif alt='Female' style='margin-left: 4pt'></td>";
if ($user["gender"] == "N/A")
    $gender = "<td class=embedded><img src=" . $pic_base_url .
        "na.gif alt='N/A' style='margin-left: 4pt'></td>";

$res = sql_query("SELECT torrent,added,uploaded,downloaded,torrents.name as torrentname,categories.name as catname,size,image,category,seeders,leechers FROM peers LEFT JOIN torrents ON peers.torrent = torrents.id LEFT JOIN categories ON torrents.category = categories.id WHERE userid=".unsafeChar($id)." AND seeder='no'") or
    sqlerr();
if (mysql_num_rows($res) > 0)
    $leeching = maketable($res);
$res = sql_query("SELECT torrent,added,uploaded,downloaded,torrents.name as torrentname,categories.name as catname,size,image,category,seeders,leechers FROM peers LEFT JOIN torrents ON peers.torrent = torrents.id LEFT JOIN categories ON torrents.category = categories.id WHERE userid=".unsafeChar($id)." AND seeder='yes'") or
    sqlerr();
if (mysql_num_rows($res) > 0)
    $seeding = maketable($res);
// /////////////// Birthday mod /////////////////////
if ($user['birthday'] != "0000-00-00") {
    // $current = gmdate("Y-m-d", time());
    $current = gmdate("Y-m-d", time() + $CURUSER['tzoffset'] * 60);
    list($year2, $month2, $day2) = split('-', $current);
    $birthday = $user["birthday"];
    $birthday = date("Y-m-d", strtotime($birthday));
    list($year1, $month1, $day1) = split('-', $birthday);
    if ($month2 < $month1) {
        $age = $year2 - $year1 - 1;
    }
    if ($month2 == $month1) {
        if ($day2 < $day1) {
            $age = $year2 - $year1 - 1;
        } else {
            $age = $year2 - $year1;
        }
    }
    if ($month2 > $month1) {
        $age = $year2 - $year1;
    }
}
// /////////////// Birthday mod/////////////////////
stdhead("Details for " . $user["username"]);
$enabled = $user["enabled"] == 'yes';
echo ("<p><table class=main border=0 cellspacing=0 cellpadding=0>" .
    "<tr><td class=embedded><h1 style='margin:0px'>$user[username]" . get_user_icons
    ($user, true) . "</h1></td>$gender$parked$anonymous$chatpost$downloadpos$uploadpos$forumpost$country</tr></table></p>\n");

if (!$enabled)
    echo ("<p><b>This account has been disabled</b></p>\n");
if (($user["showfriends"] == "yes") || get_user_class() >= UC_USER) {
    if ($CURUSER["id"] <> $user["id"]) {
        echo ("<p>(<a href=" . $DEFAULTBASEURL . "/userfriends.php?id=$id>add comment</a>)");
        $r = sql_query("SELECT id, friendid FROM friends WHERE (userid=$CURUSER[id] OR userid=$id) AND (friendid=$id OR friendid=$CURUSER[id])") or
            sqlerr(__file__, __line__);
        $friend = mysql_num_rows($r);
        $r = sql_query("SELECT id FROM blocks WHERE userid=$CURUSER[id] AND blockid=$id") or
            sqlerr(__file__, __line__);
        $block = mysql_num_rows($r);

        if ($friend)
            echo (" - (<a href=" . $DEFAULTBASEURL .
                "/friends.php?action=delete&type=friend&targetid=$id>remove from friends</a>)\n");
        elseif ($block)
            echo (" - (<a href=" . $DEFAULTBASEURL .
                "/friends.php?action=delete&type=block&targetid=$id>remove from blocks</a>)\n");
        else {
            $rq = sql_query("SELECT id, friendid FROM friends WHERE userid=$CURUSER[id] AND friendid=$id AND confirmed='no'") or
                sqlerr(__file__, __line__);
            $con = mysql_num_rows($rq);
            if ($con)
                echo (" - (<a href=" . $DEFAULTBASEURL .
                    "/friends.php#friendreqs>friend is pending</a>)");
            else
                echo (" - (<a href=" . $DEFAULTBASEURL .
                    "/friends.php?action=add&type=friend&targetid=$id>add to friends</a>)");
            echo (" - (<a href=" . $DEFAULTBASEURL .
                "/friends.php?action=add&type=block&targetid=$id>add to blocks</a>)");
        }
        if ($user["showfriends"] == "yes")
            echo (" - (<a href=" . $DEFAULTBASEURL . "/userfriends.php?id=$id>view friends</a>)\n");
        echo ("</p>\n");
    }
}
if ($CURUSER['id'] != $user['id'])
    echo (" - (<a href=/sharemarks.php?id=$id>view sharemarks</a>)</p>\n");
if ($CURUSER['class'] >= UC_MODERATOR)
    echo ' - (<a href=\'userimages.php?user=' . $user['username'] . '\'>View upload images</a>)';
if ($user["anonymous"] == 'yes' && $CURUSER['class'] < UC_VIP) {
    echo ("<table width=\"750\" border=1 cellspacing=0 cellpadding=5 class=main>");
    echo ("<tr><td colspan=\"2\" align=\"center\">".$language['anon1']."</td></tr>");
    if ($user["avatar"])
        echo ("<tr><td class=rowhead>".$language['avatar']."</td><td align=left><a href=\"" . safeChar($user["avatar"]) .
            "\" rel='lightbox' title=\"" . safeChar($user["username"]) . "\" class=\"borderimage\" onMouseover=\"borderit(this,'black')\" onMouseout=\"borderit(this,'silver')\"><img src=\"" .
            safeChar($user["avatar"]) . "\" width=150 title=\"" . safeChar($user["username"]) .
            "\"></a></td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['class']."</td><td align=left><font color='#" .
        get_user_class_color($user['class']) . "'> " . get_user_class_name($user['class']) .
        "  <img src=" . get_user_class_image($user["class"]) . " alt=" .
        get_user_class_name($user["class"]) . "> | " . safeChar($user["title"]) .
        "</td></tr>\n");
    if ($user["info"])
        echo ("<tr valign=top><td align=left colspan=2 class=text bgcolor=\"#777777\">" .
            format_comment($user["info"]) . "</td></tr>\n");
    echo ("<tr><td colspan=2 align=center><form method=get action=sendmessage.php><input type=hidden name=receiver value=" .
        $user["id"] . "><input type=submit value=\"Send message\" style='height: 23px'></form></td></tr>");
    if (get_user_class() < UC_MODERATOR && $user["id"] != $CURUSER["id"]) {
        echo ("</table>");
        end_main_frame();
        exit;
    }
    echo ("</table><br>");
}
// ===donor count down
if ($user["donor"] && $CURUSER["id"] == $user["id"] || get_user_class() >=
    UC_SYSOP) {
    $donoruntil = $user['donoruntil'];
    if ($donoruntil == '0000-00-00 00:00:00')
        echo ("");
    else {
        echo ("<b><p>Donated Status Until - $donoruntil");
        echo (" [ " . mkprettytime(strtotime($donoruntil) - gmtime()) .
            " ] to go...</b><font size=\"-2\"> to re-new donation click <a class=altlink href=donate.php>here</a>.</font></p>\n");
    }
}
// ====end
// //Get H&R Total ////
$hitruns = number_format($user["hit_and_run_total"]);
// /////////////
if ($CURUSER['id'] == $user['id'])
    echo ('<h3><a href=' . $DEFAULTBASEURL . '/usercp.php>Edit My Profile</a></h3>' .
        '<h3><a href=' . $DEFAULTBASEURL .
        '/friends.php#pending>Unconfirmed Friends</a></h3>' . '<h3><a href=\'view_announce_history.php\'>View My Announcements</a></h3>');
begin_main_frame();

?>
<table width=100% border=1 cellspacing=0 cellpadding=5>
<?php
/* flush all torrents mod */
if ($user["id"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR) {
    $un = $user["username"];

?>
<tr><td class=rowhead width=1%><?php echo $language['flush'];?></td><td align=left width=99%><?php echo ("<h0>Flush&nbsp;Torrents, <a href=flush.php?id=$id>$un</a> ! Please note abuse will be flagged instantly - All flushes are logged !</h0>\n");
?></td></tr>
<tr><td class=rowhead width=1%><?php echo $language['jdate'];?></td><td align=left width=99%><?= $joindate ?></td></tr>
<tr><td class=rowhead><?php echo $language['lseen'];?></td><td align=left><?= $lastseen ?></td></tr>
<tr><td class=rowhead width=1%><?php echo $language['elogin'];?></td><td align=left width=99%><?= safeChar($onlinetime) ?></td></tr>
<tr><td class=rowhead width=1%><?php echo $language['ttonline'];?></td><td align=left width=99%><?= safeChar($lapsetime) ?></td></tr>
<?php }
if ($user["id"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR) {
    if ($user['port'] != 0) {
?>

<tr><td class=rowhead><?php echo $language['port'];?></font></td><td class=tablea align=left><?= safeChar($user['port']) ?></td></tr>
<tr><td class=rowhead><?php echo $language['client'];?></font></td><td class=tablea align=left><?= safeChar($user['agent']) ?></td></tr>
<?php }
}
/////////////////time online code - softmp3 source////
function tonline($tid, $nix) {
global $language;
if($nix > 1168470000)
$days = floor((time() - $nix)/86400);
else
$days = floor((time() - 1168470000)/86400);
if($days==0)
$days=1;
$tid = $tid/$days;
if($tid<3600)
return number_format($tid/60,0)." minutes";
while($tid >= 3600){
$hrs++;
$tid-=3600;
}
$tid=($tid/60);
$tid = number_format($tid,0);
if($hrs>1)
return $hrs. " ".$language['hours']." ".$tid." ".$language['minutes'];
else
return $hrs. " ".$language['hour']." ".$tid." ".$language['minutes'];
}
if($user2["uptime"]>0){
print("<tr><td class=rowhead>".$language['activity']."</td>");
print("<td align=left>".$language['activity2']." ".tonline($user2["uptime"], $user2["addednix"])." ".$language['activity3']."</td></tr>\n");
}
//////////////////where is user now ////////////////
$dt = sqlesc(get_date_time(gmtime() - 180)); //-- 3 minutes without activity
if ($user["last_access"] < $dt)
  echo("<tr><td class=tableb>Now active in</td><td class=tablea>(No user activity)</td></tr>\n");
else
  echo("<tr><td class=tableb>Now active in</td><td class=tablea>". $user["page_now"] ."</td></tr>\n");
if (!$user["page_now"])
  echo("<tr><td class=tableb>Now active in</td><td class=tablea>(Never active)</td></tr>\n");
////////////////////////////////////////////////////////////////////////
if ($user["id"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR) {
    echo ("<tr><td class=rowhead>".$language['age']."</td><td align=left>" . safeChar($age) .
        "</td></tr>\n");
    $birthday = date("Y-m-d", strtotime($birthday));
    echo ("<tr><td class=rowhead>".$language['bdate']."</td><td align=left>$birthday</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['wlevel']."</td><td align=left>" .
        get_user_warns_image($user['warns']) . "</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['email']."</td><td align=left><a href=mailto:$user[email]>$user[email]</a></td></tr>\n");
    // $addr = '';
    if ($addr)
        echo ("<tr><td class=rowhead>".$language['address']."</td><td align=left>$addr</td></tr>\n");
}
////////////////new time ratio system/////////////////////////////////
//=== testing concept of "share ratio" by sir_snugglebunny
$res_share_ratio = mysql_query("SELECT SUM(seedtime) AS seed_time_total, COUNT(id) AS total_number FROM snatched WHERE seedtime > '0' AND userid =".$user['id'])or sqlerr(__FILE__, __LINE__);
$row_share_ratio = mysql_fetch_array($res_share_ratio);
//=== get times per class
switch (true)
{
//===  member
case ($user['class'] == UC_USER):
$days = 3;
break;
//=== Member +
case ($user['class'] == UC_POWER_USER):
$days = 2;
break;
//=== Member ++
case ($user['class'] == UC_VIP || $user['class'] == UC_UPLOADER || $user['class'] == UC_MODERATOR || $user['class'] == UC_ADMINISTRATOR || $user['class'] == UC_SYSOP || $user['class'] == UC_CODER):
$days = 1.5;
break;
//=== Member x
case ($user['class'] == UC_DONOR || $user['class'] >= UC_VIP):
$days = 1;
break;
}
$avg_time_ratio = (($row_share_ratio['seed_time_total'] / $row_share_ratio['total_number']) / 86400 / $days);
$avg_time_seeding = mkprettytime($row_share_ratio['seed_time_total'] / $row_share_ratio['total_number']);
if ($user["id"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR) {
echo'<tr><td class=clearalt5 align=right><b>Time Ratio:</b></td><td align=left class=clearalt5>'.(($user['downloaded'] > 0 || $user['uploaded'] > 2147483648) ? '<font color='.get_ratio_color(number_format($avg_time_ratio, 3)).'>'.number_format($avg_time_ratio, 3).'</font>     '.ratio_image_machine(number_format($avg_time_ratio, 3)).'     [<font color='.get_ratio_color(number_format($avg_time_ratio, 3)).'> '.$avg_time_seeding.'</font> per torrent average ]  Ratio based on the assumption that all torrents were NEW. ' : 'inf.').'</td></tr>';
}
////////////////////////////////////////////////////
if (get_user_class() >= UC_MODERATOR) {
    $resip = sql_query("SELECT ip FROM iplog WHERE userid =".unsafeChar($id)." GROUP BY ip") or
        sqlerr(__file__, __line__);
    $iphistory = mysql_num_rows($resip);

    if ($iphistory > 0)
        echo ("<tr><td class=rowhead> ".$language['iphist']." </td><td align=left> ".$language['iphist1']."<b><a href=iphistory.php?id=" .
            $user['id'] . "> " . safeChar($iphistory) . "  ".$language['iphist2']."</a></b></td></tr>\n");
}
if ($user["id"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR) {
    $bonuslog = explode(" ", $user["bonuscomment"]);
    $total = 0;
    for ($i = 0; $i <= count($bonuslog); $i++) {
        // echo $bonuslog[$i];
        if ($bonuslog[$i] == "upload") {
            // if you found something about "uploading"
            // then go back a couple of steps and get their amount
            // from position of the array where you found something about "upload"
            // to X-3 steps in the array.
            $points = $bonuslog[$i - 3];
            // now i have all the figures now time to do some maths biggrin.gif
            // These figures might be different on your tracker
            // On my tracker they are something like this:
            // 150 points = 1 gig
            // 250 points = 2 gigs
            // 500 points = 5 gigs
            // Note: I should really be getting these values of database!
            // So if i change those these work accordingly! @TODO
            if ($points == "150") {
                $total = $total + 1;
            }
            if ($points == "250") {
                $total = $total + 2;
            }
            if ($points == "500") {
                $total = $total + 5;
            }
        }
        // This will take care of how much love they shared with other users
        // i.e. gave away their karma
        // TODO later just an idea for others to work on
        if ($bonuslog[$i] == "gift") {
            $love = $bonuslog[$i - 3];
            // echo "<pre>Love= " . $love . "\n</pre>";
        }
        // === karma for gifts
        if ($bonuslog[$i] == 'to') {
            $love = $bonuslog[$i - 4];
            switch ($love) {
                case 100:
                    $gift = $gift + 100;
                    break;
                case 200:
                    $gift = $gift + 200;
                    break;
                case 300:
                    $gift = $gift + 300;
                    break;
                case 400:
                    $gift = $gift + 400;
                    break;
                case 500:
                    $gift = $gift + 500;
                    break;
                case 1000:
                    $gift = $gift + 1000;
                    break;
            }
        }
        // === karma recieved gifts
        if ($bonuslog[$i] == 'from') {
            $got_love = $bonuslog[$i - 4];
            switch ($got_love) {
                case 100:
                    $got_gift = $got_gift + 100;
                    break;
                case 200:
                    $got_gift = $got_gift + 200;
                    break;
                case 300:
                    $got_gift = $got_gift + 300;
                    break;
                case 400:
                    $got_gift = $got_gift + 400;
                    break;
                case 500:
                    $got_gift = $got_gift + 500;
                    break;
                case 1000:
                    $got_gift = $got_gift + 1000;
                    break;
            }
        }
    }
    // convert it to bytes so we can fuck with ratio
    $total = 1073741824 * $total;
    $pureupload = prefixed($user["uploaded"] - $total);
    $realratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded'] / $CURUSER['downloaded'] :
        0;
    $realratio = number_format($realratio, 3);
    $dayUpload = $user["uploaded"];
    $dayDownload = $user["downloaded"];
    $seconds = mkprettytime(strtotime("now") - strtotime($user["added"]));
    $days = explode("d ", $seconds);
    if (sizeof($days) > 1) {
        $dayUpload = $user["uploaded"] / $days[0];
        $dayDownload = $user["downloaded"] / $days[0];
    }

?>
<tr><td class=rowhead><?php echo $language['upped'];?></td><td align=left><?= prefixed($user["uploaded"]) ?>&nbsp;<b>[</b><?php echo $language['daily'];?><?= prefixed($dayUpload) ?><b>]</b></td></tr><tr><td class=rowhead><?php echo $language['downed'];?></td><td align=left><?= prefixed($user["downloaded"]) ?>&nbsp;<b>[</b><?php echo $language['daily'];?><?= prefixed($dayDownload) ?><b>]</b></td></tr><tr><td class=rowhead><?php echo $language['kupped'];?></td><td align=left><?= prefixed($total) ?></td></tr><tr><td class=rowhead><?php echo $language['pupped'];?></td><td align=left><?= $pureupload ?></td></td><tr><td class=rowhead><?php echo $language['rratio'];?></td><td align=left><?= $realratio ?></td></tr><tr><td class=rowhead><?php echo $language['thnr'];?></td><td align=left><?= $hitruns ?></td></tr><?php
    if ($user["downloaded"] > 0) {
        $sr = $user["uploaded"] / $user["downloaded"];
        if ($sr >= 4)
            $s = "w00t";
        else
            if ($sr >= 2)
                $s = "grin";
            else
                if ($sr >= 1)
                    $s = "smile1";
                else
                    if ($sr >= 0.5)
                        $s = "noexpression";
                    else
                        if ($sr >= 0.25)
                            $s = "sad";
                        else
                            $s = "cry";
        $sr = floor($sr * 1000) / 1000;
        $sr = "<table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font color=" .
            get_ratio_color($sr) . ">" . number_format($sr, 3) .
            "</font></td><td class=embedded>&nbsp;&nbsp;<img src=/pic/smilies/$s.gif></td></tr></table>";
        echo ("<tr><td class=rowhead style='vertical-align: middle'>Share ratio</td><td align=left valign=center style='padding-top: 1px; padding-bottom: 0px'>$sr</td></tr>\n");
    }
}
$connectable = "";
// Get connetibility and # of leeches/seeds
    $resnew = sql_query("SELECT seeder,connectable,COUNT(userid) as count,userid from peers group by seeder,userid having userid=".unsafeChar($CURUSER["id"]));
    while ($resopt = mysql_fetch_row($resnew))
        {
            if ($resopt[0]=='yes')
                $activeseed = $resopt[2];
            else
                $activeleech = $resopt[2];
            $connect =$resopt[1];
        }
     if (!$activeseed)
         $activeseed=0;
     if (!$activeleech)
        $activeleech=0;
    /// Check Connectibility
      if ($connect){
        if($connect == "yes"){
          $connectable = "<b><font color=green><a title='Connectable= Yes'>Yes - Your Port Is Open</a></font></b>";
        }else{
          $connectable = "<b><font color=red><a title='Connectable = No'>No - Your Unconnectable Contact Site Admin</a></font></b>";
        }
     }else{
        $connectable = "<b><font color=blue><a title='Unknown'>Unknown</a></font></b>";
    }
    /// end
?>
<tr><td class=rowhead><?php echo $language['conn'];?></font></td><td align=left><?= $connectable ?></td></tr>
<?php
if ($user["download"] != 0) {
    foreach ($speed as $key => $value)
        if ($user["download"] == $key)
            $dlspeed = $value;
    tr("".$language['dspeed']."", "<img src=\"pic/down_speed.png\" title=\"Download speed: " .
        $dlspeed . "\" alt=\"Download speed: " . $dlspeed . "\"> " . $dlspeed, 1);
}

reset($speed);

if ($user["upload"] != 0) {
    foreach ($speed as $key => $value)
        if ($user["upload"] == $key)
            $ulspeed = $value;
    tr("".$language['upspeed']."", "<img src=\"pic/up_speed.png\" title=\"Upload speed: " . $ulspeed .
        "\" alt=\"Upload speed: " . $ulspeed . "\"> " . $ulspeed, 1);
}
// === Karma bonus points
if (get_user_class() >= UC_MODERATOR)
    echo ("<tr><td class=rowhead><b>".$language['sbonus']."</b></td><td colspan=2 align=left>" .
        safeChar($user["seedbonus"]) . "</tr></td>\n");
// ===end
if (get_user_class() >= UC_MODERATOR)
    echo ("<tr><td class=rowhead>".$language['slot']."</td><td align=left>" . safeChar($user['freeslots']) .
        "</td></tr>\n");
if ($user["avatar"])
    echo ("<tr><td class=rowhead>".$language['avatar']."</td><td align=left><a href=\"" . safeChar($user["avatar"]) .
        "\" rel='lightbox' title=\"" . safeChar($user["username"]) . "\" class=\"borderimage\" onMouseover=\"borderit(this,'black')\" onMouseout=\"borderit(this,'silver')\"><img src=\"" .
        safeChar($user["avatar"]) . "\" width=150 title=\"" . safeChar($user["username"]) .
        "\"></a></td></tr>\n");
if ($user["signature"])
    echo ("<tr><td class=rowhead>".$language['sig']."</td><td align=left>" . format_comment($user["signature"]) .
        "</td></tr>\n");
if (get_user_class() >= UC_MODERATOR)
    if ($user["title"])
        echo ("<tr><td class=rowhead>".$language['class']."</td><td align=left><font color='#" .
            get_user_class_color($user['class']) . "'> " . get_user_class_name($user['class']) .
            "  <img src=" . get_user_class_image($user["class"]) . " alt=" .
            get_user_class_name($user["class"]) . "> | " .safeChar($user["title"]) .
            "</td></tr>\n");
    else
        echo ("<tr><td class=rowhead>".$language['class']."</td><td align=left><font color='#" .
            get_user_class_color($user['class']) . "'> " . get_user_class_name($user['class']) .
            " <img src=" . get_user_class_image($user["class"]) . " alt=" .
            get_user_class_name($user["class"]) . "></td></tr>\n");

if ($user["showfriends"] == "yes" || $CURUSER["id"] == $user["id"] || $friend ||
    $CURUSER['class'] >= UC_MODERATOR) {
    $fcount = number_format(get_row_count("friends", "WHERE userid='" . $id .
        "' AND confirmed = 'yes'"));
    if ($fcount >= 1) {
        $fr = sql_query("SELECT f.friendid as id, u.username AS name FROM friends AS f LEFT JOIN users as u ON f.friendid = u.id WHERE userid=$id AND f.confirmed='yes' ORDER BY name LIMIT 100") or
            sqlerr(__file__, __line__);
        $frnd = '';
        while ($friend = mysql_fetch_array($fr)) {
            $frnd = $frnd . "<a href=" . $DEFAULTBASEURL . "/userdetails.php?id=" . $friend['id'] .
                ">" . $friend['name'] . "</a>, ";
        }
        tr("".$language['fri']."Friends", "<a href=" . $DEFAULTBASEURL . "/userfriends.php?id=$id>" . $fcount .
            " ".$language['fri']."Friends</a> - " . $frnd, 1);

        if (isset($user['comments']))
            tr("".$language['com']."Comments", "<a href=" . $DEFAULTBASEURL . "/userfriends.php?id=$id>" . $user['username'] .
                "".$language['has']." has " . $user['comments'] . "".$language['com']." Comments</a>", 1);
    }
}
echo ("<tr><td class=rowhead>".$language['tcomm']."</td>");
if ($torrentcomments && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) ||
    get_user_class() >= UC_MODERATOR)) {
    $comments = sql_query("SELECT sum(comments) AS comments FROM torrents");
    $seconds = round(($comments) / ($torrentcomments), 3);
    echo ("<td align=left><a href=userhistory.php?action=viewcomments&id=$user[id]>$torrentcomments</a> ($seconds% of total comments)</td></tr>\n");
} else
    echo ("<td align=left>$torrentcomments</td></tr>\n");
echo ("<tr><td class=rowhead>".$language['fposts']."</td>");
if ($forumposts && (($user["class"] >= UC_USER && $user["id"] == $CURUSER["id"]) ||
    get_user_class() >= UC_MODERATOR)) {
    $postcount = sql_query("SELECT sum(postcount) AS postcount FROM forums");
    $seconds = round(($postcount) / ($forumposts), 3);
    echo ("<td align=left><a href=userhistory.php?action=viewposts&id=$user[id]>$forumposts</a> ($seconds% of total posts)</td></tr>\n");
} else
    echo ("<td align=left>$forumposts</td></tr>\n");
if ($CURUSER["id"] == $user["id"] || get_user_class() >= UC_MODERATOR)
    echo ("<tr><td class=rowhead>".$language['pviews']."</td><td align=left><a href=userhits.php?id=$id>" .
        number_format($user["hits"]) . "</a></td></tr>\n");
if (get_user_class() >= UC_MODERATOR && $user["invites"] > 0 || $user["id"] == $CURUSER["id"] &&
    $user["invites"] > 0)
    echo ("<tr><td class=rowhead>".$language['inv']."</td><td align=left><a href=invite.php>$user[invites]</a></td></tr>\n");
if (get_user_class() >= UC_MODERATOR && $user["invitedby"] > 0 || $user["id"] ==
    $CURUSER["id"] && $user["invitedby"] > 0) {
    $invitedby = sql_query("SELECT username FROM users WHERE id=$user[invitedby]");
    $invitedby2 = mysql_fetch_array($invitedby);
    echo ("<tr><td class=rowhead>".$language['invby']."</td><td align=left><a href=userdetails.php?id=$user[invitedby]>$invitedby2[username]</a></td></tr>\n");
}
if (get_user_class() >= UC_MODERATOR && $user["invitees"] > 0 || $user["id"] ==
    $CURUSER["id"] && $user["invitees"] > 0) {
    $compl = $user["invitees"];
    $compl_list = explode(" ", $compl);
    $arr = array();

    foreach ($compl_list as $array_list)
        $arr[] = $array_list;

    $compl_arr = array_reverse($arr, true);
    $f = 0;
    foreach ($compl_arr as $user_id) {
        $compl_user = sql_query("SELECT id, username FROM users WHERE id='$user_id' and status='confirmed'");
        $compl_users = mysql_fetch_array($compl_user);

        if ($compl_users["id"] > 0) {
            echo ("<tr><td class=rowhead width=1%>Invitees</td><td>");

            $compl = $user["invitees"];
            $compl_list = explode(" ", $compl);
            $arr = array();

            foreach ($compl_list as $array_list)
                $arr[] = $array_list;

            $compl_arr = array_reverse($arr, true);

            $i = 0;
            foreach ($compl_arr as $user_id) {
                $compl_user = sql_query("SELECT id, username FROM users WHERE id='$user_id' and status='confirmed' ORDER BY username");
                $compl_users = mysql_fetch_array($compl_user);
                echo ("<a href=userdetails.php?id=" . $compl_users["id"] . ">" . $compl_users["username"] .
                    "</a>&nbsp;");

                if ($i == "9")
                    break;
                $i++;
            }
            echo ("</td></tr>");
            $f = 1;
        }
        if ($f == "1")
            break;
    }
}

if ($user['hidecur'] == "yes" && (get_user_class() >= UC_MODERATOR || $CURUSER["id"] ==
    $user["id"])) {
    if ($torrents)
        echo "<tr valign=top><td class=rowhead>".$language['utor']."<a href=\"javascript: klappe_news('a1')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka1\" style=\"display: none;\">$torrents</div></td></tr>";
    if ($seeding)
        echo "<tr valign=top><td class=rowhead>".$language['stor']."<a href=\"javascript: klappe_news('a2')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka2\" style=\"display: none;\">$seeding</div></td></tr>";
    if ($leeching)
        echo "<tr valign=top><td class=rowhead>".$language['ltor']."<a href=\"javascript: klappe_news('a3')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka3\" style=\"display: none;\">$leeching</div></td></tr>";
    $res = sql_query("SELECT s.*, t.name AS name, c.name AS catname, c.image AS catimg FROM snatched AS s INNER JOIN torrents AS t ON s.torrentid = t.id LEFT JOIN categories AS c ON t.category = c.id WHERE s.userid = $user[id]") or
        sqlerr(__file__, __line__);
    if (mysql_num_rows($res) > 0)
        $snatches = snatchtable($res);
    if ($snatches)
        echo "<tr valign=top><td class=rowhead>".$language['snatched']."<a href=\"javascript: klappe_news('a4')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka4\" style=\"display: none;\">$snatches</div></td></tr>";
}
if ($user['hidecur'] == "no") {
    if ($torrents)
        echo "<tr valign=top><td class=rowhead>".$language['utor']."<a href=\"javascript: klappe_news('a1')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka1\" style=\"display: none;\">$torrents</div></td></tr>";
    if ($seeding)
        echo "<tr valign=top><td class=rowhead>".$language['stor']."<a href=\"javascript: klappe_news('a2')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka2\" style=\"display: none;\">$seeding</div></td></tr>";
    if ($leeching)
        echo "<tr valign=top><td class=rowhead>".$language['ltor']."<a href=\"javascript: klappe_news('a3')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka3\" style=\"display: none;\">$leeching</div></td></tr>";
    $res = sql_query("SELECT s.*, t.name AS name, c.name AS catname, c.image AS catimg FROM snatched AS s INNER JOIN torrents AS t ON s.torrentid = t.id LEFT JOIN categories AS c ON t.category = c.id WHERE s.userid = $user[id]") or
        sqlerr(__file__, __line__);
    if (mysql_num_rows($res) > 0)
        $snatches = snatchtable($res);
    if ($snatches)
        echo "<tr valign=top><td class=rowhead>".$language['snatched']."<a href=\"javascript: klappe_news('a4')\"><br><img border=\"0\" src=\"pic/plus.gif\" id=\"pica" .
            $array['id'] . "\" alt=\"[Hide/Show]\"></td><td align=left><div id=\"ka4\" style=\"display: none;\">$snatches</div></td></tr>";
}
// === start snatched
if (get_user_class() >= UC_MODERATOR) {
    if (isset($_GET["snatched_table"])) {
        echo "<tr><td class=clearalt6 align=right valign=top><b>".$language['sstuff']."</b><br>[ <a class=altlink href=\"userdetails.php?id=$id\" class=\"sublink\">Hide list</a> ]</td><td class=clearalt6>";
            $res = sql_query("SELECT UNIX_TIMESTAMP(sn.start_date) AS s, UNIX_TIMESTAMP(sn.complete_date) AS c, UNIX_TIMESTAMP(sn.last_action) AS l_a, UNIX_TIMESTAMP(sn.seedtime) AS s_t, sn.seedtime, UNIX_TIMESTAMP(sn.leechtime) AS l_t, sn.leechtime, sn.downspeed, sn.upspeed, sn.uploaded, sn.downloaded, sn.torrentid, sn.start_date, sn.complete_date, sn.hit_and_run, sn.mark_of_cain, sn.seeder, sn.last_action, sn.connectable, sn.agent, sn.seedtime, sn.port, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name " .
           "FROM snatched AS sn " . "LEFT JOIN torrents AS t ON t.id = sn.torrentid " .
           "LEFT JOIN categories AS cat ON cat.id = t.category " . "WHERE sn.userid=$id ORDER BY sn.start_date DESC") or
          die(mysql_error());
        echo "<table border=1 cellspacing=0 cellpadding=5 align=center><tr><td class=colhead2 align=center>Category</td><td class=colhead2 align=left>Torrent</td>" .
            "<td class=colhead2 align=center>S / L</td><td class=colhead2 align=center>Up / Down</td><td class=colhead2 align=center>Torrent Size</td>" .
            "<td class=colhead2 align=center>Ratio</td><td class=colhead2 align=center>Client</td></tr>";
        while ($arr = mysql_fetch_assoc($res)) {
            // =======change colors
            $count2 = (++$count2) % 2;
            $class = 'clearalt' . ($count2 == 0 ? '6' : '7');
           // === speed color red fast green slow ;)
            if ($arr["upspeed"] > 0)
                $ul_speed = ($arr["upspeed"] > 0 ? prefixed($arr["upspeed"]) : ($arr["seedtime"] >
                    0 ? prefixed($arr["uploaded"] / ($arr["seedtime"] + $arr["leechtime"])) :
                    prefixed(0)));
            else
                $ul_speed = prefixed(($arr["uploaded"] / ($arr['l_a'] - $arr['s'] + 1)));
            if ($arr["downspeed"] > 0)
                $dl_speed = ($arr["downspeed"] > 0 ? prefixed($arr["downspeed"]) : ($arr["leechtime"] >
                    0 ? prefixed($arr["downloaded"] / $arr["leechtime"]) : prefixed(0)));
            else
                $dl_speed = prefixed(($arr["downloaded"] / ($arr['c'] - $arr['s'] + 1)));
            switch (true) {
                case ($dl_speed > 600):
                    $dlc = 'red';
                    break;
                case ($dl_speed > 300):
                    $dlc = 'orange';
                    break;
                case ($dl_speed > 200):
                    $dlc = 'yellow';
                    break;
                case ($dl_speed < 100):
                    $dlc = 'Chartreuse';
                    break;
            }
                if ($arr["downloaded"] > 0) {
                $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
                $ratio = "<font color=" . get_ratio_color($ratio) . "><b>Ratio:</b><br>$ratio</font>";
            } else
                if ($arr["uploaded"] > 0)
                    $ratio = "Inf.";
                else
                    $ratio = "N/A";
            // / smallname seeding/leeching torrents
            $smallname2 = substr(safeChar($arr["torrent_name"]), 0, 30);
            if ($smallname2 != safeChar($arr["torrent_name"])) {
                $smallname2 .= '...';
            }
            echo "<tr><td class=$class align=center>" . ($arr['owner'] == $id ?
                "<b><font color=orange>torrent owner</font></b><br>" : "" . ($arr['complete_date'] !=
                '0000-00-00 00:00:00' ? "<b><font color=lightgreen>Finished</font></b><br>" :
                "<b><font color=red>Not Finished</font><br>") . "") . "<img src=pic/$arr[image] alt=$arr[name]></td>" .
                "<td class=$class><a class=altlink href=details.php?id=$arr[torrentid]><b>$smallname2
                </b></a> " . ($arr['complete_date'] != '0000-00-00 00:00:00' ? "<br>" .
                "<font color=yellow>HnR: " . $arr['hit_and_run'] .
                "</font><br>" . "<font color=yellow>started: " . $arr['start_date'] .
                "</font><br><font color=pink>finished: " . $arr['complete_date'] . "</font>" :
                "" . "<br><font color=yellow>started: " . $arr['start_date'] .
                "</font><br><font color=orange>Last Action: " . $arr['last_action'] . "</font> " .
                "" . ($arr['complete_date'] == '0000-00-00 00:00:00' ? "" . ($arr['owner'] == $id ?
                "" : "[ " . prefixed($arr["size"] - $arr["downloaded"]) . " still to go ]") . "" :
                "") . "") . "" . ($arr['complete_date'] != '0000-00-00 00:00:00' ? "<br>".
                "<font color=silver>time to download: " . ($arr['leechtime'] != '0' ?
                mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s']) . "") .
                "</font> <font color=$dlc>[ DLed at: $dl_speed ]<font>" . "<br>" : "<br>") .
                "<font color=lightblue>" . ($arr['seedtime'] != '0' ? "total seeding time: " .
                mkprettytime($arr['seedtime']) . " <font color=$dlc> " :
                "total seeding time: N/A") . "" . "</font>
                <font color=lightgreen> [ up speed: " .
                $ul_speed . " ] </font>" . ($arr['complete_date'] == '0000-00-00 00:00:00' ?
                "<br><font color=$dlc>Download speed: $dl_speed</font>" : "") . "</td>" .
                "<td align=center class=$class>Seeds: " . $arr['seeders'] . "<br>Leech: " . $arr['leechers'] .
                "</td><td align=center class=$class><font color=lightgreen>Uploaded:<br>" .
                "<b>" . $uploaded = prefixed($arr["uploaded"]) .
                "</b></font><br><font color=orange>Downloaded:<br><b>" . $downloaded = prefixed($arr["downloaded"]) .
                "</b></font></td>" . "<td align=center class=$class>" . prefixed($arr["size"]) .
                "<br>difference of:<br><font color=orange><b>" . prefixed($arr['size'] - $arr["downloaded"]) .
                "</b></font></td>" . "<td align=center class=$class>$ratio<br>" . ($arr['seeder'] ==
                'yes' ? "<font color=lightgreen><b>seeding</b></font>" :
                "<font color=red><b>not seeding</b></font>") . "<br>" . ($arr['mark_of_cain'] ==
                'yes' ? "<font color=red><b>marked</b></font>" :
                "<font color=green><b>not marked</b></font>") . "" .
                "</td><td align=center class=$class>" . $arr["agent"] . "<br>port: " . $arr["port"] .
                "<br>" . ($arr["connectable"] == 'yes' ?
                "<b>connectable: <font color=lightgreen>yes</font>" . "</b>" :
                "<b>connectable: <font color=red><b>no</b></font>") ."</td></tr>\n";
               
                "<td class=embedded border=1 cellspacing=0 cellpadding=3><tr><td class=colhead2>Type</td><td class=colhead2>Name</td><td class=colhead2 align=center>S</td><td class=colhead2 align=center>L</td><td class=colhead2 align=center>UL</td><td class=colhead2 align=center>DL</td><td class=colhead2 align=center>Ratio</td><td class=colhead2 align=center>When Completed</td><td class=colhead2 align=center>Last Action</td><td class=colhead2 align=center>Speed</td>\n";
                "<td style=padding: 0px class=".$class."><img src=pic/".$arr["image"]." alt=".$arr["catname"]."></td><td class=".$class."><a class=altlink href=details.php?id=".$arr["id"]."&hit=1><b>".safeChar($arr["torrent_name"])."</b></a><br><font color=".$color.">  ".(($CURUSER["class"] >= UC_MODERATOR || $user["id"] == $CURUSER["id"]) ? "seeded for: ".mkprettytime($arr["seedtime"]).(($minus_ratio != "0:00" && $arr["uploaded"] < $arr["downloaded"]) ? "<br>should still seed for: ".$minus_ratio."</font>" : "").($arr["seeder"] == "yes" ? " <font color=limegreen> [<b>seeding</b>]</font>" : $hit_n_run." ".$mark_of_cain) : "")."</td></td><td align=center class=".$class.">".$arr["seeders"]."</td><td align=center class=".$class.">".$arr["leechers"]."</td><td align=center class=".$class.">".prefixed($arr["uploaded"])."</td><td align=center class=".$class.">".prefixed($arr["downloaded"])."</td><td align=center class=".$class.">".($arr["downloaded"] > 0 ? "<font color=" . get_ratio_color(number_format($arr["uploaded"] / $arr["downloaded"], 3)) . ">".number_format($arr["uploaded"] / $arr["downloaded"], 3)."</font>" : ($arr["uploaded"] > 0 ? "Inf." : "---"))."<br></td><td align=center class=".$class.">".$arr["complete_date"]."</td><td align=center class=".$class.">".$arr["last_action"]."</td><td align=center class=".$class."><font color=".((prefixed(($arr["uploaded"] / ($arr["l_t"] - $arr["s_t"] + 1))) > 200) ? "red" : "green").">".prefixed(($arr["uploaded"] / ($arr["l_t"] - $a["s_t"] + 1)))."<font></td>\n";
          }
         echo "</table></td></tr>\n";
         
         } else
     tr( "".$language['sstuff']."<br>", "[ <a class=altlink href=\"userdetails.php?id=$id&snatched_table=1\" class=\"sublink\">Show</a> ]  - $count_snatched <font color=red><b>staff only!!!</font></b>", 1 );
}
// === end snatched
if ($user["info"])
    echo ("<tr valign=top><td align=left colspan=2 class=text bgcolor=#777777>" .
        format_comment($user["info"]) . "</td></tr>\n");
tr("".$language['report']."", "<form action=report.php?type=User&id=$id method=post><input class=button type=submit name=submit value=\"Report User\">".$language['report2']."</form>",1);
if ($CURUSER["id"] != $user["id"])
    if (get_user_class() >= UC_MODERATOR)
        $showpmbutton = 1;
    elseif ($user["acceptpms"] == "yes") {
        $r = sql_query("SELECT id FROM blocks WHERE userid=$user[id] AND blockid=$CURUSER[id]") or
            sqlerr(__file__, __line__);
        $showpmbutton = (mysql_num_rows($r) == 1 ? 0 : 1);
    } elseif ($user["acceptpms"] == "friends") {
        $r = sql_query("SELECT id FROM friends WHERE userid=$user[id] AND friendid=$CURUSER[id]") or
            sqlerr(__file__, __line__);
        $showpmbutton = (mysql_num_rows($r) == 1 ? 1 : 0);
    }

$showpmbutton = 'true';
if ($showpmbutton)
    if ($CURUSER["id"] == $row["owner"])
        $CURUSER["sendpmpos"] = "yes";
if ($CURUSER["sendpmpos"] != "no") {
    echo ("<tr><td colspan=2 align=center><form method=get action=sendmessage.php><input type=hidden name=receiver value=" .
        $user["id"] . "><input type=submit value=\"Send message\" style='height: 23px'></form></td></tr>");
} else {
}
if (get_user_class() >= UC_SYSOP) {
    echo (" <script type=text/javascript src=todger.js></script>");
    $username = safeChar($user["username"]);
    echo (" <form method=post action=delacctadmin.php name=deluser><tr><td align=center class=rowhead>".$language['deluser']."<input name=username size=20 value=" .
        $username . " type=hidden><input name=delenable type=checkbox onClick=\"if (this.checked) {enabledel();}else{disabledel();}\"></td><td colspan=2 align=center><input name=submit type=submit class=btn value=\"Delete User\" disabled></td></tr></form>");
    // ---------------------------------------------
}
echo ("</table>\n");

if (get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class()) {
    begin_frame("Edit User", true);
    echo ("<form method=post action=modtask.php>\n");
    echo ("<input type=hidden name='action' value='edituser'>\n");
    echo ("<input type=hidden name='userid' value='$id'>\n");
    echo ("<input type=hidden name='returnto' value='userdetails.php?id=$id'>\n");
    echo ("<table class=main border=1 cellspacing=0 cellpadding=5>\n");

    echo ("<tr><td class=rowhead>".$language['title']."</td><td colspan=2 align=left><input type=text size=60 name=title value=\"" .
        safeChar($user["title"]) . "\"></tr>\n");
    $avatar = safeChar($user["avatar"]);
    echo ("<tr><td class=rowhead>".$language['avaurl']."</td><td colspan=2 align=left><input type=text size=60 name=avatar value=\"$avatar\"></tr>\n");
    $signature = safeChar($user["signature"]);
    echo ("<tr><td class=rowhead>".$language['sigurl']."</td><td colspan=2 align=left><input type=text size=60 name=signature value=\"$signature\"></tr>\n");
    // we do not want mods to be able to change user classes or amount donated...
    if ($CURUSER["class"] >= UC_ADMINISTRATOR)
        echo ("<tr><td class=rowhead>".$language['donor']."Donor</td><td colspan=2 align=left><input type=radio name=donor value=yes" .
            ($user["donor"] == "yes" ? " checked" : "") .
            ">Yes <input type=radio name=donor value=no" . ($user["donor"] == "no" ?
            " checked" : "") . ">No</td></tr>\n");
    // === donor mod time based by snuggles
    if ($CURUSER["class"] >= UC_SYSOP) {
        $donor = $user["donor"] == "yes";
        echo ("<tr><td class=clearalt6 align=right><b>".$language['donor']."</b></td><td colspan=2 align=left class=clearalt6>");

        if ($donor) {
            $donoruntil = $user['donoruntil'];
            if ($donoruntil == '0000-00-00 00:00:00')
                echo ("arbitrary duration");
            else {
                echo ("<b>".$language['donor2']."</b> $donoruntil");
                echo (" [ " . mkprettytime(strtotime($donoruntil) - gmtime()) . " ] to go\n");
            }
        } else {
            echo ("".$language['dfor']."<select name=donorlength><option value=0>------</option><option value=4>1 month</option>" .
                "<option value=6>6 weeks</option><option value=8>2 months</option><option value=10>10 weeks</option>" .
                "<option value=12>3 months</option><option value=255>Unlimited</option></select>\n");
        }
        echo ("<br /><b>".$language['cdonation']."</b> <input type=text size=6 name=donated value=\"" .
            safeChar($user["donated"]) . "\">" . "<b>".$language['tdonations']."Total Donations:</b> &#163;" . safeChar
            ($user["total_donated"]) . "");
        if ($donor) {
            echo ("<br><b>".$language['adonor']."</b> <select name=donorlengthadd><option value=0>------</option><option value=4>1 month</option>" .
                "<option value=6>6 weeks</option><option value=8>2 months</option><option value=10>10 weeks</option>" .
                "<option value=12>3 months</option><option value=255>Unlimited</option></select>\n");
            echo ("<br><b>".$language['rdonor']."</b> <input name=donor value=no type=checkbox> [if they were bad ]");
        }
        echo ("</td></tr>\n");
    }
    // ====end
    if (get_user_class() == UC_MODERATOR && $user["class"] > UC_VIP)
        echo ("<input type=hidden name=class value=$user[class]\n");
    else {
        echo ("<tr><td class=rowhead>".$language['class']."</td><td colspan=2 align=left><select name=class>\n");
        if (get_user_class() == UC_MODERATOR)
            $maxclass = UC_VIP;
        else
            $maxclass = get_user_class() - 1;
        for ($i = 0; $i <= $maxclass; ++$i)
            echo ("<option value=$i" . ($user["class"] == $i ? " selected" : "") . "> " .
                get_user_class_name($i) . "\n");
        echo ("</select></td></tr>\n");
        write_staff();
    }
    // ===Fls mod
    $supportfor = safeChar($user["supportfor"]);
    echo ("<tr><td class=rowhead>".$language['support']."</td><td colspan=2 align=left><input type=radio name=support value=yes" .
        ($user["support"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=support value=no" . ($user["support"] == "no" ?
        " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['supfor']."</td><td colspan=2 align=left><textarea cols=60 rows=6 name=supportfor>$supportfor</textarea></td></tr>\n");
    // === bonus comment
    $bonuscomment = safeChar($user["bonuscomment"]);
    echo ("<tr><td class=clearalt6 align=right><b>".$language['skarma']."</b></td><td colspan=2 align=left class=clearalt6><textarea cols=60 rows=10 name=modcomment READONLY style=\"background: purple; color: yellow;\">$bonuscomment</textarea></td></tr>\n");
    // ==end
    // === bonus comment
    $bonuscomment = safeChar($user["bonuscomment"]);
    echo ("<tr><td class=clearalt6 align=right><b>".$language['skarma']."</b></td><td colspan=2 align=left class=clearalt6><textarea cols=60 rows=10 name=bonuscomment READONLY style=\"background-color:silver\">$bonuscomment</textarea></td></tr>\n");
    // ==end
    // === Karma bonus
    if (get_user_class() >= UC_SYSOP)
        echo ("<tr><td align=right><b>".$language['sbonus']."</b></td><td colspan=2 align=left><input type=text size=5 name=seedbonus value=\"" .
            safeChar($user["seedbonus"]) . "\"></tr>\n");
    // /////////Freeslots by pdq
    if (get_user_class() >= UC_ADMINISTRATOR)
        echo ("<tr><td class=rowhead>".$language['slot']."</td><td colspan=2 align=left> <input type=text size=6 name=freeslots value=\"" .
            safeChar($user["freeslots"]) . "\"></td></tr>\n");
    // ///////////////////safe mod comment/////////
    $modcomment = safeChar($user["modcomment"]);
    if (get_user_class() < UC_CODER) {
        echo ("<tr><td class=rowhead>".$language['mcom']."</td><td colspan=2 align=left><textarea cols=60 rows=6 name=modcomment READONLY>$modcomment</textarea></td></tr>\n");
    } else {
        echo ("<tr><td class=rowhead>".$language['mcom']."</td><td colspan=2 align=left><textarea cols=60 rows=6 name=modcomment >$modcomment</textarea></td></tr>\n");
    }
    echo ("<tr><td class=rowhead>".$language['amcom']."</td><td colspan=2 align=left><textarea cols=60 rows=2 name=addcomment ></textarea></td></tr>\n");
    // ////////////auto-leech warn///////////////////
    $leechwarn = $user["leechwarn"] == "yes";
    echo ("<tr><td class=rowhead>".$language['awarn']."<br><font size=1><i>(Low Ratio)</i></font></td>");
    if ($leechwarn) {
        echo ("<td align=left class=\"row1\"><font color=red>".$language['warned']."</font></td>\n");
        $leechwarnuntil = $user['leechwarnuntil'];
        if ($leechwarnuntil != '0000-00-00 00:00:00') {
            echo ("<td align=left class=\"row1\">Until $leechwarnuntil");
            echo ("<br>(" . mkprettytime(strtotime($leechwarnuntil) - gmtime()) .
                " to go)</td></tr>\n");
        } else {
            echo ("<td align=left class=\"row1\"><i>".$language['fwarn']."</i></td></tr>\n");
        }
    } else {
        echo ("<td class=\"row1\" colspan=\"2\">".$language['nowarn']."</td></tr>\n");
    }
    echo ("<tr><td class=rowhead>".$language['oautow']."</td><td colspan=2 align=left><input type=radio name=warned value=yes" .
        ($user["warned"] == "yes" ? " checked" : "") .
        ">No <input type=radio name=warned value=no" . ($user["warned"] == "no" ?
        " checked" : "") . ">Yes</td></tr>\n");
    // End//////////////////////////////////////////////////////////////////
    if ($CURUSER["class"] < UC_SYSOP)
        echo ("<input type=\"hidden\" name=\"highspeed\" value=$user[highspeed]>\n");
    else {
        echo ("<tr><td class=rowhead>".$language['highspeed']."</td><td class=row1 colspan=2 align=left><input type=radio name=highspeed value=yes" .
            ($user["highspeed"] == "yes" ? " checked" : "") .
            ">Yes        <input type=radio name=highspeed value=no" . ($user["highspeed"] ==
            "no" ? " checked" : "") . ">No</td></tr>\n");
    }
    // //////////webseeder
    echo ("<tr><td class=rowhead>".$language['webseeder']."</td><td colspan=2 align=left><input type=radio name=webseeder value=yes" .
        ($user["webseeder"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=webseeder value=no" . ($user["webseeder"] == "no" ?
        " checked" : "") . ">No<br>".$language['webseeder1']."</td></tr>\n");
    // /////////////////////
    // === Reset Hit And Runs
    if (get_user_class() >= UC_ADMINISTRATOR)
        echo ("<tr><td align=right><b>".$language['rhnr']."</b></td><td colspan=2 align=left><input type=text size=5 name=hit_and_run_total value=\"" .
            safeChar($user["hit_and_run_total"]) . "\"></tr>\n");
    // /////////////new percentage warning system////////////
    if (get_user_class() >= UC_ADMINISTRATOR)
        echo ("<tr><td class=rowhead>".$language['immune']."</td><td colspan=2 align=left><input type=radio name=immun value=yes" .
            ($user["immun"] == "yes" ? " checked" : "") .
            ">Yes <input type=radio name=immun value=no" . ($user["immun"] == "no" ?
            " checked" : "") . ">No<br />".$language['immune1']."</td></tr>\n");
    elseif (get_user_class() < UC_ADMINISTRATOR)
        echo ("<input type=\"hidden\" name=\"immun\" value=\"$user[immun]\">\n");

    if ($user["immun"] == "no") {
        $bookmcomment = safeChar($user["bookmcomment"]);

?>
<script language="Javascript">
function fuellen(f,longsource,text)
{
txtobj = document.getElementById(longsource);
f.bookmcomment.value = text;
}
</script>

<?php
        if ($user["downloaded"] > 0) {
            $uratio = $user["uploaded"] / $user["downloaded"];
            $uratio = number_format($uratio, 3);
        }
        $timeto = get_date_time(gmtime() + 14 * 86400);

        echo ("<form action=\"\" target=bookmcomment name=bookmcomment><tr><td class=rowhead>".$language['addbookm']."</td><td colspan=2 class=tablea align=left><input type=radio name=addbookmark value=yes" .
            ($user["addbookmark"] == "yes" ? " checked" : "") .
            ">Yes - Other reason <input type=radio onClick=\"fuellen(this.form,'text1','Bad Ratio (" .
            $uratio . ") Time until " . date("d.m.Y", strtotime($timeto)) . "')\" name=addbookmark value=ratio" .
            ($user["addbookmark"] == "ratio" ? " checked" : "") .
            ">Yes, Bad Ratio <input type=radio name=addbookmark onClick=\"fuellen(this.form,'text1','')\" value=no" .
            ($user["addbookmark"] == "no" ? " checked" : "") . ">No</td></tr>\n");
        echo ("<tr><td class=rowhead>".$language['reasonb']."</td><td class=tablea colspan=2 align=left><textarea cols=60 rows=6 name=bookmcomment>$bookmcomment</textarea></td></tr>\n");
        echo ("<tr><td class=rowhead>".$language['wpercen']."</td><td align=left colspan=2>
" . ($user["warns"] > 0 ? "<input type=radio name=warns value=" . ($user["warns"] -
            10) . "%>" . ($user["warns"] - 10) . "%" : "") . "
<input type=radio name=warns checked value=" . $user["warns"] .
            "><font color=blue>" . $user["warns"] . " (actual warn level !)</font>
<input type=radio name=warns value=" . ($user["warns"] + 10) . ">" . ($user["warns"] +
            10) . "%</td></tr>\n");
        echo ("<tr><td class=rowhead>".$language['reason1']."</td><td class=tablea colspan=2 align=left><textarea cols=60 rows=6 name=whywarn></textarea></td></tr>\n");
        echo ("<tr><td class=rowhead>".$language['eadjust']."</td><td colspan=2><textarea cols=60 rows=4 readonly>" .
            $user["whywarned"] . "</textarea></td></tr>");
        echo ("<tr><td class=rowhead>".$language['dlpos']."</td><td colspan=2 align=left><input type=radio name=downloadpos value=yes" .
            ($user["downloadpos"] == "yes" ? " checked" : "") .
            ">Yes <input type=radio name=downloadpos value=no" . ($user["downloadpos"] ==
            "no" ? " checked" : "") . ">No</td></tr>\n");
        $realdlremoved = ($user['dlremoveuntil'] != "0000-00-00 00:00:00" ? date("d.m.Y - H:i:s",
            strtotime($user['dlremoveuntil'])) : "No Limit set");
        echo ("<tr><td class=rowhead>".$language['dlduntil']."</td><td colspan=2>" . $realdlremoved .
            "</td></tr>\n");
        echo ("<tr><td class=\"rowhead\" rowspan=\"2\">".$language['enabled']."</td><td colspan=\"2\" align=\"left\"><input name=\"enabled\" value=\"yes\" type=\"radio\"" .
            ($enabled ? " checked" : "") . ">Yes <input name=\"enabled\" value=\"no\" type=\"radio\"" .
            (!$enabled ? " checked" : "") . ">No</td></tr>\n");
        if ($enabled)
            echo ("<tr><td colspan=\"2\" align=\"left\">".$language['disabler']."&nbsp;<input type=\"text\" name=\"disreason\" size=\"60\" /></td></tr>");
        else
            echo ("<tr><td colspan=\"2\" align=\"left\">".$language['enabler']."&nbsp;<input type=\"text\" name=\"enareason\" size=\"60\" /></td></tr>");
    } else {
        echo ("<input type=\"hidden\" name=\"addbookmark\" value=\"$user[addbookmark]\">\n");
        echo ("<input type=\"hidden\" name=\"bookmcomment\" value=\"$user[bookmcomment]\">\n");
        echo ("<input type=\"hidden\" name=\"warns\" value=\"$user[warns]\">\n");
        echo ("<input type=\"hidden\" name=\"dlremoveuntil\" value=\"$user[dlremoveuntil]\">\n");
        echo ("<input type=\"hidden\" name=\"warns\" value=\"$user[warns]\">\n");
        echo ("<input type=\"hidden\" name=\"whywarned\" value=\"$user[whywarned]\">\n");
        echo ("<input type=\"hidden\" name=\"downloadpos\" value=\"$user[downloadpos]\">\n");
        echo ("<input type=\"hidden\" name=\"enabled\" value=\"$user[enabled]\">\n");
        if (get_user_class() < UC_ADMINISTRATOR)
            echo ("<input type=\"hidden\" name=\"immun\" value=\"$user[immun]\">\n");
    }
    // ///Admin Tools////////////
    //=== hidden torrents
    $hiddentorrents = safeChar($user["hiddentorrents"]);
    echo("<tr><td class=rowhead>Show hidden torrents:</td><td align=left><input type=radio name=hiddentorrents value=yes" .($user["hiddentorrents"] == "yes" ? " checked" : "").">Yes <input type=radio name=hiddentorrents value=no" .($user["hiddentorrents"] == "no" ? " checked" : "").">No</td></tr>\n");
    echo "<tr><td align=right class=clearalt6><b>".$language['invr']."</b></td><td colspan=2 align=left class=clearalt6><input type=radio name=invite_on value=yes" . ($user["invite_on"] ==
        "yes" ? " checked" : "") . ">Yes <input type=radio name=invite_on value=no" . ($user["invite_on"] ==
        "no" ? " checked" : "") . ">No</td></tr>\n";
    echo ("<tr><td class=clearalt6 align=right><b>".$language['inv']."</b></td><td colspan=2 align=left class=clearalt6><input type=text size=3 name=invites value=\"" .
        safeChar($user["invites"]) . "\"></tr>\n");
    echo '<tr><td class=rowhead>'.$language['lsent'].'</td><td align=left><b>' . ('$gift' > 0 ?
        "<font color=yellow>$gift</font>" : '<font color=red>0</font>') .
        '</b>'.$language['lsent1'].'</td></tr>' .
        '<tr><td class=rowhead>'.$language['lrec'].'</td><td align=left><b>' . ('$got_gift' > 0 ?
        "<font color=yellow>$got_gift</font>" : '<font color=red>0</font>') .
        '</b>'.$language['lrec1'].'</td></tr>';
    echo ("<tr><td class=rowhead>".$language['passkey']."</td><td colspan=2 align=left><input name=resetkey value=1 type=checkbox> Reset passkey</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['chat']."</td><td colspan=2 align=left><input type=radio name=chatpost value=yes" .
        ($user["chatpost"] === "yes" ? " checked" : "") .
        ">Yes <input type=radio name=chatpost value=no" . ($user["chatpost"] === "no" ?
        " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['fpostp']."</td><td colspan=2 align=left><input type=radio name=forumpost value=yes" .
        ($user["forumpost"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=forumpost value=no" . ($user["forumpost"] == "no" ?
        " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['uppos']."</td><td colspan=2 align=left><input type=radio name=uploadpos value=yes" .
        ($user["uploadpos"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=uploadpos value=no" . ($user["uploadpos"] == "no" ?
        " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['smpos']."</td><td colspan=2 align=left><input type=radio name=sendpmpos value=yes" .
        ($user["sendpmpos"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=sendpmpos value=no" . ($user["sendpmpos"] == "no" ?
        " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['cban']."</td><td colspan=2 align=left><input type=radio name=casinoban value=yes" .
        ($user["casinoban"] === "yes" ? " checked" : "") .
        ">Yes <input type=radio name=casinoban value=no" . ($user["casinoban"] === "no" ?
        " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['bban']."</td><td colspan=2 align=left><input type=radio name=blackjackban value=yes" .
        ($user["blackjackban"] === "yes" ? " checked" : "") .
        ">Yes <input type=radio name=blackjackban value=no" . ($user["blackjackban"] ===
        "no" ? " checked" : "") . ">No</td></tr>\n");
    echo ("<tr><td class=rowhead>".$language['parked']."</td><td colspan=2 align=left><input type=radio name=parked value=yes" .
        ($user["parked"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=parked value=no" . ($user["parked"] == "no" ?
        " checked" : "") . ">No</td></tr>\n");
    // //altered park mod
    if ($user["parked"] == "yes") {
        $park_action = $user["park_action"];
        $park = ("<font color=red> (" . mkprettytime(strtotime($park_action) - time()) .
            " to go)</font></td></tr>\n");
        echo ("<tr><td class=rowhead><font color=red>".$language['parked']."</font></td><td align=left>" .
            safeChar($user['parked']) . " ?<font color=green> Untill</font> $park_action $park </td></tr>\n");
    } else
        echo ("<tr><td class=rowhead><font color=red>".$language['parked']."</font></td><td align=left>" .
            safeChar($user['parked']) . "</td></tr>\n");
    // / end altered parked
    echo ("<tr><td class=rowhead>".$language['anon']."</td><td colspan=2 align=left><input type=radio name=anonymous value=yes" .
        ($user["anonymous"] == "yes" ? " checked" : "") .
        ">Yes <input type=radio name=anonymous value=no" . ($user["anonymous"] == "no" ?
        " checked" : "") . ">No</td></tr>\n");
    ////////////adjust user ratio by retro
    /*
    echo ("<tr><td class=rowhead>Amount Uploaded</td><td colspan=2 align=left>" .
        "<input type=text size=40 name=uploaded value=\"" . safeChar($user["uploaded"]) .
        "\" />&nbsp;bytes<input type=hidden name=uploadbase value=\"" . safeChar($user["uploaded"]) .
        "\" /></td></tr>\n");
    echo ("<tr><td class=rowhead>Amount Downloaded</td><td colspan=2 align=left>" .
        "<input type=text size=40 name=downloaded value=\"" . safeChar($user["downloaded"]) .
        "\" />&nbsp;bytes<input type=hidden name=downloadbase value=\"" . safeChar($user["downloaded"]) .
        "\" /></td></tr>\n");
    */
    if ($CURUSER['class'] >= UC_MODERATOR) {
        echo '<br><a class=altlink href=adminbookmarks.php>'.$language['abookms'].'</a>';
        echo '<br><a class=altlink href=badratio.php?done=no>'.$language['badr'].'</a>';
        echo '<br><a class=altlink href=snatchleave.php?done=no>'.$language['snleave'].'</a>';
        // //////////////////////
        $check_if_theyre_shitty = sql_query("SELECT suspect FROM shit_list WHERE userid=$CURUSER[id] AND suspect=" .
            $id) or sqlerr(__file__, __line__);
        if (mysql_num_rows($check_if_theyre_shitty) !== 0)
            echo '<br>'.$language['shitlist'].'';
        else
            echo '<br><a class=altlink href=shit_list.php?action=new&shit_list_id=' . $id .
                '&return_to=userdetails.php?id=' . $id . '>'.$language['ashitlist'].'</a>';
    }
    // /////////maximum seed/leech Slots///////////////////////////////////////
    $seedsarr = @mysql_fetch_assoc(sql_query("SELECT COUNT(*) AS `cnt` FROM `peers` WHERE `userid`=" .
        $user["id"] . " AND `seeder`='yes'"));
    $seeds = $seedsarr["cnt"];
    $leechesarr = @mysql_fetch_assoc(sql_query("SELECT COUNT(*) AS `cnt` FROM `peers` WHERE `userid`=" .
        $user["id"] . " AND `seeder`='no'"));
    $leeches = $leechesarr["cnt"];
    $tlimits = get_torrent_limits($user);

    if ($tlimits["seeds"] >= 0) {
        if ($tlimits["seeds"] - $seeds < 1)
            $seedwarn = " style=\"background-color:red;color:orange;\"";
        $tlimits["seeds"] = " / " . $tlimits["seeds"];
    } else
        $tlimits["seeds"] = "";
    if ($tlimits["leeches"] >= 0) {
        if ($tlimits["leeches"] - $leeches < 1)
            $leechwarn = " style=\"background-color:red;color:orange;\"";
        $tlimits["leeches"] = " / " . $tlimits["leeches"];
    } else
        $tlimits["leeches"] = "";
    if ($tlimits["total"] >= 0) {
        if ($tlimits["total"] - $leeches + $seeds < 1)
            $totalwarn = " style=\"background-color:red;color:orange;\"";
        $tlimits["total"] = " / " . $tlimits["total"];
    } else {
        $tlimits["total"] = "";
    }
    echo ("<tr><td class=rowhead>".$language['mas']."</td><td colspan=2 align=left> Seeds (" .
        $seeds . $tlimits["seeds"] . ") | Leeches (" . $leeches . $tlimits["leeches"] .
        ") | Total: (" . ($seeds + $leeches) . $tlimits["total"] . ")</td></tr>\n");
    print ("<tr><td class=rowhead>".$language['fmod']."</td><td colspan=2 align=left><input name=\"forum_mod\" value=\"yes\" type=radio " .
        ($user["forum_mod"] == "yes" ? " checked" : "") . ">Yes <input name=\"forum_mod\" value=\"no\" type=\"radio\" " .
        ($user["forum_mod"] == "no" ? " checked" : "") . ">No</td></tr>\n");
    if ($user["forum_mod"] == "yes") {
        $r = sql_query("SELECT id,name,description FROM forums WHERE place = -1 ORDER BY name ASC") or
            sqlerr();
        $forumsc = mysql_num_rows($r);

?>
		<tr><td colspan="3" align="center">
		<input type="hidden" name="forums_count" value="<?= $forumsc ?>" />
		<div style=" border-style:solid; border-color:#333333; border-width: 1px 1px 1px 3px; width:100%; height:170px; overflow:auto;">
		<table cellpadding="5" border="0" style="margin:3px;" >

		<?php
        $i = 1;
        while ($a = mysql_fetch_assoc($r)) {
?>
    	<tr><td style="border-width: 1px 0px 0px 0px;border-style:dotted; "width="100%" ><a href="forums.php?action=viewforum&amp;forumid=<?= $a["id"] ?>"><?= $a["name"] ?></a><br/>
        <font style="font-size:10px; padding-left:15px;"><?= (strlen($a["description"]) >
            80 ? substr($a["description"], 0, 80) . "..." : $a["description"]) ?></font>
        </td><td nowrap="nowrap" align="right" style="border:none; border-width: 1px 0px 0px 0px;border-style:dotted" ><input type="radio" name="forums_<?= $i ?>" value="yes_<?= $a["id"] ?>" <?= (stristr
                ($user["forums_mod"], "[" . $a["id"] . "]") == true ? "checked=\"checked\"" : "") ?> title="Set moderator for this forum"/><input type="radio" name="forums_<?= $i ?>" value="no_<?= $a["id"] ?>" <?= (stristr
                ($user["forums_mod"], "[" . $a["id"] . "]") != true ? "checked=\"checked\"" : "") ?> title="Unset moderator for this forum"/></td></tr>
		<?php
            $i++;
        }
?>
		</table>
		</div>
		</td></tr>
		<?php
    }

?>
<script type="text/javascript">
function togglediv()
{
    var mySelect = document.getElementById('tselect');
    var myDiv = document.getElementById('tlimitdiv');

    if (mySelect.options[mySelect.selectedIndex].value == "manual")
        myDiv.style.visibility = 'visible';
    else
        myDiv.style.visibility = 'hidden';
    }
</script>
<?php
    echo ("<tr><td class=rowhead>".$language['tlimit']."</td><td class=tablea colspan=2 align=left><select id=\"tselect\" name=\"limitmode\" size=\"1\" onchange=\"togglediv();\">");
    echo ("<option value=\"automatic\"" . ($user["tlimitall"] == 0 ? " selected=\"selected\"" :
        "") . ">Automatic</option>\n");
    echo ("<option value=\"unlimited\"" . ($user["tlimitall"] == -1 ? " selected=\"selected\"" :
        "") . ">Unlimited</option>\n");
    echo ("<option value=\"manual\"" . ($user["tlimitall"] > 0 ? " selected=\"selected\"" :
        "") . ">Manual</option>\n");
    echo ("</select><div id=\"tlimitdiv\" style=\"display: inline;" . ($user["tlimitall"] <=
        0 ? "visibility:hidden;" : "") . "\">&nbsp;&nbsp;&nbsp;");
    echo ("Max Seeds: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxseeds\" value=\"" .
        ($user["tlimitseeds"] > 0 ? $user["tlimitseeds"] : "") . "\">");
    echo ("Max Leeches: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxleeches\" value=\"" .
        ($user["tlimitleeches"] > 0 ? $user["tlimitleeches"] : "") . "\">");
    echo ("Limits: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxtotal\" value=\"" .
        ($user["tlimitall"] > 0 ? $user["tlimitall"] : "") . "\">");
    echo ("</div></td></tr>\n");

    // ///////////////////////flood protection by Retro
    echo ("<tr><td class=rowhead>".$language['pmlimit']."</td><td colspan=2 align=left>" .
        "<input type=text  maxlength=3 size=3 name=pm_max value=\"" . safeChar($user['pm_max']) .
        "\" /> (Max 255)  -  CCVal: " . safeChar($user['pm_count']) . "</td></tr>\n");

    echo ("<tr><td class=rowhead>".$language['postlimit']."</td><td colspan=2 align=left>" .
        "<input type=text  maxlength=3 size=3 name=post_max value=\"" . safeChar($user['post_max']) .
        "\" /> (Max 255)  -  CCVal: " . safeChar($user['post_count']) . "</td></tr>\n");

    echo ("<tr><td class=rowhead>".$language['comlimit']."</td><td colspan=2 align=left>" .
        "<input type=text  maxlength=3 size=3 name=comment_max value=\"" . safeChar($user['comment_max']) .
        "\" /> (Max 255)  -  CCVal: " . safeChar($user['comment_count']) . "</td></tr>\n");
    ?>
    <script type="text/javascript">

  function togglepic(bu, picid, formid)
  {
    var pic = document.getElementById(picid);
    var form = document.getElementById(formid);
    
    if(pic.src == bu + "/plus.gif")
    {
        pic.src = bu + "/minus.gif";
        form.value = "minus";
    }else{
        pic.src = bu + "/plus.gif";
        form.value = "plus";
    }
  }

  </script>
  <?php
    // start upload download boxes //
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        //echo("<tr><td class=rowhead>Change Upload</td><td align=center><img src='/pic/plus.gif' id=uppic onClick=togglepic('$BASEURL','uppic','upchange')> <input type=text name=amountup size=10><td><select name=formatup>\n<option value=kb>kB</option>\n<option value=mb>MB</option>\n<option value=gb>GB</option><option value=tb>TB</option>\n</select>\n</td></tr>");
        //echo("<tr><td class=rowhead>Change Download</td><td align=center><img src='/pic/plus.gif' id=downpic onClick=togglepic('$BASEURL','downpic','downchange')> <input type=text name=amountdown size=10><td><select name=formatdown>\n<option value=kb>kB</option>\n<option value=mb>MB</option>\n<option value=gb>GB</option><option value=tb>TB</option></select>\n</td></tr>");
         echo("<tr><td class=rowhead>".$language['changeup']."</td><td align=center><img src='/plus.gif' id=uppic onClick=togglepic('$BASEURL','uppic','upchange')> <input type=text name=amountup size=10><td><select name=formatup>\n<option value=mb>MB</option>\n<option value=gb>GB</option></select>\n</td></tr>");
         echo("<tr><td class=rowhead>".$language['changedown']."</td><td align=center><img src='/plus.gif' id=downpic onClick=togglepic('$BASEURL','downpic','downchange')> <input type=text name=amountdown size=10><td><select name=formatdown>\n<option value=mb>MB</option>\n<option value=gb>GB</option></select>\n</td></tr>"); 
    }
    //Finish adding selections //
    echo ("</td></tr>");
    echo ("<tr><td colspan=3 align=center><input type=submit class=btn value='Okay'></td></tr>\n");
    echo ("</table>\n");
    echo ("<input type=hidden id=upchange name=upchange value=plus><input type=hidden id=downchange name=downchange value=plus>");
    echo ("</form>\n");
    end_frame();
}

end_main_frame();
stdfoot();

?>