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

if ($CURUSER) {
    $ss_a = @mysql_fetch_array(@sql_query("select uri from stylesheets where id=" . $CURUSER['stylesheet']));
    if ($ss_a) $ss_uri = $ss_a['uri'];
}
if (!$ss_uri) {
    ($r = sql_query("SELECT uri FROM stylesheets WHERE id=1")) or sqlerr(__FILE__, __LINE__);
    ($a = mysql_fetch_array($r));
    $ss_uri = $a['uri'];
}
if ($CURUSER['smile_until'] == '0000-00-00 00:00:00')
    stderr("Error", "you do not have access!");

?>
<html><head>
<script language=javascript>

function SmileIT(smile,form,text){
window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+" "+smile+" ";
window.opener.document.forms[form].elements[text].focus();
}
</script>
<title>Custom Smilies</title>
<link rel="stylesheet" href="themes/default/default.css" type="text/css">
</head>
<h2>Custom Smilies</h2>
<table class="lista" width="100%" cellpadding="1" cellspacing="1">
<tr>
<?php
$ctr = 0;
global $customsmilies;
while ((list($code, $url) = each($customsmilies))) {
    if ($count % 3 == 0)
        echo'<tr>';
    echo"<td align=center><a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlentities($_GET['form']) . "','" . htmlentities($_GET['text']) . "')\"><img border=0 src=/pic/smilies/" . $url . "></a></td>";
    $count++;

    if ($count % 3 == 0)
        echo'</tr>';
}

?>
</tr></table><br><div align="center"><a class=altlink href="javascript: window.close()"><?php echo CLOSE;
?></a></div>
<?php
?>