<?php
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
dbconn(true);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Admin Bookmarks - Nosey Cunt !");
stdhead("Staff Bookmarks");
begin_main_frame();

$addbookmark = number_format(get_row_count("users", "WHERE addbookmark='yes'"));
begin_frame("In total ($addbookmark)", true);
begin_table();

?>
<table cellpadding="4" cellspacing="1" border="0" style="width:800px" class="tableinborder" ><tr><td class="tabletitle">ID</td><td class="tabletitle" align="left">Username</td><td class="tabletitle" align="left">Suspicion</td><td class="tabletitle" align="left">Uploaded</td><td class="tabletitle" align="left">Downloaded</td><td class="tabletitle" align="left">Ratio</td></tr>
<?php

$res = mysql_query("SELECT id,username,bookmcomment,uploaded,downloaded FROM users WHERE addbookmark='yes' ORDER BY id") or print(mysql_error());

while ($arr = @mysql_fetch_assoc($res)) {
    if ($arr["downloaded"] != 0) {
        $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
    } else {
        $ratio = "---";
    }
    $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
    $uploaded = prefixed($arr["uploaded"]);
    $downloaded = prefixed($arr["downloaded"]);
    $uploaded = str_replace(" ", "<br>", prefixed($arr["uploaded"]));
    $downloaded = str_replace(" ", "<br>", prefixed($arr["downloaded"]));

    echo "<tr><td class=table >" . safeChar($arr[id]) . "</td><td class=table align=\"left\"><b><a href=userdetails.php?id=" . safeChar($arr[id]) . ">" . safeChar($arr[username]) . "</b></td><td class=table align=\"left\">" . safeChar($arr[bookmcomment]) . "</a></td><td class=table align=\"left\">" . $uploaded . "</td></a></td><td class=table align=\"left\">" . $downloaded . "</td><td class=table align=\"left\">$ratio</td></tr>";
}
end_main_frame();
end_frame();
end_table();
stdfoot();

?>