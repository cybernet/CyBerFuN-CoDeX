<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
include_once("cache/categories.php");
dbconn();
loggedinorreturn();

	$this_url = $_SERVER["SCRIPT_NAME"];
	$perPage = 10;
	
	$valid = array("add","vote","edit","fill","delete","details"); // valid actions
	$do = isset($_GET["do"]) && in_array($_GET["do"],$valid) ? $_GET["do"] : (isset($_POST["do"]) && in_array($_POST["do"],$valid) ? $_POST["do"] : ""); // action 
	$search =isset($_GET["search"]) ? htmlspecialchars($_GET["search"]) : ""; //search string
	$sort = isset($_GET["sort"]) ? 0+$_GET["sort"] : 1; // some kind of order by 
	$cat = isset($_GET["cat"]) ? 0+$_GET["cat"] : 0; // category 
	$type = isset($_GET["type"]) && $_GET["type"] == "asc"  ? "asc" : "desc" ;  //how to sort the requests default by added DESC
	$uid = isset($_GET["uid"]) ? 0+$_GET["uid"] : 0; // user id 
	$rid = isset($_GET["rid"]) ? 0+$_GET["rid"] : (isset($_POST["rid"]) ? 0+$_POST["rid"] : 0); //request id
	$notfilled  = isset($_GET["notfilled"]) && $_GET["notfilled"] == "yes" ? true : false ; // show only not filled request
	$ref = isset($_GET["r"]) ? $_GET["r"] : "";
	
	if($do == "vote" && $rid > 0)
	{
		$q = mysql_query("SELECT id from addedrequests WHERE userid=".$CURUSER["id"]." AND requestid = ".$rid) or sqlerr();
		if(mysql_num_rows($q) == 1){
			header("Refresh: 3; url=".$ref);
			stderr("Err","Looks that you already voted this request ");
		}
		else
		{
			$q = mysql_query("INSERT INTO addedrequests (requestid,userid) VALUES (".$rid.",".$CURUSER["id"].")") or sqlerr();
			$q1 = mysql_query("UPDATE requests set hits = hits+1 WHERE id =".$rid) or sqlerr();
			if($q && $q1)
			{
				header("Refresh: 2; url=".$ref);
				stderr("Success","You have been successfully voted the request"); 
			}
		}
	}
	elseif($do == "delete" && $_SERVER["REQUEST_METHOD"] == "POST")
	{
		$rids = isset($_POST["rids"]) ? $_POST["rids"] : 0;
		if(count($rids) > 0 && $rids > 0)
		{
			
			if(get_user_class() < UC_MODERATOR)
			{
				$q = mysql_query("SELECT id,userid FROM requests WHERE id IN (".join(",",$rids).") ") or sqlerr();
				unset($rids);
				while($a = mysql_fetch_assoc($q))
				{
					if($a["userid"] == $CURUSER["id"])
						$rids[] = $a["id"];
				}
			}
			if(count($rids) > 0 ) {
				if(mysql_query("DELETE r.*, v.* FROM requests as r  LEFT JOIN addedrequests as v ON v.requestid =r.id WHERE r.id IN (".join(",",$rids).") "))
				{
					header("Refresh: 2; url=".$ref);
					stderr("Success","Requests have been deleted");
				}else
					stderr("Err","There was an error while trying to delete the requests you selected !");
			}	
		}
	}
	elseif($do == "fill" && $rid > 0)
	{
		$q = mysql_query("SELECT * from requests where id =".$rid." AND filledby = 0 ") or sqlerr();
		if(mysql_num_rows($q) == 0) 
		stderr("Err","There is no request with this id or the request is filled !");
		else
		{
			$a = mysql_fetch_assoc($q);
			if($_SERVER["REQUEST_METHOD"] == "POST")
			{
				$url = isset($_POST["url"]) ? htmlspecialchars($_POST["url"]) : "";
				if(empty($url))
				stderr("Err","The url is empty!");
				if(!preg_match("/^".str_replace("/","\/",$BASEURL)."\/details\.php\?id\=[0-9]{1,10}/",$url))
				stderr("Err","That is not the correct url, your url must look like <br/><b>".$BASEURL."/details.php?id=x</b> where <b>x</b> is the id of the torrent.");
				
				$sub = "Request filled by ".$CURUSER["username"];
				$body = $CURUSER["username"]." filled this request ".htmlspecialchars($a["request"])."\n you can download the torrent from this url ".$url;
				
					$q = mysql_query("INSERT INTO messages(sender,receiver,subject,msg,added) VALUES (0,".$a["userid"].", ".sqlesc($sub).",".sqlesc($body).",".sqlesc(get_date_time()).") ") or sqlerr();
					$q1 = mysql_query("UPDATE requests set filled = ".sqlesc($url).", filledby = ".$CURUSER["id"]." WHERE id = ".$rid ) or sqlerr();
					if($q && $q1)
					{
						header("Refresh: 2; url=".$this_url);
						stderr("Success","Request filled !Wait while redirecting...");
					}
					else
						stderr("Err","Something went wrong!");
				exit;
			}
			
			stdhead("Fill request ".htmlspecialchars($a["request"]));
				?>
				<form action="" method="post">
				<table width="400" cellpadding="3" cellspacing="2" style="border-collapse:separate;" align="center">
				  <tr>
					<td width="100%" colspan="2" align="left" class="colhead">Fill request : <?=htmlspecialchars($a["request"])?> added <?=(get_elapsed_time(sql_timestamp_to_unix_timestamp($a["added"])))?> ago </td></tr>
					<tr>
						<td nowrap="nowrap">Torrent url</td>
						<td width="100%"><input type="text" name="url" size="50" onclick="select()"  /><br/><span class="small">
							your torrent url must look like <b><?=$BASEURL?>/details.php?id=x</b> where <b>x</b> is the id of the torrent.
						</span></td>
					</tr>
					<tr>
					<td width="100%" colspan="2" align="center" class="colhead"><input type="submit" value="Fill request" /><input type="hidden" value="fill" name="do" /><input type="hidden" name="rid" value="<?=$a["id"]?>" /></td></tr>
				</table>
				</form>
				<?php
			stdfoot();
			
		
		}
	}
	elseif($do == "add" || $do =="edit")
	{
		$a = array();
		if($do == "edit" && $rid == 0)
		stderr("Err","You cant edit that request");
		elseif($do == "edit" && $rid > 0)
		{
			$q = mysql_query("SELECT r.*,u.username from requests as r LEFT JOIN users as u ON u.id = r.userid WHERE r.id = ".$rid ) or sqlerr();
			if(mysql_num_rows($q) == 0)
			stderr("Err","There is no request with this id");
			$a = mysql_fetch_assoc($q);
			if($a["userid"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
			stderr("w00t",$CURUSER["username"]." what are you trying to do !?");
		}
		if($_SERVER["REQUEST_METHOD"] == "POST")
		{
			$request = isset($_POST["request"]) ? htmlspecialchars($_POST["request"]) : "";
			if(empty($request))
				stderr("Err","Request name cant be empty!");
			$cat = isset($_POST["cat"]) ? 0+$_POST["cat"] : 0;
			if($cat == 0)
				stderr("Err","You have to select a category!");
			$descr = isset($_POST["descr"]) ?  htmlspecialchars($_POST["descr"]) : "";
			if(empty($descr))
				stderr("Err","You must give us some info about the request !");
			
			if($do == "add")
			{
				$q = mysql_query("INSERT INTO requests(userid,request,cat,descr,added) VALUES(".join(",",array_map("sqlesc",array($CURUSER["id"],$request,$cat,$descr,get_date_time()))).")") or sqlerr();
				$rid = mysql_insert_id();
			}
			if($do == "edit" )
				$q = mysql_query("UPDATE requests set descr=".sqlesc($descr).",request=".sqlesc($request).",cat = ".sqlesc($cat)." WHERE id = ".$rid) or sqlerr();
			
			if($q)
					header("Refresh: 0; url=".$this_url."?do=details&rid=".$rid);
				else 
					stderr("Err","Something went wrong!");
			exit;
		}
		$cats = genrelist();
		stdhead($do == "edit" ? "Edit request ".$a["request"] : "Add new request");
		?>
		<form action="<?=$this_url?>" method="post">
		<table width="400" cellpadding="3" cellspacing="2" style="border-collapse:separate;" align="center">
		  <tr>
			<td width="100%" colspan="3" align="center" class="colhead"><?=($do == "edit" ? "Edit request ".htmlspecialchars($a["request"])." made by <a href=\"userdetails.php?id=".$a["userid"]."\">".(!$a["username"] ? "unknown[".$a["userid"]."]" : $a["username"])."</a>" : "Add new request")?></td></tr>
			<tr>
				<td nowrap="nowrap">Request</td>
				<td align="center"><input type="text" name="request" size="80" onclick="select()" value="<?=$a["request"]?>" /></td>
				<td align="center"><select name="cat">
				<option value="0">Select category</option>
					<?php
					$drp = "";
				foreach($cats as $c)
					$drp .= "<option value=\"".$c["id"]."\" ".($c["id"] == $a["cat"] ? "selected=\"selected\"" : "").">".$c["name"]."</option>";
				
				print $drp;
				unset($drp);
			  ?>
				</select></td>
			</tr>
			<tr>
			<td align="center" colspan="3">
			<?php
				print(textbbcode("compose", "descr", $a["descr"]));
			?>
			</td>
			</tr>
			<tr>
			<td width="100%" colspan="4" align="center" class="colhead">
				<input type="submit" value="<?=($do == "edit" ? "Edit" : "Add")?> request" />
				<input type="hidden" name="do" value="<?=($do == "edit" ? "edit" : "add")?>" />
				<input type="hidden" name="rid" value="<?=0+$a["id"]?>" />
			</td></tr> 
		</table>
		</form>
		<?php
		stdfoot();
	}
	elseif($do == "details" && $rid > 0)
	{
		$q = mysql_query("SELECT r.*, u.username, u2.username as uf_user FROM requests as r LEFT JOIN users as u ON u.id = r.userid LEFT JOIN users as u2 ON r.filledby = u2.id WHERE r.id = ".$rid) or sqlerr();
		if(mysql_num_rows($q) == 0)
			stderr("Err","No request with this id!");
		else
		{
			$a = mysql_fetch_assoc($q);
			
			foreach($categories as $c)
				if($c["id"] == $a["cat"])
					$a["catname"] = $c["name"];
					
			stdhead("Details for request - ".$a["request"]);
			?>
				<table width="600" cellpadding="3" cellspacing="2" style="border-collapse:separate" align="center" >
				  <tr>
					<td colspan="3" class="colhead">Details for request : <?=htmlspecialchars($a["request"])?></td>
				  </tr>
				  <tr>
					<td align="right" nowrap="nowrap">Added </td>
					<td align="center" nowrap="nowrap"><?=date("j,M - Y",strtotime($a["added"]))?></td>
					<td align="left" valign="top" rowspan="4" width="100%"><?=format_comment($a["descr"])?></td>
				  </tr>
				  <tr>
					<td align="right" nowrap="nowrap">Added by </td>
					<td align="center"><a href="userdetails.php?id=<?=$a["userid"]?>"><?=(!$a["username"] ? "unknown[".$a["userid"]."]" : $a["username"] )?></a></td>
				  </tr>
				  <tr>
					<td align="right" nowrap="nowrap">Category </td>
					<td align="center"><a href="<?=$this_url?>?cat=<?=$a["cat"]?>"><?=$a["catname"]?></a></td>
				  </tr>
				  <tr>
					<td align="right" nowrap="nowrap">Votes</td>
					<td align="center"><a href="<?=$this_url?>?do=vote&amp;rid=<?=$a["id"]?>"><?=$a["hits"]?></a></td>
				  </tr>
				  <tr>
					<td colspan="3" class="colhead">
					<?=($a["filledby"] !=0 ? "Filled by <a href=\"userdetails.php?id=".$a["filledby"]."\">".$a["uf_user"]."</a> - torrent can be found <a href=\"".$a["filled"]."\">here</a>" : "<input type=\"button\" value=\"Fill request\" onclick=\"window.location.href='".$this_url."?do=fill&amp;rid=".$a["id"]."'\" />")?>
					&nbsp;
					<?=($a["userid"] == $CURUSER["id"] || get_user_class() > UC_MODERATOR ? "<input type=\"button\" value=\"Edit request\" onclick=\"window.location.href='".$this_url."?do=edit&amp;rid=".$a["id"]."'\" />" : "" )?>
					&nbsp;
					<input type="button" value="Back" onclick="window.location.href='<?=$this_url?>'" />
					</td>
				  </tr>
				</table>
			<?php
			stdfoot();
		}
	
	}
	else
	{
	$wheres = array();
	$pager = array();
	$orders = array(1=>"r.added",2=>"r.request",3=>"r.hits");
	
	if(!empty($search)){
		$wheres[] = "r.request LIKE ".sqlesc("%".$search."%");
		$pager[] = "search=".$search; 
	}
	if($uid > 0)
		$wheres[] = "r.userid = ".$uid;
	if($notfilled)	
		$wheres[] = "r.filledby = 0 ";
	if($cat > 0) {
		$wheres[] = "r.cat = ".$cat;
		$pager[] = "cat=".$cat;
	}
	$order = "ORDER BY ".$orders[$sort]. " ".$type;
	$pager[] = "sort=".$sort."&amp;type=".$type;
	
	if(count($wheres) > 0)
	$where = "WHERE ".join(" AND ",$wheres);
	
	$count = get_row_count("requests as r",$where);
	list($p["top"], $p["bottom"], $p["limit"]) = pager($perPage, $count, $this_url."?".join("&amp;",$pager)."&amp;");
	
	$cats = genrelist();
	stdhead(empty($search) ? "View requests" : "Search reasult for \"".$search."\"");
	begin_main_frame();
	begin_frame("Requests Section",true);
	
	?>
	<form action="<?=$this_url?>" method="get" >
	<table width="800" cellpadding="3" cellspacing="2" style="border-collapse:separate;">
	  <tr>
		<td align="left" colspan="3" class="colhead">Search for a request</td>
	  </tr>
	  <tr>
	   <td align="center" colspan="3"><input type="text" name="search" value="<?=$search?>" size="100"  />&nbsp;<input type="submit" value="Search!" /></td>
	  </tr>
	  <tr>
		<td align="left" colspan="3" class="colhead">More options</td>
	  </tr>
	  <tr>
		<td align="center"> search by category
		  <select name="cat">
			<option value="0">All cats</option>
		  <?php
				$drp = "";
			foreach($cats as $c)
				$drp .= "<option value=\"".$c["id"]."\" ".($c["id"] == $cat ? "selected=\"selected\"" : "").">".$c["name"]."</option>";
			
			print $drp;
			unset($drp);
		  ?>
		  </select>
		</td>
		<td align="center"><input type="button" value="Show unfilled" onclick="window.location.href='<?=$this_url?>?notfilled=yes'" />
		  <input type="button" value="your requests" onclick="window.location.href='<?=$this_url?>?uid=<?=$CURUSER["id"]?>'"/></td>
		<td align="center"> sort by
		  <select name="sort">
			<option value="1" <?=($sort == 1 ? "selected=\"selected\"" : "")?> >added</option>
			<option value="2" <?=($sort == 2 ? "selected=\"selected\"" : "")?>>request</option>
			<option value="3" <?=($sort == 3 ? "selected=\"selected\"" : "")?>>votes</option>
		  </select>
		  &nbsp;<label for="sort_ha"><input id="sort_ha"type="radio" name="type" value="asc" <?=($type == "asc" ? "checked=\"checked\"" : "")?> />asc</label><label for="sort_hd"> <input type="radio" id="sort_hd" name="type" value="desc" <?=($type == "desc" ? "checked=\"checked\"" : "")?> /> desc</label>
		</td>
	  </tr>
	  <?php if(!empty($search)) { ?>
	  <tr>
		<td align="left" colspan="3" class="colhead">Search result</td>
	  </tr>
	  <tr>
	   <td align="center" colspan="3">You searched for <b><em><?=htmlspecialchars($search)?></em></b> <br/>if you didn't find what you wanted make a <input type="button" value="new request" onclick="window.location.href='<?=$this_url?>?do=add'"/></td>
	  </tr>
	  <?php } ?>
	</table>
	</form>
	<?php
	end_frame();
	end_main_frame();
	
	begin_main_frame();
	begin_frame("Current requests",true);

	$q = sql_query("SELECT r.id, r.request as rname , r.added, r.hits as votes , r.userid as ur_id ,u.username as ur_user , r.filledby as uf_id , u2.username as uf_user, r.cat, r.filled    FROM requests as r  LEFT JOIN users as u ON u.id = r.userid LEFT JOIN users as u2 ON u2.id = r.filledby ".$where." ".$order. " ".$p["limit"]) or print("error : ".mysql_error());
	
	if(mysql_num_rows($q) == 0 )
	stdmsg(":w00t:","No request at the moment!");
	else
	{
	?>
		<script type="text/javascript">
		function checkbox(button,filled)
		{
			for(i=0; i<document.forms[1].elements.length; i++)
			{
				if(!filled)
				{
					if(document.forms[1].elements[i].type == 'checkbox')
					{
						var state = document.forms[1].elements[i].checked;
						document.forms[1].elements[i].checked = state ? false : true ;
					}
				}
				else
				{
					if(document.forms[1].elements[i].type == 'checkbox' && document.forms[1].elements[i].id.indexOf("req_f") > -1 )
					{
						var state = document.forms[1].elements[i].checked;
						document.forms[1].elements[i].checked = state ? false : true ;
					}
				}
			}	
			var del_button = document.getElementById("del_button");
			if(state == false)
				del_button.setAttribute("onclick", filled ? "add_alert(2); return false;" : "add_alert(1); return false;");
			else 
				del_button.setAttribute("onclick", "");	
				
			button.value = (state ? "Check all" : "Uncheck all") +" "+ (filled ? "filled" : "");
		}
		function add_alert(id)
		{
			var msg = new Array();
			msg[1] = "You selecte all the requests , are you sure you know what your doing ? ";
			msg[2] = "You selecte the filled requests are you sure you know what your doing ?";
			if(confirm(msg[id]))
			document.forms[1].submit();
		}
	</script>

	<table width="800" cellpadding="3" cellspacing="2" style="border-collapse:separate;">
	  <tr>
		<td width="100%" align="left" class="colhead" style="height:23px;" ><?=($perPage < $count ? $p["top"] : "")?></td></tr>
	</table>
	<form method="post" action="<?=$this_url?>" >
	<?php
	while($a = mysql_fetch_assoc($q)) { 
		foreach($categories as $c )
			if($c["id"] == $a["cat"])
				$a["catname"] = $c["name"];
	?>
	<table width="800" cellpadding="3" cellspacing="2" style="border-collapse:separate;">
	  <tr>
		<td align="left" width="70%"  rowspan="2"><a href="<?=$this_url."?do=details&amp;rid=".$a["id"]?>" ><?=htmlspecialchars($a["rname"])?></a><br/>
			<font class="small" style="color:#222">
				added <?=(get_elapsed_time(sql_timestamp_to_unix_timestamp($a["added"])))?> ago by <a href="userdetails.php?id=<?=$a["ur_id"]?>" ><?=$a["ur_user"] != NULL ? $a["ur_user"] : "unknown[".$a["ur_id"]."]"?></a>
			</font>
		</td>
    <td align="center" class="colhead">Category</td>
    <td align="center" class="colhead">Votes</td>
    <td align="center" class="colhead" colspan="2" width="20%">Status</td>
	</tr>
	  <tr>
		<td align="center" nowrap="nowrap"><a href="<?=$this_url."?cat=".$a["cat"]?>"><b><?=$a["catname"]?></b></a></td>
		<td align="center"  nowrap="nowrap" title="vote this request"><a href="<?=$ths_url."?do=vote&amp;rid=".$a["id"]?>&amp;r=<?=urlencode($_SERVER["REQUEST_URI"])?>"><b><?=$a["votes"]?></b></a></td>
		<td align="center"  nowrap="nowrap"><?=($a["filled"] == NULL || empty($a["filled"]) ? "<a title=\"fill this request\" href=\"".$this_url."?do=fill&amp;rid=".$a["id"]."\"><font style=\"color:#ff0000;\">Not filled</font></a>" : "<font style=\"color:#009900\">Filled</font><br/>by <a href=\"userdetails.php?id=".$a["uf_id"]."\">".cutName($a["uf_user"],5)."</a>" )?></td>
		<td align="center"  nowrap="nowrap"><input type="checkbox" name="rids[]" value="<?=$a["id"]?>" id="req_<?=($a["uf_id"] > 0 ? "f" : "" )."_".$a["id"]?>" title="mark if you want to delete this" <?=($a["ur_id"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR ? "disabled=\"disabled\"" : "")?>/></td>
	  </tr>
	</table>
	<?php } ?>
	<table width="800" cellpadding="3" cellspacing="2" style="border-collapse:separate;">
	  <tr>
		<td width="50%" align="left" class="colhead"><?=($perPage < $count ? $p["bottom"] : "")?></td>
		<td width="50%" align="right" class="colhead">
		<input type="button" onclick="checkbox(this)" value="Check all"/> 
		<input type="button" onclick="checkbox(this,true)" value="Check all Filled"/> 
		<input type="submit" value="Delete" id="del_button" /><input type="hidden" name="do" value="delete" /></td>
	  </tr>
	</table>
	</form>
	<?php
}
end_frame();
end_main_frame();
stdfoot();
}
?>