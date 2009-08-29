<?php
/**
* Updated takeeditusercp.php By Bigjoos & putyn
* Credits: Djlee's code from takeprofileedit.php - Retro for the original idea - credits to the original usercp coder
*/
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
function bark($msg)
{
    genbark($msg, "Update failed!");
}
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

// ====== CoLdFuSiOn's anti injection script
function check_avatar($url)
{
    $allow_dynamic_img = 0; //You alter this value at your own peril!
    $img_ext = 'jpg,gif,png'; //image extension. Careful what you put here!
    if (!$url) return; //empty? send it back!
    $url = trim($url);

    $default = 'http://localhost/warn.jpg'; //this is what is returned if all fails
    /*
        * Check for any dynamic stuff!
        */

    if ($allow_dynamic_img != 1) {
        if (preg_match("/[?&;]/", $url)) {
            return $default;
        }

        if (preg_match("/javascript(\:|\s)/i", $url)) {
            return $default;
        }
    }

    /*
        * Check the extension
        */

    if ($img_ext) {
        $extension = preg_replace("#^.*\.(\S+)$#", "\\1", $url);

        $extension = strtolower($extension);

        if ((! $extension) OR (preg_match("#/#", $extension))) {
            return $default;
        }

        $img_ext = strtolower($img_ext);

        if (! preg_match("/" . preg_quote($extension, '/') . "(,|$)/", $img_ext)) {
            return $default;
        }
        // $url = xss_detect($url);
        if (xss_detect($url))
            return 'wanker!!!';
    }

    /*
        * Take a stab at getting a good image url
        */

    if (!preg_match("/^(http|https|ftp):\/\//i", $url)) {
        return $default;
    }
    /*
        * done all we can at this point!
        */

    $url = str_replace(' ', '%20', $url);

    return $url;
}

$action = isset($_POST["action"]) ? $_POST["action"] : '';
$updateset = array();

if ($action == "avatar") {
    // ///////////avatar check
    $avatars = ($_POST['avatars'] != '' ? 'yes' : 'no');
    $avatar = check_avatar($_POST['avatar']);
    $updateset[] = 'avatars = ' . sqlesc($avatars);
    $updateset[] = 'avatar = ' . sqlesc($avatar);
    // //////custom-title check/////////////////
    if(isset($_POST["title"]) && ($CURUSER["donor"] === "yes" || $CURUSER["class"] >= UC_MODERATOR) && ($title = $_POST["title"]) != $CURUSER["title"]) {
    $ctnotallow = array("sysop", "administrator", "admin", "mod", "moderator", "vip", "motherfucker");
    if (in_array(strtolower($title), ($ctnotallow)))
    bark("Error, Invalid custom title!");
    $updateset[] = "pre_title = ".sqlesc($title);
    $title = format_comment($title,true,false);
    $updateset[] = "title = " . sqlesc($title);
    }
    
    /*
    if (isset($_POST["title"]) && $CURUSER["class"] >= UC_VIP && ($title = $_POST["title"]) != $CURUSER["title"]) {
        $ctnotallow = array("sysop", "administrator", "admin", "mod", "moderator", "vip", "motherfucker");
        if (in_array(strtolower($title), ($ctnotallow)))
            bark("Error, Invalid custom title!");
        $updateset[] = "title = " . sqlesc($title);
    }
    */
    $action = "avatar";
} else if ($action == "signature") {
    // ---- Signature check
    if (($signatures = ($_POST["signatures"] != "" ? "yes" : "no")) != $CURUSER["signatures"])
        $updateset[] = "signatures = '$signatures'";
    if (isset($_POST["signature"]) && ($signature = $_POST["signature"]) != $CURUSER["signature"])
        $updateset[] = "signature = " . sqlesc($signature);
    // ---- end sig check
    // ////user-info check////////////
    if (isset($_POST["info"]) && (($info = $_POST["info"]) != $CURUSER["info"]))
        $updateset[] = "info = " . sqlesc($info);
    $action = "signature";
} else if ($action == "security") {
    // //////password////////
    $changedemail = 0;
    if (!mkglobal("email:chpassword:passagain:secretanswer"))
        bark("missing form data");
    if ($chpassword != "") {
        if (strlen($chpassword) > 40)
            bark("Sorry, password is too long (max is 40 chars)");
        if ($chpassword != $passagain)
            bark("The passwords didn't match. Try again.");

        $sec = mksecret();
        $passhash = md5($sec . $chpassword . $sec);

        $updateset[] = "secret = " . sqlesc($sec);
        $updateset[] = "passhash = " . sqlesc($passhash);
        logincookie($CURUSER['id'], md5($passhash . $_SERVER['REMOTE_ADDR']));
    }
    if ($email != $CURUSER["email"]) {
        if (!validemail($email))
            bark("That doesn't look like a valid email address.");
        $r = mysql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr();
        if (mysql_num_rows($r) > 0)
            bark("The e-mail address you entered is already in use.");
        $changedemail = 1;
    }
    // /////////secret hint and answer by neptune///////////
    if ($secretanswer != '') {
        if (strlen($secretanswer) > 40) bark("Sorry, secret answer is too long (max is 40 chars)");
        if (strlen($secretanswer) < 6) bark("Sorry, secret answer is too sort (min is 6 chars)");

        $new_secret_answer = md5($secretanswer);
        $updateset[] = "hintanswer = " . sqlesc($new_secret_answer);
    }
    // //passkey///
    if ($_POST['resetpasskey'] == 1) {
        $res = mysql_query('SELECT username, passhash, oldpasskey, passkey FROM users WHERE id=' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res);
        $oldpasskey = "[$arr[passkey]]$arr[oldpasskey]";
        if (strlen($oldpasskey) > 30)
            stderr('Error', 'You have reset your passkey too many times, ask an admin for permission');
        $updateset[] = 'oldpasskey = ' . sqlesc($oldpasskey);
        $passkey = md5($arr['username'] . get_date_time() . $arr['passhash']);
        $updateset[] = 'passkey = ' . sqlesc($passkey);
    }
    // ///parked/////
    if (isset($_POST["parked"]) && ($parked = $_POST["parked"]) != $CURUSER["parked"])
        $updateset[] = "parked = " . sqlesc($parked);
    // //////////anonymous user/////
    if (($anonymous = ($_POST["anonymous"] != "" ? "yes" : "no")) != $CURUSER["anonymous"])
        $updateset[] = "anonymous = '$anonymous'";
    $anonymoustopten = ($_POST["anonymoustopten"] != "" ? "yes" : "no");
    $updateset[] = "anonymoustopten = " . sqlesc($anonymoustopten);
    // ///////////hide snatch lists/////////////
    if (isset($_POST["hidecur"]) && ($hidecur = $_POST["hidecur"]) != $CURUSER["hidecur"])
        $updateset[] = "hidecur = " . sqlesc($hidecur);
    // ////////////secret hint and answer
    if (isset($_POST["changeq"]) && (($changeq = (int)$_POST["changeq"]) != $CURUSER["passhint"]) && is_valid_id($changeq))
        $updateset[] = "passhint = " . sqlesc($changeq);
    // /////////secret hint and answer/////////
    $action = "security";
} else if ($action == "torrents") {
    // ////Get default cats- notifs//////
    $r = mysql_query("SELECT id FROM categories") or sqlerr();
    while ($a = mysql_fetch_assoc($r))
    $catnotifs = $catnotifs . ($_POST["cat$a[id]"] == 'yes' ? "[cat$a[id]]" : "");
    if (($notifs = ($_POST["pmnotif"] == 'yes' ? "[pm]" : "") . ($catnotifs) . ($_POST['emailnotif'] == 'yes' ? "[email]" : "")) != $CURUSER['notifs'])
        $updateset[] = "notifs = '$notifs'";
    // ///imagecats//////
    $imagecats = (isset($_POST['imagecats']) && $_POST["imagecats"] != "" ? "yes" : "no");
    $updateset[] = "imagecats= '$imagecats'";
    // ////////highlight torrent status on browse////
    $ttablehl = ($_POST["ttablehl"] == "yes" ? "yes" : "no");
    $updateset[] = "ttablehl = " . sqlesc($ttablehl);
    // /////split torrents by day///////////
    $split = ($_POST["split"] == "yes" ? "yes" : "no");
    $updateset[] = "split = " . sqlesc($split);
    // /////show torrents on homepage///////////
    $tohp = ($_POST["tohp"] == "yes" ? "yes" : "no");
    $updateset[] = "tohp = " . sqlesc($tohp);
    // /////show recommended torrents on homepage///////////
    $rohp = ($_POST["rohp"] == "yes" ? "yes" : "no");
    $updateset[] = "rohp = " . sqlesc($rohp);
    // ////////////User class colour on browse///
    $view_uclass = (isset($_POST['view_uclass']) && $_POST["view_uclass"] != "" ? "yes" : "no");
    $updateset[] = "view_uclass= '$view_uclass'";
    // ///////////clear new tag/////pdq////////
    $update_new = (isset($_POST['update_new']) && $_POST["update_new"] != "" ? "yes" : "no");
    $updateset[] = "update_new = " . sqlesc($update_new);
    // /commentpm////
    $commentpm = $_POST["commentpm"];
    $updateset[] = "commentpm = " . sqlesc($commentpm);
    // //comment pm///////////
    // /delete pm////
    $deletepm = $_POST["deletepm"];
    $updateset[] = "deletepm = " . sqlesc($deletepm);
    // //comment pm///////////
    $action = "torrents";
} else if ($action == "personal") {
    if (is_valid_id($_POST["download"]))
        $updateset[] = "download = " . sqlesc($_POST["download"]);
    if (is_valid_id($_POST["upload"]))
        $updateset[] = "upload = " . sqlesc($_POST["upload"]);
    // ////////////////////////////////////////////////////////////////////////
    if (isset($_POST['stylesheet']) && (($stylesheet = (int)$_POST['stylesheet']) != $CURUSER['stylesheet']) && is_valid_id($stylesheet))
        $updateset[] = 'stylesheet = ' . sqlesc($stylesheet);
    //  //////////cat icons
    if (isset($_POST['categorie_icon']) && (($categorie_icon = (int)$_POST['categorie_icon']) != $CURUSER['categorie_icon']) && is_valid_id($categorie_icon))
        $updateset[] = 'categorie_icon = ' . sqlesc($categorie_icon);
    ////////////
    // /////////////////////////////////////////////////////////////////////////////////////////////
    if (isset($_POST["timezone"]) && (($timezone = 0 + $_POST["timezone"]) != $CURUSER["timezone"]))
        $updateset[] = "timezone = $timezone";
    if (($dst = ($_POST["dst"] != "" ? 60 : 0)) != $CURUSER["dst"])
        $updateset[] = "dst = '$dst'";
    if (isset($_POST["country"]) && (($country = $_POST["country"]) != $CURUSER["country"]) && is_valid_id($country))
        $updateset[] = "country = $country";
    if (isset($_POST["torrentsperpage"]) && (($torrentspp = min(100, 0 + $_POST["torrentsperpage"])) != $CURUSER["torrentsperpage"]))
        $updateset[] = "torrentsperpage = $torrentspp";
    if (isset($_POST["topicsperpage"]) && (($topicspp = min(100, 0 + $_POST["topicsperpage"])) != $CURUSER["topicsperpage"]))
        $updateset[] = "topicsperpage = $topicspp";
    if (isset($_POST["postsperpage"]) && (($postspp = min(100, 0 + $_POST["postsperpage"])) != $CURUSER["postsperpage"]))
        $updateset[] = "postsperpage = $postspp";
    if (isset($_POST["gender"]) && ($gender = $_POST["gender"]) != $CURUSER["gender"])
        $updateset[] = "gender = " . sqlesc($gender);
    $shoutboxbg = 0 + $_POST["shoutboxbg"];
    $updateset[] = "shoutboxbg = " . sqlesc($shoutboxbg);
    // /////show birthdays on homepage///////////
    $bohp = ($_POST["bohp"] == "yes" ? "yes" : "no");
    $updateset[] = "bohp = " . sqlesc($bohp);
    // /////forum online users as avatar///////////
    $forumview = (isset($_POST['forumview']) && $_POST["forumview"] != "" ? "yes" : "no");
    $updateset[] = "forumview= '$forumview'";
    // //////////////members birthdays
    if (($year = $_POST["year"]) > 0 && ($month = $_POST["month"]) > 0 && ($day = $_POST["day"]) > 0 && $CURUSER['birthday'] != ($bday = date($year . $month . $day)))
        if ($bday != '--')
            $updateset[] = "birthday= " . sqlesc($bday);
        $action = "personal";
    } else if ($action == "pm") {
        if (isset($_POST["acceptpms"]) && ($acceptpms = $_POST["acceptpms"]) != $CURUSER["acceptpms"])
            $updateset[] = "acceptpms = " . sqlesc($acceptpms);
        if (($deletepms = ($_POST["deletepms"] != "" ? "yes" : "no")) != $CURUSER["deletepms"])
            $updateset[] = "deletepms = '$deletepms'";
        if (($savepms = ($_POST["savepms"] != "" ? "yes" : "no")) != $CURUSER["savepms"])
            $updateset[] = "savepms = '$savepms'";
        // /////freinds/////
        $showfriends = ($_POST["showfriends"] != "" ? "yes" : "no");
        $updateset[] = "showfriends = '$showfriends'";
        // /pm subscribe////
        $subscription_pm = $_POST["subscription_pm"];
        $updateset[] = "subscription_pm = " . sqlesc($subscription_pm);
        // //pm subscribe///////////
        $action = "";
    }

    if ($changedemail) {
        $sec = mksecret();
        $hash = md5($sec . $email . $sec);
        $obemail = urlencode($email);
        $updateset[] = "editsecret = " . sqlesc($sec);
        $thishost = $_SERVER["HTTP_HOST"];
        $thisdomain = preg_replace('/^www\./is', "", $thishost);
        $body = <<<EOD
You have requested that your user profile (username {$CURUSER["username"]})
on $thisdomain should be updated with this email address { (htmlspecialchars($email)) }
as user contact.

If you did not do this, please ignore this email. The person who entered your
email address had the IP address {$_SERVER["REMOTE_ADDR"]}. Please do not reply.

To complete the update of your user profile, please follow this link:

http://$thishost/confirmemail.php/{$CURUSER["id"]}/$hash/$obemail

Your new email address will appear in your profile after you do this. Otherwise
your profile will remain unchanged.
EOD;

        mail($email, "$thisdomain profile change confirmation", $body, "From: $SITEEMAIL", "-f$SITEEMAIL");

        $urladd = "&mailsent=1";
    }
    mysql_query("UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__, __LINE__);
    header("Location: $BASEURL/usercp.php?edited=1&action=$action" . $urladd);

    ?>