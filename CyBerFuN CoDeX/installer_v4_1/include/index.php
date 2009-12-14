<?php
require_once("bittorrent.php");
dbconn(false);

$v_ip = $_SERVER['REMOTE_ADDR'];
$v_date = date("l d F H:i:s");

$fp = fopen("ips.txt", "a");
fputs($fp, "IP: $v_ip - DATE: $v_date\n\n");
?>
<table class=main width=750 border=0 align=center cellspacing=0 cellpadding=0><tr><td class=embedded>
<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>
<center><h2>Access Denied</h2></center>
<p align=center>
You cannot access here - Your account has been disabled and<br />
your ip will be automatically logged<br />
your current ip is <?php echo $_SERVER['REMOTE_ADDR'];  ?><br />
Have an nice day :)</p><br />
</td></tr></table>
<?php
//////////////If there a regged member then do the damage otherwise just log it///////////
sql_query("UPDATE users set enabled='no' WHERE id=$CURUSER[id]"); 
        $ban_ip = sqlesc(trim(ip2long($_SERVER['REMOTE_ADDR'])));
        $comment = sqlesc('System Directory Alert');
        $added = sqlesc(get_date_time());
        sql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES ($added, '0', $ban_ip, $ban_ip, $comment)") or sqlerr(__FILE__, __LINE__);
		    $subject = sqlesc( "System Directory Alert" );
        $body = sqlesc("User " . $CURUSER["username"] . " has attempted to view system directorys - the account has been disabled");
        auto_post( $subject , $body );
        $msg = "System Directory Alert - now go to ip bans in staff tools and cache the ban or check it out : Username: ".$CURUSER["username"]." - UserID: ".$CURUSER["id"]." - UserIP : ".getip();
		sql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg) VALUES(0, 0, '1', '" . get_date_time() . "', ".$subject." , " .sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
		write_log($msg);
fclose($fp);
?>