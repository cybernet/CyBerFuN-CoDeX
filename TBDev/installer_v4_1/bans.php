<?php
require_once ("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Bans - Nosey Cunt !");

$doUpdate = false;

$remove = isset($_GET['remove']) ? (int)$_GET['remove'] : 0;
if (is_valid_id($remove)) {
    mysql_query("DELETE FROM bans WHERE id=$remove") or sqlerr();
    write_log("Ban $remove was removed by " . $CURUSER['id'] . " (" . $CURUSER['username'] . ")");
    $doUpdate = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_ADMINISTRATOR) {
    // we doing just a cache rewrite or an add & rewrite?
    if (isset($_POST['cacheit']))
        $doUpdate = true;
    else {
        $first = trim($_POST["first"]);
        $last = trim($_POST["last"]);
        $comment = trim($_POST["comment"]);
        if (!$first || !$last || !$comment)
            stderr("Error", "Missing form data.");
        $first = ip2long($first);
        $last = ip2long($last);
        if ($first == -1 || $first === false || $last == -1 || $last === false)
            stderr("Error", "Bad IP address.");
        $comment = sqlesc($comment);
        $added = sqlesc(get_date_time());

        mysql_query("INSERT INTO bans (added, addedby, first, last, comment)
                VALUES($added, {$CURUSER['id']}, $first, $last, $comment)") or sqlerr(__FILE__, __LINE__);
        $doUpdate = true;
        // header("Location: $BASEURL/bans.php");
        // die;
    }
}

ob_start("ob_gzhandler");

$res = mysql_query("SELECT b.*, u.username FROM bans b LEFT JOIN users u on b.addedby = u.id ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

$configfile = "<" . "?php\n\n\$bans = array(\n";

stdhead("Bans");

echo("<h1>Current Bans</h1>\n");

if (mysql_num_rows($res) == 0)
    echo("<p align=center><b>Nothing found</b></p>\n");
else {
    echo("<table border=1 cellspacing=0 cellpadding=5>\n");
    echo("<tr><td class=colhead>Added</td><td class=colhead align=left>First IP</td><td class=colhead align=left>Last IP</td>" . "<td class=colhead align=left>By</td><td class=colhead align=left>Comment</td><td class=colhead>Remove</td></tr>\n");

    while ($arr = mysql_fetch_assoc($res)) {
        if ($doUpdate) {
            $configfile .= "array('id'=> '{$arr['id']}', 'first'=> {$arr['first']}, 'last'=> {$arr['last']}),\n";
        }
        $arr["first"] = long2ip($arr["first"]);
        $arr["last"] = long2ip($arr["last"]);
        echo("<tr><td>{$arr['added']}</td><td align=left>{$arr['first']}</td><td align=left>{$arr['last']}</td><td align=left><a href=userdetails.php?id={$arr['addedby']}>{$arr['username']}" . "</a></td><td align=left>" . safechar($arr['comment'], ENT_QUOTES) . "</td><td><a href=bans.php?remove={$arr['id']}>Remove</a></td></tr>\n");
    }
    echo("</table>\n");
}

if ($doUpdate) {
    $configfile .= "\n);\n\n?" . ">";
    $filenum = fopen ("cache/bans_cache.php", "w");
    ftruncate($filenum, 0);
    fwrite($filenum, $configfile);
    fclose($filenum);
}

if (get_user_class() >= UC_ADMINISTRATOR) {
    echo("<h2>Add ban</h2>\n");
    echo("<table border=1 cellspacing=0 cellpadding=5>\n");
    echo("<form method=post action=bans.php>\n");
    echo("<tr><td class=rowhead>First IP</td><td><input type=text name=first size=40></td>\n");
    echo("<tr><td class=rowhead>Last IP</td><td><input type=text name=last size=40></td>\n");
    echo("<tr><td class=rowhead>Comment</td><td><input type=text name=comment size=40></td>\n");
    echo("<tr><td colspan=2 align='center'><input type=submit name='okay' value='Add' class=btn></td></tr>\n");
    echo("<tr><td colspan=2 align='center'><input type=submit name='cacheit' value='Cache' class=btn></td></tr>\n");

    echo("</form>\n</table>\n");
}

stdfoot();

?>