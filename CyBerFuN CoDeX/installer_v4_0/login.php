<?php
require_once "include/bittorrent.php" ;
require_once "include/user_functions.php";
//ini_set('session.use_trans_sid', '0');
maxcoder();
// Begin the session
// session_start();
// (time() - $_SESSION['captcha_time'] < 10) ? exit('NO SPAM!') : NULL;
stdhead("Login");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= $title ?></title>
</head>
<body>
<?php
unset($returnto);
if (!empty($_GET["returnto"])) {
    $returnto = $_GET["returnto"];
    if (!isset($_GET["nowarn"])) {
    $error = "<div class=\"error\"><b>Error!</b><br />Unfortunately, the page you tried to view <b>can only be used when you're logged in</b>.<br />You will be redirected after a successful login.</div>";
    }
}
if (isset($error)) {
echo $error;
}
?>
<form method="post" action="takelogin.php">
<script type="text/javascript" src="captcha/captcha.js"></script>

<form method="post" action="takelogin.php">
<table align="center" border="0" cellpadding=5>
  <tr><center><font color="white">
	<p>You have <b><?=remaining ();?></b> login attempt(s).</p></center>
<script language="javascript">
function checkform (form) {
  if (form["username"].value == "") {
    alert("Are you nameless ?");
    return false ; }
 return true; }
</script>
    <td class="rowhead">Username:</td>
    <td align="left"><input type="text" size=40 name="username" /></td>
  </tr>
  <tr>
    <td class="rowhead">Password:</td>
    <td align="left"><input type="password" size=40 name="password" /></td>
  </tr>
<!--<tr><td class=rowhead>Duration:</td><td align=left><input type=checkbox name=logout value='yes' checked>Log me out after 15 minutes inactivity</td></tr>-->
  <tr>
    <td>&nbsp;</td>
    <td>
      <div id="captchaimage">
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>" onclick="refreshimg(); return false;" title="Click to refresh image">
      <img class="cimage" src="captcha/GD_Security_image.php?<?php echo time(); ?>" alt="Captcha image is messed atm" />
      </a>
      </div>
     </td>
  </tr>
  <tr>
      <td class="rowhead">PIN:</td>
      <td>
        <input type="text" maxlength="6" name="captcha" id="captcha" onBlur="check(); return false;"/>
      </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="Log in!" class=btn>
       </td>
<p>Signup: <a href="signup.php">here</a> | Recover: <a href="resetpw.php">here</a></p>
</tr>
</table>
       </form>


<?php
if (isset($returnto))
    print("<input type=\"hidden\" name=\"returnto\" value=\"" . safechar($returnto) . "\" />\n");
stdfoot();
?>
</td>
</tr>
<tr>
<table style="background:transparent" border="0" cellpadding="0" cellspacing="0" align=center>
</table>
</td>
</table>

</td>
</tr>
</table>
</body>
</html>