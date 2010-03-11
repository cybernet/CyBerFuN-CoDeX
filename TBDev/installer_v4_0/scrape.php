<?php
require_once("include/bittorrent.php");
require_once("include/benc.php");

dbconn(false);

$info_hash = ((isset($_GET["info_hash"]) && strlen(bin2hex($_GET["info_hash"])) == 40) ? $_GET["info_hash"] : "");

if (empty($info_hash))
    die("Nothing for you here go play somewere else");

global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;
mysql_connect($mysql_host, $mysql_user, $mysql_pass);

mysql_select_db($mysql_db) or die('dbconn: mysql_select_db: ' + mysql_error());

$r = "d" . benc_str("files") . "d";

$fields = "info_hash, times_completed, seeders, leechers";

if (!isset($_GET["info_hash"]))
    $query = "SELECT $fields FROM torrents ORDER BY info_hash";
else
    $query = "SELECT $fields FROM torrents WHERE " . hash_where("info_hash", unesc($_GET["info_hash"]));

$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
    $r .= "20:" . hash_pad($row["info_hash"]) . "d" .
    benc_str("complete") . "i" . $row["seeders"] . "e" .
    benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
    benc_str("incomplete") . "i" . $row["leechers"] . "e" . "e";
}

$r .= "ee";

if ($_SERVER["HTTP_ACCEPT_ENCODING"] == "gzip") {
    header("Content-Type: text/plain");
    header("Content-Encoding: gzip");
    echo gzencode($r, 9, FORCE_GZIP);
} else {
    header("Content-Type: text/plain");
    echo $r;
}

?>