<?php
require "include/bittorrent.php";
dbconn();
maxcoder();
// remade by putyn@ tbdev
$passkey = sqlesc($_GET["passkey"]);

$count = get_row_count("users", "WHERE passkey=$passkey LIMIT 1");
if ($count == 0)
    stderr("Err", "You must be registred to $SITENAME to have acces to this ! ");
else {
    $descr = "$SITENAME is powerd by tbdev.net"; //tracker description !

    // start the RSS feed output
    header("Content-Type: application/xml");
    print("<?xml version=\"1.0\" encoding=\"windows-1251\" ?>\n<rss version=\"0.91\">\n<channel>\n" . "<title>" . $SITENAME . "</title>\n<link>" . $BASEURL . "</link>\n<description>" . $descr . "</description>\n" . "<language>en-usde</language>\n<copyright>Copyright © 2008 " . $SITENAME . "</copyright>\n<webMaster>" . $SITEEMAIL . "</webMaster>\n" . "<image><title>" . $SITENAME . "</title>\n<url>" . $BASEURL . "/favicon.ico</url>\n<link>" . $BASEURL . "</link>\n" . "<width>16</width>\n<height>16</height>\n</image>\n");
    // start news shit
    $res = mysql_query("SELECT n.id,n.added,n.body,n.title,n.userid , u.username FROM news as n LEFT JOIN users as u ON n.userid=u.id WHERE ADDDATE(n.added, INTERVAL 45 DAY) > NOW() ORDER BY added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($arr = mysql_fetch_array($res)) {
        $body = str_replace(array("[", "]", "<" , ">" , "&" , "'" , "\""), array("[ ", " ]" , "&lt;", "&gt;", "&amp;", "&apos;", "&quot;"), $arr["body"]);
        $title = $arr["title"];
        $added = $arr["added"];
        $uname = htmlspecialchars($arr["username"]);
        print("
<item>
<title>" . $title . " added on " . $added . " by " . $uname . "</title><description>\n" . $body . "\n</description>
</item>");
    }
}
print("</channel>\n</rss>\n");

?>