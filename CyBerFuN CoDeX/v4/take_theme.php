<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stylesheet = 0 + $_POST["stylesheet"];

    if (is_valid_id($stylesheet))

        mysql_query("UPDATE users SET stylesheet=$stylesheet WHERE id = " . $CURUSER["id"]);

    ?>
<script language="JavaScript" type="text/javascript">
<!--
opener.location.reload(true);
self.close();
// -->
</script>
<?php
} else {
    $stylesheets = "<option value=0>Selected one</option>\n";
    include 'include/cache/stylesheets.php';
    foreach ($stylesheets as $stylesheet)
    $stylesheets .= "<option value=$stylesheet[id]" . ($CURUSER["stylesheet"] == $stylesheet['id'] ? " selected" : "") . ">$stylesheet[name]</option>\n";

    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$SITENAME?> themes</title>
</head>

<body style="background:#666666; color:#CCCCCC;">
<div align="center" style="width:200px"><fieldset>
	<legend><?=$SITENAME?> change theme</legend>
<form action="take_theme.php" method="post">
    		<p align="center"><select name="stylesheet" onchange="this.form.submit();" size="1" style="font-family: Verdana; font-size: 8pt; color: #000000; border: 1px solid #808080; background-color: #ececec">
	<?=$stylesheets?></select></p>
    		<p><input type="hidden" name="ref" value="<?=$_SERVER["REQUEST_URI"]?>" /></p>
    </form>
</fieldset></div>
</body>
</html>
<?php }
?>