<?php
require_once( "include/bittorrent.php" );
require_once( "include/phpzip.php" );
require_once ( "include/user_functions.php" );
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();

if ($usergroups['candownload'] == 'no' OR $usergroups['candownload'] != 'yes') {
stdmsg( "Sorry...", "Your usergroup is not allowed to download" );
exit;
}

/**
* **************************** uplaodpos people only ************************************
*/
if ( $CURUSER["id"] == $row["owner"] ) $CURUSER["downloadpos"] = "yes";
if ( $CURUSER["downloadpos"] != "no" ) {
    /**
    * **************************** end uplaodpos people only *********************************
    */
    if ( !preg_match( ':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches ) )
        httperr();

    $id = 0 + $matches[1];
    if ( !$id )
        httperr();

    $res = mysql_query( "SELECT name, filename, vip FROM torrents WHERE id = $id" ) or sqlerr( __FILE__, __LINE__ );
    $row = mysql_fetch_assoc( $res );

    $fn = "$torrent_dir/$id.torrent";

    if ( !$row || !is_file( $fn ) || !is_readable( $fn ) )
        httperr();

    if ( strlen( $CURUSER['passkey'] ) != 32 ) {
        $CURUSER['passkey'] = md5( $CURUSER['username'] . get_date_time() . $CURUSER['passhash'] );

        mysql_query( "UPDATE users SET passkey='$CURUSER[passkey]' WHERE id=$CURUSER[id]" );
    }

    mysql_query( "UPDATE torrents SET hits = hits + 1 WHERE id = $id" );

    if ( happyHour( "check" ) && happyCheck( "checkid", $row["category"] ) ) {
        $multiplier = happyHour( "multiplier" );
        $time = time();
        happyLog( $CURUSER["id"], $id, $multiplier );
        mysql_query( "INSERT INTO happyhour (userid, torrentid, multiplier ) VALUES (" . sqlesc( $CURUSER["id"] ) . " , " . sqlesc( $id ) . ", " . sqlesc( $multiplier ) . " )" ) or sqlerr( __FILE__, __LINE__ );
    }
    // Passkey Mod
    require_once "include/benc.php";

    if ( $row["vip"] == 'yes' && get_user_class() < UC_VIP ) {
        stdmsg( "Sorry...", "You are not allowed to download this torrent" );
        exit;
    }

    $dict = bdec_file( $fn, ( 1024 * 1024 ) );

    $dict['value']['announce']['value'] = "$BASEURL/announce.php?passkey=$CURUSER[passkey]";

    $dict['value']['announce']['string'] = strlen( $dict['value']['announce']['value'] ) . ":" . $dict['value']['announce']['value'];

    $dict['value']['announce']['strlen'] = strlen( $dict['value']['announce']['string'] );
    // download as zip file by putyn tbdev
    $name = str_replace( array( " ", ".", "-" ) , "_" , $row["name"] );
    $new = benc( $dict );
    $f = $torrent_dir . '/' . $name . '.torrent';
    $newFile = fopen( $f , "w" );
    fwrite ( $newFile, $new );
    fclose( $newFile );

    $file = array();
    $zip = new PHPZip();
    $file[] = "$f" ;
    $fName = "$torrent_dir/$name.zip" ;
    $zip->Zip( $file, $fName );
    $zip->forceDownload( $fName );
    unlink( $torrent_dir . '/' . $name . '.torrent' );
    unlink( $torrent_dir . '/' . $name . '.zip' );
    /**
    * ********************************** uploadpos people only ***************************************
    */
} else {
    die;
    // print("<tr><td class=rowhead width=1
    // Download</td><td width=99% align=left><a class=\"index\" href=\"#" . "\"><b>Download Disabled</b></a></td></tr>");
    // tr("Download", "You are not allowed to download");
}
/**
* ******************************** end uploadpos people only ************************************
*/
?>
