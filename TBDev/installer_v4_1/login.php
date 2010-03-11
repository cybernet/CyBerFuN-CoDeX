<?php
require_once ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");

session_start();
(time() - $_SESSION['captcha_time'] < 10) ? exit('No Spam - 10 Sec Delay - Stop Hammering !') : NULL;

?>
<html>
<head>
<script language="Javascript">
function switcharea()
{
document.getElementById("username").style.display = 'none';
document.getElementById("usernamebox").style.display = 'none';
document.getElementById("password").style.display = 'block';
document.login.passwordbox.focus();
}
</script>
<script language="Javascript">
function switcharea2()
{
document.getElementById("password").style.display = 'none';
document.getElementById("passwordbox").style.display = 'none';
document.getElementById("captchaimage").style.display = 'block';
document.login.captcha.focus();
}
</script>
<script type="text/javascript" src="captcha/captcha.js"></script>
<style type="text/css">
body {
	background-image:url(images/repeater.jpg);
	background-color:#bf7f51;
	background-position:top;
	text-align:center;
	font-family:Verdana, Geneva, sans-serif;
	color:#a55d35;
}
h4 {
  color:#ff0000;
  font-weight:bold;
	font-family:Verdana, Geneva, sans-serif;
}
#holder {
	background-image:url(images/ubackground.jpg);
	background-color:#bf7f51;
	background-repeat:repeat-y;
	top:0px;
	left:0px;
	position:absolute;
	width:1024px;
	height:100%;
}
#titlebox {
	background-image:url(images/title.png);
	background-repeat:no-repeat;
	background-position:right;
	text-align:center;
	margin-left:auto;
	margin-right:auto;
	margin-top:200px;
	width:656px;
	height:100px;
}
#loginbox {
	margin-left:auto;
	margin-right:auto;
	margin-top:50px;
	margin-bottom:auto;
	width:230px;
	height:81px;
	background-image:url(images/loginbackground.jpg);
	background-repeat:no-repeat;
}
.userspass {
	text-align:left;	
	padding-left:10px;
	padding-top:10px;
	color:#a55d35;
	font-family:Verdana, Geneva, sans-serif;
}
.login {
	text-align:left;	
	color:#a55d35;
	font-family:Verdana, Geneva, sans-serif;
	border-color:#bf7f51;
	border:thin;
	border-style:solid;
	margin-left:10px;
	margin-top:10px;
}
.logincap {
	text-align:left;	
	color:#a55d35;
	font-family:Verdana, Geneva, sans-serif;
	border-color:#bf7f51;
	border:thin;
	border-style:solid;
	margin-left:5px;
	margin-top:10px;
}
#passwordbutton {
	float:right;	
}
#captcha {
	float:left;	
}
</style>
</head>
<body>
<div id="holder">
<div id="titlebox"></div>
<p>Signup: <a href="signup.php">here</a> | Recover: <a href="resetpw.php">here</a></p>
<div id="loginbox">
<form name="login" method="post" action="takelogin.php">
    <div id="username" class="userspass">Username:<br />
        <input size="20" type="text" class="login" id="usernamebox" name="username" onBlur="switcharea()">
    </div>
    <div id="password" class="userspass" style="display:none;">Password:<br />
        <input size="20" type="password" class="login" id="passwordbox" name="password" onBlur="switcharea2()">
    </div>
    <div id="captchaimage" class="userspass" style="display:none;">
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>" onclick="refreshimg(); return false;" title="Click to refresh image">
      <img class="cimage" src="captcha/GD_Security_image.php?<?php echo time(); ?>" alt="Captcha image" />
      </a>
        <input type="text" size="18" maxlength="6" class="logincap" name="captcha" id="captcha" onBlur="check(); return false;"/>
        <input type="image" value="submit" id="passwordbutton" src="images/login.png">
        <tr><td>Duration:</td><td align='left'><input type='checkbox' name='logout' value='yes' />Log me out after 15 minutes inactivity</td></tr>
    </div>
</form>
</div>
<script type="text/javascript" src="entertotab.js"></script>
<script type="text/javascript">
enterToTab(document.forms.login, false);
document.forms.login.elements[0].focus();
</script>
</div>
</body>
</html>