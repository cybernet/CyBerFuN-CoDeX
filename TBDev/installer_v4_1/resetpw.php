<?php
/**
*
* @author Neptune
* @poject TBDev.Net
* @category Add-Ons
* @date Saturday, 11-1
* @time 13 min
* @copyright 2008
*/
require('include/bittorrent.php');
dbconn();
maxcoder();
if ($CURUSER) stderr('Error', 'What the hell are you trying to do? You are already logged in!');

$step = (isset($_GET["step"]) ? (int)$_GET["step"] : (isset($_POST["step"]) ? (int)$_POST["step"] : ''));

if ($step == '1') {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!mkglobal("email:captcha")) die();

        session_start();

        if (empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha)) {
            stderr('Error', 'The characters you type must match the characters in the picture. Please try again.');
        }

        if (empty($email)) stderr('Error', 'You must enter your email address!');

        if (!validemail($email)) stderr("Error", "That doesn't look like a valid email address!");

        $check = sql_query('SELECT id, status, passhint, hintanswer FROM users WHERE email = ' . sqlesc($email)) or sqlerr(__FILE__, __LINE__);
        $assoc = mysql_fetch_assoc($check) or stderr('Error', 'This email address was not found in the database.');

        if (empty($assoc['passhint']) || empty($assoc['hintanswer'])) {
            stderr('Error', 'You cannot reset your password because you have not yet definied your question/secret aswer!');
        }
        if ($assoc['status'] != 'confirmed') {
            stderr('Error', 'You cannot reset your password because your account has not yet been confirmed!');
        } else {
            stdhead();

            ?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];
            ?>?step=2"><table align="center" border="1" cellspacing="0" cellpadding="10"><tr><td class="rowhead">Question</td>
<?php

            $id[1] = '/1/';
            $id[2] = '/2/';
            $id[3] = '/3/';
            $id[4] = '/4/';
            $id[5] = '/5/';
            $id[6] = '/6/';

            $question[1] = "Mother's birthplace";
            $question[2] = 'Best childhood friend';
            $question[3] = 'Name of first pet';
            $question[4] = 'Favorite teacher';
            $question[5] = 'Favorite historical person';
            $question[6] = "Grandfather's occupation>";

            $passhint = preg_replace($id, $question, (int)$assoc['passhint']);

            ?>
<td><i><b><?php echo $passhint;
            ?> ?</b></i></td><input type="hidden" name="id" value="<?php echo (int)$assoc['id'];
            ?>"><tr><td class="rowhead">Secret Answer</td><td><input type="text" size="40" name="answer"></td></tr><tr><td colspan="2" align="center"><input type="submit" value='Next' class="btn" /></td></tr></table>

<?php }
    }
} elseif ($step == '2') {
    if (!mkglobal('id:answer')) die();

    $select = sql_query('SELECT id, username, hintanswer FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $fetch = mysql_fetch_assoc($select);

    if (!$fetch) stderr('Error', 'This user was not found in the database!');

    if (empty($answer)) stderr('Error', 'You must type your secret answer!');

    if ($fetch['hintanswer'] != md5($answer)) {
        $ip = getip();
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        $msg = "" . safeChar($fetch['username']) . ", on " . get_date_time() . ", somebody (probably you), tried to set a new password for this account by question & secret answer method but failed!" . "\n\nTheir IP Address was : " . $ip . " (" . @gethostbyaddr($ip) . ")" . "\n Their user agent was: " . $useragent . "\n\n If this wasn't you please report this event to a member of staff!\n Thank you.\n";

        sql_query('INSERT INTO messages (receiver, msg, added) VALUES (' .
            sqlesc((int)$fetch['id']) . ', ' . sqlesc($msg) . ', ' . sqlesc(get_date_time()) . ')') or sqlerr(__FILE__, __LINE__);

        stderr('Error', 'Your secret answer must match the question you provided for your account. Please try again.');
    } else {
        stdhead();
        echo '<form method="post" action="?step=3"><table align=center border="1" cellspacing="0" cellpadding="10"><tr><td class="rowhead">New password</td><td><input type="password" size=40 name="newpass"></td></tr><tr><td class="rowhead">Confirm password</td><td><input type="password" size=40 name="newpassagain"></td></tr><tr><td colspan="2" align="center"><input type="submit" value="Change it!" class="btn" /><input type="hidden" name="id" value="' . (int)$fetch['id'] . '" /></td></tr></table>';
    }
} elseif ($step == '3') {
    if (!mkglobal('id:newpass:newpassagain')) die();

    $select = sql_query('SELECT id, editsecret FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $fetch = mysql_fetch_assoc($select) or stderr('Error', 'This user was not found in the database!');

    if (empty($newpass)) stderr('Error', 'You must type your new password!');
    if ($newpass != $newpassagain) stderr("Error", "The passwords didn't match! Please try again.");
    if (strlen($newpass) < 6) stderr("Error", "Sorry, password is too short (min is 6 chars)");
    if (strlen($newpass) > 40) stderr("Error", "Sorry, password is too long (max is 40 chars)");

    $secret = mksecret();
    $newpassword = md5($secret . $newpass . $secret);

    sql_query('UPDATE users SET secret = ' . sqlesc($secret) . ', editsecret = "", passhash=' . sqlesc($newpassword) . ' WHERE id = ' . sqlesc($id) . ' AND editsecret = ' . sqlesc($fetch['editsecret']));

    if (!mysql_affected_rows()) stderr('Error', 'Unable to update user data. Please contact an administrator about this error.');
    else stderr('Success', 'Your password has been updated! Click <a href="/loginproc/index.php" class="altlink">here</a> to log in with your new password!', false);
} else {
    session_start();
    stdhead('Reset Lost Password');

    ?>
<script type="text/javascript" src="captcha/captcha.js"></script>
<br />
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];
    ?>?step=1"><table align="center" border="1" cellspacing="0" cellpadding="10">
<tr><td class="rowhead"><p><font color="aqua">Before you can reset your password, you need to type<br /> your email adress and the characters in the picture below</font></p></td></tr>
<tr><td class="rowhead">E-Mail Adress</td><td><input type="text" size="40" name="email"></td></tr><tr><td class="rowhead">Picture</td><td><div id="captchaimage"><a href="<?php echo $_SERVER['PHP_SELF'];
    ?>" onclick="refreshimg(); return false;" title="Click to refresh image"><img class="cimage" src="captcha/GD_Security_image.php?<?php echo time();
    ?>" border="0" "="Captcha image" /></a></div></td></tr><tr><td class="rowhead">Characters</td><td><input type="text" maxlength="6" name="captcha" id="captcha" onBlur="check(); return false;"/></td></tr><tr><td colspan="2" align="center"><input type="submit" value="Recover!" style='height: 25px'></td></tr></table></form>
<?php
    // require_once ("themes/default/stdfoot.php");
}
