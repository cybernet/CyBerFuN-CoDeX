<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
$id = isset($_GET["id"]) ? 0 + $_GET["id"] : "";

$res = mysql_query("SELECT id, name from torrents where id=" . unsafeChar($id) . "");
if (mysql_num_rows($res) == 0)
    stderr("Err", "No torrent with this id ");
else {
    $arr = mysql_fetch_array($res);
    stdhead("Bookmarks for " . $arr["name"] . "");

    ?>
<h2>Bookmarks for torrent <br/><a href=details.php?id=<?php echo $id?>><?php echo $arr['name']?></a></h2><table>
<?php

    $res = mysql_query("SELECT b.userid, u.username FROM bookmarks AS b LEFT JOIN users AS u ON b.userid=u.id WHERE torrentid=" . unsafeChar($id) . " AND b.private = 'no' ORDER BY u.username ASC ") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {

        ?>
<tr><td class='colhead'>Username</td></tr>
<?php
        while ($ar = mysql_fetch_array($res)) {

            ?>
<tr><td align='center'><a href='userdetails.php?id=<?php echo $ar['userid']?>'><?php echo $ar['username']?></a></td></tr>
<?php
        }
    } else
        echo' No public bookmarks for this torrent';
    echo '</table>';
    stdfoot();
}

?>