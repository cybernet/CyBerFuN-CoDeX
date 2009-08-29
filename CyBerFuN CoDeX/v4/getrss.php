<?php
require ("include/bittorrent.php");
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
$res = mysql_query("SELECT id, name FROM categories ORDER BY name");
while ($cat = mysql_fetch_assoc($res))
$catoptions .= "<input type=\"checkbox\" name=\"cat[]\" value=\"$cat[id]\" " . (strpos($CURUSER['notifs'], "[cat$cat[id]]") !== false ? " checked" : "") . "/>$cat[name]<br>";
$category[$cat['id']] = $cat['name'];

stdhead("RSS Feeds");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $link = "$BASEURL/rss.php";
    if ($_POST['feed'] == "dl")
        $query[] = "feed=dl";
    if (isset($_POST['cat']))
        $query[] = "cat=" . implode(',', $_POST['cat']);
    else {
        print("<br>You must select some categories<br>");
        stdfoot();
        die();
    }
    if ($_POST['login'] == "passkey")
        $query[] = "passkey=$CURUSER[passkey]";
    $queries = implode("&", $query);
    if ($queries)
        $link .= "?$queries";

    print("<br> Use the following url in your RSS reader: <br><br><b>$link</b><br>");
    print('<link rel="alternate" type="application/rss+xml" title="Latest Torrents" href="' . $link . '">');
    stdfoot();
    die();
}

?>
<b>Use the download / alternative link options to generate a vaild rss link for auto download in your clients<b></b></a><br><br>
<FORM method="POST" action="getrss.php">
<input type="hidden" name="norss" value="none">
<table border="1" cellspacing="1" cellpadding="5">
<TR>
<TD class="rowhead">Categories to retrieve:
</TD>
<TD><?=$catoptions?>
</TD>
</TR>
<TR>
<TD class="rowhead">Feed type:
</TD>
<TD>
<INPUT type="radio" name="feed" value="web" />Web link<BR>
<INPUT type="radio" name="feed" value="dl" />Download link
</TD>
</TR>
<TR>
<TD class="rowhead">Login type:
</TD>
<TD>
<INPUT type="radio" name="login" value="cookie" />Standard (cookies)<BR>
<INPUT type="radio" name="login" value="passkey" checked />Alternative (no cookies)
</TD>
</TR>
<TR>
<TD colspan="2" align="center">
<BUTTON type="submit">Generate RSS link</BUTTON>
</TD>
</TR>
</TABLE>
</FORM>

<?php
stdfoot();

?>