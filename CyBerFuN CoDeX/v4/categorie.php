<?php
ob_start();
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
// made by putyn tbdev
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Catergory Manager - Nosey Cunt !");
// fuction cache
function cache_categories()
{
    $res = mysql_query("select id,name,image from categories ORDER BY name ASC ");
    $configfile = "<" . "?php\n\n\$categories = array(\n";
    while ($row = mysql_fetch_assoc($res)) {
        $configfile .= "array('id'=> '{$row['id']}', 'name'=> '{$row['name']}', 'image'=> '{$row['image']}'),\n";
    }
    $configfile .= "\n);\n\n?" . ">";

    $filenum = fopen ("cache/categories.php", "w");
    ftruncate($filenum, 0);
    fwrite($filenum, $configfile);
    fclose($filenum);
}

$vactg = array("delete", "edit", "", "cache");
$actiong = (isset($_GET["action"]) ? $_GET["action"] : "");
if (!in_array($actiong , $vactg))
    stderr("Err", "Not an valid action!");

if (($actiong == "edit" || $actiong == "delete") && $_GET["cid"] == 0)
    stderr("Err", "Missing argument category id");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vaction = array("edit", "add" , "delete");
    $action = ((isset($_POST["action"]) && in_array($_POST["action"], $vaction)) ? $_POST["action"] : "");
    if (!$action)
        stderr("Err", "Missing something");
    if ($action == "add") { // add new category
            $name = htmlentities($_POST["cname"]);
        if (empty($name))
            stderr("Err", "No name given for the category!");
        $image = htmlentities($_POST["cimage"]);
        if (empty($image))
            stderr("Err", "No image given for the category!");

        $add = mysql_query("INSERT INTO categories ( name ,image ) VALUES ( " . sqlesc($name) . ", " . sqlesc($image) . ") ") or sqlerr(__FILE__, __LINE__);
        if ($add)
            stderr("Success", "New category added, go <a href=\"" . $BASEURL . "/categorie.php\">back</a> and add more!");
    } //end action add
    if ($action == "edit") { // edit action
        $cid = (isset($_POST["cid"]) ? 0 + $_POST["cid"] : "");
        $cname_edit = htmlentities($_POST["cname_edit"]);
        if (empty($cname_edit))
            stderr("Err1", "No name given for the category!");
        $cimage_edit = htmlentities($_POST["cimage_edit"]);
        if (empty($cimage_edit))
            stderr("Err1", "No image given for the category!");
        $edit = mysql_query("UPDATE categories SET name=" . sqlesc($cname_edit) . ", image=" . sqlesc($cimage_edit) . " WHERE id=" . sqlesc($cid) . " ") or sqlerr(__FILE__, __LINE__);
        if ($edit)
            stderr("Succes", "Category successfully edited! Go <a href=\"" . $BASEUL . "/categorie.php\">back</a>");
    } //end action edit
}

if ($actiong == "edit") {
    $catid = (isset($_GET["cid"]) ? 0 + $_GET["cid"] : "");
    stdhead("Edit Category");
    $res = mysql_query("SELECT id,name, image from categories where id=" . sqlesc($catid) . " LIMIT 1 ") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    $cname = htmlentities($arr["name"]);
    $cimage = htmlentities($arr["image"]);
    begin_frame("Edit category");
    print("<form action=\"categorie.php\" method=\"post\">");
    print("<table class=\"main\" border=\"1\" cellspacing=\"0\" align=\"center\" cellpadding=\"5\">\n");
    print("<tr><td class=\"colhead\">Cat Name</td><td align=\"left\"><input type=\"text\" size=\"50\" name=\"cname_edit\" value=\"" . $cname . "\" onClick=\"select()\" /></td></tr>");
    print("<tr><td class=\"colhead\">Cat image</td><td align=\"left\"><input type=\"text\" size=\"50\" name=\"cimage_edit\" value=\"" . $cimage . "\" onClick=\"select()\" /></td></tr>");
    print("<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\"Edit category\"/><input type=\"hidden\" name=\"action\" value=\"edit\" /><input type=\"hidden\" name=\"cid\" value=\"" . $arr["id"] . "\" />");
    print("</table></form>");
    end_frame();
    stdfoot();
} elseif ($actiong == "delete") {
    $catid = (isset($_GET["cid"]) ? 0 + $_GET["cid"] : "");
    $res = mysql_query("SELECT id, name FROM categories WHERE id=" . sqlesc($catid) . "") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    $count = mysql_num_rows($res);
    if ($count == 1) {
        $delete = mysql_query("DELETE FROM categories where id=" . sqlesc($catid) . "") or sqlerr(__FILE__, __LINE__);
        if ($delete) {
            write_log("log" . $CURUSER["username"] . " deleted category " . $arr["name"] . "");
            stderr("Succes", "Category successfully deleted! Go <a href=\"" . $BASEUL . "/categorie.php\">back</a>");
        }
    } else
        stderr("Err", "No category with that id!");
} elseif ($actiong == "cache") {
    cache_categories();
    header("Refresh: 2; url=categorie.php");
    stderr("Succes", "Categories saved to the cache file ");
} else {
    stdhead("Categories");
    begin_main_frame();
    // add categories form
    begin_frame("Add category");
    print("<form action=\"categorie.php\" method=\"post\">");
    print("<table class=\"main\" border=\"1\" cellspacing=\"0\" align=\"center\" cellpadding=\"5\">\n");
    print("<tr><td class=\"colhead\">Cat Name</td><td align=\"left\"><input type=\"text\" size=\"50\" name=\"cname\" /></td></tr>");
    print("<tr><td class=\"colhead\">Cat image</td><td align=\"left\"><input type=\"text\" size=\"50\" name=\"cimage\" /></td></tr>");
    print("<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" name=\"submit\" value=\"Add category\"/><input type=\"hidden\" name=\"action\" value=\"add\" />");
    print("</table></form>");
    end_frame();
    // print existing catergories
    begin_frame("Categories<br/><font class=\"small\" color=red>Remember to cache your catergories after you finish editing them from <a href=\"categorie.php?action=cache\">here</a>");
    $res = mysql_query("SELECT id, name, image FROM categories ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);
    $count = mysql_num_rows($res);
    if ($count > 0) {
        print("<table class=\"main\" border=\"1\" cellspacing=\"0\" align=\"center\" cellpadding=\"5\"><tr>\n");
        print("<td class=\"colhead\">ID</td>");
        print("<td class=\"colhead\">Cat Name</td>");
        print("<td class=\"colhead\">Cat image</td>");
        print("<td class=\"colhead\" colspan=\"2\">Action</td>");
        print("</tr>");

        while ($arr = mysql_fetch_assoc($res)) {
            $edit = "<a href=\"categorie.php?action=edit&amp;cid=" . $arr["id"] . "\"><img src=\"pic/edit.png\" title=\"Edit Category\" style=\"border:none;padding:3px;\" /></a>";
            $delete = "<a href=\"categorie.php?action=delete&amp;cid=" . $arr["id"] . "\"><img src=\"pic/del.png\" title=\"Drop Category\" style=\"border:none;padding:3px;\" /></a>";
            print("<tr>");
            print("<td align=\"center\"><a href=\"browse.php?cat=" . $arr["id"] . "\">" . $arr["id"] . "</a></td>");
            print("<td align=\"center\"><a href=\"browse.php?cat=" . $arr["id"] . "\">" . $arr["name"] . "</a></td>");
            print("<td align=\"center\"><a href=\"browse.php?cat=" . $arr["id"] . "\"><img src=\"pic/" . $arr["image"] . "\" border=\"0\" title=\"" . $arr["name"] . "\"/></a></td>");
            print("<td align=\"center\">" . $edit . "</td><td align=\"center\">" . $delete . "</td>");
            print("</tr>");
        }
        print("</table>");
    } else
        print("<h3>No categorie was found!</h3>");

    end_frame();
    end_main_frame();
    stdfoot();
}

?>
