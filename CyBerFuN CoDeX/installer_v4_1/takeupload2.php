<?php
require_once("include/benc.php");
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
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

if ($usergroups['canupload'] == 'no' OR $usergroups['canupload'] != 'yes' OR $CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no')
    die;

foreach(explode(":", "descr:type:name") as $v) {
    if (!isset($_POST[$v]))
        stderr("Error", "missing form data.");
}

if (!isset($_FILES['file']))
    stderr("Error", "missing form data.");

$f = $_FILES['file'];
$fname = unesc($f['name']);
if (empty($fname))
    stderr("Error", "Empty filename!");

$ft = 0 + $_POST['filetype'];
if ($ft == 1) {
    $artist = unesc($_POST['artist']);
    if (!$artist)
        stderr("Error", "You must enter the artist!");
    $album = unesc($_POST['album']);
    if (!$album)
        stderr("Error", "You must enter the album!");
    $year = 0 + $_POST['year'];
    if (strlen($_POST['year']) != 4)
        stderr("Error", "Year must be 4 digits!");
    if (!is_numeric($_POST['year']))
        stderr("Error", "Year must be numeric");
    $format = unesc($_POST['format']);
    if (!$_POST['format'])
        stderr("Error", "You must enter the format!");
    if (strlen($format) > 10)
        stderr("Error", "Format too long!");
    $bitrate = 0 + $_POST['bitrate'];
    if (!$_POST['bitrate'])
        stderr("Error", "You must enter the bitrate!");
    if ($bitrate < 160)
        stderr("Error", "Bitrate must be greater than 160 kbps!");
    if (!is_numeric($bitrate))
        stderr("Error", "Bitrate must be numeric");
    if (strlen($bitrate) > 10)
        stderr("Error", "Bitrate too long!");
    $filename = $artist . "-" . $album . "[" . $year . "/" . $format . "/" . $bitrate . "]";
    if (!validfilename($fname))
        stderr("Error", "Invalid filename!");
    if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
        stderr("Error", "Invalid filename (not a .torrent).");
    $shortfname = $torrent = $matches[1];
    $torrent = unesc($filename);

    $nfofile = $_FILES['nfo2'];
    if ($nfofile['name'] == '')
        stderr("Error", "No NFO!");

    if ($nfofile['size'] == 0)
        stderr("Error", "0-byte NFO");

    if ($nfofile['size'] > 65535)
        stderr("Error", "NFO is too big! Max 65,535 bytes.");

    $nfofilename = $nfofile['tmp_name'];

    if (@!is_uploaded_file($nfofilename))
        stderr("Error", "NFO upload failed");
} elseif ($ft == 2) {
    $nfofile = $_FILES['nfo'];

    if ($nfofile['size'] > 65535)
        stderr("Error", "NFO is too big! Max 65,535 bytes.");

    $nfofilename = $nfofile['tmp_name'];

    if (!validfilename($fname))
        stderr("Error", "Invalid filename!");
    if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
        stderr("Error", "Invalid filename (not a .torrent).");
    $shortfname = $torrent = $matches[1];
    if (!empty($_POST["name"]))
        $torrent = unesc($_POST['name']);
} else
    stderr("Error", "Select filetype");

$descr = unesc($_POST['descr']);
if (!$descr)
    stderr("Error", "You must enter a description!");

if ($_POST['strip'] == 'strip') {
    include 'include/strip.php';
    $descr = preg_replace("/[^\\x20-\\x7e\\x0a\\x0d]/", " ", $descr);
    strip($descr);
}

$catid = (0 + $_POST['type']);
if (!is_valid_id($catid))
    stderr("Error", "You must select a category to put the torrent in!");

$tmpname = $f['tmp_name'];
if (!is_uploaded_file($tmpname))
    stderr("Error", "eek");
if (!filesize($tmpname))
    stderr("Error", "Empty file!");

$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict))
    stderr("Error", "What the hell did you upload? This is not a bencoded file!");

function dict_check($d, $s)
{
    if ($d["type"] != "dictionary")
        stderr("Error", "not a dictionary");
    $a = explode(":", $s);
    $dd = $d["value"];
    $ret = array();
    foreach ($a as $k) {
        unset($t);
        if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
            $k = $m[1];
            $t = $m[2];
        }
        if (!isset($dd[$k]))
            stderr("Error", "dictionary is missing key(s)");
        if (isset($t)) {
            if ($dd[$k]["type"] != $t)
                stderr("Error", "invalid entry in dictionary");
            $ret[] = $dd[$k]["value"];
        } else
            $ret[] = $dd[$k];
    }
    return $ret;
}

function dict_get($d, $k, $t)
{
    if ($d["type"] != "dictionary")
        stderr("Error", "not a dictionary");
    $dd = $d["value"];
    if (!isset($dd[$k]))
        return;
    $v = $dd[$k];
    if ($v["type"] != $t)
        stderr("Error", "invalid dictionary entry type");
    return $v["value"];
}

list($ann, $info) = dict_check($dict, "announce(string):info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");
// $passkey=$announce_urls[0].'?passkey='.$CURUSER['passkey'];
// $passkey=$announce_urls[0];
// if ($passkey != $ann)
// stderr("Error", "invalid announce url! must be <b>" . $passkey . "</b>");
if (strlen($pieces) % 20 != 0)
    stderr("Error", "invalid pieces");

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
    $filelist[] = array($dname, $totallen);
    $type = "single";
} else {
    $flist = dict_get($info, "files", "list");
    if (!isset($flist))
        stderr("Error", "missing both length and files");
    if (!count($flist))
        stderr("Error", "no files");
    $totallen = 0;
    foreach ($flist as $fn) {
        list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
        $totallen += $ll;
        $ffa = array();
        foreach ($ff as $ffe) {
            if ($ffe["type"] != "string")
                stderr("Error", "filename error");
            $ffa[] = $ffe["value"];
        }
        if (!count($ffa))
            stderr("Error", "filename error");
        $ffe = implode("/", $ffa);
        $filelist[] = array($ffe, $ll);
    }
    $type = "multi";
}
$dict['value']['announce'] = bdec(benc_str($announce_urls[0])); // change announce url to local
$dict['value']['info']['value']['private'] = bdec('i1e'); // add private tracker flag
$dict['value']['info']['value']['source'] = bdec(benc_str("[$DEFAULTBASEURL] $SITENAME")); // add link for bitcomet users
unset($dict['value']['announce-list']); // remove multi-tracker capability
unset($dict['value']['nodes']); // remove cached peers (Bitcomet & Azareus)
$dict = bdec(benc($dict)); // double up on the becoding solves the occassional misgenerated infohash
$dict['value']['comment'] = bdec(benc_str("In using this torrent you are bound by the '$SITENAME' Confidentiality Agreement By Law")); // change torrent comment
list($ann, $info) = dict_check($dict, "announce(string):info");
unset($dict['value']['created by']);
$infohash = pack("H*", sha1($info["string"]));
// Replace punctuation characters with spaces
$torrent = str_replace("_", " ", $torrent);

$nfo = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename)));
$ret = mysql_query("INSERT INTO torrents (search_text, filename, owner, visible, anonymous, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo) VALUES (" .
    implode(",", array_map("sqlesc", array(searchfield("$shortfname $dname $torrent"), $fname, $CURUSER["id"], "no", $anonymous, $infohash, $torrent, $totallen, count($filelist), $type, $descr, $descr, 0 + $_POST["type"], $dname))) . ", '" . get_date_time() . "', '" . get_date_time() . "', $nfo)");
// //////new torrent upload detail sent to shoutbox//////////
if ($CURUSER["anonymous"] == 'yes')
    $message = "New Torrent : ($torrent) Uploaded - Anonymous User";
else
    $message = "New Torrent : ($torrent) Uploaded by " . safechar($CURUSER["username"]) . "";
// ///////////////////////////END///////////////////////////////////
if (!$ret) {
    if (mysql_errno() == 1062)
        stderr("Error", "torrent already uploaded!");
    stderr("Error", "mysql puked!");
}
$id = mysql_insert_id();

@mysql_query("DELETE FROM files WHERE torrent = $id");

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
mysql_query("UPDATE users SET seedbonus = seedbonus+15.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
// ///writelog/////////
if ($CURUSER["anonymous"] == 'yes')
    write_log("Torrent $id ($torrent) was uploaded by Anonymous");
else
    write_log("Torrent $id ($torrent) was uploaded by $CURUSER[username]");
// //////new torrent upload detail sent to shoutbox//////////
autoshout($message);
// ///////////////////////////end///////////////////////////////////
// ===notify people who voted on offer total credit to S4NE
if (isset($_POST['offer'])) {
    if ($_POST['offer'] > 0) {
        $res = mysql_query("SELECT `userid` FROM `offervotes` WHERE `offerid` = " . ($_POST['offer'] + 0)) or sqlerr(__FILE__, __LINE__);
        $pn_msg = "The Offer you voted for: \"$torrent\" was uploaded by " . $CURUSER["username"] . ".\nYou can Download the Torrent here";
        while ($row = mysql_fetch_assoc($res)) {
            // ===use this line if you DO HAVE subject in your PM system
            mysql_query("INSERT INTO messages (poster, sender, subject, receiver, added, msg) VALUES(0, 0, 'Offer $torrent was just uploaded', $row[userid], '" . get_date_time() . "', " . sqlesc($pn_msg) . ")") or sqlerr(__FILE__, __LINE__);
            // ===use this line if you DO NOT HAVE subject in your PM system
            // mysql_query("INSERT INTO messages (poster, sender, receiver, added, msg) VALUES(0, 0, $row[userid], '" . get_date_time() . "', " . sqlesc($pn_msg) . ")") or sqlerr(__FILE__, __LINE__);
        }
        // === delete all offer stuff
        @mysql_query("DELETE FROM `offers` WHERE `id` = " . ($_POST['offer'] + 0));
        @mysql_query("DELETE FROM `offervotes` WHERE `offerid` = " . ($_POST['offer'] + 0));
        @mysql_query("DELETE FROM `comments` WHERE `offer` = " . ($_POST['offer'] + 0) . "");
    }
}
// === end notify people who voted on offer
$res = mysql_query("SELECT name FROM categories WHERE id=$catid") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res);
$cat = $arr["name"];
$res = mysql_query("SELECT email FROM users WHERE enabled='yes' AND notifs LIKE '%[cat$catid]%' AND notifs LIKE '%[email]%'") or sqlerr(__FILE__, __LINE__);
$uploader = $CURUSER['username'];

$size = prefixed($totallen);
$description = ($html ? strip_tags($descr) : $descr);

$body = <<<EOD
A new torrent has been uploaded.

Name: $torrent
Size: $size
Category: $cat

Description
-------------------------------------------------------------------------------
$description
-------------------------------------------------------------------------------



You can use the URL below to download the torrent (you may have to login).

$DEFAULTBASEURL/details.php?id=$id&hit=1

--
$SITENAME
EOD;
$to = "";
$nmax = 100; // Max recipients per message
$nthis = 0;
$ntotal = 0;
$total = mysql_num_rows($res);
while ($arr = mysql_fetch_row($res)) {
    if ($nthis == 0)
        $to = $arr[0];
    else
        $to .= "," . $arr[0];
    ++$nthis;
    ++$ntotal;
    if ($nthis == $nmax || $ntotal == $total) {
        if (!mail("Multiple recipients <$SITEEMAIL>", "New torrent - $torrent", $body,
                "From: $SITEEMAIL\r\nBcc: $to", "-f$SITEEMAIL"))
            stderr("Error", "Your torrent has been been uploaded. DO NOT RELOAD THE PAGE!\n" . "There was however a problem delivering the e-mail notifcations.\n" . "Please let an administrator know about this error!\n");
        $nthis = 0;
    }
}
header("Location: $BASEURL/details.php?id=$id&uploaded=1");
die();

?>