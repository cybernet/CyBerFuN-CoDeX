<?php
if (!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
// //////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="300" />
<meta http-equiv="cache-control" content="private" />
<meta name="robots" content="noindex, nofollow, noarchive" />
<script type="text/javascript" src="java_klappe.js"></script>

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


<script type="text/javascript" src="FormManager.js"></script>
<link rel="shortcut icon" href="favicon.ico" />
<script type="text/javascript" src="java_klappe.js"></script>
<title>
<?= $title ?>
</title>
<link rel="stylesheet" type="text/css" href="./themes/<?=$ss_uri . "/" . $ss_uri?>.css" />
<link rel="stylesheet" type="text/css" href="themes/dotBlue/mainbody.css" />
<?php if ($CURUSER) { ?>
<link rel="alternate" type="application/rss+xml" title="Latest Torrents" href="rss.php?feed=dl&amp;passkey=<?=$CURUSER["passkey"]?>&amp;user=<?=$CURUSER["username"]?>" />
<link rel="alternate" type="application/rss+xml" title="Current Subscriptions" href="<?=$DEFAULTBASEURL?>/rss_subscriptions.php?key=<?=$CURUSER["passkey"]?>"/>
<?php } ?>

<script type="text/javascript" >
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


<script type="text/javascript" src="image-resize/jquery.js"></script>
<script type="text/javascript" src="image-resize/core-resize.js"></script>
<link type="text/css" href="ajax/css/chat.style.css" rel="stylesheet" />
<script type="text/javascript" src="ajax/js/chat.core.js"></script>
<script type="text/javascript" src="ajax/js/jquery.ui.js"></script>
 <script type="text/javascript">
	$(document).ready( 
	function()
		{
		chat_alert();
		global_refresh();
		}
	);


</script>
<link type="text/css" rel="stylesheet" href="image-resize/resize.css"  />
</head>

<body <?=($_SERVER["REQUEST_URI"] == "/browse.php" ? "onload=\"auto();\"" : "")?>>
<script type="text/javascript" src="js/wz_tooltip.js"></script>
<div id="rooms" style="z-index:90;position:absolute">
</div>
<!--status bar will be here -->
<?php
// here you set the max width for your site
$maxwidth = "80%";
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
        $icon .= "<img src=\"themes/dotBlue/pic/donor.png\" align=\"baseline\"alt=donor title=donor $icon_style />";
    if ($CURUSER['warned'] == "yes")
        $icon .= "<img src=\"themes/dotBlue/pic/warned.png\" align=\"baseline\"  alt=warned title=warned $icon_style />";
    if ($CURUSER["webseeder"] == "yes")
        $icon .= "<img src=\"themes/dotBlue/pic/webseeder.png\" align=\"baseline\"  alt=webseeder title=webseeder $icon_style/>";
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
    ////////////////function hey by putyn
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

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:none;display:block;border-collapse:collapse;">
<tr><td align="left" style="border:none; border-collapse:collapse;" valign="middle"  nowrap="nowrap">

<b>
 <?=hey()?>,&nbsp;<a href="userdetails.php?id=<?=$CURUSER["id"]?>"><?=$CURUSER["username"]?> </a>&nbsp;<?=$usrclass?>
 <?=$icon?>
 <b><?php echo $language['bonus'];?><a href="mybonus.php"><?=number_format($CURUSER['seedbonus'], 1)?></a></b>&nbsp;|
 <b><?php echo $language['slot'];?><a href="#"><?=number_format($CURUSER['freeslots'])?></a></b>&nbsp;|
 <b><?php echo $language['conn'];?><?=$connectable?>&nbsp;|</b>
 <?php if ($invites > 0) {
        ?>
   <b><?php echo $language['inv'];?><a href=invite.php> <?=$invites?> </a></b>&nbsp;|
 <?php }
    ?>
 <b><?php echo $language['ratio'];?><?=$ratio?>|&nbsp;<font style="color:#006600">U:</font> <?=$uped?> <font style="color:#990000">D:</font> <?=$downed?>| <img src="pic/arrowdown.gif" width="9" height="11" alt="downloding torrents" title="downloding torrents"/><?=$activeleech?>&nbsp;<img src="pic/arrowup.gif" width="9" height="11" alt="seeding torrents" title="seeding torrents" /><?=$activeseed?>|</b>
</td>
<td align="right" nowrap="nowrap" width="100%" style="border:none; border-collapse:collapse;padding-right:10px">
<img src="ajax/online.png" id="show_online" onclick="friends_online();" title="Show your online friends" />
<div id="online_friends" style="display:none;width:120px;z-index:200;position:absolute"></div>
<span id="clock"></span>
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
<?php
if (!empty($unread))
    $inbox = ($unread == 1 ? "$unread&nbsp;New Message&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;" : "$unread&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (New Pm)' src=pic/pn_inboxnew.gif></a>&nbsp;&nbsp;");
    else
    $inbox = "0&nbsp;New Messages&nbsp;<a href=$BASEURL/messages.php?action=viewmailbox><img height=\"16\" style=border:none alt=inbox title='inbox (No new PMs)' src=pic/pn_inbox.gif></a>&nbsp;&nbsp;";
    echo $inbox;
?>
		<a href="#" onclick="themes();"><img src="themes/dotBlue/pic/theme.png" width="16" height="16" alt=" " title="Change your theme" border="0"/></a>
		<a href="bookmarks.php"><img src="themes/dotBlue/pic/bookmarks.png" width="16" height="16" alt=" " title="Bookmarked torrents" border="0"/></a>&nbsp;
<a href="friends.php"><img style="border:none" alt="Buddylist" title="Buddylist" src="themes/dotBlue/pic/friends.png" width="16" height="16" /></a>&nbsp;
<a href="rss.php?userid=<?=$CURUSER["id"]?>"><img src="themes/dotBlue/pic/rss.png" border="0" title="RSS"/></a>&nbsp;
<a href="logout.php" onClick="return log_out()"><img src="themes/dotBlue/pic/logout.png" border="0" title="Go out and play"/></a>


</td>
</tr>

</table>



</div>
<?php }
?>
<!--status bar end-->


<table style="border:none;border-collapse:collapse; background:none;" width="100%" cellpadding="5" cellspacing="0" align="center" border="0">
  <tr>
    <td align="center" style="border:none;padding:18px;"><img src="themes/dotBlue/pic/logo.png" width="597" border="0" height="151" alt=" " usemap="#logo"  /></td>
  </tr>
</table>
<map name="logo" id="logo"><area shape="rect" coords="129,111,515,140" href="<?=$DEFAULTBASEURL . "index.php"?>" />
</map>
<!--here the menu stars-->

<table width="<?=$maxwidth?>" style="height:47px; border:none;" cellpadding="0" cellspacing="0" align="center">
<tr>
	<td style="background:url(themes/dotBlue/pic/menu_1.png) no-repeat; width:34px;border:none;"><div style="display:block; border:none; min-width:34px"></div></td>
    <td style="background:url(themes/dotBlue/pic/menu_2.png) repeat-x; height:47px;border:none;">

        <table width="100%" style="border:none;" cellpadding="5" cellspacing="0" align="center">
		<tr>
            <td align="center" class="navigation"><a href=index.php>Home</a></td>
            <td align="center" class="navigation"><a href=browse.php>Browse</a></td>
            <td align="center" class="navigation"><a href=chat.php>Irc</a></td>
            <?php
if (get_user_class() >= UC_POWER_USER) {
    ?>
            <td align="center" class="navigation"><a href="requests.php">Request</a></td>
            <?php }
?>
            <td align="center" class="navigation"><a href=viewoffers.php>Offer</a></td>
            <td align="center" class="navigation"><a href=upload.php>Upload</a></td>
            <td align="center" class="navigation"><a href=usercp.php>Profile</a></td>
            <td align="center" class="navigation"><a href=forums.php>Forum</a></td>
            <td align="center" class="navigation"><a href=helpdesk.php>Help</a></td>
            <?php
if (get_user_class() >= UC_POWER_USER) {
    ?>
            <td align="center" class="navigation"><a href="topten.php">Top10</a></td>
            <?php }
?>
            <td align="center" class="navigation"><a href=rules.php>Rules</a></td>
            <td align="center" class="navigation"><a href=faq.php>Faq</a></td>
            <td align="center" class="navigation"><a href=links.php>Links</a></td>
            <?php
echo "<td align=center class=navigation>" . ($CURUSER['show_shout'] === 'no' ? "<a class=normal href=shoutbox.php?show_shout=1&amp;show=yes>Chat on</a>" : "<a class=normal href=shoutbox.php?show_shout=1&amp;show=no>Chat off</a>") . " </td>";

?>
            <td align="center" class="navigation"><a href=staff.php>Staff</a></td>
            <?php
if (get_user_class() >= UC_MODERATOR) {
    ?>
            <td align="center" class="navigation"><a href="staffpanel.php">Admin</a></td>
            <?php }
?>
        </table></td>
    <td style="background:url(themes/dotBlue/pic/menu_3.png) no-repeat; width:32px; border:none;"><div style="display:block; border:none; min-width:32px"></div></td>

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

<table width=760 border=1 cellspacing=0 cellpadding="0" style="margin-left:auto; margin-right:auto;">
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
<table width='752' border='0' cellspacing='0' cellpadding='1'>
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
