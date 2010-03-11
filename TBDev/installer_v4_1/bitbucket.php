<?php
// by system
// pic management by pdq
// no rights reserved - public domain FTW!
require_once('include/bittorrent.php');
require_once('include/bbcode_functions.php');
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
$SaLt = 'mE0wI924dsfsfs!@B'; // change this!
$skey = 'eTe5$Ybnsccgbsfdsfsw4h6W'; // change this!
$maxsize = 1024 * 1024;
// valid file formats
$formats = array('.gif',
    '.jpg',
    '.png',
    );
// path to bucket/avatar directories
$bucketdir = (isset($_POST["avy"])?'avatars/':'bitbucket/');
$address = $DEFAULTBASEURL . '/';
$PICSALT = $SaLt . $CURUSER['username'];

if (!isset($_FILES['file'])) {
    if (isset($_GET["delete"])) {
        $getfile = safe($_GET['delete']);
        $delfile = urldecode(decrypt($getfile));
        $delhash = md5($delfile . $CURUSER['username'] . $SaLt);

        if ($delhash != $_GET['delhash'])
            stderr("umm", "what are you doing?");

        $myfile = ROOT_PATH . '/' . $delfile;
        if (is_file($myfile))
            unlink($myfile);
        else
            stderr("Hey", "Image not found!");

        if (isset($_GET["type"]) && $_GET["type"] == 2)
            header("Refresh: 2; url=$BASEURL/bitbucket.php?images=2");
        else
            header("Refresh: 2; url=$BASEURL/bitbucket.php?images=1");
        die('Deleting Image (' . $delfile . '), Redirecting...');
    }

    if (isset($_GET["avatar"]) && $_GET["avatar"] != '' && (($_GET["avatar"]) != $CURUSER["avatar"])) {
        $type = ((isset($_GET["type"]) && $_GET["type"] == 1)?1:2);
        if (!preg_match("/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $_GET["avatar"]))
            stderr("Err", "Avatar MUST be in jpg, gif or png format. Make sure you include http:// in the URL.");
        $avatar = sqlesc($_GET['avatar']);
        sql_query("UPDATE users SET avatar = $avatar WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
        header("Refresh: 0; url=$BASEURL/bitbucket.php?images=$type&updated=avatar");
    }
    // if (isset($_GET["signature"]) && $_GET["signature"] != '' && (($_GET["signature"]) != $CURUSER["signature"]))
    // {
    // if (!preg_match("/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $_GET["signature"]))
    // stderr("Err", "MUST be in jpg, gif or png format. Make sure you include http:// in the URL.");
    // $newsignature = sqlesc("[IMG]".$_GET['signature'] ."[/IMG]\n". $CURUSER["signature"]);
    // sql_query("UPDATE users SET signature = $newsignature WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
    // header("Refresh: 0; url=$BASEURL/bitbucket.php");
    // }
    stdhead('Upload an image');

    if (isset($_GET["updated"]) && $_GET["updated"] == 'avatar') {

        ?>
<h3>Updated avatar to <p><img onload='Scale(this);' src='<?php echo safe($CURUSER['avatar']);
        ?>' border='0' alt='' /></p></h3>
<?php
    }

    ?>
<form action="<?php echo $_SERVER['PHP_SELF'];
    ?>" method="post" enctype="multipart/form-data">
<table width="300" align="center">
  <tr>
    <td class="clearalt6" align="center"><p><b>Allowed file types: <?php echo join(', ', $formats);
    ?></b></p>
      <p><b>Maximum file size: <?php echo number_format($maxsize);
    ?></b></p>
    </td>
  </tr>
  <tr>
    <td align="center"><input type="file" name="file" /></td>
  </tr>
      <tr><td align="center"> <input type='checkbox' name='avy' value='1'> Tick this if avatar</td> </tr>
  <tr>
    <td align="center"><input class="btn" type="submit" value="Upload" /></td>
  </tr>
</table>
</form>
<script type="text/javascript">
function SelectAll(id)
{
    document.getElementById(id).focus();
    document.getElementById(id).select();
}
</script>
<?php
    if (isset($_GET['images']) && $_GET['images'] == 1) {

        ?>
<p align="center"><a href="bitbucket.php?images=2">View my avatars</a></p>
	<p align="center"><a href="bitbucket.php">Hide my images</a></p>
<?php } elseif (isset($_GET['images']) && $_GET['images'] == 2) {

        ?>
<p align="center"><a href="bitbucket.php?images=1">View my images</a></p>
	<p align="center"><a href="bitbucket.php">Hide my avatars</a></p>
<?php
    } else {

        ?>
<p align="center"><a href="bitbucket.php?images=1">View my images</a></p>
<p align="center"><a href="bitbucket.php?images=2">View my avatars</a></p>
<?php
    }
    if (isset($_GET['images'])) {
        foreach ((array) glob((($_GET['images'] == 2)?'avatars/':'bitbucket/') . $CURUSER['username'] . '_*') as $filename) {
            if (!empty($filename)) {
                $encryptedfilename = urlencode(encrypt($filename));
                $eid = md5($filename);
                echo '<a href="' . $BASEURL . '/' . $filename . '"><img src="' . $BASEURL . '/' . $filename . '" width="200" alt="" /><br />' . $BASEURL . '/' . $filename . '</a><br />';

                ?>
	<p>Direct link to image<br />
<input style="font-size: 9pt;text-align: center;" id="d<?php echo $eid;
                ?>d" onclick="SelectAll('d<?php echo $eid;
                ?>d');" type="text" size="70" value="<?php echo $BASEURL;
                ?>/<?php echo $filename;
                ?>" readonly='readonly' /></p>

<p align="center">Tag for forums or comments<br />
<input style="font-size: 9pt;text-align: center;" id="t<?php echo $eid;
                ?>t" onclick="SelectAll('t<?php echo $eid;
                ?>t');" type="text" size="70" value="[IMG]<?php echo $BASEURL;
                ?>/<?php echo $filename;
                ?>[/IMG]" readonly='readonly' /></p>

		<p align="center"><a href="bitbucket.php?type=<?php echo ((isset($_GET['images']) && $_GET['images'] == 2)?'2':'1');
                ?>&avatar=<?php echo $BASEURL;
                ?>/<?php echo $filename;
                ?>">Make this my Avatar!</a></p>

<!-- 		<p align="center"><a href="bitbucket.php?signature=<?php echo $BASEURL;
                ?>/<?php echo $filename;
                ?>">Add this my Signature!</a></p>  -->

<p align="center"><a href="bitbucket.php?type=<?php echo ((isset($_GET['images']) && $_GET['images'] == 2)?'2':'1');
                ?>&delete=<?php echo $encryptedfilename;
                ?>&amp;delhash=<?php echo md5($filename . $CURUSER['username'] . $SaLt);
                ?>">^^Delete this image^^</a></p>

<br />

<?php
            } else
                echo 'No Images Found';
        }
    }
    stdfoot();
    exit();
}

if ($_FILES['file']['size'] == 0) stderr('Error:', 'Upload failed.');
if ($_FILES['file']['size'] > $maxsize) stderr('Error:', 'File size is too large.');
$file = preg_replace('`[^a-z0-9\-\_\.]`i', '', $_FILES['file']['name']);
$allow = ',' . join(',', $formats);
// if(false===stristr($allow,','.substr($file,-4))) stderr('Error:','Invalid file extension.');
if (! function_exists('exif_imagetype')) {
    function exif_imagetype ($filename)
    {
        if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
            return $type;
        }
        return false;
    }
}
$it1 = exif_imagetype($_FILES['file']['tmp_name']);
if ($it1 != IMAGETYPE_GIF && $it1 != IMAGETYPE_JPEG && $it1 != IMAGETYPE_PNG) {
    echo "<h1>Upload failed!<br /> Sorry, but only real images are allowed you Freak (*.jpg, *.gif, *.png).";
    exit;
}
$path = $bucketdir . $CURUSER['username'] . '_' . $file;
$loop = 0;
while (true) {
    if ($loop > 10) stderr('Error:', 'Upload failed.');
    if (!file_exists($path)) break;
    $path = $bucketdir . $CURUSER['username'] . '_' . bucketrand() . $file;
    $loop++;
}
if (!move_uploaded_file($_FILES['file']['tmp_name'], $path))
    stderr('Error:', 'Upload failed.');
if(isset($_POST["from"]) && $_POST["from"] == "upload"){
echo ("<p><b><font color=red>Success! Paste the following url to Poster.</b></p>");
echo ("<p><b><strong>$address/$path</strong></font></b></p>");
exit;
}
stdhead('Image uploaded');

?>
<table width="300" align="center">
  <tr class="clear">
    <td align="center"><p><a href="<?php echo $_SERVER['PHP_SELF'];
?>"><strong>Upload another file</strong></a></p>
      <p>The file was uploaded successfully!</p>
      <!-- <p><a href="<?php echo $address . $path;
?>" target="_blank"><img src="<?php echo $address . $path;
?>" border="0" /></a></p> -->
    <p><img onload='Scale(this);' src="<?php echo $address . $path;
?>" border="0" /></p>
    	<script type="text/javascript">
function SelectAll(id)
{
    document.getElementById(id).focus();
    document.getElementById(id).select();
}
</script>
<p>Direct link to image<br />
<input style="font-size: 9pt;text-align: center;" id="direct" onclick="SelectAll('direct');" type="text" size="70" value="<?php echo $address . $path;
?>" readonly='readonly' /></p>

<p align="center">Tag for forums or comments
<input style="font-size: 9pt;text-align: center;" id="tag" onclick="SelectAll('tag');" type="text" size="70" value="[IMG]<?php echo $address . $path;
?>[/IMG]" readonly='readonly' /></p>

		<p align="center"><a href="bitbucket.php?type=2&avatar=<?php echo $address . $path;
?>">Make this my Avatar!</a></p>


<!-- 			<p align="center"><a href="bitbucket.php?signature=<?php echo $BASEURL;
?>/<?php echo $filename;
?>">Add this my Signature!</a></p>  -->

	<p align="center"><a href="bitbucket.php?images=1">View my images</a></p>
			<p align="center"><a href="bitbucket.php?images=2">View my avatars</a></p>
<!-- 	<p align="center"><a href="bitbucket.php?zip=<?php echo $path;
?>">Download image as zip</a></p> -->
    </td>
  </tr>
</table>

<?php
stdfoot();

function bucketrand()
{
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $out = '';
    for($i = 0;$i < 6;$i++) $out .= $chars[mt_rand(0, 61)];
    return $out;
}

function encrypt($text)
{
    global $PICSALT;
    return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $PICSALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}

function decrypt($text)
{
    global $PICSALT;
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $PICSALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}

?>