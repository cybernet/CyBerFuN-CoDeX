<?

// CyBerFuN.Ro
// By CyBerNe7
//            //
// http://cyberfun.ro/
// http://xlist.ro/

require_once("include/bittorrent.php");
dbconn();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

$id = 0 + $_GET["id"];
$res = mysql_query("SELECT torrents.*, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(torrents.last_action) AS lastseed, categories.name AS cat_name, users.username FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON torrents.owner = users.id WHERE torrents.id = $id")
	 or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_array($res);
$downloaders = array();
                $seeders = array();
                $subres = sql_query("SELECT seeder, finishedat, downloadoffset, uploadoffset, ip, port, uploaded, downloaded, to_go, UNIX_TIMESTAMP(started) AS st, connectable, agent, UNIX_TIMESTAMP(last_action) AS la, userid, peer_id FROM peers WHERE torrent =$id") or sqlerr();
                while ($subrow = mysql_fetch_assoc($subres)) {
                    if ($subrow["seeder"] == "yes")
                        $seeders[] = $subrow;
                    else
                        $downloaders[] = $subrow;
                }

                function leech_sort($a, $b)
                {
                    if (isset($_GET["usort"])) return seed_sort($a, $b);
                    $x = $a["to_go"];
                    $y = $b["to_go"];
                    if ($x == $y)
                        return 0;
                    if ($x < $y)
                        return -1;
                    return 1;
                }
                function seed_sort($a, $b)
                {
                    $x = $a["uploaded"];
                    $y = $b["uploaded"];
                    if ($x == $y)
                        return 0;
                    if ($x < $y)
                        return 1;
                    return -1;
                }
usort($seeders, "seed_sort");
usort($downloaders, "leech_sort");
echo dltable("Seeder(s)", $seeders, $row) ."<br /><a name=\"leechers\"></a>". dltable("leecher(s)", $downloaders, $row);
//echo dltable("<a name=\"seeders\">seeder</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">[Hide list]</a>", dltable("Seeder(s)", $seeders, $row), 1);
//echo dltable("<a name=\"leechers\">leecher</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">[Hide list]</a>", dltable("Leecher(s)", $downloaders, $row), 1);
// ========================================
// getAgent function by deliopoulos
// ========================================
function StdDecodePeerId($id_data, $id_name)
{
    $version_str = "";
    for ($i = 0; $i <= strlen($id_data); $i++) {
        $c = $id_data[$i];
        if ($id_name == "BitTornado" || $id_name == "ABC") {
            if ($c != '-' && ctype_digit($c)) $version_str .= "$c.";
            elseif ($c != '-' && ctype_alpha($c)) $version_str .= (ord($c)-55) . ".";
            else break;
        } elseif ($id_name == "BitComet" || $id_name == "BitBuddy" || $id_name == "Lphant" || $id_name == "BitPump" || $id_name == "BitTorrent Plus! v2") {
            if ($c != '-' && ctype_alnum($c)) {
                $version_str .= "$c";
                if ($i == 0) $version_str = intval($version_str) . ".";
            } else {
                $version_str .= ".";
                break;
            }
        } else {
            if ($c != '-' && ctype_alnum($c)) $version_str .= "$c.";
            else break;
        }
    }
    $version_str = substr($version_str, 0, strlen($version_str)-1);
    return "$id_name $version_str";
}
function MainlineDecodePeerId($id_data, $id_name)
{
    $version_str = "";
    for ($i = 0; $i <= strlen($id_data); $i++) {
        $c = $id_data[$i];
        if ($c != '-' && ctype_alnum($c)) $version_str .= "$c.";
    }
    $version_str = substr($version_str, 0, strlen($version_str)-1);
    return "$id_name $version_str";
}
function DecodeVersionString ($ver_data, $id_name)
{
    $version_str = "";
    $version_str .= intval(ord($ver_data[0]) + 0) . ".";
    $version_str .= intval(ord($ver_data[1]) / 10 + 0);
    $version_str .= intval(ord($ver_data[1]) % 10 + 0);
    return "$id_name $version_str";
}
function getagent($httpagent, $peer_id = "")
{
    // if($peer_id!="") $peer_id=hex2bin($peer_id);
    if (substr($peer_id, 0, 3) == '-AX') return StdDecodePeerId(substr($peer_id, 4, 4), "BitPump"); # AnalogX BitPump
    if (substr($peer_id, 0, 3) == '-BB') return StdDecodePeerId(substr($peer_id, 3, 5), "BitBuddy"); # BitBuddy
    if (substr($peer_id, 0, 3) == '-BC') return StdDecodePeerId(substr($peer_id, 4, 4), "BitComet"); # BitComet
    if (substr($peer_id, 0, 3) == '-BS') return StdDecodePeerId(substr($peer_id, 3, 7), "BTSlave"); # BTSlave
    if (substr($peer_id, 0, 3) == '-BX') return StdDecodePeerId(substr($peer_id, 3, 7), "BittorrentX"); # BittorrentX
    if (substr($peer_id, 0, 3) == '-CT') return "Ctorrent $peer_id[3].$peer_id[4].$peer_id[6]"; # CTorrent
    if (substr($peer_id, 0, 3) == '-KT') return StdDecodePeerId(substr($peer_id, 3, 7), "KTorrent"); # KTorrent
    if (substr($peer_id, 0, 3) == '-LT') return StdDecodePeerId(substr($peer_id, 3, 7), "libtorrent"); # libtorrent
    if (substr($peer_id, 0, 3) == '-LP') return StdDecodePeerId(substr($peer_id, 4, 4), "Lphant"); # Lphant
    if (substr($peer_id, 0, 3) == '-MP') return StdDecodePeerId(substr($peer_id, 3, 7), "MooPolice"); # MooPolice
    if (substr($peer_id, 0, 3) == '-MT') return StdDecodePeerId(substr($peer_id, 3, 7), "Moonlight"); # MoonlightTorrent
    if (substr($peer_id, 0, 3) == '-PO') return StdDecodePeerId(substr($peer_id, 3, 7), "PO Client"); #unidentified clients with versions
    if (substr($peer_id, 0, 3) == '-QT') return StdDecodePeerId(substr($peer_id, 3, 7), "Qt 4 Torrent"); # Qt 4 Torrent
    if (substr($peer_id, 0, 3) == '-RT') return StdDecodePeerId(substr($peer_id, 3, 7), "Retriever"); # Retriever
    if (substr($peer_id, 0, 3) == '-S2') return StdDecodePeerId(substr($peer_id, 3, 7), "S2 Client"); #unidentified clients with versions
    if (substr($peer_id, 0, 3) == '-SB') return StdDecodePeerId(substr($peer_id, 3, 7), "Swiftbit"); # Swiftbit
    if (substr($peer_id, 0, 3) == '-SN') return StdDecodePeerId(substr($peer_id, 3, 7), "ShareNet"); # ShareNet
    if (substr($peer_id, 0, 3) == '-SS') return StdDecodePeerId(substr($peer_id, 3, 7), "SwarmScope"); # SwarmScope
    if (substr($peer_id, 0, 3) == '-SZ') return StdDecodePeerId(substr($peer_id, 3, 7), "Shareaza"); # Shareaza
    if (preg_match("/^RAZA ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches)) return "Shareaza $matches[1]";
    if (substr($peer_id, 0, 3) == '-TN') return StdDecodePeerId(substr($peer_id, 3, 7), "Torrent.NET"); # Torrent.NET
    if (substr($peer_id, 0, 3) == '-TR') return StdDecodePeerId(substr($peer_id, 3, 7), "Transmission"); # Transmission
    if (substr($peer_id, 0, 3) == '-TS') return StdDecodePeerId(substr($peer_id, 3, 7), "TorrentStorm"); # Torrentstorm
    if (substr($peer_id, 0, 3) == '-UR') return StdDecodePeerId(substr($peer_id, 3, 7), "UR Client"); # unidentified clients with versions
    if (substr($peer_id, 0, 3) == '-UT') return StdDecodePeerId(substr($peer_id, 3, 7), "uTorrent"); # uTorrent
    if (substr($peer_id, 0, 3) == '-XT') return StdDecodePeerId(substr($peer_id, 3, 7), "XanTorrent"); # XanTorrent
    if (substr($peer_id, 0, 3) == '-ZT') return StdDecodePeerId(substr($peer_id, 3, 7), "ZipTorrent"); # ZipTorrent
    if (substr($peer_id, 0, 3) == '-bk') return StdDecodePeerId(substr($peer_id, 3, 7), "BitKitten"); # BitKitten
    if (substr($peer_id, 0, 3) == '-lt') return StdDecodePeerId(substr($peer_id, 3, 7), "libTorrent"); # libTorrent
    if (substr($peer_id, 0, 3) == '-pX') return StdDecodePeerId(substr($peer_id, 3, 7), "pHoeniX"); # pHoeniX
    if (substr($peer_id, 0, 2) == 'BG') return StdDecodePeerId(substr($peer_id, 2, 4), "BTGetit"); # BTGetit
    if (substr($peer_id, 2, 2) == 'BM') return DecodeVersionString(substr($peer_id, 0, 2), "BitMagnet"); # BitMagnet
    if (substr($peer_id, 0, 2) == 'OP') return StdDecodePeerId(substr($peer_id, 2, 4), "Opera"); # Opera
    if (substr($peer_id, 0, 4) == '270-') return "GreedBT 2.7.0"; # GreedBT
    if (substr($peer_id, 0, 4) == '271-') return "GreedBT 2.7.1"; # GreedBT 2.7.1
    if (substr($peer_id, 0, 4) == '346-') return "TorrentTopia"; # TorrentTopia
    if (substr($peer_id, 0, 3) == '-AR') return "Arctic Torrent"; # Arctic (no way to know the version)
    if (substr($peer_id, 0, 3) == '-G3') return "G3 Torrent"; # G3 Torrent
    if (substr($peer_id, 0, 6) == 'BTDWV-') return "Deadman Walking"; # Deadman Walking
    if (substr($peer_id, 5, 7) == 'Azureus') return "Azureus 2.0.3.2"; # Azureus 2.0.3.2
    if (substr($peer_id, 0, 8) == 'PRC.P---') return "BitTorrent Plus! II"; # BitTorrent Plus! II
    if (substr($peer_id, 0, 8) == 'S587Plus') return "BitTorrent Plus!"; # BitTorrent Plus!
    if (substr($peer_id, 0, 7) == 'martini') return "Martini Man"; # Martini Man
    if (substr($peer_id, 4, 6) == 'btfans') return "SimpleBT"; # SimpleBT
    if (substr($peer_id, 3, 9) == 'SimpleBT?') return "SimpleBT"; # SimpleBT
    if (ereg("MFC_Tear_Sample", $httpagent)) return "SimpleBT";
    if (substr($peer_id, 0, 5) == 'btuga') return "BTugaXP"; # BTugaXP
    if (substr($peer_id, 0, 5) == 'BTuga') return "BTuga"; # BTugaXP
    if (substr($peer_id, 0, 5) == 'oernu') return "BTugaXP"; # BTugaXP
    if (substr($peer_id, 0, 10) == 'DansClient') return "XanTorrent"; # XanTorrent
    if (substr($peer_id, 0, 16) == 'Deadman Walking-') return "Deadman"; # Deadman client
    if (substr($peer_id, 0, 8) == 'XTORR302') return "TorrenTres 0.0.2"; # TorrenTres
    if (substr($peer_id, 0, 7) == 'turbobt') return "TurboBT " . (substr($peer_id, 7, 5)); # TurboBT
    if (substr($peer_id, 0, 7) == 'a00---0') return "Swarmy"; # Swarmy
    if (substr($peer_id, 0, 7) == 'a02---0') return "Swarmy"; # Swarmy
    if (substr($peer_id, 0, 7) == 'T00---0') return "Teeweety"; # Teeweety
    if (substr($peer_id, 0, 7) == 'rubytor') return "Ruby Torrent v" . ord($peer_id[7]); # Ruby Torrent
    if (substr($peer_id, 0, 5) == 'Mbrst') return MainlineDecodePeerId(substr($peer_id, 5, 5), "burst!"); # burst!
    if (substr($peer_id, 0, 4) == 'btpd') return "BT Protocol Daemon " . (substr($peer_id, 5, 3)); # BT Protocol Daemon
    if (substr($peer_id, 0, 8) == 'XBT022--') return "BitTorrent Lite"; # BitTorrent Lite based on XBT code
    if (substr($peer_id, 0, 3) == 'XBT') return StdDecodePeerId(substr($peer_id, 3, 3), "XBT"); # XBT Client
    if (substr($peer_id, 0, 4) == '-BOW') return StdDecodePeerId(substr($peer_id, 4, 5), "Bits on Wheels"); # Bits on Wheels
    if (substr($peer_id, 1, 2) == 'ML') return MainlineDecodePeerId(substr($peer_id, 3, 5), "MLDonkey"); # MLDonkey
    if (substr($peer_id, 0, 8) == 'AZ2500BT') return "AzureusBitTyrant 1.0/1";
    if ($peer_id[0] == 'A') return StdDecodePeerId(substr($peer_id, 1, 9), "ABC"); # ABC
    if ($peer_id[0] == 'R') return StdDecodePeerId(substr($peer_id, 1, 5), "Tribler"); # Tribler
    if ($peer_id[0] == 'M') {
        if (preg_match("/^Python/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
        return MainlineDecodePeerId(substr($peer_id, 1, 7), "Mainline"); # Mainline BitTorrent with version
    }
    if ($peer_id[0] == 'O') return StdDecodePeerId(substr($peer_id, 1, 9), "Osprey Permaseed"); # Osprey Permaseed
    if ($peer_id[0] == 'S') {
        if (preg_match("/^BitTorrent\/3.4.2/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
        return StdDecodePeerId(substr($peer_id, 1, 9), "Shad0w"); # Shadow's client
    }
    if ($peer_id[0] == 'T') {
        if (preg_match("/^Python/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
        return StdDecodePeerId(substr($peer_id, 1, 9), "BitTornado"); # BitTornado
    }
    if ($peer_id[0] == 'U') return StdDecodePeerId(substr($peer_id, 1, 9), "UPnP"); # UPnP NAT Bit Torrent
    // Azureus / Localhost
    if (substr($peer_id, 0, 3) == '-AZ') {
        if (preg_match("/^Localhost ([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches)) return "Localhost $matches[1]";
        if (preg_match("/^BitTorrent\/3.4.2/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
        if (preg_match("/^Python/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
        return StdDecodePeerId(substr($peer_id, 3, 7), "Azureus");
    }
    if (ereg("Azureus", $peer_id)) return "Azureus 2.0.3.2";
    // BitComet/BitLord/BitVampire/Modded FUTB BitComet
    if (substr($peer_id, 0, 4) == 'exbc' || substr($peer_id, 1, 3) == 'UTB') {
        if (substr($peer_id, 0, 4) == 'FUTB') return DecodeVersionString(substr($peer_id, 4, 2), "BitComet Mod1");
        elseif (substr($peer_id, 0, 4) == 'xUTB') return DecodeVersionString(substr($peer_id, 4, 2), "BitComet Mod2");
        elseif (substr($peer_id, 6, 4) == 'LORD') return DecodeVersionString(substr($peer_id, 4, 2), "BitLord");
        elseif (substr($peer_id, 6, 3) == '---' && DecodeVersionString(substr($peer_id, 4, 2), "BitComet") == 'BitComet 0.54') return "BitVampire";
        else return DecodeVersionString(substr($peer_id, 4, 2), "BitComet");
    }
    // Rufus
    if (substr($peer_id, 2, 2) == 'RS') {
        for ($i = 0; $i <= strlen(substr($peer_id, 4, 9)); $i++) {
            $c = $peer_id[$i + 4];
            if (ctype_alnum($c) || $c == chr(0)) $rufus_chk = true;
            else break;
        }
        if ($rufus_chk) return DecodeVersionString(substr($peer_id, 0, 2), "Rufus"); # Rufus
    }
    // BitSpirit
    if (substr($peer_id, 14, 6) == 'HTTPBT' || substr($peer_id, 16, 4) == 'UDP0') {
        if (substr($peer_id, 2, 2) == 'BS') {
            if ($peer_id[1] == chr(0)) return "BitSpirit v1";
            if ($peer_id[1] == chr(2)) return "BitSpirit v2";
        }
        return "BitSpirit";
    }
    // BitSpirit
    if (substr($peer_id, 2, 2) == 'BS') {
        if ($peer_id[1] == chr(0)) return "BitSpirit v1";
        if ($peer_id[1] == chr(2)) return "BitSpirit v2";
        return "BitSpirit";
    }
    // eXeem beta
    if (substr($peer_id, 0, 3) == '-eX') {
        $version_str = "";
        $version_str .= intval($peer_id[3], 16) . ".";
        $version_str .= intval($peer_id[4], 16);
        return "eXeem $version_str";
    }
    if (substr($peer_id, 0, 2) == 'eX') return "eXeem"; # eXeem beta .21
    if (substr($peer_id, 0, 12) == (chr(0) * 12) && $peer_id[12] == chr(97) && $peer_id[13] == chr(97)) return "Experimental 3.2.1b2"; # Experimental 3.2.1b2
    if (substr($peer_id, 0, 12) == (chr(0) * 12) && $peer_id[12] == chr(0) && $peer_id[13] == chr(0)) return "Experimental 3.1"; # Experimental 3.1
    // if(substr($peer_id,0,12)==(chr(0)*12)) return "Mainline (obsolete)"; # Mainline BitTorrent (obsolete)
    // return "$httpagent [$peer_id]";
    return "Unknown client";
}
// ========================================
// getAgent function by deliopoulos
// ========================================

function dltable($name, $arr, $torrent)
{
    global $CURUSER;
    $s = "<b>" . count($arr) . " $name</b>\n";
    if (!count($arr))
        return $s;
    $s .= "\n";
    $s .= "<table width=100% class=main border=1 cellspacing=0 cellpadding=5>\n";
    $s .= "<tr><td class=colhead>User/IP</td>" . "<td class=colhead align=center>Connectable</td>" . "<td class=colhead align=right>Uploaded</td>" . "<td class=colhead align=right>Rate</td>" . "<td class=colhead align=right>Downloaded</td>" . "<td class=colhead align=right>Rate</td>" . "<td class=colhead align=right>Ratio</td>" . "<td class=colhead align=right>Complete</td>" . "<td class=colhead align=right>Connected</td>" . "<td class=colhead align=right>Idle</td>" . "<td class=colhead align=left>Client</td></tr>\n";
    $now = time();
    $moderator = (isset($CURUSER) && get_user_class() >= UC_MODERATOR);
    $mod = get_user_class() >= UC_MODERATOR;
    foreach ($arr as $e) {
        // user/ip/port
        // check if anyone has this ip
        ($unr = sql_query("SELECT id, username, privacy, warned, donor, anonymous FROM users WHERE id=$e[userid] ORDER BY last_access DESC LIMIT 1")) or die;
        $una = mysql_fetch_array($unr);
        if ($una["privacy"] == "strong") continue;
        ++$num;
        $highlight = $CURUSER["id"] == $una["id"] ? " bgcolor=#777777" : "";
        $s .= "<tr$highlight>\n";
        // $s .= "<tr>\n";
        if ($una["username"]) {
            if (get_user_class() < UC_MODERATOR && $una['anonymous'] == 'yes' && $e['userid'] != $CURUSER['id']) {
                $s .= "<td class=\"row1\"><i>Anonymous</i></td>\n";
            } else {
                if (get_user_class() >= UC_UPLOADER || $torrent['anonymous'] != 'yes' || $e['userid'] != $torrent['owner']) {
                    $s .= "<td class=\"row1\"><a href=userdetails.php?id=$e[userid]><b>$una[username]</b></a>" . ($una["donor"] == "yes" ? "<img src=" . "/pic/star.gif alt='Donor'>" : "") . ($una["enabled"] == "no" ? "<img src=" . "/pic/disabled.gif alt=\"This account is disabled\" style='margin-left: 2px'>" : ($una["warned"] == "yes" ? "<a href=rules.php#warning class=altlink><img src=/pic/warned.gif alt=\"Warned\" border=0></a>" : ""));
                } elseif (get_user_class() >= UC_UPLOADER || $torrent['anonymous'] = 'yes') {
                    $s .= "<td class=\"row1\"><i>Anonymous</i></a></td>\n";
                }
            }
        } else
            $s .= "<td>(unknown)</td>\n";
        $secs = max(1, ($now - $e["st"]) - ($now - $e["la"]));
        $revived = $e["revived"] == "yes";
        $s .= "<td align=center>" . ($e[connectable] == "yes" ? "Yes" : "<font color=red>No</font>") . "</td>\n";
        $s .= "<td align=right>" . prefixed($e["uploaded"]) . "</td>\n";
        $s .= "<td align=right><span style=white-space: nowrap;>" . prefixed(($e["uploaded"] - $e["uploadoffset"]) / $secs) . "/s</span></td>\n";
        $s .= "<td align=right>" . prefixed($e["downloaded"]) . "</td>\n";
        if ($e["seeder"] == "no")
            $s .= "<td align=right><div style=white-space:nowrap;>" . prefixed(($e["downloaded"] - $e["downloadoffset"]) / $secs) . "/s</div></td>\n";
        else
            $s .= "<td align=right><div style=white-space:nowrap;>" . prefixed(($e["downloaded"] - $e["downloadoffset"]) / max(1, $e["finishedat"] - $e[st])) . "/s</div></td>\n";
        if ($e["downloaded"]) {
            $ratio = floor(($e["uploaded"] / $e["downloaded"]) * 1000) / 1000;
            $s .= "<td align=\"right\"><font color=" . get_ratio_color($ratio) . ">" . number_format($ratio, 3) . "</font></td>\n";
        } else
        if ($e["uploaded"])
            $s .= "<td align=right>Inf.</td>\n";
        else
            $s .= "<td align=right>---</td>\n";
        $s .= "<td align=right>" . sprintf("%.2f%%", 100 * (1 - ($e["to_go"] / $torrent["size"]))) . "</td>\n";
        $s .= "<td align=right>" . mkprettytime($now - $e["st"]) . "</td>\n";
        $s .= "<td align=right>" . mkprettytime($now - $e["la"]) . "</td>\n";
        $s .= "<td align=left>" . safeChar(getagent($e["agent"], $e["peer_id"])) . ((get_user_class() >= UC_ADMINISTRATOR) ? "<a href='ban_client.php?agent=" . $e["agent"] . "&peer_id=" . bin2hex(substr($e["peer_id"], 0, 8)) . "&returnto=" . urlencode("details.php?id=" . intval($_GET["id"])) . "'><img src='pic/smilies/thumbsdown.gif' border='0' alt='Ban client?'></a>" : "") . "</td>\n";
        $s .= "</tr>\n";
    }
    $s .= "</table>\n";
    return $s;
}
?>
