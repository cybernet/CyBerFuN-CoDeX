<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Banned Emails - Nosey Cunt !");

/* Ban emails by x0r @tbdev.net */

$remove = $_GET['remove'] + 0;
if ($remove) {
    mysql_query("DELETE FROM bannedemails WHERE id = '$remove'") or sqlerr(__FILE__, __LINE__);
    write_log("Ban $remove was removed by $CURUSER[username]");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $comment = trim($_POST["comment"]);
    if (!$email || !$comment)
        stderr("Error", "Missing form data.");
    mysql_query("INSERT INTO bannedemails (added, addedby, comment, email) VALUES(" . sqlesc(get_date_time()) . ", $CURUSER[id], " . sqlesc($comment) . ", " . sqlesc($email) . ")") or sqlerr(__FILE__, __LINE__);
    header("Location: $_SERVER[REQUEST_URI]");
    die;
}

ob_start("ob_gzhandler");

$res = mysql_query("SELECT * FROM bannedemails ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

stdhead("Banned emails");

print("<h1>Current Bans</h1>\n");

if (mysql_num_rows($res) == 0)
    print("<p align=center><b>Nothing found</b></p>\n");
else {
    print("<table border=1 cellspacing=0 cellpadding=5>\n");
    print("<tr><td class=colhead>Added</td><td class=colhead align=left>Email</td>" . "<td class=colhead align=left>By</td><td class=colhead align=left>Comment</td><td class=colhead>Remove</td></tr>\n");

    while ($arr = mysql_fetch_assoc($res)) {
        $r2 = mysql_query("SELECT username FROM users WHERE id = $arr[addedby]") or sqlerr(__FILE__, __LINE__);
        $a2 = mysql_fetch_assoc($r2);
        print("<tr><td>$arr[added]</td><td align=left>$arr[email]</td><td align=left><a href=userdetails.php?id=$arr[addedby]>$a2[username]" . "</a></td><td align=left>$arr[comment]</td><td><a href=bannedemails.php?remove=$arr[id]>Remove</a></td></tr>\n");
    }
    print("</table>\n");
}

print("<h2>Add ban</h2>\n");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
print("<form method=\"post\" action=\"bannedemails.php\">\n");
print("<tr><td class=rowhead>Email</td><td><input type=\"text\" name=\"email\" size=\"40\"></td>\n");
print("<tr><td class=rowhead>Comment</td><td><input type=\"text\" name=\"comment\" size=\"40\"></td>\n");
print("<tr><td colspan=2>Use *@email.com as wildcard for domain.</td></tr>\n");
print("<tr><td colspan=2><input type=\"submit\" value=\"Okay\" class=\"btn\"></td></tr>\n");
print("</form>\n</table>\n");

stdfoot();

?>