<?php
require_once ("include/bittorrent.php");
dbconn();
loggedinorreturn();

	$FSCLASS = 4; //first staff class;
	$LSCLASS = 7; //last staff class;
	$FUCLASS = 0; //firs users class;
	$LUCLASS = 3; //last users class;
	
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$groups = isset($_POST["groups"]) ? $_POST["groups"] : "";
		$subject = isset($_POST["subject"]) ? htmlspecialchars($_POST["subject"]) : "";
		$msg = isset($_POST["message"]) ? htmlspecialchars($_POST["message"]) : "";
		$sender = isset($_POST["system"]) && $_POST["system"] == "yes" ? 0 : $CURUSER["id"];
		
		$err = array();
		if(empty($subject))
			$err[] = "Your message doesn't have a subject";
		if(empty($msg))
			$err[] = "There is not message in your message!";
			$msg .= "\n This is a group message !";
		if(empty($groups))
			$err[] = "You have to select a group to send your message";
		
		//exit(printr($groups));
		if(sizeof($err) == 0)
		{
			$where = array();
			$classes = array();
			$ids = array();
			foreach($groups as $group)
			{
				if(is_string($group))
				{
					switch($group)
						{
							case "all_staff" : $where[] = "u.class BETWEEN ".$FSCLASS." and ".$LSCLASS;
							break;
							case "all_users" : $where[] = "u.class BETWEEN ".$FUCLASS." and ".$LUCLASS;
							break;
							case "fls" : $where[] = "u.support='yes'";
							break;
							case "donor" : $where[] = "u.donor = 'yes'";
							break;
							case "all_friends" :
							{
								$fq = mysql_query("SELECT friendid FROM friends WHERE userid=".$CURUSER["id"]." AND confirmed='yes' ");
								if(mysql_num_rows($fq))
									while($fa = mysql_fetch_row($fq))
										$ids[] = $fa[0];
							
							}
							break;
						}
				}
				if(is_numeric($group+0) && $group+0 > 0)
					$classes[] = $group;
			}
			if(sizeof($classes) > 0 )
				$where[] = "u.class IN (".join(",",$classes).")";
			if(sizeof($where) > 0)	
			{
				$q = mysql_query("SELECT u.id FROM users AS u WHERE ".join(" OR ",$where));
				if(mysql_num_rows($q) > 0)
					while ($a = mysql_fetch_row($q))
						$ids[] = $a[0];
			}
			$ids = array_unique($ids);
			if(sizeof($ids) > 0)
			{
				$pms = array();
				foreach($ids as $rid)
					$pms[] = "(".$sender.",".$rid.",".sqlesc(get_date_time()).",".sqlesc($msg).",".sqlesc($subject).")";
				
				if(sizeof($pms) > 0)
					$r = mysql_query("INSERT INTO messages(sender,receiver,added,msg,subject) VALUES ".join(",",$pms)) or print(mysql_error());
					$err[] = ($r ? "Message sent!" : "Unable to send the message try again!");
			}
			else $err[] = "There is not users in the groups you selected!";
		}
	}
	

	$groups = array(
		array("opname"=>"Site Staff",
		      "minclass" => UC_USER,
			   "ops"=>array( 
								array(7=>"Coders"),
								array(6=>"SySops"),
								array(5=>"Admins"),
								array(4=>"Mods"),
								array(3=>"Uploaders"),
								array("fls"=>"First line support"),
								array("all_staff"=>"All staff")
							  )),
		array("opname"=>"Members Groups",
			  "minclass" => UC_MODERATOR,
				"ops" =>array(
								array(0=>"Users"),
								array(1=>"Power users"),
								array(2=>"Vips"),
								array("donor"=>"Donors"),
								array("all_users"=>"All users")
								
								)),
		array ("opname" => "Related to you",
				"minclass"=>UC_USER,
				"ops" =>array (
							array("all_friends"=>"Your friends (not yet)")
						
				))
	);
	function mysort($array)
		{
			foreach($array as $key=>$value)
				{
					foreach($value as $key2 =>$value2)
					$new[$key2] = $value2;
				}
			return $new;
		}
	function dropdown()
	{
		GLOBAL $CURUSER, $groups;
		$r = "<select multiple=\"multiple\" name=\"groups[]\"  size=\"11\" style=\"padding:5px; width:180px;\">";
		foreach($groups as $group)
		{
			if($group["minclass"] >= $CURUSER["class"])
			continue;
			$r .= "<optgroup label=\"".$group["opname"]."\">";
				$ops = mysort($group["ops"]);
				foreach($ops as $k=>$v)
					$r .= "<option value=\"".$k."\">".$v."</option>";
			$r .="</optgroup>";
		}
		$r .="</select>";
		return $r;
	}
	
	stdhead("Group message");
	begin_main_frame();
	if(sizeof($err) > 0)
	{
		$class = (stristr($err[0],"sent!") == true ? "sent" : "notsent");
		$errs = "<ul><li>".join("</li><li>",$err)."</li></ul>";
		print("<div class=\"".$class."\">$errs</div>");
	}
	?>
	<style type="text/css">
	.sent, .notsent {
	width:100%;
	padding:5px;
	margin:5px;
	border:1px solid;
	color:#333333;
	text-align:left;
	font-weight:bold;
	}
	.sent {
		border-color: #49c24f;
		background:#bcffbf;
	}
	.notsent {
		border-color: #c24949;
		background:#ffbcbc;
	}
	</style>
	<fieldset style="border:1px solid #333333; padding:5px;">
	<legend style="padding:3px 5px 3px 5px; border:solid 1px #333333; font-size:12px;font-weight:bold;">Group message</legend>
	<form action=" " method="post" >
	  <table width="500" border="1" style="border-collapse:collapse" cellpadding="5" cellspacing="0" align="center">
		<tr>
		  <td nowrap="nowrap" align="left" colspan="2"><b>Subject</b> &nbsp;&nbsp;
			<input type="text" name="subject" size="30" style="width:300px;"/></td>
		</tr>
		<tr>
		  <td nowrap="nowrap" valign="top" align="left"><b>Body</b></td>
		  <td nowrap="nowrap" align="left"><b>Groups</b></td>
		<tr>
		  <td width="100%" align="center"><textarea name="message" rows="5" cols="32" style="width:300px; height:155px"></textarea></td>
		  <td width="100%" ><?=(dropdown())?></td>
		</tr>
		<tr>
		 <td align="left"><label for="sys">Send as <b>System</b>&nbsp;</label><input id="sys" type="checkbox" name="system" value="yes" /></td><td align="right" ><input type="submit" value="Send !" /></td>
		</tr>
	  </table>
	</form>
	</fieldset>
	<?php
	end_main_frame();
	stdfoot();
?>