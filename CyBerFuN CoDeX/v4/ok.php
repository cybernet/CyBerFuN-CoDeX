<?php
require_once("include/bittorrent.php");

dbconn();

if (!mkglobal("type"))
    die();

if ($type == "signup" && mkglobal("email")) {
    stdhead("User signup");
    stdmsg("Signup successful!",
        "A confirmation email has been sent to the address you specified (" . safechar($email) . "). You need to read and respond to this email before you can use your account. If you don't do this, the new account will be deleted automatically after a few days.");
} elseif ($type == "invite" && mkglobal("email")) {
    stdhead("User invite");
    stdmsg("<font color=red>Invite successful!</font>",
        "<font color=red>A confirmation email has been sent to the address you specified (" . safeChar($email) . "). They need to read and respond to this email before they can use their account. If they don't do this, the new account will be deleted automatically after a few days.</font>");
} elseif ($type == "sysop") {
    stdhead("Sysop Account activation");
    print("<h1><font color=red>Sysop Account successfully activated!</h1></font>\n");
    if (isset($CURUSER))
        print("<p><font color=red>Your account has been activated! You have been automatically logged in. You can now continue to the <a href=\"index.php\"><b>main page</b></a> and start using your account.</font></p>\n");
    else
        print("<p><font color=red>Your account has been activated! However, it appears that you could not be logged in automatically. A possible reason is that you disabled cookies in your browser. You have to enable cookies to use your account. Please do that and then <a href=\"login.php\">log in</a> and try again.</font></p>\n");
} elseif ($type == "confirmed") {
    stdhead("Already confirmed");
    print("<h1><font color=red>Already confirmed</font></h1>\n");
    print("<p><font color=red>This user account has been confirmed. You can proceed to <a href=\"login.php\">log in</a> with it.</font></p>\n");
} elseif ($type == "confirm") {
    if (isset($CURUSER)) {
        stdhead("Signup confirmation");
        print("<h1><font color=red>Account successfully confirmed!</font></h1>\n");
        print("<p><font color=red>Your account has been activated! You have been automatically logged in. You can now continue to the <a href=\"/\"><b>main page</b></a> and start using your account.</font></p>\n");
        print("<p><font color=red>Before you start using ".$SITENAME." we urge you to read the <a href=\"rules.php\"><b>RULES</b></a> and the <a href=\"faq.php\"><b>FAQ</b></a>.</font></p>\n");
    } else {
        stdhead("Signup confirmation");
        print("<h1><font color=red>Account successfully confirmed!</h1></font>\n");
        print("<p><font color=red>This user account has been confirmed. You can proceed to <a href=\"login.php\">log in</a> with it.</font></p>\n");
    }
} else
    die();
stdfoot();

?>