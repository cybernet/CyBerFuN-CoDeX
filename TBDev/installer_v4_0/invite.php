<?php
/**
 * @author Neptune
 * @poject TBDev.Net
 * @category Add-Ons
 * @complexity 0/10
 * @time 18 min
 * @copyright 2008
 */
require("include/bittorrent.php");
require("include/bbcode_functions.php");
//require("include/user_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
$do = (isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"]) ? $_POST["do"] : ''));	
$valid_actions = array('create_invite', 'delete_invite', 'confirm_account', 'view_page', 'send_email');
$do = (($do && in_array($do,$valid_actions,true)) ? $do : '') or header("Location: ?do=view_page");

/**
 * @action Main Page
 */

if ($do == 'view_page') {
$query = sql_query('SELECT * FROM users WHERE invitedby = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$rows = mysql_num_rows($query);

stdhead('Invites');

echo '<table border=1 width=750 cellspacing=0 cellpadding=5>'.
'<tr class=tabletitle><td colspan=7 class=colhead><b>Invited Users</b></td></tr>';

if(!$rows){
echo '<tr class=tableb><td colspan=7>No invitees yet.</tr>';
} else {

echo '<tr class=tableb><td align=center><b>Username</b></td><td align=center><b>Uploaded</b></td><td align=center><b>Downloaded</b></td><td align=center><b>Ratio</b></td><td align=center><b>Status</b></td><td align=center><b>Confirm</b></td></tr>';

for ($i = 0; $i < $rows; ++$i) { 
$arr = mysql_fetch_assoc($query);
	
if ($arr['status'] == 'pending')
$user = '<td align=center>' . htmlspecialchars($arr['username']) . '</td>';
else
$user = "<td align=center><a href=userdetails.php?id=$arr[id]>" . htmlspecialchars($arr['username']) . "</a>" .($arr["warned"] == "yes" ? "&nbsp;<img src=pic/warned.gif border=0 alt='Warned'>" : "")."&nbsp;" .($arr["enabled"] == "no" ? "&nbsp;<img src=pic/disabled.gif border=0 alt='Disabled'>" : "")."&nbsp;" .($arr["donor"] == "yes" ? "<img src=pic/star.gif border=0 alt='Donor'>" : "")."</td>";

if ($arr['downloaded'] > 0) {
$ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
$ratio = '<font color=" . get_ratio_color($ratio) . ">'.$ratio.'</font>';
} else {
if ($arr['uploaded'] > 0) {
$ratio = 'Inf.';
}
else {
$ratio = '---';
}
}

if ($arr["status"] == 'confirmed')
$status = "<font color=#1f7309>Confirmed</font>";
else
$status = "<font color=#ca0226>Pending</font>";

echo '<tr class=tableb>'.$user.'<td align=center>' . prefixed($arr['uploaded']) . '</td><td align=center>' . prefixed($arr['downloaded']) . '</td><td align=center>'.$ratio.'</td><td align=center>'.$status.'</td>';

if ($arr['status'] == 'pending') {
	
echo '<td align=center><a href=?do=confirm_account&userid='.(int)$arr['id'].'&sender='.(int)$CURUSER['id'].'><img src=pic/confirm.png border=0 /></a></td>';
} 
else
echo '<td align=center>---</td>';
}

}
echo '</table><br>';

$select = sql_query("SELECT * FROM invite_codes WHERE sender = ".sqlesc($CURUSER['id'])." AND status = 'Pending'") or sqlerr();
$num_row = mysql_num_rows($select);
print("<table border=1 width=750 cellspacing=0 cellpadding=5>".
"<tr class=tabletitle><td colspan=6 class=colhead><b>Created Invite Codes</b></td></tr>");

if(!$num_row) {
echo '<tr class=tableb><td colspan=6>You have not created invite codes at the moment!</tr>'; } else { echo '<tr class=tableb><td><b>Invite Code</b></td><td><b>Created Date</b></td><td><b>Delete</b></td><td><b>Status</b></tr>';

for ($i = 0; $i < $num_row; ++$i) { $fetch_assoc = mysql_fetch_assoc($select);

echo '<tr class=tableb><td>'.$fetch_assoc['code'].' <a href="?do=send_email&id='.(int)$fetch_assoc['id'].'"><img src="pic/email.gif" border="0" / ></td><td>'."" . get_elapsed_time(sql_timestamp_to_unix_timestamp($fetch_assoc['invite_added'])) . " ago".'</td>';
echo '<td><a href="?do=delete_invite&id='.(int)$fetch_assoc['id'].'"><img src=pic/del.png border=0 /></a></td><td>'.$fetch_assoc['status'].'</td></tr>'; } }
echo '<tr class=tableb><td colspan=7 align=center><form action="?do=create_invite" method="post"><input type=submit value="Create Invite Code" style=height: 20px></form></td></tr>';
echo '</table>'; stdfoot(); }

/**
 * @action Create Invites
 */

elseif ($do =='create_invite') {

if ($CURUSER['invites'] <= 0) stderr('Error', 'No invites!');
if ($CURUSER["invite_on"] == 'no') stderr("Denied", "Your invite sending privileges has been disabled by the Staff!");

$res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res); 

if ($arr[0] >= $invites) stderr("Error", "Sorry, user limit reached. Please try again later.");

$invite = md5(mksecret());

sql_query('INSERT INTO invite_codes (sender, invite_added, code) VALUES ( ' . sqlesc((int)$CURUSER['id']) . ', ' . sqlesc(get_date_time()) . ', ' . sqlesc($invite) . ' )') or sqlerr(__FILE__, __LINE__);

sql_query('UPDATE users SET invites = invites - 1 WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);

header("Location: ?do=view_page");
}

/**
 * @action Send e-mail
 */

elseif ($do =='send_email') {
	
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
$email = (isset($_POST['email'])? htmlentities($_POST['email']) : '');
$invite = (isset($_POST['code'])? $_POST['code'] : '');

if (!$email) stderr("Error", "You must enter an email address!");

$check = (mysql_fetch_row(sql_query('SELECT COUNT(*) FROM users WHERE email = ' . sqlesc($email)))) or sqlerr(__FILE__, __LINE__);
if ($check[0] != 0) stderr('Error', 'This email address is already in use!');

if (!validemail($email)) stderr("Error", "That doesn't look like a valid email address.");

$inviter = htmlspecialchars($CURUSER['username']);
$body = <<<EOD
You have been invited to $SITENAME by $inviter. They have
specified this address ($email) as your email. If you do not know this person, please ignore this email. Please do not reply.

This is a private site and you must agree to the rules before you can enter:

$DEFAULTBASEURL/useragreement.php

$DEFAULTBASEURL/rules.php

$DEFAULTBASEURL/faq.php

------------------------------------------------------------

To confirm your invitation, you have to follow this link and type the invite code:

$DEFAULTBASEURL/invite_signup.php

Invite Code: $invite

------------------------------------------------------------

After you do this, your inviter need's to confirm your account. 
We urge you to read the RULES and FAQ before you start using $SITENAME.
EOD;
$sendit = mail($email, "You have been invited to $SITENAME", $body, "From: $SITEEMAIL", "-f$SITEEMAIL"); 

if (!$sendit) stderr('Error', 'Unable to send mail. Please contact an administrator about this error.');
else stderr('', 'A confirmation email has been sent to the address you specified.'); }

$id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : ''));

if (!is_valid_id($id)) stderr('Error', 'Invalid ID!');

$query = sql_query('SELECT * FROM invite_codes WHERE id = ' . sqlesc($id) . ' AND sender = ' . sqlesc($CURUSER['id']).' AND status = "Pending"') or sqlerr(__FILE__, __LINE__);
$fetch = mysql_fetch_assoc($query) or stderr('Error', 'This invite code does not exist.');

stdhead();
echo '<form method="post" action="?do=send_email"><table border="1" cellspacing="0" cellpadding="10">
<tr><td class="rowhead">E-Mail</td><td><input type="email" size="40" name="email"></td></tr><input type="hidden" name="code" value='.$fetch['code'].' /><tr><td colspan="2" align="center"><input type="submit" value="Send e-mail" class="btn" /></td></tr></table>';
stdfoot(); }

/**
 * @action Delete Invites
 */

elseif ($do =='delete_invite') {
	
$id = (isset($_GET["id"]) ? (int)$_GET["id"] : (isset($_POST["id"]) ? (int)$_POST["id"] : ''));	

$query = sql_query('SELECT * FROM invite_codes WHERE id = ' . sqlesc($id) . ' AND sender = ' . sqlesc($CURUSER['id']).' AND status = "Pending"') or sqlerr(__FILE__, __LINE__);
$assoc = mysql_fetch_assoc($query) or stderr('Error','This invite code does not exist.');

isset($_GET['sure']) && $sure = htmlentities($_GET['sure'] == 'yes');

if (!$sure) stderr('Delete invite', 'Are you sure you want to delete this invite code? Click <a href="'.$_SERVER['PHP_SELF'].'?do=delete_invite&id='.(int)$id.'&sure=yes">here</a> to delete it or <a href="?do=view_page">here</a> to go back.');

sql_query('DELETE FROM invite_codes WHERE id = ' . sqlesc($id) . ' AND sender =' . sqlesc($CURUSER['id'].' AND status = "Pending"')) or sqlerr(__FILE__, __LINE__);

sql_query('UPDATE users SET invites = invites + 1 WHERE id = '.sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);

header("Location: ?do=view_page");
}

/**
 * @action Confirm Accounts
 */

elseif ($do ='confirm_account') {
	
$userid = (isset($_GET["userid"]) ? (int)$_GET["userid"] : (isset($_POST["userid"]) ? (int)$_POST["userid"] : ''));

if (!is_valid_id($userid)) stderr("Error", "Invalid ID.");

$select = sql_query('SELECT id, username FROM users WHERE id = ' . sqlesc($userid) . ' AND invitedby = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$assoc = mysql_fetch_assoc($select) or stderr('Error','No user with this ID.');

isset($_GET['sure']) && $sure = htmlentities($_GET['sure'] == 'yes');

if (!$sure) stderr('Confirm account', 'Are you sure you want to confirm '.htmlspecialchars($assoc['username']).'´s account? Click <a href="?do=confirm_account&userid='.(int)$userid.'&sender='.(int)$CURUSER['id'].'&sure=yes">here</a> to confirm it or <a href="?do=view_page">here</a> to go back.');

sql_query('UPDATE users SET status = "confirmed" WHERE id = '.sqlesc($userid).' AND invitedby = '.sqlesc($CURUSER['id']).' AND status="pending"') or sqlerr(__FILE__, __LINE__);
//==pm to new invitee/////
$msg = sqlesc("Dear [you] :wave:
Welcome to $SITENAME!
  
We have made many changes to the site, and we hope you enjoy them! 
We have been working hard to make $SITENAME somethin' special!

$SITENAME has a strong community (just check out forums), and is a feature rich site. We hope you'll join in on all the fun!
 
Be sure to read the [url=$BASEURL/rules.php]Rules[/url] and [url=$BASEURL/faq.php]FAQ[/url] before you start using the site.
We are a strong friendly community here :D $SITENAME is so much more then just torrents.
Just for kicks, we've started you out with 200.0 Karma Bonus  Points, and a couple of bonus GB to get ya started! 
so, enjoy  
cheers, 
$SITENAME Staff");
$id = $assoc["id"];
$subject = sqlesc("Welcome to $SITENAME !");
$added = sqlesc(get_date_time());
mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $id, $msg, $added)") or sqlerr(__FILE__, __LINE__);
///////////////////end////////////
header("Location: ?do=view_page");
}
?>