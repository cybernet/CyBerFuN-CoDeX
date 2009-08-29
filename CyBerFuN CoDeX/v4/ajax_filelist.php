<?php
header("Content-Type: text/html; charset=iso-8859-1");
require_once("include/bittorrent.php");
dbconn();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

$id = 0 + $_GET["id"];
$s = "<table width=500 class=colorss class=main border=\"1\" cellspacing=0 cellpadding=\"5\">\n";
$subres = sql_query("SELECT * FROM files WHERE torrent = $id ORDER BY id");
$s .= "<tr><td width=500 class=colhead>Type</td><td class=colhead>Path</td><td class=colhead align=right>Size</td></tr>\n";
while ($subrow = mysql_fetch_array($subres)) {
preg_match('/\\.([A-Za-z0-9]+)$/', $subrow["filename"], $ext);
$ext = strtolower($ext[1]);
if (!file_exists("pic/icons/" . $ext . ".png")) $ext = "Unknown";
$s .= "<tr><td align\"center\"><img align=center src=\"pic/icons/" . $ext . ".png\" alt=\"$ext file\"></td><td class=tableb2 width=700>" . safeChar($subrow["filename"]) . "</td><td align=\"right\">" . prefixed($subrow["size"]) . "</td></tr>\n";
}
$s .= "</table>\n";
echo $s;

?>