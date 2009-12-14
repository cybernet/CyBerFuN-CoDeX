<?php
// userimages.php
// pic management by pdq
require_once "include/bittorrent.php";
dbconn(false);
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

@session_start();

if ($CURUSER['class'] < UC_MODERATOR) // for staff only
    die('No access');

$Name = (isset($_GET['user'])?safe($_GET['user']):safe($_SESSION['picname']));
if (!isset($Name))
    stderr ("Hmm", "No user selected");

$SaLt = '9#Qhj5%^2SA'; // change this!
$skey = '5j$h#%2yq^Q# qty\ty'; // change this!
if ($CURUSER['username'] != $Name) {
    $staffnames = array('pdq', 'ptf', 'tinfoilhat'); // :P paranoid users here
    if (in_array($Name, $staffnames))
        stderr ("Forbidden", "Shoo fly!");
}

$_SESSION['picname'] = $Name;
$PICSALT = $CURUSER['username'] . $SaLt;
$address = $DEFAULTBASEURL . '/';

if (isset($_GET["delete"]) && ($CURUSER['class'] >= UC_MODERATOR)) {
    $getfile = safe($_GET['delete']);
    $delfile = urldecode(decrypt($getfile));
    $delhash = md5($delfile . $_SESSION['picname'] . $skey);

    if ($delhash != $_GET['delhash'])
        stderr("umm", "what are you doing?");

    $myfile = ROOT_PATH . '/' . $delfile;
    if (is_file($myfile))
        unlink($myfile);
    else
        stderr("Hey", "Image not found!");

    if (isset($_GET["type"]) && $_GET["type"] == 2)
        header("Refresh: 2; url=$BASEURL/userimages.php?images=2&user=$_SESSION[picname]");
    else
        header("Refresh: 2; url=$BASEURL/userimages.php?images=1&user=$_SESSION[picname]");
    die('Deleting Image (' . $delfile . '), Redirecting...');
}

if (isset($_GET["avatar"]) && $_GET["avatar"] != '' && (($_GET["avatar"]) != $CURUSER["avatar"])) {
    $type = ((isset($_GET["type"]) && $_GET["type"] == '1')?1:2);
    if (!preg_match("/^http:\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $_GET["avatar"]))
        stderr("Err", "Avatar MUST be in jpg, gif or png format. Make sure you include http:// in the URL.");
    $avatar = sqlesc($_GET['avatar']);
    sql_query("UPDATE users SET avatar = $avatar WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
    header("Refresh: 0; url=$BASEURL/userimages.php?images=$type&updated=avatar&user=$_SESSION[picname]");
}
// start display
stdhead($Name . '\'s images');

if (isset($_GET["updated"]) && $_GET["updated"] == 'avatar') {

    ?>
<h3>Updated avatar to <p><img onload='Scale(this);' src='<?php echo safe($CURUSER['avatar']);
    ?>' border='0' alt='' /></p></h3>
<?php
}

?>
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
<p align="center"><a href="userimages.php?images=2&user=<?php echo $Name;
    ?>">View <?php echo $Name;
    ?> avatars</a></p>
	<p align="center"><a href="userimages.php?user=<?php echo $Name;
    ?>">Hide <?php echo $Name;
    ?> images</a></p>
<?php } elseif (isset($_GET['images']) && $_GET['images'] == 2) {

    ?>
<p align="center"><a href="userimages.php?images=1&user=<?php echo $Name;
    ?>">View <?php echo $Name;
    ?> images</a></p>
	<p align="center"><a href="userimages.php?user=<?php echo $Name;
    ?>">Hide <?php echo $Name;
    ?> avatars</a></p>

<?php
} else {

    ?>
<p align="center"><a href="userimages.php?images=1&user=<?php echo $Name;
    ?>">View <?php echo $Name;
    ?> images</a></p>
<p align="center"><a href="userimages.php?images=2&user=<?php echo $Name;
    ?>">View <?php echo $Name;
    ?> avatars</a></p>
<?php
}
if (isset($_GET['images'])) {
    foreach ((array) glob((($_GET['images'] == 2)?'avatars/':'bitbucket/') . $Name . '_*') as $filename) {
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

		<p align="center"><a href="userimages.php?type=<?php echo ((isset($_GET['images']) && $_GET['images'] == 2)?'2':'1');
            ?>&avatar=<?php echo $BASEURL;
            ?>/<?php echo $filename;
            ?>">Make this my Avatar!</a></p>

<p align="center"><a href="userimages.php?type=<?php echo ((isset($_GET['images']) && $_GET['images'] == 2)?'2':'1');
            ?>&delete=<?php echo $encryptedfilename;
            ?>&amp;delhash=<?php echo md5($filename . $_SESSION['picname'] . $skey);
            ?>">^^Delete this image^^</a></p>

<br />

<?php
        } else
            echo 'No Images Found';
    }
}
stdfoot();
exit();

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