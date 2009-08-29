<?php
if (!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
// //////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="300" />
<meta http-equiv="cache-control" content="private" />
<meta name="robots" content="noindex, nofollow, noarchive" />
<script type="text/javascript">
<!--
function Post()
{
document.compose.action = "?action=post"
document.compose.target = "";
document.compose.submit();
return true;
}
function themes() {
    window.open('take_theme.php','My themes','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
}
// -->
</script>
<script type="text/javascript">
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=740,height=380,left = 340,top = 280');");
}
</script>
<script type="text/javascript">


/***********************************************
* Dynamic Ajax Content- A,© Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var bustcachevar=1 //bust potential caching of external pages after initial request? (1=yes, 0=no)
var loadedobjects=""
var rootdomain="http://"+window.location.hostname
var bustcacheparameter=""

function ajaxpage(url, containerid){
var page_request = false
if (window.XMLHttpRequest) // if Mozilla, Safari etc
page_request = new XMLHttpRequest()
else if (window.ActiveXObject){ // if IE
try {
page_request = new ActiveXObject("Msxml2.XMLHTTP")
}
catch (e){
try{
page_request = new ActiveXObject("Microsoft.XMLHTTP")
}
catch (e){}
}
}
else
return false
document.getElementById(containerid).innerHTML='<img src="pic/loading.gif" alt="LoadingData" />'
page_request.onreadystatechange=function(){
loadpage(page_request, containerid)
}
if (bustcachevar) //if bust caching of external page
bustcacheparameter=(url.indexOf("?")!=-1)? "&"+new Date().getTime() : "?"+new Date().getTime()
page_request.open('GET', url+bustcacheparameter, true)
page_request.send(null)
}

function loadpage(page_request, containerid){
if (page_request.readyState == 4 && (page_request.status==200 || window.location.href.indexOf("http")==-1))
document.getElementById(containerid).innerHTML=page_request.responseText
}

function loadobjs(){
if (!document.getElementById)
return
for (i=0; i<arguments.length; i++){
var file=arguments[i]
var fileref=""
if (loadedobjects.indexOf(file)==-1){ //Check to see if this object has not already been added to page before proceeding
if (file.indexOf(".js")!=-1){ //If object is a js file
fileref=document.createElement('script')
fileref.setAttribute("type","text/javascript");
fileref.setAttribute("src", file);
}
else if (file.indexOf(".css")!=-1){ //If object is a css file
fileref=document.createElement("link")
fileref.setAttribute("rel", "stylesheet");
fileref.setAttribute("type", "text/css");
fileref.setAttribute("href", file);
}
}
if (fileref!=""){
document.getElementsByTagName("head").item(0).appendChild(fileref)
loadedobjects+=file+" " //Remember this object as being already added to page
}
}
}

</script>
<script type="text/javascript" src="FormManager.js">
/****************************************************
* Form Dependency Manager- By Twey- http://www.twey.co.uk
* Visit Dynamic Drive for this script and more: http://www.dynamicdrive.com
****************************************************/
</script>
<script type="text/javascript">
<!--
function SelectAll(id)
{
    document.getElementById(id).focus();
    document.getElementById(id).select();
}
function SetSize(obj, x_size) {
      if (obj.offsetWidth > x_size) {
      obj.style.width = x_size;
  };
};
//-->
</script>
<link rel="shortcut icon" href="favicon.ico" />

<script type="text/javascript" src="java_klappe.js"></script>
<title>
<?= $title ?>
</title>
<link rel="stylesheet" type="text/css" href="./themes/<?=$ss_uri . "/" . $ss_uri?>.css" />
<link rel="stylesheet" type="text/css" href="./themes/blackred/blackred.css">
<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
<?php if ($CURUSER) {
    ?>
<link rel="alternate" type="application/rss+xml" title="Latest Torrents" href="rss.php?feed=dl&amp;passkey=<?=$CURUSER["passkey"]?>&amp;user=<?=$CURUSER["username"]?>" />
<?php }
?>
<script type="text/javascript" src="keyboard.js" charset="UTF-8"></script>
<link rel="stylesheet" type="text/css" href="keyboard.css" />
<!--<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="js/lightbox.js"></script>
<script type="text/javascript" src="js/blendtrans.js"></script>
<script type="text/javascript" src="js/fade.js"></script>
-->
<script type="text/javascript"language="JavaScript1.2">
function log_out()
{
    ht = document.getElementsByTagName("html");
    ht[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
    if (confirm(l_logout))
    {
        return true;
    }
    else
    {
        ht[0].style.filter = "";
        return false;
    }
}
var l_logout="Are you sure, you want to logout?";

function closeit(box)
{
document.getElementById(box).style.display="none";
}

function showit(box)
{
document.getElementById(box).style.display="block";
}
</script>

<script type="text/javascript">

//<!-- Begin
var checkflag = "false";
function check(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
return "Uncheck All"; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
return "Check All"; }
}
//  End -->
</script>

<script type="text/javascript">
  function schimba (srcpoza, numepoza) {
   document.images[numepoza].src=srcpoza;
  }
 </script>
 <script src="query-1.3.2.min.js" type="text/javascript"></script>

<script type="text/javascript"> 

function DisplayIRC()

{

$.ajax({

url : "irc.php",

success : function (data) {

$("#irchere").html(data).FadeIn("slow");

}

});

}

</script> 
<script type="text/javascript" src="image-resize/jquery.js"></script>
<script type="text/javascript" src="image-resize/core-resize.js"></script>
<link type="text/css" rel="stylesheet" href="image-resize/resize.css"  />
<script src="jquery-1.3.2.min.js" type="text/javascript"></script>
</head>

<body <?=($_SERVER["REQUEST_URI"] == "/browse.php" ? "onload=\"auto();\"" : "")?>>
<script type="text/javascript" src="js/wz_tooltip.js"></script>
<div id="rooms" style="z-index:90;position:absolute">
</div>
<tr>
<div align="center"><img src="themes/blackred/pic/Installer.png"/>
</tr>
<!--///////// menu//////////////////-->
<table background="themes/blackred/pic/bgnav2.gif"  class="cHs" width="800"  height="35"border="0" align="center" cellpadding="0" cellspacing="0"><tr><td>&nbsp;&nbsp;&nbsp;
<a href="index.php"><b>Home</b></a></td>
<td><a href="browse.php"><b>Browse</b></a></td>
<td><a href="requests.php"><b>Requests</b></a></td>
<td><a href="upload.php"><b>Upload</b></a></td>
<td><a href="usercp.php"><b>Profile</b></a></td>
<td><a href="viewoffers.php"><b>Offer</b></a></td>
<td><a href="forums.php"><b>Forum</b></a></td>
<td><a href="rules.php"><b>Rules</b></a></td>
<td><a href="faq.php"><b>F.A.Q</b></a></td>
<td><a href="chat.php"><b>Irc</b></a></td>
<?
echo "<td align=center class=navigation>".($CURUSER['show_shout'] === 'no' ? "<a class=normal href=shoutbox.php?show_shout=1&show=yes>Chat on</a>" : "<a class=normal href=shoutbox.php?show_shout=1&show=no>Chat off</a>")." </td>";
?>
<td><a href="staff.php"><b>Staff</b></a></td>
</tr></table>
&nbsp;
<!--/////////end of menu////////////--->
<table background="themes/blackred/pic/bg2.png"  class="cHs" width="950" border="0" align="center" cellpadding="0" cellspacing="0">
<tr>
<td colspan="3" class="cHs2" height="21" background="themes/blackred/pic/bg1.png" border="0"><table class="clear" width:="950" margin-top:="0" align="center" border="0" cellspacing="1" cellpadding="8">
<? if (!$CURUSER) { ?>
<? } else { ?>
<center>
<tr>
</table>
</td>
</tr>
<? } ?>
<tr>
<td class="cHs" background="pic/left.gif"></td>
<td class='cHs' align=center width=1000 background="transparent" class=interior valign=top><br />
<!-- /////// some vars for the statusbar;o) //////// -->
<?php
if ($CURUSER) {
    $datum = getdate();
    $datum["hours"] = sprintf("%02.0f", $datum["hours"]);
    $datum["minutes"] = sprintf("%02.0f", $datum["minutes"]);
    $invites = $CURUSER['invites'];
    $uped = prefixed($CURUSER['uploaded']);
    $downed = prefixed($CURUSER['downloaded']);

    $ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded'] / $CURUSER['downloaded'] : 0;
    $ratio = number_format($ratio, 1);
    $color = get_ratio_color($ratio);
    if ($color)
        $ratio = "<font color=$color>$ratio</font>";

    $icon = "";
    $icon_style = "style=\"padding-left:2px;padding-right:2px\"";
    if ($CURUSER['donor'] == "yes")
        $icon .= "<img src=\"themes/blackred/pic/star.gif\" align=\"baseline\"alt=donor title=donor $icon_style />";
    if ($CURUSER['warned'] == "yes")
        $icon .= "<img src=\"themes/blackred/pic/warned.png\" align=\"baseline\"  alt=warned title=warned $icon_style />";
    if ($CURUSER["webseeder"] == "yes")
        $icon .= "<img src=\"themes/blackred/pic/webseeder.png\" align=\"baseline\"  alt=webseeder title=webseeder $icon_style/>";
    //// check for messages/active torrents and connectability by pdq :) //////////////////
    if (($msgalert) && ((isset($_SESSION['timeunread']) && (gmtime() > $_SESSION['timeunread'] + (60 * 5))) || (!isset($_SESSION['timeunread']))))
    {
    $res1 = sql_query("SELECT COUNT(*) FROM messages WHERE receiver=".$CURUSER['id']." AND unread='yes' AND location >= '1'") or sqlerr(__file__, __line__);
    $arr1 = mysql_fetch_row($res1);
    $_SESSION['unread'] = $arr1[0];
    $_SESSION['timeunread'] = gmtime();
    }
    $unread = (isset($_SESSION["unread"]))?0 + $_SESSION["unread"]:'0';

    if ((isset($_SESSION['timeseed']) && (gmtime() > $_SESSION['timeseed'] + (60 * 5))) || (!isset($_SESSION['timeseed']))) // 3 minutes :p
    {
    $res2 = sql_query("SELECT seeder, connectable, COUNT(*) AS pCount FROM peers WHERE userid=".
    $CURUSER['id']." GROUP BY seeder") or print (mysql_error());
    $seedleech = array('yes' => '0', 'no' => '0');
    while ($row = mysql_fetch_assoc($res2))
    {
        if ($row['seeder'] == 'yes')
            $seedleech['yes'] = $row['pCount'];
        else
            $seedleech['no'] = $row['pCount'];

        if (!isset($row['connectable']))
            $connectable = "---";
        else
            $connectable = ($row['connectable'] == "yes"?
                "<img src=\"themes/" . $ss_uri . "/pic/yescon.png\" />":"<img src=\"themes/" . $ss_uri . "/pic/notcon.png\" />");
            $_SESSION['seedyes'] = $seedleech['yes'];
            $_SESSION['seedno'] = $seedleech['no'];
            $_SESSION['connectable'] = $connectable;
    }
    $_SESSION['timeseed'] = gmtime();
}
$connectable = (isset($_SESSION['connectable']))?($_SESSION['connectable']):'---';
$activeseed = (isset($_SESSION['seedyes']))?0 + $_SESSION['seedyes']:'0';
$activeleech = (isset($_SESSION['seedno']))?0 + $_SESSION['seedno']:'0';  
    // //////////////////comment mod updated by putyn////////////////////////
    $r_c = sql_query("SELECT p.torrent, c.id as comid,t.name,t.owner FROM peers as p LEFT JOIN torrents as t ON p.torrent=t.id  LEFT JOIN comments as c ON c.torrent=p.torrent AND c.user=p.userid WHERE t.owner !=" . $CURUSER["id"] . " AND p.userid=" . $CURUSER["id"]);
    $comment = "";
    if (mysql_num_rows($r_c) > 0) {
        while ($a_c = mysql_fetch_assoc($r_c)) {
            if (!isset($a_c["comid"])) {
                $comment .= "<a href=details.php?id=" . $a_c["torrent"] . ">" . $a_c["name"] . "</a><br/>";
            }
        }
    }
    // /////////////////////////////////////////////
    function hey()
    {
        GLOBAL $CURUSER, $config, $php_file, $page_find, $lang_off, $language;
        $now = date("H", time() + (($CURUSER["timezone"] + $CURUSER["dst"]) * 60));
        switch ($now) {
            case ($now >= 7 && $now < 10):
                return "".$language['stdhey']."";
            case ($now >= 10 && $now < 12):
                return "".$language['stdhey1']."";
            case ($now >= 12 && $now < 17):
                return "".$language['stdhey2']."";
            case ($now >= 17 && $now < 19):
                return "".$language['stdhey3']."";
            case ($now >= 19 && $now < 21):
                return "".$language['stdhey4']."";
            case ($now >= 21 && $now < 23):
                return "".$language['stdhey5']."";
            case ($now >= 0 && $now < 7):
                return "".$language['stdhey6']."";

            default: return "".$language['stdhey7']."";
        }
    } 
    if ($CURUSER['override_class'] != 255) $usrclass = "&nbsp;<b>(" . get_user_class_name($CURUSER['class']) . ")</b>&nbsp;";
    elseif (get_user_class() >= UC_MODERATOR) $usrclass = "&nbsp;<a href=setclass.php><b>(" . get_user_class_name($CURUSER['class']) . ")</b></a>&nbsp;";
?>
<div id="statusbar" class="statusbar">
<table cellpadding="0" cellpadding="0" cellspacing="0"   align="center" border="0" width="90%">
<tr>
<td width="14" height="54"><img src="themes/blackred/statusbar/colt_st1.png" alt="" width="14" height="54"></td
<td align ="center" style = "background-image: url(themes/blackred/statusbar/bk2A.png);"
<div id="statusbar" class="statusbar">
<table width="100%" background="themes/blackred/statusbar/bk2A.png" height="54";>
        <tr>
		    <td align="left" >
<b><?=hey()?>,&nbsp;<a href="userdetails.php?id=<?=$CURUSER['id']?>"><?=$CURUSER['username']?> </a></b><?=$usrclass?>
 <?=$icon?>
 <b><?php echo $language['bonus'];?><a href="mybonus.php"><?=number_format( $CURUSER['seedbonus'], 1 )?></a></b>&nbsp;|
 <b><?php echo $language['slot'];?><a href="#"><?=number_format( $CURUSER['freeslots'] )?></a></b>&nbsp;|
 <b><?php echo $language['conn'];?><?=$connectable?>&nbsp;|</b>
 
 <br /><b><?php echo $language['ratio'];?><?=$ratio?> |&nbsp;<font style="color:#006600">U:</font> <?=$uped?> <font style="color:#990000">D:</font> <?=$downed?> | <img src="pic/arrowdown.gif" width="9" height="11" alt="downloding torrents" title="downloding torrents"/><?=$activeleech?>&nbsp;<img src="pic/arrowup.gif" width="9" he ght="11" alt="seeding torrents" title="seeding torrents" /><?=$activeseed?> |
 <?if($invites>0){?>
   <b><?php echo $language['inv'];?><a href=invite.php> <?=$invites?> </a></b>&nbsp;|
 <?}?>
&nbsp;<?php if ( get_user_class() > UC_MODERATOR ) { ?><a href="staffpanel.php"><b>[Admin]</b></a><?php } ?>
				  </table>
				  <td width="15" height="54" style = "background-image: url(themes/blackred/statusbar/MID2.png);">
	<td  height="54" style = "background-image: url(themes/blackred/statusbar/bk2.png);">
                 <div align="right"><font color="#0066FF"><span class="smallfont"><?=$clock?><span id="clock"></span><br>
                <script type="text/javascript">
function refrClock(){
var d=new Date();
var s=d.getSeconds();
var m=d.getMinutes();
var h=d.getHours();
var day=d.getDay();
var date=d.getDate();
var month=d.getMonth();
var year=d.getFullYear();
var am_pm;
if (s<10) {s="0" + s}
if (m<10) {m="0" + m}
if (h>12) {h-=12;am_pm = "Pm"}
else {am_pm="Am"}
if (h<10) {h="0" + h}
document.getElementById("clock").innerHTML=h + ":" + m + ":" + s + " " + am_pm;
setTimeout("refrClock()",1000);
}
refrClock();
</script>
                  </font>
<?php
if (!empty($unread))
    $inbox = ($unread == 1 ? "$unread&nbsp;New Message&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;" : "$unread&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;");
    else
    $inbox = "0&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (No new PMs)' src=pic/pn_inbox.gif></a>&nbsp;&nbsp;";
    echo $inbox;
?>
		


		<a href="#" onclick="themes();"><img src="themes/blackred/pic/theme.png" width="13" height="13" alt=" " title="Change your theme" border="0"/></a>
		<a href="bookmarks.php"><img src="themes/blackred/pic/bookmarks.png" width="13" height="13" alt=" " title="Bookmarked torrents" border="0"/></a>&nbsp;
<a href="friends.php"><img style="border:none" alt="Buddylist" title="Buddylist" src="themes/default/pic/friends.png" width="13" height="13" /></a>&nbsp;
<a href="rss.php?userid=<?=$CURUSER["id"]?>"><img src="themes/blackred/pic/rss.png" border="0" title="RSS"/></a>&nbsp;
<a href="logout.php" onClick="return log_out()"><img src="themes/blackred/pic/logout.png" border="0" height="13" width="13" title="Go out and play"/></a>                



    </div>
    <td width="16"><img src="themes/blackred/statusbar/colt_dr.png" alt="" width="16" height="54"></td>
  </tr>


</span></table>
<?php }
?>
&nbsp;&nbsp;&nbsp;
<!--status bar end-->
<?php
// happy hour
if ($CURUSER) {
    if (happyHour("check")) {
        echo("<table border=0 cellspacing=0 cellpadding=10  ><tr><td align=center style=\"background:#CCCCCC;color:#222222; padding:10px\">\n");
        echo("<b>Hey its now happy hour ! " . ((happyCheck("check") == 255) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=" . happyCheck("check") . "\">this category</a> is free this happy hour") . "<br/><font color=red>" . happyHour("time") . " </font> remaining from this happy hour!</b>");
        echo("</td></tr></table>\n");
    }
}
// === report link by pdq :)
  if ($CURUSER['class'] >= UC_MODERATOR) { 
  if (((isset($_SESSION['r_added']) && (gmtime() > $_SESSION['r_added'] + (60 * 30))) || (!isset($_SESSION['r_added'])))) {
        $res_reports = sql_query( "SELECT COUNT(*) FROM reports WHERE delt_with = '0'" );
        $num_reports = mysql_fetch_row($res_reports);
        $_SESSION['reports'] = $num_reports[0];
        $_SESSION['r_added'] = gmtime();
    }
    $num_reports = (isset($_SESSION["reports"]))?0 + $_SESSION["reports"]:'0';
    if ( $num_reports > 0 )
    {
    echo"<p><table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style='padding: 10px; background: #A60A15' align=center><b>Hey $CURUSER[username]! $num_reports Report" . ( $num_reports > 1 ? "s" : "" ) . " to be dealt with<br/>click <a href=reports.php>HERE</a> to view reports</b></td></tr></table></p>\n";
    unset($_SESSION['r_added']);
    unset($_SESSION['reports']);
}
}
//== helpdesk link by pdq :)
if ($CURUSER['class'] >= UC_MODERATOR) { 
  if (((isset($_SESSION['h_added']) && (gmtime() > $_SESSION['h_added'] + (60 * 3600))) ||  //30 minutes :p
  (!isset($_SESSION['h_added'])))) {
        $resa = sql_query( "select count(id) as problems from helpdesk WHERE solved = 'no'" );
        $num_problems = mysql_fetch_row($resa);
        $_SESSION['problems'] = $num_problems[0];
        $_SESSION['h_added'] = gmtime();
    }
    $num_problems = (isset($_SESSION["problems"]))?0 + $_SESSION["problems"]:'0';
    if ( $num_problems > 0 )
    {
    echo( "<p><table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style='padding: 10px; background: #A60A15'>\n" . "Hi <b>$CURUSER[username]</b>, there " . ( $problems == 1 ? 'is' : 'are' ) . " <b>$problems question" . ( $problems == 1 ? '' : 's' ) . "</b> at the help desk that needs a reply.<br/>please click <b><a href=$BASEURL/helpdesk.php?action=problems>HERE</a></b> to deal with it." . "</td></tr></table></p>\n" );
    unset($_SESSION['h_added']);
    unset($_SESSION['problems']);
}
}
// ////////////running at a lower class/////////
if ($CURUSER['override_class'] != 255 && $CURUSER) { // Second condition needed so that this box isn't displayed for non members/logged out members.
        echo("<table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td  style=\"padding: 10px; background-image: url(pic/back_newpm.gif)\">\n");
    echo("<b><a href=restoreclass.php><font color=black>You are running under a lower class. Click here to restore.</font></a></b>");
    echo("</td></tr></table></p>\n");
}

if ($CURUSER && $CURUSER['country'] == 0) {
    echo("<table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style=\"padding: 10px; background-image: url(pic/back_newpm.gif)\">\n");
    echo("<b><a href=\"usercp.php\"><font color=black>Please choose your country in your profile !</font></a></b>");
    echo("</td></tr></table></p>\n");
}
// === free download
if (($CURUSER) && ($free_for_all)) {
    echo '<table width=50%><tr><td class=colhead colspan=3 align=center>' . unesc($freetitle) . '</td></tr><tr><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td><td><div align=center>' . format_comment($freemessage) . '</div></td><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td></tr></table><br />';
}
// === double download
if (($CURUSER) && ($double_for_all)) {
    echo '<table width=50%><tr><td class=colhead colspan=3 align=center>' . unesc($doubletitle) . '</td></tr><tr><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td><td><div align=center>' . format_comment($doublemessage) . '</div></td><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td></tr></table><br />';
}
if (isset($comment) && !empty($comment)) {
    echo("<table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style=\"padding: 10px; background:lightgreen\">\n");
    echo("<font color=black>Please leave a comment on:<br>$comment</font>");
    echo("</td></tr></table></p>\n");
}
// Announcement Code...
$ann_subject = trim($CURUSER['curr_ann_subject']);
$ann_body = trim($CURUSER['curr_ann_body']);

if ((!empty($ann_subject)) AND (!empty($ann_body))) {

    ?>
<!-- <table width=756 class=main border=0 cellspacing=0 cellpadding=0><tr><td class=embedded> -->
<p>
<table width=760 border=1 cellspacing=0 cellpadding=5>
  <tr>
    <td bgcolor=#466248><b><font color=white>Announcement: <?php echo(safe($ann_subject));
    ?></font></b></td>
  </tr>
  <tr>
    <td style='padding: 10px; background: #FFFFFF'><?php echo(format_comment($ann_body));
    ?> <br />
      <hr />
      <br />
      Click <a href=<?php echo(safe($DEFAULTBASEURL))?>/clear_announcement.php> <i><b>here</b></i></a> to clear this announcement.</td>
  </tr>
</table>
</p>
<?php
}
if ($CURUSER["tenpercent"] == "no") {

    ?>
<script  type="text/javascript">
function enablesubmit() {
document.tenpercent.submit.disabled = document.tenpercent.submit.checked;
}
function disablesubmit() {
document.tenpercent.submit.disabled = !document.tenpercent.submit.checked;
}
</script>
<?php
}
// === shoutbox
if ($CURUSER['show_shout'] === "yes") {

    ?>
<script type="text/javascript">
function SmileIT(smile,form,text){
document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
document.forms[form].elements[text].focus();
}
function PopMoreSmiles(form,name) {
link='moresmiles.php?form='+form+'&text='+name
newWin=window.open(link,'moresmile','height=500,width=450,resizable=no,scrollbars=yes');
if (window.focus) {newWin.focus()}
}
function PopCustomSmiles(form,name) {
link='moresmilies_custom.php?form='+form+'&text='+name
newWin=window.open(link,'moresmile','height=600,width=400,resizable=yes,scrollbars=yes');
if (window.focus) {newWin.focus()}
}
</script>
<script type="text/javascript" ><!--
function mySubmit() {
setTimeout('document.shbox.reset()',100);
}
//--></SCRIPT>
<form action='shoutbox.php' method='get' target='sbox' name='shbox' onSubmit="mySubmit()">
<table width='710' border='0' cellspacing='0' cellpadding='1'>
  <tr>
    <td class=colhead><h2 align="center">

          ShoutBox [ <a href=shoutbox.php?show_shout=1&amp;show=no>close</a> ]

      </h2></td>
  </tr>
  <tr>
  <td >

  <iframe src='shoutbox.php' width='800%' height='200' frameborder='0' name='sbox' marginwidth='0' marginheight='0' align='center'></iframe>
  <br/>
  <br/>


  <div align="center">
    <b>Shout!:</b>
    <input type='text' maxlength=360 name='shbox_text' size='150' />
    <input class=button type='submit' value='Send' />
    <input type='hidden' name='sent' value='yes' />

  <br />
	<a href="javascript:SmileIT(':-)','shbox','shbox_text')"><img border=0 src=pic/smilies/smile1.gif /></a> <a href="javascript:SmileIT(':smile:','shbox','shbox_text')"><img border=0 src=pic/smilies/smile2.gif /></a> <a href="javascript:SmileIT(':-D','shbox','shbox_text')"><img border=0 src=pic/smilies/grin.gif /></a> <a href="javascript:SmileIT(':lol:','shbox','shbox_text')"><img border=0 src=pic/smilies/laugh.gif /></a> <a href="javascript:SmileIT(':w00t:','shbox','shbox_text')"><img border=0 src=pic/smilies/w00t.gif /></a> <a href="javascript:SmileIT(':blum:','shbox','shbox_text')"><img border=0 src=pic/smilies/blum.gif /></a> <a href="javascript:SmileIT(';-)','shbox','shbox_text')"><img border=0 src=pic/smilies/wink.gif /></a> <a href="javascript:SmileIT(':devil:','shbox','shbox_text')"><img border=0 src=pic/smilies/devil.gif /></a> <a href="javascript:SmileIT(':yawn:','shbox','shbox_text')"><img border=0 src=pic/smilies/yawn.gif /></a> <a href="javascript:SmileIT(':-/','shbox','shbox_text')"><img border=0 src=pic/smilies/confused.gif /></a> <a href="javascript:SmileIT(':o)','shbox','shbox_text')"><img border=0 src=pic/smilies/clown.gif /></a> <a href="javascript:SmileIT(':innocent:','shbox','shbox_text')"><img border=0 src=pic/smilies/innocent.gif /></a> <a href="javascript:SmileIT(':whistle:','shbox','shbox_text')"><img border=0 src=pic/smilies/whistle.gif /></a> <a href="javascript:SmileIT(':unsure:','shbox','shbox_text')"><img border=0 src=pic/smilies/unsure.gif /></a> <a href="javascript:SmileIT(':blush:','shbox','shbox_text')"><img border=0 src=pic/smilies/blush.gif /></a> <a href="javascript:SmileIT(':hmm:','shbox','shbox_text')"><img border=0 src=pic/smilies/hmm.gif /></a> <a href="javascript:SmileIT(':hmmm:','shbox','shbox_text')"><img border=0 src=pic/smilies/hmmm.gif /></a> <a href="javascript:SmileIT(':huh:','shbox','shbox_text')"><img border=0 src=pic/smilies/huh.gif /></a> <a href="javascript:SmileIT(':look:','shbox','shbox_text')"><img border=0 src=pic/smilies/look.gif /></a> <a href="javascript:SmileIT(':rolleyes:','shbox','shbox_text')"><img border=0 src=pic/smilies/rolleyes.gif /></a> <a href="javascript:SmileIT(':kiss:','shbox','shbox_text')"><img border=0 src=pic/smilies/kiss.gif /></a> <a href="javascript:SmileIT(':blink:','shbox','shbox_text')"><img border=0 src=pic/smilies/blink.gif /></a> <a href="javascript:SmileIT(':baby:','shbox','shbox_text')"><img border=0 src=pic/smilies/baby.gif /></a><br/>

	<p><a href='shoutbox.php' target='sbox'>[ Refresh ]</a><a href="javascript:PopMoreSmiles('shbox','shbox_text')">[ More Smilies ]</a></p>
                              <? 
  if (get_user_class() >= UC_MODERATOR) { ?>
  <a href="javascript:popUp('shoutbox_commands.php')">[ Commands ]</a>
  <? } ?>
    </div>
    <br />
  </td>
  </tr>
</table>
</form>
<?php }
?>