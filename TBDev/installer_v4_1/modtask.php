<?php
// ///////////////updated modtask by Retro//////////////////
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
function puke($text = "w00t")
{
    stderr("w00t", $text);
}
// Check Permission
if ($usergroups['caneditusersettings'] == 'no' OR $usergroups['caneditusersettings'] != 'yes') {
stderr( "Sorry...", "You dont have permission to edit user settings" );
exit;
}

if ($CURUSER['class'] < UC_MODERATOR)
    die();
// Correct call to script
if ((isset($_POST['action'])) && ($_POST['action'] == "edituser")) {
    // Set user id
    if (isset($_POST['userid']))
        $userid = $_POST['userid'];
    else
        die();
    // and verify...
    if (!is_valid_id($userid))
        stderr("Error", "Bad user ID.");
    // Fetch current user data...
    $res = sql_query("SELECT * FROM users WHERE id=" . sqlesc($userid)) or sqlerr(__file__,__line__);
    // Check to make sure u re not editing someone of the same or higher class
    if (get_user_class() <= $userid['class'] && ($CURUSER['id'] != $userid &&
        get_user_class() < UC_MODERATOR))
        stderr('Error', 'You cannot edit someone of the same or higher class.. injecting stuff arent we? Action logged');
    $user = mysql_fetch_assoc($res) or sqlerr(__file__, __line__);
    $curinvites = $user["invites"];
    $curseedbonus = $user["seedbonus"];
    $curhit_and_run_total = $user["hit_and_run_total"];
    $curinvite_on = $user["invite_on"];
    $cursignature = $user["signature"];
    $curtitle = $user["title"];
    $curemail = $user["email"];
    $curavatar = $user["avatar"];
    $curenabled = $user["enabled"];
    $curmaxseeds = $user["maxseeds"];
    $curmaxleeches = $user["maxleeches"];
    $curmaxtotal = $user["maxtotal"];
    $curpost_max = $user["post_max"];
    $curcomment_max = $user["comment_max"];
    $curpm_max = $user["pm_max"];
    $curdonor = $user["donor"];
    $curdonated = $user["donated"];
    $curuploadpos = $user["uploadpos"];
    $cursendpmpos = $user["sendpmpos"];
    $curchatpost = $user["chatpost"];
    $curcasinoban = $user["casinoban"];
    $curblackjackban = $user["blackjackban"];
    $curlotteryban = $user["blackjackban"];
    $curdownloadpos = $user["downloadpos"];
    $curforumpost = $user["forumpost"];
    $cursupport = $user["support"];
    $cursupportfor = $user["supportfor"];
    $curhighspeed = $user["highspeed"];
    $curwebseeder = $user["webseeder"];
    $curclass = $user["class"];
    $curwarned = $user["warned"];
    $curdownloaded = $user["downloaded"];
    $curuploaded = $user["uploaded"];
    $curdownloadtoadd = $user["downloadtoadd"];
    $curuploadtoadd = $user["uploadtoadd"];
    $curwarned = $user["warned"];
    $percwarn = $_POST["warns"];
    $whywarned = $_POST["whywarn"];
    $warncomment = $user["whywarned"];
    $nowdlremoved = $user["dlremoveuntil"];
    $curpercwarn = $user["warns"];
    if ($_POST["warns"] == $user["warns"])
        $downloadpos = $_POST["downloadpos"];
    $immun = $_POST["immun"];
    $curimmun = $user["immun"];
    $curnewupload = $user["uploaded"];
    $curnewdownload = $user["downloaded"];
    $curforum_mod = $user["forum_mod"];
    $curenabled = $user["enabled"];
    $curdisabled = $user["disabled"];
    $updateset = array();

    $modcomment = (isset($_POST['modcomment']) && $CURUSER['class'] == UC_CODER) ? $_POST['modcomment'] :
        $user['modcomment'];
    // Set class
    if ((isset($_POST['class'])) && (($class = $_POST['class']) != $user['class'])) {
        if (!is_valid_user_class($class) || get_user_class() <= $_POST['class'])
            stderr(("Error"), "Bad class :-P");
        // Notify user
        $what = ($class > $user['class'] ? "promoted" : "demoted");
        $msg = sqlesc("You have been $what to '" . get_user_class_name($class) . "' by " .
            $CURUSER['username']);
        $added = sqlesc(get_date_time());
        sql_query("INSERT INTO messages (sender, receiver, msg, added) VALUES(0, $userid, $msg, $added)") or
            sqlerr(__file__, __line__);

        $updateset[] = "class = " . sqlesc($class);

        $modcomment = gmdate("Y-m-d") . " - $what to '" . get_user_class_name($class) .
            "' by $CURUSER[username].\n" . $modcomment;
    }
    // ////////////////////////flood protection/////////
    // Set Post Limit
    if (isset($_POST['post_max'])) {
        $post_max = 0 + $_POST['post_max'];
        $curpost_max = $user['post_max'];

        if (($post_max < 0) or ($post_max > 255))
            $post_max = 20; // Default number

        if ($post_max != $curpost_max) {
            $modcomment = gmdate("Y-m-d") . " - Post Limit changed from " . $curpost_max .
                " to " . $post_max . " by " . $CURUSER['username'] . ".\n" . $modcomment;
            $updateset[] = "post_max = " . sqlesc($post_max);
        }
    }
    // Set Comment Limit
    if (isset($_POST['comment_max'])) {
        $comment_max = 0 + $_POST['comment_max'];
        $curcomment_max = $user['comment_max'];

        if (($comment_max < 0) or ($comment_max > 255))
            $comment_max = 20; // Default number

        if ($comment_max != $curcomment_max) {
            $modcomment = gmdate("Y-m-d") . " - Comment Limit changed from " . $curcomment_max .
                " to " . $comment_max . " by " . $CURUSER['username'] . ".\n" . $modcomment;
            $updateset[] = "comment_max = " . sqlesc($comment_max);
        }
    }
    // Set PM Limit
    if (isset($_POST['pm_max'])) {
        $pm_max = 0 + $_POST['pm_max'];
        $curpm_max = $user['pm_max'];

        if (($pm_max < 0) or ($pm_max > 255))
            $pm_max = 20; // Default number

        if ($pm_max != $curpm_max) {
            $modcomment = gmdate("Y-m-d") . " - PM Limit changed from " . $curpm_max .
                " to " . $pm_max . " by " . $CURUSER['username'] . ".\n" . $modcomment;
            $updateset[] = "pm_max = " . sqlesc($pm_max);
        }
    }
    // ////////////////////torrent- limit mod////////////////
    // Slots
    switch ($_POST["limitmode"]) {
        case "automatic":
        default:
            $maxtotal = 0;
            $maxseeds = 0;
            $maxleeches = 0;
            break;

        case "unlimited":
            $maxtotal = -1;
            $maxseeds = 0;
            $maxleeches = 0;
            break;

        case "manual":
            $maxtotal = intval($_POST["maxtotal"]);
            $maxseeds = intval($_POST["maxseeds"]);
            $maxleeches = intval($_POST["maxleeches"]);

            if ($maxseeds > $maxtotal)
                $maxseeds = $maxtotal;
            if ($maxleeches > $maxtotal)
                $maxleeches = $maxtotal;
            // Allow leeches to be set to 0, but not total and seeds.
            if ($maxtotal <= 0 || $maxleeches < 0 || $maxseeds <= 0)
                stderr("Doh", "You must specify a value in each box");

            break;
    }
    if ($maxtotal <> intval($user["tlimitall"]) || $maxseeds <> intval($user["tlimitseeds"]) ||
        $maxleeches <> intval($user["tlimitleeches"])) {
        $updateset[] = "tlimitall = " . $maxtotal;
        $updateset[] = "tlimitseeds = " . $maxseeds;
        $updateset[] = "tlimitleeches = " . $maxleeches;
        $modcomment = gmdate("Y-m-d") . " - Torrent limit changed: " . $maxleeches .
            " Leeches, " . $maxseeds . " Seeds, Total " . $maxtotal . ".\n" . $modcomment;
        $added = sqlesc(get_date_time());
        $subject = sqlesc("Torrent limit changed.");
        $msg = sqlesc("Torrent limit changed: " . $maxleeches . " Leeches, " . $maxseeds .
            " Seeds, Total " . $maxtotal . " by " . $CURUSER[username] . "");
        sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
            sqlerr(__file__, __line__);
    }
    // End
    // //////////////////new warning system///////////////////
    if ($curimmun == "no" && $immun == "no") {
        if ($percwarn != $curpercwarn) {
            if (!isset($_POST["whywarn"]) || empty($_POST["whywarn"]))
                puke("You have to enter a reason for Warn Adjustment!");
            if ($percwarn > $curpercwarn) {
                if ($percwarn == "30")
                    $dlremovetime = 3;
                elseif ($percwarn == "60")
                    $dlremovetime = 6;
                elseif ($percwarn == "90")
                    $dlremovetime = 9;
                if ($percwarn == "30" || $percwarn == "60" || $percwarn == "90") {
                    if ($nowdlremoved != "0000-00-00 00:00:00")
                        $dlremoveuntil = get_date_time(strtotime($nowdlremoved) + $dlremovetime * 86400);
                    else
                        $dlremoveuntil = get_date_time(gmtime() + $dlremovetime * 86400);
                } else
                    $dlremoveuntil = $nowdlremoved;
                if ($dlremoveuntil != "0000-00-00 00:00:00")
                    $downloadpos = "no";
                else
                    $downloadpos = "yes";
                $newpercwarn = ($curpercwarn + 10);
                $subject = sqlesc("Warn level changed");
                $warncomm = "" . date("d.m.Y") . " - Warn level set to " . $newpercwarn .
                    " % by " . $CURUSER['username'] . " Reason : " . $_POST["whywarn"] . " \n " . $warncomment .
                    "";
                $msg = sqlesc("Warn level set to " . $newpercwarn . " % by " . $CURUSER['username'] .
                    " Reason : " . $_POST["whywarn"] . ".\n " . ($percwarn == 30 || $percwarn == 60 ||
                    $percwarn == 90 ? "Also your DL Rights are disabled until " . date("d.m.Y - H:i:s",
                    strtotime($dlremoveuntil)) . " " : "") . "");
                $modcomment = gmdate("Y-m-d") . " - Warning set to " . $newpercwarn . " % by " .
                    $CURUSER['username'] . ".\n" . $modcomment;
                $added = sqlesc(get_date_time());
                $lastwarned = date("d.m.Y");
                sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
                    sqlerr(__file__, __line__);
                $updateset[] = "lastwarned = '$lastwarned'";
                $updateset[] = "whywarned = " . sqlesc($warncomm);
                $updateset[] = "dlremoveuntil = " . sqlesc($dlremoveuntil);
                $updateset[] = "warns = " . sqlesc($newpercwarn);
                $updateset[] = "downloadpos = " . sqlesc($downloadpos);
                write_log("Member $editedusername was given a " . $percwarn .
                    " % warn level increase by $CURUSER[username]\n", "99B200", "user");
            } elseif ($percwarn < $curpercwarn) {
                $downloadpos = "yes";
                $newpercwarn = ($curpercwarn - 10);
                $subject = sqlesc("Warn level set lower");
                $warncomm = "" . date("d.m.Y") . " - Warn level set to " . $newpercwarn .
                    " % by " . $CURUSER['username'] . " Reason : " . $_POST["whywarn"] . " \n " . $warncomment .
                    "";
                $msg = sqlesc("Warn level set to " . $newpercwarn . " % by " . $CURUSER['username'] .
                    " and your DL Rights are enabled.");
                $modcomment = gmdate("Y-m-d") . " - Warning set to " . $newpercwarn . " % by " .
                    $CURUSER['username'] . ".\n" . $modcomment;
                $added = sqlesc(get_date_time());
                sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
                    sqlerr(__file__, __line__);
                $updateset[] = "warns = " . sqlesc($newpercwarn);
                $updateset[] = "whywarned = " . sqlesc($warncomm);
                $updateset[] = "dlremoveuntil = '0000-00-00 00:00:00'";
                $updateset[] = "downloadpos = " . sqlesc($downloadpos);
                write_log("Member $editedusername was given a " . $percwarn .
                    " % warn level decrease by $CURUSER[username]\n", "99B200", "user");
            }
        }
    }
    if ($immun != $curimmun) {
        if ($immun == 'yes')
            $modcomment = gmdate("Y-m-d") . " - Immunity set by " . $CURUSER['username'] .
                ".\n" . $modcomment;
        else
            $modcomment = gmdate("Y-m-d") . " - Immunity removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
        $updateset[] = "immun = " . sqlesc($immun);
    }
    // ////////////////end warning system/////////////
    // ////////boomarks/////////////////////////
    $addbookmark = $_POST["addbookmark"];
    $bookmcomment = $_POST["bookmcomment"];
    // /////////////////////////////////////
    $updateset[] = "bookmcomment = " . sqlesc($bookmcomment);
    $updateset[] = "addbookmark = " . sqlesc($addbookmark);
    // /////////////////end bookmarks/////////////////////////////
    // === add donated amount to user and to funds table
    if ((isset($_POST['donated'])) && (($donated = $_POST['donated']) != $user['donated'])) {
        $added = sqlesc(get_date_time());
        mysql_query("INSERT INTO funds (cash, user, added) VALUES ($donated, $userid, $added)") or
            sqlerr(__file__, __line__);
        $updateset[] = "donated = " . sqlesc($donated);
        $updateset[] = "total_donated = $user[total_donated] + " . sqlesc($donated);
    }
    // ====end
    // === Set donor - Time based
    if ((isset($_POST['donorlength'])) && ($donorlength = 0 + $_POST['donorlength'])) {
        if ($donorlength == 255) {
            $modcomment = gmdate("Y-m-d") . " - Donor status set by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $msg = sqlesc("You have received donor status from " . $CURUSER['username']);
            $subject = sqlesc("Thank You for Your Donation!");
            $updateset[] = "donoruntil = '0000-00-00 00:00:00'";
        } else {
            $donoruntil = get_date_time(gmtime() + $donorlength * 604800);
            $dur = $donorlength . " week" . ($donorlength > 1 ? "s" : "");
            $msg = sqlesc("Dear " . $user['username'] . "
:wave:
Thanks for your support to $SITENAME!
Your donation helps us in the costs of running the site!
As a donor, you are given some bonus gigs added to your uploaded amount, the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile:

so, thanks again, and enjoy!
cheers,
$SITENAME Staff

PS. Your donator status will last for $dur and can be found on your user details page and can only be seen by you :smile: It was set by " .
                $CURUSER['username']);

            $subject = sqlesc("Thank You for Your Donation!");
            $modcomment = gmdate("Y-m-d") . " - Donator status set for $dur by " . $CURUSER['username'] .
                "\n" . $modcomment;
            $updateset[] = "donoruntil = " . sqlesc($donoruntil);
        }
        $added = sqlesc(get_date_time());
        mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or
            sqlerr(__file__, __line__);
        $updateset[] = "donor = 'yes'";
        $res = mysql_query("SELECT class FROM users WHERE id = $userid") or sqlerr(__file__,
            __line__);
        $arr = mysql_fetch_array($res);
        if ($user['class'] < UC_UPLOADER)
            $updateset[] = "class = '2'"; //=== set this to the number for vip on your server
    }
    // === add to donor length // thanks to CoLdFuSiOn & ShadowLeader
    if ((isset($_POST['donorlengthadd'])) && ($donorlengthadd = 0 + $_POST['donorlengthadd'])) {
        $donoruntil = $user["donoruntil"];
        $dur = $donorlengthadd . " week" . ($donorlengthadd > 1 ? "s" : "");
        $msg = sqlesc("Dear " . $user['username'] . "
:wave:
Thanks for your continued support to $SITENAME!
Your donation helps us in the costs of running the site. Everything above the current running costs will go towards next months costs!
As a donor, you are given some bonus gigs added to your uploaded amount, and, you have the the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile:

so, thanks again, and enjoy!
cheers,
$SITENAME Staff

PS. Your donator status will last for an extra $dur on top of your current donation status, and can be found on your user details page and can only be seen by you :smile: It was set by " .
            $CURUSER['username']);

        $subject = sqlesc("Thank You for Your Donation... Again!");
        $modcomment = gmdate("Y-m-d") . " - Donator status set for another $dur by " . $CURUSER['username'] .
            "\n" . $modcomment;
        $donorlengthadd = $donorlengthadd * 7;
        mysql_query("UPDATE users SET donoruntil = IF(donoruntil='0000-00-00 00:00:00', ADDDATE(NOW(), INTERVAL $donorlengthadd DAY ), ADDDATE( donoruntil, INTERVAL $donorlengthadd DAY)) WHERE id = $userid") or
            sqlerr(__file__, __line__);
        $added = sqlesc(get_date_time());
        mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or
            sqlerr(__file__, __line__);
        $updateset[] = "donated = $user[donated] + " . sqlesc($_POST['donated']);
        $updateset[] = "total_donated = $user[total_donated] + " . sqlesc($_POST['donated']);
    }
    // === end add to donor length
    // === Clear donor if they were bad
    if (isset($_POST['donor']) && (($donor = $_POST['donor']) != $user['donor'])) {
        $updateset[] = "donor = " . sqlesc($donor);
        $updateset[] = "donoruntil = '0000-00-00 00:00:00'";
        $updateset[] = "donated = '0'";
        $updateset[] = "class = '1'"; //=== use this line to set them back to poweruser... change 2 to your poweruser class number
        if ($donor == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Donor status removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $msg = sqlesc("Your donator status has expired.");
            $added = sqlesc(get_date_time());
            $subject = sqlesc("Donator status expired.");
            mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $userid, $msg, $added)") or
                sqlerr(__file__, __line__);
        }
    }
    // ===end
    // Enable / Disable
    if ((isset($_POST['enabled'])) && (($enabled = $_POST['enabled']) != $user['enabled']))
    {
    if ($enabled == 'yes'){
    $modcomment = gmdate("Y-m-d") . " - Enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    write_log ("accenabled"," Account <a href=\"userdetails.php?id=".$user["id"]."\">$user[username]</a> was enabled by <a href=\"userdetails.php?id=".$CURUSER["id"]."\">$CURUSER[username]</a>");
    }else{
    $reasond = $_POST["reasond"];
    if(empty($reasond))
    puke("NO reason for disabling this user");
    $modcomment = gmdate("Y-m-d") . " - Disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
    write_log ("accdisabled"," Account <a href=\"userdetails.php?id=".$user["id"]."\">$user[username]</a> was disabled by <a href=\"userdetails.php?id=".$CURUSER["id"]."\">$CURUSER[username]</a><br/>REASON : $reasond ");
    }
    $updateset[] = "enabled = " . sqlesc($enabled);
    $updateset[] = "disable_reason = " . sqlesc($reasond);
    }
    /// Add remove uploaded
    if ($CURUSER['class']>= UC_ADMINISTRATOR){
        $uploadtoadd = 0 + $_POST["amountup"];
        $downloadtoadd = 0 + $_POST["amountdown"];
        $formatup = $_POST["formatup"];
        $formatdown = $_POST["formatdown"];
        $mpup = $_POST["upchange"];
        $mpdown = $_POST["downchange"];
        if($uploadtoadd > 0)    {
            if($mpup == "plus"){
                $newupload = $user["uploaded"] + ($formatup == mb ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                $modcomment = gmdate("Y-m-d") . " - Added Upload (".$uploadtoadd." ".$formatup .") by " . $CURUSER['username'] ."\n" . $modcomment;
            }
            else{
                $newupload = $user["uploaded"] - ($formatup == mb ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));                 
                if ($newupload>=0)
                        $modcomment = gmdate("Y-m-d") . " - Subtracted Upload (".$uploadtoadd." ".$formatup .") by " . $CURUSER['username'] ."\n" . $modcomment;
            }
            if ($newupload>=0)
                $updateset[] =  "uploaded = ".sqlesc($newupload);
        }

        if($downloadtoadd > 0)     {
            if($mpdown == "plus"){
                $newdownload = $user["downloaded"] + ($formatdown == mb ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                $modcomment = gmdate("Y-m-d") . " - Added Download (".$downloadtoadd." ".$formatdown .") by " . $CURUSER['username'] ."\n" . $modcomment;
            }
            else{
                $newdownload = $user["downloaded"] - ($formatdown == mb ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                if ($newdownload>=0)                        
                $modcomment = gmdate("Y-m-d") . " - Subtracted Download (".$downloadtoadd." ".$formatdown .") by " . $CURUSER['username'] ."\n" . $modcomment;
            }
            if ($newdownload>=0)
                    $updateset[] =  "downloaded = ".sqlesc($newdownload);
        }
    }
    /// End add/remove upload 
    /*
    // Change Uploaded Amount by retro - advised to use this instead :)
    if (((isset($_POST['uploaded'])) && (isset($_POST['uploadbase'])) && (($uploaded =
        $_POST['uploaded']) != ($uploadbase = $_POST['uploadbase'])))) {
        if ($uploaded < $uploadbase)
            $modcomment = gmdate("Y-m-d") . " - Upload reduced to " . $uploaded . " from " .
                $uploadbase . " by " . $CURUSER['username'] . ".\n" . $modcomment;
        else
            $modcomment = gmdate("Y-m-d") . " - Upload increased to " . $uploaded . " from " .
                $uploadbase . " by " . $CURUSER['username'] . ".\n" . $modcomment;

        $updateset[] = "uploaded = " . sqlesc($uploaded);
    }
    // Change Downloaded Amount
    if (((isset($_POST['downloaded'])) && (isset($_POST['downloadbase'])) && (($downloaded =
        $_POST['downloaded']) != ($downloadbase = $_POST['downloadbase'])))) {
        if ($downloaded < $downloadbase)
            $modcomment = gmdate("Y-m-d") . " - Download reduced to " . $downloaded .
                " from " . $downloadbase . " by " . $CURUSER['username'] . ".\n" . $modcomment;
        else
            $modcomment = gmdate("Y-m-d") . " - Download increased to " . $downloaded .
                " from " . $downloadbase . " by " . $CURUSER['username'] . ".\n" . $modcomment;

        $updateset[] = "downloaded = " . sqlesc($downloaded);
    }
    */
    // === Enable / Disable chat box rights
    if ((isset($_POST['chatpost'])) && (($chatpost = $_POST['chatpost']) != $user['chatpost'])) {
        $modcomment = gmdate("Y-m-d") . " - Chat post rights set to " . sqlesc($chatpost) .
            " by " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "chatpost = " . sqlesc($chatpost);
    }
    // Forum Post Enable / Disable
    if ((isset($_POST['forumpost'])) && (($forumpost = $_POST['forumpost']) != $user['forumpost'])) {
        if ($forumpost == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Posting enabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Forum post rights.");
            $msg = sqlesc("Your Posting rights have been given back by " . $CURUSER['username'] .
                ". You can post to forum again.");
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else {
            $modcomment = gmdate("Y-m-d") . " - Posting disabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Forum post rights.");
            $msg = sqlesc("Your Posting rights have been removed by " . $CURUSER['username'] .
                ", Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        }
        $updateset[] = "forumpost = " . sqlesc($forumpost);
    }
    // ///////////Casino ban
    if ((isset($_POST['casinoban'])) && (($casinoban = $_POST['casinoban']) != $user['casinoban'])) {
        if ($casinoban == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Casino ban removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $msg = sqlesc("Your Casino rights have been given back by " . $CURUSER['username'] .
                ". You can use the casino again.");
            $added = sqlesc(get_date_time());
            $subject = sqlesc("Casino Ban.");
            mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
                sqlerr(__file__, __line__);
        } else {
            $modcomment = gmdate("Y-m-d") . " - You have been banned from Casino use by " .
                $CURUSER['username'] . ".\n" . $modcomment;
            $msg = sqlesc("Your Casino rights have been removed by " . $CURUSER['username'] .
                ", Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            $subject = sqlesc("Casino Ban.");
            mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
                sqlerr(__file__, __line__);
        }
        $updateset[] = "casinoban = " . sqlesc($casinoban);
    }
    // ///////////Blackjack ban
    if ((isset($_POST['blackjackban'])) && (($blackjackban = $_POST['blackjackban']) !=
        $user['blackjackban'])) {
        if ($blackjackban == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Blackjack ban removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $msg = sqlesc("Your Blackjack rights have been given back by " . $CURUSER['username'] .
                ". You can use Blackjack again.");
            $added = sqlesc(get_date_time());
            $subject = sqlesc("BlackJack Ban.");
            mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
                sqlerr(__file__, __line__);
        } else {
            $modcomment = gmdate("Y-m-d") . " - You have been banned from Blackjack use by " .
                $CURUSER['username'] . ".\n" . $modcomment;
            $msg = sqlesc("Your Blackjack rights have been removed by " . $CURUSER['username'] .
                ", Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            $subject = sqlesc("BlackJack Ban.");
            mysql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or
                sqlerr(__file__, __line__);
        }
        $updateset[] = "blackjackban = " . sqlesc($blackjackban);
    }
    // lottery ban
    if ((isset($_POST['lotteryban'])) && (($lotteryban = $_POST['lotteryban']) != $user['lotteryban']))
    {
    if ($lotteryban == 'no')
    {
    $modcomment = gmdate("Y-m-d")." - Lottery ban removed by ".$CURUSER['username'].".\n" . $modcomment;
    $msg = sqlesc("Your Lottery rights have been given back by ".$CURUSER['username'].". You can use the lottery again.");
    $added = sqlesc(get_date_time());
    $subject = sqlesc("Lottery Ban.");
    sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
    }
    else
    {
    $modcomment = gmdate("Y-m-d")." - You have been banned from the site lottery by ".$CURUSER['username'].".\n" . $modcomment;
    $msg = sqlesc("Your Lottery rights have been removed by ".$CURUSER['username'].", Please PM ".$CURUSER['username']." for the reason why.");
    $added = sqlesc(get_date_time());
    $subject = sqlesc("Lottery Ban.");
    sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES (0, $userid, $msg, $added, $subject)") or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = "lotteryban = " . sqlesc($lotteryban);
    }
    // Set higspeed Upload Enable / Disable
    if ((isset($_POST['highspeed'])) && (($highspeed = $_POST['highspeed']) != $user['highspeed'])) {
        if ($highspeed == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Highspeed Upload enabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Highspeed uploader status.");
            $msg = sqlesc("You  have been set as a high speed uploader by  " . $CURUSER['username'] .
                ". You can now upload torrents using highspeeds without being flagged as a cheater  .");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($highspeed == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Highspeed Upload disabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Highspeed uploader status.");
            $msg = sqlesc("Your highspeed upload setting has been disabled by " . $CURUSER['username'] .
                ". Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "highspeed = " . sqlesc($highspeed);
    }
    // Change Custom Title
    if ((isset($_POST['title'])) && (($title = $_POST['title']) != ($curtitle = $user['pre_title'])))
    {
    $modcomment = gmdate("Y-m-d") . " - Custom Title changed to '".$title."' from '".$curtitle."' by " . $CURUSER['username'] . ".\n" . $modcomment;
    write_info("User account $userid (<a href='userdetails.php?id=$userid'>$user[username]</a>) Custom Title changed to '".$title."' from '".$curtitle."' by <a href='userdetails.php?id=$CURUSER[id]'>$CURUSER[username]</a>");
    $updateset[] = "pre_title = " . sqlesc($title);
    require_once ("include/bbcode_functions.php");
    $title = format_comment($title,true,false);
    $updateset[] = "title = " . sqlesc($title);
    }
    // The following code will place the old passkey in the mod comment and create
    // a new passkey. This is good practice as it allows usersearch to find old
    // passkeys by searching the mod comments of members.
    $res = sql_query("SELECT warned, enabled, username, class, passhash, passkey FROM users WHERE id=$userid") or
        sqlerr(__file__, __line__);
    // Reset Passkey
    if ((isset($_POST['resetkey'])) && ($_POST['resetkey'])) {
        $newpasskey = md5($arr['username'] . get_date_time() . $arr['passhash']);
        $modcomment = gmdate("Y-m-d") . " - Passkey " . $arr['passkey'] . " Reset to " .
            $newpasskey . " by " . $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "passkey=" . sqlesc($newpasskey);
    }
    // === karma bonus
    if ((isset($_POST['seedbonus'])) && (($seedbonus = $_POST['seedbonus']) != $user['seedbonus'])) {
        $modcomment = gmdate("Y-m-d") . " - seeding bonus set to $seedbonus  by " . $CURUSER['username'] .
            ".\n" . $modcomment;
        $updateset[] = "seedbonus = " . sqlesc($seedbonus);
    }
    // change hit run total
    if ((isset($_POST['hit_and_run_total'])) && (($hit_and_run_total = $_POST['hit_and_run_total']) != ($curhit_and_run_total =
        $user['hit_and_run_total']))) {
        $modcomment = gmdate("Y-m-d") . " - Total Hit and Run amount changed to '" . $hit_and_run_total .
            "' from '" . $curhit_and_run_total . "' by " . $CURUSER['username'] . ".\n" . $modcomment;

        $updateset[] = "hit_and_run_total = " . sqlesc($hit_and_run_total);
    }
    // Add Comment to ModComment
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment']))) {
        $modcomment = gmdate("Y-m-d") . " - " . $addcomment . " - " . $CURUSER['username'] .
            ".\n" . $modcomment;
    }
    // change freeslots
    if ((isset($_POST['freeslots'])) && (($freeslots = $_POST['freeslots']) != ($curfreeslots =
        $user['freeslots']))) {
        $modcomment = gmdate("Y-m-d") . " - freeslots amount changed to '" . $freeslots .
            "' from '" . $curfreeslots . "' by " . $CURUSER['username'] . ".\n" . $modcomment;
    }
    $updateset[] = "freeslots = " . sqlesc($freeslots);

    // Set Upload Enable / Disable
    if ((isset($_POST['uploadpos'])) && (($uploadpos = $_POST['uploadpos']) != $user['uploadpos'])) {
        if ($uploadpos == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Upload enabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Upload Rights.");
            $msg = sqlesc("You have been given upload rights by " . $CURUSER['username'] .
                ". You can now upload torrents.");
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($uploadpos == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Upload disabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Upload Rights.");
            $msg = sqlesc("Your upload rights have been removed by " . $CURUSER['username'] .
                ". Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "uploadpos = " . sqlesc($uploadpos);
    }
    // Set private message rights
    if ((isset($_POST['sendpmpos'])) && (($sendpmpos = $_POST['sendpmpos']) != $user['sendpmpos'])) {
        if ($sendpmpos == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - User Pm system enabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Pm Rights.");
            $msg = sqlesc("You have been given back Pm rights by " . $CURUSER['username'] .
                ". You can now use the message system again.");
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($sendpmpos == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Pm rights disabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Pm Rights.");
            $msg = sqlesc("Your Pm rights have been removed by " . $CURUSER['username'] .
                ". Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "sendpmpos = " . sqlesc($sendpmpos);
    }
    // Set Download Enable / Disable
    if ((isset($_POST['downloadpos'])) && (($downloadpos = $_POST['downloadpos']) !=
        $user['downloadpos'])) {
        if ($downloadpos == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Download enabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Downloading Rights.");
            $msg = sqlesc("Your download rights have been given back by " . $CURUSER['username'] .
                ". You can download torrents again.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, subject, msg, added) VALUES (0, $userid, $subject, $msg, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($downloadpos == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Download disabled by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Downloading Rights.");
            $msg = sqlesc("Your download rights have been removed by " . $CURUSER['username'] .
                ", Please PM " . $CURUSER['username'] . " for the reason why.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, subject, msg, added) VALUES (0, $userid, $subject, $msg, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "downloadpos = " . sqlesc($downloadpos);
    }
    // Avatar Changed
    if ((isset($_POST['avatar'])) && (($avatar = $_POST['avatar']) != ($curavatar =
        $user['avatar']))) {
        $modcomment = gmdate("Y-m-d") . " - Avatar changed from " . htmlspecialchars($curavatar) .
            " to " . htmlspecialchars($avatar) . " by " . $CURUSER['username'] . ".\n" . $modcomment;

        $updateset[] = "avatar = " . sqlesc($avatar);
    }

    // Signature Changed
    if ((isset($_POST['signature'])) && (($signature = $_POST['signature']) != ($cursignature =
        $user['signature']))) {
        $modcomment = gmdate("Y-m-d") . " - Signature changed from " . htmlspecialchars($cursignature) .
            " to " . htmlspecialchars($signature) . " by " . $CURUSER['username'] . ".\n" .
            $modcomment;

        $updateset[] = "signature = " . sqlesc($signature);
    }

    // Set Parking Enable / Disable
    if ((isset($_POST['parked'])) && (($parked = $_POST['parked']) != $user['parked'])) {
        if ($parked == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Parked  by " . $CURUSER['username'] . ".\n" .
                $modcomment;
            $subject = sqlesc("Account parked.");
            $msg = sqlesc("Your account has been set to parked mode by " . $CURUSER['username'] .
                ". You can remove this in your profile when you are ready to use your account again.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($parked == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Parking removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Parked Status Removed.");
            $msg = sqlesc("Your account has been removed from parked status by " . $CURUSER['username'] .
                ", propably because you requested it or did it on accident.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "parked = " . sqlesc($parked);
    }
    // Over-ride auto warns
    if ((isset($_POST['warned'])) && (($warned = $_POST['warned']) != $user['warned'])) {
        if ($warned == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Warned by " . $CURUSER['username'] . ".\n" .
                $modcomment;
            $subject = sqlesc("System Warning.");
            $msg = sqlesc("Your profile has a warning applied by " . $CURUSER['username'] .
                ". Contact admin if this incorrect.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($warned == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Auto system warning removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Warning removed.");
            $msg = sqlesc("Your auto system warn has been removed by " . $CURUSER['username'] .
                ", just be careful in future : ).");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "warned = " . sqlesc($warned);
    }
    // remove users anonymous status
    if ((isset($_POST['anonymous'])) && (($anonymous = $_POST['anonymous']) != $user['anonymous'])) {
        if ($anonymous == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Anonymous status set by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Anonymous status.");
            $msg = sqlesc("Your account has been set to anonymous by " . $CURUSER['username'] .
                ". You can remove this in your profile when you want.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($anonymous == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Anonymous removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Anonymous status.");
            $msg = sqlesc("Your account has been removed from Anonymous status by " . $CURUSER['username'] .
                ", Your rights will be given back once you contact staff.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error

        $updateset[] = "anonymous = " . sqlesc($anonymous);
    }
    // === allow invites
    if ((isset($_POST['invite_on'])) && (($invite_on = $_POST['invite_on']) != $user['invite_on'])) {
        $modcomment = gmdate("Y-m-d") . " - Invites allowed changed from $user[invite_on] to $invite_on by " .
            $CURUSER['username'] . ".\n" . $modcomment;
        $updateset[] = "invite_on = " . sqlesc($invite_on);
    }
    // set webseeder yes no
    if ((isset($_POST['webseeder'])) && (($webseeder = $_POST['webseeder']) != $user['webseeder'])) {
        if ($webseeder == 'yes') {
            $modcomment = gmdate("Y-m-d") . " - Webseeder set by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Webseeder status.");
            $msg = sqlesc("Your now set as a webseeder by " . $CURUSER['username'] .
                ". your torrents will have an image on browse");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } elseif ($webseeder == 'no') {
            $modcomment = gmdate("Y-m-d") . " - Webseeder removed by " . $CURUSER['username'] .
                ".\n" . $modcomment;
            $subject = sqlesc("Webseeder status.");
            $msg = sqlesc("Your account has been removed from webseeder status by " . $CURUSER['username'] .
                ", your torrents wont have the high speed icon anymore.");
            $added = sqlesc(get_date_time());
            mysql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES (0, $userid, $msg, $subject, $added)") or
                sqlerr(__file__, __line__);
        } else
            die(); // Error
        $updateset[] = "webseeder = " . sqlesc($webseeder);
    }
    // change invites
    if ((isset($_POST['invites'])) && (($invites = $_POST['invites']) != ($curinvites =
        $user['invites']))) {
        $modcomment = gmdate("Y-m-d") . " - invite amount changed to '" . $invites .
            "' from '" . $curinvites . "' by " . $CURUSER['username'] . ".\n" . $modcomment;

        $updateset[] = "invites = " . sqlesc($invites);
    }
    // FLS Support
        if (isset($_POST['support']) && ($support = $_POST['support']) != $user['support'] )
        {
                if ($support == 'yes')
                        $modcomment = gmdate("Y-m-d H:i") . " - Promoted to Fls by " . $CURUSER['username'] . "\n" . $modcomment;
                elseif ($support == 'no')
                        $modcomment = gmdate("Y-m-d H:i") . " - Demoted from Fls by " . $CURUSER['username'] . "\n" . $modcomment;
                else
                        die();

                $updateset[] = "support = " . sqlesc($support);
        }

        if (isset($_POST['supportfor']) && ($supportfor = $_POST['supportfor']) != $user['supportfor'] ) {
                $updateset[] = "supportfor = ".sqlesc($supportfor);
        }
    // forum moderator mod by putyn tbdev
    // start
    if (isset($_POST["forum_mod"]) && ($forum_mod = $_POST["forum_mod"]) != $user["forum_mod"]) {
        $whatm = ($forum_mod == "yes" ? "added " : "removed");
        if ($forum_mod == "no") {
            $updateset[] = "forums_mod = ''";
            mysql_query("DELETE FROM forum_mods WHERE uid=" . $user["id"]) or sqlerr(__file__,
                __line__);
        }
        $updateset[] = "forum_mod=" . sqlesc($forum_mod);
        $modcomment = gmdate("Y-m-d") . " " . $CURUSER["username"] . " " . $whatm .
            " forum rights\n" . $modcomment;
    }
    // update forums list
    $forumsc = (isset($_POST["forums_count"]) ? 0 + $_POST["forums_count"] : 0);

    if ($forumsc > 0 && $forum_mod != "no") {
        for ($i = 1; $i < $forumsc + 1; $i++) {
            if (substr($_POST["forums_$i"], 0, 3) == "yes")
                $foo[] = (int)substr($_POST["forums_$i"], 4);
        }
        foreach ($foo as $fo) {
            $boo[] = "(" . $fo . "," . $user["id"] . "," . sqlesc($user["username"]) . ")";
            $boo1[] = "[" . $fo . "]";
        }

        mysql_query("DELETE FROM forum_mods WHERE uid=" . $user["id"]) or sqlerr(__file__,
            __line__);
        mysql_query("INSERT INTO forum_mods(fid,uid,user) VALUES " . join(",", $boo)) or
            sqlerr(__file__, __line__);
        $updateset[] = "forums_mod=" . sqlesc(join("", $boo1));
    }
    // end forum moderator mod
    // ////////////////////////////// dont change anything under here ///////////////////////////////
    /* retros add remove upload/download
    // --------------------------------------------
    // download amount
    // --------------------------------------------
    if ($downloaded && $curdownloaded != $downloaded) {
        if ($downloaded)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) download amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // upload amount
    // --------------------------------------------
    if ($uploaded && $curuploaded != $uploaded) {
        if ($uploaded)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) upload amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    */
    // --------------------------------------------
    // download amount
    // --------------------------------------------
    if ($downloadtoadd && $curdownloadtoadd != $downloadtoadd) {
        if ($newdownload)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) download amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // upload amount
    // --------------------------------------------
    if ($uploadtoadd && $curuploadtoadd != $uploadtoadd) {
        if ($uploadtoadd)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) upload amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }    
    // --------------------------------------------
    // un-warned
    // --------------------------------------------
    if ($warned && $curwarned != $warned) {
        if ($warned == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) warned by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // un-warned
    // --------------------------------------------
    if ($warned && $curwarned != $warned) {
        if ($warned == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) un-warned by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // promote and demote
    // --------------------------------------------
    if ($curclass != $class) {
        if ($class > $curclass ? "promoted" : "demoted")
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) $what to '" .
                get_user_class_name($class) . "' by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // invites rights
    // --------------------------------
    if ($invite_on && $curinvite_on != $invite_on) {
        if ($invite_on == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) invites rights enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // invites rights
    // --------------------------------
    if ($invite_on && $curinvite_on != $invite_on) {
        if ($invite_on == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) invites rights removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // invites amount
    // --------------------------------
    if ($invites && $curinvites != $invites) {
        if ($invites)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) invites amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // seedbonus amount
    // --------------------------------
    if ($seedbonus && $curseedbonus != $seedbonus) {
        if ($seedbonus)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) seedbonus amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // max seed amount
    // --------------------------------
    if ($maxseeds && $curmaxseeds != $maxseeds) {
        if ($maxseeds)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Max seed amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // max leech amount
    // --------------------------------
    if ($maxleeches && $curmaxleeches != $maxleeches) {
        if ($maxleeches)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Max leech amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // max total amount
    // --------------------------------
    if ($maxtotal && $curmaxtotal != $maxtotal) {
        if ($maxtotal)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Max seed/leech total amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
        // --------------------------------
    // reset hit and runs
    // --------------------------------
    if ($hit_and_run_total && $curhit_and_run_total != $hit_and_run_total) {
        if ($hit_and_run_total)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Hit and run total reset by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // max post
    // --------------------------------
    if ($post_max && $curpost_max != $post_max) {
        if ($post_max)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Max post amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // max comment amount
    // --------------------------------
    if ($comment_max && $curcomment_max != $comment_max) {
        if ($comment_max)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Max comments amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // max pm amount
    // --------------------------------
    if ($pm_max && $curpm_max != $pm_max) {
        if ($pm_maxl)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Max pm total amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // add firstline support
    // --------------------------------
    if ($support && $cursupport != $support) {
        if ($support == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) added to FirstLine Support by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // remove firstline support
    // --------------------------------
    if ($support && $cursupport != $support) {
        if ($support == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) removed from FirstLine Support by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // changed support for
    // --------------------------------
    if ($supportfor && $cursupportfor != $supportfor) {
        if ($supportfor)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) support for info changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // forum post possible
    // --------------------------------
    if ($forumpost && $curforumpost != $forumpost) {
        if ($forumpost == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) forum posting enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // forum post possible
    // --------------------------------
    if ($forumpost && $curforumpost != $forumpost) {
        if ($forumpost == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) forum posting disabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // enable download possible
    // --------------------------------
    if ($downloadpos && $curdownloadpos != $downloadpos) {
        if ($downloadpos == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) downloads enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // disable download
    // --------------------------------
    if ($downloadpos && $curdownloadpos != $downloadpos) {
        if ($downloadpos == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) downloads disabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // --------------------------------
    // webseeder
    // --------------------------------
    if ($webseeder && $curwebseeder != $webseeder) {
        if ($webseeder == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Webseeder set by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // webseeder
    // --------------------------------
    if ($webseeder && $curwebseeder != $webseeder) {
        if ($webseeder == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Webseeder set by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // enable upload possible
    // --------------------------------
    if ($uploadpos && $curuploadpos != $uploadpos) {
        if ($uploadpos == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) uploads enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // upload disable
    // --------------------------------
    if ($uploadpos && $curuploadpos != $uploadpos) {
        if ($uploadpos == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) uploads disabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // enable pms
    // --------------------------------
    if ($sendpmpos && $cursendpmpos != $sendpmpos) {
        if ($sendpmpos == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) User Pm's enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // disable pms
    // --------------------------------
    if ($sendpmpos && $cursendpmpos != $sendpmpos) {
        if ($sendpmpos == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) User Pm's disabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // casino ban
    // --------------------------------
    if ($casinoban && $curcasinoban != $casinoban) {
        if ($casinoban == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Casino rights removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // casino ban remove
    // --------------------------------
    if ($casinoban && $curcasinoban != $casinoban) {
        if ($casinoban == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Casino rights enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // blackjack ban
    // --------------------------------
    if ($blackjackban && $curblackjackban != $blackjackban) {
        if ($blackjackban == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Blackjack rights removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // blackjack ban
    // --------------------------------
    if ($blackjackban && $curblackjackban != $blackjackban) {
        if ($blackjackban == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Blackjack rights enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // lottery ban
    // --------------------------------
    if ($lotteryban && $curlotteryban != $lotteryban) {
        if ($lotteryban == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) lottery rights removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // lottery ban
    // --------------------------------
    if ($lotteryban && $curlotteryban != $lotteryban) {
        if ($lotteryban == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) lottery rights enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // shoutbox ban
    // --------------------------------
    if ($chatpost && $curchatpost != $chatpost) {
        if ($chatpost == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Shoutbox rights removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // shoutbox ban
    // --------------------------------
    if ($chatpost && $curchatpost != $chatpost) {
        if ($chatpost == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Shoutbox rights enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // user immune
    // --------------------------------
    if ($immun && $curimmun != $immun) {
        if ($immun == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) immunity enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // user immune
    // --------------------------------
    if ($immun && $curimmun != $immun) {
        if ($immun == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) immunity removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // donated
    // --------------------------------------------
    if ($donated != $curdonated) {
        if ($donated)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) donated amount changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // donor
    // --------------------------------
    if ($donor && $curdonor != $donor) {
        if ($donor == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) received a donor by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------
    // un-donor
    // --------------------------------
    if ($donor && $curdonor != $donor) {
        if ($donor == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) donor was removed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // forum mod
    // --------------------------------------------
    if ($forum_mod != $curforum_mod) {
        if ($forum_mod == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Forum moderator enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // disable
    // --------------------------------------------
    if ($forum_mod != $curforum_mod) {
        if ($forum_mod == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Forum moderator disabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // enable
    // --------------------------------------------
    if ($enabled != $curenabled) {
        if ($enabled == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) enabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // disable
    // --------------------------------------------
    if ($disabled != $curdisabled) {
        if ($disabled == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) disabled by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // highspeed seeder
    // --------------------------------------------
    if ($highspeed != $curhighspeed) {
        if ($highspeed == 'yes')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Highspeed user set by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // highspeed seeder
    // --------------------------------------------
    if ($highspeed != $curhighspeed) {
        if ($highspeed == 'no')
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) Highspeed user returned to normal status by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // changed avatar
    // --------------------------------------------
    if ($avatar && $curavatar != $avatar) {
        if ($avatar)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) avatar changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // changed signature
    // --------------------------------------------
    if ($signature && $cursignature != $signature) {
        if ($signature)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) signature changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // changed email
    // --------------------------------------------
    if ($email && $curemail != $email) {
        if ($email)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) email changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // changed title
    // --------------------------------------------
    if ($title && $curtitle != $title) {
        if ($title)
            write_info("User account $userid (<a href=userdetails.php?id=$userid>$user[username]</a>) title changed by <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a>");
    }
    // --------------------------------------------
    // Add ModComment... (if we changed smt we update otherwise we dont include this..)
    if (($CURUSER['class'] == UC_CODER && ($user['modcomment'] != $_POST['modcomment'] ||
        $modcomment != $_POST['modcomment'])) || ($CURUSER['class'] < UC_CODER && $modcomment !=
        $user['modcomment']))
        $updateset[] = "modcomment = " . sqlesc($modcomment);
    if (sizeof($updateset) > 0)
        mysql_query("UPDATE users SET  " . implode(", ", $updateset) . " WHERE id=" .
            sqlesc($userid) . "") or sqlerr(__file__, __line__);
    status_change($userid);
	//forummods(true);
    $returnto = $_POST["returnto"];
    header("Location: $DEFAULTBASEURL/$returnto");
    die();
}
puke();

?>