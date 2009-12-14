<?php

/*
   * @version Codex InstallerV4 Beta based on svnroot/tbdevnet/trunk/Tbdev 01-01-08/
  
*/

$pic_base_url = 'pic/';
$SITENAME = 'tbdev.net =]';
define ('TBVERSION', 'TBDEV.NET-01-01-08');

function safe($var)
{
    return str_replace(array('&', '>', '<', '"', '\''), array('&amp;', '&gt;', '&lt;', '&quot;', '&#039;'), str_replace(array('&gt;', '&lt;', '&quot;', '&#039;', '&amp;'), array('>', '<', '"', '\'', '&'), $var));
}

function stdhead($title = "")
{
    global $SITENAME, $pic_base_url;
    header("Content-Type: text/html; charset=iso-8859-1");
    // header("Pragma: No-cache");
    if ($title == "")
        $title = $SITENAME . (isset($_GET['tbv'])?" (" . TBVERSION . ")":'');
    else
        $title = $SITENAME . (isset($_GET['tbv'])?" (" . TBVERSION . ")":'') . " :: " . safe($title);

    //$stylesheet = "themes/default/default.css";

    //require_once '../styles.php';

    ?>
<html><head>
<title>Pre-Coded Installer</title>
<link rel="stylesheet" href="themes/default/default.css" type="text/css">
<style type="text/css">
p {
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	padding:0px 0px 0px 0px;
}

</style>
<script type="text/javascript">
 
var ClassName = "color"; 
var FocusColor = "#0099FF"; 
 
window.onload = function() {
    var inputfields = document.getElementsByTagName("input");
    for(var x = 0 ; x < inputfields.length ; x++ ) {
        if(inputfields[x].getAttribute("class") == ClassName) {
            inputfields[x].onfocus = function() {
                OriginalColor = this.style.border;
                this.style.border = "2px solid "+FocusColor;
            }
            inputfields[x].onblur = function() {
                this.style.border = OriginalColor;
            }
        }
    }   
}
</script>
</head>
<body style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
<?php

    $w = "width=100%";

    ?>
<table class=mainouter <?php $w;

    ?> border="1" cellspacing="0" cellpadding="10">

<!------------- MENU ------------------------------------------------------------------------>

<?php

} // stdhead
if (get_magic_quotes_gpc() === 1 || get_magic_quotes_runtime() === 1) {
    $error = 1;
    $message = "Disable magic_quotes in your php.ini";
} else {
    $error = 0;
    $message = "Magic quotes are off \o/";
}
stdhead('Installerv4');
require "functions.php";
// Form
function step_1()
{
    global $message, $error;
    echo'
    <form method="post" action="index.php">
     <center><img src=/pic/logo.gif></center>
  <table width="100%" cellpadding="5" border="0" align="center">
    <tr>
      <td colspan="2" class="image">Database Configuration </td>
    </tr>
    <tr>
      <td>Database Server (use localhost if not sure) </td>
      <td><input class="color" name="server" type="text" id="server" value="localhost" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Database Name</td>
      <td><input class="color" name="dbname" type="text" id="dbname" value="tbdev" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Database User </td>
      <td><input class="color" name="dbuser" type="text" id="dbuser" value="tbdev" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Database Password </td>
      <td><input class="color" name="dbpass" type="password" id="dbpass" size="40" maxlength="40" /></td>
    </tr>

    <tr>
      <td colspan="2" class="image">Pre Configuration </td>
    </tr>
    	<tr>
      <td>Site URL</td>
      <td><input class="color" name="baseurl" type="text" id="baseurl" value="http://localhost" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Coder Username - Dont change this.</td>
      <td><input class="color" name="coderuser" type="text" id="coderuser" value="Admin" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Coder Password </td>
      <td><input class="color" name="coderpass" type="password" id="coderpass" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Coder Password Confirm </td>
      <td><input class="color" name="coderpass2" type="password" id="coderpass2" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td>Coder Email </td>
      <td><input class="color" name="codermail" type="text" id="codermail" size="40" maxlength="40" /></td>
    </tr>
    <tr>
      <td colspan="2"><b><div align=center><font size=2>The /include/config.php must be writable (CHMOD 777)</font><br /><font size=2>The /install/secrets.php must be writable (CHMOD 777)</font></div></b>
  ' . (isset($error) && $error == 0?'<p align="center" style="color: green">' . $message . '</p>':'<p align="center" style="color: red">' . $message . '</p>') . '

        </td>

    </tr>
    <tr>
      <td colspan="2"><div align="center">
        <input name="install" type="submit" class="btn" value="Install" />
      </div></td>
    </tr>
  </table>
  <p>&nbsp;</p>
</form>
	';
}

?></table><?php

include('secrets.php');
if (defined("TB_INSTALLED")) {

    ?>
  <table width="100%" cellpadding="5" border="0" align="center">
 <tr><td align="center">
 <title>TBDEV.NET Pre-Coded Installer</title>
<link rel="stylesheet" href="themes/default/default.css" type="text/css">
<style type="text/css">
p {
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	padding:0px 0px 0px 0px;
}

</style>
<h2>Install Finished </h2><br />
<font size="2">You may now set up your site configuration</font><br /><br />
	<a href="../core.php"><font size="3" color="#ff0000">HERE</font></a></td>

	</tr>

</table>

<?php
    die;
    exit;
}
if (isset($_POST['install'])) {
    if ($_POST['install'] || $_GET['install']) {
        update_config();
        basic_query();
        insert_coder();
        finale();

        ?>
<form method="post" type="hidden" action="../admincp.php">
<?php
    }
} else {
    step_1();
}

?>