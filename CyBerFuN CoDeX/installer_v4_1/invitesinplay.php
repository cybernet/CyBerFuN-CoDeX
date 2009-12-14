<?php
require "include/bittorrent.php";
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
if (get_user_class() < UC_SYSOP)
    hacker_dork("Invite's in play - Nosey Cunt !");

function nice_pager($rpp, $count, $href, $opts = array()) // thx yuna or whoever wrote it
{
    $pages = ceil($count / $rpp);

    if (!$opts["lastpagedefault"])
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    if (isset($_GET["page"])) {
        $page = 0 + $_GET["page"];
        if ($page < 0)
            $page = $pagedefault;
    } else
        $page = $pagedefault;

    $pager = "<td class=\"pager\">Page:</td><td class=\"pagebr\">&nbsp;</td>";

    $mp = $pages - 1;
    $as = "<b>«</b>";
    if ($page >= 1) {
        $pager .= "<td class=\"pager\">";
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\" style=\"text-decoration: none;\">$as</a>";
        $pager .= "</td><td class=\"pagebr\">&nbsp;</td>";
    }

    $as = "<b>»</b>";
    if ($page < $mp && $mp >= 0) {
        $pager2 .= "<td class=\"pager\">";
        $pager2 .= "<a href=\"{$href}page=" . ($page + 1) . "\" style=\"text-decoration: none;\">$as</a>";
        $pager2 .= "</td>$bregs";
    } else $pager2 .= $bregs;

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "<td class=\"pager\">...</td><td class=\"pagebr\">&nbsp;</td>";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;

            $text = $i + 1;
            if ($i != $page)
                $pagerarr[] = "<td class=\"pager\"><a title=\"$start&nbsp;-&nbsp;$end\" href=\"{$href}page=$i\" style=\"text-decoration: none;\"><b>$text</b></a></td><td class=\"pagebr\">&nbsp;</td>";
            else
                $pagerarr[] = "<td class=\"highlight\"><b>$text</b></td><td class=\"pagebr\">&nbsp;</td>";
        }
        $pagerstr = join("", $pagerarr);
        $pagertop = "<table class=\"main\"><tr>$pager $pagerstr $pager2</tr></table>\n";
        $pagerbottom = "Overall $count on $i Page(s), showing $rpp per page.<br /><br /><table class=\"main\">$pager $pagerstr $pager2</table>\n";
    } else {
        $pagertop = $pager;
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
}

stdhead("Invites In Play");
$invite = number_format(get_row_count("users", "WHERE invites > '0'"));

begin_table();
$page = $_GET['page'];
$perpage = 50;

$res = sql_query("SELECT COUNT(*) FROM users WHERE invites >'0'") or sqlerr();
$arr = mysql_fetch_row($res);
$count = $arr[0];
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
    list($pagertop, $pagerbottom, $limit) = nice_pager(50, $count, "" . $_SERVER['PHP_SELF'] . "?" . $addparam);

    $query = "SELECT * FROM users WHERE invites >'0' ORDER BY username $limit";
    $res = sql_query($query) or die(mysql_error());
} else
    unset($res);
$num = mysql_num_rows($res);
echo("<table border=0 class=invitesouter width=675 cellspacing=0 cellpadding=2>\n");
if ($count) {
    echo("<tr><td align=\"left\" colspan=\"8\">");
    echo($pagertop);
    echo("</td></tr>");
}
echo("<tr align=center><td class=colhead width=90>User Name</td>
<td class=colhead width=70>Registered</td>
<td class=colhead width=75>Last access</td>
<td class=colhead width=75>User Class</td>
<td class=colhead width=70>Downloaded</td>
<td class=colhead width=70>UpLoaded</td>
<td class=colhead width=45>Ratio</td>
<td class=colhead width=65>invites</td>");

while ($arr = mysql_fetch_assoc($res)) {
    if ($arr['added'] == '0000-00-00 00:00:00')
        $arr['added'] = '-';
    if ($arr['last_access'] == '0000-00-00 00:00:00')
        $arr['last_access'] = '-';

    if ($arr["downloaded"] != 0)
        $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
    else
        $ratio = "---";
    $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
    $uploaded = prefixed($arr["uploaded"]);
    $downloaded = prefixed($arr["downloaded"]);
    $added = substr($arr['added'], 0, 10);
    $last_access = substr($arr['last_access'], 0, 10);
    $class = get_user_class_name($arr["class"]);
    if ($arr[invites] < 3) { // change the number 5 to any number you like , that number is used to trace users with invites more than 5 invites .
        echo("<tr><td align=left class=invite><a href=userdetails.php?id=$arr[id]><b>$arr[username]</b></a></td>
<td align=center class=invite>$added</td>
<td align=center class=invite>$last_access</td>
<td align=center class=invite>$class</td>
<td align=center class=invite>$downloaded</td>
<td align=center class=invite>$uploaded</td>
<td align=center class=invite>$ratio</td>
<td align=center class=invite>$arr[invites]</td></tr>");
    } else {
        echo("<tr><td align=left class=wtf><a href=userdetails.php?id=$arr[id]><b>$arr[username]</b></a></td>
<td align=center class=wtf>$added</td>
<td align=center class=wtf>$last_access</td>
<td align=center class=wtf>$class</td>
<td align=center class=wtf>$downloaded</td>
<td align=center class=wtf>$uploaded</td>
<td align=center class=wtf>$ratio</td>
<td align=center class=wtf>$arr[invites]</td></tr>");
    }
}

if ($count) {
    echo("<tr><td align=\"left\" colspan=\"8\">");
    echo($pagerbottom);
    echo("</td></tr>");
}
echo("</table>");
stdfoot();

?>
