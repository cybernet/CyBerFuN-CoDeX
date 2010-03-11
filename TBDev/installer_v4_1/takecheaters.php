<?php
include("include/bittorrent.php");
dbconn();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if (get_user_class() < UC_ADMINISTRATOR)
    die();

if ($_POST["nowarned"] == "nowarned") {
    if (empty($_POST["desact"]) && empty($_POST["remove"]))
        stderr("Error...", "You must select a user.");

    if (!empty($_POST["remove"])) {
        mysql_query("DELETE FROM cheaters WHERE id IN (" . implode(", ", $_POST["remove"]) . ")") or sqlerr(__FILE__, __LINE__);
    }

    if (!empty($_POST["desact"])) {
        mysql_query("UPDATE users SET enabled = 'no' WHERE id IN (" . implode(", ", $_POST["desact"]) . ")") or sqlerr(__FILE__, __LINE__);
    }
}

header("Refresh: 0; url=/cheaters.php");

?>