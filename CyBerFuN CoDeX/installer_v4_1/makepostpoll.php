<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if (get_user_class() < UC_USER)
    stderr("Error: Permission denied", "Sorry the add poll is for Users and above.");

$action = isset($_GET["action"]) ?$_GET["action"] : '';
$pollid = 0 + $_GET["pollid"];

$topicid = 0 + $_POST["topicid"];
$returnto = $_POST["returnto"];
if ($returnto == "") $returnto = $_GET['returnto'] . '&topicid=' . $_GET['topicid'];

if ($action == "edit") {
    if (!is_valid_id($pollid))
        stderr("Error", "Invalid ID $pollid.");
    $res = sql_query("SELECT * FROM polls WHERE id = $pollid")
    or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 0)
        stderr("Error", "No poll found with ID $pollid.");
    $poll = mysql_fetch_array($res);
}

if (($_SERVER["REQUEST_METHOD"] == "POST") && !$topicid) {
    $topicid = $_POST["updatetopicid"];
    $pollid = $_POST["pollid"];
    $question = $_POST["question"];
    $option0 = $_POST["option0"];
    $option1 = $_POST["option1"];
    $option2 = $_POST["option2"];
    $option3 = $_POST["option3"];
    $option4 = $_POST["option4"];
    $option5 = $_POST["option5"];
    $option6 = $_POST["option6"];
    $option7 = $_POST["option7"];
    $option8 = $_POST["option8"];
    $option9 = $_POST["option9"];
    $option10 = $_POST["option10"];
    $option11 = $_POST["option11"];
    $option12 = $_POST["option12"];
    $option13 = $_POST["option13"];
    $option14 = $_POST["option14"];
    $option15 = $_POST["option15"];
    $option16 = $_POST["option16"];
    $option17 = $_POST["option17"];
    $option18 = $_POST["option18"];
    $option19 = $_POST["option19"];
    $sort = $_POST["sort"];

    if (!$question || !$option0 || !$option1)
        stderr("Error", "Missing form data!");

    if ($pollid)
        sql_query("UPDATE polls SET " . "question = " . sqlesc($question) . ", " . "option0 = " . sqlesc($option0) . ", " . "option1 = " . sqlesc($option1) . ", " . "option2 = " . sqlesc($option2) . ", " . "option3 = " . sqlesc($option3) . ", " . "option4 = " . sqlesc($option4) . ", " . "option5 = " . sqlesc($option5) . ", " . "option6 = " . sqlesc($option6) . ", " . "option7 = " . sqlesc($option7) . ", " . "option8 = " . sqlesc($option8) . ", " . "option9 = " . sqlesc($option9) . ", " . "option10 = " . sqlesc($option10) . ", " . "option11 = " . sqlesc($option11) . ", " . "option12 = " . sqlesc($option12) . ", " . "option13 = " . sqlesc($option13) . ", " . "option14 = " . sqlesc($option14) . ", " . "option15 = " . sqlesc($option15) . ", " . "option16 = " . sqlesc($option16) . ", " . "option17 = " . sqlesc($option17) . ", " . "option18 = " . sqlesc($option18) . ", " . "option19 = " . sqlesc($option19) . ", " . "sort = " . sqlesc($sort) . " " . "WHERE id = $pollid") or sqlerr(__FILE__, __LINE__);
    else {
        sql_query("INSERT INTO polls VALUES(0" . ", '" . get_date_time() . "'" . ", " . sqlesc($question) . ", " . sqlesc($option0) . ", " . sqlesc($option1) . ", " . sqlesc($option2) . ", " . sqlesc($option3) . ", " . sqlesc($option4) . ", " . sqlesc($option5) . ", " . sqlesc($option6) . ", " . sqlesc($option7) . ", " . sqlesc($option8) . ", " . sqlesc($option9) . ", " . sqlesc($option10) . ", " . sqlesc($option11) . ", " . sqlesc($option12) . ", " . sqlesc($option13) . ", " . sqlesc($option14) . ", " . sqlesc($option15) . ", " . sqlesc($option16) . ", " . sqlesc($option17) . ", " . sqlesc($option18) . ", " . sqlesc($option19) . ", " . sqlesc($sort) . ", 0 " . ")") or sqlerr(__FILE__, __LINE__);

        $pollnum = mysql_insert_id();

        sql_query("UPDATE topics SET pollid = $pollnum WHERE id = $topicid");
    }

    header("Location: $returnto");
    die;
}

stdhead();

?>

<table border=1 cellspacing=0 cellpadding=5 width=80%>
<?php
if ($pollid)
    print("<tr><td class=colhead2 colspan=2 align=center><h1>Edit poll</h1></td></tr>");
else
    print("<tr><td class=colhead2 colspan=2 align=center><h1>Add poll</h1></td></tr>");

?>
<form method=post action=makepostpoll.php>
<input type=hidden name=updatetopicid value=<?=$topicid?>>
<input type=hidden name=pollid value=<?=$poll["id"]?>>
<input type=hidden name=returnto value=<?=$returnto?>>
<tr><td class=clearalt6 colspan=2 align=center><br>Fields marked with an <font color=red>*</font> are required.<br></td></tr>
<tr><td class=clearalt6 align=right valign=top><br><font color=red>*</font><b>Question </b></td><td align=left class=clearalt6><br><textarea name=question cols=80 rows=4><?=$poll['question']?></textarea></td></tr>
<tr><td class=clearalt6 align=right><font color=red>*</font><b>Option 1</b> </td><td align=left class=clearalt6><input name=option0 size=80 maxlength=40 value="<?=$poll['option0']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><font color=red>*</font><b>Option 2</b> </td><td align=left class=clearalt6><input name=option1 size=80 maxlength=40 value="<?=$poll['option1']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 3</b></td><td align=left class=clearalt6><input name=option2 size=80 maxlength=40 value="<?=$poll['option2']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 4</b></td><td align=left class=clearalt6><input name=option3 size=80 maxlength=40 value="<?=$poll['option3']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 5</b></td><td align=left class=clearalt6><input name=option4 size=80 maxlength=40 value="<?=$poll['option4']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 6</b></td><td align=left class=clearalt6><input name=option5 size=80 maxlength=40 value="<?=$poll['option5']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 7</b></td><td align=left class=clearalt6><input name=option6 size=80 maxlength=40 value="<?=$poll['option6']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 8</b></td><td align=left class=clearalt6><input name=option7 size=80 maxlength=40 value="<?=$poll['option7']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 9</b></td><td align=left class=clearalt6><input name=option8 size=80 maxlength=40 value="<?=$poll['option8']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 10</b></td><td align=left class=clearalt6><input name=option9 size=80 maxlength=40 value="<?=$poll['option9']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 11</b></td><td align=left class=clearalt6><input name=option10 size=80 maxlength=40 value="<?=$poll['option10']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 12</b></td><td align=left class=clearalt6><input name=option11 size=80 maxlength=40 value="<?=$poll['option11']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 13</b></td><td align=left class=clearalt6><input name=option12 size=80 maxlength=40 value="<?=$poll['option12']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 14</b></td><td align=left class=clearalt6><input name=option13 size=80 maxlength=40 value="<?=$poll['option13']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 15</b></td><td align=left class=clearalt6><input name=option14 size=80 maxlength=40 value="<?=$poll['option14']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 16</b></td><td align=left class=clearalt6><input name=option15 size=80 maxlength=40 value="<?=$poll['option15']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 17</b></td><td align=left class=clearalt6><input name=option16 size=80 maxlength=40 value="<?=$poll['option16']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 18</b></td><td align=left class=clearalt6><input name=option17 size=80 maxlength=40 value="<?=$poll['option17']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 19</b></td><td align=left class=clearalt6><input name=option18 size=80 maxlength=40 value="<?=$poll['option18']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Option 20</b></td><td align=left class=clearalt6><input name=option19 size=80 maxlength=40 value="<?=$poll['option19']?>"><br></td></tr>
<tr><td class=clearalt6 align=right><b>Sort</b></td><td class=clearalt6>
<input type=radio name=sort value=yes <?=$poll["sort"] != "no" ? " checked" : "" ?>>Yes
<input type=radio name=sort value=no <?=$poll["sort"] == "no" ? " checked" : "" ?>> No
</td></tr>
<tr><td colspan=2 align=center class=clearalt6><input class=button type=submit value=<?=$pollid?"'Edit poll'":"'Create poll'"?> style='height: 20pt'><br><br></td></tr>
</table>


</form>

<?php stdfoot();
?>