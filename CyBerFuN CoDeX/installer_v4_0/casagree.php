<?php
//ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
//////////////////////////////////config
//////who is able to play just
//$player = UC_BANNED;
//$player = UC_LEECH;
//$player = UC_USER;
$player = UC_POWER_USER;
//$player = UC_VIP;
//$player = UC_UPLOADER;
//$player = UC_MODERATOR;
//$player = UC_ADMINISTRATOR;
//$player = UC_SYSOP;
//$player = UC_OWNER;
//$player = UC_CODER;
if (get_user_class() < $player)
stderr("Sorry ".$CURUSER["username"]  , "The MODERATOR do not allow your class to play casino. Power Users and above only.");
if ($_POST["agree"]=="Yes"){
mysql_query("UPDATE users SET casagree = 'yes' WHERE id = '".unsafeChar($CURUSER['id'])."'");
header("Location: $BASEURL/casino.php");
}elseif ($_POST["agree"]=="No"){
header("Location: $BASEURL/index.php");
}
////////////////////////////////////////////////standard html begin
stdhead(casino);
begin_main_frame();
begin_table();
echo("<form name=agree method=post action=$phpself>");
echo("<table width=\"700\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"embedded\">");
begin_frame("Casino Agreement");
tr("Yes I have read this agreement and understand this agreement, Please take me to the casino",'<input name="agree" type="radio" checked value="Yes">',1);
tr("No I do not agree with this agreement, return to the home page ",'<input name="agree" type="radio" value="No">',1);
tr("Submit:","<input type=submit value='Submit!' >",1);
echo("<center><h1>" . safeChar($CURUSER[username]) . "</h1></center>");
echo("<center><h2>Please read fully, You must pick yes to enter the casino<h2></center>");
echo("If you agree yes to this page you will not have to see it again.<br><br> WARNING: Using the casino can hurt your overall site ratio !!!<br><br>WARNING: You will be betting your upload credits.<br><br>If you loose a bet the amount of the bet will be deducted from your upload total.<br><br>Use the casino at your own risk, We are not responsible if you bet to much and cause yourself to loose upload credits<br><br>");
echo("You can lose power user status if you lose upload credit and drop below the min ratio for power user.<br><br>See FAQ for min ratio.<br><br>You can receive a ratio warning if your ratio is to low from losing in the casino.<br><br>Staff will not give you back lost upload credits.<br><br>");
echo("Winners are picked by random system and there is no set pattern.<br><br>Lost upload is either give to the winner or just removed from your account.<br><br>However if you win the amout won is added to your upload total.");
end_table();
end_frame();
end_main_frame();
stdfoot();
?>