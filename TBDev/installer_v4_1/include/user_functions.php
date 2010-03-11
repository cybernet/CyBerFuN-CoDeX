<?php
//if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
function check_banned_emails ($email) {
    $expl = explode("@", $email);
    $wildemail = "*@".$expl[1];
    /* Ban emails by x0r @tbdev.net */
    $res = sql_query("SELECT id, comment FROM bannedemails WHERE email = ".sqlesc($email)." OR email = ".sqlesc($wildemail)."") or sqlerr(__FILE__, __LINE__);
    if ($arr = mysql_fetch_assoc($res))
    stderr("Sorry..","This email address is banned!<br /><br /><strong>Reason</strong>: $arr[comment]", false);
}

function getpage() {
global $CURUSER;
$page = getenv("SCRIPT_NAME");
if ($CURUSER && $CURUSER["page_now"] <> $page)
sql_query("UPDATE users SET page_now = ".sqlesc($page)." WHERE id = $CURUSER[id]") or sqlerr(__FILE__,__LINE__);
}

function validusername($username)
{
	if ($username == "")
	  return false;

	// The following characters are allowed in user names
	$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	for ($i = 0; $i < strlen($username); ++$i)
	  if (strpos($allowedchars, $username[$i]) === false)
	    return false;

	return true;
}

/////////////function writestaff by putyn
function write_staff()
	{
		define("MAX_CLASS",8);
		define("STAFF_CLASS",4);
		$fnames = ROOT_PATH . "settings/STAFFNAMES";
		$fids = ROOT_PATH . "settings/STAFFIDS";
		$r = sql_query("select id,username,class FROM users WHERE class BETWEEN ".STAFF_CLASS." and ".MAX_CLASS." order by class DESC, username ASC") or print(mysql_error());
		while ($a = mysql_fetch_assoc($r))
		{
			$ids[] = $a["id"];
			if($a["class"] == MAX_CLASS)
			$names[] = $a["username"]; 
		}
		file_put_contents($fnames,join(" ",$names));
		file_put_contents($fids,join(" ",$ids));
	}

function get_user_icons($arr, $big = false)
{
    if ($big)
    {
        $donorpic = "starbig.gif";
        $hnrwarnpic = "warned9.gif";
        $leechwarnpic = "warnedbig.gif";
        $warnedpic = "warnedbig3.gif";
        $disabledpic = "disabledbig.gif";
        $style = "style='margin-left: 4pt'";
    }
    else
    {
        $donorpic = "star.gif";
        $hnrwarnpic = "warned6.gif";
        $warnedpic = "warned3.gif";
        $disabledpic = "disabled.gif";
        $style = "style=\"margin-left: 2pt\"";
    }
    $leechwarn ='';
    $pics = $arr["donor"] == "yes" ? "<img src=pic/$donorpic alt='Donor' border=0 $style>" : "";
    if ($arr["enabled"] == "yes")
        
        $pics .= ($arr["leechwarn"] == "yes" ? "<img src=pic/$leechwarnpic alt=\"Leechwarned\" border=0 $style>" : "") . ($arr["warned"] == "yes" ? "<img src=pic/$warnedpic alt=\"Warned\" border=0 $style>" : "") . ($arr["hnrwarn"] == "yes" ? "<img src=pic/$hnrwarnpic alt=\"Hit n Run Warned\" border=0 $style>" : "");
    else
        $pics .= "<img src=pic/$disabledpic alt=\"Disabled\" border=0 $style>\n";
    return $pics;
}

  function get_ratio_color($ratio)
  {
    if ($ratio < 0.1) return "#ff0000";
    if ($ratio < 0.2) return "#ee0000";
    if ($ratio < 0.3) return "#dd0000";
    if ($ratio < 0.4) return "#cc0000";
    if ($ratio < 0.5) return "#bb0000";
    if ($ratio < 0.6) return "#aa0000";
    if ($ratio < 0.7) return "#990000";
    if ($ratio < 0.8) return "#880000";
    if ($ratio < 0.9) return "#770000";
    if ($ratio < 1) return "#660000";
    if (($ratio >= 1.0) && ($ratio < 2.0)) return "#006600";
    if (($ratio >= 2.0) && ($ratio < 3.0)) return "#007700";
    if (($ratio >= 3.0) && ($ratio < 4.0)) return "#008800";
    if (($ratio >= 4.0) && ($ratio < 5.0)) return "#009900";
    if (($ratio >= 5.0) && ($ratio < 6.0)) return "#00aa00";
    if (($ratio >= 6.0) && ($ratio < 7.0)) return "#00bb00";
    if (($ratio >= 7.0) && ($ratio < 8.0)) return "#00cc00";
    if (($ratio >= 8.0) && ($ratio < 9.0)) return "#00dd00";
    if (($ratio >= 9.0) && ($ratio < 10.0)) return "#00ee00";
    if ($ratio >= 10) return "#00ff00";
    return "#777777";
  }

  function get_slr_color($ratio)
  {
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    if (($ratio >= 1.0) && ($ratio < 2.0)) return "#006600";
    if (($ratio >= 2.0) && ($ratio < 3.0)) return "#007700";
    if (($ratio >= 3.0) && ($ratio < 4.0)) return "#008800";
    if (($ratio >= 4.0) && ($ratio < 5.0)) return "#009900";
    if (($ratio >= 5.0) && ($ratio < 6.0)) return "#00aa00";
    if (($ratio >= 6.0) && ($ratio < 7.0)) return "#00bb00";
    if (($ratio >= 7.0) && ($ratio < 8.0)) return "#00cc00";
    if (($ratio >= 8.0) && ($ratio < 9.0)) return "#00dd00";
    if (($ratio >= 9.0) && ($ratio < 10.0)) return "#00ee00";
    if ($ratio >= 10) return "#00ff00";
    return "#777777";
  }


function get_user_class()
{
  global $CURUSER;
  return $CURUSER["class"];
}

function get_user_class_name($class)
{
global $duserclass0, $duserclass1, $duserclass2, $duserclass3, $duserclass4, $duserclass5, $duserclass6, $duserclass7, $duserclass8, $duserclass9, $duserclass10, $duserclass11, $duserclass12, $duserclass13;
  switch ($class)
  {

    //case UC_LEECH: return duserclass0;

    case UC_USER: return duserclass1;

    case UC_IRC_USER: return duserclass2;

    case UC_POWER_USER: return duserclass3;

    case UC_VIP: return duserclass4;

    case UC_SUPER_VIP: return duserclass5;

    case UC_UPLOADER: return duserclass6;

    case UC_MODERATOR: return duserclass7;

    case UC_SUPER_MODERATOR: return duserclass8;

    case UC_ADMINISTRATOR: return duserclass9;

    case UC_SYSOP: return duserclass10;
    
    case UC_STAFFLEADER: return duserclass11;
    
    case UC_DESIGNER: return duserclass12;

    case UC_CODER: return duserclass13;
  }
  return "";
}

function is_valid_user_class($class)
{
  return is_numeric($class) && floor($class) == $class && $class >= UC_USER && $class <= UC_CODER;
}

function is_valid_id($id)
{
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}

function get_user_class_color($class)
{
    switch ($class)
    {        
        //case UC_LEECH: return "FFFFFF";
        case UC_USER: return "8E35EF";
        case UC_IRC_USER: return " ";
        case UC_POWER_USER: return "f9a200";
        case UC_VIP: return "009F00";
        case UC_SUPER_VIP: return " ";
        case UC_UPLOADER: return "0000FF";
        case UC_MODERATOR: return "FE2E2E";
        case UC_SUPER_MODERATOR: return " ";
        case UC_ADMINISTRATOR: return "B000B0";
        case UC_SYSOP: return "4080B0";
        case UC_STAFFLEADER: return "FF0000";
        case UC_DESIGNER: return "FF0000";
        case UC_CODER: return "B40404";
    }
    return "";
}
function get_user_class_image($class)
{
  switch ($class)
  {    
    //case UC_LEECH: return "pic/class/leecher.gif";
    
    case UC_USER: return "pic/class/user.gif";
    
    case UC_IRC_USER: return "pic/class/user.gif";

    case UC_POWER_USER: return "pic/class/pu.gif";

    case UC_VIP: return "pic/class/vip.gif";

    case UC_SUPER_VIP: return "pic/class/vip.gif";
    
    case UC_UPLOADER: return "pic/class/uploader.gif";

    case UC_MODERATOR: return "pic/class/mod.gif";
    
    case UC_SUPER_MODERATOR: return "pic/class/mod.gif";

    case UC_ADMINISTRATOR: return "pic/class/admin.gif";

    case UC_SYSOP: return "pic/class/sysop.gif";
    
    case UC_STAFFLEADER: return "pic/class/leader.gif";
    
    case UC_DESIGNER: return "pic/class/leader.gif";
  
    case UC_CODER: return "pic/class/coder.gif";

    }
  return "";
}
////////////////user warn progressbar//////
function get_user_warns_image($w) {
$maxpx = "40"; // Maximum amount of pixels for the progress bar

if ($w == 0) $warns = "<img src=\"/pic/progbar-rest.gif\" height=9 width=" . ($maxpx) . " />";
if ($w == 100) $warns = "<img src=\"/pic/progbar-red.gif\" height=9 width=" . ($maxpx) . " />";
if ($w >= 1 && $w <= 30) $warns = "<img src=\"/pic/progbar-green.gif\" height=9 width=" . ($w*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$w)*($maxpx/100)) . " />";
if ($w >= 31 && $w <= 65) $warns = "<img src=\"/pic/progbar-yellow.gif\" height=9 width=" . ($w*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$w)*($maxpx/100)) . " />";
if ($w >= 66 && $w <= 99) $warns = "<img src=\"/pic/progbar-red.gif\" height=9 width=" . ($w*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$w)*($maxpx/100)) . " />";
return "<img src=\"/pic/bar_left.gif\" />" . $warns ."<img src=\"/pic/bar_right.gif\" />";
}
////////////////user warn progressbar//////
///////////inbox progress indicator
function get_percent_inbox_image($p) {
$maxpx = "40"; // Maximum amount of pixels for the progress bar

if ($p == 0) $progress = "<img src=\"/pic/progbar-rest.gif\" height=9 width=" . ($maxpx) . " />";
if ($p == 100) $progress = "<img src=\"/pic/progbar-red.gif\" height=9 width=" . ($maxpx) . " />";
if ($p >= 1 && $p <= 30) $progress = "<img src=\"/pic/progbar-green.gif\" height=9 width=" . ($p*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$p)*($maxpx/100)) . " />";
if ($p >= 31 && $p <= 65) $progress = "<img src=\"/pic/progbar-yellow.gif\" height=9 width=" . ($p*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$p)*($maxpx/100)) . " />";
if ($p >= 66 && $p <= 99) $progress = "<img src=\"/pic/progbar-red.gif\" height=9 width=" . ($p*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$p)*($maxpx/100)) . " />";
return "<img src=\"/pic/bar_left.gif\" />" . $progress ."<img src=\"/pic/bar_right.gif\" />";
}
///////////torrent progress indicator
function get_percent_completed_image($p) {
$maxpx = "40"; // Maximum amount of pixels for the progress bar

if ($p == 0) $progress = "<img src=\"/pic/progbar-rest.gif\" height=9 width=" . ($maxpx) . " />";
if ($p == 100) $progress = "<img src=\"/pic/progbar-green.gif\" height=9 width=" . ($maxpx) . " />";
if ($p >= 1 && $p <= 30) $progress = "<img src=\"/pic/progbar-red.gif\" height=9 width=" . ($p*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$p)*($maxpx/100)) . " />";
if ($p >= 31 && $p <= 65) $progress = "<img src=\"/pic/progbar-yellow.gif\" height=9 width=" . ($p*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$p)*($maxpx/100)) . " />";
if ($p >= 66 && $p <= 99) $progress = "<img src=\"/pic/progbar-green.gif\" height=9 width=" . ($p*($maxpx/100)) . " /><img src=\"/pic/progbar-rest.gif\" height=9 width=" . ((100-$p)*($maxpx/100)) . " />";
return "<img src=\"/pic/bar_left.gif\" />" . $progress ."<img src=\"/pic/bar_right.gif\" />";
}
function get_percent_donated_image($d) {
       $img = "progress-";
       if ($p == 100)
$img .= "5";
       elseif (($d >= 0) && ($d <= 10))
$img .= "0";
       elseif (($d >= 11) && ($d <= 40))
$img .= "1";
       elseif (($d >= 41) && ($d <= 60))
$img .= "2";
       elseif (($d >= 61) && ($d <= 80))
$img .= "3";
       elseif (($d >= 81) && ($d <= 99))
$img .= "4";
       return "<img src=\""."pic/".$img.".gif\"/>";
}
///////////////Rep system - taken from Tbdev09 /////////////
function get_reputation($user, $mode = '', $rep_is_on = TRUE)
{
global $BASEURL, $rep_is_online ;



$member_reputation = "";
if( $rep_is_on )
{
@include 'cache/rep_cache.php';
// ok long winded file checking, but it's much better than file_exists
if( ! isset( $reputations ) || ! is_array( $reputations ) || count( $reputations ) < 1)
{
return '<span title="Cache doesn\'t exist or zero length">Reputation: Offline</span>';
}

$user['g_rep_hide'] = isset( $user['g_rep_hide'] ) ? $user['g_rep_hide'] : 0;
$user['username'] = (isset($user['anonymous']) && ($user['anonymous'] == 'yes') ? 'Anonymous' : $user['username']);
// Hmmm...bit of jiggery-pokery here, couldn't think of a better way.
$max_rep = max(array_keys($reputations));
if($user['reputation'] >= $max_rep)
{
$user_reputation = $reputations[$max_rep];
}
else
foreach($reputations as $y => $x)
{
if( $y > $user['reputation'] ) { $user_reputation = $old; break; }
$old = $x;
}
//$rep_is_on = TRUE;
//$CURUSER['g_rep_hide'] = FALSE;
$rep_power = $user['reputation'];
$posneg = '';
if( $user['reputation'] == 0 )
{
$rep_img = 'balance';
$rep_power = $user['reputation'] * -1;
}
elseif( $user['reputation'] < 0 )
{
$rep_img = 'neg';
$rep_img_2 = 'highneg';
$rep_power = $user['reputation'] * -1;
}
else
{
$rep_img = 'pos';
$rep_img_2 = 'highpos';
}
/**
if( $rep_power > 500 )
{
// work out the bright green shiny bars, cos they cost 100 points, not the normal 100
$rep_power = ( $rep_power - ($rep_power - 500) ) + ( ($rep_power - 500) / 2 );
}
**/
// shiny, shiny, shiny boots...
// ok, now we can work out the number of bars/pippy things
$pips = 12;
switch ($mode)
{
case 'comments':
$pips = 12;
break;
case 'torrents':
$pips = 1003;
break;
case 'users':
$pips = 970;
break;
case 'posts':
$pips = 12;
break;
default:
$pips = 12; // statusbar
}

$rep_bar = intval($rep_power / 100);
if( $rep_bar > 10 )
{
$rep_bar = 10;
}

if( $user['g_rep_hide'] ) // can set this to a group option if required, via admin?
{
$posneg = 'off';
$rep_level = 'rep_off';
}
else
{ // it ain't off then, so get on with it! I wanna see shiny stuff!!
$rep_level = $user_reputation ? $user_reputation : 'rep_undefined';// just incase

for( $i = 0; $i <= $rep_bar; $i++ )
{
if( $i >= 5 )
{
$posneg .= "<img src='pic/rep/reputation_$rep_img_2.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
}
else
{
$posneg .= "<img src='pic/rep/reputation_$rep_img.gif' border='0' alt=\"Reputation Power $rep_power\n{$user['username']} $rep_level\" title=\"Reputation Power $rep_power {$user['username']} $rep_level\" />";
}
}
}
// now decide the locale
if($mode != '')
return "Rep: ".$posneg . "<br /><br /><a href='javascript:;' onclick=\"PopUp('$BASEURL/reputation.php?pid={$user['id']}&amp;locale=".$mode."','Reputation',400,241,1,1);\"><img src='".$BASEURL."/pic/forumicons/giverep.jpg' border='0' alt='Add reputation:: {$user['username']}' title='Add reputation:: {$user['username']}' /></a>";
else
return " ".$posneg;
} // END IF ONLINE
// default
return '<span title="Set offline by admin setting">Rep System Offline</span>';
}
//////////////Tbsource rep mod //////////
?>