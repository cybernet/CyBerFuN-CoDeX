<?php
require "include/bittorrent.php";
require_once("include/bbcode_functions.php");
require_once("include/user_functions.php");
dbconn();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_ADMINISTRATOR)
    stderr("Error", "Access denied.");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    mkglobal("readclass:writeclass:createclass:subforum:descr:place");
    if (empty($readclass) || empty($writeclass) || empty($createclass) || empty($subforum) || empty($descr) || empty($place))
        stderr("Err", "You missed something !");
    else {
        mysql_query("INSERT INTO forums(`name`,`description` ,`minclassread` ,`minclasswrite` ,`minclasscreate`,`place`,`forid`) VALUES(" . join(",", array_map("sqlesc", array($subforum, $descr, $readclass, $writeclass, $createclass, $place, $place))) . ")")or sqlerr(__FILE__, __LINE__);
        if (mysql_insert_id()) {
            header("Refresh: 2; url=" . $_SERVER["PHP_SELF"]);
            stderr("Succes", "Forum added");
        } else
            stderr("Err", "Something was wrong");
    }
} else {
    stdhead();
    begin_frame();
    // first build the list with all the subforums
    $r_list = mysql_query("SELECT f.id as parrentid , f.name as parrentname , f2.id as subid , f2.name as subname, f2.minclassread, f2.minclasswrite, f2.minclasscreate, f2.description FROM forums as f LEFT JOIN forums as f2 ON f2.place=f.id WHERE f2.place !=-1 ORDER BY f.id ASC") or sqlerr(__FILE__, __LINE__);

    ?>
	<table width="60%" cellpadding="4" cellspacing="0" border="1" align="center" style=" border-collapse:collapse">

	<tr>
    	<td width="100%" align="left" rowspan="2" class="colhead">Subforum</td>
        <td nowrap="nowrap" align="center" rowspan="2" class="colhead">Parrent forum</td>
        <td colspan="3" align="center" class="colhead">Permissions</td>
        <td align="center" rowspan="2" class="colhead">Modify</td>

    </tr>
    <tr>
    	<td nowrap="nowrap" class="colhead">read</td>
        <td nowrap="nowrap" class="colhead">write</td>
        <td nowrap="nowrap" class="colhead">create</td>

    </tr>

	<?php
    while ($a = mysql_fetch_assoc($r_list)) {

        ?>
		<tr>
    	<td width="100%" align="left" ><a href="forums.php?action=viewforum&amp;forumid=<?=($a["subid"])?>" ><?=($a["subname"])?></a><br/><?=($a["description"])?></td>
        <td nowrap="nowrap" align="center"><a href="forums.php?action=viewforum&amp;forumid=<?=($a["parrentid"])?>" ><?=($a["parrentname"])?></a></td>

       	<td nowrap="nowrap"><?=(get_user_class_name($a["minclassread"]))?></td>
        <td nowrap="nowrap"><?=(get_user_class_name($a["minclasswrite"]))?></td>
        <td nowrap="nowrap"><?=(get_user_class_name($a["minclasscreate"]))?></td>
		<td align="center" nowrap="nowrap" ><a href="forums.php?action=deleteforum&amp;forumid=<?=($a["subid"])?>"><img src="pic/del.png" title="Delete Forum" style="border:none;padding:2px;" /></a><a href="forums.php?action=editforum&amp;forumid=<?=($a["subid"])?>"><img src="pic/edit.png" title="Edit Forum" style="border:none;padding:2px;" /></a></td>
    </tr>

	<?php
    }
    print("</table>");
    end_frame();
    begin_frame("Add new subforum");

    ?>
	<form action="<?=($_SERVER["PHP_SELF"])?>" method="post">
	<table width="60%" cellpadding="4" cellspacing="0" border="1" align="center" style=" border-collapse:collapse">
	<tr>
		<td align="right" class="colhead">subforum in</td>
		<td nowrap="nowrap" colspan="3" align="left" >
			<?php
    $select = "<select name=\"place\">
			<option value=\"\">Select</option>\n";
    $r = mysql_query("SELECT id,name FROM forums WHERE place=-1 ORDER BY name ASC") or die();
    while ($ar = mysql_fetch_assoc($r))
    $select .= "<option value=\"" . $ar["id"] . "\">" . $ar["name"] . "</option>\n";

    $select .= "</select>\n";

    print($select);

    ?>
		</td>
	  </tr>
	  <tr>
		<td align="right" class="colhead">Subforum</td>
		<td nowrap="nowrap" colspan="3" align="left" ><input type="text" name="subforum" size="60" /></td>
	  </tr>
	  <tr>
		<td align="right" class="colhead">Description</td>
		<td nowrap="nowrap" colspan="3" align="left" ><textarea name="descr" rows="4" cols="60"></textarea></td>
	  </tr>
	  <tr>
		<td align="right" class="colhead">Permisions</td>
		<td align="center">
			<select name="createclass">
			<option value="">Create</option>
				<?php
    $maxclass = get_user_class();
    for ($i = 0; $i <= $maxclass; ++$i)
    print("<option value=\"$i\">" . get_user_class_name($i) . "\n");

    ?>
        </select>

		</td>
		<td align="center"><select name="writeclass">
			<option value="">Write</option>
				<?php
    $maxclass = get_user_class();
    for ($i = 0; $i <= $maxclass; ++$i)
    print("<option value=\"$i\">" . get_user_class_name($i) . "\n");

    ?>
        </select></td>
		<td align="center"><select name="readclass">
			<option value="">Read</option>
				<?php
    $maxclass = get_user_class();
    for ($i = 0; $i <= $maxclass; ++$i)
    print("<option value=\"$i\">" . get_user_class_name($i) . "\n");

    ?>
        </select></td>
	  </tr>
	  <tr><td align="center" colspan="4" class="colhead"><input type="submit" value="add Subforum"/></td></tr>
	</table>
	</form>
	<?php
    end_frame();
    stdfoot();
}

?>