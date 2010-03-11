<?php
require ("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Moforums - Nosey Cunt !");
// Presets

$act = isset($_GET["act"]) ?$_GET["act"] : '';
$id = 0 + $_GET['id'];

if (!$act) {
    $act = "forum";
}
// DELETE FORUM ACTION
if ($act == "del") {
    if (get_user_class() < UC_SYSOP)
        stderr("Error", "Permission denied.");

    if (!$id) {
        header("Location: $PHP_SELF?act=forum");
        die();
    }

    mysql_query ("DELETE FROM overforums where id = $id") or sqlerr(__FILE__, __LINE__);

    header("Location: $PHP_SELF?act=forum");
    die();
}
// EDIT FORUM ACTION
if ($_POST['action'] == "editforum") {
    if (get_user_class() < UC_SYSOP)
        stderr("Error", "Permission denied.");

    $name = $_POST['name'];
    $desc = $_POST['desc'];

    if (!$name && !$desc && !$id) {
        header("Location: $PHP_SELF?act=forum");
        die();
    }

    mysql_query("UPDATE overforums SET sort = '" . $_POST['sort'] . "', name = " . sqlesc($_POST['name']) . ", description = " . sqlesc($_POST['desc']) . ", forid = 0, minclassview = '" . $_POST['viewclass'] . "' where id = '" . $_POST['id'] . "'") or sqlerr(__FILE__, __LINE__);
    header("Location: $PHP_SELF?act=forum");
    die();
}
// ADD FORUM ACTION
if ($_POST['action'] == "addforum") {
    if (get_user_class() < UC_SYSOP)
        stderr("Error", "Permission denied.");

    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);

    if (!$name && !$desc) {
        header("Location: $PHP_SELF?act=forum");
        die();
    }

    mysql_query("INSERT INTO overforums (sort, name,  description,  minclassview, forid) VALUES(" . $_POST['sort'] . ", " . sqlesc($_POST['name']) . ", " . sqlesc($_POST['desc']) . ", " . $_POST['viewclass'] . ", 1)") or sqlerr(__FILE__, __LINE__);

    header("Location: $PHP_SELF?act=forum");
    die();
}

stdhead("Overforum Edit");
begin_main_frame();

if ($act == "forum") {
    // SHOW FORUMS WITH FORUM MANAGMENT TOOLS
    begin_frame("Overforums");

    ?>
<script language="JavaScript">
<!--
function confirm_delete(id)
{
   if(confirm('Are you sure you want to delete this overforum?'))
   {
      self.location.href='<?php $PHP_SELF;
    ?>?act=del&id='+id;
   }
}
//-->
</script>
<?php
    echo '<table width="100%"  border="0" align="center" cellpadding="2" cellspacing="0">';
    echo "<tr><td class=colhead align=left>Name</td><td class=colhead>Viewed By</td><td class=colhead>Modify</td></tr>";
    $result = mysql_query ("SELECT  * FROM overforums ORDER BY sort ASC");
    if ($row = mysql_fetch_array($result)) {
        do {
            echo "<tr><td><a href=forums.php?action=forumview&forid=" . $row["id"] . "><b>" . $row["name"] . "</b></a><br>" . $row["description"] . "</td>";
            echo "<td>" . get_user_class_name($row["minclassview"]) . "</td><td align=center nowrap><b><a href=\"" . $PHP_SELF . "?act=editforum&id=" . $row["id"] . "\">Edit</a>&nbsp;|&nbsp;<a href=\"javascript:confirm_delete('" . $row["id"] . "');\"><font color=red>Delete</font></a></b></td></tr>";
        } while ($row = mysql_fetch_array($result));
    } else {
        print "<tr><td>Sorry, no records were found!</td></tr>";
    }
    echo "</table>";

    ?>
<br><br>
<form method=post action="<?=$PHP_SELF;
    ?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead>Make new forum</td>
  </tr>
  <tr>
    <td><b>Overforum name</td>
    <td><input name="name" type="text" size="20" maxlength="60"></td>
  </tr>
  <tr>
    <td><b>Overforum description  </td>
    <td><input name="desc" type="text" size="30" maxlength="200"></td>
  </tr>

    <tr>
    <td><b>Minimun view permission </td>
    <td>
    <select name=viewclass>\n
    <?php
    $maxclass = get_user_class();
    for ($i = 0; $i <= $maxclass; ++$i)
    print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

    ?>
	</select>
    </td>
  </tr>

    <tr>
    <td><b>Overforum rank </td>
    <td>
    <select name=sort>\n
    <?php
    $res = mysql_query ("SELECT sort FROM overforums");
    $nr = mysql_num_rows($res);
    $maxclass = $nr + 1;
    for ($i = 0; $i <= $maxclass; ++$i)
    print("<option value=$i>$i \n");

    ?>
	</select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="addforum"><input type="submit" name="Submit" value="Make overforum"></td>
  </tr>
</table>

<?php

    print("<tr><td align=center colspan=1><form method=\"get\" action=\"forummanage.php#add\"></form><form method=\"get\" action=\"forummanage.php#add\"><input type=\"submit\" value=\"Forum Manager\" style='height: 18px' /></form></td></tr>\n");
    end_frame();
}
?>

<?php if ($act == "editforum") {
    // EDIT PAGE FOR THE FORUMS
    $id = 0 + $_GET["id"];
    begin_frame("Edit Overforum");
    $result = mysql_query ("SELECT * FROM overforums where id = '$id'");
    if ($row = mysql_fetch_array($result)) {
        // Get OverForum Name - To Be Written
        do {

            ?>

<form method=post action="<?=$PHP_SELF;
            ?>">
<table width="100%"  border="0" cellspacing="0" cellpadding="3" align="center">
<tr align="center">
    <td colspan="2" class=colhead>edit overforum: <?=$row["name"];
            ?></td>
  </tr>

    <td><b>Overforum name</td>
    <td><input name="name" type="text" size="20" maxlength="60" value="<?=$row["name"];
            ?>"></td>
  </tr>
  <tr>
    <td><b>Overforum description  </td>
    <td><input name="desc" type="text" size="30" maxlength="200" value="<?=$row["description"];
            ?>"></td>
  </tr>


    <tr>
    <td><b>Minimun view permission </td>
    <td>
    <select name=viewclass>\n
    <?php
            $maxclass = get_user_class();
            for ($i = 0; $i <= $maxclass; ++$i)
            print("<option value=$i" . ($row["minclassview"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");

            ?>
	</select>
    </td>
  </tr>


    <tr>
    <td><b>Overforum rank </td>
    <td>
    <select name=sort>\n
    <?php
            $res = mysql_query ("SELECT sort FROM overforums");
            $nr = mysql_num_rows($res);
            $maxclass = $nr + 1;
            for ($i = 0; $i <= $maxclass; ++$i)
            print("<option value=$i" . ($row["sort"] == $i ? " selected" : "") . ">$i \n");

            ?>
	</select>


    </td>
  </tr>

  <tr align="center">
    <td colspan="2"><input type="hidden" name="action" value="editforum"><input type="hidden" name="id" value="<?=$id;
            ?>"><input type="submit" name="Submit" value="Edit overforum"></td>
  </tr>
</table>

<?php
        } while ($row = mysql_fetch_array($result));
    } else {
        print "Sorry, no records were found!";
    }

    print("<tr><td align=center colspan=1><form method=\"get\" action=\"moforums.php#add\"><input type=\"submit\" value=\"Return\" style='height: 18px' /></form></td></tr>\n");
    end_frame();
}
?>

<?php
end_main_frame();
stdfoot();

?>