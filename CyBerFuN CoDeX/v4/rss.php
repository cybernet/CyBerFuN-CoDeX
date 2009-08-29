<?php
require "include/bittorrent.php";
dbconn();

$passkey = $_GET["passkey"];
if ($passkey) {
    $user = mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM users WHERE passkey =" . sqlesc($passkey) . ""));
    if ($user[0] != 1)
        exit();
}
$feed = $_GET["feed"];
$cat = $_GET["cat"];
// name a category
$res = mysql_query("SELECT id, name FROM categories");
while ($cat = mysql_fetch_assoc($res))
$category[$cat['id']] = $cat['name'];
// RSS Feed description
$DESCR = "This is a private tracker, and you have to register before you can get full access to the site. Before you do anything here at YourSite we suggest you read the rules! There are only a few rules to abide by, and we do enforce them!";
// by category ?
if ($cat)
    foreach ($_GET["cat"] as $cat)
    $cats[] = 0 + $cat;
if ($cats)
    $where = "category IN (" . implode(", ", $cats) . ") AND";
// start the RSS feed output
header("Content-Type: application/xml");
print("<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n<rss version=\"0.91\">\n<channel>\n" . "<title>" . $SITENAME . "</title>\n<link>" . $BASEURL . "</link>\n<description>" . $DESCR . "</description>\n" . "<language>en-usde</language>\n<copyright>Copyright ? 2004 " . $SITENAME . "</copyright>\n<webMaster>" . $SITEEMAIL . "</webMaster>\n" . "<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" . "<width>16</width>\n<height>16</height>\n<description>" . $DESCR . "</description>\n</image>\n");
// get all vars
$res = mysql_query("SELECT id,name,descr,filename,size,category,seeders,leechers,added FROM torrents WHERE $where visible='yes' ORDER BY added DESC LIMIT 15") or sqlerr(__FILE__, __LINE__);
while ($row = mysql_fetch_row($res)) {
    list($id, $name, $descr, $filename, $size, $cat, $seeders, $leechers, $added, $catname) = $row;
    // seeders ?
    if (($seeders) != 1) {
        $s = "s";
        $aktivs = "$seeders seeder$s";
    } else
        $aktivs = "no seeders";
    // leechers ?
    if ($leechers != 1) {
        $l = "s";
        $aktivl = "$leechers leecher$l";
    } else
        $aktivl = "no leecher";
    if (isset($_GET["feed"])) $feed = $_GET["feed"];
    else $feed = " ";
    // ddl or detail ?
    if ($feed == "dl")
        $link = "$BASEURL/rssdownload.php/$id/" . ($passkey ? "$passkey/" : "") . "$filename";
    else
        $link = "$BASEURL/details.php?id=$id&amp;hit=1";
    // measure the totalspeed
    if ($seeders >= 1 && $leechers >= 1) {
        $spd = mysql_query("SELECT (t.size * t.times_completed + SUM(p.downloaded)) / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS totalspeed FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND p.torrent = '$id' GROUP BY t.id ORDER BY added ASC LIMIT 15") or sqlerr(__FILE__, __LINE__);
        $a = mysql_fetch_assoc($spd);
        $totalspeed = prefixed($a["totalspeed"]) . "/s";
    } else
        $totalspeed = "no traffic";
    // output of all data
    echo("<item><title>" . htmlspecialchars($name) . "</title>\n<link>" . $link . "</link>\n<category>\nCategory: " . $category[$cat] . " </category>\n<size> Size: " . prefixed($size) . "</size>\n<status> Status: " . $aktivs . " and " . $aktivl . "</status>\n<speed> Speed: " . $totalspeed . "</speed>\n<added> Added: " . $added . "</added>\n<description> Description:\n " . htmlspecialchars($descr) . "\n</description>\n</item>\n");
}
echo("</channel>\n</rss>\n");

?>