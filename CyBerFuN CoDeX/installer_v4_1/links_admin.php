<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
dbconn(false);
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();

if (get_user_class() < UC_MODERATOR)
hacker_dork("Links Admin - Nosey Cunt !");

$sql = sql_query("SELECT * FROM links_categories") or sqlerr(__FILE__, __LINE__);

if(!mysql_num_rows($sql))
stderr("ERROR", "No Categories");

while ( $arr = mysql_fetch_assoc($sql)) {
$cats[] = $arr;
}

if('POST' == $_SERVER['REQUEST_METHOD']) {

if('edit' == $_POST['action'])
Do_Edit($cats);

if('links_update' == $_POST['action'])
Do_links_Update();

if('cat_update' == $_POST['action'])
Do_Cat_Update();

if('cat_add' == $_POST['action'])
Do_Cat_Add();

if('links_add' == $_POST['action'])
Do_links_Add();

//===added delete
if('links_delete' == $_POST['action'])
Do_links_Delete();
}


stdhead("links Admin");
begin_table();
echo'<div class=faqhead>Edit Section</div><div class=faqbody><form name=inputform method=post action=links_admin.php><input type=hidden name=action value=edit />Section<input type=radio name=option value=heading /> Text<input type=radio name=option value=body /><br /><br /><select name=cat><option value=0>--Select One--</option>';

foreach ( $cats as $v) {
echo "<option value=$v[cid]>$v[rcat_name]</option>";
}
echo '</select> <input type=submit name=submit value=Edit class=button></form></div>';

New_Cat_Form();
New_links_form();

//===added delete
function Do_links_Delete($cats = array()) {

if ($_POST['id']){
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if(!is_valid_id($id))
Do_Error("Error", "Bad ID!");
}
$sql = sql_query("DELETE FROM links WHERE id = $id") or sqlerr(__FILE__, __LINE__);
stderr("Info", "Updated successfully Deleted! <a class=altlink href='links_admin.php'>Go Back To links Admin?</a>");
}

function Do_Edit($cats = array()) {

if(empty($_POST['option']))
Do_Error("Error", "No option selected <a href='links_admin.php'>Go Back</a>");

if(!isset($_POST['cat']) || empty($_POST['cat']) || !is_valid_id($_POST['cat']))
Do_Error("Error", "No Section selected");

$cat_id = (int)$_POST['cat'];
$option = ($_POST['option'] == 'heading') ? 'links_categories' : 'links';
$sql = sql_query("SELECT * FROM {$option} WHERE cid = {$cat_id}") or stderr("SQL Error", "OOps!");

if(!mysql_num_rows($sql))
stderr("SQL Error", "Nothing doing here!");

stdhead("Edit ".safechar($option));

if('links_categories' == $option) {

while($row = mysql_fetch_assoc($sql)) {

echo "<div class='faqhead'>heading No.{$row['cid']}</div><div class='faqbody'><form name='inputform' method='post' action='links_admin.php'>".
"<input type='hidden' name='action' value='cat_update' /><input type='hidden' name='cat' value='{$row['cid']}' />".
"<input type='text' value='".safechar($row['rcat_name'], ENT_QUOTES)."' name='rcat_name' style='width:380px;' /> ".
"<input type='submit' name='submit' value='Edit' class='button'></form></div>";
}
}
else{

while($row = mysql_fetch_assoc($sql)) {
begin_frame();
echo "<div class='faqhead'>Link No.{$row['id']}</div><div class='faqbody'>";
print $row['mtime'];
echo "<form name='compose' method='post' action='links_admin.php'>";

echo "<input type='hidden' name='action' value='links_update' />";

echo "<input type='hidden' name='links_id' value='{$row['id']}' />";

echo "<input type='text' value='{$row['heading']}' name='heading' style='width:380px;' /> ";

echo "<select name='cat'><option value=''>--Select One--</option>";

foreach($cats as $v) { print "<option value='".$v['cid']."'>".$v['rcat_name']. '</option>'; }
echo "</select><br />";
//echo "<textarea name='text' rows='5' style='width:380px;'>".htmlentities($row['text'])."</textarea>";
$body = htmlentities($row['body']);
textbbcode("compose","body",$body);
echo "<br /><input type='submit' name='submit' value='Edit This Entry' class='button'>";

echo "</form></div>";
//===added delete
echo "<form name='deleteform' method='post' action='links_admin.php'>";

echo "<input type='hidden' name='action' value='links_delete' />";

echo "<input type='hidden' name='id' value='{$row['id']}' />";

echo "<br><input type='submit' name='submit' value='Delete This Entry' class='button'>";

echo "</form></div>";
end_frame();
}
}
Stdfoot();
exit();
}


function DO_links_Update() {

if(empty($_POST['links_id']) || !is_valid_id($_POST['links_id']) || empty($_POST['heading']) || empty($_POST['body']))
Do_Error("Error", "Don't leave any fields blank");

$cat_id = (!empty($_POST['cat'])) ? ",cid = ".(int)$_POST['cat'] : '';

$sql = "UPDATE links SET heading = ".sqlesc(strip_tags($_POST['heading'])).", body = ".sqlesc(strip_tags($_POST['body']))." $cat_id , mtime = UNIX_TIMESTAMP() WHERE id = {$_POST['links_id']}";
@sql_query($sql);

if(mysql_affected_rows() == -1)
stderr("SQL Error", "Update failed");

stderr("Info", "Updated successfully <a href='links_admin.php'>Go Back To Admin</a>");
}

function Do_Cat_Update() {

$cat_id = (int)$_POST['cat'];

if(!is_valid_id($cat_id))
Do_Error("Error", "No values");

if(empty($_POST['rcat_name']) || (strlen($_POST['rcat_name']) > 100))
Do_Error("Error", "No value or value too big");

$sql = "Update links_categories SET rcat_name = ".sqlesc(strip_tags($_POST['rcat_name']))." WHERE cid=$cat_id";
@sql_query($sql);

if(mysql_affected_rows() == -1)
stderr("Warning", "Couldn't forefill that request");

stderr("Info", "heading successfully updated");
}

function Do_Cat_Add() {

if(empty($_POST['rcat_name']) || strlen($_POST['rcat_name']) > 100)
Do_Error("Error", "Field is blank or length too long!");

$cat_name = sqlesc(strip_tags($_POST['rcat_name']));
$sql = "INSERT INTO links_categories (rcat_name) VALUES ($cat_name)";
@sql_query($sql);

if(mysql_affected_rows() == -1)
stderr("Warning", "Couldn't forefill that request");

stdhead("Add New heading");
New_Cat_Form(1);
stdfoot();
exit();
}

function Do_links_Add() {

if(empty($_POST['heading']) || empty($_POST['body']) || strlen($_POST['heading']) > 100)
Do_Error("Error", "Field is blank or length too long!");

$cat_id = (int)$_POST['cat'];

if(!is_valid_id($cat_id))
Do_Error("Error", "No heading");

$heading = sqlesc(strip_tags($_POST['heading']));
$body = sqlesc(strip_tags($_POST['body']));

$sql = "INSERT INTO links (cid, heading, body, ctime) VALUES ($cat_id, $heading, $body, UNIX_TIMESTAMP()+(3600*24*3))";
@sql_query($sql);

if(mysql_affected_rows() == -1)
stderr("Warning", "Couldn't forefill that request");

stdhead("Add New heading");
New_links_Form(1);
stdfoot();
exit();
}

function New_Cat_Form($info = 0) {

$html = '';

if($info) $html = Do_Info("heading successfully added!");;

$html .= "<div class='faqhead'>Add A New heading</div><div class='faqbody'>";
$html .= "<form name='inputform' method='post' action='links_admin.php'>";
$html .= "<input type='text' value='' name='rcat_name' style='width:380px;' />";
$html .= "<input type='hidden' name='action' value='cat_add' />";
$html .= " <input type='submit' name='submit' value='Add' class='button'>";
$html .= "</form></div>";

return print $html;

}

function New_links_Form($info = 0) {

global $cats;

$html = '';

if($info) $html = Do_Info("Rule successfully added!");

$html .= "<div class='faqhead'>Add A New section</div><div class='faqbody'>";
$html .= "<form name='inputform' method='post' action='links_admin.php'>";
$html .= "<input type='hidden' name='action' value='links_add' />";
$html .= "<input type='text' value='' name='heading' style='width:380px;' /><br /><br />";
$html .= "<select name='cat'><option value=''>--Select--</option>";

foreach($cats as $v) { $html .= "<option value=$v[cid]>$v[rcat_name]</option>"; }

$html .= "</select><br /><br /><textarea name='body' rows='5' class='textbox' style='width:380px;'></textarea><br />";
$html .= "<input type='submit' name='save_cat' value='Add' class='button'>";
$html .= "</form></div>";

return print $html;
}

function Do_Info($text)
{
$info = "<div class='infohead'><img src='pic/warned0.gif' /> Info</div><div class='infobody'>\n";
$info .= $text;
$info .= "</div><br />";
$info .= "<a href='links_admin.php'>Go Back To Admin</a> OR Add another?";
return $info;
}

function Do_Error($heading, $text)
{
stdhead("Error Page");

print "<div class='errorhead'><img src='pic/warned.gif' /> $heading</div><div class='errorbody'>\n";
print("$text\n");
print "</div>";
stdfoot();
die;
}
echo "<br /><p align='center' style='color:orange;font-weight:bold;'>links system based on FAQ System 2006 © CoLdFuSiOn</p>";
end_table();
stdfoot();
?>