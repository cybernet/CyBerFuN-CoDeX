<?php
require ("include/bittorrent.php");
// require ("include/user_functions.php");
require ("include/bbcode_functions.php");
ob_start("ob_gzhandler");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
stdhead("Episodes");
// define shows here
$showcount = 13;
$showname[1] = "Lost";
$showimg[1] = "pic/episodes/lost.jpg";

$showname[2] = "Prison Break";
$showimg[2] = "pic/episodes/prbreak.jpg";

$showname[3] = "My Name is Earl";
$showimg[3] = "pic/episodes/earl.jpg";

$showname[4] = "House";
$showimg[4] = "pic/episodes/house.jpg";

$showname[5] = "Smallville";
$showimg[5] = "pic/episodes/small.jpg";

$showname[6] = "Heroes";
$showimg[6] = "pic/episodes/heroes.jpg";

$showname[7] = "Scrubs";
$showimg[7] = "pic/episodes/scrubs.jpg";

$showname[8] = "CSI";
$showimg[8] = "pic/episodes/csi.jpg";

$showname[9] = "Battlestar Galactica";
$showimg[9] = "pic/episodes/battle.jpg";

$showname[10] = "One Tree Hill";
$showimg[10] = "pic/episodes/oth.jpg";

$showname[10] = "OC";
$showimg[10] = "pic/episodes/oc.jpg";

$showname[11] = "24";
$showimg[11] = "pic/episodes/24.jpg";

$showname[12] = "The Simpsons";
$showimg[12] = "pic/episodes/simpson.jpg";

$showname[13] = "The Unit";
$showimg[13] = "pic/episodes/unit.jpg";

function get_show($show, $exact = "", $episode = "")
{
    if (!$show) {
        return false;
    }

    if ($fp = fopen("http://www.tvrage.com/quickinfo.php?show=" . urlencode($show) . "&ep=" . urlencode($episode) . "&exact=" . urlencode($exact), "r")) {
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            list ($sec, $val) = explode('@', $line, 2);
            if ($sec == "Show Name") {
                $ret[0] = $val;
            } elseif ($sec == "Show URL") {
                $ret[1] = $val;
            } elseif ($sec == "Premiered") {
                $ret[2] = $val;
            } elseif ($sec == "Country") {
                $ret[7] = $val;
            } elseif ($sec == "Status") {
                $ret[8] = $val;
            } elseif ($sec == "Classification") {
                $ret[9] = $val;
            } elseif ($sec == "Latest Episode") {
                list ($ep, $title, $airdate) = explode('^', $val);
                $ret[3] = $ep . ", \"" . $title . "\" aired on " . $airdate;
            } elseif ($sec == "Next Episode") {
                list ($ep, $title, $airdate) = explode('^', $val);
                $ret[4] = $ep . ", \"" . $title . "\" airs on " . $airdate;
            } elseif ($sec == "Episode Info") {
                list ($ep, $title, $airdate) = explode('^', $val);
                $ret[5] = $ep . ", \"" . $title . "\" aired on " . $airdate;
            } elseif ($sec == "Episode URL") {
                $ret[6] = $val;
            }
        }
        fclose($fp);
        if ($ret[0]) {
            return $ret;
        }
    } else {
        return false;
    }
}
// search
?>
<br><img src=/pic/banner.jpg width=500 border=0><br>
<table width=500 cellpadding=1>
<tr><br>
<td class=colhead align=center>Search</td>
<tr><td align=center>
<form action='http://www.tvrage.com/search.php' method='GET'>
Search For: <select name='sonly'><option value='0'>Shows & People</option><option value='1'>Shows</option><option value='2'>People</option></select>
<input type='text' name='search'>
<input type='submit' value='Search!'>
</form>
</td></tr>
</tr>
</table>
<br><br>
<?php
// end of search
for($i = 1; $i < $showcount + 1; $i++) {
    $show_infos1 = get_show("$showname[$i]", "1");
    // view shows
    print("<table border=1 width=500 cellspacing=0 cellpadding=1>\n");
    print("<tr><td class=colhead align=center>" . $show_infos1[0] . "</td>\n");
    print("<tr><td class=colhead align=center><img src=$showimg[$i]></td></tr>\n");
    print("<tr><td align=center><table border=0 width=500 cellspacing=0 cellpadding=2>\n");
    print("<tr><td class=colhead align=center>Last Episode</td>\n");
    print("<td>" . $show_infos1[3] . "</td></tr>\n");
    print("<tr><td class=colhead align=center>Next Episode</td>\n");
    print("<td>" . $show_infos1[4] . "</td></tr>\n");
    print("<tr><td class=colhead align=center>Country</td>\n");
    print("<td>" . $show_infos1[7] . "</td></tr>\n");
    print("<tr><td class=colhead align=center>Status</td>\n");
    print("<td>" . $show_infos1[8] . "</td></tr>\n");
    print("<tr><td class=colhead align=center>Show Link</td>\n");
    print("<td><a class=altlink target=_blank href=" . $show_infos1[1] . ">Link</a></td></tr>\n");
    print("<td></tr></table>\n");
    print("</tr></table><br><br>\n");
}
// end
// start rss read
define('MAGPIE_DIR', 'magpierss/');
define('MAGPIE_CACHE_DIR', 'cache/magpie_cache');
require_once(MAGPIE_DIR . 'rss_fetch.inc');
print("<table border=1 width=500 cellspacing=0 cellpadding=3>\n");
print("<tr>\n\t<td class=colhead align=center width=60>Time</td>\n\t<td class=colhead align=center width=220>Name</td>\n\t<td class=colhead align=center width=220>Description</td>\n</tr>\n");
$url = 'http://www.tvrage.com/myrss.php';
if ($url) {
    $rss = fetch_rss($url);
    foreach ($rss->items as $item) {
        if ($item['description'] == '') $chas = $item['title'];
        $href = $item['link'];
        $title = $item['title'];
        $title = str_replace(" - ", "", $title);
        $description = $item['description'];
        if ($item['description'] <> '') print("<tr>\n\t<td align=center>$chas</td>\n\t<td align=center><a target =_blank href=$href>$title</a></td>\n\t");
        if ($item['description'] <> '') print("<td align=center>$description</a></td>\n</tr>\n");
    }
}
print("</table>\n");
// end
stdfoot();

?>