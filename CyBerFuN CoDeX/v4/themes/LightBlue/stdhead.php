<?php
if ($CURUSER) {
    foreach($mood as $key => $value)
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $moodname = $change[$CURUSER['mood']]['name'];
    $moodpic = $change[$CURUSER['mood']]['image'];
}
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
<script type="text/javascript" src="java_klappe.js"></script>
<script type="text/javascript" src="javascripts/ajax.js"></script>
<script type="text/javascript" src="javascripts/ajax-poller.js"></script>
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
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=740,height=380,left = 340,top = 280');");
}
</script>
<script type="text/javascript">

/***********************************************
* Highlight Table Cells Script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* Visit http://www.dynamicDrive.com for hundreds of DHTML scripts
* This notice must stay intact for legal use
***********************************************/

//Specify highlight behavior. "TD" to highlight table cells, "TR" to highlight the entire row:
var highlightbehavior="TR"

var ns6=document.getElementById&&!document.all
var ie=document.all

function changeto(e,highlightcolor){
source=ie? event.srcElement : e.target
if (source.tagName=="TABLE")
return
while(source.tagName!=highlightbehavior && source.tagName!="HTML")
source=ns6? source.parentNode : source.parentElement
if (source.style.backgroundColor!=highlightcolor&&source.id!="ignore")
source.style.backgroundColor=highlightcolor
}

function contains_ns6(master, slave) { //check if slave is contained by master
while (slave.parentNode)
if ((slave = slave.parentNode) == master)
return true;
return false;
}

function changeback(e,originalcolor){
if (ie&&(event.fromElement.contains(event.toElement)||source.contains(event.toElement)||source.id=="ignore")||source.tagName=="TABLE")
return
else if (ns6&&(contains_ns6(source, e.relatedTarget)||source.id=="ignore"))
return
if (ie&&event.toElement!=source||ns6&&e.relatedTarget!=source)
source.style.backgroundColor=originalcolor
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
<link rel="stylesheet" type="text/css" href="themes/LightBlue/mainbody.css" />
<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
<?php if ($CURUSER) {
    ?>
<link rel="alternate" type="application/rss+xml" title="Latest Torrents" href="rss.php?feed=dl&amp;passkey=<?=$CURUSER["passkey"]?>&amp;user=<?=$CURUSER["username"]?>" />
<link rel="alternate" type="application/rss+xml" title="Current Subscriptions" href="<?=$DEFAULTBASEURL?>/rss_subscriptions.php?key=<?=$CURUSER["passkey"]?>"/>
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


<script type="text/javascript" src="image-resize/jquery.js"></script>
<script type="text/javascript" src="image-resize/core-resize.js"></script>
<link type="text/css" rel="stylesheet" href="image-resize/resize.css"  />
</head>

<body <?=($_SERVER["REQUEST_URI"] == "/browse.php" ? "onload=\"auto();\"" : "")?>>
<script type="text/javascript" src="js/wz_tooltip.js"></script>
<div id="rooms" style="z-index:90;position:absolute">
</div>
<!--status bar will be here -->
<?php

// here you set the max width for your site
$maxwidth = "70%";
// end width
// some var for satus bar
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
        $icon .= "<img src=\"themes/LightBlue/pic/donor.png\" align=\"baseline\"alt=donor title=donor $icon_style />";
    if ($CURUSER['warned'] == "yes")
        $icon .= "<img src=\"themes/LightBlue/pic/warned.png\" align=\"baseline\"  alt=warned title=warned $icon_style />";
    if ($CURUSER["webseeder"] == "yes")
        $icon .= "<img src=\"themes/LightBlue/pic/webseeder.png\" align=\"baseline\"  alt=webseeder title=webseeder $icon_style/>";
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
<div align="center">
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="statusbar" style="border:0;border-collapse:collapse; padding-top:3px">
<tr>
<td style="border:0;border-collapse:collapse; text-align:center;"><?=hey()?>,<a href="userdetails.php?id=<?=$CURUSER['id']?>"><?=$CURUSER['username']?> </a></b><?=$usrclass?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $language['ratio'];?>&nbsp;</b><?=$ratio?>&nbsp;&nbsp;|&nbsp;&nbsp;<img src="themes/LightBlue/pic/upload.gif" />&nbsp;<?=$uped?>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<img src="themes/LightBlue/pic/downl.gif" />&nbsp;<?=$downed?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $language['activ'];?>&nbsp;&nbsp;<img src="themes/LightBlue/pic/downl.gif" />&nbsp;<?=$activeleech?>&nbsp;&nbsp;<img src="themes/LightBlue/pic/upload.gif" />&nbsp;<?=$activeseed?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $language['bonus'];?>&nbsp;<a href="mybonus.php"><?=number_format($CURUSER['seedbonus'], 1)?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $language['slot'];?>&nbsp;<a href="#"><?=number_format($CURUSER['freeslots'])?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $language['conn'];?><?=$connectable?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php if ($invites > 0) { ?><?php echo $language['inv'];?>&nbsp;<a href=invite.php><?=$invites?></a>
| <span id="clock"><?=$clock?></span>
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
<?php } ?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php
if (!empty($unread))
    $inbox = ($unread == 1 ? "$unread&nbsp;New Message&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;" : "$unread&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;");
    else
    $inbox = "0&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (No new PMs)' src=pic/pn_inbox.gif></a>&nbsp;&nbsp;";
    echo $inbox;
?>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" onclick="themes();"><img src="themes/default/pic/theme.png" width="12" height="12" alt=" " title="Change your theme" border="0"/></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="bookmarks.php"><img src="themes/default/pic/bookmarks.png" width="12" height="12" alt=" " title="Bookmarked torrents" border="0"/></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="friends.php"><img style="border:none" alt="Buddylist" title="Buddylist" src="themes/default/pic/friends.png" width="12" height="12" /></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="logout.php" onClick="return log_out()"><img src="themes/default/pic/logout.png"  width="12" height="12" border="0" title="Go out and play"/></a>
</td>
</tr>
</table>
</div>

<?php }
?>
<!--status bar end-->


<table style="border:none;border-collapse:collapse; background:none;" width="100%" cellpadding="5" cellspacing="0" align="center" border="0">
  <tr>
    <td align="center" style="border:none;padding-top:25px;"><img src="themes/LightBlue/pic/logo.png"/></td>
  </tr>
</table>
<!--here the menu stars-->

<table width="<?=$maxwidth?>" style="height:47px; border:none;" cellpadding="0" cellspacing="0" align="center">
<tr>
	<td style="background:url(themes/LightBlue/pic/menu_1.png) no-repeat; width:34px;border:none;"><div style="display:block; border:none; min-width:34px"></div></td>
    <td style="background:url(themes/LightBlue/pic/menu_2.png) repeat-x; height:47px;border:none;" cellpadding="3"><table width="100%" border="0" cellspacing="0" cellpadding="5px" style="border:none;">
  <tr>
    <td height="0" class="menu123"><a href=index.php><?php echo $language['home'];?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=browse.php><?php echo $language['brws'];?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
    if (get_user_class() >= UC_POWER_USER) { ?> <a href="requests.php"><?php echo $language['req'];?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php }    ?><?php
    if (get_user_class() >= UC_UPLOADER) { ?><a href=upload.php><?php echo $language['upload'];?></a><?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=forums.php><?php echo $language['forum'];?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=helpdesk.php><?php echo $language['help'];?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=staff.php><?php echo $language['staff'];?></a>
            </td>
  </tr>
  <tr>
    <td height="0" class="menu456">Shoutbox&nbsp;-&nbsp;<?php echo "".($CURUSER['show_shout'] === 'no' ? "<a href=shoutbox.php?show_shout=1&show=yes>Show</a>" : "<a href=shoutbox.php?show_shout=1&show=no>Hide</a>").""; ?>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="topten.php"><?php echo $language['tten'];?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=rules.php><?php echo $language['rules'];?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=wiki.php><?php echo $language['wik'];?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=usercp.php><?php echo $language['usercp'];?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href=donate.php><?php echo $language['don'];?></a><? if (get_user_class() >= UC_MODERATOR) {        ?> &nbsp;&nbsp;|&nbsp;&nbsp;<a href="staffpanel.php"><?php echo $language['spanel'];?></a><?php } ?><? if (get_user_class() >= UC_CODER) {        ?> &nbsp;&nbsp;|&nbsp;&nbsp;<a href="core.php"><?php echo $language['core'];?></a>
            <?php }
    ?></td>
  </tr>
</table>
</td>
    <td style="background:url(themes/LightBlue/pic/menu_3.png) no-repeat; width:32px; border:none;"><div style="display:block; border:none; min-width:32px"></div></td>

</tr>
</table>
<!--here the menu ends-->
<!--the main body -->
<table width="<?=$maxwidth?>" style="border:none;background:none;" border="0" cellpadding="0" cellspacing="0" align="center">
<tr>
	<td class="tl"><div style="display:block; border:none; min-width:34px"></div></td>
    <td class="tm"></td>
    <td class="tr"><div style="display:block; border:none; min-width:32px"></div></td>

</tr>
<tr>
	<td class="ml" nowrap="nowrap">&nbsp;</td>
    <td class="main_body" align="center">
<!--will end on the other side-->
<?php
if (isset($unread) && !empty($unread)) {
    print("<table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style=\"padding: 10px; background-image: url(pic/back_newpm.gif)\">\n");
    print("<b><a href=$BASEURL/messages.php?action=viewmailbox><font color=white>You have $unread new message" . ($unread > 1 ? "s" : "") . "!</font></a></b>");
    print("</td></tr></table></p>\n");
}
// happy hour
if ($CURUSER) {
    if (happyHour("check")) {
        print("<table border=0 cellspacing=0 cellpadding=10  ><tr><td align=center style=\"background:#CCCCCC;color:#222222; padding:10px\">\n");
        print("<b>Hey its now happy hour ! " . ((happyCheck("check") == 255) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=" . happyCheck("check") . "\">this category</a> is free this happy hour") . "<br/><font color=red>" . happyHour("time") . " </font> remaining from this happy hour!</b>");
        print("</td></tr></table>\n");
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
  if (((isset($_SESSION['h_added']) && (gmtime() > $_SESSION['h_added'] + (60 * 30))) || (!isset($_SESSION['h_added'])))) {
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
        print("<table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td  style=\"padding: 10px; background-image: url(pic/back_newpm.gif)\">\n");
    print("<b><a href=restoreclass.php><font color=black>You are running under a lower class. Click here to restore.</font></a></b>");
    print("</td></tr></table></p>\n");
}
if ($CURUSER && $CURUSER['country'] == 0) {
    print("<table border=0 cellspacing=0 cellpadding=10 bgcolor=red><tr><td style=\"padding: 10px; background-image: url(pic/back_newpm.gif)\">\n");
    print("<b><a href=\"usercp.php\"><font color=black>Please choose your country in your profile !</font></a></b>");
    print("</td></tr></table></p>\n");
}
// === free download
if (($CURUSER) && ($free_for_all)) {
    echo '<table width=50%><tr><td class=colhead colspan=3 align=center>' . unesc($freetitle) . '</td></tr><tr><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td><td><div align=center>' . format_comment($freemessage) . '</div></td><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td></tr></table><br />';
}
// === double download
if (($CURUSER) && ($double_for_all)) {
    echo '<table width=50%><tr><td class=colhead colspan=3 align=center>' . unesc($doubletitle) . '</td></tr><tr><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td><td><div align=center>' . format_comment($doublemessage) . '</div></td><td width=42 align=center valign=center><img src=pic/cat_free.gif alt=FREE! /></td></tr></table><br />';
}
if ($comment) {
    print("<table border=0 cellspacing=10 cellpadding=10 ><tr><td style=\"padding: 10px;background:lightgreen\">\n");
    print("<font color=black>Please leave a comment on:<br/>$comment</font>");
    print("</td></tr></table><br/>\n");
}
// Announcement Code...
$ann_subject = trim($CURUSER['curr_ann_subject']);
$ann_body = trim($CURUSER['curr_ann_body']);

if ((!empty($ann_subject)) AND (!empty($ann_body))) {

    ?>
<!-- <table width=756 class=main border=0 cellspacing=0 cellpadding=0><tr><td class=embedded> -->

<table width=752 border=1 cellspacing=0 cellpadding="0" style="margin-left:auto; margin-right:auto;">
  <tr>
    <td class="colhead"><b>Announcement: <?php print(safe($ann_subject));
    ?></b></td>
  </tr>
  <tr>
    <td style="padding: 10px;background:#cccccc;color:#666666"><?php print(format_comment($ann_body));
    ?> <br />
      <hr />
      <br />
      Click <a href="<?php print(safe($DEFAULTBASEURL))?>/clear_announcement.php"> <i><b>here</b></i></a> to clear this announcement.</td>
  </tr>
</table>

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
<table width='752' border='0' cellspacing='0' cellpadding='1' style="margin-left:auto; margin-right:auto;" >
  <tr>
    <td class=colhead><h2 align="center">

          ShoutBox [ <a href=shoutbox.php?show_shout=1&amp;show=no>close</a> ]

      </h2></td>
  </tr>
  <tr>
  <td >

  <iframe src='shoutbox.php' width='750' height='200' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe>
  <br/>
  <br/>


  <div align="center">
    <b>Shout!:</b>
    <input type='text' maxlength=380 name='shbox_text' size='100' />
    <input class=button type='submit' value='Send' />
    <input type='hidden' name='sent' value='yes' />

  <br />
	<a href="javascript:SmileIT(':-)','shbox','shbox_text')"><img border=0 src=pic/smilies/smile1.gif /></a> <a href="javascript:SmileIT(':smile:','shbox','shbox_text')"><img border=0 src=pic/smilies/smile2.gif /></a> <a href="javascript:SmileIT(':-D','shbox','shbox_text')"><img border=0 src=pic/smilies/grin.gif /></a> <a href="javascript:SmileIT(':lol:','shbox','shbox_text')"><img border=0 src=pic/smilies/laugh.gif /></a> <a href="javascript:SmileIT(':w00t:','shbox','shbox_text')"><img border=0 src=pic/smilies/w00t.gif /></a> <a href="javascript:SmileIT(':blum:','shbox','shbox_text')"><img border=0 src=pic/smilies/blum.gif /></a> <a href="javascript:SmileIT(';-)','shbox','shbox_text')"><img border=0 src=pic/smilies/wink.gif /></a> <a href="javascript:SmileIT(':devil:','shbox','shbox_text')"><img border=0 src=pic/smilies/devil.gif /></a> <a href="javascript:SmileIT(':yawn:','shbox','shbox_text')"><img border=0 src=pic/smilies/yawn.gif /></a> <a href="javascript:SmileIT(':-/','shbox','shbox_text')"><img border=0 src=pic/smilies/confused.gif /></a> <a href="javascript:SmileIT(':o)','shbox','shbox_text')"><img border=0 src=pic/smilies/clown.gif /></a> <a href="javascript:SmileIT(':innocent:','shbox','shbox_text')"><img border=0 src=pic/smilies/innocent.gif /></a> <a href="javascript:SmileIT(':whistle:','shbox','shbox_text')"><img border=0 src=pic/smilies/whistle.gif /></a> <a href="javascript:SmileIT(':unsure:','shbox','shbox_text')"><img border=0 src=pic/smilies/unsure.gif /></a> <a href="javascript:SmileIT(':blush:','shbox','shbox_text')"><img border=0 src=pic/smilies/blush.gif /></a> <a href="javascript:SmileIT(':hmm:','shbox','shbox_text')"><img border=0 src=pic/smilies/hmm.gif /></a> <a href="javascript:SmileIT(':hmmm:','shbox','shbox_text')"><img border=0 src=pic/smilies/hmmm.gif /></a> <a href="javascript:SmileIT(':huh:','shbox','shbox_text')"><img border=0 src=pic/smilies/huh.gif /></a> <a href="javascript:SmileIT(':look:','shbox','shbox_text')"><img border=0 src=pic/smilies/look.gif /></a> <a href="javascript:SmileIT(':rolleyes:','shbox','shbox_text')"><img border=0 src=pic/smilies/rolleyes.gif /></a> <a href="javascript:SmileIT(':kiss:','shbox','shbox_text')"><img border=0 src=pic/smilies/kiss.gif /></a> <a href="javascript:SmileIT(':blink:','shbox','shbox_text')"><img border=0 src=pic/smilies/blink.gif /></a> <a href="javascript:SmileIT(':baby:','shbox','shbox_text')"><img border=0 src=pic/smilies/baby.gif /></a><br/>

	<p><a href='shoutbox.php' target='sbox'>[ Refresh]</a><a href="javascript:PopMoreSmiles('shbox','shbox_text')">[ More Smilies ]</a></p>
    <?php
  if (get_user_class() >= UC_MODERATOR) { ?>
  <a href="javascript:popUp('shoutbox_commands.php')">[ Commands ]</a>
  <? } ?>
    </div>
    <br />
  </td>
  </tr>
</table>
</form>
<?php 
}
?>
