<?php
require_once("include/benc.php");
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
// ini_set("memory_limit","12M");
ini_set("upload_max_filesize", $max_torrent_size);
function bark($msg)
{
    genbark($msg, "Upload failed!");
}
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
/*
//== Anti flood - uncomment to use
if (get_user_class() < UC_POWER_USER)  
{
$minutes = 30;
  $limit = 2;
  $res = sql_query("SELECT COUNT(*) FROM torrents WHERE owner = $CURUSER[id] AND added > '".get_date_time(gmtime() - ($minutes * 60))."'") or sqlerr(__FILE__,__LINE__);
  $row = mysql_fetch_row($res);

  if ($row[0] > $limit)
    stderr("Flood", "You can't upload more than $limit torrents in $minutes minutes.");

}
*/
if ($CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no')  
    die;

if (!function_exists('is_valid_url')) {
    function is_valid_url($link)
    {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $link);
    }
}
foreach(explode(":", "descr:type:name") as $v) {
    if (!isset($_POST[$v]))
        bark("missing form data");
}

if (!isset($_FILES["file"]))
    bark("missing form data");

if (!empty($_POST['poster']))
    $poster = unesc($_POST['poster']);

if (!empty($_POST['url']))
    $url = unesc($_POST['url']);

$f = $_FILES["file"];
$fname = unesc($f["name"]);

if (!empty($_POST['tube']))
    $tube = unesc($_POST['tube']);

if (get_user_class() >= UC_ADMINISTRATOR)
    $multiplicator = 0 + $_POST["multiplicator"];
else
    $multiplicator = "0";

if (empty($fname))
    bark("Empty filename!");
if ($_POST['uplver'] == 'yes') {
    $anonymous = "yes";
    $anon = "Anonymous";
} else {
    $anonymous = "no";
    $anon = $CURUSER["username"];
}

if ($nforeq) {
    $nfofile = $_FILES['nfo'];
    if ($nfofile['name'] == '')
        bark("No NFO!");

    if ($nfofile['size'] == 0)
        bark("0-byte NFO");

    if ($nfofile['size'] > 65535)
        bark("NFO is too big! Max 65,535 bytes.");

    $nfofilename = $nfofile['tmp_name'];

    if (@!is_uploaded_file($nfofilename))
        bark("NFO upload failed");
} else {
    if ($nfofile['name'] != '') {
        if ($nfofile['size'] == 0)
            bark("0-byte NFO");
        if ($nfofile['size'] > 65535)
            bark("NFO is too big! Max 65,535 bytes.");

        $nfofilename = $nfofile['tmp_name'];

        if (@!is_uploaded_file($nfofilename))
            bark("NFO upload failed");
    }
}
//Auto nfo
$descr = unesc($_POST["descr"]);
if (!$descr && $nfofile['name'] == '')
    bark("You must enter a description or NFO!");
//strip
if ($_POST['strip'] == 'strip') {
    include 'include/strip.php';
    $descr = preg_replace("/[^\\x20-\\x7e\\x0a\\x0d]/", " ", $descr);
    strip($descr);
}

$scene = ($_POST["scene"] != "no" ? "yes" : "no");
$request = ($_POST["request"] != "no" ? "yes" : "no");
$catid = (0 + $_POST["type"]);
if (!is_valid_id($catid))
    bark("You must select a category to put the torrent in!");

$movie_cat = array(3,5,10,11); //add here your movie category
if (in_array($catid, $movie_cat))
{
$subs = isset($_POST['subs'])? implode(",", $_POST['subs']) : "" ;    
if(empty($subs))
bark ("You must select a subtile for your movie");
}

$minclass = isset($_POST["minclass"]) ? 0+$_POST["minclass"] : 255;

if (!validfilename($fname))
    bark("Invalid filename!");
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
    bark("Invalid filename (not a .torrent).");
$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"]))
    $torrent = unesc($_POST["name"]);

$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname))
    bark("eek");
if (!filesize($tmpname))
    bark("Empty file!");

if (isset($_POST["music"]))
    $genre = implode(",", $_POST['music']);
elseif (isset($_POST["movie"]))
    $genre = implode(",", $_POST['movie']);
elseif (isset($_POST["game"]))
    $genre = implode(",", $_POST['game']);
elseif (isset($_POST["apps"]))
    $genre = implode(",", $_POST['apps']);

$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict))
    bark("What the hell did you upload? This is not a bencoded file!");

function dict_check($d, $s)
{
    if ($d["type"] != "dictionary")
        bark("not a dictionary");
    $a = explode(":", $s);
    $dd = $d["value"];
    $ret = array();
    $t = '';
    foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
            $k = $m[1];
            $t = $m[2];
        }
        if (!isset($dd[$k]))
            bark("dictionary is missing key(s)");
        if (isset($t)) {
            if ($dd[$k]["type"] != $t)
                bark("invalid entry in dictionary");
            $ret[] = $dd[$k]["value"];
        } else
            $ret[] = $dd[$k];
    }
    return $ret;
}

function dict_get($d, $k, $t)
{
    if ($d["type"] != "dictionary")
        bark("not a dictionary");
    $dd = $d["value"];
    if (!isset($dd[$k]))
        return;
    $v = $dd[$k];
    if ($v["type"] != $t)
        bark("invalid dictionary entry type");
    return $v["value"];
}

list($ann, $info) = dict_check($dict, "announce(string):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

if (strlen($pieces) % 20 != 0)
    bark("invalid pieces");

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
    $filelist[] = array($dname, $totallen);
    $type = "single";
} else {
    $flist = dict_get($info, "files", "list");
    if (!isset($flist))
        bark("missing both length and files");
    if (!count($flist))
        bark("no files");
    $totallen = 0;
    foreach ($flist as $fn) {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;
        $ffa = array();
        foreach ($ff as $ffe) {
            if ($ffe["type"] != "string")
                bark("filename error");
            $ffa[] = $ffe["value"];
        }
        if (!count($ffa))
            bark("filename error");
        $ffe = implode("/", $ffa);
        $filelist[] = array($ffe, $ll);
    }
    $type = "multi";
}
$poster = unesc($_POST['poster']);
$recommended = unesc($_POST['recommended']);
$tube = unesc($_POST['tube']);
$url = unesc($_POST['url']);
$dict['value']['announce'] = bdec(benc_str($announce_urls[0])); // change announce url to local
$dict['value']['info']['value']['private'] = bdec('i1e'); // add private tracker flag
$dict['value']['info']['value']['source'] = bdec(benc_str("[$DEFAULTBASEURL] $SITENAME")); // add link for bitcomet users
unset($dict['value']['announce-list']); // remove multi-tracker capability
unset($dict['value']['nodes']); // remove cached peers (Bitcomet & Azareus)
$dict = bdec(benc($dict)); // double up on the becoding solves the occassional misgenerated infohash
$dict['value']['comment'] = bdec(benc_str("In using this torrent you are bound by the '$SITENAME' Confidentiality Agreement By Law")); // change torrent comment
list($ann, $info) = dict_check($dict, "announce(string):info");
unset($dict['value']['created by']); //Null the created_by field///
$infohash = pack("H*", sha1($info["string"]));
$uclass = $CURUSER["class"] ;
// Replace punctuation characters with spaces
$torrent = str_replace("_", " ", $torrent);
// /////////pretime////////
/*
$pre = getpre($torrent,1);
$timestamp = strtotime($pre);
$tid = time();
if (empty($pre)) {
$predif = "N/A";
}else{
$predif = ago($tid - $timestamp);
}
*/
//=== hidden torrents
if ($CURUSER["hiddentorrents"] == "yes" || get_user_class() >= UC_MODERATOR)
$hidden = unesc($_POST["hidden"]);
else
$hidden = "no";
//===  staff torrents
if (get_user_class() >= UC_MODERATOR)
$staffonly = unesc($_POST["staffonly"]);
else
$staffonly = "no";
//=== free download?
if (get_user_class() >= UC_MODERATOR)
$countstats = unesc($_POST["countstats"]);
else
$countstats = "yes";
//===end
// === allow comments?
if (get_user_class() >= UC_MODERATOR && get_user_class() <= UC_CODER)
    $allow_comments = unesc($_POST['allow_comments']);
else
    $allow_comments = "yes";
// ===end
$nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
$smalldescr = $_POST["description"];
//$ret = sql_query("INSERT INTO torrents (search_text, filename, owner, visible, tube, multiplicator, uclass, anonymous, request, scene, info_hash, name, size, numfiles, url, poster, hidden, staffonly, countstats, half, newgenre, type, vip, allow_comments, subs, descr, ori_descr, description, category, minclass, save_as, added, last_action, nfo, afterpre) VALUES (" .implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $tube, $multiplicator, $uclass, $anonymous, $request, $scene, $infohash, $torrent, $totallen, count($filelist), $url, $poster, $hidden, $staffonly, $countstats, $half, $genre, $type, $vip, $allow_comments, $subs, $descr, $descr, $smalldescr, 0 + $_POST["type"], $minclass, $dname))) . ", '" . get_date_time() . "', '" . get_date_time() . "', $nfo, '" . $predif . "')");  // or sqlerr(__FILE__, __LINE__);
// == uncomment above to enable doopies pre times on browse
$ret = sql_query("INSERT INTO torrents (search_text, filename, owner, visible, tube, multiplicator, uclass, anonymous, request, scene, info_hash, name, size, numfiles, url, poster, hidden, staffonly, countstats, half, newgenre, type, vip, allow_comments, subs, descr, ori_descr, description, category, minclass, save_as, added, last_action, nfo) VALUES (" . implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $tube, $multiplicator, $uclass, $anonymous, $request, $scene, $infohash, $torrent, $totallen, count($filelist), $url, $poster, $hidden, $staffonly, $countstats, $half, $genre, $type, $vip, $allow_comments, $subs, $descr, $descr, $smalldescr, 0 + $_POST["type"], $minclass, $dname))) . ", '" . get_date_time() . "', '" . get_date_time() . "', $nfo)") or sqlerr(__FILE__, __LINE__);

if (!$ret) {
    if (mysql_errno() == 1062)
        bark("torrent already uploaded!");
    bark("mysql puked: " . mysql_error());
}
$id = mysql_insert_id();

if ($CURUSER["anonymous"] == 'yes')
    $message = "New Torrent : [url=$DEFAULTBASEURL/details.php?id=$id] " . safeChar($torrent) . "[/url] Uploaded - Anonymous User";
else
    $message = "New Torrent : [url=$DEFAULTBASEURL/details.php?id=$id] " . safeChar($torrent) . "[/url] Uploaded by " . safechar($CURUSER["username"]) . "";

@sql_query("DELETE FROM files WHERE torrent = $id");

function file_list($arr,$id)
{
    foreach($arr as $v)
        $new[] = "($id,".sqlesc($v[0]).",".$v[1].")";
    return join(",",$new);
}
mysql_query("INSERT INTO files (torrent, filename, size) VALUES ".file_list($filelist,$id));

$fp = fopen("$torrent_dir/$id.torrent", "w");
if ($fp) {
    @fwrite($fp, benc($dict), strlen(benc($dict)));
    fclose($fp);
}
// ===add karma
sql_query("UPDATE users SET seedbonus = seedbonus+15.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
// ===end
$fstaff = 3;
if($minclass != $fstaff){
write_log("torrentupload", "Torrent $id ($torrent) was uploaded by $CURUSER[username]");
//===end
// //////new torrent upload detail sent to shoutbox//////////
autoshout($message);
// ///////////////////////////end///////////////////////////////////
}
/* RSS feeds */

if (($fd1 = @fopen("rss.xml", "w")) && ($fd2 = fopen("rssdd.xml", "w"))) {
    $cats = "";
    $res = sql_query("SELECT id, name FROM categories");
    while ($arr = mysql_fetch_assoc($res))
    $cats[$arr["id"]] = $arr["name"];
    $s = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"0.91\">\n<channel>\n" . "<title>TorrentBits</title>\n<description>0-week torrents</description>\n<link>$DEFAULTBASEURL/</link>\n";
    @fwrite($fd1, $s);
    @fwrite($fd2, $s);
    $r = sql_query("SELECT id,name,descr,filename,category FROM torrents ORDER BY added DESC LIMIT 15") or sqlerr(__FILE__, __LINE__);
    while ($a = mysql_fetch_assoc($r)) {
        $cat = $cats[$a["category"]];
        $s = "<item>\n<title>" . safechar($a["name"] . " ($cat)") . "</title>\n" . "<description>" . safechar($a["descr"]) . "</description>\n";
        @fwrite($fd1, $s);
        @fwrite($fd2, $s);
        @fwrite($fd1, "<link>$DEFAULTBASEURL/details.php?id=$a[id]&amp;hit=1</link>\n</item>\n");
        $filename = safechar($a["filename"]);
        @fwrite($fd2, "<link>$DEFAULTBASEURL/download.php/$a[id]/$filename</link>\n</item>\n");
    }
    $s = "</channel>\n</rss>\n";
    @fwrite($fd1, $s);
    @fwrite($fd2, $s);
    @fclose($fd1);
    @fclose($fd2);
}

/* Email notifs */
/**
* $res = sql_query("SELECT name FROM categories WHERE id=$catid") or sqlerr();
* $arr = mysql_fetch_assoc($res);
* $cat = $arr["name"];
* $res = sql_query("SELECT email FROM users WHERE enabled='yes' AND notifs LIKE '%[cat$catid]%'") or sqlerr();
* $uploader = $CURUSER['username'];
*
* $size = mksize($totallen);
* $description = ($html ? strip_tags($descr) : $descr);
*
* $body = <<<EOD
* A new torrent has been uploaded.
*
* Name: $torrent
* Size: $size
* Category: $cat
* Uploaded by: $uploader
*
* Description
* -------------------------------------------------------------------------------
* $description
* -------------------------------------------------------------------------------
*
* You can use the URL below to download the torrent (you may have to login).
*
* $DEFAULTBASEURL/details.php?id=$id&hit=1
*
* --
* $SITENAME
* EOD;
* $to = "";
* $nmax = 100; // Max recipients per message
* $nthis = 0;
* $ntotal = 0;
* $total = mysql_num_rows($res);
* while ($arr = mysql_fetch_row($res))
* {
* if ($nthis == 0)
* $to = $arr[0];
* else
* $to .= "," . $arr[0];
* ++$nthis;
* ++$ntotal;
* if ($nthis == $nmax || $ntotal == $total)
* {
* if (!mail("Multiple recipients <$SITEEMAIL>", "New torrent - $torrent", $body,
* "From: $SITEEMAIL\r\nBcc: $to", "-f$SITEEMAIL"))
* stderr("Error", "Your torrent has been been uploaded. DO NOT RELOAD THE PAGE!\n" .
* "There was however a problem delivering the e-mail notifcations.\n" .
* "Please let an administrator know about this error!\n");
* $nthis = 0;
* }
* }
*/
header("Location: $BASEURL/details.php?id=$id&uploaded=1");

?>