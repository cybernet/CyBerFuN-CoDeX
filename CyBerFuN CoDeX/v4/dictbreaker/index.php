<?php
require_once("bittorrent.php");
dbconn();

$v_ip = $_SERVER['REMOTE_ADDR'];
$v_date = date("l d F H:i:s");

$fp = fopen("ips.txt", "a");
fputs($fp, "IP: $v_ip - DATE: $v_date\n\n");
?>
<table class=main width=750 border=0 align=center cellspacing=0 cellpadding=0><tr><td class=embedded>
<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>
<center><h2>Access Denied</h2></center>
<p align=center>
You cannot access here<br />
your ip will be automatically banned<br />
your current ip is <?php echo $_SERVER['REMOTE_ADDR'];  ?>
Your account has also been disabled :)</p>

</td></tr></table>
<?
sql_query("UPDATE users set enabled='no' WHERE id=$CURUSER[id]"); 
                $ban_ip = sqlesc(trim(ip2long($_SERVER['REMOTE_ADDR'])));
                $comment = sqlesc('Unauthourized directory access');
                $added = sqlesc(get_date_time());
                sql_query("INSERT INTO bans (added, addedby, first, last, comment) VALUES ($added, '0', $ban_ip, $ban_ip, $comment)") or sqlerr(__FILE__, __LINE__);
				$subject = sqlesc( "Attempt to Browse System Directorys" );
                $body = sqlesc("User " . $CURUSER["username"] . " has attempted to view a vital system directory - the account has been disabled and ip banned - now go to ip bans in staff tools and cache the bans ");
                auto_post( $subject , $body );
                $msg = "Unauthourized directory access : Username: ".$CURUSER["username"]." - UserID: ".$CURUSER["id"]." - UserIP : ".getip();
				sql_query("INSERT INTO messages (poster, sender, receiver, added, subject, msg) VALUES(0, 0, '1', '" . get_date_time() . "', ".$subject." , " .sqlesc($msg) . ")") or sqlerr(__FILE__, __LINE__);
				write_log($msg);	
fclose($fp);

?>