<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");

dbconn();
maxcoder();
$res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
if ($arr[0] >= $maxusers)
    stderr("Error", "Sorry, user limit reached. Please try again later.");

if (!mkglobal("wantusername:wantpassword:passagain:email:captcha:passhint:hintanswer"))
    die();
session_start();
if (empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha)) {
    header('Location: signup.php');
    exit();
}

function bark($msg)
{
    stdhead();
    stdmsg("Signup failed!", $msg);
    stdfoot();
    exit;
}

function isportopen($port)
{
    $sd = @fsockopen($_SERVER["REMOTE_ADDR"], $port, $errno, $errstr, 1);
    if ($sd) {
        fclose($sd);
        return true;
    } else
        return false;
}

if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($passhint) || empty($hintanswer))
    bark("Don't leave any fields blank.");
if (strlen($wantusername) > 12)
    bark("Sorry, username is too long (max is 12 chars)");

if ($wantpassword != $passagain)
    bark("The passwords didn't match! Must've typoed. Try again.");

if (strlen($wantpassword) < 6)
    bark("Sorry, password is too short (min is 6 chars)");

if (strlen($wantpassword) > 40)
    bark("Sorry, password is too long (max is 40 chars)");

if ($wantpassword == $wantusername)
    bark("Sorry, password cannot be same as user name.");

if (!validemail($email))
    bark("That doesn't look like a valid email address.");

if (!validusername($wantusername))
    bark("Invalid username.");
// make sure user agrees to everything...
if ($_POST["rulesverify"] != "yes" || $_POST["faqverify"] != "yes" || $_POST["ageverify"] != "yes")
    stderr("Signup failed", "Sorry, you're not qualified to become a member of this site.");
// check if email addy is already in use
$a = (@mysql_fetch_row(@sql_query("select count(*) from users where email='$email'"))) or die(mysql_error());
if ($a[0] != 0)
    bark("The e-mail address " . safeChar($email) . " is already in use.");

//=== check if ip addy is already in use
$c = (@mysql_fetch_row(@sql_query("select count(*) from users where ip='" . $_SERVER['REMOTE_ADDR'] . "'"))) or die(mysql_error());
if ($c[0] != 0)
stderr("Error", "The ip " . $_SERVER['REMOTE_ADDR'] . " is already in use. We only allow one account per ip address.");


$editsecret = (!$arr[0]?"": ENA_EMAIL_CONFIRM?mksecret():"");
$secret = mksecret();
$wantpasshash = md5($secret . $wantpassword . $secret);
$wanthintanswer = md5($hintanswer);
check_banned_emails($email);

$ret = mysql_query($q = ("INSERT INTO users (username, passhash, secret, editsecret, email, added, status" .
        (!$arr[0]?", class":"") . (!ENA_EMAIL_CONFIRM?", last_access, enabled":"") . ") VALUES (" .
        implode(",", array_map("sqlesc",
                array($wantusername, $wantpasshash, $secret, $editsecret, $email, get_date_time()))) . ",'" . (!$arr[0] || !ENA_EMAIL_CONFIRM?'confirmed':'pending') . "'" .
        (!$arr[0]?', ' . UC_SYSOP:'') .
        (!ENA_EMAIL_CONFIRM?", '" . get_date_time() . "', 'yes'":'') . ')'));
$message = "Welcome New $SITENAME Member : - " . safeChar($wantusername) . "";
if (!$ret) {
    if (mysql_errno() == 1062)
        bark("Username already exists!");
    bark("borked");
}

$id = mysql_insert_id();
$msg = sqlesc("Dear $wantusername :wave:
Welcome to $SITENAME!
  
We have made many changes to the site, and we hope you enjoy them! 
We have been working hard to make $SITENAME somethin' special!

$SITENAME has a strong community (just check out forums), and is a feature rich site. We hope you'll join in on all the fun!
 
Be sure to read the [url=$BASEURL/rules.php]rules[/url] and [url=$BASEURL/faq.php]FAQ[/url] before you start using the site.
We are a strong friendly community here :D $SITENAME is so much more then just torrents.
Just for kicks, we've started you out with 200.0 Karma Bonus  Points, and a couple of bonus GB to get ya started! 
so, enjoy  
cheers, 
$SITENAME Staff");

$subject = sqlesc("Welcome to $SITENAME!");
$added = sqlesc(get_date_time());
mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $id, $msg, $added)") or sqlerr(__FILE__, __LINE__);
write_log("newmember", "User account $id ($wantusername) was created");

$psecret = md5($editsecret);
// /////	New member welcome text///
autoshout($message);
// ////////////////////////////////
$body = <<<EOD
You have requested a new user account on $SITENAME and you have
specified this address ($email) as user contact.

If you did not do this, please ignore this email. The person who entered your
email address had the IP address {$_SERVER["REMOTE_ADDR"]}. Please do not reply.

To confirm your user registration, you have to follow this link:

$DEFAULTBASEURL/confirm.php?id=$id&secret=$psecret

After you do this, you will be able to use your new account. If you fail to
do this, you account will be deleted within a few days. We urge you to read
the RULES and FAQ before you start using $SITENAME.
EOD;

if ($arr[0] || ENA_EMAIL_CONFIRM)
    mail($email, "$SITENAME user registration confirmation", $body, "From: $SITEEMAIL", "-f$SITEEMAIL");
else
    logincookie($id, $wantpasshash);

header("Refresh: 0; url=ok.php?type=" . (!$arr[0]?"sysop": ENA_EMAIL_CONFIRM ? ("signup&email=" . urlencode($email)):"confirm"));

?>