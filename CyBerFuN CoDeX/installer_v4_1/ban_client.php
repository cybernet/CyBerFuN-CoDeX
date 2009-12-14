<?php
// *****************************************************************
// Version: 1.1
// *****************************************************************
//
// Filename: ban_client.php
// Parent:   details.php
// Requires: bittorrent.php, banned_clients.php
// Author:   Petr1fied
// Date:     2007-06-17
// Updated:  2007-07-01
//
// Usage:
// - Bans specific BitTorrent Clients, both individual versions and
//   all versions of a client can be banned.
//
// ####### HISTORY ################################################
//
// 1.0 2007-06-17 - Petr1fied - Intital development.
// 1.1 2007-07-01 - Petr1fied - Ported to TBDev and changed to be
//                              SQL free.
//
// *****************************************************************
require_once ("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn(false);
maxcoder();	
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
stdhead('Ban BitTorrent Client');

if (get_user_class() < UC_ADMINISTRATOR)
{
    stdmsg(ERROR,"You're not authorised to view this page");
    stdfoot();
    exit;
}
else
{
    (isset($_GET["agent"]) ? $agent=urldecode($_GET["agent"]) : $agent="");
    (isset($_GET["peer_id"]) ? $peer_id=urldecode($_GET["peer_id"]) : $peer_id="");
    (isset($_GET["returnto"]) ? $url=urldecode($_GET["returnto"]) : $url="index.php");
    (isset($_POST["confirm"]) ? $confirm=$_POST["confirm"] : $confirm="");
    (isset($_POST["reason"]) ? $reason=$_POST["reason"] : $reason="");
    (isset($_POST["banall"]) ? $banall="yes" : $banall="no");
    $peer_id_ascii=hex2bin($peer_id);
    $client=getagent($agent, $peer_id);
    
    $filename = "include/banned_clients.txt";
    if (filesize($filename)==0 || !file_exists($filename))
        $banned_clients=array();
    else
    {
        $handle = fopen($filename, "r");
        $banned_clients = unserialize(fread($handle, filesize($filename)));
        fclose($handle);
    }

    if($_POST["confirm"])
    {
        if($confirm=="Yes" && $reason!="")
        {
            $banned=0;

            foreach($banned_clients as $k => $v)
            {
                if(substr($peer_id, 0, (($banall=="yes") ? 6 : 16 )) == $v["peer_id"])
                $banned=1;
            }

            if($banned==1)
            {
                stdmsg(ERROR,"This client is already banned");
                stdfoot();
                exit;
            }
            
          
            if(empty($banned_clients))
            {
                if($banall=="yes")
                {
                    $client=substr($client, 0, stripos($client, " "))." (All versions)";
                    $banned_clients[1]=array('peer_id' => substr($peer_id, 0, 6), 'peer_id_ascii' => substr($peer_id_ascii, 0, 3), 'user_agent' => 'N/A', 'client_name' => $client, 'reason' => $reason);
                }
                else
                    $banned_clients[1]=array('peer_id' => substr($peer_id, 0, 16), 'peer_id_ascii' => substr($peer_id_ascii, 0, 8), 'user_agent' => $agent, 'client_name' => $client, 'reason' => $reason);
            }
            else
            {
                if($banall=="yes")
                {
                    $client=substr($client, 0, stripos($client, " "))." (All versions)";
                    $banned_clients[]=array('peer_id' => substr($peer_id, 0, 6), 'peer_id_ascii' => substr($peer_id_ascii, 0, 3), 'user_agent' => 'N/A', 'client_name' => $client, 'reason' => $reason);
                }
                else
                    $banned_clients[]=array('peer_id' => substr($peer_id, 0, 16), 'peer_id_ascii' => substr($peer_id_ascii, 0, 8), 'user_agent' => $agent, 'client_name' => $client, 'reason' => $reason);                
            }
            $data=serialize($banned_clients);
            
            $fd = fopen($filename, "w") or die("Can't update $filename, please CHMOD it to 777");
            fwrite($fd,$data) or die("Can't save file");
            fclose($fd);
            
            stdmsg("Success","This client has been added to the banned list");
            print("<center><a href='$url'>Return</a></center>");
            stdfoot();
            exit();
        }
        elseif($confirm=="No")
        {
            redirect($url);
        }
        else
        {
            stdmsg(ERROR,"You must enter a reason!");
            stdfoot();
            exit();
        }
    }
    ?>
    <p align='center'>By visiting this page you are indicating that
    you want to ban the following client:</p>
    <form method='post' name='action'>
    <table align='center' width=70%>
      <tr>
        <td class='header' align='center'><strong>Client</strong></td>
        <td class='header' align='center'><strong>User Agent</strong></td>
        <td class='header' align='center'><strong>peer_id</strong></td>
        <td class='header' align='center'><strong>peer_id ascii</strong></td>
      </tr>
      <tr>
        <td class='lista' align='center'><?=$client?></td>
        <td class='lista' align='center'><?=$agent?></td>
        <td class='lista' align='center'><?=$peer_id?></td>
        <td class='lista' align='center'><?=$peer_id_ascii?></td>
      </tr>
      <tr>
        <td class='lista' align='right'><strong>Reason</strong></td>
        <td class='lista' colspan='3'><input type='text' name='reason' value='' size='70' maxlength='255'>
        &nbsp;&nbsp;&nbsp;<strong>Ban all versions?</strong><input type='checkbox' name='banall'></td>
      </tr>
      <tr>
        <td class='block' colspan='4'>&nbsp</td>
      <tr>
    </table>
    <p align='center'>Are you sure you want to do this? (you will receive no further confirmation).</p>
    <center>
    <input type='submit' name='confirm' value='Yes'>&nbsp;<input type='submit' name='confirm' value='No'>
    <center></form><br />
    <?php
}
stdfoot();

function hex2bin ($input, $assume_safe=true)
{
    if ($assume_safe !== true && ! ((strlen($input) % 2) === 0 || preg_match ('/^[0-9a-f]+$/i', $input)))
        return "";
    return pack('H*', $input );
}

#========================================
#getAgent function by deliopoulos
#========================================
function StdDecodePeerId($id_data, $id_name){
  $version_str = "";
  for ($i=0; $i<=strlen($id_data); $i++){
    $c = $id_data[$i];
    if ($id_name=="BitTornado" || $id_name=="ABC") {
      if ($c!='-' && ctype_digit($c)) $version_str .= "$c.";
      elseif ($c!='-' && ctype_alpha($c)) $version_str .= (ord($c)-55).".";
      else break;
    }
    elseif($id_name=="BitComet"||$id_name=="BitBuddy"||$id_name=="Lphant"||$id_name=="BitPump"||$id_name=="BitTorrent Plus! v2") {
      if ($c != '-' && ctype_alnum($c)){
        $version_str .= "$c";
        if($i==0) $version_str = intval($version_str) .".";
      }
      else{
        $version_str .= ".";
        break;
      }
    }
    else {
      if ($c != '-' && ctype_alnum($c)) $version_str .= "$c.";
      else break;
    }
  }
  $version_str = substr($version_str,0,strlen($version_str)-1);
  return "$id_name $version_str";
}
function MainlineDecodePeerId($id_data, $id_name){
  $version_str = "";
  for ($i=0; $i<=strlen($id_data); $i++){
    $c = $id_data[$i];
    if ($c != '-' && ctype_alnum($c)) $version_str .= "$c.";
  }
  $version_str = substr($version_str,0,strlen($version_str)-1);
    return "$id_name $version_str";
}
function DecodeVersionString ($ver_data, $id_name){
    $version_str = "";
    $version_str .= intval(ord($ver_data[0]) + 0).".";
    $version_str .= intval(ord($ver_data[1])/10 + 0);
    $version_str .= intval(ord($ver_data[1])%10 + 0);
    return "$id_name $version_str";
}
function getagent($httpagent, $peer_id="") {
  if($peer_id!="") $peer_id=hex2bin($peer_id);
  if(substr($peer_id,0,3)=='-AX') return StdDecodePeerId(substr($peer_id,4,4),"BitPump"); # AnalogX BitPump
  if(substr($peer_id,0,3)=='-BB') return StdDecodePeerId(substr($peer_id,3,5),"BitBuddy"); # BitBuddy
  if(substr($peer_id,0,3)=='-BC') return StdDecodePeerId(substr($peer_id,4,4),"BitComet"); # BitComet
  if(substr($peer_id,0,3)=='-BS') return StdDecodePeerId(substr($peer_id,3,7),"BTSlave"); # BTSlave
  if(substr($peer_id,0,3)=='-BX') return StdDecodePeerId(substr($peer_id,3,7),"BittorrentX"); # BittorrentX
  if(substr($peer_id,0,3)=='-CT') return "Ctorrent $peer_id[3].$peer_id[4].$peer_id[6]"; # CTorrent
  if(substr($peer_id,0,3)=='-KT') return StdDecodePeerId(substr($peer_id,3,7),"KTorrent"); # KTorrent
  if(substr($peer_id,0,3)=='-LT') return StdDecodePeerId(substr($peer_id,3,7),"libtorrent"); # libtorrent
  if(substr($peer_id,0,3)=='-LP') return StdDecodePeerId(substr($peer_id,4,4),"Lphant"); # Lphant
  if(substr($peer_id,0,3)=='-MP') return StdDecodePeerId(substr($peer_id,3,7),"MooPolice"); # MooPolice
  if(substr($peer_id,0,3)=='-MT') return StdDecodePeerId(substr($peer_id,3,7),"Moonlight"); # MoonlightTorrent
  if(substr($peer_id,0,3)=='-PO') return StdDecodePeerId(substr($peer_id,3,7),"PO Client"); #unidentified clients with versions
  if(substr($peer_id,0,3)=='-QT') return StdDecodePeerId(substr($peer_id,3,7),"Qt 4 Torrent"); # Qt 4 Torrent
  if(substr($peer_id,0,3)=='-RT') return StdDecodePeerId(substr($peer_id,3,7),"Retriever"); # Retriever
  if(substr($peer_id,0,3)=='-S2') return StdDecodePeerId(substr($peer_id,3,7),"S2 Client"); #unidentified clients with versions
  if(substr($peer_id,0,3)=='-SB') return StdDecodePeerId(substr($peer_id,3,7),"Swiftbit"); # Swiftbit
  if(substr($peer_id,0,3)=='-SN') return StdDecodePeerId(substr($peer_id,3,7),"ShareNet"); # ShareNet
  if(substr($peer_id,0,3)=='-SS') return StdDecodePeerId(substr($peer_id,3,7),"SwarmScope"); # SwarmScope
  if(substr($peer_id,0,3)=='-SZ') return StdDecodePeerId(substr($peer_id,3,7),"Shareaza"); # Shareaza
  if(preg_match("/^RAZA ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches)) return "Shareaza $matches[1]";
  if(substr($peer_id,0,3)=='-TN') return StdDecodePeerId(substr($peer_id,3,7),"Torrent.NET"); # Torrent.NET
  if(substr($peer_id,0,3)=='-TR') return StdDecodePeerId(substr($peer_id,3,7),"Transmission"); # Transmission
  if(substr($peer_id,0,3)=='-TS') return StdDecodePeerId(substr($peer_id,3,7),"TorrentStorm"); # Torrentstorm
  if(substr($peer_id,0,3)=='-UR') return StdDecodePeerId(substr($peer_id,3,7),"UR Client"); # unidentified clients with versions
  if(substr($peer_id,0,3)=='-UT') return StdDecodePeerId(substr($peer_id,3,7),"uTorrent"); # uTorrent
  if(substr($peer_id,0,3)=='-XT') return StdDecodePeerId(substr($peer_id,3,7),"XanTorrent"); # XanTorrent
  if(substr($peer_id,0,3)=='-ZT') return StdDecodePeerId(substr($peer_id,3,7),"ZipTorrent"); # ZipTorrent
  if(substr($peer_id,0,3)=='-bk') return StdDecodePeerId(substr($peer_id,3,7),"BitKitten"); # BitKitten
  if(substr($peer_id,0,3)=='-lt') return StdDecodePeerId(substr($peer_id,3,7),"libTorrent"); # libTorrent
  if(substr($peer_id,0,3)=='-pX') return StdDecodePeerId(substr($peer_id,3,7),"pHoeniX"); # pHoeniX
  if(substr($peer_id,0,2)=='BG') return StdDecodePeerId(substr($peer_id,2,4),"BTGetit"); # BTGetit
  if(substr($peer_id,2,2)=='BM') return DecodeVersionString(substr($peer_id,0,2),"BitMagnet"); # BitMagnet
  if(substr($peer_id,0,2)=='OP') return StdDecodePeerId(substr($peer_id,2,4),"Opera"); # Opera
  if(substr($peer_id,0,4)=='270-') return "GreedBT 2.7.0"; # GreedBT
  if(substr($peer_id,0,4)=='271-') return "GreedBT 2.7.1"; # GreedBT 2.7.1
  if(substr($peer_id,0,4)=='346-') return "TorrentTopia"; # TorrentTopia
  if(substr($peer_id,0,3)=='-AR') return "Arctic Torrent"; # Arctic (no way to know the version)
  if(substr($peer_id,0,3)=='-G3') return "G3 Torrent"; # G3 Torrent
  if(substr($peer_id,0,6)=='BTDWV-') return "Deadman Walking"; # Deadman Walking
  if(substr($peer_id,5,7)=='Azureus') return "Azureus 2.0.3.2"; # Azureus 2.0.3.2
  if(substr($peer_id,0,8)=='PRC.P---') return "BitTorrent Plus! II"; # BitTorrent Plus! II
  if(substr($peer_id,0,8)=='P87.P---') return "BitTorrent Plus!"; # BitTorrent Plus!
  if(substr($peer_id,0,4)=='Plus') return StdDecodePeerId(substr($peer_id,4,5),"BitTorrent Plus! v2"); # BitTorrent Plus! v2 (not 100% sure on this one)
  if(substr($peer_id,0,8)=='S587Plus') return "BitTorrent Plus!"; # BitTorrent Plus!
  if(substr($peer_id,0,7)=='martini') return "Martini Man"; # Martini Man
  if(substr($peer_id,4,6)=='btfans') return "SimpleBT"; # SimpleBT
  if(substr($peer_id,3,9)=='SimpleBT?') return "SimpleBT"; # SimpleBT
  if(ereg("MFC_Tear_Sample", $httpagent)) return "SimpleBT";
  if(substr($peer_id,0,5)=='btuga') return "BTugaXP"; # BTugaXP
  if(substr($peer_id,0,5)=='BTuga') return "BTuga"; # BTugaXP
  if(substr($peer_id,0,5)=='oernu') return "BTugaXP"; # BTugaXP
  if(substr($peer_id,0,10)=='DansClient') return "XanTorrent"; # XanTorrent
  if(substr($peer_id,0,16)=='Deadman Walking-') return "Deadman"; # Deadman client
  if(substr($peer_id,0,8)=='XTORR302') return "TorrenTres 0.0.2"; # TorrenTres
  if(substr($peer_id,0,7)=='turbobt') return "TurboBT ".(substr($peer_id,7,5)); # TurboBT
  if(substr($peer_id,0,7)=='a00---0') return "Swarmy"; # Swarmy
  if(substr($peer_id,0,7)=='a02---0') return "Swarmy"; # Swarmy
  if(substr($peer_id,0,7)=='T00---0') return "Teeweety"; # Teeweety
  if(substr($peer_id,0,7)=='rubytor') return "Ruby Torrent v".ord($peer_id[7]); # Ruby Torrent
  if(substr($peer_id,0,5)=='Mbrst') return MainlineDecodePeerId(substr($peer_id,5,5),"burst!"); # burst!
  if(substr($peer_id,0,4)=='btpd') return "BT Protocol Daemon ".(substr($peer_id,5,3)); # BT Protocol Daemon
  if(substr($peer_id,0,8)=='XBT022--') return "BitTorrent Lite"; # BitTorrent Lite based on XBT code
  if(substr($peer_id,0,3)=='XBT') return StdDecodePeerId(substr($peer_id,3,3), "XBT"); # XBT Client
  if(substr($peer_id,0,4)=='-BOW') return StdDecodePeerId(substr($peer_id,4,5),"Bits on Wheels"); # Bits on Wheels
  if(substr($peer_id,1,2)=='ML') return MainlineDecodePeerId(substr($peer_id,3,5),"MLDonkey"); # MLDonkey
  if(substr($peer_id,0,8)=='AZ2500BT') return "AzureusBitTyrant 1.0/1";
  if($peer_id[0]=='A') return StdDecodePeerId(substr($peer_id,1,9),"ABC"); # ABC
  if($peer_id[0]=='R') return StdDecodePeerId(substr($peer_id,1,5),"Tribler"); # Tribler
  if($peer_id[0]=='M'){
    if(preg_match("/^Python/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
    return MainlineDecodePeerId(substr($peer_id,1,7),"Mainline"); # Mainline BitTorrent with version
  }
  if($peer_id[0]=='O') return StdDecodePeerId(substr($peer_id,1,9),"Osprey Permaseed"); # Osprey Permaseed
  if($peer_id[0]=='S'){
    if(preg_match("/^BitTorrent\/3.4.2/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
    return StdDecodePeerId(substr($peer_id,1,9),"Shad0w"); # Shadow's client
  }
  if($peer_id[0]=='T'){
    if(preg_match("/^Python/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
    return StdDecodePeerId(substr($peer_id,1,9),"BitTornado"); # BitTornado
  }
  if($peer_id[0]=='U') return StdDecodePeerId(substr($peer_id,1,9),"UPnP"); # UPnP NAT Bit Torrent
  # Azureus / Localhost
  if(substr($peer_id,0,3)=='-AZ') {
    if(preg_match("/^Localhost ([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches)) return "Localhost $matches[1]";
    if(preg_match("/^BitTorrent\/3.4.2/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
    if(preg_match("/^Python/", $httpagent, $matches)) return "Spoofing BT Client"; # Spoofing BT Client
    return StdDecodePeerId(substr($peer_id,3,7),"Azureus");
  }
  if(ereg("Azureus", $peer_id)) return "Azureus 2.0.3.2";
  # BitComet/BitLord/BitVampire/Modded FUTB BitComet
  if(substr($peer_id,0,4)=='exbc' || substr($peer_id,1,3)=='UTB'){
    if(substr($peer_id,0,4)=='FUTB') return DecodeVersionString(substr($peer_id,4,2),"BitComet Mod1");
    elseif(substr($peer_id,0,4)=='xUTB') return DecodeVersionString(substr($peer_id,4,2),"BitComet Mod2");
    elseif(substr($peer_id,6,4)=='LORD') return DecodeVersionString(substr($peer_id,4,2),"BitLord");
    elseif(substr($peer_id,6,3)=='---' && DecodeVersionString(substr($peer_id,4,2),"BitComet")=='BitComet 0.54') return "BitVampire";
    else return DecodeVersionString(substr($peer_id,4,2),"BitComet");
  }
  # Rufus
  if(substr($peer_id,2,2)=='RS'){
    for ($i=0; $i<=strlen(substr($peer_id,4,9)); $i++){
      $c = $peer_id[$i+4];
      if (ctype_alnum($c) || $c == chr(0)) $rufus_chk = true;
      else break;
    }
    if ($rufus_chk) return DecodeVersionString(substr($peer_id,0,2),"Rufus"); # Rufus
  }
  # BitSpirit
  if(substr($peer_id,14,6)=='HTTPBT' || substr($peer_id,16,4)=='UDP0') {
    if(substr($peer_id,2,2)=='BS') {
      if($peer_id[1]==chr(0)) return "BitSpirit v1";
      if($peer_id[1]== chr(2)) return "BitSpirit v2";
    }
        return "BitSpirit";
  }
  #BitSpirit
  if(substr($peer_id,2,2)=='BS') {
    if($peer_id[1]==chr(0)) return "BitSpirit v1";
    if($peer_id[1]==chr(2)) return "BitSpirit v2";
    return "BitSpirit";
  }
  # eXeem beta
  if(substr($peer_id,0,3)=='-eX') {
    $version_str = "";
    $version_str .= intval($peer_id[3],16).".";
    $version_str .= intval($peer_id[4],16);
    return "eXeem $version_str";
  }
  if(substr($peer_id,0,2)=='eX') return "eXeem"; # eXeem beta .21
  if(substr($peer_id,0,12)==(chr(0)*12) && $peer_id[12]==chr(97) && $peer_id[13]==chr(97)) return "Experimental 3.2.1b2"; # Experimental 3.2.1b2
  if(substr($peer_id,0,12)==(chr(0)*12) && $peer_id[12]==chr(0) && $peer_id[13]==chr(0)) return "Experimental 3.1"; # Experimental 3.1
  //if(substr($peer_id,0,12)==(chr(0)*12)) return "Mainline (obsolete)"; # Mainline BitTorrent (obsolete)
  //return "$httpagent [$peer_id]";
  return "Unknown client";
}
#========================================
#getAgent function by deliopoulos
#========================================


?>