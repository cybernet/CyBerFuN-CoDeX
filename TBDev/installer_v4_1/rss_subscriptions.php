<?php
require "include/bittorrent.php";
require "include/bbcode_functions.php";
dbconn();

$key = isset($_GET["key"]) ? htmlspecialchars($_GET["key"]) : "";
if(empty($key))
exit("This is only for ".$SITENAME." members! if you are a member check the site for the correct link!");

$q = mysql_query("SELECT u.username,u.id as uid , s.topicid FROM users as u LEFT JOIN subscriptions as s ON s.userid = u.id WHERE u.passkey =".sqlesc($key));
if(mysql_num_rows($q) == 0)
exit("Are you sure your a member at ".$SITENAME. " ? ");
else
while($a = mysql_fetch_assoc($q))
{
$user = $a["username"];
$uid = $a["uid"];
$tids[] = $a["topicid"];
}
if(count($tids) == 0)
exit("Nothing here first subscribe to some topics!");
unset($q); unset($a);
print("<"."?");
?>
xml version="1.0" encoding="ISO-8859-1" <?="?".">"?>
<rss version="0.91">
<channel>
<title><?=$SITENAME?> - <?=$user?>'s subscriptions</title>
<link><?=$BASEURL?></link>
<description>site moto or whatever</description>
<language>en-usde</language>
<copyright>Copyright © <?=date("Y")?> <?=$SITENAME?></copyright>
<webMaster><?=$SITEEMAIL?></webMaster>
<image>
<title><?=$SITENAME?></title>
<url><?=$BASEURL?>/favicon.ico</url>
<link>
<?=$BASEURL?>
</link>
<width>16</width>
<height>16</height>
</image>

<?php
$q = sql_query("SELECT f.name as forum,f.id as fid, t.id as tid, t.subject, t.lastpost as tlast , u.username as towner , u.id as towner_id, p.id as pid ,p.body, p.added, u2.username as last_poster, u2.id as last_poster_id ,u2.avatar as last_poster_avatar, r.lastpostread
FROM topics as t
LEFT JOIN forums as f ON t.forumid = f.id
LEFT JOIN posts as p ON t.lastpost = p.id
LEFT JOIN users as u ON t.userid = u.id
LEFT JOIN users as u2 ON p.userid = u2.id
LEFT JOIN readposts as r ON r.topicid = t.id AND r.userid = ".$uid."
WHERE t.id IN (".join(",",$tids).") ORDER BY t.id , p.added DESC ");
while($a = mysql_fetch_assoc($q))
{
?>
<item>
<description>
<![CDATA[
<br/>
<table width="100%" cellpadding="5" border="1" cellspacing="0" style="border-collapse:collapse;">
<tr>
<td align="left" colspan="2" ><a href="forums.php?action=viewforum&amp;forumid=<?=$a["tid"]?>#p<?=$a["pid"]?>"><?=$a["subject"]?></a>&nbsp;<a href="<?=$_SERVER["SCRIPT_NAME"]?>?do=remove&amp;tid=<?=$a["tid"]?>">[unsubscribe]</a>&nbsp;<?=($a["tlast"] == $a["lastpostread"] ? "" : "(NEW)")?></td>
</tr>
<tr>
<td width="100%" align="left" valign="top" ><?=format_comment($a["body"])?></td>
<td nowrap="nowrap" valign="top" align="center"><img src="<?=($a["last_poster_avatar"] ? $a["last_poster_avatar"] : "pic/default_avatar.png")?>" width="80" title="<?=$a["last_poster"]?>'s avatar" alt=" "/><br/>last post <?=(get_elapsed_time(sql_timestamp_to_unix_timestamp($a["added"])))?> ago<br/>by <a href="userdetails.php?id=<?=$a["last_poster_id"]?>"><?=$a["last_poster"]?></a></td>
</tr>
<tr>
<td colspan="2" width="100%">topic started by <a href="userdetails.php?id=<?=$a["towner_id"]?>"><?=$a["towner"]?></a> in forum <a href="forums.php?action=viewforum&amp;forumid=<?=$a["fid"]?>"><?=$a["forum"]?></a>
</td>
</tr>
</table>
]]>
</description>
</item>
<?php

}
?>
</channel>
</rss>