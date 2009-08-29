<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

function getagent($httpagent, $peer_id = "")
{
    if (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_B([0-9][0-9|*])(.+)$)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_CVS)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/^Java\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/<2.0.7.0";
    elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/BitTorrent\/S-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "Shadow's/$matches[1]";
    elseif (preg_match("/BitTorrent\/U-([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "UPnP/$matches[1]";
    elseif (preg_match("/^BitTor(rent|nado)\\/T-(.+)$/", $httpagent, $matches)) {
        $id = substr($peer_id, 0, 4);
        if ($id == "T03H" || $id == "T03I" || $id == "T03F") {
            return 'TorrentFlux';
        } else {
            return "BitTornado/$matches[2]";
        }
        return Unknown;
    } elseif (preg_match("/^BitTorrent\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
    elseif (preg_match("/^ABC ([0-9]+\.[0-9]+(\.[0-9]+)*)\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
    if (substr($peer_id, 0, 6) == "A310--")
        return "ABC/3.1";
    elseif (preg_match("/^ABC\/Tribler_ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "Tribler/$matches[1]";
    elseif (preg_match("/^Python-urllib\/.+?, BitTorrent\/([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "BitTorrent/$matches[1]";
    elseif (ereg("^BitTorrent\/BitSpirit$", $httpagent))
        return "BitSpirit";
    elseif (ereg("^DansClient", $httpagent))
        return "XanTorrent";
    elseif (preg_match("/^BitTorrent\/brst(.+)/", $httpagent, $matches))
        return "Burst/$matches[1]";
    elseif (preg_match("/^RAZA (.+)$/", $httpagent, $matches))
        return "Shareaza/$matches[1]";
    if (substr($peer_id, 0, 8) == "-SZ2210-")
        return "Shareaza/2.2.1.0";
    elseif (preg_match("/Rufus\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Rufus/$matches[1]";
    elseif (preg_match("/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches)) {
        if (substr($peer_id, 0, 6) == "exbc8")
            return "BitComet/0.56";
        elseif (substr($peer_id, 0, 8) == "-BC0070-")
            return "BitComet/0.70";
        elseif (substr($peer_id, 0, 8) == "-BC0071-")
            return "BitComet/0.71";
        elseif (substr($peer_id, 0, 8) == "-BC0072-")
            return "BitComet/0.72";
        elseif (substr($peer_id, 0, 8) == "-BC0073-")
            return "BitComet/0.73";
        elseif (substr($peer_id, 0, 8) == "-BC0074-")
            return "BitComet/0.74";
        elseif (substr($peer_id, 0, 8) == "-BC0075-")
            return "BitComet/0.75";
        elseif (substr($peer_id, 0, 8) == "-BC0076-")
            return "BitComet/0.76";
        elseif (substr($peer_id, 0, 8) == "-BC0077-")
            return "BitComet/0.77";
        elseif (substr($peer_id, 0, 8) == "-BC0078-")
            return "BitComet/0.78";
        elseif (substr($peer_id, 0, 8) == "-BC0079-")
            return "BitComet/0.79";
        elseif (substr($peer_id, 0, 8) == "-BC0080-")
            return "BitComet/0.80";
        elseif (substr($peer_id, 0, 8) == "-BC0081-")
            return "BitComet/0.81";
        elseif (substr($peer_id, 0, 8) == "-BC0082-")
            return "BitComet/0.82";
        elseif (substr($peer_id, 0, 8) == "-BC0083-")
            return "BitComet/0.83";
        elseif (substr($peer_id, 0, 8) == "-BC0084-")
            return "BitComet/0.84";
        elseif (substr($peer_id, 0, 8) == "-BC0085-")
            return "BitComet/0.85";
        elseif (substr($peer_id, 0, 8) == "-BC0086-")
            return "BitComet/0.86";
        elseif (substr($peer_id, 0, 7) == "exbcL")
            return "BitLord/1.0";
        elseif (substr($peer_id, 0, 7) == "exbcL")
            return "BitLord/1.1";
        else
            return "BitTorrent/$matches[1]";
    } elseif (preg_match("/^Python-urllib\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "G3 Torrent";
    elseif (preg_match("/MLDonkey\/([0-9]+).([0-9]+).([0-9]+)*/", $httpagent, $matches))
        return "MLDonkey/$matches[1].$matches[2].$matches[3]";
    elseif (preg_match("/ed2k_plugin v([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "eDonkey/$matches[1]";
    elseif (preg_match("/uTorrent\/([0-9]+)([0-9]+)([0-9]+)([0-9A-Z]+)/", $httpagent, $matches))
        return "µTorrent/$matches[1].$matches[2].$matches[3].$matches[4]";
    elseif (ereg("^0P3R4H", $httpagent))
        return "Opera BT Client";
    elseif (preg_match("/CT([0-9]+)([0-9]+)([0-9]+)([0-9]+)/", $peer_id, $matches))
        return "cTorrent/$matches[1].$matches[2].$matches[3].$matches[4]";
    elseif (preg_match("/Transmission\/([0-9]+).([0-9]+)/", $httpagent, $matches))
        return "Transmission/$matches[1].$matches[2]";
    elseif (preg_match("/KT([0-9]+)([0-9]+)([0-9]+)([0-9]+)/", $peer_id, $matches))
        return "KTorrent/$matches[1].$matches[2].$matches[3].$matches[4]";
    elseif (substr($peer_id, 0, 8) == "-MP130n-")
        return "MooPolice";
    elseif (preg_match("/Ares ([0-9]+).([0-9]+).([0-9]+)*/", $httpagent, $matches))
        return "Ares/$matches[1].$matches[2].$matches[3]";
    else
        return "---";
}

if ($CURUSER['class'] < UC_MODERATOR) {
    stderr('Error', 'Permission denied.');
}

$res = mysql_query('SELECT peers.agent, peer_id, userid, username FROM peers LEFT JOIN users ON peers.userid = users.id') or sqlerr(__FILE__, __LINE__);

stdhead('All Clients');

echo '<table align="center" border="3" cellspacing="0" cellpadding="5">' . "\n" . '<tr><td class="colhead">Client</td><td class="colhead">Peer ID</td><td class="colhead">Shown as (getagent func)</td><td class="colhead">Used By</td></tr>' . "\n";
while ($arr = mysql_fetch_assoc($res)) {
    echo '<tr><td align="left">' . safeChar($arr['agent']) . '</td><td align="left">' . safeChar($arr['peer_id']) . '</td>' . '<td align="left">' . safeChar(getagent($arr['agent'], $arr['peer_id'])) . '</td><td align="center"><a href="userdetails.php?id=' . $arr['userid'] . '"><b>' . safeChar($arr['username']) . '</b></a></td></tr>' . "\n";
}
echo "</table>\n";
stdfoot();

?>