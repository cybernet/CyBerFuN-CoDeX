<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if ($CURUSER["class"] < UC_SYSOP)
    stderr("Error", "Ooops, you've been logged");

$sql = sql_query("SELECT * FROM faq_categories") or die("Ooops");

if (!mysql_num_rows($sql))
    stderr("ERROR", "No Categories");

while ($arr = mysql_fetch_assoc($sql)) {
    $cats[] = $arr;
}

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    if ('edit' == $_POST['action'])
        Do_Edit($cats);

    if ('faq_update' == $_POST['action'])
        Do_Faq_Update();

    if ('cat_update' == $_POST['action'])
        Do_Cat_Update();

    if ('cat_add' == $_POST['action'])
        Do_Cat_Add();

    if ('faq_add' == $_POST['action'])
        Do_Faq_Add();
}

stdhead("FAQAdmin");
begin_table();
echo "<div class='faqhead'>Edit Category</div><div class='faqbody'>";

echo "<form name='inputform' method='post' action='faqadmin.php'>";

echo "<input type='hidden' name='action' value='edit' />";

echo "Category<input type='radio' name='option' value='category' /> Question<input type='radio' name='option' value='question' /><br /><br />";

echo "<select name='cat'><option value=''>--Select One--</option>";

foreach ($cats as $v) {
    print "<option value='" . $v['cid'] . "'>" . $v['fcat_name'] . '</option>';
}
echo "</select> <input type='submit' name='submit' value='Edit' class='btns'>";

echo "</form></div>";
// /////////////////////////////////////////////////////////////////////////
New_Cat_Form();
// //////////////////////////////////////////////////////////////////////////
New_Faq_form();
// //////////////////////////////////////////////////////////////////////////
function Do_Edit($cats = array())
{
    if (empty($_POST['option']))
        Do_Error("Error", "No option selected <a href='faqadmin.php'>Go Back</a>");

    if (!isset($_POST['cat']) || empty($_POST['cat']) || !is_valid_id($_POST['cat']))
        Do_Error("Error", "No Category selected");

    $cat_id = (int)$_POST['cat'];

    $option = ($_POST['option'] == 'category') ? 'faq_categories' : 'faq';

    $sql = sql_query("SELECT * FROM {$option} WHERE cid = {$cat_id}") or stderr("SQL Error", "OOps!");

    if (!mysql_num_rows($sql))
        stderr("SQL Error", "Nothing doing here!");

    stdhead("Edit " . safechar($option));

    if ('faq_categories' == $option) {
        while ($row = mysql_fetch_assoc($sql)) {
            echo "<div class='faqhead'>Category No.{$row['cid']}</div><div class='faqbody'>";

            echo "<form name='inputform' method='post' action='faqadmin.php'>";

            echo "<input type='hidden' name='action' value='cat_update' />";

            echo "<input type='hidden' name='cat' value='{$row['cid']}' />";

            echo "<input type='text' value='" . htmlentities($row['fcat_name'], ENT_QUOTES) . "' name='fcat_name' style='width:380px;' /> ";

            echo "<input type='submit' name='submit' value='Edit' class='btns'>";

            echo "</form></div>";
        }
    } else {
        while ($row = mysql_fetch_assoc($sql)) {
            echo "<div class='faqhead'>Faq No.{$row['id']}</div><div class='faqbody'>";
            print $row['mtime'];
            echo "<form name='inputform' method='post' action='faqadmin.php'>";

            echo "<input type='hidden' name='action' value='faq_update' />";

            echo "<input type='hidden' name='faq_id' value='{$row['id']}' />";

            echo "<input type='text' value='{$row['question']}' name='question' style='width:380px;' /> ";

            echo "<select name='cat'><option value=''>--Select One--</option>";

            foreach($cats as $v) {
                print "<option value='" . $v['cid'] . "'>" . $v['fcat_name'] . '</option>';
            }

            echo "</select><br /><textarea name='Answer' rows='5' style='width:380px;'>" . htmlentities($row['answer']) . "</textarea><br />";
            echo "<input type='submit' name='submit' value='Edit' class='btns'>";

            echo "</form></div>";
        }
    }
    Stdfoot();
    exit();
}

function DO_Faq_Update()
{
    if (empty($_POST['faq_id']) || !is_valid_id($_POST['faq_id']) || empty($_POST['question']) || empty($_POST['Answer']))
        Do_Error("Error", "Don't leave any fields blank");

    $cat_id = (!empty($_POST['cat'])) ? ",cid = " . (int)$_POST['cat'] : '';

    $sql = "UPDATE faq SET question = " . sqlesc(strip_tags($_POST['question'])) . ", answer = " . sqlesc(strip_tags($_POST['Answer'])) . " $cat_id , mtime = UNIX_TIMESTAMP() WHERE id = {$_POST['faq_id']}";
    @sql_query($sql);

    if (mysql_affected_rows() == -1)
        stderr("SQL Error", "Update failed");

    stderr("Info", "Updated successfully");
}

function Do_Cat_Update()
{
    $cat_id = (int)$_POST['cat'];

    if (!is_valid_id($cat_id))
        Do_Error("Error", "No values");

    if (empty($_POST['fcat_name']) || (strlen($_POST['fcat_name']) > 100))
        Do_Error("Error", "No value or value too big");

    $sql = "Update faq_categories SET fcat_name = " . sqlesc(strip_tags($_POST['fcat_name'])) . " WHERE cid=$cat_id";

    @sql_query($sql);

    if (mysql_affected_rows() == -1)
        stderr("Warning", "Couldn't forefill that request");

    stderr("Info", "Category successfully updated");
}

function Do_Cat_Add()
{
    if (empty($_POST['fcat_name']) || strlen($_POST['fcat_name']) > 100)
        Do_Error("Error", "Field is blank or length too long!");

    $cat_name = sqlesc(strip_tags($_POST['fcat_name']));

    $sql = "INSERT INTO faq_categories (fcat_name) VALUES ($cat_name)";

    @sql_query($sql);

    if (mysql_affected_rows() == -1)
        stderr("Warning", "Couldn't forefill that request");

    stdhead("Add New Category");

    New_Cat_Form(1);

    stdfoot();

    exit();
}

function Do_Faq_Add()
{
    if (empty($_POST['question']) || empty($_POST['Answer']) || strlen($_POST['question']) > 100)
        Do_Error("Error", "Field is blank or length too long!");

    $cat_id = (int)$_POST['cat'];

    if (!is_valid_id($cat_id))
        Do_Error("Error", "No category");

    $question = sqlesc(strip_tags($_POST['question']));
    $answer = sqlesc(strip_tags($_POST['Answer']));

    $sql = "INSERT INTO faq (cid, question, answer, ctime) VALUES ($cat_id, $question, $answer, UNIX_TIMESTAMP()+(3600*24*3))";

    @sql_query($sql);

    if (mysql_affected_rows() == -1)
        stderr("Warning", "Couldn't forefill that request");

    stdhead("Add New Category");

    New_Faq_Form(1);

    stdfoot();

    exit();
}

function New_Cat_Form($info = 0)
{
    $html = '';

    if ($info) $html = Do_Info("Category successfully added!");;

    $html .= "<div class='faqhead'>Add A New Category</div><div class='faqbody'>";

    $html .= "<form name='inputform' method='post' action='faqadmin.php'>";

    $html .= "<input type='text' value='' name='fcat_name' style='width:380px;' />";

    $html .= "<input type='hidden' name='action' value='cat_add' />";

    $html .= " <input type='submit' name='submit' value='Add' class='btns'>";

    $html .= "</form></div>";

    return print $html;
}

function New_Faq_Form($info = 0)
{
    global $cats;

    $html = '';

    if ($info) $html = Do_Info("FAQ successfully added!");

    $html .= "<div class='faqhead'>Add A New Question & Answer</div><div class='faqbody'>";

    $html .= "<form name='inputform' method='post' action='faqadmin.php'>";

    $html .= "<input type='hidden' name='action' value='faq_add' />";

    $html .= "<input type='text' value='' name='question' style='width:380px;' /><br /><br />";

    $html .= "<select name='cat'><option value=''>--Select--</option>";

    foreach($cats as $v) {
        $html .= "<option value='" . $v['cid'] . "'>" . $v['fcat_name'] . '</option>';
    }

    $html .= "</select><br /><br /><textarea name='Answer' rows='5' class='textbox' style='width:380px;'></textarea><br />";

    $html .= "<input type='submit' name='save_cat' value='Add' class='btns'>";

    $html .= "</form></div>";

    return print $html;
}

function Do_Info($text)
{
    $info = "<div class='infohead'><img src='pic/warned0.gif' /> Info</div><div class='infobody'>\n";
    $info .= $text;
    $info .= "</div><br />";
    $info .= "<a href='faqadmin.php'>Go Back To Admin</a> OR Add another?";
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

echo "<br /><p align='center' style='color:orange;font-weight:bold;'>FAQ System 2006 © CoLdFuSiOn</p>";
end_table();
stdfoot();

?>