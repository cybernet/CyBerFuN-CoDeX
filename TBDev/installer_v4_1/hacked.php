<?php
include 'include/bittorrent.php';
dbconn();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR) {
    stderr("Error", "Permission denied.");
}
stdhead(' Hacked Azureus Clients');

?>
<table width=750 class=main border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>



<h2>Hacked Az Clients</h2>

<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text><ul>
<?php
function getagent($httpagent)
{
    if (preg_match("/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/BitTorrent\\/S-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "Shadow's/$matches[1]";
    elseif (preg_match("/BitTorrent\\/U-([0-9]+\\.[0-9]+\\.[0-9]+)/", $httpagent, $matches))
        return "UPnP/$matches[1]";
    elseif (preg_match("/^BitTorrent\\/T-(.+)$/", $httpagent, $matches))
        return "BitTornado/$matches[1]";
    elseif (preg_match("/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "BitTorrent/$matches[1]";
    elseif (preg_match("/^Python-urllib\\/.+?, BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "BitTorrent/$matches[1]";
    elseif (ereg("^BitTorrent\\/BitSpirit$", $httpagent))
        return "BitSpirit";
    elseif (preg_match("/^BitTorrent\\/brst(.+)/", $httpagent, $matches))
        return "Burst/$matches[1]";
    elseif (preg_match("/^RAZA (.+)$/", $httpagent, $matches))
        return "Shareaza/$matches[1]";
    else
        return "---";
}

function getclient($peerid, $agent = null)
{
    $azclients = array('AZ' => 'Azureus',
        'BB' => 'BitBuddy',
        'CT' => 'CTorrent',
        'MT' => 'MoonlightTorrent',
        'LT' => 'LibTorrent',
        'BX' => 'Bittorrent X',
        'TS' => 'TorrentStorm',
        'TN' => 'TorrentDotNET',
        'SS' => 'SwarmScope',
        'XT' => 'XanTorrent',
        'BS' => 'BitSlave'); //azuranus style
    $sclients = array('S' => 'Shadow',
        'U' => 'UPnP',
        'T' => 'BitTornado',
        'A' => 'ABC'); //shadow style clients
    $bcclients = array('exbc' => 'BitComet'); //bitcomet style
    if (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_B[0-9]+)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_[a-zA-Z]+)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/^Java\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/<2.0.7.0";
    elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Azureus/$matches[1]";
    elseif (preg_match("/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches)) {
        if (substr($peer_id, 0, 6) == "exbc\08")
            return "BitComet/0.56";
        elseif (substr($peer_id, 0, 6) == "exbc\09")
            return "BitComet/0.57";
        elseif (substr($peer_id, 0, 4) == "FUTB")
            return "BitComet/0.57p";
        elseif (substr($peer_id, 0, 6) == "exbc\0:")
            return "BitComet/0.58";
        elseif (substr($peer_id, 0, 8) == "-BC0059-")
            return "BitComet/0.59";
        elseif (substr($peer_id, 0, 8) == "-BC0060-")
            return "BitComet/0.60";
        elseif (substr($peer_id, 0, 7) == "exbc\0L")
            return "BitLord/1.0";
        elseif (substr($peer_id, 0, 7) == "exbcL")
            return "BitLord/1.1";
        else
            return "BitTorrent/$matches[1]";
    } elseif (ereg("^uTorrent", $httpagent)) {
        if (substr($peer_id, 0, 8) == "-UT1130-")
            return "uTorrent 1.1.3";
        if (substr($peer_id, 0, 8) == "-UT1140-")
            return "uTorrent 1.1.4";
        if (substr($peer_id, 0, 8) == "-UT1150-")
            return "uTorrent 1.1.5";
        if (substr($peer_id, 0, 8) == "-UT1161-")
            return "uTorrent 1.1.6.1";
        if (substr($peer_id, 0, 8) == "-UT1171-")
            return "uTorrent 1.1.7.1";
        else
            return "uTorrent";
    } elseif (preg_match("/^Python-urllib\/.+?, BitTorrent\/([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "BitTorrent/$matches[1]";
    elseif (preg_match("/^Python-urllib\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
        return "G3 Torrent";
    elseif (preg_match("/^BitTorrent\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
    elseif (preg_match("/^ABC ([0-9]+\.[0-9]+(\.[0-9]+)*)\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
    elseif (preg_match("/^ABC\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "ABC/$matches[1]";
    elseif (ereg("^BitTorrent\/BitSpirit$", $httpagent))
        return "BitSpirit";
    elseif (preg_match("/^BitsOnWheels( |\/)([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "BitsOnWheels/$matches[2]";
    elseif (preg_match("/BitTorrentPlus\/(.+)$/", $httpagent, $matches))
        return "BitTorrent Plus!/$matches[1]";
    elseif (preg_match("/^BitTorrent\/brst(.+)/", $httpagent, $matches))
        return "Burst/$matches[1]";
    elseif (preg_match("/^BitTor(rent|nado)\\/T-(.+)$/", $httpagent, $matches))
        return "BitTornado/$matches[2]";
    elseif (preg_match("/^BitTornado\\/T-(.+)$/", $httpagent, $matches))
        return "BitTornado/$matches[1]";
    elseif (preg_match("/^Python-urllib\/.+?, BitTorrent\/T-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "BitTornado/$matches[1]";
    elseif (ereg("^Deadman Walking", $httpagent))
        return "Deadman Walking";
    elseif (preg_match("/ed2k_plugin v([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "eDonkey/$matches[1]";
    elseif (preg_match("/^eXeem( |\/)([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
        return "eXeem$matches[1]$matches[2]";
    elseif (preg_match("/^libtorrent\/(.+)$/", $httpagent, $matches))
        return "libtorrent/$matches[1]";
    elseif (preg_match("/MLDonkey( |\/)(.+)$/i", $httpagent, $matches))
        return "MLdonkey$matches[1]$matches[2]";
    elseif (ereg("^0P3R4H", $httpagent))
        return "OperaBT";
    elseif (preg_match("/Rufus\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "Rufus/$matches[1]";
    elseif (preg_match("/BitTorrent\/S-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "Shadow's/$matches[1]";
    elseif (preg_match("/^RAZA (.+)$/", $httpagent, $matches))
        return "Shareaza/$matches[1]";
    elseif (preg_match("/^Shareaza (.+)$/", $httpagent, $matches))
        return "Shareaza/$matches[1]";
    elseif (preg_match("/^Python-urllib\/.+?, BitTorrent\/TurboBT ([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
        return "TurboBT/$matches[1]";
    elseif (preg_match("/BitTorrent\/U-([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
        return "UPnP/$matches[1]";
    elseif (preg_match("/^uTorrent(|(\/[0-9][0-9][0-9][0-9]))$/i", $httpagent, $matches))
        return "uTorrent$matches[1]";
    elseif (ereg("^DansClient", $httpagent))
        return "XanTorrent";
    elseif (ereg("^XBT Client", $httpagent))
        return "XBT Client";
    elseif (get_user_class() >= UC_UPLOADER && preg_match("/^A\\x02\\x06\\x09/", $peerid, $matches))
        return "Az-Hacked";
    else {
        if ($agent)
            return getagent($agent);
    }
}

$result = mysql_query("SELECT p.peer_id, p.agent, u.username, u.id FROM peers AS p, users AS u WHERE p.userid =
u.id");
while ($row = mysql_fetch_assoc($result)) {
    if (getclient($row["peer_id"], $row['agent']) == 'Az-Hacked') {
        echo '<a href="$BASEURL/userdetails.php?id=' . $row['id'] . '">
  ' . $row['username'] . '</a><br />';
    }
}

?>
</table></table>
<?php php >
stdfoot();

?>