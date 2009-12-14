<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Inactive Users - Nosey Cunt !");
// made by putyn tbdev.net
// email part by x0r tbdev.net
// config
$sitename = "www.installerv4.net"; // Sitename, format: site.com
$replyto = "noreply@installerv4.net"; // The Reply-to email.
$record_mail = true; // set this true or false . If you set this true every time whene you send a mail the time , userid , and the number of mail sent will be recorded
$days = 2; //number of days of inactivite
// end config
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];
    if (empty($_POST["userid"]) && (($action == "deluser") || ($action == "mail")))
        stderr("Err", "For this to work you must select at least a user !");

    if ($action == "deluser" && (!empty($_POST["userid"]))) {
        mysql_query("DELETE FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
        stderr("Successfully", "You have successfully deleted the selected accounts! <a href=\"" . $BASEURL . "/inactive.php\">Go back</a>");
    }
    if ($action == "disable" && (!empty($_POST["userid"]))) {
        mysql_query("UPDATE users SET enabled='no' WHERE id IN (" . implode(", ", $_POST['userid']) . ") ");
        stderr("Successfully", "You have successfully disabled the selected accounts! <a href=\"" . $BASEURL . "/inactive.php\">Go back</a>");
    }

    if ($action == "mail" && (!empty($_POST["userid"]))) {
        $res = sql_query("SELECT id, email , username, added, last_access FROM users WHERE id IN (" . implode(", ", $_POST['userid']) . ") ORDER BY last_access DESC ");
        $count = mysql_num_rows($res);
        while ($arr = mysql_fetch_array($res)) {
            $id = $arr["id"];
            $username = $arr["username"];
            $email = safeChar($arr["email"]);
            $added = $arr["added"];
            $last_access = $arr["last_access"];

            $subject = "Your account at $sitename !";
            $message = "Hi!
Your account at $sitename has been marked as inactive and will be deleted. If you wish to remain a member at $sitename, please login.

Your username is: $username
And was created: $added
Last accessed: $last_access

Login at: http://www.$sitename/loginproc/
If you have forgotten your password you can retrieve it at http://www.$sitename/recover.php

New login system installed - if you get a white screen then simply delete your old cookies for installerv4.net then login  
  
Welcome back! //Staff at $sitename
";
            $headers = 'From: no-reply@' . $sitename . "\r\n" . 'Reply-To:' . $replyto . "\r\n" . 'X-Mailer: PHP/' . phpversion();

            $mail = @mail($email, $subject, $message, $headers);
        }

        if ($record_mail) {
            $date = time();
            $userid = $CURUSER["id"];
            if ($count > 0 && $mail)
                mysql_query("update avps set value_i='$date', value_u='$count', value_s='$userid' WHERE arg='inactivemail' ") or sqlerr(__FILE__, __LINE__);
        }

        if ($mail)
            stderr("Success", "Messages sent.");
        else
            stderr("Error", "Try again.");
    }
}
stdhead("Inactive Users");

$dt = sqlesc(get_date_time(gmtime() - ($days * 86400)));
$res = sql_query("SELECT id,username,class,email,uploaded,downloaded,last_access FROM users WHERE last_access<$dt AND status='confirmed' AND enabled='yes' ORDER BY last_access DESC ") or sqlerr(__FILE__, __LINE__);
$count = mysql_num_rows($res);
if ($count > 0) {

    ?>
<script LANGUAGE="JavaScript">

<!-- Begin
var checkflag = "false";
function check(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
return "Uncheck All"; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
return "Check All"; }
}
// End -->
</script>
<?php
    print("<h2>" . safeChar($count) . " accounts inactive for longer than " . safeChar($days) . " days.</h2>");
    print("<form action=\"inactive.php\" method=\"post\">");
    print("<table class=main border=1 cellspacing=0 cellpadding=5><tr>\n");
    print("<td class=colhead>Username</td>");
    print("<td class=colhead>Class</td>");
    print("<td class=colhead>Mail</td>");
    print("<td class=colhead>Ratio</td>");
    print("<td class=colhead>Last Seen</td>");
    print("<td class=colhead align=\"center\">x</td>");

    while ($arr = mysql_fetch_assoc($res)) {
        $ratio = ($arr["downloaded"] > 0 ? number_format($arr["uploaded"] / $arr["downloaded"], 3) : ($arr["uploaded"] > 0 ? "Inf." : "---"));
        $last_seen = (($arr["last_access"] == "0000-00-00 00:00:00") ? "never" : "" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["last_access"])) . "&nbsp;ago");
        $class = get_user_class_name($arr["class"]);
        print("<tr>");
        print("<td><a href=\"userdetails.php?id=" . $arr["id"] . "\">" . $arr["username"] . "</a></td>");
        print("<td>" . $class . "</td>");
        print("<td><a href=\"mailto:" . $arr["email"] . "\">" . safeChar($arr["email"]) . "</a></td>");
        print("<td>" . $ratio . "</td>");
        print("<td>" . $last_seen . "</td>");
        print("<td align=\"center\" bgcolor=\"#FF0000\"><input type=\"checkbox\" name=\"userid[]\" value=\"" . $arr["id"] . "\" /></td>");
        print("</tr>");
    }
    print("<tr><td colspan=\"6\" class=\"colhead\" align=\"center\">
<select name=\"action\">
<option value=\"mail\">Send mail</option>
<option value=\"deluser\" " . ($CURUSER["class"] < UC_ADMINISTRATOR ? "disabled" : "") . ">Delete users</option>
<option value=\"disable\">Disable Accounts</option>
</select>&nbsp;&nbsp;<input type=\"submit\" name=\"submit\" value=\"Apply Changes\"/>&nbsp;&nbsp;<input type=\"button\" value=\"Check all\" onClick=\"this.value=check(form)\"></td></tr>");

    if ($record_mail) {
        $ress = sql_query("SELECT avps.value_s AS userid, avps.value_i AS last_mail, avps.value_u AS mails, users.username FROM avps LEFT JOIN users ON avps.value_s=users.id WHERE avps.arg='inactivemail' LIMIT 1");
        $date = mysql_fetch_assoc($ress);
        if ($date["last_mail"] > 0)
            print("<tr><td colspan=\"6\" class=\"colhead\" align=\"center\" style=\"color:red;\">Last Email sent by <a href=\"usersdetails.php?id=" . htmlspecialchars($date["userid"]) . "\">" . htmlspecialchars($date["username"]) . "</a> on <b>" . gmdate("d M Y", $date["last_mail"]) . " -  " . $date["mails"] . "</b> Email" . ($date["mails"] > 1 ? "s" : "") . "  sent !</td></tr>");
    }
    print("</table></form>");
} else {
    print("<h2>No account inactive for longer than " . $days . " days.</h2>");
}
stdfoot();

?>
