<?php
ob_start("ob_gzhandler");
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
stdhead("Delete Torrent");
begin_main_frame();

?>
<?php
if ($_GET[mode] == "delete") {
    if (get_user_class() >= UC_MODERATOR) {
        $table = "torrents";
        $table2 = "sitelog";
        $res = sql_query("SELECT id, name,owner,seeders FROM torrents WHERE id IN (" . implode(", ", unsafeChar($_POST[delete])) . ")") or sqlerr(__FILE__, __LINE__);
        echo"The following torrents has been deleted:<br><br>";
        while ($row = mysql_fetch_array($res)) {
            echo"ID: " . safeChar($row[id]) . " - " . safeChar($row[name]) . "<br>";
            $reasonstr = "Dead: 0 seeders, 0 leechers = 0 peers total";
            $text = "Torrent " . safeChar($row[id]) . " (" . safeChar($row[name]) . ") was deleted by " . safeChar($CURUSER[username]) . "($reasonstr)\n";
            $added = sqlesc(get_date_time());
            write_log("torrentdelete","Torrent $id ($row[name]) was deleted by '<a href=\"userdetails.php?id=$CURUSER[id]\">$CURUSER[username]</a>' Reason : ($reasonstr)\n");
        }
        sql_query("DELETE FROM $table where id IN (" . implode(", ", unsafeChar($_POST[delete])) . ")") or sqlerr(__FILE__, __LINE__);
    } else {
        echo"You are not allowed to view this page";
    }
}

?>
<?php
end_main_frame();
stdfoot();

?>