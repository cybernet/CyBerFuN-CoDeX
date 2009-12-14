<?php
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
require_once ("include/user_functions.php");
dbconn();

if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Usergroups- Nosey Cunt !");



/*  ,$usergroups;  Stdhead Global and autoclean */ 

/* stdhead :)
 if ($usergroups['isbanned'] == 'yes') {
        die("Sorry but your usergroup is banned from this server");
    }
*/   

/* example comment.php
if ($usergroups['cancomment'] == 'no' OR $usergroups['cancomment'] != 'yes' OR $row['allow_comments'] == 'no') {
  stderr( "Sorry...", "Your usergroup is not allowed to comment on torrents" );
	exit;
}
*/ 
/* userlogin
    //unset($GLOBALS["CURUSER"]);    
    to
    unset($GLOBALS["CURUSER"], $GLOBALS["usergroups"], $usergroup);
   
    $classarrays = array(UC_USER, UC_POWER_USER, UC_VIP, UC_UPLOADER, UC_MODERATOR, UC_ADMINISTRATOR, UC_SYSOP, UC_DESIGNER, UC_CODER);
	  $usergroup = $row['class'];
    if (in_array($usergroup, $classarrays))
		$usergroup = $usergroup + 1;
    $get_group_data = sql_query('SELECT * FROM usergroups WHERE gid = '.sqlesc($usergroup));
	  $group_data_results = mysql_fetch_array($get_group_data);
	  $GLOBALS["usergroups"] = $group_data_results;

*/

/*
CREATE TABLE `usergroups` (
  `gid` smallint(5) unsigned NOT NULL auto_increment,
  `title` varchar(20) NOT NULL,
  `description` tinytext NOT NULL,
  `isbanned` enum('yes','no') NOT NULL default 'no',
  `canpm` enum('yes','no') NOT NULL default 'yes',
  `candownload` enum('yes','no') NOT NULL default 'yes',
  `canupload` enum('yes','no') NOT NULL default 'no',
  `canrequest` enum('yes','no') NOT NULL default 'yes',
  `cancomment` enum('yes','no') NOT NULL default 'yes',
  `canbookmark` enum('yes','no') NOT NULL default 'yes',
  `canusercp` enum('yes','no') NOT NULL default 'yes',
  `canresetpasskey` enum('yes','no') NOT NULL default 'yes',
  `canviewotherprofile` enum('yes','no') NOT NULL default 'yes',
  `canthanks` enum('yes','no') NOT NULL default 'yes',
  `canshout` enum('yes','no') NOT NULL default 'yes',
  `caninvite` enum('yes','no') NOT NULL default 'yes',
  `canbonus` enum('yes','no') NOT NULL default 'yes',
  `canmemberlist` enum('yes','no') NOT NULL default 'yes',
  `canfriendlist` enum('yes','no') NOT NULL default 'yes',
  `cantopten` enum('yes','no') NOT NULL default 'yes',
  `caneditusersettings` enum('yes','no') NOT NULL default 'no',
  `canstaffpanel` enum('yes','no') NOT NULL default 'no',
  `autoinvite` tinyint(5) unsigned NOT NULL default '3',
  PRIMARY KEY  (`gid`)
) TYPE=MyISAM;
*/

function ssr ($arg)
{
    if (is_array($arg)) {
        foreach ($arg as $key => $arg_bit) {
            $arg[$key] = ssr($arg_bit);
        }
    } else {
        $arg = stripslashes($arg);
    }
    return $arg;
}

function GetVar ($name)
{
    if (is_array($name)) {
        foreach ($name as $var) GetVar ($var);
    } else {
        if (!isset($_REQUEST[$name]))
            return false;
        if (get_magic_quotes_gpc()) {
            $_REQUEST[$name] = ssr($_REQUEST[$name]);
        }
        $GLOBALS[$name] = $_REQUEST[$name];
        return $GLOBALS[$name];
    }
}

function yesno($title, $name, $value="yes")
{	
	if($value == "no")
	{
		$nocheck = " checked=\"checked\"";
	}
	else
	{
		$yescheck = " checked=\"checked\"";
	}
	echo "<tr>\n<td valign=\"top\" width=\"60%\" align=\"right\">$title</td>\n<td valign=\"top\" width=\"40%\" align=\"left\"><label><input type=\"radio\" name=\"$name\" value=\"yes\"".(isset($yescheck) ? $yescheck : '')." />&nbsp;Yes</label> &nbsp;&nbsp;<label><input type=\"radio\" name=\"$name\" value=\"no\"".(isset($nocheck) ? $nocheck : '')." />&nbsp;No</label></td>\n</tr>\n";
}

function inputbox($title, $name, $value="", $size="25", $extra="", $maxlength="", $autocomplete=1, $extra2="")
{
	
	$value = htmlspecialchars($value);
	if($autocomplete != 1)
	{
		$ac = " autocomplete=\"off\"";
	}else
		$ac = "";

	if($value != '')
	{
		$value = " value=\"{$value}\"";
	}
	if($maxlength != '')
	{
    	$maxlength = " maxlength=\"$maxlength\"";
  	}
  	if($size != '')
  	{
    	$size = " size=\"$size\"";
  	}
	echo "<tr>\n<td valign=\"top\" width=\"60%\" align=\"right\">$title</td>\n<td valign=\"top\" width=\"40%\" align=\"left\">\n$extra2<input type=\"text\" id=\"specialboxes\" name=\"$name\"$size$maxlength$ac$value />\n$extra\n</td>\n</tr>\n";
}

$gid = isset($_POST['gid']) ? (int)$_POST['gid'] : (isset($_GET['gid']) ? (int)$_GET['gid'] : '');

$action = isset($_POST['action']) ? htmlspecialchars($_POST['action']) : (isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'usergroups');
$allowed_actions = array('usergroups', 
                         'editusergroup', 
                         'updategroup',
                         'deleteug',
                         'newgroup',
                         'creategroup',);
                         
if (!in_array($action, $allowed_actions))
	  $action = 'usergroups';

//==User groups listing
if ($action == 'usergroups') 
{
	stdhead('User Groups admin page');
	$query = sql_query('SELECT gid, title, description FROM usergroups');
	if (mysql_num_rows($query) == 0) {
		stdmsg('Error ',' There are no user groups!');
    echo "<input type='button' onclick=\"window.location='usergroups.php?action=newgroup'\" value=\"newgroup\">";
		stdfoot();
		exit;
	}else
	echo '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">';
	echo '<tbody>
          <tr>
            <td>
              <table class="tback" border="0" cellpadding="6" cellspacing="0" width="100%">
                <tbody>
                  <tr>
                    <td class="colhead" colspan="5" align="center">Default user groups</td>
                  </tr>';
                  
	echo '<tr class="subheader">
          <td width="10%" align="center">Group ID</td>
          <td width="30%" align="left">Group Name</td>
          <td width="40%" align="left">Description</td>
          <td width="10%" align="center">All users</td>
          <td width="10%" align="center">Cancel</td>
        </tr>';
        
	while ($usergroup = mysql_fetch_array($query)) {
		$group = $usergroup['gid']-1;
		$total = sql_query('SELECT COUNT(id) as totalusers FROM users WHERE class = '.sqlesc($group));
		$totalusers = mysql_fetch_array($total);		
		echo '<tr>
            <td align="center">'.(int)$usergroup['gid'].'</td>
            <td align="left"><a href=?action=editusergroup&gid='.(int)$usergroup['gid'].'>'.htmlspecialchars($usergroup['title']).'</a></td>
            <td align="left">'.htmlspecialchars($usergroup['description']).'</td>
            <td align="center">'.(int)$totalusers['totalusers'].'</td>
            <td align="right"><a href=?action=deleteug&gid='.(int)$usergroup['gid'].'>Cancel</a></td>
          </tr>';
	}
	echo '</table></table>';
    echo "<p align='right'><input type='button' onclick=\"window.location='usergroups.php?action=newgroup'\" value=\"newgroup\"></p>";
	stdfoot();

}

//Change User Group
elseif ($action == 'editusergroup' ) 

{
	stdhead('Change User Group');
	$query = sql_query('SELECT * FROM usergroups WHERE gid = '.sqlesc($gid));
  
  if (mysql_num_rows($query) == 0) 
  {
		stdmsg('Error ',' Invalid group!');
		stdfoot();
		exit;
	}
  
  
if ($action == 'edit' && $CURUSER['class'] < $av_class)
		stderr('Error', 'You are not allowed to edit this page.');
		
  else
	$usergroup = mysql_fetch_array($query);
	echo '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">';
	echo '<tbody><tr><td><table class="tback" border="0" cellpadding="6" cellspacing="0" width="100%"><tbody><tr><td class="colhead" colspan="2" align="center">User group modified: '.$usergroup['title'].' ('.$usergroup['description'].')</td></tr>';
	echo '<tr class="subheader"><td align="center" colspan="2">Permissions: General</td></tr>';
	echo '<form method="post" action="usergroups.php?action=updategroup">
	<input type="hidden" name="action" value="updategroup">
	<input type="hidden" name="gid" value="'.$gid.'">';
	echo '</tbody>';
	yesno('Is \'Banned\' Group?<br /><small>If this group is a \'banned\' usergroup, users will be able to be \'banned\' into this usergroup.</small>', 'isbanned', ($usergroup['isbanned'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Use Private Messaging?<br /><small>User can send PM.</small>', 'canpm', ($usergroup['canpm'] == 'yes' ? 'yes' : 'no'));	
	yesno('Can Download Torrent?<br /><small>User can Download a torrent.</small>', 'candownload', ($usergroup['candownload'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Upload Torrent?<br /><small>User can Upload a torrent.</small>', 'canupload', ($usergroup['canupload'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Request Torrent?<br /><small>User can Request a torrent.</small>', 'canrequest', ($usergroup['canrequest'] == 'yes' ? 'yes' : 'no'));

	yesno('Can Post Comment?<br /><small>User can post a Comment.</small>', 'cancomment', ($usergroup['cancomment'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Bookmark Torrent?<br /><small>User can Bookmark a torrent.</small>', 'canbookmark', ($usergroup['canbookmark'] == 'yes' ? 'yes' : 'no'));
	  
  yesno('Can Thanks on Torrents?<br /><small>User can Say Thanks on Torrents.</small>', 'canthanks', ($usergroup['canthanks'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Use Shoutbox?<br /><small>User can Shout on Tracker.</small>', 'canshout', ($usergroup['canshout'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Use Invite System?<br /><small>User can Invite his friends.</small>', 'caninvite', ($usergroup['caninvite'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Use Bonus Points<br /><small>User can Exchange his Karma Bonus Points.</small>', 'canbonus', ($usergroup['canbonus'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Reset Passkey?<br /><small>User can reset his Passkey.</small>', 'canresetpasskey', ($usergroup['canresetpasskey'] == 'yes' ? 'yes' : 'no'));	

	echo '<tr class="subheader"><td align="center" colspan="2">Permissions: Viewing</td></tr>';
	yesno('Can View UserCP?<br /><small>User can view his Control Page.</small>', 'canusercp', ($usergroup['canusercp'] == 'yes' ? 'yes' : 'no'));	
	yesno('Can View Profiles?<br /><small>User can view other user Profiles.</small>', 'canviewotherprofile', ($usergroup['canviewotherprofile'] == 'yes' ? 'yes' : 'no'));
	yesno('Can View Memberlist?<br /><small>User can view Memberlist.</small>', 'canmemberlist', ($usergroup['canmemberlist'] == 'yes' ? 'yes' : 'no'));
	yesno('Can View Friendlist?<br /><small>User can view Friendlist.</small>', 'canfriendlist', ($usergroup['canfriendlist'] == 'yes' ? 'yes' : 'no'));
	yesno('Can View Top10 Page?<br /><small>User can view Top10 Page.</small>', 'cantopten', ($usergroup['cantopten'] == 'yes' ? 'yes' : 'no'));
	
	echo '<tr class="subheader"><td align="center" colspan="2">Permissions: Administrative</td></tr>';
	yesno('Can Edit User Settings?<br /><small>User Can Edit User Settings.</small>', 'caneditusersettings', ($usergroup['caneditusersettings'] == 'yes' ? 'yes' : 'no'));
	yesno('Can Access Staff Panel?<br /><small>User can access Staff Panel of tracker.</small>', 'canstaffpanel', ($usergroup['canstaffpanel'] == 'yes' ? 'yes' : 'no'));
	
	echo '<tr class="subheader"><td align="center" colspan="2">Permissions: Limitations</td></tr>';
	inputbox('Automatic Invite<br /><small>Set the limit of automatic invites for each month<br />Set to 0 to disable this.</small>', 'autoinvite', $usergroup['autoinvite']);
    echo '<tr>
          <td colspan="2" align="right">
            <input type="submit" value="Edit"> 
            <input type="reset" value="Cancel">
          </td>
        </tr>';
	echo '</form></table></table>'; 
	stdfoot();

}


//Data update
elseif ($action == 'updategroup')
{
getvar(array('isbanned',
               'canpm',
               'candownload',
               'canupload',
               'canrequest',
               'cancomment',
               'canbookmark',
               'canusercp',
               'canresetpasskey',
               'canviewotherprofile',
               'canthanks',
               'canshout',
               'caninvite',
               'canbonus',
               'canmemberlist',
               'canfriendlist',
               'cantopten',
               'caneditusersettings',
               'canstaffpanel',
               'autoinvite'));

	$updateset[] = 'isbanned			=	'.sqlesc($isbanned);
	$updateset[] = 'canpm				=	'.sqlesc($canpm);
	$updateset[] = 'candownload			=	'.sqlesc($candownload);
	$updateset[] = 'canupload			=	'.sqlesc($canupload);
	$updateset[] = 'canrequest			=	'.sqlesc($canrequest);
	$updateset[] = 'cancomment			=	'.sqlesc($cancomment);
	$updateset[] = 'canbookmark			=	'.sqlesc($canbookmark);
	$updateset[] = 'canusercp			=	'.sqlesc($canusercp);
	$updateset[] = 'canresetpasskey		=	'.sqlesc($canresetpasskey);
	$updateset[] = 'canviewotherprofile	=	'.sqlesc($canviewotherprofile);
	$updateset[] = 'canthanks			=	'.sqlesc($canthanks);
	$updateset[] = 'canshout			=	'.sqlesc($canshout);
	$updateset[] = 'caninvite			=	'.sqlesc($caninvite);
	$updateset[] = 'canbonus			=	'.sqlesc($canbonus);
	$updateset[] = 'canmemberlist		=	'.sqlesc($canmemberlist);	
	$updateset[] = 'canfriendlist		=	'.sqlesc($canfriendlist);
	$updateset[] = 'cantopten			=	'.sqlesc($cantopten);
	$updateset[] = 'caneditusersettings	=	'.sqlesc($caneditusersettings);
	$updateset[] = 'canstaffpanel		=	'.sqlesc($canstaffpanel);
  $updateset[] = 'autoinvite			=	'.sqlesc(intval($autoinvite));

  if (sizeof($updateset) > 0)
 	sql_query('UPDATE usergroups SET  ' . implode(", ", $updateset) . ' WHERE gid='.sqlesc($gid)) or sqlerr(__FILE__, __LINE__);
  header("Refresh: 0; url=".$_SERVER["PHP_SELF"]);
}


//Delete user group
elseif($action == 'deleteug') 
{
$todel = intval($_GET['gid']);
$h = mysql_query("DELETE FROM usergroups WHERE gid = ".sqlesc($todel)) or die(mysql_error());
if($h)
header("Refresh: 0; url=".$_SERVER["PHP_SELF"]);
die;
}

//New Group
elseif($action == 'newgroup') 
{
	stdhead('New user group');
  $gid = !empty($_GET['gid']) ? $_GET['gid'] : '0';
	$query = sql_query('SELECT * FROM usergroups WHERE gid = '.sqlesc($gid));  
	$usergroup = mysql_fetch_array($query);
	echo '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">';
	echo '<tbody>
          <tr>
            <td>
              <table class="tback" border="0" cellpadding="6" cellspacing="0" width="100%">
                <tbody>
                  <tr>
                    <td class="colhead" colspan="2" align="center">New Group</td>
                  </tr>';
	echo '</tbody>';
	
	echo '<form method="post" action="usergroups.php?action=creategroup">
	<input type="hidden" name="action" value=creategroup>';
	
  echo'<tbody><tr><td class="subheader" colspan="2" align="center">General Options</td></tr>';
inputbox('Usergroup ID','gid','',35,'<BR>The  group id');
inputbox('Usergroup Name','title','',35,'<BR>The title of the group');
inputbox('Usergroup Description','description','',35,'<BR>The description of the group');
echo'</tbody>';

	echo'<tbody><tr><td class="subheader" colspan="2" align="center">Permissions: General</td></tr>';
	yesno('Is \'Banned\' Group?<br /><small>If this group is a \'banned\' usergroup, users will be able to be \'banned\' into this usergroup.</small>','isbanned');
  yesno('Can use PM system<br /><small>If set to no, users in this UG can\'t send or recive messages</small>', 'canpm');
  yesno('Can download torrents', 'candownload');
  yesno('Can upload torrents', 'canupload');
  yesno('Can request torrents', 'canrequest');
  yesno('Can post comments', 'cancomment');
  yesno('Can use bookmark', 'canbookmark');
  yesno('Can reset passkey', 'canresetpasskey');
  yesno('Can thanks on torrents', 'canthanks');
  yesno('Can use Shoutbox', 'canshout');
  yesno('Can use Invite', 'caninvite');
  yesno('Can use karma system', 'canbonus');
  	
  echo '</tbody><tbody><tr><td class="subheader" colspan="2" align="center">Permissions: Viewing</td></tr>';
  yesno('Can View UserCP?<br /><small>User can view his Control Page.</small>', 'canusercp');	
  yesno('Can View Profiles?<br /><small>User can view other user Profiles.</small>', 'canviewotherprofile');
  yesno('Can View Memberlist?<br /><small>User can view Memberlist.</small>', 'canmemberlist');
  yesno('Can View Friendlist?<br /><small>User can view Friendlist.</small>', 'canfriendslist');
  yesno('Can View Top10 Page?<br /><small>User can view Top10 Page.</small>', 'cantopten');
  
  echo '</tbody><tbody><tr><td class="subheader" colspan="2" align="center">Permissions: Administrative</td></tr>';
  yesno('Can Edit User Settings?<br /><small>User Can Edit User Settings.</small>', 'caneditusersettings', ($usergroup['caneditusersettings'] == 'yes' ? 'yes' : 'no'));
  yesno('Can Access Staff Panel?<br /><small>User can access Staff Panel of tracker.</small>', 'canstaffpanel');

 	echo '<tr class="subheader"><td align="center" colspan="2">Permissions: Limitations</td></tr>';
	inputbox('Automatic Invite<br /><small>Set the limit of automatic invites for each month<br />Set to 0 to disable this.</small>', 'autoinvite', $usergroup['autoinvite']);

	echo '<tr>
          <td colspan="2" align="right">
            <input type="submit" value="submit"> 
            <input type="reset" value="reset">
          </td>
        </tr>';
        
	echo '</form></table></table>';
	stdfoot();
}
//Create a group, insert data in the database
elseif($action == 'creategroup') 
{
	getvar(array('gid',
	             'title',
	             'description',  
               'isbanned',
               'canpm',
               'candownload',
               'canupload',
               'canrequest',
               'cancomment',
               'canbookmark',
               'canusercp',
               'canresetpasskey',
               'canviewotherprofile',
               'canthanks',
               'canshout',
               'caninvite',
               'canbonus',
               'canmemberlist',
               'canfriendlist',
               'cantopten',
               'caneditusersettings',
               'canstaffpanel',
               'autoinvite'));

$addnew = sql_query(
"INSERT INTO usergroups(  gid,
                          title,
                          description,
                          isbanned,
                          canpm,
                          candownload,
                          canupload,
                          canrequest,
                          cancomment,
                          canbookmark,
                          canusercp,
                          canresetpasskey,
                          canviewotherprofile,
                          canthanks,
                          canshout,
                          caninvite,
                          canbonus,
                          canmemberlist,
                          canfriendlist,
                          cantopten,
                          caneditusersettings,
                          canstaffpanel,
                          autoinvite)
                        
                        VALUES (  ".sqlesc($gid).",
                                  ".sqlesc($title).",
                                  ".sqlesc($description).",
                                  ".sqlesc($isbanned).",
                                  ".sqlesc($canpm).",
                                  ".sqlesc($candownload).",
                                  ".sqlesc($canupload).",
                                  ".sqlesc($canrequest).",
                                  ".sqlesc($cancomment).",
                                  ".sqlesc($canbookmark).",
                                  ".sqlesc($canusercp).",
                                  ".sqlesc($canresetpasskey).",
                                  ".sqlesc($canviewotherprofile).",
                                  ".sqlesc($canthanks).",
                                  ".sqlesc($canshout).",
                                  ".sqlesc($caninvite).",
                                  ".sqlesc($canbonus).",
                                  ".sqlesc($canmemberlist).",
                                  ".sqlesc($canfriendlist).",
                                  ".sqlesc($cantopten).",
                                  ".sqlesc($caneditusersettings).",
                                  ".sqlesc($canstaffpanel).",
                                  ".sqlesc($autoinvite).")") or sqlerr(__FILE__, __LINE__);

if(!$addnew) {
	 die('MySQL error.');
}
else {
header("Refresh: 0; url=".$_SERVER["PHP_SELF"]);
}
}
//Message if you want to perform an operation which is not defined
else
	stderr('Error ',' Invalid operation!');
?>