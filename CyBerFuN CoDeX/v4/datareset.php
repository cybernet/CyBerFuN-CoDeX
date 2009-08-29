<?php
// ////////Data reset by Putyn///////////////////////////
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Reset Ratio - Nosey Cunt !");


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$tid = (isset($_POST["tid"]) ? 0 + $_POST["tid"] : 0);
	if($tid == 0)
		stderr(":w00t:","wtf are your trying to do!?");
	if (get_row_count("torrents","where id=".$tid) != 1)
		stderr(":w00t:","That is not a torrent !!!!");
	
	$q = mysql_query("SELECT s.downloaded as sd , t.id as tid, t.name,t.size, u.username,u.id as uid,u.downloaded as ud FROM torrents as t LEFT JOIN snatched as s ON s.torrentid = t.id LEFT JOIN users as u ON u.id = s.userid WHERE t.id =".$tid) or print(mysql_error());
	while ($a = mysql_fetch_assoc($q))
	{
		$newd = ($a["ud"] > 0 ? $a["ud"]-$a["sd"] : 0 );
		$new_download[] = "(".$a["uid"].",".$newd.")";
		$tname = $a["name"];
		$msg = "Hey , ".$a["username"]."\n";
		$msg .= "Looks like torrent [b]".$a["name"]."[/b] is nuked and we want to take back the data you downloaded\n";
		$msg .= "So you downloaded ".prefixed($a["sd"])." your new download will be ".prefixed($newd)."\n";
		$pms[] = "(0,".$a["uid"].",".sqlesc(get_date_time()).",".sqlesc($msg).")";
	}
	//send the pm !!
	mysql_query("INSERT into messages (sender, receiver, added, msg) VALUES ".join(",",$pms)) or print(mysql_error());
	//update user download amount
	mysql_query("INSERT INTO users (id,downloaded) VALUES ".join(",",$new_download)." ON DUPLICATE key UPDATE downloaded=values(downloaded)") or print(mysql_error());
	deletetorrent($tid);
	stderr(":w00t:","it worked! long live tbdev!");
	write_log("Torrent $tname was deleted by ".$CURUSER["username"]." and users Re-Paid Download");
	
}
else
{	
	stdhead("Torrent Reset");
	begin_frame();
	?>
	<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
	<fieldset>
	<legend> Reset Ratio for nuked torrents</legend>
    <table width="500" border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse" align="center">
    	<tr><td align="right" nowrap="nowrap">Torrent id</td><td align="left" width="100%"><input type="text" name="tid" size="20" /></td></tr>
        <tr><td style="background:#990033; color:#CCCCCC;" colspan="2">
        	<ul>
					<li>Torrent id must be a number and only a number!!!</li>
					<li>If the torrent is not nuked or there is not problem with it , don't use this !</li>
					<li>If you don't know what this will do , <b>go play somewere else</b></li>
				</ul>
			</td></tr>
			<tr><td colspan="2" align="center"><input type="submit" value="re-pay!" /></td></tr>
		</table>

	</fieldset>

	</form>
	<?php
	end_frame();
	stdfoot();
}
?>