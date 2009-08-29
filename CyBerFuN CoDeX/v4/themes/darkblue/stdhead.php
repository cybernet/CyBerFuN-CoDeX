<?php
if ($CURUSER) {
    foreach($mood as $key => $value)
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $moodname = $change[$CURUSER['mood']]['name'];
    $moodpic = $change[$CURUSER['mood']]['image'];
}
// //////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="300" />
<meta http-equiv="cache-control" content="private" />
<meta name="robots" content="noindex, nofollow, noarchive" />
<link href="themes/<?=$ss_uri?>/layout2.css" rel="stylesheet" type="text/css" />
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
<!--
function SetSize(obj, x_size) {
      if (obj.offsetWidth > x_size) {
      obj.style.width = x_size;
  };
};
//-->
</script>
<link rel="stylesheet" href="css/lightbox.css" type="text/css" media="screen" />
<?php if ($CURUSER) {
    ?>
<link rel="alternate" type="application/rss+xml" title="Latest Torrents" href="rss.php?passkey=<?=$CURUSER["passkey"]?>&user=<?=$CURUSER["username"]?>">
<link rel="alternate" type="application/rss+xml" title="Current Subscriptions" href="<?=$DEFAULTBASEURL?>/rss_subscriptions.php?key=<?=$CURUSER["passkey"]?>"/>
<?php }
?>
<script type="text/javascript" src="keyboard.js" charset="UTF-8"></script>
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
<script type="text/javascript">
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=740,height=380,left = 340,top = 280');");
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
<!--
  Final word of advice: You can wrap the STYLE element above in IE conditional
  comments like below to prevent the .HTC being downloaded by IE7+ at all.
  It's by no means required, and will most likely break IE6 installed alongside
  IE7 on the same computer (despite working fine in vanilla system-wide IE6).
 -->
 <!--[if lte IE 6]>
 <style>
  /*
  USAGE:
  Copy and paste this one line into your site's CSS stylesheet.
  Add comma-separated CSS selectors / element names that have transparent PNGs.
  The path to the HTC is relative to the HTML file that includes it.
  See below for another method of activating the script without adding CSS here.
 */

 img, div, input, td, a, table { behavior: url("themes/darkblue/iefix/iepngfix.htc") }


 /*
  Here's an example you might use in practice:
  img, div, .pngfix, input { behavior: url("/css/iepngfix.htc") }
 */
</style><![endif]-->
<title><?= $title ?></title>
</head>
    <body>
    <script type="text/javascript" src="js/wz_tooltip.js"></script>
        <div id="status">
         <!--status bar will be here -->
<?php
// some vars for status bar
if ($CURUSER) {
    $datum = getdate();
    $datum['hours'] = sprintf("%02.0f", $datum['hours']);
    $datum['minutes'] = sprintf("%02.0f", $datum['minutes']);
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
        $icon .= "<img src=\"themes/" . $ss_uri . "/images/donor.png\" align=\"baseline\"alt=donor title=donor $icon_style />";
    if ($CURUSER['warned'] == "yes")
        $icon .= "<img src=\"themes/" . $ss_uri . "/images/warned.png\" align=\"baseline\"  alt=warned title=warned $icon_style />";
    if ($CURUSER["webseeder"] == "yes")
        $icon .= "<img src=\"themes/" . $ss_uri . "/images/webseeder.png\" align=\"baseline\"  alt=webseeder title=webseeder $icon_style/>";
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
    // ////////////////////////////////////////////////////////////////
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
    if (get_user_class() >= UC_MODERATOR) {
        $p = "[<a href=staffpanel.php><strong>Staffpanel</strong></a> - <a href=staff.php><strong>Staff List</strong></a>]";
    } else {
        $p = "[<a href=staff.php><strong>Staff List</strong></a>]";
    }

    if ($CURUSER['override_class'] != 255) $usrclass = "<b>(" . get_user_class_name($CURUSER['class']) . ")</b>";
    elseif (get_user_class() >= UC_MODERATOR) $usrclass = "<a href=setclass.php><b>(" . get_user_class_name($CURUSER['class']) . ")</b></a>";

    ?>
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" style="border:none;display:block;border-collapse:collapse;">
<tr><td align="left" style="border:none; border-collapse:collapse; padding-left:5px;" valign="middle" nowrap="nowrap">
<span style="font-size:12px">
<strong>
 <?=hey()?>,&nbsp;<a href="userdetails.php?id=<?=$CURUSER['id']?>"><?=$CURUSER['username']?></a></strong> <?=$usrclass?> <?=$icon?> <?=$p?>
 <span style="color: #C0C0C0"><strong><?php echo $language['conn'];?></strong></span><?=$connectable?>&nbsp;|
 <span style="color: #C0C0C0"><strong><?php echo $language['bonus'];?></strong></span><a href="mybonus.php"><?=number_format($CURUSER['seedbonus'], 1)?></a></strong>&nbsp;|
 <span style="color: #C0C0C0"><strong><?php echo $language['slot'];?></strong></span><a href="#"><?=number_format($CURUSER['freeslots'])?></a></b>&nbsp;|

<?php if ($invites > 0) {
        ?>
 <span style="color: #C0C0C0"><strong><?php echo $language['inv'];?></strong></span><a href=invite.php> <?=$invites?> </a>
 <?php }
    ?> <br />
 <span style="color: #C0C0C0"><strong><?php echo $language['ratio'];?></strong></span><?=$ratio?>&nbsp;|&nbsp;
 <span style="color: #C0C0C0"><strong>U: </strong></span><span style="color:#0295f2"><?=$uped?></span>&nbsp;|&nbsp;
 <span style="color: #C0C0C0"><strong>D: </strong></span><span style="color:#0295f2"><?=$downed?></span>&nbsp;|
 <img src="pic/arrowdown.gif" width="9" height="11" alt="downloading torrents" title="downloding torrents" /><?=$activeleech?>&nbsp;<img src="pic/arrowup.gif" width="9" height="11" alt="seeding torrents" title="seeding torrents" /><?=$activeseed?> |</b>
<strong> ShoutBox: </strong><?php echo "" . ($CURUSER['show_shout'] === 'no' ? "<a href=shoutbox.php?show_shout=1&show=yes>Show&nbsp;|&nbsp;</a>" : "<a href=shoutbox.php?show_shout=1&show=no>Hide&nbsp;|&nbsp;</a>") . "";
?>
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
</td>
</span>
<td align="right" nowrap="nowrap" width="100%" style="border:none; border-collapse:collapse;padding-right:10px">
<span style="font-size:12px">
<?php
if (!empty($unread))
    $inbox = ($unread == 1 ? "$unread&nbsp;New Message&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;" : "$unread&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;");
    else
    $inbox = "0&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (No new PMs)' src=pic/pn_inbox.gif></a>&nbsp;&nbsp;";
    echo $inbox;
?>
<a href="#" onclick="themes();"><img src="themes/<?=$ss_uri?>/images/theme.png" width="16" height="16" alt=" " title="Change your theme" border="0"/></a>
<a href="bookmarks.php"><img src="themes/<?=$ss_uri?>/images/bookmarks.png" width="16" height="16" alt=" " title="Bookmarked torrents" border="0"/></a>&nbsp;
<a href="friends.php"><img style="border:none" alt="Buddylist" title="Buddylist" src="themes/<?=$ss_uri?>/images/friends.png" width="16" height="16" /></a>&nbsp;
<a href="rss.php?userid=<?=$CURUSER["id"]?>"><img src="themes/<?=$ss_uri?>/images/rss.png" border="0" title="RSS"/></a>&nbsp;
<a href="logout.php" onClick="return log_out()"><img src="themes/<?=$ss_uri?>/images/logout.png" border="0" title="Go out and play"/></a>
</span>
</td>
</tr>

</table>
<?php }
?>
        </div>
		<div id="contentmain">
            <div id="topmenu">
                <img src="themes/<?=$ss_uri?>/images/topmenu.png" border="0" usemap="#toplinks" />
                <map name="toplinks" id="toplinks">
                    <area shape="rect" coords="24,3,88,51" href="browse.php" alt="Browse Torrents" />
                    <area shape="rect" coords="89,2,144,50" href="forums.php" alt="Browse Forums" />
                    <area shape="rect" coords="145,2,200,50" href="index.php" alt="Home" />
                    <area shape="rect" coords="639,2,694,50" href="helpdesk.php" alt="Helpdesk" />
                    <area shape="rect" coords="695,2,759,50" href="jirc.php" alt="IRC Chat" />
                    <area shape="rect" coords="760,2,824,50" href="usercp.php" alt="Profile &amp; User CP" />
                </map>
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
if (isset($unread) && !empty($unread)) {

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
if ($CURUSER['override_class'] != 255 && $CURUSER) { // Second condition needed so that this box isn't displayed for non members/logged out members.

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
if (isset($comment) && !empty($comment)) {

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
<?php }
?>  <!-- comment mod -->
<?php
$ann_subject = trim($CURUSER['curr_ann_subject']);
$ann_body = trim($CURUSER['curr_ann_body']);

if ((!empty($ann_subject)) AND (!empty($ann_body))) {

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
                    <?php print(safe($ann_subject));
    ?></h1>
                    <?php print(format_comment($ann_body));
    ?>
                    <br /><hr /><br />
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
<?php }
?> <!-- announcement -->
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
        echo("Hi <strong>$CURUSER[username]</strong>, there " . ($problems == 1 ? 'is' : 'are') . " <strong>$problems question" . ($problems == 1 ? '' : 's') . "</strong> at the help desk that needs a reply.<br />please click <strong><a href=$BASEURL/helpdesk.php?action=problems>HERE</a></strong> to deal with it.");
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
if ($CURUSER) {
    if (happyHour("check")) {

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
        echo("<b>Hey its now happy hour ! " . ((happyCheck("check") == 255) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=" . happyCheck("check") . "\">this category</a> is free this happy hour") . "<br/><font color=#ffffff>" . happyHour("time") . " </font> remaining from this happy hour!</b>");

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
// === free download

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
                          <? 
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
<?php }
?> <!-- shoutbox -->


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