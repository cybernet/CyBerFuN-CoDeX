<?php
require $_SERVER["DOCUMENT_ROOT"]."/include/bittorrent.php";
dbconn();
loggedinorreturn();

	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	header("Content-Type: text/html; charset=iso-8859-1");
	
	$do = (isset($_POST["action"]) ? $_POST["action"] : "");
	$room = (isset($_POST["room"]) ? 0+$_POST["room"] : 0 );
	$shout = (isset($_POST["shout"]) ? $_POST["shout"] : "");
	$shoutid = (isset($_POST["shoutid"]) ? 0+$_POST["shoutid"] : 0);
	$to = (isset($_POST["to"]) ? 0+$_POST["to"] : 0);
	$user = (isset($_POST["user"]) ? 0+$_POST["user"] : $CURUSER["id"]);
	//$user = $CURUSER["id"];
	$time = time();
	
	function refresh($room)
	{
		GLOBAL $CURUSER;
		$r = mysql_query("SELECT r.id as room ,m.id as sid ,m.user ,m.body,m.date,u.username FROM chat_rooms as r LEFT JOIN chat_msgs as m ON r.id=m.room LEFT JOIN users as u on m.user=u.id WHERE r.id =".$room." ORDER by m.date DESC ") or print(mysql_error());
		if (mysql_num_rows($r)== 0)
		print("No messages");
		else
		{
			while ($a = mysql_fetch_assoc($r)){
			if(isset($a["sid"]) && $a["user"] > 0)
			print("<font class=\"small\">".date("d/M | h:i",$a["date"])."</font>".( ($CURUSER["id"] == $a["user"]) ? "<img src=\"ajax/delete.png\" width=\"12\" height=\"12\" border=\"0\" title=\"Delete shout!s\"  id=\"delete_button_".$a["room"]."\" style=\"padding:1px;cursor:pointer;\"onclick=\"delete_shout(".$a["room"].",".$a["sid"].");\"><img src=\"ajax/edit.png\" id=\"edit_button_".$a["room"]."\" width=\"12\" height=\"12\" border=\"0\" title=\"Edit shout!\" style=\"padding:1px;cursor:pointer;display:none;\"onclick=\"edit_shout(".$a["room"].",".$a["sid"].");\">" : "&nbsp;")."<a href=\"userdetails.php?id=".$a["user"]."\">".$a["username"]."</a>&nbsp;".htmlspecialchars($a["body"])."<br/>");
			elseif($a["user"] == 0)
			print("<font class=\"small\"><em>System :</em>&nbsp;".htmlspecialchars($a["body"])." (".date("d/M|h:i|A",$a["date"]).")</font><br/>");
			else
			print("No messages");
			}
		}
	
	}
	
	
	if($do == "shout")
	{	if(empty($shout))
		print("Shout is empty");
		else
		{
		mysql_query("INSERT INTO chat_msgs (user,body,date,room) VALUES(".implode(",",array_map("sqlesc",array($user,$shout,$time,$room))).") ") or print(mysql_error());
		}
		//refresh($room);
	}
	elseif($do == "delete")
	{
		if($shoutid > 0)
		mysql_query("DELETE FROM chat_msgs where id=".$shoutid) or print(mysql_error());
		//refresh($room);
	
	}
	elseif($do == "get_shout")
	{
		if($shoutid > 0)
		print(mysql_result(mysql_query("SELECT body from chat_msgs WHERE id=".$shoutid),0)) or print(mysql_error());
	
	}
	elseif($do == "edit_shout")
	{
		if($shoutid > 0 && !empty($shout))
		mysql_query("UPDATE chat_msgs set body=".sqlesc($shout)." WHERE id=".$shoutid) or print(mysql_error());
	
	}
	elseif ($do == "chat_alert")
	{
		$r = mysql_query("SELECT r.id, r.by_user , u.username FROM chat_rooms as r LEFT JOIN users as u on r.by_user=u.id WHERE r.status='open' AND r.for_user=".$user) or print(mysql_error());
		if (mysql_num_rows($r) == 1 ){
			$a = mysql_fetch_row($r);
				print( json_encode(array ( "user" => $a[1], "username"=> $a[2], "room"=>$a[0])));
				
		}
	}
	elseif ($do == "progress")
	{
	
		if($room > 0){
		
			$r = mysql_query ("SELECT for_user from chat_rooms where id=".$room) or print(mysql_error());
			$a = mysql_fetch_row($r);
				if($a[0] == $user)
			mysql_query("UPDATE chat_rooms set status='progress' where id=".$room) or print(mysql_error());
		}
	
	}
	elseif($do == "close_room")
	{
		if($room > 0)
		mysql_query("UPDATE chat_rooms set status='close' where id=".$room) or print(mysql_error());
			
		
	}
	elseif ($do == "chat_request")
	{	if ($to > 0)
		{
			$r = mysql_query("SELECT id FROM chat_rooms WHERE for_user=".$to." AND by_user=".$user." ")  or print(mysql_error());
			$user_name = (mysql_result(mysql_query("SELECT username from users WHERE id=".$to),0));
			if(mysql_num_rows($r) == 0 )
			{
				mysql_query("INSERT INTO chat_rooms(`for_user`,`by_user`,`status`) VALUES(".$to.",".$user.",'open') ")or print(mysql_error());
				print( json_encode(array ( "username"=> $user_name, "room"=>mysql_insert_id() )));
			}
			else
			{
				$a = mysql_fetch_row($r);
				mysql_query("UPDATE chat_rooms set status='open' where id=".$a[0]) or print(mysql_error());
				print( json_encode(array ( "username"=> $user_name, "room"=>$a[0] )));
			}
		}
	}
	elseif($do == "reject")
	{
		$user_name = (mysql_result(mysql_query("SELECT username from users WHERE id=".$user),0));
		$msg = $user_name." rejected the chat request .Reason: ".$shout;
		mysql_query("INSERT INTO chat_msgs (user,body,date,room) VALUES(".implode(",",array_map("sqlesc",array(0,$msg,$time,$room))).") ") or print(mysql_error());
		mysql_query("update chat_rooms set status='close' WHERE id=".$room) or print(mysql_error());
	}
	elseif($do == "onlineList")
	{
		$dt = 180; //time in s
		$r = mysql_query("SELECT f.userid AS fid, u.username FROM users as u LEFT JOIN friends as f ON f.userid=u.id WHERE f.friendid=".$user." AND f.confirmed='yes' ORDER BY u.username ASC ") or print(mysql_error());
		//$r = mysql_query("SELECT f.userid AS fid, u.username,r.status FROM users as u LEFT JOIN chat_rooms as r ON (r.by_user = ".$user." AND r.for_user=u.id)LEFT JOIN friends as f ON f.userid=u.id WHERE f.friendid=".$user." AND UNIX_TIMESTAMP(u.last_access) > ".(time()-$dt)." ORDER by u.username ASC ") or print(mysql_error());
		//$r = mysql_query("SELECT f.userid AS fid, u.username,r.status FROM users as u LEFT JOIN chat_rooms as r ON (r.by_user = ".$user." AND r.for_user=u.id)LEFT JOIN friends as f ON f.userid=u.id WHERE f.friendid=".$user." ORDER by u.username ASC ") or print(mysql_error());
		if (mysql_num_rows($r) == 0 ) 
		print("No user online");
		else 
		{
			while($a = mysql_fetch_assoc($r))
			{
				$chat_icon =(($a["status"] == "progress" || $a["status"] == "open") ? "<img src=\"ajax/chat_progress.png\" style=\"padding:-4px;\" title=\"Chat in progress\" onclick=\"chat_request(".$a["fid"].")\">" : ($a["status"] == "close" ? "<img src=\"ajax/chat_closed.png\" style=\"padding:-4px;\"  title=\"Chat closed, click to reopen!\" onclick=\"chat_request(".$a["fid"].")\" />" : "<img src=\"ajax/chat_new.png\" style=\"padding:-4px;\"  title=\"Start new chat\"  onclick=\"chat_request(".$a["fid"].")\" />"));
				print("<div style=\"width:100%;\"><div style=\"width:90%;white-space:nowrap;float:left\"><a href=\"userdetails.php?id=".$a["fid"]."\">".$a["username"]."</a></div><div style=\"width:10%;white-space: nowrap;float:right;\">".$chat_icon."</div></div><br/>");
			}
		}
	
	}
	else
	refresh($room);

?>