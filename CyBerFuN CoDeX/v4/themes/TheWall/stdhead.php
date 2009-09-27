<?php 
////////////// Design by TheHippy - Redesigned by KiD for Tbdev Installer and Big Joos | Release date May 04 2009 at http://chat2pals.co.uk & Tbdev.net////////////

if ($CURUSER)
  {
       foreach($mood as $key => $value)
         $change[$value['id']]=array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
         $moodname = $change[$CURUSER['mood']]['name'];
         $moodpic = $change[$CURUSER['mood']]['image'];
  }
////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="300" />
<meta http-equiv="cache-control" content="private" />
<meta name="robots" content="noindex, nofollow, noarchive" />
<link href="themes/<?=$ss_uri?>/layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="java_klappe.js"></script>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/ajax-poller.js"></script>
<script type="text/javascript" src="FormManager.js">
/****************************************************
* Form Dependency Manager- By Twey- http://www.twey.co.uk
* Visit Dynamic Drive for this script and more: http://www.dynamicdrive.com
****************************************************/
</script>

<script language=javascript>
<!--
function Post()
{
document.compose.action = "?action=post"
document.compose.target = "";
document.compose.submit();
return true;
}

function Preview()
{
document.compose.action = "preview.php?"
document.compose.target = "_blank";
document.compose.submit();
return true;
}
-->
function themes() {
    window.open('take_theme.php','My themes','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
}
function popitup(url) {
    newwindow=window.open(url,'usermood.php','height=335,width=735,resizable=no,scrollbars=no,toolbar=no,menubar=no');
    if (window.focus) {newwindow.focus()}
    return false;
}
</script>
<script type="text/javascript">

/***********************************************
* Dynamic Ajax Content-  Dynamic Drive DHTML code library (www.dynamicdrive.com)
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

<script type="text/javascript">
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=740,height=380,left = 340,top = 280');");
}
</script>

<script type="text/javascript">
<!--
function SetSize(obj, x_size) {
      if (obj.offsetWidth > x_size) {
      obj.style.width = x_size;
  };
};
//-->
</script>

<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
<? if ($CURUSER) { ?>
<link rel="alternate" type="application/rss+xml" title="Latest Torrents" href="rss.php?passkey=<?=$CURUSER["passkey"]?>&user=<?=$CURUSER["username"]?>">
<link rel="alternate" type="application/rss+xml" title="Current Subscriptions" href="<?=$DEFAULTBASEURL?>/rss_subscriptions.php?key=<?=$CURUSER["passkey"]?>"/>
<? } ?>
<script type="text/javascript" src="keyboard.js" charset="iso-8859-1"></script>
<link rel="stylesheet" type="text/css" href="keyboard.css">
<script type="text/javascript" src="js/prototype.js"></script>
<script type="text/javascript" src="js/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="js/lightbox.js"></script>
<script language="javascript" src="js/blendtrans.js"></script>
<script language="javascript" src="js/fade.js"></script>
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
</script>
<script type="text/javascript">
function closeit(box)
{
document.getElementById(box).style.display="none";
}

function showit(box)
{
document.getElementById(box).style.display="block";
}
</script>
<script LANGUAGE="JavaScript">

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
<script type="text/javascript" src="ncode_imageresizer.js"></script>
<script type="text/javascript">
<!--
NcodeImageResizer.MODE = 'newwindow';
NcodeImageResizer.MAXWIDTH = "600";
NcodeImageResizer.MAXHEIGHT = "480";

NcodeImageResizer.Msg1 = 'Click this bar to view the full image.';
NcodeImageResizer.Msg2 = 'This image has been resized. Click this bar to view the full image.';
NcodeImageResizer.Msg3 = 'This image has been resized. Click this bar to view the full image.';
NcodeImageResizer.Msg4 = 'Click this bar to view the small image.';
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
<title><?= $title ?></title>
</head>
    <body onload="MM_preloadImages('images/menu/news1.png','images/menu/browse1.png','images/menu/forums1.png','images/menu/wiki1.png','images/menu/rules1.png','images/menu/help1.png','images/menu/staff1.png')">
<script type="text/javascript" src="js/wz_tooltip.js"></script>
        <div id="status">
         <!--status bar will be here -->
<?php
//some vars for status bar
 if ($CURUSER) {
$datum = getdate();
$datum['hours'] = sprintf("%02.0f", $datum['hours']);
$datum['minutes'] = sprintf("%02.0f", $datum['minutes']);
$invites = $CURUSER['invites'];
$uped = prefixed($CURUSER['uploaded']);
$downed = prefixed($CURUSER['downloaded']);

$ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded']/$CURUSER['downloaded'] : 0;
$ratio = number_format($ratio, 1);
$color = get_ratio_color($ratio);
if ($color)
$ratio = "<font color=$color>$ratio</font>";

$icon = "";
$icon_style = "style=\"padding-left:2px;padding-right:2px\"";
if ($CURUSER['donor'] == "yes")
$icon .= "<img src=\"themes/".$ss_uri."/images/donor.png\" align=\"baseline\"alt=donor title=donor $icon_style />";
if ($CURUSER['warned'] == "yes")
$icon .= "<img src=\"themes/".$ss_uri."/images/warned.png\" align=\"baseline\"  alt=warned title=warned $icon_style />";
if ($CURUSER["webseeder"] == "yes")
$icon .="<img src=\"themes/".$ss_uri."/images/webseeder.png\" align=\"baseline\"  alt=webseeder title=webseeder $icon_style/>";
    //// check for messages/seed-leech/connectable by pdq :) //////////////////
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
        if ($row['seeder'] == yes)
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
////////////////////comment mod updated by putyn////////////////////////
$r_c = sql_query("SELECT p.torrent, c.id as comid,t.name,t.owner FROM peers as p LEFT JOIN torrents as t ON p.torrent=t.id  LEFT JOIN comments as c ON c.torrent=p.torrent AND c.user=p.userid WHERE t.owner !=".$CURUSER["id"]." AND p.userid=".$CURUSER["id"]);
$comment ="";
if(mysql_num_rows($r_c) > 0)
{
while($a_c = mysql_fetch_assoc($r_c))
	{
	if(!isset($a_c["comid"]))
		{
		$comment .= "<a href=details.php?id=".$a_c["torrent"].">".$a_c["name"]."</a><br/>";
		}
	}
}
//////////////////////////////////////////////////////////////////
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
if ($CURUSER['override_class'] != 255) $usrclass = "<b>(".get_user_class_name($CURUSER['class']).")</b>";
elseif(get_user_class() >= UC_MODERATOR) $usrclass = "<a href=setclass.php><b>(".get_user_class_name($CURUSER['class']).")</b></a>";
?>
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" style="border:none;display:block;border-collapse:collapse;">
<tr><td align="left" style="border:none; border-collapse:collapse; padding-left:5px;" valign="middle" nowrap="nowrap" style="color:#FFFFFF">
<span style="font-size:10px">
<a href="chat.php">Chat</a>&nbsp;|&nbsp;<a href="request.php">Request</a>&nbsp;|&nbsp;<b>Shoutbox -</b>&nbsp;<?php echo "".($CURUSER['show_shout'] === 'no' ? "<a href=shoutbox.php?show_shout=1&show=yes>Show</a>" : "<a href=shoutbox.php?show_shout=1&show=no>Hide</a>").""; ?>&nbsp;|&nbsp;<a href="usercp.php">Profile</a><? if (get_user_class() >= UC_MODERATOR){ ?>&nbsp;|&nbsp;<a href="upload2.php">Upload Music</a>&nbsp;|&nbsp;<a href="multiupload.php">Multi-Upload</a>&nbsp;|&nbsp;<a href="upload.php">Upload</a><? } ?> <? if (get_user_class() >= UC_MODERATOR){ ?>&nbsp;|&nbsp;<a href="staffpanel.php">StaffPanel</a><? } ?><br /><? if (get_user_class() >= UC_ADMINISTRATOR){ ?><a href="adduser.php"><font color="#FF8900">Add User</a>&nbsp;|&nbsp;<a href="freeleech.php"><font color="#FF8900">View Fl/Du</a>&nbsp;|&nbsp;<? } ?><? if (get_user_class() >= UC_MODERATOR){ ?><a href="usersearch1.php"><font color="#FF8900">User Search</a>&nbsp;|&nbsp;<a href="calculator.php"><font color="#FF8900">Byte Calculator</a>&nbsp;|&nbsp;<a href="snatched_torrents.php"><font color="#FF8900">Snatched Torrents</a>&nbsp;|&nbsp;<a href="users.php"><font color="#FF8900">Users</a> |
<span id="clock"><?=$clock?></span>
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
</script> |
<? } ?></font></td></span>
<td align="right" nowrap="nowrap" width="100%" style="border:none; border-collapse:collapse;padding-right:10px">
<span style="font-size:10px">
<a href="#" onclick="themes();">Themes</a>&nbsp;|&nbsp;<a href="bookmarks.php">Bookmarks</a>&nbsp;|&nbsp;<a href="friends.php">Friends</a>&nbsp;|&nbsp;<b>Connectable:</b>&nbsp;<?=$connectable?><br /><b>Active Torrents:&nbsp;<img src="themes/TheWall/images/as.png" />&nbsp;<font color="#FFF"><?=$activeleech?></font></b>&nbsp;|&nbsp;<b><img src="themes/TheWall/images/al.png" />&nbsp;<font color="#FFF"><?=$activeseed?></font></b>&nbsp;|&nbsp;<?php
if (!empty($unread))
    $inbox = ($unread == 1 ? "$unread&nbsp;New Message&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;" : "$unread&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;");
    else
    $inbox = "0&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (No new PMs)' src=pic/pn_inbox.gif></a>&nbsp;&nbsp;";
    echo $inbox;
    
?></td>
</tr>

</table>
<?php } ?>
        </div>
		<div id="contentmain">
            <div id="menuu"><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><a href="index.php"><img src="themes/TheWall/images/menu/news.png" border="0" id="Image1" onmouseover="MM_swapImage('Image1','','themes/TheWall/images/menu/news1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td><a href="browse.php"><img src="themes/TheWall/images/menu/browse.png" border="0" id="Image2" onmouseover="MM_swapImage('Image2','','themes/TheWall/images/menu/browse1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td><a href="forums.php"><img src="themes/TheWall/images/menu/forums.png" border="0" id="Image3" onmouseover="MM_swapImage('Image3','','themes/TheWall/images/menu/forums1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td><a href="wiki.php"><img src="themes/TheWall/images/menu/wiki.png" border="0" id="Image4" onmouseover="MM_swapImage('Image4','','themes/TheWall/images/menu/wiki1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td><a href="rules.php"><img src="themes/TheWall/images/menu/rules.png" border="0" id="Image5" onmouseover="MM_swapImage('Image5','','themes/TheWall/images/menu/rules1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td><a href="helpdesk.php"><img src="themes/TheWall/images/menu/help.png" border="0" id="Image6" onmouseover="MM_swapImage('Image6','','themes/TheWall/images/menu/help1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td><a href="staff.php"><img src="themes/TheWall/images/menu/staff.png" border="0" id="Image7" onmouseover="MM_swapImage('Image7','','themes/TheWall/images/menu/staff1.png',1)" onmouseout="MM_swapImgRestore()" /></a></td>
    <td background="themes/TheWall/images/menu/bag_right.png" width="472px"><table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <span style="font-size:10px">
    <td style="font-size:10px; padding-right:10px;" align="right"><?=hey()?>,&nbsp;<a href="userdetails.php?id=<?=$CURUSER['id']?>"><?=$CURUSER['username']?></a>&nbsp;<?=$usrclass?>&nbsp;<?=$icon?>&nbsp;<a href="logout.php" onClick="return log_out()">(Logout)</a></td>
  </tr>
  <tr>
    <td style="font-size:10px; padding-right:10px;" align="right"<?php echo $language['ratio'];?>></span>&nbsp;<?=$ratio?>&nbsp;|&nbsp;<img src="themes/TheWall/images/upload.png" />&nbsp;<?=$uped?>&nbsp;|&nbsp;<img src="themes/TheWall/images/download.png" />&nbsp;<?=$downed?></td>
  </tr>
  <tr>
    <td style="font-size:10px; padding-right:10px;" align="right"><?php echo $language['bonus'];?><a href="mybonus.php">&nbsp;<font color="#FF8900"><b><?=number_format($CURUSER['seedbonus'], 1)?></b></font></a>&nbsp;|&nbsp;<?php echo $language['slot'];?>&nbsp;<font color="#FF8900"><b><?=number_format($CURUSER['freeslots'])?></font></b></a>&nbsp;|&nbsp;<? if($invites>0){?><?php echo $language['inv'];?><a href=invite.php>&nbsp;<font color="#FF8900"><b><?=$invites?> </b></font></a></span>
 <? }?></td>
  </tr>
</table>
</td>
  </tr>
</table>
</div>
<div align="center"><a href="index.php"><img src="themes/TheWall/images/logo.png" border="0" /></a></div>
</div>
 <?php
// === report link by pdq :)
  if ($CURUSER['class'] >= UC_MODERATOR) { 
  if (((isset($_SESSION['r_added']) && (gmtime() > $_SESSION['r_added'] + (60 * 30))) || (!isset($_SESSION['r_added'])))) {
        $res_reports = sql_query( "SELECT COUNT(*) FROM reports WHERE delt_with = '0'" );
        $num_reports = mysql_fetch_row($res_reports);
        $_SESSION['reports'] = $num_reports[0];
        $_SESSION['r_added'] = gmtime();
    }
    $num_reports = (isset($_SESSION["reports"]))?0 + $_SESSION["reports"]:'0';
if ($num_reports > 0) {
?>
             </p>
<table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
<?php
	echo"<strong>Hey $CURUSER[username]! $num_reports Report" . ($num_reports > 1 ? "s" : "") . " to be dealt with<br />click <a href=reports.php>HERE</a> to view reports</strong>";
	unset($_SESSION['r_added']);
	unset($_SESSION['reports']);
	?>
                        </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>    
            <br />
<?php
}
} // reports
if (isset($unread) && !empty($unread))
{
?>
                <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
<?php
	echo("<strong><a href=$BASEURL/messages.php?action=viewmailbox>You have $unread new message" . ($unread > 1 ? "s" : "") . " !</a></strong>");
	?>
                        </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>    
            <br />
<?php
} // unread mail
if ($CURUSER['override_class'] != 255 && $CURUSER) // Second condition needed so that this box isn't displayed for non members/logged out members.
{
	?>
            <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
<?php
	echo("<strong><a href=restoreclass.php>You are running under a lower class. Click here to restore.</a></strong>");
?>
                    </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>
<br />
<?php 
}
if (isset($comment) && !empty($comment)){
?>
            <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
                    <?php echo("Please leave a comment on:<br />$comment");
					?>
                                        </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>
            <br />
<?php } ?>  <!-- comment mod -->
<?php 
$ann_subject = trim($CURUSER['curr_ann_subject']);
$ann_body = trim($CURUSER['curr_ann_body']);

if ((!empty($ann_subject)) AND (!empty($ann_body)))
{
?>
            <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
                    <h1>Announcement:
                    <?php print(safe($ann_subject));?></h1>
                    <?php print(format_comment($ann_body));?>
                    <br />- - - - - - - - - - - - - - - - - - - - - - <br />
                    Click <a href=<?php print(safe($DEFAULTBASEURL))?>/clear_announcement.php>
                    <i>here</i></a>  to clear this announcement.
                    </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>
            <br />
<?php } ?> <!-- announcement -->
<?php
//== helpdesk link by pdq :)
if ($CURUSER['class'] >= UC_MODERATOR) { 
  if (((isset($_SESSION['h_added']) && (gmtime() > $_SESSION['h_added'] + (60 * 30))) || (!isset($_SESSION['h_added'])))) {
        $resa = sql_query( "select count(id) as problems from helpdesk WHERE solved = 'no'" );
        $num_problems = mysql_fetch_row($resa);
        $_SESSION['problems'] = $num_problems[0];
        $_SESSION['h_added'] = gmtime();
    }
    $num_problems = (isset($_SESSION["problems"]))?0 + $_SESSION["problems"]:'0';
if ($num_problems > 0) {
	?>
            <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
<?php
echo("Hi <strong>$CURUSER[username]</strong>, there ".($problems == 1 ? 'is' : 'are')." <strong>$problems question".($problems == 1 ? '' : 's')."</strong> at the help desk that needs a reply.<br />please click <strong><a href=$BASEURL/helpdesk.php?action=problems>HERE</a></strong> to deal with it.");
unset($_SESSION['h_added']);
unset($_SESSION['problems']);
?>
                    </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>
<br />
<?php 
}
}
if ($CURUSER){
echo("<br/>");
echo ("<table align=center border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style=\"padding: 10px; background-image: url(pic/back_newpm.gif)\">\n");
echo("<b><a href=$BASEURL/upload.php><font color=white>Upload and get 15 bonus points!</font></a></b>");
echo("</td></tr></table></p>\n");
if (happyHour("check")) 
{
	?>
    <!-- start Happy Hour -->
                <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
                    <?php
	echo("<b>Hey its now happy hour ! ".((happyCheck("check") == 255) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=".happyCheck("check")."\">this category</a> is free this happy hour" )."<br/><font color=#ffffff>".happyHour("time")." </font> remaining from this happy hour!</b>");
	?>
                    </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>    
            <br />
    <!-- end Happy Hour --> <!-- happy hour -->
<?php
}
}

if (($CURUSER) && ($free_for_all)) {
     ?>
    <!-- start free for all -->
                <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
                    <?php
    echo '<table align=center width=50%><tr><td class=colhead colspan=3>' . unesc($freetitle) . '</td></tr><tr><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE!></td><td><div align=center>' . format_comment($freemessage) . '</div></td><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE!></td></tr></table><br />';
       ?>
                    </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>
            <br />
    <!-- end free for all --> <!-- free for all -->
<?php
}
// === double download
if (($CURUSER) && ($double_for_all)) {
?>
    <!-- start double for all -->
                <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="rtopleft"></td>
                    <td class="rtopcenter"></td>
                    <td class="rtopright"></td>
                </tr>
                <tr>
                	<td class="rleftcenter"></td>
                    <td class="rmiddlecenter">
                    <?php
    echo '<table align=center width=50%><tr><td class=colhead colspan=3 >' . unesc($doubletitle) . '</td></tr><tr><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td><td><div align=center>' . format_comment($doublemessage) . '</div></td><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td></tr></table><br />';
       ?>
                    </td>
                	<td class="rrightcenter"></td>
            	</tr>
                <tr>
                	<td class="rbottomleft"></td>
                    <td class="rbottomcenter"></td>
                    <td class="rbottomright"></td>
                </tr>
            </table>
            <br />
    <!-- end double for all --> <!-- double for all -->
<?php
}

if ($CURUSER['show_shout'] === "yes") {
?>
<script language=javascript>
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
<script LANGUAGE="JavaScript"><!--
function mySubmit() 
setTimeout('document.shbox.reset()',100);
}
//--></SCRIPT>
            <!-- start shoutbox table-->
            <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="topleft"></td>
                    <td class="topcenter"></td>
                    <td class="topright"></td>
                </tr>
                <tr>
                	<td class="leftcenter"></td>
                    <td class="middlecenter">
<iframe src='shoutbox.php' width='95%' height='200' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe><br><br>
<form action='shoutbox.php' method='get' target='sbox' name='shbox' onSubmit="mySubmit()">
<strong>Shout:</strong> <input type='text' maxlength=380 name='shbox_text' size='80'> - <input class=button type='submit' value='Send'> <input type='hidden' name='sent' value='yes'> - <a href='shoutbox.php' target='sbox'><input class=button type='submit' value='Refresh'></a>
</form>
<a href="javascript: SmileIT(':-)','shbox','shbox_text')"><img border=0 src=pic/smilies/smile1.gif></a>
<a href="javascript: SmileIT(':smile:','shbox','shbox_text')"><img border=0 src=pic/smilies/smile2.gif></a>
<a href="javascript: SmileIT(':-D','shbox','shbox_text')"><img border=0 src=pic/smilies/grin.gif></a>
<a href="javascript: SmileIT(':lol:','shbox','shbox_text')"><img border=0 src=pic/smilies/laugh.gif></a>
<a href="javascript: SmileIT(':w00t:','shbox','shbox_text')"><img border=0 src=pic/smilies/w00t.gif></a>
<a href="javascript: SmileIT(':blum:','shbox','shbox_text')"><img border=0 src=pic/smilies/blum.gif></a>
<a href="javascript: SmileIT(';-)','shbox','shbox_text')"><img border=0 src=pic/smilies/wink.gif></a>
<a href="javascript: SmileIT(':devil:','shbox','shbox_text')"><img border=0 src=pic/smilies/devil.gif></a>
<a href="javascript: SmileIT(':yawn:','shbox','shbox_text')"><img border=0 src=pic/smilies/yawn.gif></a>
<a href="javascript: SmileIT(':-/','shbox','shbox_text')"><img border=0 src=pic/smilies/confused.gif></a>
<a href="javascript: SmileIT(':o)','shbox','shbox_text')"><img border=0 src=pic/smilies/clown.gif></a>
<a href="javascript: SmileIT(':innocent:','shbox','shbox_text')"><img border=0 src=pic/smilies/innocent.gif></a>
<a href="javascript: SmileIT(':whistle:','shbox','shbox_text')"><img border=0 src=pic/smilies/whistle.gif></a>
<a href="javascript: SmileIT(':unsure:','shbox','shbox_text')"><img border=0 src=pic/smilies/unsure.gif></a>
<a href="javascript: SmileIT(':blush:','shbox','shbox_text')"><img border=0 src=pic/smilies/blush.gif></a>
<a href="javascript: SmileIT(':hmm:','shbox','shbox_text')"><img border=0 src=pic/smilies/hmm.gif></a>
<a href="javascript: SmileIT(':hmmm:','shbox','shbox_text')"><img border=0 src=pic/smilies/hmmm.gif></a>
<a href="javascript: SmileIT(':huh:','shbox','shbox_text')"><img border=0 src=pic/smilies/huh.gif></a>
<a href="javascript: SmileIT(':look:','shbox','shbox_text')"><img border=0 src=pic/smilies/look.gif></a>
<a href="javascript: SmileIT(':rolleyes:','shbox','shbox_text')"><img border=0 src=pic/smilies/rolleyes.gif></a>
<a href="javascript: SmileIT(':kiss:','shbox','shbox_text')"><img border=0 src=pic/smilies/kiss.gif></a>
<a href="javascript: SmileIT(':blink:','shbox','shbox_text')"><img border=0 src=pic/smilies/blink.gif></a>
<a href="javascript: SmileIT(':baby:','shbox','shbox_text')"><img border=0 src=pic/smilies/baby.gif></a>
<a href="javascript: SmileIT(':\'-(','shbox','shbox_text')"><img border=0 src=pic/smilies/cry.gif></a>
<br />
<a href="javascript: PopMoreSmiles('shbox','shbox_text')"><strong>Smilies</strong></a>
                  <br />
  <?php 
  if (get_user_class() >= UC_MODERATOR) { ?>
  <a href="javascript:popUp('shoutbox_commands.php')">Commands</a>
  <? } ?>
                  </td>
                	<td class="rightcenter"></td>
            	</tr>
                <tr>
                	<td class="bottomleft"></td>
                    <td class="bottomcenter"></td>
                    <td class="bottomright"></td>
                </tr>
            </table>    
            <!-- end shoutbox table-->
            <br />
<?php } ?> <!-- shoutbox -->


            <table id="centerstuff" width="850px" cellpadding="0" cellspacing="0">
                <tr>
                	<td class="topleft"></td>
                    <td class="topcenter"></td>
                    <td class="topright"></td>
                </tr>
                <tr>
                	<td class="leftcenter"></td>
                    <td class="middlecenter">
						<div align="center">
