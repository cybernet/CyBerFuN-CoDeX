<?php
include('include/bittorrent.php');
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_ADMINISTRATOR)
hacker_dork("Freeslots manager - Nosey Cunt !");
/*+----------------------------------+
  | Made by Putyn @ tbdev 31/05/2009 |
  +----------------------------------+
*///dont forget to edit this 
	$maxclass = UC_CODER;
	$firstclass = UC_USER;
	
	function mkpositive($n)
	{
		return strstr((string)$n,"-") ? 0 : $n ; // this will return 0 for negative numbers 
	}
	
	if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST")
	{
		$classes = isset($_POST["classes"])? $_POST["classes"] : "";
		$all = ($classes[0] == 255 ? true : false );
		if(empty($classes) && sizeof($classes) == 0 )
			stderr("Err","You need at least one class selected");
		$a_do = array("add","remove","remove_all");
		$do = isset($_POST["do"]) && in_array($_POST["do"],$a_do) ? $_POST["do"] : "";
		if(empty($do))
			stderr("Err","wtf are you trying to do ");
			
		$freeslots = isset($_POST["freeslots"]) ? 0+$_POST["freeslots"] : 0;
		if($freeslots == 0 && ($do == "add" || $do == "remove"))
			stderr("Err","You can't remove/add 0");
			
		$sendpm = isset($_POST["pm"]) && $_POST["pm"] == "yes" ? true : false;
		
		$pms = array();
		$users = array();
		//select the users
		$q = mysql_query("SELECT id,freeslots FROM users ".($all ? "" : "WHERE class in (".join(",",$classes).")" )." ORDER BY id desc ") or sqlerr(__FILE__, __LINE__);
		if(mysql_num_rows($q) == 0)
		stderr("Sorry","There are no users in the class(es) you selected");
			while($a = mysql_fetch_assoc($q))
			{
				$users[] = "(".$a["id"].", ".($do == "remove_all" ? 0 : ($do == "add" ? $a["freeslots"] + $freeslots : mkpositive($a["freeslots"] - $freeslots))) .")";
				if($sendpm)
				{
					$subject = sqlesc($do == "remove_all" && $do == "remove" ?  "freeslots removed" : "freeslots added");
					$body = sqlesc("Hey,\n we have decided to ". ($do == "remove_all" ?  "remove all freeslots from your group class" : ($do == "add" ? "add $freeslots freeslot".($freeslots > 1 ? "s" : "")." to your group class" : "remove $freeslots freeslot".($freeslots > 1 ? "s" : "")."  from your group class")). " !\n ".$SITENAME ." staff");
					$pms[] = "(0,".$a["id"].",".sqlesc(get_date_time()).",$subject,$body)" ;
				}
			}
			
			if(sizeof($users) > 0)
				$r = mysql_query("INSERT INTO users(id,freeslots) VALUES ".join(",",$users)." ON DUPLICATE key UPDATE freeslots=values(freeslots) ") or sqlerr(__FILE__, __LINE__);
			if(sizeof($pms) > 0)
				$r1 = mysql_query("INSERT INTO messages (sender, receiver, added, subject, msg) VALUES ".join(",",$pms)." ") or sqlerr(__FILE__, __LINE__);
				
			if($r && ($sendpm ? $r1 : true))
			{
				header("Refresh: 2; url=".$_SERVER["PHP_SELF"]);
				stderr("Success","Operation done!");
			}
			else
				stderr("Error","Something was wrong");
	}
	stdhead("Add/Remove freeslots");
	begin_frame();
	?>
	<form  action="<?=$_SERVER["PHP_SELF"]?>" method="post">
	<table width="500" cellpadding="5" cellspacing="0" border="1" align="center">
	  <tr>
		<td valign="top" align="right">Classes</td>
		<td width="100%" align="left" colspan="3">
			<?php
					$r= "<label for=\"all\"><input type=\"checkbox\" name=\"classes[]\" value=\"255\" id=\"all\" />All classes</label><br/>\n";
				for($i=$firstclass;$i<$maxclass+1; $i++ )
					$r .= "<label for=\"c$i\"><input type=\"checkbox\" name=\"classes[]\" value=\"$i\" id=\"c$i\" />".get_user_class_name($i)." </label><br/>\n";
				echo $r;
			?>
		</td>
	  </tr>
	  <tr>
		<td valign="top" align="center" >Options</td>
		<td valign="top">Do
		  <select name="do" >
			<option value="add">add freeslots</option>
			<option value="remove">remove freeslots</option>
			<option value="remove_all">Remove all freeslots</option>
		  </select></td>
		<td>freeslots <input type="text" maxlength="2" name="freeslots" size="5" />
		</td>
		<td >Send pm <select name="pm" ><option value="no">no</option><option value="yes">yes</option></select></td></tr>
		<tr><td colspan="4" align="center"><input type="submit" value="Do!" /></td></tr>
	</table>
	</form>
<?php
	end_frame();
	stdfoot();
?>