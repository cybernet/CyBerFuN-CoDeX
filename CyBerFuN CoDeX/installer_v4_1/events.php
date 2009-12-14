<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
require_once("include/function_bonus.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Events - Nosey Cunt !");

$scheduled_events = mysql_fetch_all("SELECT * from `events` ORDER BY `startTime` DESC LIMIT 10;", array());

if (is_array($scheduled_events)){
foreach ($scheduled_events as $scheduled_event)
{
if (is_array($scheduled_event) && array_key_exists('startTime', $scheduled_event) &&
array_key_exists('endTime', $scheduled_event))
{
$startTime = 0;
$endTime = 0;
$overlayText = "";
$displayDates = true;
//$freeleechEnabled = false;
//$freeleech_enabled = false;//make false to disable free leech
//$double_upload_enabled = false;
//$invites_enabled = false;
$startTime = $scheduled_event['startTime'];
//if(!is_int($startTime))
// $startTime = 0;
$endTime = $scheduled_event['endTime'];
//if(!is_int($endTime))
// $endTime = 0;
if (time() < $endTime && time() > $startTime)
{
if (array_key_exists('overlayText', $scheduled_event))
$overlayText = $scheduled_event['overlayText'];
if (!is_string($overlayText))
$overlayText = "";
if (array_key_exists('displayDates', $scheduled_event))
$displayDates = (bool)(int)$scheduled_event['displayDates'];
if (!is_bool($displayDates))
$displayDates = false;
if (array_key_exists('freeleechEnabled', $scheduled_event)) {
$freeleechEnabled = $scheduled_event['freeleechEnabled'];
}
if (!is_bool($freeleechEnabled)){
$freeleechEnabled = false;
}
if (array_key_exists('duploadEnabled', $scheduled_event)){
$duploadEnabled = $scheduled_event['duploadEnabled'];
}
if (!is_bool($duploadEnabled)) {
$duploadEnabled = false;
}
if (array_key_exists('hdownEnabled', $scheduled_event)) {
$hdownEnabled = $scheduled_event['hdownEnabled'];
}
if (!is_bool($hdownEnabled)) {
$hdownEnabled = false;
}

if ($freeleechEnabled){
$freeleech_enabled = true;
}
if ($duploadEnabled){
$double_upload_enabled = true;
}
if ($hdownEnabled){
$hdown_enabled = true;
}

if ($displayDates) {
$overlay_text = "<span style=\"font-size: 90%\">$overlayText</span><br/><span style=\"font-size: 60%\">" .
date("d/m", $startTime) . " - " . date("d/m", $endTime) . "</span>\n";
} else {
$overlay_text = "<span style=\"font-size: 90%\">$overlayText</span>\n";
}
}
}
}
}

if (get_user_class() < UC_MODERATOR)
stderr("Sorry", "Access denied.");
if(array_key_exists('js', $_GET)){
Header('Content-Type: text/javascript');
?>
function checkAllGood(event){
var result = confirm("Are you sure you want to remove '" + event + "' Event ?");
if(result)
return true;
else
return false;
}
<?php
exit();
}
stdhead("Edit Events");
echo "<script type=\"text/javascript\" src=\"?js\"></script>\n";

if(!is_array($scheduled_events)){
echo "Error: Events not loaded.";
}else{
foreach($_POST as $key => $value){
if(gettype($pos = strpos($key, "_"))!=boolean){
$id = (int)substr($key, $pos + 1);
if(gettype(strpos($key, "removeEvent_"))!=boolean){
$sql = "DELETE FROM `events` WHERE `id` = $id LIMIT 1;";
$res = mysql_query($sql);
if(mysql_error()!=0)
echo "<p>Error Deleting Event: " . mysql_error() . "</p>\n";
else{
if(mysql_affected_rows()==0)
echo "<p>Error Deleting Event: " . mysql_error() . "</p>\n";
else{
echo "<p>Deleted.</p>\n";
//$scheduled_events = mysql_fetch_all("SELECT * from `events` WHERE `startTime` <= ". time() .";", null);
//$scheduled_events = mysql_fetch_all("SELECT * from `events`", null);
}
}
}elseif(gettype(strpos($key, "saveEvent_"))!=boolean){
$text = "";
$start = 0;
$end = 0;
$showDates = 0;
$freeleech =0;
$doubleUpload = 0;
$hdown = 0;

if(array_key_exists('editText', $_POST))
$text = $_POST['editText'];
if(array_key_exists('editStartTime', $_POST))
$start = strtotime(trim($_POST['editStartTime']));
if(array_key_exists('editEndTime', $_POST))
$end = strtotime(trim($_POST['editEndTime']));
if(array_key_exists('editFreeleech', $_POST))
$freeleech = 1;
if(array_key_exists('editShowDates', $_POST))
$showDates = 1;
if($id==-1)
$sql = "INSERT INTO `events`(`overlayText`, `startTime`, `endTime`, `displayDates`, `freeleechEnabled`) VALUES ('$text', $start, $end, $showDates, $freeleech);";
else
$sql = "UPDATE `events` SET `overlayText` = '$text',`startTime` = $start, `endTime` = $end, `displayDates` = $showDates, `freeleechEnabled` = $freeleech WHERE `id` = $id;";
//echo "<p>$sql</p>";
$res = mysql_query($sql);
if(mysql_error()!=0)
echo "<p>Error Saving Event: " . mysql_error() . "</p>\n";
else{
if(mysql_affected_rows()==0)
echo "<p>Possible Error Saving (No Changes)</p>\n";
else{
echo "<p>Saved.</p>\n";
//$scheduled_events = mysql_fetch_all("SELECT * from `events` WHERE `startTime` <= ". time() .";", null);
//$scheduled_events = mysql_fetch_all("SELECT * from `events` ;", null);
}
}
}
}
}
?>
<p><strong> Events Schedular </strong> (eZERO) - <strong> <font color=red>BETA</font> </strong> </p>
<form action="" method="post">
<table width="100%" cellpadding="6">
<tr><th>User</th><th>Text</th><th>Start</th><th>End</th><th>Freeleech?</th><th>DUpload?</th><th>halfdownload?</th><th>Show Dates?</th><th>&nbsp;</th></tr>
<?php
foreach($scheduled_events as $scheduled_event){
$id = $scheduled_event['id'];
$username = get_user_name($scheduled_event['userid']);
$text = $scheduled_event['overlayText'];
$start = date("F j, Y, g:i a", (int)$scheduled_event['startTime']);
$end = date("F j, Y, g:i a", (int)$scheduled_event['endTime']);
$freeleech = (bool)(int)$scheduled_event['freeleechEnabled'];
$doubleUpload = (bool)(int)$scheduled_event['duploadEnabled'];
$hdown = (bool)(int)$scheduled_event['hdownEnabled'];

if($freeleech){
$freeleech = "<img src=\"".$pic_base_url."/on.gif\" alt=\"Freeleech Enabled\" title=\"Enabled\" />";
}else{
$freeleech = "<img src=\"".$pic_base_url."/off.gif\" alt=\"Freeleech Disabled\" title=\"Disabled\" />";
}
if($doubleUpload){
$doubleUpload = "<img src=\"".$pic_base_url."/on.gif\" alt=\"Double Upload Enabled\" title=\"Enabled\" />";
}else{
$doubleUpload = "<img src=\"".$pic_base_url."/off.gif\" alt=\"Double Upload Disabled\" title=\"Disabled\" />";
}

if($hdown){
$hdown = "<img src=\"".$pic_base_url."/on.gif\" alt=\"Halfdownload Enabled\" title=\"Enabled\" />";
}else{
$hdown = "<img src=\"".$pic_base_url."/off.gif\" alt=\"Halfdownload Disabled\" title=\"Disabled\" />";
}
$showdates = (bool)(int)$scheduled_event['displayDates'];
if($showdates){
$showdates = "<img src=\"".$pic_base_url."/on.gif\" alt=\"Showing of Dates Enabled\" title=\"Enabled\" />";
}else{
$showdates = "<img src=\"".$pic_base_url."/off.gif\" alt=\"Showing of Dates Disabled\" title=\"Disabled\" />";
}

echo "<tr><td align=\"center\">$username[username]</td><td align=\"center\">$text</td><td align=\"center\">$start</td><td align=\"center\">$end</td><td align=\"center\">$freeleech</td><td align=\"center\">$doubleUpload</td><td align=\"center\">$hdown</td><td align=\"center\">$showdates</td><td align=\"center\"><input type=\"submit\" name=\"editEvent_$id\" value=\"Edit\" /> <input type=\"submit\" onclick=\"return checkAllGood('$text')\" name=\"removeEvent_$id\" value=\"Remove\" /></td></tr>";
}
?>
<tr><td colspan="9" align="right"><input type="submit" name="editEvent_-1" value="Add Event" /></td></tr>
</table>
<?php
foreach($_POST as $key => $value){
if(gettype($pos = strpos($key, "_"))!=boolean){
$id = (int)substr($key, $pos + 1);
if(gettype(strpos($key, "editEvent_"))!=boolean){
if($id==-1){
?>
<table>
<tr><th align="right">Text</th><td><input type="text" name="editText" /></td></tr>
<tr><th align="right">Start Time</th><td><input type="text" name="editStartTime" /></td></tr>
<tr><th align="right">End Time</th><td><input type="text" name="editEndTime" /></td></tr>
<tr><th align="right">Freeleech</th><td><input type="checkbox" name="editFreeleech" /></td></tr>
<tr><th align="right">DoubleUpload</th><td><input type="checkbox" name="editDUpload" /></td></tr>
<tr><th align="right">halfdownload</th><td><input type="checkbox" name="edithalfdownload" /></td></tr>
<tr><th align="right">Show Dates</th><td><input type="checkbox" name="editShowDates" /></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="saveEvent_-1" value="Save Changes" /></td></tr>
</table>
<?php
}else
foreach($scheduled_events as $scheduled_event){
if($id == $scheduled_event['id']){
$text = $scheduled_event['overlayText'];
$start = date("Y-m-d H:i:s O", (int)$scheduled_event['startTime']);
$end = date("Y-m-d H:i:s O", (int)$scheduled_event['endTime']);
$freeleech = (bool)(int)$scheduled_event['freeleechEnabled'];
if($freeleech){
$freeleech = " checked=\"checked\"";
}else{
$freeleech = "";
}

$showdates = (bool)(int)$scheduled_event['displayDates'];
if($showdates){
$showdates = " checked=\"checked\"";
}else{
$showdates = "";
}?>
<table>
<tr><th align="right">Text</th><td><input type="text" name="editText" value="<?php echo $text; ?>" /></td></tr>
<tr><th align="right">Start Time</th><td><input type="text" name="editStartTime" value="<?php echo $start; ?>" /></td></tr>
<tr><th align="right">End Time</th><td><input type="text" name="editEndTime" value="<?php echo $end; ?>" /></td></tr>
<tr><th align="right">Freeleech</th><td><input type="checkbox" name="editFreeleech" <?php echo $freeleech; ?>/></td></tr>
<tr><th align="right">DoubleUpload</th><td><input type="checkbox" name="editDoubleupload" <?php echo $doubleUpload; ?>/></td></tr>
<tr><th align="right">halfdownload</th><td><input type="checkbox" name="editHalfdownload" <?php echo $halfdownload; ?>/></td></tr>
<tr><th align="right">Show Dates</th><td><input type="checkbox" name="editShowDates" <?php echo $showdates; ?>/></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="saveEvent_<?php echo $id; ?>" value="Save Changes" /></td></tr>
</table>

<?php
break;
}
}
}
}
}
?>
</form>
<?php
}
stdfoot();
die;

?>
