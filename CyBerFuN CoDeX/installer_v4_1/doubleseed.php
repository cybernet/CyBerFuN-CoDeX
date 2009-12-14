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

if ($usergroups['candownload'] == 'no' OR $usergroups['candownload'] != 'yes') {
stdmsg( "Sorry...", "Your usergroup is not allowed to download" );
exit;
}

if ( $CURUSER["downloadpos"] != "no" && $CURUSER['freeslots'] >= '1' ) // comment if you don't use 'downloadpos'
     { // if ($CURUSER['freeslots']>='1') // uncomment if you don't use 'downloadpos'
        mysql_query( "UPDATE users SET freeslots = ($CURUSER[freeslots]-1) WHERE id = $CURUSER[id] && $CURUSER[freeslots]>=1" ) or sqlerr();

    if ( !preg_match( ':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches ) )
        httperr();

    $id = 0 + $matches[1];
    if ( !$id )
        httperr();

    $res = mysql_query( "SELECT name, vip FROM torrents WHERE id = $id" ) or sqlerr( __FILE__, __LINE__ );
    $row = mysql_fetch_assoc( $res );

    $fn = "$torrent_dir/$id.torrent";

    if ( !$row || !is_file( $fn ) || !is_readable( $fn ) )
        httperr();

    mysql_query( "UPDATE torrents SET hits = hits + 1 WHERE id = $id" );
    // /
    $added = sqlesc( get_date_time() );
    $userid = $CURUSER['id'];
    $resfs = mysql_query( "SELECT * FROM freeslots WHERE torrentid=$id && userid=$CURUSER[id]" );
    $arrfs = mysql_fetch_assoc( $resfs );
    $pq = $arrfs["torrentid"] == $id && $arrfs["userid"] == $CURUSER["id"];

    if ( $pq && $arrfs["doubleup"] == 'yes' ) {
        stderr( "Doh!", "Doubleseed slot already in use." );
        die();
    }
    mysql_query( "UPDATE users SET freeslots = ($CURUSER[freeslots]-1) WHERE id = $CURUSER[id] && $CURUSER[freeslots]>=1" ) or sqlerr();

    if ( $pq && $arrfs["free"] == 'yes' )
        mysql_query( "UPDATE freeslots SET doubleup='yes', addedup=$added WHERE torrentid=$id && userid=$CURUSER[id] && free='yes'" ) or sqlerr( __FILE__, __LINE__ );
    elseif ( $pq && $arrfs["free"] == 'no' )
        mysql_query( "INSERT INTO freeslots (torrentid, userid, doubleup, addedup) VALUES ($id,$userid,'yes',$added)" ) or sqlerr( __FILE__, __LINE__ );
    else
        mysql_query( "INSERT INTO freeslots (torrentid, userid, doubleup, addedup) VALUES ($id,$userid,'yes',$added)" ) or sqlerr( __FILE__, __LINE__ );
    // write_log("User id=<a href=$DEFAULTBASEURL/userdetails.php?id=$userid >$userid</a> [$CURUSER[username]] has used a doubleseed slot on torrent id=<a href=$DEFAULTBASEURL/details.php?id=$id>$id</a>.");
    // /
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

    $dict['value']['created by'] = bdec( benc_str( "" . $CURUSER['username'] . "" ) );

    header( 'Content-Disposition: attachment; filename="' . $torrent['filename'] . '"' );

    if ( strstr( $HTTP_USER_AGENT, "MSIE" ) ) {
        $attachment = " ";
    } else {
        $attachment = " attachment;";
    }
    $saveasname = "extract.xls";
    header( 'Content-Type: application/vnd.ms-excel' );
    header( 'Content-Disposition: $attachment filename="' . $saveasname . '"' );
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    header( "Content-Type: application/x-bittorrent" );
    print( benc( $dict ) );
} else {
    // die;
    // if the below doesnt work comment it all out minus the ending } and uncomment the //die;
    stdhead();
    print( "<tr><td class=rowhead width=1%>Download</td><td width=99% align=left>Sorry, but you have no doubleseed slots.<img src='/pic/smilies/sad.gif' border='0' alt=':('></td></tr>" );

    stdfoot();
}

?>