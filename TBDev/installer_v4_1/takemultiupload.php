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
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if ($usergroups['canupload'] == 'no' OR $usergroups['canupload'] != 'yes' OR $CURUSER["class"] < $upclass || $CURUSER["uploadpos"] == 'no')
    die;

$nfofilename = array();
$matches = array();
$fname = array();

if (!isset($_FILES["file1"]) && !isset($_FILES["file2"]) && !isset($_FILES["file3"]) && !isset($_FILES["file4"]) && !isset($_FILES["file5"])) {
    bark("You didn't specify any filename!");
} else {
    $f1 = $_FILES["file1"];
    $nfofile1 = $_FILES['nfo1'];
    $fname[] = unesc($f1["name"]);
    if ($nfofile1['size'] > 65535)
        bark("No NFO! for #1 torrent OR NFO #1 is too big! Max 65,535 bytes.");

    $f2 = $_FILES["file2"];
    $nfofile2 = $_FILES['nfo2'];
    $fname[] = unesc($f2["name"]);
    if ($nfofile2['size'] > 65535)
        bark("No NFO! for #2 torrent OR NFO #2 is too big! Max 65,535 bytes.");

    $f3 = $_FILES["file3"];
    $nfofile3 = $_FILES['nfo3'];
    $fname[] = unesc($f3["name"]);
    if ($nfofile3['size'] > 65535)
        bark("No NFO! for #3 torrent OR NFO #3 is too big! Max 65,535 bytes.");

    $f4 = $_FILES["file4"];
    $nfofile4 = $_FILES['nfo4'];
    $fname[] = unesc($f4["name"]);
    if ($nfofile4['size'] > 65535)
        bark("No NFO! for #4 torrent OR NFO #4 is too big! Max 65,535 bytes.");

    $f5 = $_FILES["file5"];
    $nfofile5 = $_FILES['nfo5'];
    $fname[] = unesc($f5["name"]);
    if ($nfofile5['size'] > 65535)
        bark("No NFO! #5 torrent OR NFO #5 is too big! Max 65,535 bytes.");
    // some crucial checks
    if (!validfilename($fname[0]) || !validfilename($fname[1]) || !validfilename($fname[2]) || !validfilename($fname[3]) || !validfilename($fname[4]))
        bark("One of the filenames was invalid!");

    if (!preg_match('/^(.+)\.torrent$/si', $fname[0], $matches[0]))
        bark("Invalid filename 1(not a .torrent).");
    if (!preg_match('/^(.+)\.torrent$/si', $fname[1], $matches[1]))
        bark("Invalid filename 2(not a .torrent).");
    if (!preg_match('/^(.+)\.torrent$/si', $fname[2], $matches[2]))
        bark("Invalid filename 3(not a .torrent).");
    if (!preg_match('/^(.+)\.torrent$/si', $fname[3], $matches[3]))
        bark("Invalid filename 4(not a .torrent).");
    if (!preg_match('/^(.+)\.torrent$/si', $fname[4], $matches[4]))
        bark("Invalid filename 5(not a .torrent).");
    // very important check in terms of security
    if ($nfofile1['name'] != '')
        $nfofilename[] = $nfofile1['tmp_name'];
    if (@!is_uploaded_file($nfofilename[0]))
        bark("NFO1 upload failed " . $nfofilename[0]);
    if ($nfofile2['name'] != '')
        $nfofilename[] = $nfofile2['tmp_name'];
    if (@!is_uploaded_file($nfofilename[1]))
        bark("NFO2 upload failed");
    if ($nfofile3['name'] != '')
        $nfofilename[] = $nfofile3['tmp_name'];
    if (@!is_uploaded_file($nfofilename[2]))
        bark("NFO3 upload failed");
    if ($nfofile4['name'] != '')
        $nfofilename[] = $nfofile4['tmp_name'];
    if (@!is_uploaded_file($nfofilename[3]))
        bark("NFO4 upload failed");
    if ($nfofile5['name'] != '')
        $nfofilename[] = $nfofile5['tmp_name'];
    if (@!is_uploaded_file($nfofilename[4]))
        bark("NFO5 upload failed");
}

$descr = unesc($_POST["description"]);

if (!$descr) {
    bark("Please select either 'Take description from its respective NFO' OR enter a custom description to go with all torrents'");
}

$cat = array();

$catid = (0 + $_POST["alltype"]);
if (!is_valid_id($catid))
    bark("You must select a category to put ALL the torrent in!");
// use the posted type category first -- if not set then just apply from settings
if (isset($_POST["type1"])) {
    $cat[0] = 0 + $_POST["type1"];
    if (!is_valid_id($cat[0]))
        $cat[0] = 0 + $_POST["alltype"];
}
if (isset($_POST["type2"])) {
    $cat[1] = 0 + $_POST["type2"];
    if (!is_valid_id($cat[1]))
        $cat[1] = 0 + $_POST["alltype"];
}
if (isset($_POST["type3"])) {
    $cat[2] = 0 + $_POST["type3"];
    if (!is_valid_id($cat[2]))
        $cat[2] = 0 + $_POST["alltype"];
}

if (isset($_POST["type4"])) {
    $cat[3] = 0 + $_POST["type4"];
    if (!is_valid_id($cat[3]))
        $cat[3] = 0 + $_POST["alltype"];
}
if (isset($_POST["type5"])) {
    $cat[4] = 0 + $_POST["type5"];
    if (!is_valid_id($cat[4]))
        $cat[4] = 0 + $_POST["alltype"];
}
// using arrays is better you will soon find out why!
$shortname = array();
$tmpname = array();
$dict = array();
// more required arrays
$ann = array();
$info = array();
$dbname = array();
$plen = array();
$pieces = array();

$filelist = array();
$totallen = array();
$infohash = array();
$torrent = array();
$nfo = array();
$ids = array();

$tmpname[] = $f1["tmp_name"];
$tmpname[] = $f2["tmp_name"];
$tmpname[] = $f3["tmp_name"];
$tmpname[] = $f4["tmp_name"];
$tmpname[] = $f5["tmp_name"];

$i = 0; // i miss my normal for loops

// this is why!
foreach($tmpname as $value) {
    $shortfname[$i] = $torrent[$i] = $matches[$i];

    if (!is_uploaded_file($value))
        bark("Bad filename found on file no #$i");
    if (!filesize($value))
        bark("Empty file! $value");

    $dict[] = bdec_file($value, $max_torrent_size);
    if (!isset($dict[$i]))
        bark("What the hell did you upload? This is not a bencoded file 1!");
    // I think i can just use $ann[] $dbname[] $info[] and $plen[] without specifying the index $i -- but this should work too
    list($ann[$i], $info[$i]) = dict_check($dict[$i], "announce(string):info");
    list($dname[$i], $plen[$i], $pieces[$i]) = dict_check($info[$i], "name(string):piece length(integer):pieces(string)");
    if (!in_array($ann[$i], $announce_urls, 1))
        bark("invalid announce url! in file no #$i must be " . $announce_urls[0] . " - Make sure its exactly like that even the port number should be in there like '80'");
    if (strlen($pieces[$i]) % 20 != 0)
        bark("invalid pieces in file $i");
    $totallen = dict_get($info[$i], "length", "integer");
    if (isset($totallen)) {
        $filelist[] = array($dname[$i], $totallen);
        $type = "single";
    } else {
        $flist = dict_get($info[$i], "files", "list");
        if (!isset($flist)) {
            bark("missing both length and files in #$i torrent");
        }
        if (!count($flist)) {
            bark("Missing files in torrent #$i");
        }
        $totallen = 0;

        foreach ($flist as $fn) {
            list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
            $totallen += $ll;
            $ffa = array();
            foreach ($ff as $ffe) {
                if ($ffe["type"] != "string")
                    bark("filename error on torrent #$i");
                $ffa[] = $ffe["value"];
            }
            if (!count($ffa))
                bark("filename error");
            $ffe = implode("/", $ffa);
            $filelist[] = array($ffe, $ll);
        }

        $type = "multi";
    }

    /* Private Tracker mod code goes below */
    $info[$i]['value']['source']['type'] = "string";
    $info[$i]['value']['source']['value'] = $SITENAME;
    $info[$i]['value']['source']['strlen'] = strlen($info[$i]['value']['source']['value']);
    $info[$i]['value']['private']['type'] = "integer";
    $info[$i]['value']['private']['value'] = 1;
    $dict[$i]['value']['info'] = $info[$i];
    $dict[$i] = benc($dict[$i]);
    $dict[$i] = bdec($dict[$i]);

    list($ann[$i], $info[$i]) = dict_check($dict[$i], "announce(string):info");
    unset($dict['value']['created by']);
    $infohash[$i] = pack("H*", sha1($info[$i]["string"]));
    /* ...... end of Private Tracker mod */

    $torrent[$i] = str_replace("_", " ", $torrent[$i]);
    $torrent[$i] = str_replace("'", " ", $torrent[$i]);
    $torrent[$i] = str_replace("\"", " ", $torrent[$i]);
    $torrent[$i] = str_replace(",", " ", $torrent[$i]);
    $nfo[$i] = sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename[$i])));

    $first = $shortfname[$i][1];
    $second = $dname[$i];
    $third = $torrent[$i][1];

    $ret = mysql_query("INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo) VALUES (" . implode(",", array_map("sqlesc", array(searchfield("$first $second $third"), $fname[$i], $CURUSER["id"], "no", $infohash[$i], $torrent[$i][1], $totallen, count($filelist[$i]), $type, $descr, $descr, $cat[$i], $dname[$i]))) . ", '" . get_date_time() . "', '" . get_date_time() . "', $nfo[$i])");
    // //////new torrent upload detail sent to shoutbox//////////
    if ($CURUSER["anonymous"] == 'yes')
        $message = "[url=$BASEURL/multidetails.php?id1=$ids[0]&id2=$ids[1]&id3=$ids[2]&id4=$ids[3]&id5=$ids[4]]Multiple Torrents were just uploaded! Click here to see them[/url] - Anonymous User";
    else
        $message = "[url=$BASEURL/multidetails.php?id1=$ids[0]&id2=$ids[1]&id3=$ids[2]&id4=$ids[3]&id5=$ids[4]]Multiple Torrents were just uploaded! Click here to see them[/url]  Uploaded by " . safechar($CURUSER["username"]) . "";
    // ///////////////////////////END///////////////////////////////////
    if (!$ret) {
        if (mysql_errno() == 1062)
            bark("#$i torrent was already uploaded!");
        bark("mysql puked: " . mysql_error());
    }

    $id = mysql_insert_id();
    $ids[] = $id;
    @mysql_query("DELETE FROM files WHERE torrent = $id");
    foreach ($filelist as $file) {
        @mysql_query("INSERT INTO files (torrent, filename, size) VALUES ($id, " . sqlesc($file[0]) . "," . $file[1] . ")");
    }
    $fp = fopen("$torrent_dir/$id.torrent", "w");
    if ($fp) {
        @fwrite($fp, benc($dict[$i]), strlen(benc($dict[$i])));
        fclose($fp);
    }
    // ===add karma
    mysql_query("UPDATE users SET seedbonus = seedbonus+75.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
    // ===end
    // //////new torrent upload detail sent to shoutbox//////////
    autoshout($message);
    // ///////////////////////////end///////////////////////////////////
    $i++;
}

function dict_check($d, $s)
{
    // echo $d["type"];
    // print_r($d);
    if ($d["type"] != "dictionary")
        bark("not a dictionary");

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
header("Location: $BASEURL/multidetails.php?id1=$ids[0]&id2=$ids[1]&id3=$ids[2]&id4=$ids[3]&id5=$ids[4]&uploaded=1");

?>