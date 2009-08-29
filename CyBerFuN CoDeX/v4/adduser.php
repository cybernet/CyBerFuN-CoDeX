<?php
require ("include/bittorrent.php");
require_once("include/bbcode_functions.php");
// require_once("include/user_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Smartass!", "What the hell are you doing here?");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "" || $_POST["class"] == "" || $_POST["seedbonus"] == "" || $_POST["modcomment"] == "")
        stderr("Error", "Missing form data.");
    if (!validusername($_POST["username"]))
        stderr("Error", "Invalid username.");
    if ($_POST["password"] != $_POST["password2"])
        stderr("Error", "Passwords mismatch.");
    if (!validemail($_POST['email']))
        stderr("Error", "Not valid email");

    $class = 0 + $_POST["class"];
    $country = 0 + $_POST["country"];
    $seedbonus = 0 + $_POST["seedbonus"];
    $modcomment = $_POST["modcomment"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    // //// email stuff \\\\\\\\
    $email = $_POST["email"];
    // check_banned_emails($email);
    // check if email addy is already in use
    $res = mysql_query("SELECT COUNT(*) FROM users WHERE email='$email'") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    if ($arr[0] != 0)
        stderr("Error", "The e-mail address is already in use.");
    // //// finish email stuff \\\\\\\\
    $secret = mksecret();
    $passhash = md5($secret . $password . $secret);
    $passkey = md5($username . get_date_time() . $passhash);

    mysql_query("INSERT INTO users (email, secret, username, passhash, passkey, class, country, seedbonus, modcomment, status, added, last_access) VALUES(" . implode(",", array_map("sqlesc", array($email, $secret, $username, $passhash, $passkey, $class, $country, $seedbonus, $modcomment, 'confirmed'))) . ",NOW(),NOW())");
    $res = mysql_query("SELECT id FROM users WHERE username=" . sqlesc($username) . "");
    $arr = mysql_fetch_row($res);
    if (!$arr)
        stderr("Error", "Unable to create the account. The user name is possibly already taken.");
    $id = 0 + $arr["0"];
    $msg = sqlesc("Dear $username

:wave:

Welcome to $SITENAME!

Be sure to read the rules and FAQ before you start using the site.

We are a community based Tracker, and we hope that you will get involved in our forums, not just the torrents.

so, enjoy

cheers,

The $SITENAME Staff");
    $subject = sqlesc("Welcome to $SITENAME!");
    $added = sqlesc(get_date_time());
    mysql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, $id, $msg, $added)") or sqlerr(__FILE__, __LINE__);
    write_log("newmember", "User account $id ($username) just added by $CURUSER[username]");

    header("Location: $BASEURL/userdetails.php?id=$id");
    die;
}
stdhead("Add user");

?>
<h1>Add user</h1>
<form method=post action=adduser.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead>User name</td><td><input type=text name=username size=40></td></tr>
<tr><td class=rowhead>Password</td><td><input type=password name=password size=40></td></tr>
<tr><td class=rowhead>Re-type password</td><td><input type=password name=password2 size=40></td></tr>
<tr><td class=rowhead>E-mail</td><td><input type=text name=email size=40></td></tr>
<tr><td class=rowhead>Bonus</td><td><input type=text size=5 name=seedbonus value="0.0"></td></tr>
<?php
// ////////added country by putyn
$countries = "<option value=0>Select one</option>\n";
$ct_r = mysql_query("SELECT id,name FROM countries ORDER BY name") or die;
while ($ct_a = mysql_fetch_array($ct_r))
$countries .= "<option value=$ct_a[id]>$ct_a[name]</option>\n";
print("<tr><td class=rowhead>Country</td><td  colspan=2 align=\"left\"><select name=country>\n$countries\n</select></td></tr>\n");
print("<tr><td class=rowhead>Class</td><td colspan=2 align=left><select name=class>\n");
$maxclass = get_user_class() - 1;
for ($i = 0; $i <= $maxclass; ++$i)
print("<option value=$i>" . get_user_class_name($i) . "\n");
print("</select></td></tr>\n");

?>
<tr><td class=rowhead>Comment</td><td><input type=text size=60 name=modcomment value="Added By <?php echo $CURUSER['username'];
?>" READONLY></td></tr>
<tr><td colspan=2 align=center><input type=submit value="Okay" class=btn></td></tr>
</table>
</form>
<?php stdfoot();
?>
