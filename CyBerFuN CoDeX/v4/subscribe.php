<?php
include("include/bittorrent.php");
dbconn();
loggedinorreturn();

$do = isset($_GET["do"]) ? htmlspecialchars($_GET["do"]) : "";
$sure = isset($_GET["sure"]) && $_GET["sure"] == "yes" ? true : false;
$ref = isset($_GET["r"]) ? str_replace("&amp;","&",$_GET["r"]) : "";
$tid = isset($_GET["tid"]) ? 0+$_GET["tid"] : 0;
$uid = 0+$CURUSER["id"];

if(($do == "add" || $do == "remove") && $tid > 0)
{
$q = mysql_query("SELECT s.id, t.subject as tname FROM topics as t LEFT JOIN subscriptions as s ON s.topicid = t.id AND s.userid = ".$uid." where t.id =".$tid) or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_assoc($q);

$is_sub = ($a["id"] != NULL ? true : false);

if($do == "add")
{
if($is_sub)
stderr("err","You are already subscribed to this topic");
else
{
if(mysql_query("INSERT INTO subscriptions(userid,topicid) VALUES (".$uid.",".$tid.")"))
{
header("Refresh: 2; url=".$ref);
stderr("Success","You have been successfully subscribed to the topic! Please wait while redirecting...");
}
else
stderr("Err","There was an error you should contact the sysop");

}
}
else
{
if(!$is_sub)
stderr("Err","Sorry but there is no record of you being subscribed to this topic!");

if(!$sure)
stderr("Sanity check...", "You are about to delete the subscribe for topic <b>".htmlspecialchars($a["tname"])."</b>. Click <a href=".$_SERVER["SCRIPT_NAME"]."?do=remove&amp;tid=".$tid."&amp;r=".urlencode($ref)."&amp;sure=yes>here</a> if you are sure.");
else
{
if(mysql_query("DELETE FROM subscriptions WHERE topicid=".$tid." AND userid = ".$uid))
{
header("Refresh: 2; url=".$ref);
stderr("Success","You have been successfully un-subscribed from the topic! Please wait while redirecting...");
}
else
stderr("Err","There was an error you should contact the sysop");
}

}

}
{
stdhead("Current Subscriptions");
begin_main_frame();
begin_frame("Current Subscriptions");
//select the tids
$q = mysql_query("SELECT * FROM subscriptions WHERE userid=".$uid) or sqlerr(__FILE__, __LINE__);
if(mysql_num_rows($q) == 0)
stderr(":w00t:","You are not yet subscribed to any forums");
else
while($a = mysql_fetch_assoc($q))
$s_tids[] = $a["topicid"];

unset($a); unset($q);
$q = mysql_query("SELECT f.name as forum,f.id as fid, t.id as tid, t.subject, t.lastpost as tlast , u.username as towner , u.id as towner_id, p.id as pid ,p.body, p.added, u2.username as last_poster, u2.id as last_poster_id ,u2.avatar as last_poster_avatar, r.lastpostread
FROM topics as t
LEFT JOIN forums as f ON t.forumid = f.id
LEFT JOIN posts as p ON t.lastpost = p.id
LEFT JOIN users as u ON t.userid = u.id
LEFT JOIN users as u2 ON p.userid = u2.id
LEFT JOIN readposts as r ON r.topicid = t.id AND r.userid = ".$uid."
WHERE t.id IN (".join(",",$s_tids).") ORDER BY t.id , p.added DESC ") or sqlerr(__FILE__, __LINE__);
while($a = mysql_fetch_assoc($q))
{
?>
<br/>
<table width="100%" cellpadding="5" cellspacing="2" style="border-collapse:separate;">
<tr>
<td align="left" colspan="2" class="colhead"><a href="forums.php?action=viewforum&amp;forumid=<?=$a["tid"]?>#p<?=$a["pid"]?>"><?=$a["subject"]?></a>&nbsp;<span class="small"><a href="<?=$_SERVER["SCRIPT_NAME"]?>?do=remove&amp;tid=<?=$a["tid"]?>">[unsubscribe]</a></span>&nbsp;<?=($a["tlast"] == $a["lastpostread"] ? "" : "(NEW)")?></td>
</tr>
<tr>
<td width="100%" align="left" valign="top" ><?=format_comment($a["body"])?></td>
<td nowrap="nowrap" valign="top" align="center"><img src="<?=($a["last_poster_avatar"] ? $a["last_poster_avatar"] : "pic/default_avatar.png")?>" width="80" title="<?=$a["last_poster"]?>'s avatar" alt=" "/><br/>last post <?=(get_elapsed_time(sql_timestamp_to_unix_timestamp($a["added"])))?> ago<br/>by <a href="userdetails.php?id=<?=$a["last_poster_id"]?>"><?=$a["last_poster"]?></a></td>
</tr>
<tr>
<td class="colhead small" colspan="2" width="100%">topic started by <a href="userdetails.php?id=<?=$a["towner_id"]?>"><?=$a["towner"]?></a> in forum <a href="forums.php?action=viewforum&amp;forumid=<?=$a["fid"]?>"><?=$a["forum"]?></a>
</td>
</tr>
</table>
<?php
}
end_frame();
end_main_frame();
stdfoot();
}
?>