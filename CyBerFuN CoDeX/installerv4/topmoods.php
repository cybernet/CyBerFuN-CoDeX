<?php
require_once "include/bittorrent.php";
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
// error_reporting(E_ALL ^ E_NOTICE); // overriding config settings cuz of notice errors here, on todo list! :p
stdhead('Top Moods');

?><table>
<tr><td class='embedded'>
<small>You may select your mood by clicking on the smiley in the top left corner of the site.</small></td></tr></table>
<?php
$query = "SELECT mood, COUNT(mood) as moodcount FROM users GROUP BY mood ORDER BY moodcount DESC";
$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);

$abba = "<h2>Top Moods</h2>" . "    <table border='1' cellspacing='0' cellpadding='5'>" . "<tr><td class='colhead' align='center'>Count</td><td class='colhead' align='center'>Mood</td><td class='colhead' align='center'>Icon</td></tr>\n";
while ($arr = mysql_fetch_assoc($res)) {
    foreach($mood as $key => $value)
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $mooduname = safe($change[$arr['mood']]['name']);
    $moodupic = safe($change[$arr['mood']]['image']);
    $moodcount = 0 + $arr['moodcount'];

    $abba .= "<tr><td align='center'>" . $moodcount . "</td><td align='center'>" . $mooduname . "</td><td align='center'><img src='pic/smilies/" . $moodupic . "' border='0' alt='' /></td></tr>\n";
}

$abba .= "</table>\n";

echo $abba;
stdfoot();

?>