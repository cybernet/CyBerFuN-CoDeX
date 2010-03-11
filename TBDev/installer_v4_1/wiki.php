<?php
ob_start();
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
global $CURUSER;
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
// /////////////////added and modified newmsg////////////////////////////////////
function newmsg($heading = '', $text = '', $div = 'success', $htmlstrip = false)
{
    if ($htmlstrip) {
        $heading = safeChar(trim($heading));
        $text = safeChar(trim($text));
    }
    print("<table class=\"main\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"embedded\">\n");
    print("<div class=\"$div\">" . ($heading ? "<b>$heading</b><br />" : "") . "$text</div></td></tr></table>\n");
}
// /////////////////////////////////////////////////////////////////////////////////
// ////////////////////////////added and modified newerr///////////////////////////
function newerr($heading = '', $text = '', $die = true, $foot = true, $div = 'error', $htmlstrip = false)
{
    newmsg($heading, $text, $div, $htmlstrip);
    if ($foot)
        end_main_frame();
    stdfoot();
    if ($die)
        die;
}
// /////////////////////////////////////////////
function datetimetransform($input) // OUTPUTS SERVERTIME REPLACE THIS FUNCTION IF YOU HAVE USER DEFINED TIMEZONES
{
    $todayh = getdate($input);

    if ($todayh["seconds"] < 10) {
        $todayh["seconds"] = "0" . $todayh["seconds"] . "";
    }
    if ($todayh["minutes"] < 10) {
        $todayh["minutes"] = "0" . $todayh["minutes"] . "";
    }
    if ($todayh["hours"] < 10) {
        $todayh["hours"] = "0" . $todayh["hours"] . "";
    }
    if ($todayh["mday"] < 10) {
        $todayh["mday"] = "0" . $todayh["mday"] . "";
    }
    if ($todayh["mon"] < 10) {
        $todayh["mon"] = "0" . $todayh["mon"] . "";
    }
    $sec = $todayh[seconds];
    $min = $todayh[minutes];
    $hours = $todayh[hours];
    $d = $todayh[mday];
    $m = $todayh[mon];
    $y = $todayh[year];

    $input = "$d-$m-$y $hours:$min:$sec";
    return $input;
}
// ///////////////////added systems navmenu fix////////////////////////////////
function navmenu()
{
    $ret = '<div id="wiki-navigation" align="center"><div><a href="wiki.php">Index</a> - <a href="wiki.php?action=add">Add</a></div><div align="right"><form action="wiki.php" method="post">';
    $ret .= "\n" . '<a href="wiki.php?action=sort&letter=a">A</a>';
    for($i = 0;$i < 25;$i++) {
        $ret .= "\n- " . '<a href="wiki.php?action=sort&letter=' . chr($i + 98) . '">' . chr($i + 66) . '</a>';
    }
    $ret .= "\n" . '<input type="text" name="article"> <input type="submit" value="Search" name="wiki"></form></div></div>';
    return $ret;
}
// //////////////////////////////////////////////////////
function articlereplace($input)
{
    $input = str_replace(" ", "+", $input);
    return $input;
}

function wikisearch($input)
{
    return str_replace(array("%", "_"), array("\\%", "\\_"), mysql_real_escape_string($input));
}

function wikireplace($input)
{
    return preg_replace(array('/\[\[(.+?)\]\]/i', '/\=\=\ (.+?)\ \=\=/i'), array('<a href="wiki.php?action=article&name=$1">$1</a>', '<div id="$1" style="border-bottom: 1px solid grey; font-weight: bold; width: 100%; font-size: 14px;">$1</div>'), $input);
}

function wikimenu()
{
    $res2 = sql_query("SELECT name FROM wiki ORDER BY id DESC LIMIT 1");
    $latest = mysql_fetch_assoc($res2);
    $latestarticle = articlereplace($latest["name"]);
    $ret = "<div id=\"wiki-content-right\">
					<div id=\"details\">
						<ul>
							<li><b>Permissions:</b></li>
							&nbsp;&nbsp;Read: User<br />
							&nbsp;&nbsp;Write: User<br />
							&nbsp;&nbsp;Edit: Moderator
						</ul>
						<ul>
							<li><b>Latest Article:</b></li>
							<a href=\"wiki.php?action=article&name=$latestarticle\">$latest[name]</a>
						</ul>
						<ul>
							<li><b>Version:</b> 0.01B</li>
							<li><b>Author:</b> <a href=\"http://paaskehare.dk\" target=\"_blank\">Paaskehare</a></li>
						</ul>
					</div>
				</div>
		";
    return $ret;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["article-add"])) {
        $name = sqlesc($_POST["article-name"]);
        $body = sqlesc($_POST["article-body"]);
        sql_query("INSERT INTO `wiki` ( `name` , `body` , `userid`, `time` )
VALUES ($name, $body, '" . $CURUSER["id"] . "', '" . TIMENOW . "')") or sqlerr(__FILE__, __LINE__);
        echo "<meta http-equiv=\"refresh\" content=\"0; url=wiki.php?action=article&name=" . $_POST["article-name"] . "\">";
    }
    if (isset($_POST["article-edit"])) {
        $id = $_POST["article-id"];
        $name = sqlesc($_POST["article-name"]);
        $body = sqlesc($_POST["article-body"]);
        sql_query("UPDATE wiki SET name = $name, body = $body, lastedit = '" . TIMENOW . "', lastedituser = '" . $CURUSER["id"] . "' WHERE id = $id");
        echo "<meta http-equiv=\"refresh\" content=\"0; url=wiki.php?action=article&name=" . $_POST["article-name"] . "\">";
    }
    if (isset($_POST["wiki"])) {
        $wikisearch = articlereplace($_POST["article"]);
        echo "<meta http-equiv=\"refresh\" content=\"0; url=wiki.php?action=article&name=$wikisearch\">";
    }
}

stdhead("Wiki");
begin_main_frame();

if (isset($_GET["action"])) {
    $action = safeChar($_GET["action"]);
    if (isset($_GET["name"])) {
        $mode = "name";
        $name = safeChar($_GET["name"]);
    }
    if (isset($_GET["id"])) {
        $mode = "id";
        $id = safeChar($_GET["id"]);
    }
    if (isset($_GET["letter"]))
        $letter = safeChar($_GET["letter"]);
    // IF NOTHING IS SET, SHOW INDEX
} else {
    $action = "article";
    $mode = "name";
    $name = "index";
}

if ($action == "article") {
    $res = sql_query("SELECT * FROM wiki WHERE $mode = '" . ($mode == "name" ? "$name" : "$id") . "'");
    if (mysql_num_rows($res) == 1) {
        echo navmenu();

        echo "<div id=\"wiki-container\">
  <div id=\"wiki-row\">";
        while ($wiki = mysql_fetch_array($res)) {
            if ($wiki[lastedit]) {
                $check = sql_query("SELECT username FROM users WHERE id = $wiki[lastedituser]");
                $checkit = mysql_fetch_assoc($check);
                $edit = "<i>Last Updated by: <a href=\"userdetails.php?id=$wiki[userid]\">$checkit[username]</a> - " . datetimetransform($wiki[lastedit]) . "</i>";
            }
            $check = sql_query("SELECT username FROM users WHERE id = $wiki[userid]");
            $author = mysql_fetch_assoc($check);
            echo "
				<div id=\"wiki-content-left\" align=\"right\">
					<div id=\"name\"><b><a href=\"wiki.php?action=article&name=$wiki[name]\">$wiki[name]</a></b></div>
					<div id=\"content\">" . ($wiki[userid] > 0 ? "<font style=\"color: grey; font-size: 9px;\"><i>Article added by <a href=\"userdetails.php?id=$wiki[userid]\"><b>$author[username]</b></i></a></font><br /><br />" : "") . wikireplace(format_comment($wiki["body"])) . "";
            echo "<div align=\"right\">" . ($edit ? "<font style=\"color: grey; font-size: 9px;\">$edit</font>" : "") . (get_user_class() >= UC_MODERATOR || $CURUSER["id"] == $wiki["userid"] ? " - <a href=\"wiki.php?action=edit&id=$wiki[id]\">Edit</a>" : "") . "</div>";
            echo "</div></div>";
        }

        echo wikimenu();

        echo "</div>";
        echo "</div>";
    } else {
        $search = sql_query("SELECT * FROM wiki WHERE name LIKE '%" . wikisearch($name) . "%'");
        if (mysql_num_rows($search) > 0) {
            echo "Search results for: <b>$name</b>";
            while ($wiki = mysql_fetch_array($search)) {
                if ($wiki["userid"] !== 0)
                    $wikiname = mysql_fetch_assoc(sql_query("SELECT username FROM users WHERE id = $wiki[userid]"));
                echo "
				<div class=\"wiki-search\">
					<b><a href=\"wiki.php?action=article&name=" . articlereplace($wiki["name"]) . "\">$wiki[name]</a></b> Added by: <a href=\"userdetails.php?id=$wiki[userid]\">$wikiname[username]</a></div>";
            }
        } else {
            newerr("Error", "No article found.");
        }
    }
}

if ($action == "add") {
    echo navmenu();

    echo "<div id=\"wiki-container\">
  <div id=\"wiki-row\">";
    echo "
				<div id=\"wiki-content-left\" align=\"right\">
					<form method=\"post\" action=\"wiki.php\">
					<div id=\"name\"><input type=\"text\" name=\"article-name\" id=\"name\"></div>
					<div id=\"content-add\"><textarea name=\"article-body\" id=\"body\">$wiki[body]</textarea>
					<div align=\"center\"><input type=\"submit\" name=\"article-add\" value=\"OK\"></div>
				</div></form></div>";

    echo wikimenu();

    echo "</div>";
    echo "</div>";
}

if ($action == "edit") {
    $res = sql_query("SELECT * FROM wiki WHERE id = $id");
    $rescheck = sql_query("SELECT userid FROM wiki WHERE id = $id");

    $wikicheck = mysql_fetch_assoc($rescheck);
    if ((get_user_class() >= UC_MODERATOR) OR ($CURUSER["id"] == $wikicheck["userid"])) {
        echo navmenu();

        echo "<div id=\"wiki-container\">
  <div id=\"wiki-row\">";
        while ($wiki = mysql_fetch_array($res)) {
            echo "
				<div id=\"wiki-content-left\" align=\"right\">
					<form method=\"post\" action=\"wiki.php\">
					<div id=\"name\"><input type=\"hidden\" name=\"article-id\" value=\"$wiki[id]\">
					<input type=\"text\" name=\"article-name\" id=\"name\" value=\"$wiki[name]\"></div>
					<div id=\"content-add\"><table width=100% height=100% id=\"wikiedit\" border=0 cellpadding=0 cellspacing=0><tr><td><textarea name=\"article-body\" id=\"body\">$wiki[body]</textarea>
					<div align=\"center\"><input type=\"submit\" name=\"article-edit\" value=\"Edit\"> <input type=\"button\" value=\"Preview\" onclick=\"editPreview()\" /></div></td></tr></table>";
            echo "</div></form></div>";
        }

        echo wikimenu();

        echo "</div>";
        echo "</div>";
    } else
        newerr("Error", "Access Denied");
}

if ($action == "sort") {
    $sortres = sql_query("SELECT * FROM wiki WHERE name LIKE '$letter%' ORDER BY name");
    if (mysql_num_rows($sortres) > 0) {
        echo navmenu();
        echo "Articles starting with the letter <b>$letter</b>";
        while ($wiki = mysql_fetch_array($sortres)) {
            if ($wiki["userid"] !== 0)
                $wikiname = mysql_fetch_assoc(sql_query("SELECT username FROM users WHERE id = $wiki[userid]"));
            echo "
				<div class=\"wiki-search\">
					<b><a href=\"wiki.php?action=article&name=" . articlereplace($wiki["name"]) . "\">$wiki[name]</a></b> Added by: <a href=\"userdetails.php?id=$wiki[userid]\">$wikiname[username]</a></div>";
        }
    } else {
        echo navmenu();
        newerr("Error", "No articles starting with letter <b>$letter</b> found.");
    }
}
end_main_frame();
stdfoot();
ob_end_flush();

?>