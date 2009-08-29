<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
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


function nfostrip($nfo)
{
$match = array("/[^a-zA-Z0-9-+.,&=זרוצײה:;*'\"ֶ״ֵ\/\@\[\]\(\)\s]/", "/((\x0D\x0A\s*){3,}|(\x0A\s*){3,}|(\x0D\s*){3,})/", "/\x0D\x0A|\x0A|\x0D/");
$replace = array("", "<br />\n<br />\n", "<br />\n");
$nfo = preg_replace($match, $replace, trim($nfo));

return $nfo;
}

stdhead("NFO Ripper");
echo "<h1>NFO Ripper</h1>";

// NFO STRIP FROM TEXTAREA:
if (isset($_POST["submit"]))
echo "<div style=\"margin-left: 100px;\" align=\"left\">".nfostrip($_POST["nfo"])."</div>";

// NFO FILE UPLOAD:
elseif (isset($_POST["submitup"]) && isset($_FILES["nfofile"]) && !empty($_FILES["nfofile"]["name"])) {

$nfofile = $_FILES['nfofile']['tmp_name'];
echo "<div style=\"margin-left: 100px;\" align=\"left\">".nfostrip(file_get_contents($nfofile))."</div>";
}
?>
<form enctype="multipart/form-data" action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<input type="file" name="nfofile" size="81" /><input type="submit" name="submitup" value="Upload!" />
</form>

<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<textarea style="font-size: 11px; width: 80%; height: 200px; margin: 15px; border: 1px solid black;" name="nfo"></textarea>
<br />
<input type="submit" name="submit" value="Rip!" style="width: 100px;" />
</form>
<?php
stdfoot();
?>