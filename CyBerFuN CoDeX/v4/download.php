<?php
require_once( "include/bittorrent.php" );
require_once ( "include/user_functions.php" );
require_once ( "include/bbcode_functions.php" );
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();

/**
* **************************** uplaodpos people only ************************************
*/
///////////////
$row = 'false';
///////////////
if ( $CURUSER["id"] == $row["owner"] ) $CURUSER["downloadpos"] = "yes";
if ( $CURUSER["downloadpos"] != "no" ) {
    /**
    * **************************** end uplaodpos people only *********************************
    */
    // if (!preg_match(':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["ORIG_PATH_INFO"], $matches))
    if ( !preg_match( ':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches ) )
        httperr();

    $id = 0 + $matches[1];
    if ( !$id )
        httperr();

    $res = mysql_query( "SELECT name, filename, category, vip, minclass FROM torrents WHERE id = $id" ) or sqlerr( __FILE__, __LINE__ );
    $row = mysql_fetch_assoc( $res );
		if($row["minclass"] != 255 && $row["minclass"] > $CURUSER["class"])
			die("This is not for you");
    if ( happyHour( "check" ) && happyCheck( "checkid", $row["category"] ) ) {
        $multiplier = happyHour( "multiplier" );
        $time = time();
        happyLog( $CURUSER["id"], $id, $multiplier );
        mysql_query( "INSERT INTO happyhour (userid, torrentid, multiplier ) VALUES (" . sqlesc( $CURUSER["id"] ) . " , " . sqlesc( $id ) . ", " . sqlesc( $multiplier ) . ")" );
    }
		
    $fn = "$torrent_dir/$id.torrent";

    if ( !$row || !is_file( $fn ) || !is_readable( $fn ) )
        httperr();

    mysql_query( "UPDATE torrents SET hits = hits + 1 WHERE id = $id" );

    require_once "include/benc.php";

    if ( $row["vip"] == 'yes' && get_user_class() < UC_VIP ) {
        stdmsg( "Sorry...", "You are not allowed to download this torrent" );
        exit;
    }

    if ( strlen( $CURUSER['passkey'] ) != 32 ) {
        $CURUSER['passkey'] = md5( $CURUSER['username'] . get_date_time() . $CURUSER['passhash'] );

        mysql_query( "UPDATE users SET passkey='$CURUSER[passkey]' WHERE id=$CURUSER[id]" );
    }

    $dict = bdec_file( $fn, ( 1024 * 1024 ) );

    $dict['value']['announce']['value'] = "$BASEURL/announce.php?passkey=$CURUSER[passkey]";

    $dict['value']['announce']['string'] = strlen( $dict['value']['announce']['value'] ) . ":" . $dict['value']['announce']['value'];

    $dict['value']['announce']['strlen'] = strlen( $dict['value']['announce']['string'] );
    // ////////kill the createdby////////
    $dict['value']['created by'] = bdec( benc_str( "" . $CURUSER['username'] . "" ) );
    // ////////replace it with curuser//////////////////
    // header('Content-Disposition: attachment; filename="'.$row['filename'].'"');
    header( "Content-Disposition: attachment; filename=\"[$SITENAME] " . $row["name"] . ".torrent\"" );
    header( "Content-Type: application/x-bittorrent" );

    print( benc( $dict ) );
    /**
    * ******************************** uploadpos people only ***************************************
    */
} else {
die;
//print("<tr><td class=rowhead width=1%>Download</td><td width=99% align=left><a class=\"index\" href=\"#" . "\"><b>Download Disabled</b></a></td></tr>");
//tr("Download", "You are not allowed to download");
}
/********************************* end uploadpos people only *************************************/
?>