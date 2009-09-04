<?php
// CyBerFuN.Ro
// By CyBerNe7
//            //
// www.cyberfun.ro //
////////////////////////////////////////////////////////
// Bonus Mod by TvRecall.org
// Bonus Mod Updated by devin
// Version 0.3
// Updated 01/05/2006
// under GPL-License
///////////////////////////////////////////////////////
/******************************************************
Total credit to TvRecall for writing this fine mod in the first place,
the mod has since been altered with code and input by:
devinkray - cddvdheaven - DRRRR - vlahdr - sherl0k - okiee - lords - XiaNYdE
dopeydwerg - WRK - Fantomax - porthos - dokty - xam - wicked & Sir_SnuggleBunny

UPDATED!!! Wed, Nov 28th 2007
*******************************************************/
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/function_bonus.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
parked();
getpage();
function I_smell_a_rat($var){
if ((0 + $var) == 1)
$var = 0 + $var;
else
stderr("Error", "I smell a rat!");
}

function write_bonus_log($userid, $amount, $type){
  $added = sqlesc(get_date_time());
  $donation_type = $type;
  sql_query("INSERT INTO bonuslog (id, donation, type, added_at) VALUES('$userid', '$amount', '$donation_type', $added)") or sqlerr(__FILE__, __LINE__);
}

$bonus = safeChar($CURUSER['seedbonus'], 1);
$ubonus = safeChar($CURUSER['uploaded'], 1);
/////////freeleech
if ($_GET["freeleech_success"]){
$freeleech_success = 0 + $_GET["freeleech_success"];
if($freeleech_success != '1' && $freeleech_success != '2')
stderr("Error", "I smell a rat on freeleech!");
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
if($freeleech_success == '1'){

if ($_GET["norefund"] != '0') {
echo("<table width=80%><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>".
"<td align=left class=clearalt6><img src=".$pic_base_url."smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b>
$CURUSER[username] you have set the tracker <b>Free Leech !</b> <img src=".$pic_base_url."/smilies/w00t.gif alt=w00t><br><br>Rest of $_GET[norefund] points have been contributed towards the next freeleech automatically!".
"<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>".
"</td></tr></table>");
stdfoot();
} else {
echo("<table width=80%><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>".
"<td align=left class=clearalt6><img src=".$pic_base_url."smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b>
$CURUSER[username] you have set the tracker <b>Free Leech !</b> <img src=".$pic_base_url."/smilies/w00t.gif alt=w00t><br>".
"<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>".
"</td></tr></table>");
stdfoot();
}

die;
}
if($freeleech_success == '2'){
echo("<table width=80%><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>".
"<td align=left class=clearalt6><img src=".$pic_base_url."smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b>".
"$CURUSER[username] you have contributed towards making the tracker Free Leech ! <img src=".$pic_base_url."/smilies/w00t.gif alt=w00t><br>".
"<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>".
"</td></tr></table>");
stdfoot();
die;
}
}
////////doubleup
if ($_GET["doubleup_success"]){
$doubleup_success = 0 + $_GET["doubleup_success"];
if($doubleup_success != '1' && $doubleup_success != '2')
stderr("Error", "I smell a rat on freeleech!");
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
if($doubleup_success == '1'){

if ($_GET["norefund"] != '0') {
echo("<table width=80%><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>".
"<td align=left class=clearalt6><img src=".$pic_base_url."smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b>
$CURUSER[username] you have set the tracker <b>Double Up !</b> <img src=".$pic_base_url."/smilies/w00t.gif alt=w00t><br><br>Rest of $_GET[norefund] points have been contributed towards the next Double upload period automatically!".
"<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>".
"</td></tr></table>");
stdfoot();
} else {
echo("<table width=80%><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>".
"<td align=left class=clearalt6><img src=".$pic_base_url."smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b>
$CURUSER[username] you have set the tracker <b>Double Up !</b> <img src=".$pic_base_url."/smilies/w00t.gif alt=w00t><br>".
"<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>".
"</td></tr></table>");
stdfoot();
}

die;
}
if($doubleup_success == '2'){
echo("<table width=80%><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>".
"<td align=left class=clearalt6><img src=".$pic_base_url."smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b>".
"$CURUSER[username] you have contributed towards making the tracker Double Upload ! <img src=".$pic_base_url."/smilies/w00t.gif alt=w00t><br>".
"<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>".
"</td></tr></table>");
stdfoot();
die;
}
}
//////////
switch (true){
case (isset($_GET['up_success']));{

I_smell_a_rat($_GET['up_success']);
}
$amt = (int)$_GET['amt'];

switch ($amt) {
case $amt == 275.0:
$amt = '1 GB';
break;
case $amt == 350.0:
$amt = '2.5 GB';
break;
case $amt == 550.0:
$amt = '5 GB';
break;
case $amt == 1000.0:
$amt = '10 GB';
break;
case $amt == 2000.0:
$amt = '25 GB';
break;
case $amt == 4000.0:
$amt = '50 GB';
break;
case $amt == 8000.0:
$amt = '100 GB';
break;
case $amt == 40000.0:
$amt = '520 GB';
break;
default:
$amt = '1 TB';
}

stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead2 align=left colspan=2><h1>Success!</h1></td></tr><tr>'.
'<td class=clearalt6 align=left><img src=pic/smilies/karma.gif alt=good_karma></td>'.
'<td class=clearalt6 align=left><b>Congratulations ! </b>'.$CURUSER['username'].' you have just increased your upload amount by '.$amt.'!'.
' <img src=pic/smilies//w00t.gif alt=w00t><br><br><br><br> click to go back to your '.
'<a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;

case (isset($_GET['class_success'])):{
I_smell_a_rat($_GET['class_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr>'.
'<tr><td align=left class=clearalt6><img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you have got yourself VIP Status for one month! <img src=pic/smilies/w00t.gif alt=w00t><br>'.
'<br> Click to go back to your <a class=altlink href=mybonus.php>Karma Points</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['smile_success'])):{
I_smell_a_rat($_GET['smile_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr>'.
'<tr><td align=left class=clearalt6><img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you have got yourself a set of custom smilies for one month! <img src=pic/smilies/w00t.gif alt=w00t><br>'.
'<br> Click to go back to your <a class=altlink href=mybonus.php>Karma Points</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['warning_success'])):{
I_smell_a_rat($_GET['warning_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr>'.
'<tr><td align=left class=clearalt6><img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you have removed your warning for the low price of 1000 points!! <img src=pic/smilies/w00t.gif alt=w00t><br>'.
'<br> Click to go back to your <a class=altlink href=mybonus.php>Karma Points</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['invite_success'])):{
I_smell_a_rat($_GET['invite_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you have got your self 3 new invites! <img src=pic/smilies/w00t.gif alt=w00t><br><br>'.
' click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['title_success'])):{
I_smell_a_rat($_GET['title_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>'.
'<td align=left class=clearalt6><img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you are now known as <b>'.$CURUSER['title'].'</b>! <img src=pic/smilies/w00t.gif alt=w00t><br>'.
'<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['ratio_success'])):{
I_smell_a_rat($_GET['ratio_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr>'.
'<td align=left class=clearalt6><img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! </b> '.$CURUSER['username'].' you'.
' have gained a 1 to 1 ratio on the selected torrent, and the difference in MB has been added to your total upload! <img src=pic/smilies/w00t.gif alt=w00t><br>'.
'<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br>'.
'</td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_fail'])):{
I_smell_a_rat($_GET['gift_fail']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Huh?</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/oops.gif alt=good_karma></td><td align=left class=clearalt6><b>Not so fast there Mr. fancy pants!</b><br>'.
'<b>'.$CURUSER['username'].'...</b> you can not spread the karma to yourself...<br>If you want to spread the love, pick another user! <br>'.
'<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_fail1'])):{
I_smell_a_rat($_GET['gift_fail1']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Huh?</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/oops.gif alt=good_karma></td><td align=left class=clearalt6><b>Not so fast there Mr. fancy pants!</b><br>'.
'<b>'.$CURUSER['username'].'...</b> you can not give upload credit to yourself...<br>If you want to spread the gig love, pick another user! <br>'.
'<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_fail2'])):{
I_smell_a_rat($_GET['gift_fail2']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Huh?</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/oops.gif alt=good_karma></td><td align=left class=clearalt6><b>You can\'t give nothing or a minus value!</b><br>'.
'<b>'.$CURUSER['username'].'...</b> ...<br>If you want to spread the gig love, enter a value! <br>'.
'<br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_fail_user'])):{
I_smell_a_rat($_GET['gift_fail_user']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Error</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/oops.gif alt=good_karma></td><td align=left class=clearalt6><b>Sorry '.$CURUSER['username'].'...</b>'.
'<br> No User with that username <br><br> click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.'.
'<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_fail_points'])):{
I_smell_a_rat($_GET['gift_fail_points']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>O0PS!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/cry.gif alt=oops></td><td align=left class=clearalt6><b>Sorry </b>'.$CURUSER['username'].' you don\'t have enough Karma points'.
'<br> go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_fail_points1'])):{
I_smell_a_rat($_GET['gift_fail_points1']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>O0PS!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/cry.gif alt=oops></td><td align=left class=clearalt6><b>Sorry </b>'.$CURUSER['username'].' you don\'t have enough Upload credit'.
'<br> go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_success'])):{
I_smell_a_rat($_GET['gift_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! '.$CURUSER['username'].' </b>'.
'you have spread the Karma well.<br><br>Member <b>'.safeChar($_GET['usernamegift']).'</b> will be pleased with your kindness!<br><br>This is the message that was sent:<br>'.
'<b>Subject:</b> Someone Loves you!<br> <p>You have been given a gift of <b>'.(0 + $_GET['gift_amount_points']).'</b> Karma points by '.$CURUSER['username'].'</p><br>'.
'You may also <a class=altlink href=sendmessage.php?receiver='.(0 + $_GET['gift_id']).'>send '.safeChar($_GET['usernamegift']).' a message as well</a>, or go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['gift_success1'])):{
I_smell_a_rat($_GET['gift_success1']);
}
$mb_basic=1024*1024;
$nobits1 = (0 + $_GET['gift_amount_points1']*$mb_basic*1024);
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6><b>Congratulations! '.$CURUSER['username'].' </b>'.
'you have spread your gigs well.<br><br>Member <b>'.safeChar($_GET['usernamegift']).'</b> will be pleased with your kindness!<br><br>This is the message that was sent:<br>'.
'<b>Subject:</b> Someone Loves you!<br> <p>You have been given a gift of <b>'.prefixed($nobits1).'</b> Upload credit by '.$CURUSER['username'].'</p><br>'.
'You may also <a class=altlink href=sendmessage.php?receiver='.(0 + $_GET['gift_id']).'>send '.safeChar($_GET['usernamegift']).' a message as well</a>, or go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;

case (isset($_GET['freeslots_success'])):{
I_smell_a_rat($_GET['freeslots_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you have got your self 3 freeleech slots! <img src=pic/w00t.gif alt=w00t><br><br>'.
' click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;

case (isset($_GET['gift_fail_itrade'])):{
I_smell_a_rat($_GET['gift_fail_itrade']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>OOPS!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/cry.gif alt=oops></td><td align=left class=clearalt6><b>Sorry </b>'.$CURUSER['username'].' you don\'t have any Invites'.
'<br> go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
case (isset($_GET['itrade_success'])):{
I_smell_a_rat($_GET['itrade_success']);
}
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead align=left colspan=2><h1>Success!</h1></td></tr><tr><td align=left class=clearalt6>'.
'<img src=pic/smilies/karma.gif alt=good_karma></td><td align=left class=clearalt6>'.
'<b>Congratulations! </b>'.$CURUSER['username'].' you have got your self 200 points! <img src=pic/smilies/w00t.gif alt=w00t><br><br>'.
' click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;

case (isset($_GET['dlreset_success']));{
I_smell_a_rat($_GET['dlreset_success']);
}
$amt = (int)$_GET['amt'];
switch ($amt) {
case $amt == 50000.0:
default:
$amt = '1 Byte';
break;
}

stdhead($CURUSER['username'] . "'s Karma Bonus Page");
echo'<table width=756><tr><td class=colhead2 align=left colspan=2><h1>Success!</h1></td></tr><tr>'.
'<td class=clearalt6 align=left><img src=pic/smilies/karma.gif alt=good_karma></td>'.
'<td class=clearalt6 align=left><b>Congratulations ! </b>'.$CURUSER['username'].' you have just reset your total downloaded amount to '.$amt.'!'.
' <img src=pic/smilies//w00t.gif alt=w00t><br><br><br><br> click to go back to your '.
'<a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br><br></td></tr></table>';
stdfoot();
die;
}

//=== exchange
if ($_GET['exchange']){
I_smell_a_rat($_GET['exchange']);

$userid = 0 + $CURUSER['id'];
if (!is_valid_id($userid))
stderr("Error", "That is not your user ID!");

$option = 0 + $_POST['option'];

$res_points = sql_query("SELECT * FROM bonus WHERE id =" . sqlesc($option));
$arr_points = mysql_fetch_assoc($res_points);

$art = $arr_points['art'];
$points = $arr_points['points'];

if ($points <= 0)
stderr("Error", "I smell a rat!");

$seedbonus=safeChar($bonus-$points,1);
$upload = $CURUSER['uploaded'];
$bonuscomment = $CURUSER['bonuscomment'];

if($bonus < $points) 
stderr("Sorry", "you do not have enough Karma points!");

switch ($art){

case 'traffic':
//=== trade for one upload credit
$up = $upload + $arr_points['menge'];
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for upload bonus.\n " .$bonuscomment;
sql_query("UPDATE users SET uploaded = $upload + $arr_points[menge], seedbonus = '$seedbonus', lastchange='" . get_date_time() . "', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?up_success=1&amt=$points");
die;
break;

//--- doubleupload
case 'doubleup':
$points2 = 199999;
$pointspool = $arr_points['pointspool'];
$points= safeChar($points,1);
$donation = 0 + $_POST['donate'];
$seedbonus = ($bonus - $donation);
if($bonus < $donation && $bonus < $donation || $donation <= 0 ||$donation > $points2){
stderr("Error", " <br>Points: ".safeChar($donation)." <br> Bonus: ".safeChar($bonus)." <br> Donation: ".safeChar($donation)." <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br>");
die;
}

if(($pointspool+$donation) >= $arr_points["points"] ){
$now = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
$end = mktime(date("H"),date("i"),date("s"),date("m"),date("d")+3,date("Y"));
$message = "DoubleUpload [ON]";
$sql = "INSERT INTO `events`(`userid`,`overlayText`, `startTime`, `endTime`, `displayDates`, `duploadEnabled`) VALUES ('$userid', '$message', '$now', '$end', '1', '1');";
$norefund = ($donation + $pointspool) % $points;
sql_query($sql)or sqlerr(__FILE__, __LINE__);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points contributed for doubleupload.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = '$seedbonus',  bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
$sql2 = "UPDATE bonus SET pointspool = '$norefund' WHERE id = '23' LIMIT 1";
sql_query($sql2)or sqlerr(__FILE__, __LINE__);
write_bonus_log($CURUSER["id"], $donation, $type = "doubleupload");
header("Refresh: 0; url=$BASEURL/mybonus.php?doubleup_success=1&norefund=$norefund");
die;

} else {
// add to the pool
$sql = "UPDATE bonus SET pointspool = pointspool + '$donation' WHERE id = '23' LIMIT 1";
sql_query($sql)or sqlerr(__FILE__, __LINE__);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points contributed for doubleupload.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
write_bonus_log($CURUSER["id"], $donation, $type = "doubleupload");
header("Refresh: 0; url=$BASEURL/mybonus.php?doubleup_success=2");
die;
}
die;
break;
//--- Freeleech
case 'freeleech':
$points2 = 199999;
$pointspool = $arr_points['pointspool'];
$points= safeChar($points,1);
$donation = 0 + $_POST['donate'];
$seedbonus = ($bonus - $donation);
if($bonus < $donation && $bonus < $donation || $donation <= 0 ||$donation > $points2){
stderr("Error", " <br>Points: ".safeChar($donation)." <br> Bonus: ".safeChar($bonus)." <br> Donation: ".safeChar($donation)." <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.<br>");
die;
}

if(($pointspool+$donation) >= $arr_points["points"] ){
$now = mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));
$end = mktime(date("H"),date("i"),date("s"),date("m"),date("d")+3,date("Y"));
$message = "FreeLeech [ON]";
$sql = "INSERT INTO `events`(`userid`,`overlayText`, `startTime`, `endTime`, `displayDates`, `freeleechEnabled`) VALUES ('$userid', '$message', '$now', '$end', '1', '1');";
$norefund = ($donation + $pointspool) % $points;
sql_query($sql)or sqlerr(__FILE__, __LINE__);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points contributed for freeleech.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = '$seedbonus',  bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
$sql2 = "UPDATE bonus SET pointspool = '$norefund' WHERE id = '21' LIMIT 1";
sql_query($sql2)or sqlerr(__FILE__, __LINE__);
write_bonus_log($CURUSER["id"], $donation, $type = "freeleech");
header("Refresh: 0; url=$BASEURL/mybonus.php?freeleech_success=1&norefund=$norefund");
die;

} else {
// add to the pool
$sql = "UPDATE bonus SET pointspool = pointspool + '$donation' WHERE id = '21' LIMIT 1";
sql_query($sql)or sqlerr(__FILE__, __LINE__);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points contributed for freeleech.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
write_bonus_log($CURUSER["id"], $donation, $type = "freeleech");
header("Refresh: 0; url=$BASEURL/mybonus.php?freeleech_success=2");
die;
}
die;
break;

case 'ratio':
//=== trade for one torrent 1:1 ratio
$torrent_number = 0 + $_POST['torrent_id'];
$res_snatched = sql_query("SELECT s.uploaded, s.downloaded, t.name FROM snatched AS s LEFT JOIN torrents AS t ON t.id = s.torrentid WHERE s.userid = '$userid' AND torrentid = ".sqlesc($torrent_number)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
$arr_snatched = mysql_fetch_assoc($res_snatched);
if ($arr_snatched['name'] == '')
stderr("Error", "No torrent with that ID!<br>Back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.");
if ($arr_snatched['uploaded'] >= $arr_snatched['downloaded'])
stderr("Error", "Your ratio on that torrent is fine, you must have selected the wrong torrent ID.<br>Back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page.");
sql_query("UPDATE snatched SET uploaded = '$arr_snatched[downloaded]' WHERE userid = '$userid' AND torrentid = ".sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
$difference = $arr_snatched['downloaded'] - $arr_snatched['uploaded'];
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for 1 to 1 ratio on torrent: ".$arr_snatched['name']." ".$torrent_number.", ".$difference." added .\n " .$bonuscomment;
sql_query("UPDATE users SET uploaded = $upload + $difference, lastchange='" . get_date_time() . "', bonuscomment = '$bonuscomment', seedbonus = '$seedbonus' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?ratio_success=1");
die;
    break;
case 'class':
//=== trade for one month VIP status
if ($CURUSER['class'] > UC_VIP)
stderr("Error", "Now why would you want to lower yourself to VIP?<br>go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page and think that one over.");
$vip_until = get_date_time(gmtime() + 28*86400);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for 1 month VIP Status.\n " .$bonuscomment;
sql_query("UPDATE users SET class = ".UC_VIP.", vip_added = 'yes', vip_until = '$vip_until', lastchange='" . get_date_time() . "', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?class_success=1");
die;
break;
case 'warning':
//=== trade for removal of warning :P
if ($CURUSER['warned'] == 'no')
stderr("Error", "How can we remove a warning that isn't there?<br>go back to your <a class=altlink href=mybonus.php>Karma Bonus Point</a> page and think that one over.");
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for removing warning.\n " .$bonuscomment;
$res_warning = sql_query("SELECT modcomment FROM users WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
$modcomment = safeChar($arr['modcomment']);
$modcomment = gmdate("Y-m-d") . " - warning removed by -Bribe with Karma.\n". $modcomment;
$modcom = sqlesc($modcomment);
sql_query("UPDATE users SET warned = 'no', warneduntil = '0000-00-00 00:00:00', lastchange='" . get_date_time() . "', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment', modcomment = $modcom WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
$dt = sqlesc(get_date_time());
$subject = sqlesc("Warning removed by Karma.");
$msg = sqlesc("Your warning has been removed by the big Karma payoff... Please keep on your best behaviour from now on.\n");
sql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, $userid, $dt, $msg, $subject)") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?warning_success=1");
die;
break;
case 'smile':
//=== trade for one month special smilies :P
$smile_until = get_date_time(gmtime() + 28*86400);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for 1 month of custom smilies.\n " .$bonuscomment;
sql_query("UPDATE users SET smile_until = '$smile_until', lastchange='" . get_date_time() . "', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?smile_success=1");
die;
break;
case 'invite':
//=== trade for invites
$invites = $CURUSER['invites'];
$inv = $invites+$arr_points['menge'];
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for invites.\n " .$bonuscomment;
sql_query("UPDATE users SET invites = '$inv', lastchange='" . get_date_time() . "', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?invite_success=1");
die;
break;
case 'title':
//=== trade for special title
/**** the $words array are words that you DO NOT want the user to have... use to filter "bad words" & user class...
the user class is just for show, but what the hell :p Add more or edit to your liking.
*note if they try to use a restricted word, they will recieve the special title "I just wasted my karma" *****/

$title = sqlesc(htmlentities($_POST['title']));
$words = array('fuck', 'shit', 'Moderator', 'Administrator', 'Admin', 'pussy', 'Sysop', 'cunt', 'nigger', 'VIP', 'Super User', 'Power User', 'ADMIN', 'SYSOP', 'MODERATOR', 'ADMINISTRATOR', 'cyberfun');
$title = str_replace($words, "I just wasted my karma", $title);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for custom title. old title was $CURUSER[title] new title is $title\n " .$bonuscomment;
sql_query("UPDATE `users` SET `title` = '$title',
`seedbonus` = '$seedbonus',
`bonuscomment` = '$bonuscomment',
`lastchange` = '" . get_date_time() . "' WHERE `users`.`id` =$userid LIMIT 1 ;") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?title_success=1");
die;
break;
case 'gift_1':
//=== trade for giving the gift of karma
$points = 0 + $_POST['bonusgift'];
$usernamegift = htmlentities(trim($_POST['username']));
$res = sql_query("SELECT id,seedbonus,bonuscomment,username FROM users WHERE username=" . sqlesc($usernamegift));
$arr = mysql_fetch_assoc($res);
$useridgift = $arr['id'];
$userseedbonus = $arr['seedbonus'];
$bonuscomment_gift = $arr['bonuscomment'];
$usernamegift = $arr['username'];

$check_me = array(100,200,300,400,500,1000);
if (!in_array($points, $check_me))
stderr("Error", "I smell a rat!");

if($bonus >= $points){
$points= safeChar($points,1);
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points as gift to $usernamegift .\n " .$bonuscomment;
$bonuscomment_gift = gmdate("Y-m-d") . " - recieved " .$points. " Points as gift from $CURUSER[username] .\n " .$bonuscomment_gift;
$seedbonus=$bonus-$points;
$giftbonus1=$userseedbonus+$points;
if ($userid==$useridgift){
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail=1");
die;
}
if (!$useridgift){
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail_user=1");
die;
}
sql_query("SELECT bonuscomment,id FROM users WHERE id = '$useridgift'") or sqlerr(__FILE__, __LINE__);
//=== and to post to the person who gets the gift!
sql_query("UPDATE users SET seedbonus = '$seedbonus', lastchange='" . get_date_time() . "', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE users SET seedbonus = '$giftbonus1', lastchange='" . get_date_time() . "', bonuscomment = '$bonuscomment_gift' WHERE id = '$useridgift'");
//===send message
$subject = sqlesc("Someone Loves you");
$added = sqlesc(get_date_time());
$msg = sqlesc("You have been given a gift of $points Karma points by ".$CURUSER['username']);
sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $useridgift, $msg, $added)") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_success=1&gift_amount_points=$points&usernamegift=$usernamegift&gift_id=$useridgift");
die;
}
else{
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail_points=1");
die;
}
break;
case 'gift_2':
$upgift = 0 + $_POST['unit'];
$usernamegift = htmlentities(trim($_POST['username']));
$res = sql_query("SELECT id,uploaded,bonuscomment,username FROM users WHERE username=" . sqlesc($usernamegift));
$arr = mysql_fetch_assoc($res);
$useridgift = $arr['id'];
$userupload = $arr['uploaded'];
$bonuscomment_gift = $arr['bonuscomment'];
$usernamegift = $arr['username'];
$mb_basic=1024*1024;

if(isset($_POST['unit'])){
if ($_POST["unit"] =='2')
$nobits = $_POST["amnt"]*$mb_basic*1024;
}
$amt1 = $_POST["amnt"];
if($ubonus >= $nobits){
$upgift= safeChar($upgift,1);
$bonuscomment = gmdate("Y-m-d") . " - " .prefixed($nobits). " Upload Credit as gift to $usernamegift .\n " .$bonuscomment;
$bonuscomment_gift = gmdate("Y-m-d") . " - recieved " .prefixed($nobits). " Upload Credit as gift from $CURUSER[username] .\n " .$bonuscomment_gift;
$upbonus=$ubonus-$nobits;
$upbonus1=$userupload+$nobits;
if ($userid==$useridgift){
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail1=1");
die;
}
if (!$useridgift){
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail_user=1");
die;
}
if ($amt1 <= 0){
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail2=1");
die;
}
sql_query("SELECT bonuscomment,id FROM users WHERE id = '$useridgift'") or sqlerr(__FILE__, __LINE__);
//=== and to post to the person who gets the gift!
sql_query("UPDATE users SET uploaded = '$upbonus', lastchange='" . get_date_time() . "', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE users SET uploaded = '$upbonus1', lastchange='" . get_date_time() . "', bonuscomment = '$bonuscomment_gift' WHERE id = '$useridgift'");
//===send message
$subject = sqlesc("Someone Loves you");
$added = sqlesc(get_date_time());
$msg = sqlesc("You have been given a gift of " .prefixed($nobits). " Upload Credit by ".$CURUSER['username']);
sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $useridgift, $msg, $added)") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_success1=1&gift_amount_points1=$amt1&usernamegift=$usernamegift&gift_id=$useridgift");
die;
}
else{
header("Refresh: 0; url=$BASEURL/mybonus.php?gift_fail_points1=1");
die;
}
 case 'freeslots':
//=== trade for freeslots
$freeslots = $CURUSER['freeslots'];
$slots = $freeslots+$arr_points['menge'];
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for freeslots.\n " .$bonuscomment;
sql_query("UPDATE users SET freeslots = '$slots', lastchange='" . get_date_time() . "', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?freeslots_success=1");
die;
break;

case 'itrade':
//=== trade for points
$invites = $CURUSER['invites'];
$inv = $invites+$arr_points['menge'];
if ($invites == 0)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " invites for bonus points.\n" .$bonuscomment;
sql_query("UPDATE users SET invites = invites - 1, lastchange='" . get_date_time() . "', seedbonus = seedbonus + 200 WHERE id = '$userid' AND invites = '$invites +1'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?itrade_success=1");
die;
break;
case 'trafficres':
//=== trade for download reset
$downloaded1 = $CURUSER['downloaded'];
$downloaded = 1 + $arr_points['menge'];
if ($downloaded1 == 1)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " Points for Download reset.\n " .$bonuscomment;
sql_query("UPDATE users SET downloaded = $downloaded + $arr_points[menge], lastchange='" . get_date_time() . "', seedbonus = '$seedbonus', bonuscomment = '$bonuscomment' WHERE id = '$userid'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?dlreset_success=1&amt=$points");
die;
break;
case 'itrade2':
//=== trade for slots
$invites = $CURUSER['invites'];
$inv = $invites+$arr_points['menge'];
if ($invites == 0)
stderr("Error", "I smell a rat!");
$bonuscomment = gmdate("Y-m-d") . " - " .$points. " invites for bonus points.\n" .$bonuscomment;
sql_query("UPDATE users SET invites = invites - 1, lastchange='" . get_date_time() . "', freeslots = freeslots + 2 WHERE id = '$userid' AND invites = '$invites +1'") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=$BASEURL/mybonus.php?itrade2_success=1");
die;
break;
}
}
//==== this is the default page
stdhead($CURUSER['username'] . "'s Karma Bonus Page");
$sql = "SELECT id,SUM(donation) as total FROM `bonuslog` WHERE `type` = 'freeleech' OR 'doubleupload' GROUP BY `id` ORDER BY total DESC LIMIT 10;";
$res2 = sql_query($sql);
while($row = mysql_fetch_assoc($res2)){
$top_donators_id = $row["id"];
$damount_donated = $row["total"];
$top_donators_username = get_user_name($top_donators_id);
$top_donators .= "<li><a href=userdetails.php?id=$top_donators_id>" . $top_donators_username["username"] . "</a> ( $damount_donated )</li>";
}
?>
FreeLeech [
            <?php

            if($freeleech_enabled){
                    echo "<b><font color=\"darkgreen\"><strong>ON </strong></font></b> ".date("d/m", $freeleech_start_time) . " - " . date("d/m", $freeleech_end_time);
                  } else {
                    echo "<b><font color=\"" . $fcolor . "\"><strong>" . $fstatus . "</strong></font></b>";
                    }
                  ?>
            ]
    DoubleUpload [
            <?php if($double_upload_enabled){
                    echo "<b><font color=\"darkgreen\"><strong>ON </strong></font></b>".date("d/m", $double_upload_start_time) . " - " . date("d/m", $double_upload_end_time);
                  } else {
                    echo "<b><font color=\"" . $dcolor . "\"><strong>" . $dstatus . "</strong></font></b>";
                    }
                  ?>
            ]
<?php
begin_frame();
$uploaded = $CURUSER['uploaded'];
echo'<table align=center width=756 border=1 cellspacing=0 cellpadding=5><tr><td class=colhead2 colspan=4>' . '<h1>' . $SITENAME . ' Karma Bonus Point system:</h1></td></tr><tr><td align=center colspan=4 class=clearalt6>' . 'Exchange your <a class=altlink href=mybonus.php>Karma/Upload Bonus Points</a> [ current ' . $bonus . '/' . prefixed($uploaded) . ' ] for goodies!' . '<br><br>[ If no buttons appear, you have not earned enough bonus points to trade. ]<br><br><tr>' . '<td class=colhead2 align=left>Description</td>' . '<td class=colhead2 align=center>Points</td><td class=colhead2 align=center>Trade</td></tr>';

$res = sql_query("SELECT * FROM bonus WHERE enabled = 'yes' ORDER BY id ASC");

while ($gets = mysql_fetch_assoc($res)){
//=======change colors
$count1= (++$count1)%2;
$class = 'clearalt'.($count1==0?'6':'7');

$otheroption = "<table width=100%><tr><td class=$class><b>Username:</b><input type=text name=username size=20 maxlength=24></td><td class=$class> <b>to be given: </b><select name=bonusgift> <option value=100.0> 100.0</option> <option value=200.0> 200.0</option> <option value=300.0> 300.0</option> <option value=400.0> 400.0</option><option value=500.0> 500.0</option><option value=1000.0> 1000.0</option></select> Karma points!</td></tr></table>";
$otheroption2 = "<table width=100%><tr><td class=$class><b>Username:</b><input type=text name=username size=20 maxlength=24></td><td class=$class> <b>Amount:</b><input type=text name=amnt size=8 maxlength=24><select name=unit> <option value=2> GB</option></select></td></tr></table>";
$otheroption_title = "<input type=text name=title size=30 maxlength=30>";
$otheroption_donate = "<input type=text name=donate size=10 maxlength=10>";
echo'<form action=mybonus.php?exchange=1 method=post>';

switch (true){
case ($gets['id'] == 5):
echo'<tr><td align=left class='.$class.'><h1><font color="#CECFF3">'.$gets['bonusname'].'</font></h1>'.$gets['description'].'<br><br>Enter the <b>Special Title</b> you would like to have '.$otheroption_title.' click Exchange! </td><td align=center class='.$class.'>'.$gets['points'].'</td>';
break;
case ($gets['id'] == 7):
echo'<tr><td align=left class='.$class.'><h1><font color="#CECFF3">'.$gets['bonusname'].'</font></h1>'.$gets['description'].'<br><br>Enter the <b>username</b> of the person you would like to send karma to, and select how many points you want to send and click Exchange!<br>'.$otheroption.'</td><td align=center class='.$class.'>min.<br>'.$gets['points'].'<br>max.<br>1000.0</td>';
break;
case ($gets['id'] == 9):
echo'<tr><td align=left class='.$class.'><h1><font color="#CECFF3">'.$gets['bonusname'].'</font></h1>'.$gets['description'].'</td><td align=center class='.$invites.'>min.<br>'.$gets['points'].'</td>';
break;
case ($gets['id'] == 10):
echo'<tr><td align=left class='.$class.'><h1><font color="#CECFF3">'.$gets['bonusname'].'</font></h1>'.$gets['description'].'<br><br>Enter the <b>ID number of the Torrent:</b> <input type=text name=torrent_id size=4 maxlength=8> you would like to buy a 1 to 1 ratio on.</td><td align=center class='.$class.'>min.<br>'.$gets['points'].'</td>';
break;
case ($gets['id'] == 20):
echo'<tr><td align=left class='.$class.'><h1><font color="#CECFF3">'.$gets['bonusname'].'</font></h1>'.$gets['description'].'<br><br>Enter the <b>username</b> of the person you would like to send the upload credit to, and select how many points you want to send and click Exchange!<br>'.$otheroption2.'</td><td align=center class='.$class.'>min.<br>'.$gets['points'].'<br></td>';
break;
case ($gets['id'] == 21):
echo'<tr><td align=left class='.$class.'><h1><font color=\"#CECFF3\">'.$gets["bonusname"].'</font></h1>'.$gets['description'].'<br><h4>Top 10 Contributors </h4> <ol>'.$top_donators.' </ol> <br>Enter the <b>amount to contribute</b> '.$otheroption_donate.'</td><td align=center class='.$class.'>' .$gets['points'] .' <br/> </td>';
break;
case ($gets['id'] == 23):
echo'<tr><td align=left class='.$class.'><h1><font color=\"#CECFF3\">'.$gets["bonusname"].'</font></h1>'.$gets['description'].'<br><h4>Top 10 Contributors </h4> <ol>'.$top_donators.' </ol> <br>Enter the <b>amount to contribute</b> '.$otheroption_donate.'</td><td align=center class='.$class.'>' .$gets['points'] .' <br/> </td>';
break;

default:
echo'<tr><td align=left class='.$class.'><h1><font color="#CECFF3">'.$gets['bonusname'].'</font></h1>'.$gets['description'].'</td><td align=center class='.$class.'>'.$gets['points'].'</td>';
}

echo'<input type=hidden name=option value='.$gets['id'].'> <input type=hidden name=art value='.$gets['art'].'>';
if ($gets['id'] == 18 || $gets['id'] == 19)
{
$bonus = $CURUSER['invites'];
}
else
{
$bonus = $CURUSER['seedbonus'];
}
if($bonus >= $gets['points']) {

switch (true){
case ($gets['id'] == 7):
echo'<td class='.$class.'><input class=button type=submit name=submit value="Karma Gift!"></form></td>';
break;
case ($gets['id'] == 20):
echo'<td class='.$class.'><input class=button type=submit name=submit value="Upload Gift!"></form></td>';
break;
case ($gets['id'] == 21 ):
echo'<td align=center class='.$class.'>'. ($gets['points'] - $gets['pointspool']) . ' <br/>Points needed! <br/><input class=button type=submit name=submit value="Contribute!"></form></td>';
break;
case ($gets['id'] == 23 ):
echo'<td align=center class='.$class.'>'. ($gets['points'] - $gets['pointspool']) . ' <br/>Points needed! <br/><input class=button type=submit name=submit value="Contribute!"></form></td>';
break;
default:
echo'<td class='.$class.'><input class=button type=submit name=submit value="Exchange!"></form></td>';
}
}
else
echo'<td class='.$class.' align=center><b>more points needed</b></form></td>';

}


echo'</table><br><br><br><table width=756><tr><td class=colhead2><h1>What the hell are these Karma Bonus points,'.
' and how do I get them?</h1></td></tr><tr><td class=clearalt6>- For every hour that you seed a torrent, you are awarded with 1'.
' Karma Bonus Point... <br>If you save up enough of them, you can trade them in for goodies like bonus GB(s) to '.
'your upload<br> stats,getting more invites, or doing the real Karma booster... give them to another user!<br>'.
'and yes! this is awarded on a per torrent basis even if there are no leechers on the Torrent you are seeding! <br>'.
'<h1>Other things that will get you karma points:</h1><ul><li>uploading a new torrent = 15 points</li><li>'.
'putting up an offer = 10 points</li><li>filling a request = 10 points</li><li>comment on torrent = 3 points</li>'.
'<li>saying thanks = 2 points</li><li>rating a torrent = 2 points</li><li>making a post = 1 point</li>'.
'<li>starting a topic = 2 points</li><li>voting on poll = 1 point</li>'.
'</ul><h1>Some things that will cost you karma points:</h1><ul>'.
'<li>upload credit</li><li>custom title</li>'.
'<li>one month VIP status</li><li>a 1:1 ratio on a torrent</li>'.
'<li>buying off your warning - Note dont waste your points buying off Hit and Run warnings the system will Automatically re-apply it</li><li>one month custom smilies for the forums and comments</li>'.
'<li>getting extra invites</li><li>Freeleech contribution - Max 199999 points - button can be seen if you have over 100000 points.</li><li>Double Upload contribution - Max 199999 points - button can be seen if you have over 100000 points.</li><li>Download reset</li><li>giving a gift of karma points to another user</li>'.
'<li>making a request</li></ul><p>But keep in mind that everything that can get'.
' you karma can also be lost, <br>ie:if you up a torrent then delete it, you will gain and then lose 15 points, <br>'.
'making a post and having it deleted will do the same<br><br>... and there are other hidden bonus karma points all '.
'over the site.<br><br>Yet another way to help out your ratio! </p><p>*please note, staff can give or take away '.
'points for breaking the rules, or doing good for the community.</p></td></tr></table><p align=center>'.
'<a class=altlink href=index.php>back to homepage</a></p></td>'; 
end_frame();
stdfoot();
?>
