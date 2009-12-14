<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
parked();

$do = (isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"])? $_POST["do"] : ""));
// print($do);
$id = (isset($_GET["id"]) ? 0 + $_GET["id"] : (isset($_POST["id"])? 0 + $_POST["id"] : "0"));
$sure = (isset($_GET["sure"]) && $_GET["sure"] == "yes" ? "yes" : "no");

if ($do == "delete" && $id > 0) {
    $rs = mysql_query("SELECT id,title,user from snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
    $ar = mysql_fetch_assoc($rs);
    if (mysql_num_rows($rs) == 0)
        stderr("Err", "No news with that id.");
    elseif ($ar["user"] != $CURUSER["id"])
        stderr("Err", "You don't have the right to delete this news");
    else {
        if ($sure == "no")
            stderr("Sanity check...", "You are about to delete staff news " . $ar["title"] . ", if you're sure <a href=\"" . $_SERVER["PHP_SELF"] . "?do=delete&amp;id=$id&amp;sure=yes\">click here</a>");
        elseif ($sure == "yes") {
            mysql_query("DELETE FROM snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
            write_log($CURUSER["username"] . " deleted staff news " . $ar["title"]);
            header("Refresh: 2; url=" . $_SERVER["PHP_SELF"]);
            stderr("Succes", "News deleted!");
        }
    }
} elseif ($do == "add" || $do == "edit") {
    if ($do == "edit") {
        $rs = mysql_query("SELECT * FROM snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
        $ar = mysql_fetch_assoc($rs);
        if (mysql_num_rows($rs) == 0)
            stderr("Err", "No news with that id.");
        elseif ($ar["user"] != $CURUSER["id"])
            stderr("Err", "You don't have the right to edite this news");
        else {
            stdhead("Edit news " . $ar["title"]);
            begin_frame("Edit news " . $ar["title"]);
        }
    } elseif ($do == "add") {
        stdhead ("Add new staff news");
        begin_frame("Add new staff news");
    }

    ?>
			<script type="text/javascript">
			function checkit(id)
			{	var button = document.getElementById(id);
				if (button.checked == true)
					button.checked = false;
				if (button.checked == false)
					button.checked = true;
			}
			</script>
		<form method="post" action="<?=$_SERVER["PHP_SELF"]?>">
		<table width="500" align="center" border="1" cellspacing="0" cellpadding="7" >

			<tr><td nowrap="nowrap">Title</td><td width="100%" align="left"><input type=text name='title' size="80" value="<?=($do == "edit" ? $ar["title"] : "")?>" /></td></tr>
			<tr><td nowrap="nowrap">Body</td><td width="100%" align="left"><textarea rows="5" cols="80" name="body"><?=($do == "edit" ? $ar["body"] : "")?></textarea></td></tr>
			<tr><td nowrap="nowrap">Type</td><td width="100%" align="left">
			  <input type="radio" id="notice" name="type" value="notice" <?=((($do == "edit" && $ar["type"] == "notice") || $do == "add")? "checked=\"checked\"" : "")?> />
			  <span style="color:#339900; cursor:default" onclick="checkit('notice');">Notice</span>
			  <input type="radio" id="warning" name="type" value="warning" <?=(($do == "edit" && $ar["type"] == "warning")? "checked=\"checked\"" : "")?> />
			  <span style="color:#FF3300; cursor:default" onclick="checkit('warning');">Warning</span>
			  <input type="radio" id="important" name="type" value="important" <?=(($do == "edit" && $ar["type"] == "important") ? "checked=\"checked\"" : "")?> />
			  <span style="color:#990000; cursor:default" onclick="checkit('important');">Important</span></td></tr>
			<tr><td  colspan="2" align="center"><input type="hidden" name="do" value="<?=($do == "edit" ? "nedit" : "nadd")?>" /><input type=submit  value="<?=($do == "edit" ? "Edit": "Add")?>" />
			<?php
    if ($do == "edit")
        print("<input type=\"hidden\" name=\"id\" value=" . $ar["id"] . "/>");

    ?>
			</td></tr>
			</table></form>
		<?php
    end_frame();
    stdfoot();
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && ($do == "nedit" || $do == "nadd")) {
    $title = $_POST["title"];
    if (empty($title))
        stderr("Err", "There is no title");

    $body = $_POST["body"];
    if (empty($body))
        stderr("Err", "There is not body");
    $t = array("notice", "warning", "important");
    $type = ((isset($_POST["type"]) && in_array($_POST["type"], $t)) ? $_POST["type"] : "notice");

    if ($do == "nedit") {
        $rs = mysql_query("select id from snews where id=" . $id) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($rs) == 0)
            stderr("Err", "No news with that id");
        else {
            $res = mysql_query("UPDATE snews set title=" . sqlesc($title) . ", body=" . sqlesc($body) . ", type=" . sqlesc($type) . ", last_edit=" . time() . " where id=" . $id) or sqlerr(__FILE__, __LINE__);
            if (!$res)
                stderr("Err", "Something was wrong!");
            else {
                header("Refresh: 2; url=" . $_SERVER["PHP_SELF"]);
                stderr("Succes", "News edited!");
            }
        }
    } elseif ($do == "nadd") {
        $res = mysql_query("INSERT INTO snews (title, body, type, added, user) VALUES(" . sqlesc($title) . ", " . sqlesc($body) . "," . sqlesc($type) . "," . time() . ", " . $CURUSER["id"] . ") ") or sqlerr(__FILE__, __LINE__);
        if (!$res)
            stderr("Err", "Something was wrong!");
        else {
            header("Refresh: 2; url=" . $_SERVER["PHP_SELF"]);
            stderr("Succes", "News added!");
        }
    }
} else {
    stdhead("Staff news");
    begin_frame("Current News <font class=\"small\"><a href=\"" . $_SERVER["PHP_SELF"] . "?do=add\">[add news]</a>");
    $res = mysql_query("SELECT n.id,n.title,n.body,n.type,n.added,n.last_edit, n.user as uid ,u.username from snews as n LEFT JOIN users as u ON n.user=u.id ORDER BY n.added DESC , n.last_edit DESC") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 0)
        stdmsg("Notice", "There is no news found!");
    else {
        while ($arr = mysql_fetch_assoc($res)) {

            ?>
				<table width="100%" class="news" title="This news is flagged as <?=($arr["type"])?>" cellpadding="2" cellspacing="0" border="1" style="border-collapse:collapse;padding:10px">
				  <tr>
					<td class="news_title" style="color:<?=($arr["type"] == "notice" ? "#339900" : ($arr["type"] == "warning" ? "#FF3300" : ($arr["type"] == "important" ? "#990000" : "")))?>" ><font class="small"><?=(gmdate("d M Y", $arr["added"]))?></font> - <u><?=($arr["title"])?></u></td>
				  </tr>
				  <tr>
					<td class="news_body"><?=(format_comment($arr["body"]))?></td>
				  </tr>
				  <tr>
					<td class="news_author">.::// <a href="userdetails.php?id=<?=($arr["uid"])?>"><?=($arr["username"])?></a>
					<?php
            if ($arr["uid"] == $CURUSER["id"]) {

                ?>
					<a href="<?=($_SERVER["PHP_SELF"])?>?do=edit&amp;id=<?=$arr["id"]?>"><img src="pic/edit.png" title="Edit News" style="border:none;padding:2px;" /></a>
					<a href="<?=($_SERVER["PHP_SELF"])?>?do=delete&amp;id=<?=$arr["id"]?>"><img src="pic/del.png" title="Delete News" style="border:none;padding:2px;" /></a>
					<?php
            }

            ?>&nbsp;<?=($arr["last_edit"] > 0 ? "<font class=\"small\">last edited " . (gmdate("d M Y", $arr["last_edit"])) . "</font>" : "")?>
					</td>
				  </tr>
				</table>
				<br/>
			<?php
        }
    }
    end_frame();

    stdfoot();
}

?>