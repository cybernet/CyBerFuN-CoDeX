<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
parked();
?>
<html><head>
<link rel="stylesheet" href="themes/green/green.css" type="text/css">
<title>more clickable smilies</title>
</head>
<BODY BGCOLOR="#000000" TEXT="#ffffff" LINK="#ff0000" VLINK="#808080">

<script language=javascript>

function SmileIT(smile,form,text){
window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+" "+smile+" ";
window.opener.document.forms[form].elements[text].focus();
window.close();
}
</script>

<table class="lista" width="100%" cellpadding="1" cellspacing="1">
<?php

while ((list($code, $url) = each($smilies))) {
    if ($count % 3 == 0)
        echo("\n<tr>");

    echo("\n\t<td class=\"lista\" align=\"center\"><a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . $_GET["form"] . "','" . $_GET["text"] . "')\"><img border=0 src=pic/smilies/" . $url . "></a></td>");
    $count++;

    if ($count % 3 == 0)
        echo("\n</tr>");
} while ((list($code, $url) = each($privatesmilies))) {
    if ($count % 3 == 0)
        echo("\n<tr>");

    echo("\n\t<td class=\"lista\" align=\"center\"><a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . $_GET["form"] . "','" . $_GET["text"] . "')\"><img border=0 src=pic/smilies/" . $url . "></a></td>");
    $count++;

    if ($count % 3 == 0)
        echo("\n</tr>");
}

?>
</tr>
</table>
<div align="center">
<a href="javascript: window.close()"><?php echo CLOSE;
?></a>
</div>
<?php
?>