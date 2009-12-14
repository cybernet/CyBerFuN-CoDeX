<?php

require_once("include/bittorrent.php");

function stdmsg2($heading, $text, $htmlstrip = false)
{
    if ($htmlstrip) {
        $heading = safeChar($heading);
        $text = safeChar($text);
    }
    print("<table class=main width=750 border=0 cellpadding=0 cellspacing=0><tr><td class=embedded>\n");
    if ($heading)
        print("<h2>$heading</h2>\n");
    print("<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td class=text>\n");
    print($text . "</td></tr></table></td></tr></table>\n");
}

if (!mkglobal("type"))
	die();

if ($type == "signup" && mkglobal("email")) {
	stdhead("User signup");
  stdmsg2("Signup successful!",
		"A confirmation email has been sent to the address you specified (" . htmlspecialchars($email) . "). You need to read and respond to this email before you can use your account. If you don't do this, the new account will be deleted automatically after a few days.");
	stdfoot();
}
elseif ($type == "sysop") {
		stdhead("Sysop Account activation");
		stdmsg2("Sysop Account successfully activated!", 
			(isset($CURUSER)?
			"Your account has been activated! You have been automatically logged in. You can now continue to the <a href=\"loginproc/index.php\"><b>main page</b></a> and start using your account.":
			"Your account has been activated! However, it appears that you could not be logged in automatically. A possible reason is that you disabled cookies in your browser. You have to enable cookies to use your account. Please do that and then <a href=\"loginproc/index.php\">log in</a> and try again.")
		);
	stdfoot();
	}
elseif ($type == "confirmed") {
	stdhead("Already confirmed");
	stdmsg2("Already confirmed","This user account has already been confirmed. You can proceed to <a href=\"login.php\">log in</a> with it.");
	stdfoot();
}
elseif ($type == "confirm") {
	stdhead("Signup confirmation");
	stdmsg2("Account successfully confirmed!",
		isset($CURUSER) ? 
			"<p>Your account has been activated! You have been automatically logged in. You can now continue to the <a href=\"loginproc/index.php\"><b>main page</b></a> and start using your account.</p>\n".
			"<p>Before you start using " . $SITENAME . " we urge you to read the <a href=\"rules.php\"><b>RULES</b></a> and the <a href=\"faq.php\"><b>FAQ</b></a>.</p>\n"
			:"Your account has been activated! However, it appears that you could not be logged in automatically. A possible reason is that you disabled cookies in your browser. You have to enable cookies to use your account. Please do that and then <a href=\"login.php\">log in</a> and try again."
	);
	stdfoot();
}
else
	die();

?>