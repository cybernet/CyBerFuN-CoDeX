<?php
require_once( "include/bittorrent.php" );
require_once "include/user_functions.php";
require_once ( "include/bbcode_functions.php" );
dbconn( false );
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
// === added turn on / off shoutbox
if ( ( isset( $_GET['show_shout'] ) ) && ( ( $show_shout = $_GET['show'] ) !== $CURUSER['show_shout'] ) ) {
    sql_query( "UPDATE users SET show_shout = " . sqlesc( $_GET['show'] ) . " WHERE id = $CURUSER[id]" );
    header( "Location: " . $_SERVER['HTTP_REFERER'] );
}
unset( $insert );
$insert = false;
$query = '';
// DELETE SHOUT
if ( isset( $_GET['del'] ) && get_user_class() >= UC_MODERATOR && is_valid_id( $_GET['del'] ) )
    mysql_query( "DELETE FROM shoutbox WHERE id=" . sqlesc( $_GET['del'] ) );
// Empty shout - coder/owner
if ( isset( $_GET['delall'] ) && get_user_class() >= UC_SYSOP )
    $query = "TRUNCATE TABLE shoutbox";
mysql_query( $query );
// Edit shout
if ( isset( $_GET['edit'] ) && get_user_class() >= UC_MODERATOR && is_valid_id( $_GET['edit'] ) ) {
    $sql = sql_query( "SELECT id,text FROM shoutbox WHERE id=" . sqlesc( $_GET['edit'] ) );
    $res = mysql_fetch_array( $sql );

    ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $language['charset'];?>" />
	<style type="text/css">
	#specialbox{
	border: 1px solid gray;
	width: 600px;
	background: #FBFCFA;
	font: 11px verdana, sans-serif;
	color: #000000;
	padding: 3px;	outline: none;
	}

	#specialbox:focus{
	border: 1px solid black;
	}
	.btn {
	cursor:pointer;
	border:outset 1px #ccc;
	background:#999;
	color:#666;
	font-weight:bold;
	padding: 1px 2px;
	background: #000000 repeat-x left top;
	}
	</style>
	</head>
	<body bgcolor=#F5F4EA class="date">
	<?php
    echo '<form method=post action=shoutbox.php>';
    echo '<input type=hidden name=id value=' . ( int )$res['id'] . '>';
    echo '<textarea name=text rows=3 id=specialbox>' . safechar( $res['text'] ) . '</textarea>';
    echo '<input type=submit name=save value=save class=btn>';
    echo '</form></body></html>';
    die;
}
// UPDATE SHOUT?
if ( isset( $_POST['text'] ) && get_user_class() >= UC_MODERATOR && is_valid_id( $_POST['id'] ) ) {
    $text = trim( $_POST['text'] );
    $id = ( int )$_POST['id'];
    if ( isset( $text ) && isset( $id ) && is_valid_id( $id ) )
        sql_query( "UPDATE shoutbox SET text = " . sqlesc( $text ) . " WHERE id=" . sqlesc( $id ) );
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $language['charset'];?>" />
<title>ShoutBox</title>
<meta http-equiv="REFRESH" content="60; URL=shoutbox.php" />
<script type="text/javascript" >
function confirm_delete()
{
   if(confirm('Are you sure you want to do this ?'))
   {
     if(confirm('Are you 100% sure ?'))
	 {
		alert("Your are sure!");
		self.location.href='<?php $DEFAULTBASEURL ?>/shoutbox.php?delall';
	 }
   }
}
</script>
<style type="text/css">
A {color: #356AA0; font-weight: bold; font-size: 9pt; }
A:hover {color: #FF0000;}
.small {color: #ff0000; font-size: 9pt; font-family: arial; }
.date {color: #ff0000; font-size: 9pt;}
.error {
 color: #990000;
 background-color: #FFF0F0;
 padding: 7px;
 margin-top: 5px;
 margin-bottom: 10px;
 border: 1px dashed #990000;
}
A {color: #FFFFFF; font-weight: bold; }
A:hover {color: #FFFFFF;}
.small {font-size: 10pt; font-family: arial; }
.date {font-size: 8pt;}
</style>


<?php
// default Theme
if ( $CURUSER['shoutboxbg'] == "1" ) {

    ?>
<style type="text/css">
A {color: #000000; font-weight: bold; }
A:hover {color: #FF273D;}
.small {font-size: 10pt; font-family: arial; }
.date {font-size: 8pt;}
</style>
<?php
    $bg = 'bgcolor=#ffffff';
    $fontcolor = '#000000';
    $dtcolor = '#356AA0';
}
// large text Theme
if ( $CURUSER['shoutboxbg'] == "2" ) {

    ?>
<style type="text/css">
A {color: #ffffff; font-weight: bold; }
A:hover {color: #FF273D;}
.small {font-size: 10pt; font-family: arial; }
.date {font-size: 8pt;}
</style>
<?php
    $bg = 'bgcolor=#777777';
    $fontcolor = '#000000';
    $dtcolor = '#FFFFFF';
}
// Klima Theme
if ( $CURUSER['shoutboxbg'] == "3" ) {

    ?>
<style type="text/css">
A {color: #FFFFFF; font-weight: bold; }
A:hover {color: #FFFFFF;}
.small {font-size: 10pt; font-family: arial; }
.date {font-size: 8pt;}
</style>
<?php
    $bg = 'bgcolor=#000000';
    $fontcolor = '#FFFFFF';
    $dtcolor = '#FFFFFF';
}

?>
</head>
<body bgcolor=#F5F4EA>
<?php
if ($CURUSER['chatpost'] == 'no' OR $usergroups['canshout'] == 'no' OR $usergroups['canshout'] != 'yes')
{
print( "<div class=error align=center><br>Sorry, you are not authorized to Shout.  (<a href=\"rules.php\" target=\"_blank\"><font color=black>Contact Site Admin For Reason Why</font></a>)<br><br></div>" );
exit;
}

if ( isset( $_GET['sent'] ) && ( $_GET['sent'] == "yes" ) ) {
    $limit = 20;
    $userid = $CURUSER["id"];
    $date = sqlesc( time() );
    $text = trim( $_GET["shbox_text"] );
    $text_parsed = format_comment($text);
	
	if(stristr($text,"/system") && $CURUSER["class"] >= UC_MODERATOR)
	{
		$userid = 2;
		$text = str_replace(array("/SYSTEM","/system"),"",$text);
		$text_parsed = format_comment($text);
	
	}
    // ///////////////////////shoutbox command system by putyn & pdq /////////////////////////////
    $commands = array( "\/EMPTY", "\/GAG", "\/UNGAG", "\/WARN", "\/UNWARN", "\/DISABLE", "\/ENABLE" ); // this / was replaced with \/ to work with the regex
    $pattern = "/(" . implode( "|", $commands ) . "\w+)\s([a-zA-Z0-9_\s(?i)]+)/";
	
    if ( preg_match( $pattern, $text, $vars ) && $CURUSER["class"] >= UC_MODERATOR ) {
        $command = $vars[1];
        $user = $vars[2];

        $c = sql_query( "SELECT id, class, modcomment FROM users where username=" . sqlesc( $user ) ) or sqlerr();
        $a = mysql_fetch_row( $c );

        if ( mysql_num_rows( $c ) == 1 && $CURUSER["class"] > $a[1] ) {
            switch ( $command ) {
                case "/EMPTY" :
                    $what = 'deleted all shouts';
                    $msg = "[b]" . $user . "'s[/b] shouts have been deleted";
                    $query = "DELETE FROM shoutbox where userid = " . $a[0];
                    break;
                case "/GAG" :
                    $what = 'gagged';
                    $modcomment = gmdate( "Y-m-d" ) . " - [ShoutBox] User has been gagged by " . $CURUSER["username"] . "\n" . $a["modcomment"];
                    $msg = "[b]" . $user . "[/b] - has been gagged by " . $CURUSER["username"];
                    $query = "UPDATE users SET chatpost='no', modcomment = concat(" . sqlesc( $modcomment ) . ", modcomment) WHERE id = " . $a[0];
                    break;
                case "/UNGAG" :
                    $what = 'ungagged';
                    $modcomment = gmdate( "Y-m-d" ) . " - [ShoutBox] User has been ungagged by " . $CURUSER["username"] . "\n" . $a[2];
                    $msg = "[b]" . $user . "[/b] - has been ungagged by " . $CURUSER["username"];
                    $query = "UPDATE users SET chatpost='yes', modcomment = concat(" . sqlesc( $modcomment ) . ", modcomment) WHERE id = " . $a[0];
                    break;
                case "/WARN" :
                    $what = 'warned';
                    $modcomment = gmdate( "Y-m-d" ) . " - [ShoutBox] User has been warned by " . $CURUSER["username"] . "\n" . $a[2];
                    $msg = "[b]" . $user . "[/b] - has been warned by " . $CURUSER["username"];
                    $query = "UPDATE users SET warned='yes', modcomment = concat(" . sqlesc( $modcomment ) . ", modcomment) WHERE id = " . $a[0];
                    break;
                case "/UNWARN" :
                    $what = 'unwarned';
                    $modcomment = gmdate( "Y-m-d" ) . " - [ShoutBox] User has been unwarned by " . $CURUSER["username"] . "\n" . $a[2];
                    $msg = "[b]" . $user . "[/b] - has been unwarned by " . $CURUSER["username"];
                    $query = "UPDATE users SET warned='no', modcomment = concat(" . sqlesc( $modcomment ) . ", modcomment) WHERE id = " . $a[0];
                    break;
                case "/DISABLE" :
                    $what = 'disabled';
                    $modcomment = gmdate( "Y-m-d" ) . " - [ShoutBox] User has been disabled by " . $CURUSER["username"] . "\n" . $a[2];
                    $msg = "[b]" . $user . "[/b] - has been disabled by " . $CURUSER["username"];
                    $query = "UPDATE users SET enabled='no', modcomment = concat(" . sqlesc( $modcomment ) . ", modcomment) WHERE id = " . $a[0];
                    break;
                case "/ENABLE" :
                    $what = 'enabled';
                    $modcomment = gmdate( "Y-m-d" ) . " - [ShoutBox] User has been enabled by " . $CURUSER["username"] . "\n" . $a[2];
                    $msg = "[b]" . $user . "[/b] - has been enabled by " . $CURUSER["username"];
                    $query = "UPDATE users SET enabled='yes', modcomment = concat(" . sqlesc( $modcomment ) . ", modcomment) WHERE id = " . $a[0];
                    break;
            }
            if ( sql_query( $query ) )
                autoshout( $msg );
            print "<script type=\"text/javascript\">parent.document.forms[0].shbox_text.value='';</script>";
            write_log("shoutcom", "Shoutbox user " . $user . " has been " . $what . " by " . $CURUSER["username"] );
        }
    }
	else {
        $a = mysql_fetch_row( mysql_query( "SELECT userid,date FROM shoutbox ORDER by id DESC LIMIT 1 " ) ) or print( "bad thing in query" );
        if ( empty( $text ) || strlen( $text ) == 1 )
            print( "<font class=\"small\" color=\"red\">Shout can't be empty</font>" );
        elseif ( $a[0] == $userid && ( time() - $a[1] ) < $limit && get_user_class() < UC_MODERATOR )
            print( "<font class=\"small\" color=\"red\">$limit seconds between shouts <font class=\"small\">Seconds Remaining : (" . ( $limit - ( time() - $a[1] ) ) . ")</font></font>" );
        else {
            sql_query( "INSERT INTO shoutbox (id, userid, date, text, text_parsed) VALUES ('id'," . sqlesc( $userid ) . ", $date, " . sqlesc( $text ) . ",".sqlesc( $text_parsed ) .")" ) or sqlerr( __FILE__, __LINE__ );
            print "<script type=\"text/javascript\">parent.document.forms[0].shbox_text.value='';</script>";
        }
    }
}
// //////////////////////
$res = sql_query( "SELECT s.id, s.userid, s.date , s.text,u.username,u.class,u.donor,u.warned,u.downloadpos,u.chatpost,u.forumpost,u.uploadpos,u.parked  FROM shoutbox as s LEFT JOIN users as u ON s.userid=u.id ORDER BY s.date DESC LIMIT 30" ) or sqlerr( __FILE__, __LINE__ );
if ( mysql_num_rows( $res ) == 0 )
    print( "No shouts here " );
else {
    print( "<table border=0 cellspacing=0 cellpadding=2 width='100%' align='left' class='small'>\n" );
    while ( $arr = mysql_fetch_assoc( $res ) ) {
        $edit = ( get_user_class() >= UC_MODERATOR ? "<a href=/shoutbox.php?edit=" . $arr['id'] . "><img src=" . $pic_base_url . "button_edit2.gif border=0 title=\"Edit Shout\" /></a> " : "" );
        $del = ( get_user_class() >= UC_MODERATOR ? "<a href=/shoutbox.php?del=" . $arr['id'] . "><img src=" . $pic_base_url . "button_delete2.gif border=0 title=\"Delete Single Shout\" /></a> " : "" );
        $delall = ( get_user_class() >= UC_SYSOP ? "<a href=/shoutbox.php?delall onclick=\"confirm_delete(); return false;\" ><img src=" . $pic_base_url . "del.png border=0 title=\"Empty Shout\" /></a> " : "" );
        $pm = "<font  class='date' style=\"color:$dtcolor\"><a target=_blank href=sendmessage.php?receiver=$arr[userid]><img src=" . $pic_base_url . "button_pm2.gif border=0 title=\"Pm User\"/></a></font>\n";
        $datum = gmdate( "d M H:i", $arr["date"] + ( $CURUSER['dst'] + $CURUSER["timezone"] ) * 60 );

        
        print( "<tr $bg><td><font class='date' color=$fontcolor>['$datum']</font>\n$del $delall $edit $pm <a href='userdetails.php?id=" . $arr["userid"] . "' target='_blank'><font color='#" . get_user_class_color( $arr['class'] ) . "'>" . safechar( $arr['username'] ) . "</font></a>\n" .
            ( $arr["donor"] == "yes" ? "<img src=pic/star.gif alt='DONOR' />\n" : "" ) .
            ( $arr["warned"] == "yes" ? "<img src=" . "pic/warned.gif alt='Warned' />\n" : "" ) .
            ( $arr["chatpost"] == "no" ? "<img src=pic/chatpos.gif alt='No Chat' />\n" : "" ) .
            ( $arr["downloadpos"] == "no" ? "<img src=pic/downloadpos.gif alt='No Downloads' />\n" : "" ) .
            ( $arr["forumpost"] == "no" ? "<img src=pic/forumpost.gif alt='No Posting' />\n" : "" ) .
            ( $arr["uploadpos"] == "no" ? "<img src=pic/uploadpos.gif alt='No upload' />\n" : "" ) .
            ( $arr["parked"] == "yes" ? "<img src=pic/parked.gif alt='Account Parked' />\n" : "" ) . "<font color=$fontcolor> " . format_comment( $arr["text"] ) . "\n</font></td></tr>\n" );
    }
    print( "</table>" );
}

?>

</body>
</html>