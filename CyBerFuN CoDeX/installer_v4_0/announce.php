<?

// CyBerFuN.Ro
// By CyBerNe7
//            //
// http://cyberfun.ro/
// http://xlist.ro/

define( 'IN_ANNOUNCE', true );
require_once ( "include/bittorrent.php" );
require_once ( "include/benc.php" );

// Bug fixed - tracker send invalid data
// cybernet2u
// http://xList.ro/
// http://cyberfun.ro/
// http://tracker.cyberfun.ro/
if (!function_exists('getallheaders'))
{
    function getallheaders()
    {
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       return $headers;
    }
}

//=== bad stuff let's just kill this right off
$headers = getallheaders();
if (isset($headers['Cookie']) || isset($headers['Accept-Language']) || isset($headers['Accept-Charset']))
exit('It takes 46 muscles to frown but only 4 to flip \'em the bird.');

function err( $msg )
{
    benc_resp( array( "failure reason" => array( type => "string", value => $msg ) ) );
    exit();
}

function benc_resp( $d )
{
    benc_resp_raw( benc( array( type => "dictionary", value => $d ) ) );
}

function benc_resp_raw( $x )
{
    header( "Content-Type: text/plain" );
    header( "Pragma: no-cache" );

    if ( $_SERVER['HTTP_ACCEPT_ENCODING'] == 'gzip' ) {
        header( "Content-Encoding: gzip" );
        echo gzencode( $x, 9, FORCE_GZIP );
    } else
        echo $x;
}

foreach ( array( "passkey", "info_hash", "peer_id", "ip", "event" ) as $x )
$GLOBALS[$x] = "" . $_GET[$x];
foreach ( array( "port", "downloaded", "uploaded", "left" ) as $x )
$GLOBALS[$x] = 0 + $_GET[$x];
if ( strpos( $passkey, "?" ) ) {
    $tmp = substr( $passkey, strpos( $passkey, "?" ) );
    $passkey = substr( $passkey, 0, strpos( $passkey, "?" ) );
    $tmpname = substr( $tmp, 1, strpos( $tmp, "=" ) - 1 );
    $tmpvalue = substr( $tmp, strpos( $tmp, "=" ) + 1 );
    $GLOBALS[$tmpname] = $tmpvalue;
}

foreach ( array( "passkey", "info_hash", "peer_id", "port", "downloaded",
        "uploaded", "left" ) as $x )
if ( !isset( $x ) )
    err( "Missing key: $x" );
foreach ( array( "info_hash", "peer_id" ) as $x )
if ( strlen( $GLOBALS[$x] ) != 20 )
    err( "Invalid $x (" . strlen( $GLOBALS[$x] ) . " - " . urlencode( $GLOBALS[$x] ) . ")" );
if ( strlen( $passkey ) != 32 )
    err( "Invalid passkey (" . strlen( $passkey ) . " - $passkey)" );
$ip = getip();
$rsize = 50;
foreach ( array( "num want", "numwant", "num_want" ) as $k ) {
    if ( isset( $_GET[$k] ) ) {
        $rsize = 0 + $_GET[$k];
        break;
    }
}

if ( !$port || $port > 0xffff )
    err( "invalid port" );

if ( !isset( $event ) )
    $event = "";

$seeder = ( $left == 0 ) ? "yes" : "no";
// Banned Clients - By Petr1fied
$filename = "include/banned_clients.txt";
if ( filesize( $filename ) == 0 || !file_exists( $filename ) )
    $banned_clients = array();
else {
    $handle = fopen( $filename, "r" );
    $banned_clients = unserialize( fread( $handle, filesize( $filename ) ) );
    fclose( $handle );
}

foreach ( $banned_clients as $c ) {
    if ( substr( bin2hex( $peer_id ), 0, 16 ) == $c["peer_id"] || substr( bin2hex( $peer_id ),
                0, 6 ) == $c["peer_id"] )
        err( $c["user_agent"] . " is banned. Reason : " . $c["reason"] );
}

dbconn( false );

$res = mysql_query( "SELECT id, added, banned, vip, half, multiplicator, seeders + leechers AS numpeers, UNIX_TIMESTAMP(added) AS ts, countstats, hidden, staffonly FROM torrents WHERE " . hash_where( "info_hash", $info_hash ) );

$torrent = mysql_fetch_assoc( $res );
if ( !$torrent )
    err( "torrent not registered with this tracker" );

$torrentid = $torrent["id"];
$hidden = $torrent["hidden"];
$staffonly = $torrent["staffonly"];
$fields = "seeder, peer_id, ip, port, uploaded, downloaded, userid, last_action, (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(last_action)) AS announcetime, UNIX_TIMESTAMP(last_action) AS ts, UNIX_TIMESTAMP(NOW()) AS nowts, UNIX_TIMESTAMP(prev_action) AS prevts";

$numpeers = $torrent["numpeers"];
$limit = "";
if ( $numpeers > $rsize )
    $limit = "ORDER BY RAND() LIMIT $rsize";
// If user is a seeder, then only supply leechers.
// This helps with the zero upload cheat, as it doesn't supply anyone who has
// a full copy.
$wantseeds = "";
if ( $seeder == 'yes' )
    $wantseeds = "AND seeder = 'no'";

$res = mysql_query( "SELECT $fields FROM peers WHERE torrent = $torrentid AND connectable = 'yes' $wantseeds $limit" ) or
err( 'peers query failure' );
// //////////////////Compact mode begin/////////////////////////////
function dht_client_recog()
{
    $pid = $GLOBALS['peer_id'];
    if ( substr( $pid, 0, 3 ) == "-AZ" )
        return true; // Azureus
    elseif ( substr( $pid, 0, 4 ) == "-BC0" )
        return true; // BitComet
    elseif ( substr( $pid, 2, 2 ) == "BS" )
        return true; // BitSpirit
    elseif ( substr( $pid, 0, 3 ) == "-UT" )
        return true; // uTorrent
    else
        return false;
}

if ( $_GET['compact'] != 1 ) {
    $resp = "d" . benc_str( "interval" ) . "i" . $announce_interval . "e" . ( dht_client_recog
        () ? ( benc_str( "private" ) . 'i1e' ) : '' ) . benc_str( 'peers' ) . "l";
} else {
    $resp = "d" . benc_str( "interval" ) . "i" . $announce_interval . "e5:" . "peers";
}
$peer = array();
$peer_num = 0;
while ( $row = mysql_fetch_assoc( $res ) ) {
    if ( $_GET['compact'] != 1 ) {
        $row["peer_id"] = hash_pad( $row["peer_id"] );
        if ( $row["peer_id"] === $peer_id ) {
            $self = $row;
            continue;
        }
        $resp .= "d" . benc_str( "ip" ) . benc_str( $row["ip"] );
        if ( !$_GET['no_peer_id'] ) {
            $resp .= benc_str( "peer id" ) . benc_str( $row["peer_id"] );
        }
        $resp .= benc_str( "port" ) . "i" . $row["port"] . "e" . "e";
    } else {
        $peer_ip = explode( '.', $row["ip"] );
        $peer_ip = pack( "C*", $peer_ip[0], $peer_ip[1], $peer_ip[2], $peer_ip[3] );
        $peer_port = pack( "n*", ( int )$row["port"] );
        $time = intval( ( time() % 7680 ) / 60 );
        if ( $_GET['left'] == 0 ) {
            $time += 128;
        }
        $time = pack( "C", $time );
        $peer[] = $time . $peer_ip . $peer_port;
        $peer_num++;
    }
}
if ( $_GET['compact'] != 1 )
    $resp .= "ee";
else {
    $o = "";
    for ( $i = 0; $i < $peer_num; $i++ ) {
        $o .= substr( $peer[$i], 1, 6 );
    }
    $resp .= strlen( $o ) . ':' . $o . 'e';
}
$selfwhere = "torrent = $torrentid AND " . hash_where( "peer_id", $peer_id );
// /////////////////////////// End compact mode////////////////////////////////
if ( !isset( $self ) ) {
    $res = mysql_query( "SELECT $fields FROM peers WHERE $selfwhere" );
    $row = mysql_fetch_assoc( $res );
    if ( $row ) {
        $userid = $row["userid"];
        $self = $row;
    }
}
// === stop banned IPs
$nip = ip2long( $ip );
$res = mysql_query( "SELECT id FROM bans WHERE $nip >= first AND $nip <= last" );
if ( mysql_num_rows( $res ) > 0 )
    err( "Yer Banned - Stop Hammering ffs" );
// ///////////up/down stats/////////////
// // Up/down stats ////////////////////////////////////////////////////////////
// Anti Flood Code
// This code is designed to ensure that no more than two announces can occur
// within a 10 second period. This is to ensure that Flooding doesn't happen
$announce_wait = 10;
if ( isset( $self ) && ( $self['prevts'] > ( $self['nowts'] - $announce_wait ) ) ) {
    err( 'There is a minimum announce time of ' . $announce_wait . ' seconds' );
}
if ( !isset( $self ) ) {
    $valid = @mysql_fetch_row( @mysql_query( "SELECT COUNT(*) FROM peers WHERE torrent=$torrentid AND passkey=" . sqlesc( $passkey ) ) );
    if ( $valid[0] >= 2 && $seeder == 'no' )
        err( "Connection limit exceeded! You may only leech from one location at a time." );
    if ( $valid[0] >= 3 && $seeder == 'yes' )
        err( "Connection limit exceeded!" );
    $rz = mysql_query( "SELECT id, tlimitseeds, tlimitleeches, tlimitall, uploaded, downloaded, class, hiddentorrents, enabled FROM users WHERE passkey=" . sqlesc( $passkey ) . "" ) or err( "Tracker error 2" );
    $user_q = mysql_query("SELECT id, uploaded, downloaded, class, hiddentorrents, parked, enabled FROM users WHERE passkey = " . sqlesc($passkey) . "") or err("User SQL error. Contact staff.");
    if (mysql_num_rows($user_q) > 1) err("Please reset your passkey and re-download all torrent files.. Passkey collision.");
    $user = mysql_fetch_assoc($user_q);
    // ///////////////////////////////////////////////////////// Stop and register users who cheat w. passkey
    $pklength = strlen( $passkey );
    if ( mysql_num_rows( $rz ) < 1 || $pklength != 32 ) { // If there was no user found with that passkey, or if passkey is not 32 chars long
        if ( $pklength != 32 )
            $reason = "Passkey incorrect lenght ($pklength).";
        else
        if ( mysql_num_rows( $rz ) < 1 )
            $reason = "No user found.";
        // Insert into passkeyerr table.
        $err = mysql_query( "INSERT INTO passkeyerr (added, lastchange, ip, firstpasskey, lastpasskey, firstreason, lastreason, times) VALUES('" . get_date_time() . "', '" . get_date_time() . "', '$ip', " . sqlesc( $passkey ) . ", " . sqlesc( $passkey ) . ", '$reason', '$reason', '1')" );
        // If the ip is already regged there, update the post instead, and add + 1 to offences.
        if ( !$err ) {
            if ( mysql_errno() == 1062 )
                mysql_query( "UPDATE passkeyerr SET lastchange = '" . get_date_time() . "', lastpasskey = " . sqlesc( $passkey ) . ", lastreason = '$reason', times = times + 1 WHERE ip = '$ip'" ) or
                err( "AC3 - 2 Error. Contact staff." );
            else
                err( "AC3 Error. Contact staff." );
        }
        // Send an error message to users client, with reason and warning.
        err( "Invalid passkey. $reason You might now be IP-banned for misbehaving." );
    }
    else if ($user["enabled"] != "yes")
    err("Your account has been banned! Delete this torrent from your client. Stop hammering!");
    if ( $MEMBERSONLY && mysql_num_rows( $rz ) == 0 )
        err( "Unknown passkey. Please redownload the torrent !" );
    $az = mysql_fetch_assoc( $rz );
    $userid = $az["id"];
    $hiddentorrents = $az["hiddentorrents"];
    //=== hidden torrent
    if ($hidden == "yes"){
    if($hiddentorrents == "no" && $az["class"] < UC_MODERATOR){
    err("Torrent not recognized with Tracker!");
    }        
    }
    //===staff only torrent
    if ($staffonly == "yes"){
    if($az["class"] < UC_MODERATOR){
    err("Torrent not recognized with Tracker!");
    }        
    }
    $webseeder = ( ( isset( $az["webseeder"] ) && $az["webseeder"] == 'yes' && $seeder == 'yes' ) ? "yes" : "no" );
    // ///// Torrent-Limit
    if ( $az["tlimitall"] >= 0 ) {
        $arr = mysql_fetch_assoc( mysql_query( "SELECT COUNT(*) AS cnt FROM peers WHERE userid=$userid" ) );
        $numtorrents = $arr["cnt"];
        $arr = mysql_fetch_assoc( mysql_query( "SELECT COUNT(*) AS cnt FROM peers WHERE userid=$userid AND seeder='yes'" ) );
        $seeds = $arr["cnt"];
        $leeches = $numtorrents - $seeds;
        $limit = get_torrent_limits( $az );

        if ( ( $limit["total"] > 0 ) && ( ( $numtorrents >= $limit["total"] ) || ( $left == 0 && $seeds >= $limit["seeds"] ) || ( $left > 0 && $leeches >= $limit["leeches"] ) ) )
            err( "Maximum Torrent-Limit reached ($limit[seeds] Seeds, $limit[leeches] Leeches, $limit[total] total)" );
    }
    if ( $az["vip"] == "yes" && get_user_class() < UC_VIP )
        err( "VIP Access Required, You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you more Vip-Only Torrents!" );
    if ( ( $waiton ) && ( $left > 0 && $user['class'] < UC_VIP ) ) {
        $gigs = $az["uploaded"] / ( 1024 * 1024 * 1024 );
        $elapsed = floor( ( gmtime() - $torrent["ts"] ) / 3600 );
        $ratio = ( ( $az["downloaded"] > 0 ) ? ( $az["uploaded"] / $az["downloaded"] ) : 1 );
        if ( $ratio < 0.5 || $gigs < 5 )
            $wait = $wait1;
        elseif ( $ratio < 0.65 || $gigs < 6.5 )
            $wait = $wait2;
        elseif ( $ratio < 0.8 || $gigs < 8 )
            $wait = $wait3;
        elseif ( $ratio < 0.95 || $gigs < 9.5 )
            $wait = $wait4;
        else
            $wait = 0;
        if ( $elapsed < $wait )
            err( "Not authorized (" . ( $wait - $elapsed ) . "h) - READ THE FAQ!" );
    }
} else {
    // Get the last uploaded amount from user account for reference and store it in $last_up
    $rst = mysql_query( "SELECT class, username, highspeed, uploaded, webseeder FROM users WHERE id = $userid " ) or
    err( "Tracker error 5" );
    $art = mysql_fetch_assoc( $rst );
    $last_up = $art["uploaded"];
    $class = $art["class"];
    $webseeder = ( ( isset( $art["webseeder"] ) && $art["webseeder"] == 'yes' && $seeder == 'yes' ) ? "yes" : "no" );
    $highspeed = $art["highspeed"];
    $upthis = max( 0, $uploaded - $self["uploaded"] );
    $downthis = max( 0, $downloaded - $self["downloaded"] );
    $upspeed = ( $upthis > 0 ? $upthis / $self["announcetime"] : 0 );
    $downspeed = ( $downthis > 0 ? $downthis / $self["announcetime"] : 0 );
    $announcetime = ( $self["seeder"] == "yes" ? "seedtime = seedtime + $self[announcetime]" : "leechtime = leechtime + $self[announcetime]" );
    $resfs = mysql_query("SELECT * FROM freeslots WHERE torrentid=$torrentid && userid=$userid");
    $arrfs = mysql_fetch_assoc($resfs);
    $pq = $arrfs["torrentid"] == $torrentid && $arrfs["userid"] == $userid;
    // ///do the math////
    $multiplicator = $torrent['multiplicator'];
    if ( $multiplicator == "2" )
        $upthis = $upthis * 2;
    elseif ( $multiplicator == "3" )
        $upthis = $upthis * 3;
    elseif ( $multiplicator == "4" )
        $upthis = $upthis * 4;
    elseif ( $multiplicator == "5" )
        $upthis = $upthis * 5;
    ///////////////////
    $happy = mysql_query( "SELECT id, multiplier from happyhour where userid=" . sqlesc( $userid ) . " AND torrentid=" . sqlesc( $torrentid ) . " " );
    $happyhour = mysql_num_rows( $happy ) == 0 ? false : true;
    $happy_multi = mysql_fetch_row( $happy );
    $multiplier = $happy_multi["multiplier"];
    if ( $happyhour ) {
        $upthis = $upthis * $multiplier;
        $downthis = 0;
    }
    /////////////////////
    if ( $torrent['half'] == 'yes' )
    {
    $downthis = $downthis / 2;
    }
    //==freeleech/doubleupload system by ezero - recoded block by putyn
    $q = mysql_query("SELECT * FROM events ORDER BY startTime DESC LIMIT 1") or print (mysql_error());
	  $a = mysql_fetch_assoc($q);
	  if($a["startTime"] < time() && $a["endTime"] >time())
	  {
		if($a['freeleechEnabled'] == 1)
			$downthis = 0;
		if($a['duploadEnabled'] == 1){
			$upthis *=2;
			$downthis = 0;
		}
	  }
    ///////////////////////////////////////////
        if ( $upthis > 0 || $downthis > 0 )
        {
        if (!($free_for_all || $torrent["countstats"]=='no' || ($pq && $arrfs["free"] == 'yes'))) // is it a non free torrent
        $updq[0] = "downloaded = downloaded + $downthis";
        $updq[1] = "uploaded = uploaded + " . (($arrfs['doubleup'] == 'yes' || $double_for_all) ? ($upthis * 2) : $upthis);
        $udq = implode(',', $updq);
        mysql_query("UPDATE users SET $udq WHERE id=$userid") or err("Tracker error 3");
        }
        // ///// Initial sanity check xMB/s for 1 second
        if ( $upthis > 2097152 ) {
            // Work out time difference
            $endtime = time();
            $starttime = $self['ts'];
            $diff = ( $endtime - $starttime );
            // Normalise to prevent divide by zero.
            $rate = ( $upthis / ( $diff + 1 ) );
            // Currently 2MB/s. Increase to 5MB/s once finished testing.
            if ( $rate > 2097152 ) {
                if ( $class < UC_CODER and $highspeed == "no" ) {
                    $rate = prefixed( $rate );
                    $client = $agent;
                    $userip = getip();
                    auto_enter_cheater( $userid, $rate, $upthis, $diff, $torrentid, $client, $userip,
                        $last_up );
                    $modcomment = gmdate( "Y-m-d" ) . " Warned and download disabled for possible ratio cheating at high upload speeds\n" . $arr['modcomment'];
                    mysql_query( "UPDATE users set warned='yes', downloadpos='no', modcomment = " .
                        sqlesc( $modcomment ) . " WHERE id=$userid" ) or sqlerr( __file__, __line__ );
                    $body = sqlesc( "Cheat alert : [url=$BASEURL/userdetails.php?id=" . $userid . "]View Members profile[/url] \n This member has been flagged with a high upload speed\n Automatic warning and download disablement applied - Please verify the members actual upload speeds.\n " );
                    $subject = sqlesc( "High Upload Speed Detected" );
                    auto_post( $subject, $body );
                }
            }
        }
    }
    // //////////////end abnormal upload speed detection ////
    function portblacklisted( $port )
    {
        // direct connect
        if ( $port >= 411 && $port <= 413 )
            return true;
        // bittorrent
        if ( $port >= 6881 && $port <= 6889 )
            return true;
        // kazaa
        if ( $port == 1214 )
            return true;
        // gnutella
        if ( $port >= 6346 && $port <= 6347 )
            return true;
        // emule
        if ( $port == 4662 )
            return true;
        // winmx
        if ( $port == 6699 )
            return true;

        return false;
    }

    if ( portblacklisted( $port ) )
        err( "Port $port is blacklisted." );
    else {
        $sockres = @fsockopen( $ip, $port, $errno, $errstr, 5 );
        if ( !$sockres )
            $connectable = "no";
        else {
            $connectable = "yes";
            @fclose( $sockres );
        }
    }

    $updateset = array();

    if ( isset( $self ) && $event == "stopped" ) {
        mysql_query( "DELETE FROM peers WHERE $selfwhere" ) or err( "D Err" );
//=== hit and run system by sir_snugglebunny   
$res_snatch = mysql_query("SELECT seedtime, uploaded, downloaded, finished, UNIX_TIMESTAMP(start_date) AS start_snatch FROM snatched WHERE torrentid = $torrentid AND userid = $userid") or err('Snatch Error 1');
$a = mysql_fetch_array($res_snatch);
//=== only run the function if the ratio is below 1
if( ($a['uploaded'] + $upthis) < ($a['downloaded'] + $downthis) && $a['finished'] == 'yes')
{
$HnR_time_seeded = ($a['seedtime'] + $self['announcetime']);

//=== get times per class
switch (true)
{
//===  member
case ($arr_class['class'] < UC_USER):
$days_3 = 3*86400; //== 3 days
$days_14 = 2*86400; //== 2 days
$days_over_14 = 86400; //== 1 day
break;
//=== Member +
case ($arr_class['class'] == UC_POWER_USER):
$days_3 = 2*86400; //== 2 days
$days_14 = 129600; //== 36 hours
$days_over_14 = 64800; //== 18 hours
break;
//=== Member ++  
case ($arr_class['class'] == UC_VIP || $arr_class['class'] == UC_UPLOADER || $arr_class['class'] == UC_MODERATOR):
$days_3 = 129600; //== 36 hours
$days_14 = 86400; //== 24 hours
$days_over_14 = 43200; //== 12 hours
break;
//=== Member x 
case ($arr_class['class'] == UC_ADMINSTRATOR || $arr_class['class'] >= UC_SYSOP):
$days_3 = 86400; //== 24 hours
$days_14 = 43200; //== 12 hours
$days_over_14 = 21600; //== 6 hours
break;
}

switch(true)
{
case (($a['start_snatch'] - $torrent['ts']) < 7*86400):
$minus_ratio = ($days_3 - $HnR_time_seeded);
// or using ratio
//$minus_ratio = ($days_3 - $HnR_time_seeded) - ($arr_snatch['uploaded'] / $arr_snatch['downloaded'] * 3 * 86400);
break;
case (($a['start_snatch'] - $torrent['ts']) < 21*86400):
$minus_ratio = ($days_14 - $HnR_time_seeded);
// or using ratio
//$minus_ratio = ($days_14 - $HnR_time_seeded) - ($arr_snatch['uploaded'] / $arr_snatch['downloaded'] * 2 * 86400);
break;
case (($a['start_snatch'] - $torrent['ts']) >= 21*86400):
$minus_ratio = ($days_over_14 - $HnR_time_seeded);
// or using ratio
//$minus_ratio = ($days_over_14 - $HnR_time_seeded) - ($arr_snatch['uploaded'] / $arr_snatch['downloaded'] * 86400);
break;
}

$hit_and_run = (($minus_ratio > 0 && ($a['uploaded'] + $upthis) < ($a['downloaded'] + $downthis)) ? ', hit_and_run= '.sqlesc(get_date_time()) : ', hit_and_run = \'0000-00-00 00:00:00\'');
} //=== end if not 1:1 ratio
else
$hit_and_run = ', hit_and_run = \'0000-00-00 00:00:00\'';
//=== end hit and run

//=== new snatched position    
mysql_query("UPDATE snatched SET ip = ".sqlesc($ip).", port = $port, connectable = '$connectable', uploaded = uploaded + $upthis, downloaded = downloaded + $downthis, to_go = $left, upspeed = $upspeed, downspeed = $downspeed, $announcetime, last_action = '".get_date_time()."', seeder = 'no', agent = ".sqlesc($agent)." $hit_and_run WHERE torrentid = $torrentid AND userid = $userid") or err('SL Err 1');
    if (mysql_affected_rows())
    {
    $updateset[] = ($self['seeder'] == 'yes' ? 'seeders = seeders - 1' : 'leechers = leechers - 1');
    }
    } elseif ( isset( $self ) ) {
        $prev_action = sqlesc( $self['last_action'] );
        if ( $event == "completed" ) {
            $updateset[] = "times_completed = times_completed + 1";
            $finished = ", finishedat = UNIX_TIMESTAMP()";
            $finished1 = ", complete_date = '".get_date_time()."', finished = 'yes'";
        }

        mysql_query( "UPDATE peers SET ip = " . sqlesc( $ip ) . ", port = $port, connectable = '$connectable', webseeder='$webseeder', uploaded = $uploaded, downloaded = $downloaded, to_go = $left, last_action = NOW(), prev_action = $prev_action, seeder = '$seeder', agent = " .
            sqlesc( $agent ) . " $finished WHERE $selfwhere" ) or err( "PL Err 1" );
        if ( mysql_affected_rows() ) {
            if ( $seeder <> $self["seeder"] )
                $updateset[] = ( $seeder == "yes" ?
                    "seeders = seeders + 1, leechers = leechers - 1" :
                    "seeders = seeders - 1, leechers = leechers + 1" );
            $anntime = "timesann = timesann + 1";
            mysql_query( "UPDATE snatched SET ip = " . sqlesc( $ip ) . ", port = $port, connectable = '$connectable', uploaded = uploaded + $upthis, downloaded = downloaded + $downthis, to_go = $left, upspeed = $upspeed, downspeed = $downspeed, $announcetime, last_action = '" .
                get_date_time() . "', seeder = '$seeder', agent = " . sqlesc( $agent ) . " $finished1, $anntime WHERE torrentid = $torrentid AND userid = $userid" ) or
            err( "SL Err 2" );
        }
    } else {
        if ( $az["parked"] == "yes" )
            err( "Your account is parked! (Read the FAQ)" );
        elseif ( $az["downloadpos"] == "no" )
            err( "Your downloading priviledges have been disabled! (Read the rules)" );
        mysql_query( "INSERT INTO peers (torrent, userid, peer_id, ip, port, webseeder, connectable, uploaded, downloaded, to_go, started, last_action, seeder, agent, downloadoffset, uploadoffset, passkey) VALUES ($torrentid, $userid, " .
            sqlesc( $peer_id ) . ", " . sqlesc( $ip ) . ", $port,'$webseeder', '$connectable', $uploaded, $downloaded, $left, NOW(), NOW(), '$seeder', " .
            sqlesc( $agent ) . ", $downloaded, $uploaded, " . sqlesc( unesc( $passkey ) ) . ")" ) or
        err( "PL Err 2" );
        if ( mysql_affected_rows() ) {
            $updateset[] = ( $seeder == "yes" ? "seeders = seeders + 1" :
                "leechers = leechers + 1" );
            $anntime = "timesann = timesann + 1";
            mysql_query( "UPDATE snatched SET ip = " . sqlesc( $ip ) . ", port = $port, connectable = '$connectable', to_go = $left, last_action = '" .
                get_date_time() . "', seeder = '$seeder', agent = " . sqlesc( $agent ) . ", $anntime, hit_and_run = '0000-00-00 00:00:00', mark_of_cain = 'no' WHERE torrentid = $torrentid AND userid = $userid" ) or
            err( "SL Err 3" );
            if ( !mysql_affected_rows() && $seeder == "no" )
                mysql_query( "INSERT INTO snatched (torrentid, userid, peer_id, ip, port, connectable, uploaded, downloaded, to_go, start_date, last_action, seeder, agent) VALUES ($torrentid, $userid, " .
                    sqlesc( $peer_id ) . ", " . sqlesc( $ip ) . ", $port, '$connectable', $uploaded, $downloaded, $left, '" .
                    get_date_time() . "', '" . get_date_time() . "', '$seeder', " . sqlesc( $agent ) . ")" ) or err( "SL Err 4" );
        }
    }

    if ( $seeder == "yes" ) {
        if ( $torrent["banned"] != "yes" )
            $updateset[] = "visible = 'yes'";
        $updateset[] = "last_action = NOW()";
    }

    if ( count( $updateset ) )
        mysql_query( "UPDATE torrents SET " . join( ",", $updateset ) . " WHERE id = $torrentid" );
    benc_resp_raw( $resp );

    ?>
