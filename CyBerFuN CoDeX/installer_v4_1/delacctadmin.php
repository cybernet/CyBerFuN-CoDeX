<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();

if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Quick Delete - Nosey Cunt !");

if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);

    if (!$username)
        stderr("Error", "Please fill out the form correctly.");

    $res = mysql_query(

        "SELECT * FROM users WHERE username=" . sqlesc($username)) or sqlerr();
    if (mysql_num_rows($res) != 1)
        stderr("Error", "Bad user name or password. Please verify that all entered information is correct.");
    $arr = mysql_fetch_assoc($res);

    $id = $arr['id'];
    $res = mysql_query("DELETE FROM users WHERE id=" . sqlesc($id) . "") or sqlerr();
    if (mysql_affected_rows() != 1)
        stderr("Error", "Unable to delete the account.");
    write_log("userdelete", " : <b>$arr[username]</b> | ID: <b>$arr[id]</b> | <b>Deleted by staff</b>");
    stderr("Success", "The account <b>" . safeChar($username) . "</b> was deleted.");
}
stdhead("Delete account");

?>
<h1>Delete account</h1>
<table border=1 cellspacing=0 cellpadding=5>
<form method=post action=delacctadmin.php>
<tr><td class=rowhead>User name</td><td><input size=40 name=username></td></tr>

<tr><td colspan=2><input type=submit class=btn value='Delete'></td></tr>
</form>
</table>
<?php
stdfoot();

?>


