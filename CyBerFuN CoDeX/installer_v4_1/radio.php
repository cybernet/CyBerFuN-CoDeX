<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
dbconn(false);
stdhead();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

begin_main_frame();

/************************************************************************/
/* ShoutCast Suite for TB Source */
/* v1.0 */
/* =========================== */
/* */
/* Copyright © 2005 by SLaTkiS */
/* http://www.noviteti.com */
/* */
/************************************************************************/

$index = 1;

global $bgcolor2;

/////////////////////////
// SECTION 1 :: CONFIG //
/////////////////////////

error_reporting (E_ALL ^ E_WARNING ^ E_NOTICE);
//SHOUTcast server settings, needed to get the XML output from the DNAS
$shout_server = "";
$shout_port = "";
$shout_password = "";

//Set this to whatever the bitrate you are streaming at is
$bitrate="128 kbps";

//Stream Name
$streamname="Scene-Base";

//////////////////////////////////////
// SECTION 2 :: CONNECT AND RECEIVE //
//////////////////////////////////////

$shout_socket = fsockopen ($shout_server, $shout_port, $errno, $errstr,30);

if (!$shout_socket)
{
print "<center>";
print "<img src=pic/off.gif>";
print "<b><font size=3><font color=yellow><img src=pic/off.gif><br /> Radio - Offline</b></font><br>";
print "Artist: <b> N/A</b><br>";
print "Song: <b>N/A</b><br>";
print "Bitrate: <b>N/A</b><br>";
print "Listeners: <b>N/A</b><br>";
print "Time: <b>".date ("h:i:s A")."</b><br>";
print "</center>";
}

else
{

$xml_load = "";

// Let's say hello
fputs ($shout_socket, "GET /admin.cgi?pass=".$shout_password."&mode=viewxml HTTP/1.1\nUser-Agent:Mozilla\n\n");

// Now get the XML

while (!feof($shout_socket)) {
$xml_load .= fgets ($shout_socket, 1000);
}

}

if ($shout_socket) {

//////////////////////////////
// SECTION 3 :: PERPARE XML //
//////////////////////////////

// For my own sanity, I'm getting OUT of XML here, replacing tags with [ ] brackets, so that
// <SONG> becomes [SONG], etc...

$xml_load = strtr ($xml_load, '<', '[');
$xml_load = strtr ($xml_load, '>', ']');

$tag_separated = explode ("]", $xml_load);

foreach ($tag_separated as $key => $value) {
$tag_separated[$key] = $value."]\n";
if (substr_count($value, "Content-Type")) {$tag_separated[$key] = "";}
}


//////////////////////////////
// SECTION 4 :: PARSING XML //
//////////////////////////////

// $titles array will hold the last 10 songs played
// Note that $titles[0] will give you the currently playing song
// -- the following are provided to let you know which stats are being grabbed by this script

$titles = array();
$currentlisteners=0;
$peaklisteners=0;
$maxlisteners=0;
$reportedlisteners=0;
$averagetime=0;
$servergenre="";
$serverurl="";
$servertitle="";

foreach ($tag_separated as $value) {
if (substr_count($value, "[/TITLE]")) {
$value = str_replace ("[/TITLE]","", $value);
array_push ($titles, $value);
}

if (substr_count ($value, "[/CURRENTLISTENERS]")) {
$value = str_replace ("[/CURRENTLISTENERS]","", $value);
$currentlisteners=$value;
}

if (substr_count ($value, "[/PEAKLISTENERS]")) {
$value = str_replace ("[/PEAKLISTENERS]","", $value);
$peaklisteners=$value;
}

if (substr_count ($value, "[/MAXLISTENERS]")) {
$value = str_replace("[/MAXLISTENERS]","", $value);
$maxlisteners=$value;
}

if (substr_count ($value, "[/REPORTEDLISTENERS]")) {
$value = str_replace("[/REPORTEDLISTENERS]","", $value);
$reportedlisteners=$value;
}

if (substr_count ($value, "[/AVERAGETIME]")) {
$value = str_replace("[/AVERAGETIME]","", $value);
$averagetime=$value;
$tmp=$averagetime / 60;
$averagesec=$averagetime % 60;
if ($averagesec < 10) {$averagesec = "0".$averagesec;}
$averagemin = sprintf ("%d",$tmp);
$averagehour = $averagemin / 60;
$averagemin = $averagemin % 60;
$averagehour = sprintf ("%d", $averagehour);
}

if (substr_count ($value, "[/SERVERGENRE]")) {
$value = str_replace("[/SERVERGENRE]","", $value);
$servergenre=$value;
}

if (substr_count ($value, "[/SERVERURL]")) {
$value = str_replace("[/SERVERURL]","", $value);
$serverurl=$value;
}

if (substr_count ($value, "[/SERVERTITLE]")) {
$value = str_replace("[/SERVERTITLE]","", $value);
$servertitle=$value;
if (substr_count ($servertitle, "N/A")) {$servertitle = "Radio is currently offline!";}
}

if (substr_count ($value, "[/STREAMHITS]")) {
$value = str_replace("[/STREAMHITS]","", $value);
$streamhits=$value;
}
}

// $nowplaying[0] = currently playing artist
// $nowplaying[1] = currently playing title
// Obviously, use of this requires that titles be named like so:
// Artist - Title
// If not, just use $titles[0] for the current song

$temp = $titles[0];
$nowplaying = explode (" - ",$temp);

//////////////////////////////////
// SECTION 5 :: OUTPUT THE PAGE //
//////////////////////////////////

//Show if on or off -added by bodhisattva//

$fp = fsockopen("$shout_server", $shout_port, &$errno, &$errstr, 30);
if(!$fp) {
$success=2;
}
if($success!=2){ //if connection
fputs($fp,"GET /7.html HTTP/1.0\r\nUser-Agent: XML Getter (Mozilla Compatible)\r\n\r\n");
while(!feof($fp)) {
$page .= fgets($fp, 1000);
}
fclose($fp);
$page = ereg_replace(".*<body>", "", $page); //extract data
$page = ereg_replace("</body>.*", ",", $page); //extract data
$numbers = explode(",",$page);
$currentlisteners=$numbers[0];
$connected=$numbers[1];
if($connected==1)
$wordconnected="yes";
else
$wordconnected="no";
}

if($success!=2 && $connected==1){

?>
<center>
<?
?>
<center><img src=pic/on.gif>
<?
?>
<br>
<p>
<p>
<?
?>
<center><a href="/listen.pls" onmouseover="roll_over('winamp', 'pic/winamp_over.png')"
onmouseout="roll_over('winamp', 'pic/winamp.png')" style="border:hidden;" ><img src="pic/winamp.png" name="winamp" alt="click here to listen with Winamp" style="border:hidden;"></a><a href="listen.asx" onmouseover="roll_over('wmp', 'pic/wmp_over.png')"
onmouseout="roll_over('wmp', 'pic/wmp.png')" style="border:hidden;" ><img src="pic/wmp.png" name="wmp" alt="click here to listen with Windows Media Player" style="border:hidden;"></a></center></span>
<?
}
else{
print "<center>";
print "<b><font size=3><font color=yellow> Radio - Offline</b></font><br>";
print "Artist: <b> N/A</b><br>";
print "Song: <b>N/A</b><br>";
print "Bitrate: <b>N/A</b><br>";
print "Listeners: <b>N/A</b><br>";
print "Time: <b>".date ("h:i:s A")."</b><br>";
print "</center>";
}
}
end_main_frame();
stdfoot();
?>