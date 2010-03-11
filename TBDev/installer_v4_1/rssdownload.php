<?php
require_once("include/bittorrent.php");
dbconn(false);
maxcoder();
$url = $_SERVER['REQUEST_URI'];
$url_array=explode("/",$url);
$passkey= $url_array[3];
//no point looking up sql for no info
if($passkey == "")
{
print("No PassKey:YOU MUST BE A USER OF YOURSITE TO USE THIS FEED"); exit(); }
//sql
$userpaa = mysql_query("SELECT * FROM users WHERE passkey =".sqlesc($passkey)."") or die(mysql_error());
$userpaa = mysql_fetch_assoc($userpaa);
if ($userpaa["passkey"] != $passkey){
print("The rss feeds are for members only No User Id Found"); exit();
}
function bark($msg) {
stdhead();
stdmsg("Download failed!", $msg);
stdfoot();
exit;
}
if (!preg_match(':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches))
httperr();
$id = 0 + $matches[1];
if (!$id)
httperr();
$res = mysql_query("SELECT name FROM torrents WHERE id =".sqlesc($id)."") or sqlerr(__FILE__, __LINE__);
$row = mysql_fetch_assoc($res);
$fn = "$torrent_dir/$id.torrent";
if (!$row || !is_file($fn) || !is_readable($fn))
httperr();
mysql_query("UPDATE torrents SET hits = hits + 1 WHERE id = $id");
$added=sqlesc(get_date_time());
$res2 = mysql_query("SELECT name, category FROM torrents WHERE id=".sqlesc($id)."") or sqlerr(__FILE__, __LINE__);
$arr2 = mysql_fetch_assoc($res2);
$name = sqlesc($arr2["name"]);
$cat = sqlesc($arr2["category"]);
require_once "include/benc.php";
if (strlen($userpaa['passkey']) != 32) {
$userpaa['passkey'] = md5($userpaa['username'].get_date_time().$userpaa['passhash']);
mysql_query("UPDATE users SET passkey='$userpaa[passkey]' WHERE id=$userpaa[id]");
}
$dict = bdec_file($fn, (1024*1024));
$dict['value']['announce']['value'] = "$BASEURL/announce.php?passkey=$userpaa[passkey]";
$dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
$dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);
header('Content-Disposition: attachment');
header("Content-Type: application/x-bittorrent");
print(benc($dict));
?>