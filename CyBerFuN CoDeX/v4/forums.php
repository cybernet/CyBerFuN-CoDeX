<?php
/**
* Bleach Forums Improved and Optimized for TBDEV.NET by Alex2005
*/
$page_find ='forums';
include("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
getpage();
if ($FORUMS_ONLINE == '0' AND $CURUSER['class'] < UC_MODERATOR)
    stderr('Information', 'The forums are currently offline for maintainance work');

if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if (function_exists('parked'))
    parked();

/**
* Configs Start
*/
/**
* The max class, ie: UC_CODER
*
* Is able to delete, edit the forum etc...
*/
define('MAX_CLASS', UC_CODER);

/**
* The max file size allowed to be uploaded
*
* Default: 1024*1024 = 1MB
*/
$maxfilesize = 12400 * 1024;

/**
* Set's the max file size in php.ini, no need to change
*/
ini_set("upload_max_filesize", $maxfilesize);

/**
* Set's the root path, change only if you know what you are doing
*/
// define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
/**
* The path to the attachment dir, no slahses
*/
$attachment_dir = ROOT_PATH . "forumattaches";

/**
* The width of the forum, in percent, 100% is the full width
*
* Note: the width is also set in the function begin_main_frame()
*/
$forum_width = '100%';

/**
* The readpost expiry date, default 14 days
*
* Note: if you already have it, delete this one
*/
$READPOST_EXPIRY = 14 * 86400;

/**
* The extensions that are allowed to be uploaded by the users
*
* Note: you need to have the pics in the $pic_base_url folder, ie zip.gif, rar.gif
*/
$allowed_file_extensions = array('rar', 'zip');

/**
* The max subject lenght in the topic descriptions, forum name etc...
*/
$maxsubjectlength = 60;

/**
* Get's the users posts per page, no need to change
*/
$postsperpage = (empty($CURUSER['postsperpage']) ? 25 : (int)$CURUSER['postsperpage']);

/**
* Set to true if you want to use the flood mod
*/
$use_flood_mod = true;

/**
* If there are more than $limit(default 10) posts in the last $minutes(default 5) minutes, it will give them a error...
*
* Requires the flood mod set to true
*/
$minutes = 5;
$limit = 10;

/**
* Set to true if you want to use the attachment mod
*
* Requires 2 extra tables(attachments, attachmentdownloads), so efore enabling it, make sure you have them...
*/
$use_attachment_mod = true;

/**
* Set to true if you want to use the forum poll mod
*
* Requires 2 extra tables(postpolls, postpollanswers), so efore enabling it, make sure you have them...
*/
$use_poll_mod = true;

/**
* Set to false to disable the forum stats
*/
$use_forum_stats_mod = true;

/**
* Change the pics to the ones you use
*/
$forum_pics = array('default_avatar' => 'default_avatar.png', 'arrow_up' => 'p_up.gif', 'online_btn' => 'user_online.gif',
    'offline_btn' => 'user_offline.gif', 'pm_btn' => 'pm.gif', 'p_report_btn' => 'report.gif',
    'p_quote_btn' => 'p_quote.gif', 'p_delete_btn' => 'p_delete.gif', 'p_edit_btn' => 'p_edit.gif');

/**
* Just a check, so that the default url, wont have a ending backslash(to double backslash the links), no need to edit or delete
*/
$DEFAULTBASEURL_rev = strrev($DEFAULTBASEURL);
if ($DEFAULTBASEURL_rev[0] == '/') {
    $DEFAULTBASEURL_rev[0] = '';
    $DEFAULTBASEURL = strrev($DEFAULTBASEURL_rev);
}
/**
* Configs End
*/
// added by putyn
function post_icons($s = 0)
{
    $body = "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"8\" >
				<tr><td width=\"20%\" valign=\"top\" align=\"right\"><strong>Post Icons</strong> <br/>
				<font class=\"small\">(Optional)</font></td>\n";
    $body .= "<td width=\"80%\" align=\"left\">\n";

    for($i = 1; $i < 15;$i++) {
        $body .= "<input type=\"radio\" value=\"" . $i . "\" name=\"iconid\" " . ($s == $i ? "checked=\"checked\"" : "") . " />\n<img align=\"middle\" alt=\"\" src=\"pic/post_icons/icon" . $i . ".gif\"/>\n";
        if ($i == 7)
            $body .= "<br/>";
    }

    $body .= "<br/><input type=\"radio\" value=\"0\" name=\"iconid\"  " . ($s == 0 ? "checked=\"checked\"" : "") . " />[Use None]\n";
    $body .= "</td></tr></table>\n";

    return $body;
}
// function for subforum :)
function subforums($arr)
{
    $sub = "<font class=\"small\"><b>Subforums:</b>";
    $i = 0;
    foreach($arr as $k) {
        $sub .= "&nbsp;<img src=\"pic/bullet_" . ($k["new"] == 1 ? "green.png" : "white.png") . "\" width=\"8\" title=\"" . ($k["new"] == 1 ? "New posts" : "Not new post") . "\" border=\"0\" /><a href=\"forums.php?action=viewforum&amp;forumid=" . $k["id"] . "\">" . $k["name"] . "</a>" . ((count($arr)-1) == $i ? "" : ",");
        $i++;
    }
    $sub .= "</font>";
    return $sub;
}
function get_count($arr)
{
    $topics = 0;
    $posts = 0;
    foreach($arr as $k) {
        $topics += $k["topics"];
        $posts += $k["posts"];
    }
    return array($posts, $topics);
}
// end subforum
// forum moderator by putyn
$forummods = forummods();
//exit(print_r($forummods));
function showMods($ars)
	{
		$mods = "<font class=\"small\">Led by:&nbsp;";
		$i=0;
		$count = count($ars);
		foreach($ars as $a)
		{
		$mods .= "<a href=\"userdetails.php?id=".$a[0]."\">".$a[1]."</a>".(($count -1) == $i ? "":",");
		$i++;
		}
		$mods.="</font>";
		return $mods;
	}
function isMod($fid)
{
    GLOBAL $CURUSER;
    return (stristr($CURUSER["forums_mod"], "[" . $fid . "]") == true ? true : false) ;
}
// end forum moderator :)
$action = (isset($_GET["action"]) ? $_GET["action"] : (isset($_POST["action"]) ? $_POST["action"] : ''));

if (!function_exists('highlight')) {
    function highlight($search, $subject, $hlstart = '<b><font color=red>', $hlend = '</font></b>')
    {
        $srchlen = strlen($search); // lenght of searched string
        if ($srchlen == 0)
            return $subject;

        $find = $subject;
        while ($find = stristr($find, $search)) { // find $search text in $subject -case insensitiv
            $srchtxt = substr($find, 0, $srchlen); // get new search text
            $find = substr($find, $srchlen);
            $subject = str_replace($srchtxt, $hlstart . $srchtxt . $hlend, $subject); // highlight founded case insensitive search text
        }

        return $subject;
    }
}

function genreforumlist() {
global $CURUSER;
$res = sql_query("SELECT id, name FROM forums WHERE minclassread<=$CURUSER[class] ORDER BY name");

while ($row = mysql_fetch_assoc($res))
$ret[] = $row;
return $ret;
}

function catch_up($id = 0)
{
    global $CURUSER, $READPOST_EXPIRY;

    $userid = (int)$CURUSER['id'];

    $res = sql_query("SELECT t.id, t.lastpost, r.id AS r_id, r.lastpostread " . "FROM topics AS t " . "LEFT JOIN posts AS p ON p.id = t.lastpost " . "LEFT JOIN readposts AS r ON r.userid=" . sqlesc($userid) . " AND r.topicid=t.id " . "WHERE p.added > " . sqlesc(get_date_time(gmtime() - $READPOST_EXPIRY)) .
        (!empty($id) ? ' AND t.id ' . (is_array($id) ? 'IN (' . implode(', ', $id) . ')' : '= ' . sqlesc($id)) : '')) or sqlerr(__FILE__, __LINE__);

    while ($arr = mysql_fetch_assoc($res)) {
        $postid = (int)$arr['lastpost'];

        if (!is_valid_id($arr['r_id']))
            sql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, " . (int)$arr['id'] . ", $postid)") or sqlerr(__FILE__, __LINE__);
        else if ($arr['lastpostread'] < $postid)
            sql_query("UPDATE readposts SET lastpostread = $postid WHERE id = " . $arr['r_id']) or sqlerr(__FILE__, __LINE__);
    }
    mysql_free_result($res);
}

function forum_stats()
{
    global $pic_base_url, $forum_width, $DEFAULTBASEURL, $CURUSER ,$language, $page_find ;

    $forumusers = '';

    $res = sql_query("SELECT id, username, donor, warned, class, avatar FROM users WHERE forum_access >= " . sqlesc(get_date_time(gmtime() - 180)) . " ORDER BY forum_access DESC") or sqlerr(__FILE__, __LINE__);
    while ($arr = mysql_fetch_assoc($res)) {
        // ///////////////view online users as avatars in forum////////////
        if ($CURUSER["forumview"] == 'yes') {
            if ($arr["avatar"]) {
               $forumusers .= "<a href=\"" . safeChar($arr["avatar"]) .
        "\" rel='lightbox' title=\"" . safeChar($arr["username"]) . "\" class=\"borderimage\" onMouseover=\"borderit(this,'black')\" onMouseout=\"borderit(this,'silver')\"><img src=\"" .
        safeChar($arr["avatar"]) . "\" width=\"78\" height=\"130\" title=\"{$arr["username"]}\"" . safeChar($arr["username"]) .
        "\"></a>";
            } else {
                $forumusers .= "<a href=\"userdetails.php?id={$arr["id"]}\" target=\"_blank\"> <img src=\"/pic/default_avatar.png\" width=\"78\" height=\"130\" alt=\"{$arr["username"]}\" title=\"{$arr["username"]}\"/> </a>";
            }
        } else

        if (!empty($forumusers))
            $forumusers .= ",\n";

        if (!function_exists('get_user_class_color')) {
            switch ($arr["class"]) {
                case UC_CODER:
                    $username = "<font color=red>" . $arr["username"] . "</font>";
                    break;
                case UC_SYSOP:
                    $username = "<font color=darkred>" . $arr["username"] . "</font>";
                    break;

                case UC_ADMINISTRATOR:
                    $username = "<font color=#B000B0>" . $arr["username"] . "</font>";
                    break;

                case UC_MODERATOR:
                    $username = "<font color=#ff5151>" . $arr["username"] . "</font>";
                    break;

                case UC_UPLOADER:
                    $username = "<font color=#6464FF>" . $arr["username"] . "</font>";
                    break;

                case UC_VIP:
                    $username = "<font color=#009F00>" . $arr["username"] . "</font>";
                    break;

                case UC_POWER_USER:
                    $username = "<font color=#f9a200>" . $arr["username"] . "</font>";
                    break;

                case UC_USER:
                    $username = "<font color=#FF007F>" . $arr["username"] . "</font>";
                    break;
            }
        } else
        if ($CURUSER["forumview"] == 'no')
            $username = "<font color=#" . get_user_class_color($arr["class"]) . ">" . $arr["username"] . "</font>";

        $donator = ($arr["donor"] === "yes");
        $warned = ($arr["warned"] === "yes");

        if ($donator || $warned)
            $forumusers .= "<span style=\"white-space:nowrap\">";
        // $username = '';
        $forumusers .= "<a href='$DEFAULTBASEURL/userdetails.php?id={$arr['id']}'><b>$username</b></a>";
        if ($donator)
            $forumusers .= "<img src='{$pic_base_url}star.gif' alt='Donated {$arr['donor']}' />";

        if ($warned)
            $forumusers .= "<img src='{$pic_base_url}warned.gif' alt='Warned {$arr['warned']}' />";

        if ($donator || $warned)
            $forumusers .= "</span>";
    }
    if (empty($forumusers))
        $forumusers = "No users on-line";

    $topic_post_res = sql_query("SELECT SUM(topiccount) AS topics, SUM(postcount) AS posts FROM forums");
    $topic_post_arr = mysql_fetch_assoc($topic_post_res);

    ?>
	<br />
	<table width='<?php echo $forum_width;
    ?>' border=0 cellspacing=0 cellpadding=5>
        <tr>
            <td class="colhead" align="center"><?php echo $language['active'];?></td>
        </tr>

        <tr>
            <td class='text'><?php echo $forumusers;
    ?></td>
        </tr>

        <tr>
            <td class='colhead' align='center'><h2><?php echo $language['omw'];?><b> <?php echo number_format($topic_post_arr['posts']);
    ?></b> <?php echo $language['postsi'];?> <b><?php echo number_format($topic_post_arr['topics']);
    ?></b> <?php echo $language['threads'];?></h2></td>
        </tr>
	</table><?php
}

function show_forums($forid, $subforums = false, $sfa = "", $show_mods = false)
{
    global $CURUSER, $pic_base_url, $READPOST_EXPIRY, $DEFAULTBASEURL , $ss_uri, $forummods ;

    $forums_res = sql_query("SELECT f.id, f.name, f.description, f.postcount, f.topiccount, f.minclassread, p.added, p.topicid, p.userid, p.id AS pid, u.username, t.subject, t.lastpost, r.lastpostread " . "FROM forums AS f " . "LEFT JOIN posts AS p ON p.id = (SELECT MAX(lastpost) FROM topics WHERE forumid = f.id) " . "LEFT JOIN users AS u ON u.id = p.userid " . "LEFT JOIN topics AS t ON t.id = p.topicid " . "LEFT JOIN readposts AS r ON r.userid = " . sqlesc($CURUSER['id']) . " AND r.topicid = p.topicid " . "WHERE " . ($subforums == false ? "f.forid = $forid AND f.place =-1 ORDER BY f.forid ASC" : "f.place=$forid ORDER BY f.id ASC") . "") or sqlerr(__FILE__, __LINE__);

    while ($forums_arr = mysql_fetch_assoc($forums_res)) {
        if ($CURUSER['class'] < $forums_arr["minclassread"])
            continue;

        $forumid = (int)$forums_arr["id"];
        $lastpostid = (int)$forums_arr['lastpost'];

        if ($subforums == false && !empty($sfa[$forumid])) {
            if (($sfa[$forumid]['lastpost']['postid'] > $forums_arr['pid'])) {
                $lastpost = "" . $sfa[$forumid]['lastpost']['added'] . "<br />" . "by <a href='$DEFAULTBASEURL/userdetails.php?id=" . (int)$sfa[$forumid]['lastpost']['userid'] . "'><b>" . safeChar($sfa[$forumid]['lastpost']['user']) . "</b></a><br />" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$sfa[$forumid]['lastpost']['topic'] . "&amp;page=p" . $sfa[$forumid]['lastpost']['postid'] . "#p" . $sfa[$forumid]['lastpost']['postid'] . "'><b>" . safeChar($sfa[$forumid]['lastpost']['tname']) . "</b></a>";
            } elseif (($sfa[$forumid]['lastpost']['postid'] < $forums_arr['pid'])) {
                $lastpost = "" . $forums_arr["added"] . "<br />" . "by <a href='$DEFAULTBASEURL/userdetails.php?id=" . (int)$forums_arr["userid"] . "'><b>" . safeChar($forums_arr['username']) . "</b></a><br />" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$forums_arr["topicid"] . "&amp;page=p$lastpostid#p$lastpostid'><b>" . safeChar($forums_arr['subject']) . "</b></a>";
            } else
                $lastpost = "N/A";
        } else {
            if (is_valid_id($forums_arr['pid']))
                $lastpost = "" . $forums_arr["added"] . "<br />" . "by <a href='$DEFAULTBASEURL/userdetails.php?id=" . (int)$forums_arr["userid"] . "'><b>" . safeChar($forums_arr['username']) . "</b></a><br />" . "in <a href='" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=" . (int)$forums_arr["topicid"] . "&amp;page=p$lastpostid#p$lastpostid'><b>" . safeChar($forums_arr['subject']) . "</b></a>";
            else
                $lastpost = "N/A";
        }

        if (is_valid_id($forums_arr['pid']))
            $img = 'unlocked' . ((($forums_arr['added'] > (get_date_time(gmtime() - $READPOST_EXPIRY)))?((int)$forums_arr['pid'] > $forums_arr['lastpostread']):0)?'new':'');
        else
            $img = "unlocked";
        if ($subforums == false && !empty($sfa[$forumid])) {
            list($subposts, $subtopics) = get_count($sfa[$forumid]["count"]);
            $topics = $forums_arr["topiccount"] + $subtopics;
            $posts = $forums_arr["postcount"] + $subposts;
        } else {
            $topics = $forums_arr["topiccount"];
            $posts = $forums_arr["postcount"];
        }

        ?><tr>
			<td align='left' style="border:none;">
				<table border=0 cellspacing=0 cellpadding=0 style="border:none;">
					<tr>
						<td class=embedded style='padding-right: 5px'><img src="themes/<?=$ss_uri . "/forum/" . $img;
        ?>.png" /></td>
						<td class=embedded>
							<a href='<?php echo $_SERVER['PHP_SELF'];
        ?>?action=viewforum&amp;forumid=<?php echo $forumid;
        ?>'><b><?php echo safeChar($forums_arr["name"]);
        ?></b></a><?php
        if ($CURUSER['class'] >= UC_ADMINISTRATOR || isMod($forumid)) {

            ?>&nbsp;<font class='small'>[<a class='altlink' href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=editforum&amp;forumid=<?php echo $forumid;
            ?>'>Edit</a>][<a class='altlink' href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=deleteforum&amp;forumid=<?php echo $forumid;
            ?>'>Delete</a>]</font><?php
        }

        if (!empty($forums_arr["description"])) {

            ?><br /><?php echo safeChar($forums_arr["description"]);
        }
        if ($subforums == false && !empty($sfa[$forumid]))
            echo("<br/>" . subforums($sfa[$forumid]["topics"]));
        if ($show_mods == true && isset($forummods[$forumid]))
            print("<br/>" . showMods($forummods[$forumid]));

        ?></td>
					</tr>
				</table>
			</td>
			<td align='center'><?php echo number_format($topics);
        ?></td>
			<td align='center'><?php echo number_format($posts);
        ?></td>
			<td align='left' nowrap="nowrap"><?php echo $lastpost;
        ?></td>
		</tr><?php
    }
}
// -------- Returns the minimum read/write class levels of a forum
function get_forum_access_levels($forumid)
{
    $res = sql_query("SELECT minclassread, minclasswrite, minclasscreate FROM forums WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
        return false;

    $arr = mysql_fetch_assoc($res);

    return array("read" => $arr["minclassread"], "write" => $arr["minclasswrite"], "create" => $arr["minclasscreate"]);
}
// -------- Returns the forum ID of a topic, or false on error
function get_topic_forum($topicid)
{
    $res = sql_query("SELECT forumid FROM topics WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) != 1)
        return false;

    $arr = mysql_fetch_assoc($res);

    return (int)$arr['forumid'];
}
// -------- Returns the ID of the last post of a forum
function update_topic_last_post($topicid)
{
    $res = sql_query("SELECT MAX(id) AS id FROM posts WHERE topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res) or die("No post found");

    sql_query("UPDATE topics SET lastpost = {$arr['id']} WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
}

function get_forum_last_post($forumid)
{
    $res = sql_query("SELECT MAX(lastpost) AS lastpost FROM topics WHERE forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

    $arr = mysql_fetch_assoc($res);

    $postid = (int)$arr['lastpost'];

    return (is_valid_id($postid) ? $postid : 0);
}
// -------- Inserts a quick jump menu
function insert_quick_jump_menu($currentforum = 0)
{
    global $CURUSER, $DEFAULTBASEURL;

    ?>
	<div align='center'>
	<form method='get' action='<?php echo $_SERVER['PHP_SELF'];
    ?>' name='jump'>
	<input type="hidden" name="action" value="viewforum" />
	<div align='center'><b>Quick jump</b>
	<select name='forumid' onChange="if(this.options[this.selectedIndex].value != -1){ forms['jump'].submit() }">
	<?php
    $res = sql_query("SELECT id, name, minclassread FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);

    while ($arr = mysql_fetch_assoc($res))
    if ($CURUSER['class'] >= $arr["minclassread"])
        echo "<option value=" . $arr["id"] . ($currentforum == $arr["id"] ? " selected" : "") . '>' . $arr["name"] . "</option>";

    ?>
	</select>
	<input type='submit' value='Go!' class='gobutton' />
	</div>
	</form>
	</div>
	<?php
}
// -------- Inserts a compose frame
function insert_compose_frame($id, $newtopic = true, $quote = false, $attachment = false)
{
    global $maxsubjectlength, $CURUSER, $max_torrent_size, $maxfilesize, $pic_base_url, $use_attachment_mod, $forum_pics, $DEFAULTBASEURL;

    if ($newtopic) {
        $res = sql_query("SELECT name FROM forums WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res) or die("Bad forum ID!");

        ?><h3>New topic in <a href='<?php echo $_SERVER['PHP_SELF'];
        ?>?action=viewforum&amp;forumid=<?php echo $id;
        ?>'><?php echo safeChar($arr["name"]);
        ?></a> forum</h3><?php
    } else {
        $res = sql_query("SELECT subject, locked FROM topics WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res) or die("Forum error, Topic not found.");

        if ($arr['locked'] == 'yes') {
            stdmsg("Sorry", "The topic is locked.");

            end_table();
            end_main_frame();
            stdfoot();
            exit();
        }

        ?><h3 align="center"><?php echo $language['replyto'];?><a href='<?php echo $_SERVER['PHP_SELF'];
        ?>action=viewtopic&amp;topicid=<?php echo $id;
        ?>'><?php echo safeChar($arr["subject"]);
        ?></a></h3><?php
    }

    begin_frame("Compose", true);

    ?><form method='post' name='compose' action='<?php echo $_SERVER['PHP_SELF'];
    ?>' enctype='multipart/form-data'>
	<input type="hidden" name="action" value="post" />
	<input type='hidden' name='<?php echo ($newtopic ? 'forumid' : 'topicid');
    ?>' value='<?php echo $id;
    ?>' /><?php

    begin_table(true);

    if ($newtopic) {

        ?>
		<tr>
			<td class='rowhead' width="10%">Subject</td>
			<td align='left'>
				<input type='text' size='100' maxlength='<?php echo $maxsubjectlength;
        ?>' name='subject' style='height: 19px' />
			</td>
		</tr><?php
    }

    if ($quote) {
        $postid = (int)$_GET["postid"];
        if (!is_valid_id($postid)) {
            stdmsg("Error", "Invalid ID!");

            end_table();
            end_main_frame();
            stdfoot();
            exit();
        }

        $res = sql_query("SELECT posts.*, users.username FROM posts JOIN users ON posts.userid = users.id WHERE posts.id = $postid") or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 0) {
            stdmsg("Error", "No post with this ID");

            end_table();
            end_main_frame();
            stdfoot();
            exit();
        }

        $arr = mysql_fetch_assoc($res);
    }

    ?><tr>
		<td class='rowhead' width="10%">Body</td>
		<td><?php
    $qbody = ($quote ? "[quote=" . safeChar($arr["username"]) . "]" . safeChar(unesc($arr["body"])) . "[/quote]" : '');
    if (function_exists('textbbcode'))
        textbbcode("compose", "body", $qbody);
    else {

        ?><textarea name="body" style="width:99%" rows="7"><?php echo $qbody;
        ?></textarea><?php
    }

    if ($use_attachment_mod && $attachment) {

        ?><tr>
				<td colspan='2'><fieldset class="fieldset"><legend>Add attachment</legend>
					<input type='checkbox' name='uploadattachment' value='yes' />
					<input type="file" name="file" size="60" />
                    <div class='error'>Allowed files: rar, zip<br />Max file size: <?php echo prefixed($maxfilesize);
        ?></div></fieldset>
				</td>
			</tr><?php
    }

    ?>
		<tr>
		<td align=center colspan=2>
		<?=(post_icons())?>
		</td>
	</tr>
		<tr>
        	<td colspan='2' align='center'>
            <input type='submit' value='Submit' />
			</td>
		</tr>

		</td>
        </tr><?php

    end_table();

    ?></form><?php

    ?><?php

    end_frame();
    // ------ Get 10 last posts if this is a reply
    if (!$newtopic) {
        $postres = sql_query("SELECT p.id, p.added, p.body, u.id AS uid, u.username, u.avatar " . "FROM posts AS p " . "LEFT JOIN users AS u ON u.id = p.userid " . "WHERE p.topicid = " . sqlesc($id) . " " . "ORDER BY p.id DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($postres) > 0) {

            ?><br /><?php
            begin_frame("Last 10 post's in reverse order");

            while ($post = mysql_fetch_assoc($postres)) {
                $avatar = ($CURUSER["avatars"] == "yes" ? safeChar($post["avatar"]) : '');
               
                if (empty($avatar))
                    $avatar = $pic_base_url . $forum_pics['default_avatar'];

                ?><p class=sub>#<?php echo $post["id"];
                ?> by <?php echo (!empty($post["username"]) ? $post["username"] : "unknown[{$post['uid']}]");
                ?> at <?php echo $post["added"];
                ?> GMT</p><?php

                begin_table(true);

                ?>
					<tr>
						<td height='100' width='100' align='center' style='padding: 0px' valign="top"><img height='100' width='100' src="<?php echo $avatar;
                ?>" /></td>
						<td class='comment' valign='top'><?php echo format_comment($post["body"]);
                ?></td>
					</tr><?php

                end_table();
            }

            end_frame();
        }
    }

    insert_quick_jump_menu();
}

if ($action == 'updatetopic') {
    $topicid = (isset($_GET['topicid']) ? (int)$_GET['topicid'] : (isset($_POST['topicid']) ? (int)$_POST['topicid'] : 0));
    if (!is_valid_id($topicid))
        stderr('Error...', 'Invalid topic ID!');

    $topic_res = sql_query('SELECT t.sticky, t.locked, t.subject, t.forumid, f.minclasswrite, ' . '(SELECT COUNT(id) FROM posts WHERE topicid = t.id) As post_count ' . 'FROM topics AS t ' . 'LEFT JOIN forums AS f ON f.id = t.forumid ' . 'WHERE t.id = ' . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($topic_res) == 0)
        stderr('Error...', 'No topic with that ID!');

    $topic_arr = mysql_fetch_assoc($topic_res);
    if (isMod($topic_arr["forumid"]) || $CURUSER['class'] >= UC_MODERATOR) {
        if (($CURUSER['class'] < (int)$topic_arr['minclasswrite']) && !isMod($topic_arr["forumid"]))
            stderr('Error...', 'You are not allowed to edit this topic.');

        $forumid = (int)$topic_arr['forumid'];
        $subject = $topic_arr['subject'];

        if ((isset($_GET['delete']) ? $_GET['delete'] : (isset($_POST['delete']) ? $_POST['delete'] : '')) == 'yes') {
            if ((isset($_GET['sure']) ? $_GET['sure'] : (isset($_POST['sure']) ? $_POST['sure'] : '')) != 'yes')
                stderr("Sanity check...", "You are about to delete this topic: <b>" . safeChar($subject) . "</b>. Click <a href=" . $_SERVER['PHP_SELF'] . "?action=$action&amp;topicid=$topicid&amp;delete=yes&amp;sure=yes>here</a> if you are sure.");

            write_log("Topic <b>" . $subject . "</b> was deleted by <a href='$DEFAULTBASEURL/userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . "</a>.");

            if ($use_attachment_mod) {
                $res = sql_query("SELECT attachments.filename " . "FROM posts " . "LEFT JOIN attachments ON attachments.postid = posts.id " . "WHERE posts.topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

                while ($arr = mysql_fetch_assoc($res))
                if (!empty($arr['filename']) && is_file($attachment_dir . "/" . $arr['filename']))
                    unlink($attachment_dir . "/" . $arr['filename']);
            }

            sql_query("DELETE posts, topics " .
                ($use_attachment_mod ? ", attachments, attachmentdownloads " : "") .
                ($use_poll_mod ? ", postpolls, postpollanswers " : "") . "FROM topics " . "LEFT JOIN posts ON posts.topicid = topics.id " .
                ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") .
                ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id = topics.pollid " . "LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "WHERE topics.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=viewforum&forumid=' . $forumid);
            exit();
        }

        $returnto = $_SERVER['PHP_SELF'] . '?action=viewtopic&topicid=' . $topicid;

        $updateset = array();

        $locked = ($_POST['locked'] == 'yes' ? 'yes' : 'no');
        if ($locked != $topic_arr['locked'])
            $updateset[] = 'locked = ' . sqlesc($locked);

        $sticky = ($_POST['sticky'] == 'yes' ? 'yes' : 'no');
        if ($sticky != $topic_arr['sticky'])
            $updateset[] = 'sticky = ' . sqlesc($sticky);

        $new_subject = $_POST['subject'];
        if ($new_subject != $subject) {
            if (empty($new_subject))
                stderr('Error...', 'Topic name cannot be empty.');

            $updateset[] = 'subject = ' . sqlesc($new_subject);
        }

        $new_forumid = (int)$_POST['new_forumid'];
        if (!is_valid_id($new_forumid))
            stderr('Error...', 'Invalid forum ID!');

        if ($new_forumid != $forumid) {
            $post_count = (int)$topic_arr['post_count'];

            $res = sql_query("SELECT minclasswrite FROM forums WHERE id = " . sqlesc($new_forumid)) or sqlerr(__FILE__, __LINE__);

            if (mysql_num_rows($res) != 1)
                stderr("Error...", "Forum not found!");

            $arr = mysql_fetch_assoc($res);
            if ($CURUSER['class'] < (int)$arr['minclasswrite'])
                stderr('Error...', 'You are not allowed to move this topic into the selected forum.');

            $updateset[] = 'forumid = ' . sqlesc($new_forumid);

            sql_query("UPDATE forums SET topiccount = topiccount - 1, postcount = postcount - " . sqlesc($post_count) . " WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
            sql_query("UPDATE forums SET topiccount = topiccount + 1, postcount = postcount + " . sqlesc($post_count) . " WHERE id = " . sqlesc($new_forumid)) or sqlerr(__FILE__, __LINE__);

            $returnto = $_SERVER['PHP_SELF'] . '?action=viewforum&forumid=' . $new_forumid;
        }

        if (sizeof($updateset) > 0)
            sql_query("UPDATE topics SET " . implode(', ', $updateset) . " WHERE id = " . sqlesc($topicid));

        header('Location: ' . $returnto);
        exit();
    }
} else if ($action == "editforum") { // -------- Action: Edit Forum
        $forumid = (int)$_GET["forumid"];
    if ($CURUSER['class'] == MAX_CLASS || isMod($forumid)) {
        if (!is_valid_id($forumid))
            stderr('Error', 'Invalid ID!');

        $res = sql_query("SELECT name, description, minclassread, minclasswrite, minclasscreate FROM forums WHERE id = $forumid") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($res) == 0)
            stderr('Error', 'No forum found with that ID!');

        $forum = mysql_fetch_assoc($res);

        stdhead("Edit forum");
        if ($FORUMS_ONLINE == '0')
            stdmsg('Warning', ''.$language['maint'].'');
        begin_main_frame();
        begin_frame("Edit Forum", "center");
        echo("<form method=post action=" . $_SERVER['PHP_SELF'] . "?action=updateforum&amp;forumid=$forumid>\n");
        begin_table();
        echo("<tr><td class=rowhead>Forum name</td>" . "<td align=left style='padding: 0px'><input type=text size=60 maxlength=$maxsubjectlength name=name " . "style='border: 0px; height: 19px' value=\"" . safeChar($forum['name']) . "\" /></td></tr>\n" . "<tr><td class=rowhead>Description</td>" . "<td align=left style='padding: 0px'><textarea name=description cols=68 rows=3 style='border: 0px'>" . safeChar($forum['description']) . "</textarea></td></tr>\n" . "<tr><td class=rowhead></td><td align=left style='padding: 0px'>&nbsp;Minimum <select name=readclass>");
        for ($i = 0; $i <= MAX_CLASS; ++$i)
        echo("<option value=$i" . ($i == $forum['minclassread'] ? " selected" : "") . ">" . get_user_class_name($i) . "</option>\n");
        echo("</select> Class required to View<br/>\n&nbsp;Minimum <select name=writeclass>");
        for ($i = 0; $i <= MAX_CLASS; ++$i)
        echo("<option value=$i" . ($i == $forum['minclasswrite'] ? " selected" : "") . ">" . get_user_class_name($i) . "</option>\n");
        echo("</select> Class required to Post<br/>\n&nbsp;Minimum <select name=createclass>");
        for ($i = 0; $i <= MAX_CLASS; ++$i)
        echo("<option value=$i" . ($i == $forum['minclasscreate'] ? " selected" : "") . ">" . get_user_class_name($i) . "</option>\n");
        echo("</select> Class required to Create Topics</td></tr>\n" . "<tr><td colspan=2 align=center><input type=submit value='Submit' /></td></tr>\n");
        end_table();
        echo("</form>");

        end_frame();
        end_main_frame();
        stdfoot();
        exit();
    }
} else if ($action == "updateforum") { // -------- Action: Update Forum
        $forumid = (int)$_GET["forumid"];
    if ($CURUSER['class'] == MAX_CLASS || isMod($forumid)) {
        if (!is_valid_id($forumid))
            stderr('Error', 'Invalid ID!');

        $res = sql_query('SELECT id FROM forums WHERE id = ' . sqlesc($forumid));
        if (mysql_num_rows($res) == 0)
            stderr('Error', 'No forum with that ID!');

        $name = $_POST['name'];
        $description = $_POST['description'];

        if (empty($name))
            stderr("Error", "You must specify a name for the forum.");

        if (empty($description))
            stderr("Error", "You must provide a description for the forum.");

        sql_query("UPDATE forums SET name = " . sqlesc($name) . ", description = " . sqlesc($description) . ", minclassread = " . sqlesc((int)$_POST['readclass']) . ", minclasswrite = " . sqlesc((int)$_POST['writeclass']) . ", minclasscreate = " . sqlesc((int)$_POST['createclass']) . " WHERE id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
} else if ($action == 'deleteforum') { // -------- Action: Delete Forum
        $forumid = (int)$_GET['forumid'];
    if ($CURUSER['class'] == MAX_CLASS || isMod($forumid)) {
        if (!is_valid_id($forumid))
            stderr('Error', 'Invalid ID!');

        $confirmed = (int)$_GET['confirmed'];
        if (!$confirmed) {
            $rt = sql_query("SELECT topics.id, forums.name " . "FROM topics " . "LEFT JOIN forums ON forums.id=topics.forumid " . "WHERE topics.forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
            $topics = mysql_num_rows($rt);
            $posts = 0;

            if ($topics > 0) {
                while ($topic = mysql_fetch_assoc($rt)) {
                    $ids[] = $topic['id'];
                    $forum = $topic['name'];
                }

                $rp = sql_query("SELECT COUNT(id) FROM posts WHERE topicid IN (" . join(', ', $ids) . ")");
                foreach ($ids as $id)
                if ($a = mysql_fetch_row($rp))
                    $posts += $a[0];
            }

            if ($use_attachment_mod || $use_poll_mod) {
                $res = sql_query("SELECT " .
                    ($use_attachment_mod ? "COUNT(attachments.id) AS attachments " : "") .
                    ($use_poll_mod ? ($use_attachment_mod ? ', ' : '') . "COUNT(postpolls.id) AS polls " : "") . "FROM topics " . "LEFT JOIN posts ON topics.id=posts.topicid " .
                    ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " : "") .
                    ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id=topics.pollid " : "") . "WHERE topics.forumid=" . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

                ($use_attachment_mod ? $attachments = 0 : null);
                ($use_poll_mod ? $polls = 0 : null);

                if ($arr = mysql_fetch_assoc($res)) {
                    ($use_attachment_mod ? $attachments = $arr['attachments'] : null);
                    ($use_poll_mod ? $polls = $arr['polls'] : null);
                }
            }
            stderr("** WARNING! **", "Deleting forum with id=$forumid (" . $forum . ") will also delete " . $posts . " post" . ($posts != 1 ? 's' : '') . ($use_attachment_mod ? ", " . $attachments . " attachment" . ($attachments != 1 ? 's' : '') : "") . ($use_poll_mod ? " and " . ($polls - $attachments) . " poll" . (($polls - $attachments) != 1 ? 's' : '') : "") . " in " . $topics . " topic" . ($topics != 1 ? 's' : '') . ". [<a href=" . $_SERVER['PHP_SELF'] . "?action=deleteforum&amp;forumid=$forumid&amp;confirmed=1>ACCEPT</a>] [<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid>CANCEL</a>]");
        }

        $rt = sql_query("SELECT topics.id " . ($use_attachment_mod ? ", attachments.filename " : "") . "FROM topics " . "LEFT JOIN posts ON topics.id = posts.topicid " .
            ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " : "") . "WHERE topics.forumid = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);

        while ($topic = mysql_fetch_assoc($rt)) {
            $tids[] = $topic['id'];

            if ($use_attachment_mod && !empty($topic['filename'])) {
                $filename = $attachment_dir . "/" . $topic['filename'];
                if (is_file($filename))
                    unlink($filename);
            }
        }

        sql_query("DELETE posts.*, topics.*, forums.* " . ($use_attachment_mod ? ", attachments.*, attachmentdownloads.* " : "") . ($use_poll_mod ? ", postpolls.*, postpollanswers.* " : "") . "FROM posts " .
            ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") . "LEFT JOIN topics ON topics.id = posts.topicid " . "LEFT JOIN forums ON forums.id = topics.forumid " .
            ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id = topics.pollid " . "LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "WHERE posts.topicid IN (" . join(', ', $tids) . ")") or sqlerr(__FILE__, __LINE__);

        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
} else if ($action == "newtopic") { // -------- Action: New topic
        $forumid = (int)$_GET["forumid"];
    if (!is_valid_id($forumid))
        stderr('Error', 'Invalid ID!');

    stdhead("New topic");
    begin_main_frame();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');
    insert_compose_frame($forumid, true, false, true);
    end_main_frame();
    stdfoot();
    exit();
} else if ($action == "post") { // -------- Action: Post
        $forumid = (isset($_POST['forumid']) ? (int)$_POST['forumid'] : null);
    if (isset($forumid) && !is_valid_id($forumid))
        stderr('Error', 'Invalid forum ID!');

    $posticon = (isset($_POST["iconid"]) ? 0 + $_POST["iconid"] : 0);
    $topicid = (isset($_POST['topicid']) ? (int)$_POST['topicid'] : null);
    if (isset($topicid) && !is_valid_id($topicid))
        stderr('Error', 'Invalid topic ID!');
    // Anti Flood Code
    if (!($CURUSER['post_count'] < $CURUSER['post_max']))
        stderr('Notice', 'You have reached your Post limit. Please wait 15 minutes before retrying.');
    $newtopic = is_valid_id($forumid);

    $subject = (isset($_POST["subject"]) ? $_POST["subject"] : '');

    if ($newtopic) {
        $subject = trim($subject);

        if (empty($subject))
            stderr("Error", "You must enter a subject.");

        if (strlen($subject) > $maxsubjectlength)
            stderr("Error", "Subject is limited to " . $maxsubjectlength . " characters.");
    } else
        $forumid = get_topic_forum($topicid) or die("Bad topic ID");

    if ($CURUSER["forumpost"] == 'no')
        stderr("Sorry", "Your are not allowed to post.)");
    // ------ Make sure sure user has write access in forum
    $arr = get_forum_access_levels($forumid) or die("Bad forum ID");

    if ($CURUSER['class'] < $arr["write"] || ($newtopic && $CURUSER['class'] < $arr["create"]) && !isMod($forumid))
        stderr("Error", "Permission denied.");

    $body = trim($_POST["body"]);

    if (empty($body))
        stderr("Error", "No body text.");

    $userid = (int)$CURUSER["id"];

    if ($use_flood_mod && $CURUSER['class'] < UC_MODERATOR && !isMod($forumid)) {
        $res = sql_query("SELECT COUNT(id) AS c FROM posts WHERE userid = " . $CURUSER['id'] . " AND added > '" . get_date_time(gmtime() - ($minutes * 60)) . "'");
        $arr = mysql_fetch_assoc($res);

        if ($arr['c'] > $limit)
            stderr("Flood", "More than " . $limit . " posts in the last " . $minutes . " minutes.");
    }
    // //////////////bot for topics
    if ($newtopic) {
        sql_query("INSERT INTO topics (userid, forumid, subject) VALUES($userid, $forumid, " . sqlesc($subject) . ")") or sqlerr(__FILE__, __LINE__);
        $topicid = mysql_insert_id() or stderr("Error", "No topic ID returned!");
        $message = "" . $CURUSER['username'] . " started a new thread [url=$BASEURL/forums.php?action=viewtopic&topicid=$topicid&page=last]" . $subject . "[/url]";
        if (!in_array($forumid, array("1","8","9"))) {
            autoshout($message);
        }
        sql_query("INSERT INTO posts (topicid, userid, added, body, posticon) VALUES($topicid, $userid, " . sqlesc(get_date_time()) . ", " . sqlesc($body) . ",$posticon)") or sqlerr(__FILE__, __LINE__);
        $postid = mysql_insert_id() or stderr("Error", "No post ID returned!");
        update_topic_last_post($topicid);
        //===add karma
        sql_query("UPDATE users SET seedbonus = seedbonus+2.0 WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
        //===end
        sql_query("UPDATE users SET post_count = post_count + 1 WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    } else {
        // ---- Make sure topic exists and is unlocked
        $res = mysql_query("SELECT locked, subject FROM topics WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($res) == 0)
            stderr('Error', 'Inexistent Topic!');

        $arr = mysql_fetch_assoc($res);
        $subject = safeChar($arr["subject"]);

        if ($arr["locked"] == 'yes' && $CURUSER['class'] < UC_MODERATOR && !isMod($forumid))
            stderr("Error", "This topic is locked; No new posts are allowed.");
        //subscribe
            $q = sql_query("SELECT s.userid FROM subscriptions  as s LEFT JOIN users as u ON u.id = s.userid WHERE topicid = ".$topicid." AND subscription_pm='yes' ") or sqlerr(__FILE__, __LINE__);
            if(mysql_num_rows($q) > 0)
            {
                $subject = "New post in topic ".$arr["subject"];
                $body = "Hey there! \n A thread you subscribed to: [b]".$arr["subject"]."[/b] has had a new post!\n click [url=".$BASEURL."/forums.php?action=viewtopic&topicid=".$topicid."&page=last][b]HERE[/b][/url] to read it!\n\nTo view your subscriptions, or un-subscribe, click [url=".$BASEURL."/subscribe.php][b]HERE[/b][/url].\n\ncheers.";
                while($a = mysql_fetch_assoc($q))
                {
                    if($a["userid"] == $CURUSER["id"])
                               continue;
                    $pms[] = "(0,".$a["userid"].",".sqlesc($subject).",".sqlesc($body).",".sqlesc(get_date_time()).")";
                }
                if(count($pms) > 0)
                sql_query("INSERT INTO messages (sender, receiver, subject, msg, added) VALUES ".join(",",$pms)) or sqlerr(__FILE__, __LINE__);    
            }
        //endsubscribe
        // ------ Check double post
        $doublepost = sql_query("SELECT p.id, p.added, p.userid, p.body, t.lastpost, t.id " . "FROM posts AS p " . "INNER JOIN topics AS t ON p.id = t.lastpost " . "WHERE t.id = $topicid AND p.userid = $userid AND p.added > " . sqlesc(get_date_time(gmtime() - 1 * 86400)) . " " . "ORDER BY p.added DESC LIMIT 1") or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($doublepost) == 0 || $CURUSER['class'] >= UC_MODERATOR) {
            sql_query("INSERT INTO posts (topicid, userid, added, body,posticon) VALUES($topicid, $userid, " . sqlesc(get_date_time()) . ", " . sqlesc($body) . ",$posticon)") or sqlerr(__FILE__, __LINE__);
            $message = $CURUSER['username'] . " replied to the thread [url=$BASEURL/forums.php?action=viewtopic&topicid=$topicid&page=last] " . $subject . " [/url]";
            if (!in_array($forumid, array("1","8","9"))) {
            autoshout($message);
            }
            $postid = mysql_insert_id() or die("Post id n/a");

            update_topic_last_post($topicid);
        } else {
            $results = mysql_fetch_assoc($doublepost);
            $postid = (int)$results['lastpost'];

            sql_query("UPDATE posts SET body = " . sqlesc(trim($results['body']) . "\n\n" . $body) . ", editedat = " . sqlesc(get_date_time()) . ", editedby = $userid, posticon=$posticon WHERE id=$postid") or sqlerr(__FILE__, __LINE__);
        }
    }

    if ($use_attachment_mod && ((isset($_POST['uploadattachment']) ? $_POST['uploadattachment'] : '') == 'yes')) {
        $file = $_FILES['file'];

        $fname = trim(stripslashes($file['name']));
        $size = $file['size'];
        $tmpname = $file['tmp_name'];
        $tgtfile = $attachment_dir . "/" . $fname;
        $pp = pathinfo($fname = $file['name']);
        $error = $file['error'];
        $type = $file['type'];

        $uploaderror = '';

        if (empty($fname))
            $uploaderror = "Invalid Filename!";

        if (!validfilename($fname))
            $uploaderror = "Invalid Filename!";

        foreach ($allowed_file_extensions as $allowed_file_extension);
        if (!preg_match('/^(.+)\.[' . join(']|[', $allowed_file_extensions) . ']$/si', $fname, $matches))
            $uploaderror = 'Only files with the following extensions are allowed: ' . join(', ', $allowed_file_extensions) . '.';

        if ($size > $maxfilesize)
            $uploaderror = "Sorry, that file is too large.";

        if ($pp['basename'] != $fname)
            $uploaderror = "Bad file name.";

        if (file_exists($tgtfile))
            $uploaderror = "Sorry, a file with the name already exists.";

        if (!is_uploaded_file($tmpname))
            $uploaderror = "Can't Upload file!";

        if (!filesize($tmpname))
            $uploaderror = "Empty file!";

        if ($error != 0)
            $uploaderror = "There was an error while uploading the file.";

        if (empty($uploaderror)) {
            sql_query("INSERT INTO attachments (topicid, postid, filename, size, owner, added, type) VALUES ('$topicid','$postid'," . sqlesc($fname) . ", " . sqlesc($size) . ", '$userid', " . sqlesc(get_date_time()) . ", " . sqlesc($type) . ")") or sqlerr(__FILE__, __LINE__);

            move_uploaded_file($tmpname, $tgtfile);
        }
    }

    $headerstr = "Location: " . $_SERVER['PHP_SELF'] . "?action=viewtopic&topicid=$topicid" . ($use_attachment_mod && !empty($uploaderror) ? "&uploaderror=$uploaderror" : "") . "&page=last";

    header($headerstr . ($newtopic ? '' : "#p$postid"));
    exit();
} else if ($action == "viewtopic") { // -------- Action: View topic
        $userid = (int)$CURUSER["id"];

    if ($use_poll_mod && $_SERVER['REQUEST_METHOD'] == "POST") {
        $choice = $_POST['choice'];
        $pollid = (int)$_POST["pollid"];
        if (ctype_digit($choice) && $choice < 256 && $choice == floor($choice)) {
            $res = sql_query("SELECT pa.id " . "FROM postpolls AS p " . "LEFT JOIN postpollanswers AS pa ON pa.pollid = p.id AND pa.userid = " . sqlesc($userid) . " " . "WHERE p.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);
            $arr = mysql_fetch_assoc($res) or stderr('Sorry', 'Inexistent poll!');

            if (is_valid_id($arr['id']))
                stderr("Error...", "Dupe vote");

            sql_query("INSERT INTO postpollanswers VALUES(id, " . sqlesc($pollid) . ", " . sqlesc($userid) . ", " . sqlesc($choice) . ")") or sqlerr(__FILE__, __LINE__);

            if (mysql_affected_rows() != 1)
                stderr("Error...", "An error occured. Your vote has not been counted.");
        } else
            stderr("Error..." , "Please select an option.");
    }

    $topicid = (int)$_GET["topicid"];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid topic ID!');

    $page = (isset($_GET["page"]) ? $_GET["page"] : 0);
    // ------ Get topic info
    $res = sql_query("SELECT " . ($use_poll_mod ? 't.pollid, ' : '') . "t.locked, t.subject, t.sticky, t.userid AS t_userid, t.forumid, t.numratings, t.ratingsum, f.name AS forum_name, f.minclassread, f.minclasswrite, f.minclasscreate, (SELECT COUNT(id)FROM posts WHERE topicid = t.id) AS p_count " . "FROM topics AS t " . "LEFT JOIN forums AS f ON f.id = t.forumid " . "WHERE t.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr("Error", "Topic not found");
    mysql_free_result($res);

    ($use_poll_mod ? $pollid = (int)$arr["pollid"] : null);
    $t_userid = (int)$arr['t_userid'];
    $locked = ($arr['locked'] == 'yes' ? true : false);
    $subject = $arr['subject'];
    $sticky = ($arr['sticky'] == "yes" ? true : false);
    $forumid = (int)$arr['forumid'];
    $forum = $arr["forum_name"];
    $postcount = (int)$arr['p_count'];
    $rating = '';
    if ($arr["numratings"] != 0)
        $rating = ROUND($arr["ratingsum"] / $arr["numratings"], 1);
    $rpic = ratingpic($rating);
    if ($CURUSER["class"] < $arr["minclassread"])
        stderr("Error", "You are not permitted to view this topic.");
    // ------ Update hits column
    sql_query("UPDATE topics SET views = views + 1 WHERE id=$topicid") or sqlerr(__FILE__, __LINE__);
    // ------ Make page menu
    $pagemenu1 = "<p align='center'>";
    $perpage = $postsperpage;
    $pages = ceil($postcount / $perpage);

    if ($page[0] == "p") {
        $findpost = substr($page, 1);
        $res = sql_query("SELECT id FROM posts WHERE topicid=$topicid ORDER BY added") or sqlerr(__FILE__, __LINE__);
        $i = 1;
        while ($arr = mysql_fetch_row($res)) {
            if ($arr[0] == $findpost)
                break;
            ++$i;
        }
        $page = ceil($i / $perpage);
    }

    if ($page == "last")
        $page = $pages;
    else {
        if ($page < 1)
            $page = 1;
        else if ($page > $pages)
            $page = $pages;
    }

    $offset = ((int)$page * $perpage) - $perpage;
    $offset = ($offset < 0 ? 0 : $offset);

    $pagemenu2 = '';
    for ($i = 1; $i <= $pages; ++$i)
    $pagemenu2 .= ($i == $page ? "<b>[<u>$i</u>]</b>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=$topicid&amp;page=$i><b>$i</b></a>");

    $pagemenu1 .= ($page == 1 ? "<b>&lt;&lt;&nbsp;Prev</b>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page - 1) . "><b>&lt;&lt;&nbsp;Prev</b></a>");
    $pmlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $pagemenu3 = ($page == $pages ? "<b>Next&nbsp;&gt;&gt;</b></p>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=$topicid&amp;page=" . ($page + 1) . "><b>Next&nbsp;&gt;&gt;</b></a></p>");

    stdhead("Forums :: View Topic: $subject");
    echo "<script type='text/javascript' src='./scripts/popup.js'></script>";
    begin_main_frame();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');
    if ($use_poll_mod && is_valid_id($pollid)) {
        $res = sql_query("SELECT p.*, pa.id AS pa_id, pa.selection FROM postpolls AS p LEFT JOIN postpollanswers AS pa ON pa.pollid = p.id AND pa.userid = " . $CURUSER['id'] . " WHERE p.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) > 0) {
            $arr1 = mysql_fetch_assoc($res);

            $userid = (int)$CURUSER['id'];
            $question = safeChar($arr1["question"]);
            $o = array($arr1["option0"], $arr1["option1"], $arr1["option2"], $arr1["option3"], $arr1["option4"],
                $arr1["option5"], $arr1["option6"], $arr1["option7"], $arr1["option8"], $arr1["option9"],
                $arr1["option10"], $arr1["option11"], $arr1["option12"], $arr1["option13"], $arr1["option14"],
                $arr1["option15"], $arr1["option16"], $arr1["option17"], $arr1["option18"], $arr1["option19"]);

            ?><table cellpadding=5 width='<?php echo $forum_width;
            ?>' align='center'>
			<tr><td class=colhead align=left><h2>Poll<?php

            if ($userid == $t_userid || $CURUSER['class'] >= UC_MODERATOR) {

                ?><font class='small'> - [<a href='<?php echo $_SERVER['PHP_SELF'];
                ?>?action=makepoll&amp;subaction=edit&amp;pollid=<?php echo $pollid;
                ?>'><b>Edit</b></a>]</font><?php

                if ($CURUSER['class'] >= UC_MODERATOR) {

                    ?><font class='small'> - [<a href='<?php echo $_SERVER['PHP_SELF'];
                    ?>?action=deletepoll&amp;pollid=<?php echo $pollid;
                    ?>'><b>Delete</b></a>]</font><?php
                }
            }

            ?></h2></td></tr><?php
            ?><tr><td align=center class='clearalt7'><?php
            ?><table width="55%"><tr><td class='clearalt6'>
			<div align=center><b><?php echo $question;
            ?></b></div>
			<?php

            $voted = (is_valid_id($arr1['pa_id']) ? true : false);

            if (($locked && $CURUSER['class'] < UC_MODERATOR) ? true : $voted) {
                $uservote = ($arr1["selection"] != '' ? (int)$arr1["selection"] : -1);

                $res3 = sql_query("SELECT selection FROM postpollanswers WHERE pollid = " . sqlesc($pollid) . " AND selection < 20");
                $tvotes = mysql_num_rows($res3);

                $vs = $os = array();

                while ($arr3 = mysql_fetch_row($res3))
                $vs[$arr3[0]] += 1;

                reset($o);
                for ($i = 0; $i < count($o); ++$i)
                if ($o[$i])
                    $os[$i] = array($vs[$i], $o[$i]);

                function srt($a, $b)
                {
                    if ($a[0] > $b[0])
                        return -1;

                    if ($a[0] < $b[0])
                        return 1;

                    return 0;
                }

                if ($arr1["sort"] == "yes")
                    usort($os, "srt");

                ?><br /><?php
                ?><table width='100%' cellpadding="5"><?php
                for ($i = 0; $a = $os[$i]; ++$i) {
                    if ($i == $uservote)
                        $a[1] .= " *";

                    $p = ($tvotes == 0 ? 0 : round($a[0] / $tvotes * 100));
                    $c = ($i % 2 ? '' : "poll");

                    ?><tr><?php
                    ?><td width='1%' style="padding:3px;" class='embedded<?php echo $c;
                    ?>' nowrap="nowrap"><?php echo safeChar($a[1]);
                    ?></td><?php
                    ?><td width='99%' class='embedded<?php echo $c;
                    ?>' align="center"><?php
                    ?><img src='<?php echo $pic_base_url;
                    ?>bar_left.gif' /><?php
                    ?><img src='<?php echo $pic_base_url;
                    ?>bar.gif' height='9' width='<?php echo ($p * 3);
                    ?>' /><?php
                    ?><img src='<?php echo $pic_base_url;
                    ?>bar_right.gif' />&nbsp;<?php echo $p;
                    ?>%</td><?php
                    ?></tr><?php
                }

                ?></table><?php
                ?><p align=center>Votes: <b><?php echo number_format($tvotes);
                ?></b></p><?php
            } else {

                ?><form method=post action="<?php echo $_SERVER['PHP_SELF'];
                ?>?action=viewtopic&amp;topicid=<?php echo $topicid;
                ?>"><?php
                ?><input type='hidden' name='pollid' value=<?php echo $pollid;
                ?> /><?php

                for ($i = 0; $a = $o[$i]; ++$i)
                echo "<input type=radio name=choice value=$i />" . safeChar($a) . "<br />";

                ?><br /><?php
                ?><p align=center><input type=submit value='Vote!' /></p><?php
            }

            ?></form></td></tr></table><?php

            $listvotes = (isset($_GET['listvotes']) ? true : false);

            if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
                if (!$listvotes)
                    echo "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=$topicid&amp;listvotes>List Voters</a>";
                else {
                    $res4 = sql_query("SELECT pa.userid, u.username FROM postpollanswers AS pa LEFT JOIN users AS u ON u.id = pa.userid WHERE pa.pollid = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);
                    $voters = '';
                    while ($arr4 = mysql_fetch_assoc($res4)) {
                        if (!empty($voters) && !empty($arr4['username']))
                            $voters .= ', ';

                        $voters .= "<a href='$DEFAULTBASEURL/userdetails.php?id=" . (int)$arr4['userid'] . "'><b>" . safeChar($arr4['username']) . "</b></a>";
                    }

                    echo $voters . '(<font class="small"><a href="' . str_replace('&listvotes', '', $_SERVER['REQUEST_URI']) . '">hide</a></font>)';
                }
            }

            ?></td></tr></table><?php
        } else {

            ?><br /><?php
            stdmsg('Sorry', "Poll doesn't exist");
        }

        ?><br /><?php
    }

    ?><a name='top'></a>
    <h1 align="left"><a href="<?php echo $_SERVER['PHP_SELF'];
    ?>?action=viewforum&amp;forumid=<?php echo $forumid;
    ?>"><?php echo $forum;
    ?></a> &gt; <?php echo safeChar($subject);
    ?></h1><?php
    //echo("<br/><a href=subscriptions.php?topicid=$topicid&amp;subscribe=1><b><font color=green>Subscribe to Forum</font></b></a>");
    //subscribe
    $sub_c = mysql_fetch_row(sql_query("SELECT count(id) FROM subscriptions where topicid =".$topicid." AND userid = ".$CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
    echo("<h3><a href=\"subscribe.php?do=".($sub_c[0] == 1 ? "remove" : "add" )."&amp;tid=".$topicid."&amp;r=".urlencode($_SERVER["REQUEST_URI"])."\">".($sub_c[0] == 1 ? "Un-Subscribe from" : "Subscribe to" )." this topic</a></h3>");
    //end subscribe
    echo("<br /><br />");

    ?>
<script src="topic_rate.js" type="text/javascript"></script>
<script  type="text/javascript">
var linkset=new Array()
//SPECIFY MENU SETS AND THEIR LINKS. FOLLOW SYNTAX LAID OUT
linkset[0]='<p align=center><b>Rate Topic!<\/b><\/p>'
linkset[0]+='<a class=altlink href=takerate.php?topic_id=<?php echo $topicid?>&amp;rate_me=5> 5 <img src="pic/5.gif" alt="5 - tops" border=\"0\" /><\/a>'
linkset[0]+='<a class=altlink href=takerate.php?topic_id=<?php echo $topicid?>&amp;rate_me=4> 4 <img src="pic/4.gif" alt="4 - great" border=\"0\" /><\/a>'
linkset[0]+='<a class=altlink href=takerate.php?topic_id=<?php echo $topicid?>&amp;rate_me=3> 3 <img src="pic/3.gif" alt="3 - ok" border=\"0\" /><\/a>'
linkset[0]+='<a class=altlink href=takerate.php?topic_id=<?php echo $topicid?>&amp;rate_me=2> 2 <img src="pic/2.gif" alt="2 - eh" border=\"0\" /><\/a>'
linkset[0]+='<a class=altlink href=takerate.php?topic_id=<?php echo $topicid?>&amp;rate_me=1> 1 <img src="pic/1.gif" alt="1 - bad" border=\"0\"/><\/a>'

function confirm_att(id)
{
   if(confirm('Are you sure you want to delete this ?'))
   {
		window.open('<?=$SERVER['PHP_SELF']?>?action=attachment&subaction=delete&attachmentid='+id,'attachment','toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50');
		window.location.reload(true)
   }
}
</script>
<?php
    echo"<a class= altlink href=\"#\" onMouseover=\"showmenu(event,linkset[0])\" onMouseout=\"delayhidemenu()\"><b>Topic Rating : </b> $rpic</a>";
    // ------ echo table
    begin_frame();

    $res = sql_query("SELECT p.id, p.added, p.userid, p.added, p.body, p.editedby, p.editedat,p.posticon, u.id as uid, u.username as uusername, u.class, u.avatar, u.donor, u.title, u.mood, u.reputation, u.country, u.enabled, u.warned, u.uploaded, u.downloaded, u.signature, u.last_access, (SELECT COUNT(id)  FROM posts WHERE userid = u.id) AS posts_count, u2.username as u2_username " . ($use_attachment_mod ? ", at.id as at_id, at.filename as at_filename, at.postid as at_postid, at.size as at_size, at.downloads as at_downloads, at.owner as at_owner " : "") . ", (SELECT lastpostread FROM readposts WHERE userid = " . sqlesc((int)$CURUSER['id']) . " AND topicid = p.topicid LIMIT 1) AS lastpostread " . "FROM posts AS p " . "LEFT JOIN users AS u ON p.userid = u.id " .
        ($use_attachment_mod ? "LEFT JOIN attachments AS at ON at.postid = p.id " : "") . "LEFT JOIN users AS u2 ON u2.id = p.editedby " . "WHERE p.topicid = " . sqlesc($topicid) . " ORDER BY id LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);
    $pc = mysql_num_rows($res);
    $pn = 0;

    while ($arr = mysql_fetch_assoc($res)) {
        ++$pn;

        $lpr = $arr['lastpostread'];
        $postid = (int)$arr["id"];
        $postadd = $arr['added'];
        $posterid = (int)$arr['userid'];
        $posticon = ($arr["posticon"] > 0 ? "<img src=\"pic/post_icons/icon" . $arr["posticon"] . ".gif\" style=\"padding-left:3px;\" title=\"post icon\" />" : "&nbsp;");
        $added = $arr['added'] . " GMT <font class=small>(" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['added']))) . ")</font>";
        // ---- Get poster details
        $uploaded = prefixed($arr['uploaded']);
        $downloaded = prefixed($arr['downloaded']);
        $member_reputation = $arr['uusername'] != '' ? get_reputation($arr) : '';
        $last_access = $arr['last_access'];
        if ($arr['downloaded'] > 0) {
            $ratio = $arr['uploaded'] / $arr['downloaded'];
            $color = get_ratio_color($ratio);
            $ratio = number_format($ratio, 3);
            if ($color)
                $ratio = "<font color=$color>" . $ratio . "</font>";
        } else if ($arr['uploaded'] > 0)
            $ratio = "&infin;";
        else
            $ratio = "---";
        foreach($mood as $key => $value)
        $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
        $mooduname = $change[$arr['mood']]['name'];
        $moodupic = $change[$arr['mood']]['image'];
        if (($postid > $lpr) && ($postadd > (get_date_time(gmtime() - $READPOST_EXPIRY)))) {
            $newp = "&nbsp;&nbsp;<span class='red'>(New)</span>";
        }
        $signature = ($CURUSER['signatures'] == 'yes' ? format_comment($arr['signature']) : '');
        $postername = $arr['uusername'];
        $avatar = (!empty($postername) ? ($CURUSER['avatars'] == "yes" ? safeChar($arr['avatar']) : '') : '');
        $title = (!empty($postername) ? (empty($arr['title']) ? "(" . get_user_class_name($arr['class']) . ")" : "(" . format_comment($arr['title']) . ")") : '');
        $forumposts = (!empty($postername) ? ($arr['posts_count'] != 0 ? $arr['posts_count'] : 'N/A') : 'N/A');
        $by = (!empty($postername) ? "<a href='$DEFAULTBASEURL/userdetails.php?id=$posterid'>" . $postername . "</a>" . ($arr['donor'] == "yes" ? "<img src=" . $pic_base_url . "star.gif alt='Donor' />" : '') . ($arr['enabled'] == 'no' ? "<img src=" . $pic_base_url . "disabled.gif alt=\"This account is disabled\" style='margin-left: 2px' />" : ($arr['warned'] == 'yes'? "<img src=" . $pic_base_url . "warned.gif alt=\"Warned\" border=0 />" : '')) : "unknown[" . $posterid . "]");
         /////////////////Basic Forum rep mod///////////////////////////////
$resrep = sql_query("SELECT id FROM posts WHERE userid=$posterid");
$np = mysql_num_rows($resrep);
switch (true)
{
    case ($np >= 1000):
        $s = "yay";
        break;
    case ($np >= 700):
        $s = "w00t";
        break;
    case ($np >= 500):
        $s = "smile1";
        break;
    case ($np >= 300):
        $s = "mml";
        break;
    case ($np >= 100):
        $s = "sad";
        break;
        case ($np >= 50):
        $s = "cry";
        break;
    default;
        $s = "noexpression";
        break;
}
$np = "".number_format($np)."&nbsp;&nbsp;<img src=\"pic/smilies/{$s}.gif\" alt='' />";
//////////////////////////////////////////////////////////////////////////////////////
        if (empty($avatar))
            $avatar = $pic_base_url . $forum_pics['default_avatar'];
        // echo "<a name=$postid></a>";
        echo ($pn == $pc ? '<a name=last></a>' : '');

        begin_table();

        ?><tr><td width='737' colspan="2"><table class="main"><tr><td style="border:none;" width="100%"><?=$posticon?><a  id="p<?=$postid?>" name="p<?=$postid?>"href='<?php echo $_SERVER['PHP_SELF'];
        ?>?action=viewtopic&amp;topicid=<?=$topicid;
        ?>&amp;page=p<?=$postid;
        ?>#p<?=$postid;
        ?>'>#<?=$postid;
        ?></a> by <?=$by;
        ?> <?=$title;
        ?> <a href='usermood.php' onClick="return popitup('usermood.php')">
                                <span class='tool'><img border="0" src='<?php echo $pic_base_url;
        ?>smilies/<?php echo safe($moodupic);
        ?>' alt='' /><span class='tip'><?php echo safe($arr['uusername']);
        ?> <?php echo safe($mooduname);
        ?>!</span></span></a>  at <?php echo $added;
        if (isset($newp)) {
            echo ("$newp");
        }
        ?><?php
        ?></td><td style="border:none;"><a href="#top"><img align="right" border="0" src='<?php echo $pic_base_url . $forum_pics['arrow_up'];
        ?>' alt='Top' /></a></td></tr></table></td></tr><?php

        $highlight = (isset($_GET['highlight']) ? $_GET['highlight'] : '');
        $body = (!empty($highlight) ? highlight(safeChar(trim($highlight)), format_comment($arr['body'])) : format_comment($arr['body']));

        if (is_valid_id($arr['editedby']))
            $body .= "<p><font size=1 class=small>Last edited by <a href='$DEFAULTBASEURL/userdetails.php?id=" . $arr['editedby'] . "'><b>" . $arr['u2_username'] . "</b></a> at " . $arr['editedat'] . " GMT</font></p>";
        //if (is_valid_id($arr['editedby']))
            //$body .= "<p><font size=1 class=small><div id=\"nifty\" align=\"right\"><b class=\"rtop\"><b class=\"r1\"></b><b class=\"r2\"></b><b class=\"r3\"></b><b class=\"r4\"></b></b>Last edited by <a href='$DEFAULTBASEURL/userdetails.php?id=" . $arr['editedby'] . "'><b>" . $arr['u2_username'] . "</b></a> at " . $arr['editedat'] . " GMT</font></p></div><b class=\"rbottom\"><b class=\"r4\"></b><b class=\"r3\"></b><b class=\"r2\"></b><b class=\"r1\"></b></b>";
        if ($use_attachment_mod && ((!empty($arr['at_filename']) && is_valid_id($arr['at_id'])) && $arr['at_postid'] == $postid)) {
            foreach ($allowed_file_extensions as $allowed_file_extension)
            if (substr($arr['at_filename'], -3) == $allowed_file_extension)
                $aimg = $allowed_file_extension;

            $body .= "<div style=\"padding:6px\"><fieldset class=\"fieldset\">
					<legend>Attached Files</legend>

					<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\">
					<tr>
					<td nowrap=\"nowrap\"><img class=\"inlineimg\" src=\"$pic_base_url$aimg.gif\" width=\"16\" height=\"16\" border=\"0\" style=\"vertical-align:baseline\" />&nbsp;</td>
					<td nowrap=\"nowrap\"><a href=\"" . $_SERVER['PHP_SELF'] . "?action=attachment&amp;attachmentid=" . $arr['at_id'] . "\" target=\"_blank\">" . safeChar($arr['at_filename']) . "</a> (" . prefixed($arr['at_size']) . ", " . $arr['at_downloads'] . " downloads)</td>
					<td nowrap=\"nowrap\">&nbsp;&nbsp;<input type=\"button\" class=\"none\" value=\"See who downloaded\" tabindex=\"1\" onclick=\"window.open('" . $_SERVER['PHP_SELF'] . "?action=whodownloaded&amp;fileid=" . $arr['at_id'] . "','whodownloaded','toolbar=no, scrollbars=yes, resizable=yes, width=600, height=250, top=50, left=50'); return false;\" />" . ($CURUSER['class'] >= UC_MODERATOR ? "&nbsp;&nbsp;<input type=\"button\" class=\"gobutton\" value=\"Delete\" tabindex=\"2\" onclick=\"confirm_att(" . $arr['at_id'] . "); return false;\" />" : "") . "</td>
					</tr>
					</table>
					</fieldset>
					</div>";
        }

        if (!empty($signature))
            $body .= "<p style='vertical-align:bottom'><br/>____________________<br/>" . $signature;

        ?>
		<tr valign='top'><td width='150' align='center' style='padding: 0px'  nowrap="nowrap">
		<img width='150' src="<?=$avatar?>" /><br /><fieldset style='text-align:left;border:none;'>
		<b>Forum Reputation:&nbsp;&nbsp;<?php echo $np;?><br />
		<?php echo $member_reputation;?><br />
		<b><?php echo $language['posts'];?>:</b>&nbsp;&nbsp;&nbsp;<?=$forumposts;
        ?><br />
		<b><?php echo $language['ratio'];?></b>&nbsp;&nbsp;&nbsp;<?=$ratio;?><br />
		<b><?php echo $language['upped'];?></b>&nbsp;&nbsp;&nbsp;<?=$uploaded;
        ?><br />
		<b><?php echo $language['downed'];?></b>&nbsp;&nbsp;&nbsp;<?=$downloaded;
        ?>
		</fieldset></td><td class='text' width='100%'><?=$body;
        ?></td></tr><?php

        ?><tr>
			<td>
				<img src='<?php echo $pic_base_url . $forum_pics[($last_access > get_date_time(gmtime()-360) || $posterid == $CURUSER['id'] ? 'on' : 'off') . 'line_btn'];
        ?>' border=0 />&nbsp;
				<a href="<?php echo $DEFAULTBASEURL;
        ?>/sendmessage.php?receiver=<?php echo $posterid;
        ?>"><img src="<?php echo $pic_base_url . $forum_pics['pm_btn'];
        ?>" border="0" alt="PM <?php echo safeChar($postername);
        ?>" /></a>&nbsp;
				<a href='<?php echo $DEFAULTBASEURL;
        ?>/report.php?type=Post&amp;id=<?php echo $postid;
        ?>&amp;id_2=<?php echo $topicid;
        ?>&amp;id_3=<?php echo $posterid;
        ?>'><img src="<?php echo $pic_base_url . $forum_pics['p_report_btn'];
        ?>" border="0" alt="Report Post" /></a>
			</td>
			<td align='right'>
		<?php
        if (!$locked || $CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {

            ?><a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=quotepost&amp;topicid=<?php echo $topicid;
            ?>&amp;postid=<?php echo $postid;
            ?>'><img src="<?php echo $pic_base_url . $forum_pics['p_quote_btn'];
            ?>" border="0" alt="Quote Post" /></a>&nbsp;<?php
        }

        if ($CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {

            ?><a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=deletepost&amp;postid=<?php echo $postid;
            ?>'><img src="<?php echo $pic_base_url . $forum_pics['p_delete_btn'];
            ?>" border="0" alt="Delete Post" /></a>&nbsp;<?php
        }

        if (($CURUSER["id"] == $posterid && !$locked) || $CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {

            ?><a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=editpost&amp;postid=<?php echo $postid;
            ?>'><img src="<?php echo $pic_base_url . $forum_pics['p_edit_btn'];
            ?>" border="0" alt="Edit Post" /></a><?php
        }

        ?></td></tr><?php

        end_table();

        ?><br /><?php
    }

    if ($use_poll_mod && (($userid == $t_userid || $CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) && !is_valid_id($pollid))) {

        ?>
		<table cellpadding="5" width=<?php echo $forum_width;
        ?>>
        <tr>
        	<td align="right">
            	<form method='post' action='<?php echo $_SERVER['PHP_SELF'];
        ?>'>
                <input type='hidden' name='action' value="makepoll" />
				<input type='hidden' name='topicid' value="<?php echo $topicid;
        ?>" />
				<input type='submit' value='Add a Poll' />
				</form>
			</td>
        </tr>
        </table>
        <br />
        <?php
    }

    if (($postid > $lpr) && ($postadd > (get_date_time(gmtime() - $READPOST_EXPIRY)))) {
        if ($lpr)
            sql_query("UPDATE readposts SET lastpostread = $postid WHERE userid = $userid AND topicid = $topicid") or sqlerr(__FILE__, __LINE__);
        else
            sql_query("INSERT INTO readposts (userid, topicid, lastpostread) VALUES($userid, $topicid, $postid)") or sqlerr(__FILE__, __LINE__);
    }
    // ------ Mod options
    if ($CURUSER['class'] >= UC_MODERATOR || isMod($forumid)) {
        echo("<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">");
        begin_table();

        ?>
		<tr>
			<td colspan="2" class='colhead'><?php echo $language['soption'];?>
			<input type='hidden' name='action' value='updatetopic' />
			<input type='hidden' name='topicid' value='<?php echo $topicid;
        ?>' />
			</td>
		</tr>




		<tr>
			<td class="rowhead" width="1%"><?php echo $language['sticky'];?></td>
			<td>
				<select name="sticky">
					<option value="yes"<?php echo ($sticky ? " selected='selected'" : '');
        ?>>Yes</option>
					<option value="no"<?php echo (!$sticky ? " selected='selected'" : '');
        ?>>No</option>
				</select>
			</td>
		</tr>

		<tr>
			<td class="rowhead"><?php echo $language['locked'];?></td>
			<td>
				<select name="locked">
					<option value="yes"<?php echo ($locked ? " selected='selected'" : '');
        ?>>Yes</option>
					<option value="no"<?php echo (!$locked ? " selected='selected'" : '');
        ?>>No</option>
				</select>
			</td>
		</tr>

		<tr>
			<td class="rowhead"><?php echo $language['tname'];?></td>
			<td>
				<input type="text" name="subject" size="60" maxlength="<?php echo $maxsubjectlength;
        ?>" value="<?php echo safeChar($subject);
        ?>" />
			</td>
		</tr>

		<tr>
			<td class="rowhead"><?php echo $language['mtopic'];?></td>
			<td>
				<select name='new_forumid'><?php
        $res = sql_query("SELECT id, name, minclasswrite FROM forums ORDER BY name") or sqlerr(__FILE__, __LINE__);
        while ($arr = mysql_fetch_assoc($res))
        if ($CURUSER['class'] >= $arr["minclasswrite"])
            echo '<option value="' . (int)$arr["id"] . '"' . ($arr["id"] == $forumid ? ' selected="selected"' : '') . '>' . safeChar($arr["name"]) . '</option>';

        ?></select>
			</td>
		</tr>

		<tr>
			<td class="rowhead" nowrap="nowrap"><?php echo $language['dtopic'];?></td>
			<td>
				<select name="delete">
					<option value="no" selected="selected">No</option>
					<option value="yes">Yes</option>
				</select>

				<br />
				<b>Note:</b><?php echo $language['changes'];?>
			</td>
		</tr>

		<tr>
			<td colspan="2" align="center">
				<input type="submit" value="Update Topic" />
			</td>
		</tr>

		<?php
        end_table();
        echo("</form>");
    }

    end_frame();

    echo $pagemenu1 . $pmlb . $pagemenu2 . $pmlb . $pagemenu3;

    if ($locked && $CURUSER['class'] < UC_MODERATOR && !isMod($forumid)) {

        ?><p align="center">This topic is locked; no new posts are allowed.</p><?php
    } else {
        $arr = get_forum_access_levels($forumid);

        if ($CURUSER['class'] < $arr["write"]) {

            ?><p align="center"><i>You are not permitted to post in this forum.</i></p>

			<?php
            if ($CURUSER['post_max'] == 0)
                echo("<p><i>Your posting privilege has been revoked.</i></p>\n");
            elseif (!($CURUSER['post_count'] < $CURUSER['post_max']))
                echo("<p><i>You have reached your posting limit. Please retry in 15 minutes.</i></p>\n");

            $maypost = false;
        } else
            $maypost = true;
    }
    // ------ "View unread" / "Add reply" buttons
    ?>
	<table align="center" class="main" border=0 cellspacing=0 cellpadding=0><tr>
	<td class=embedded>
		<form method=get action='<?php echo $_SERVER['PHP_SELF'];
    ?>'><input type=hidden name=action value=viewunread /><input type=submit value='Show new' /></form>
	</td>
	<?php
    if ($maypost) {

        ?>
		<td class=embedded style='padding-left: 10px'>
			<form method=get action='<?php echo $_SERVER['PHP_SELF'];
        ?>'>
			<input type=hidden name=action value=reply /><input type="hidden" name="topicid" value="<?php echo $topicid;
        ?>" /><input type=submit value='Answer' /></form>
		</td>
		<?php
    }

    ?></tr></table><?php

    if ($maypost) {

        ?>
		<table style='border:1px solid #000000;' align="center"><tr>
		<td style='padding:10px;text-align:center;'>
		<b><?php echo $language['qreply'];?></b>
		<form name='compose' method='post' action='<?php echo $_SERVER['PHP_SELF'];
        ?>'>
		<input type=hidden name=action value=post />
		<input type=hidden name=topicid value=<?php echo $topicid;
        ?> />
		<textarea name="body" rows="4" cols="70"></textarea><br />
		<input type=submit value="Submit" />
		</form></td></tr></table>
		<?php
    }
    // ------ Forum quick jump drop-down
    insert_quick_jump_menu($forumid);

    end_main_frame();
    stdfoot();

    $uploaderror = (isset($_GET['uploaderror']) ? safeChar($_GET['uploaderror']) : '');

    if (!empty($uploaderror)) {

        ?><script>alert("Upload Failed: <?php echo $uploaderror;
        ?>\nHowever your post was successful saved!\n\nClick 'OK' to continue.");</script><?php
    }

    exit();
} else if ($action == "quotepost") { // -------- Action: Quote
        $topicid = (int)$_GET["topicid"];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid ID!');

    stdhead("Post reply");
    begin_main_frame();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');
    insert_compose_frame($topicid, false, true);
    end_main_frame();
    stdfoot();
    exit();
} else if ($action == "reply") { // -------- Action: Reply
        $topicid = (int)$_GET["topicid"];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid ID!');

    stdhead("Post reply");
    begin_main_frame();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');
    insert_compose_frame($topicid, false, false, true);
    end_main_frame();
    stdfoot();
    exit();
} else if ($action == "editpost") { // -------- Action: Edit post
        $postid = (int)$_GET["postid"];
    if (!is_valid_id($postid))
        stderr('Error', 'Invalid ID!');

    $res = sql_query("SELECT p.userid, p.topicid, p.posticon, p.body, t.locked,t.forumid  " . "FROM posts AS p " . "LEFT JOIN topics AS t ON t.id = p.topicid " . "WHERE p.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
        stderr("Error", "No post with that ID!");

    $arr = mysql_fetch_assoc($res);

    if (($CURUSER["id"] != $arr["userid"] || $arr["locked"] == 'yes') && $CURUSER['class'] < UC_MODERATOR && !isMod($arr["forumid"]))
        stderr("Error", "Access Denied!");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $body = trim($_POST['body']);
        $posticon = (isset($_POST["iconid"]) ? 0 + $_POST["iconid"] : 0);
        if (empty($body))
            stderr("Error", "Body cannot be empty!");

        sql_query("UPDATE posts SET body = " . sqlesc($body) . ", editedat = " . sqlesc(get_date_time()) . ", editedby = {$CURUSER['id']}, posticon=$posticon WHERE id = $postid") or sqlerr(__FILE__, __LINE__);

        header("Location: {$_SERVER['PHP_SELF']}?action=viewtopic&topicid={$arr['topicid']}&page=p$postid#p$postid");
        exit();
    }

    stdhead();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');
    begin_main_frame();

    ?>
	<h3>Edit Post</h3>

	<form name=edit method=post action='<?php echo $_SERVER['PHP_SELF'];
    ?>?action=editpost&amp;postid=<?php echo $postid;
    ?>'>

	<table border=1 cellspacing=0 cellpadding=5 width=100%>
	<tr>
		<td class=rowhead width="10%">Body</td>
		<td align=left style='padding: 0px'>
		<?php
    $ebody = safeChar(unesc($arr["body"]));
    if (function_exists('textbbcode'))
        textbbcode("edit", "body", $ebody);
    else {

        ?><textarea name="body" style="width:99%" rows="7"><?php echo $ebody;
        ?></textarea><?php
    }

    ?>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
		<?=(post_icons($arr["posticon"]))?>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2><input type=submit value='Update post' class=gobutton /></td>
	</tr>
	</table>

	</form>

	<?php
    end_main_frame();
    stdfoot();
    exit();
} elseif ($action == "deletetopic") {
    $topicid = (int)$_GET['topicid'];
    if (!is_valid_id($topicid))
        stderr('Error', 'Invalid ID');

    $r = mysql_query("SELECT t.id,t.subject " . ($use_poll_mod ? ",t.pollid" : "") . ",t.forumid,(SELECT COUNT(p.id) FROM posts as p where p.topicid=" . $topicid . ") AS posts FROM topics as t WHERE t.id=" . $topicid) or sqlerr(__FILE__, __LINE__);
    $a = mysql_fetch_assoc($r) or stderr("Error", "No topic was found");

    if ($CURUSER["class"] >= UC_MODERATOR || isMod($a["forumid"])) {
        $sure = (int)$_GET['sure'];
        if (!$sure)
            stderr("Sanity check...", "You are about to delete topic " . $a["subject"] . ". Click <a href=" . $_SERVER['PHP_SELF'] . "?action=deletetopic&amp;topicid=$topicid&amp;sure=1>here</a> if you are sure.");
        else {
            write_log("Topic <b>" . $a["subject"] . "</b> was deleted by <a href='$DEFAULTBASEURL/userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . "</a>.");

            if ($use_attachment_mod) {
                $res = sql_query("SELECT attachments.filename " . "FROM posts " . "LEFT JOIN attachments ON attachments.postid = posts.id " . "WHERE posts.topicid = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

                while ($arr = mysql_fetch_assoc($res))
                if (!empty($arr['filename']) && is_file($attachment_dir . "/" . $arr['filename']))
                    unlink($attachment_dir . "/" . $arr['filename']);
            }

            sql_query("DELETE posts, topics " .
                ($use_attachment_mod ? ", attachments, attachmentdownloads " : "") .
                ($use_poll_mod ? ", postpolls, postpollanswers " : "") . "FROM topics " . "LEFT JOIN posts ON posts.topicid = topics.id " .
                ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") .
                ($use_poll_mod ? "LEFT JOIN postpolls ON postpolls.id = topics.pollid " . "LEFT JOIN postpollanswers ON postpollanswers.pollid = postpolls.id " : "") . "WHERE topics.id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=viewforum&forumid=' . $a["forumid"]);
            exit();
        }
    }
} else if ($action == 'deletepost') { // -------- Action: Delete post
        $postid = (int)$_GET['postid'];
    if (!is_valid_id($postid))
        stderr('Error', 'Invalid ID');

    $res = sql_query("SELECT p.topicid " . ($use_attachment_mod ? ", a.filename" : "") . ", t.forumid, (SELECT COUNT(id) FROM posts WHERE topicid=p.topicid) AS posts_count, " . "(SELECT MAX(id) FROM posts WHERE topicid=p.topicid AND id < p.id) AS p_id " . "FROM posts AS p " . "LEFT JOIN topics as t on t.id=p.topicid " .
        ($use_attachment_mod ? "LEFT JOIN attachments AS a ON a.postid = p.id " : "") . "WHERE p.id=" . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr("Error", "Post not found");

    if (isMod($arr["forumid"]) || $CURUSER['class'] >= UC_MODERATOR) {
        $topicid = (int)$arr['topicid'];

        if ($arr['posts_count'] < 2)
            stderr("Error", "Can't delete post; it is the only post of the topic. You should<br /><a href=" . $_SERVER['PHP_SELF'] . "?action=deletetopic&amp;topicid=$topicid>delete the topic</a> instead.");

        $redirtopost = (is_valid_id($arr['p_id']) ? "&page=p" . $arr['p_id'] . "#p" . $arr['p_id'] : '');

        $sure = (int)$_GET['sure'];
        if (!$sure)
            stderr("Sanity check...", "You are about to delete a post. Click <a href=" . $_SERVER['PHP_SELF'] . "?action=deletepost&amp;postid=$postid&amp;sure=1>here</a> if you are sure.");

        sql_query("DELETE posts.* " . ($use_attachment_mod ? ", attachments.*, attachmentdownloads.* " : "") . "FROM posts " .
            ($use_attachment_mod ? "LEFT JOIN attachments ON attachments.postid = posts.id " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " : "") . "WHERE posts.id = " . sqlesc($postid)) or sqlerr(__FILE__, __LINE__);

        if ($use_attachment_mod && !empty($arr['filename'])) {
            $filename = $attachment_dir . "/" . $arr['filename'];
            if (is_file($filename))
                unlink($filename);
        }

        update_topic_last_post($topicid);

        header("Location: {$_SERVER['PHP_SELF']}?action=viewtopic&topicid=" . $topicid . $redirtopost);
        exit();
    }
} else if ($use_poll_mod && ($action == 'deletepoll' && $CURUSER['class'] >= UC_MODERATOR)) {
    $pollid = (int)$_GET["pollid"];
    if (!is_valid_id($pollid))
        stderr("Error", "Invalid ID!");

    $res = sql_query("SELECT pp.id, t.id AS tid FROM postpolls AS pp LEFT JOIN topics AS t ON t.pollid = pp.id WHERE pp.id = " . sqlesc($pollid));
    if (mysql_num_rows($res) == 0)
        stderr("Error", "No poll found with that ID.");

    $arr = mysql_fetch_array($res);

    $sure = (int)$_GET['sure'];
    if (!$sure || $sure != 1)
        stderr('Sanity check...', 'You are about to delete a poll. Click <a href=' . $_SERVER['PHP_SELF'] . '?action=' . safeChar($action) . '&amp;pollid=' . $arr['id'] . '&amp;sure=1>here</a> if you are sure.');

    sql_query("DELETE pp.*, ppa.* FROM postpolls AS pp LEFT JOIN postpollanswers AS ppa ON ppa.pollid = pp.id WHERE pp.id = " . sqlesc($pollid));

    if (mysql_affected_rows() == 0)
        stderr('Sorry...', 'There was an error while deleting the poll, please re-try.');

    sql_query("UPDATE topics SET pollid = '0' WHERE pollid = " . sqlesc($pollid));

    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=viewtopic&topicid=' . (int)$arr['tid']);
    exit();
} else if ($use_poll_mod && $action == 'makepoll') {
    $subaction = (isset($_GET["subaction"]) ? $_GET["subaction"] : (isset($_POST["subaction"]) ? $_POST["subaction"] : ''));
    $pollid = (isset($_GET["pollid"]) ? (int)$_GET["pollid"] : (isset($_POST["pollid"]) ? (int)$_POST["pollid"] : 0));

    $topicid = (isset($_POST["topicid"]) ? (int)$_POST["topicid"] : 0);

    if ($subaction == "edit") {
        if (!is_valid_id($pollid))
            stderr("Error", "Invalid ID!");

        $res = sql_query("SELECT pp.*, t.id AS tid FROM postpolls AS pp LEFT JOIN topics AS t ON t.pollid = pp.id WHERE pp.id = " . sqlesc($pollid)) or sqlerr(__FILE__, __LINE__);

        if (mysql_num_rows($res) == 0)
            stderr("Error", "No poll found with that ID.");

        $poll = mysql_fetch_assoc($res);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !$topicid) {
        $topicid = (int)($subaction == "edit" ? $poll['tid'] : $_POST["updatetopicid"]);

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

        if ($subaction == "edit" && is_valid_id($pollid))
            sql_query("UPDATE postpolls SET " . "question = " . sqlesc($question) . ", " . "option0 = " . sqlesc($option0) . ", " . "option1 = " . sqlesc($option1) . ", " . "option2 = " . sqlesc($option2) . ", " . "option3 = " . sqlesc($option3) . ", " . "option4 = " . sqlesc($option4) . ", " . "option5 = " . sqlesc($option5) . ", " . "option6 = " . sqlesc($option6) . ", " . "option7 = " . sqlesc($option7) . ", " . "option8 = " . sqlesc($option8) . ", " . "option9 = " . sqlesc($option9) . ", " . "option10 = " . sqlesc($option10) . ", " . "option11 = " . sqlesc($option11) . ", " . "option12 = " . sqlesc($option12) . ", " . "option13 = " . sqlesc($option13) . ", " . "option14 = " . sqlesc($option14) . ", " . "option15 = " . sqlesc($option15) . ", " . "option16 = " . sqlesc($option16) . ", " . "option17 = " . sqlesc($option17) . ", " . "option18 = " . sqlesc($option18) . ", " . "option19 = " . sqlesc($option19) . ", " . "sort = " . sqlesc($sort) . " " . "WHERE id = " . sqlesc((int)$poll["id"])) or sqlerr(__FILE__, __LINE__);
        else {
            if (!is_valid_id($topicid))
                stderr('Error', 'Invalid topic ID!');

            sql_query("INSERT INTO postpolls VALUES(id" . ", " . sqlesc(get_date_time()) . ", " . sqlesc($question) . ", " . sqlesc($option0) . ", " . sqlesc($option1) . ", " . sqlesc($option2) . ", " . sqlesc($option3) . ", " . sqlesc($option4) . ", " . sqlesc($option5) . ", " . sqlesc($option6) . ", " . sqlesc($option7) . ", " . sqlesc($option8) . ", " . sqlesc($option9) . ", " . sqlesc($option10) . ", " . sqlesc($option11) . ", " . sqlesc($option12) . ", " . sqlesc($option13) . ", " . sqlesc($option14) . ", " . sqlesc($option15) . ", " . sqlesc($option16) . ", " . sqlesc($option17) . ", " . sqlesc($option18) . ", " . sqlesc($option19) . ", " . sqlesc($sort) . ")") or sqlerr(__FILE__, __LINE__);

            $pollnum = mysql_insert_id();

            sql_query("UPDATE topics SET pollid = " . sqlesc($pollnum) . " WHERE id = " . sqlesc($topicid)) or sqlerr(__FILE__, __LINE__);
        }

        header("Location: {$_SERVER['PHP_SELF']}?action=viewtopic&topicid=$topicid");
        exit();
    }
    stdhead();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');
    begin_main_frame();
    if ($subaction == "edit")
        echo "<h1>Edit poll</h1>";

    ?>
	<table border=1 cellspacing=0 cellpadding=5 width=100%>
	<form method=post action='<?php echo $_SERVER['PHP_SELF'];
    ?>'>
    <input type=hidden name=action value=<?php echo $action;
    ?> />
	<input type=hidden name=subaction value=<?php echo $subaction;
    ?> />
	<input type=hidden name=updatetopicid value=<?php echo (int)$topicid;
    ?> />
	<?php
    if ($subaction == "edit") {

        ?><input type=hidden name=pollid value=<?php echo (int)$poll["id"];
        ?> /><?php
    }

    ?>
	<tr><td class=rowhead>Question <font color=red>*</font></td><td align=left><textarea name=question cols=70 rows=4><?php echo ($subaction == "edit" ? safeChar($poll['question']) : '');
    ?></textarea></td></tr>
	<tr><td class=rowhead>Option 1 <font color=red>*</font></td><td align=left><input name=option0 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option0']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 2 <font color=red>*</font></td><td align=left><input name=option1 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option1']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 3</td><td align=left><input name=option2 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option2']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 4</td><td align=left><input name=option3 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option3']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 5</td><td align=left><input name=option4 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option4']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 6</td><td align=left><input name=option5 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option5']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 7</td><td align=left><input name=option6 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option6']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 8</td><td align=left><input name=option7 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option7']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 9</td><td align=left><input name=option8 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option8']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 10</td><td align=left><input name=option9 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option9']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 11</td><td align=left><input name=option10 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option10']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 12</td><td align=left><input name=option11 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option11']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 13</td><td align=left><input name=option12 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option12']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 14</td><td align=left><input name=option13 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option13']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 15</td><td align=left><input name=option14 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option14']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 16</td><td align=left><input name=option15 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option15']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 17</td><td align=left><input name=option16 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option16']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 18</td><td align=left><input name=option17 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option17']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 19</td><td align=left><input name=option18 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option18']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Option 20</td><td align=left><input name=option19 size=80 maxlength=40 value="<?php echo ($subaction == "edit" ? safeChar($poll['option19']) : '');
    ?>"><br/></td></tr>
	<tr><td class=rowhead>Sort</td><td>
	<input type=radio name=sort value=yes <?php echo ($subaction == "edit" ? ($poll["sort"] != "no" ? " checked" : "") : '');
    ?> />Yes
	<input type=radio name=sort value=no <?php echo ($subaction == "edit" ? ($poll["sort"] == "no" ? " checked" : "") : ' checked');
    ?> /> No
	</td></tr>
	<tr><td colspan=2 align=center><input type=submit value='<?php echo ($pollid ? 'Edit poll' : 'Create poll');
    ?>' style='height: 20pt'></td></tr>
	</table>
	<p align="center"><font color=red>*</font> required</p>

	</form><?php
    end_main_frame();
    stdfoot();
} else if ($use_attachment_mod && $action == "attachment") {
    @ini_set('zlib.output_compression', 'Off');
    @set_time_limit(0);

    if (@ini_get('output_handler') == 'ob_gzhandler' && @ob_get_length() !== false) {
        @ob_end_clean();
        header('Content-Encoding:');
    }

    $id = (int)$_GET['attachmentid'];
    if (!is_valid_id($id))
        die('Invalid Attachment ID!');

    $at = sql_query("SELECT filename, owner, type FROM attachments WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $resat = mysql_fetch_assoc($at) or die('No attachment with that ID!');
    $filename = $attachment_dir . '/' . $resat['filename'];

    if (!is_file($filename))
        die('Inexistent atachment.');

    if (!is_readable($filename))
        die('Attachment is unreadable.');

    if ((isset($_GET['subaction']) ? $_GET['subaction'] : '') == 'delete') {
        if ($CURUSER['id'] <> $resat["owner"] && $CURUSER['class'] < UC_MODERATOR)
            die('Not your attachment to delete.');

        unlink($filename);

        sql_query("DELETE attachments, attachmentdownloads " . "FROM attachments " . "LEFT JOIN attachmentdownloads ON attachmentdownloads.fileid = attachments.id " . "WHERE attachments.id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

        die('<font color=red>File successfully deleted...</font>');
    }

    sql_query("UPDATE attachments SET downloads = downloads + 1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);

    $res = sql_query("SELECT fileid FROM attachmentdownloads WHERE fileid=" . sqlesc($id) . " AND userid=" . sqlesc($CURUSER['id']));
    if (mysql_num_rows($res) == 0)
        sql_query("INSERT INTO attachmentdownloads (fileid, username, userid, date, downloads) VALUES (" . sqlesc($id) . ", " . sqlesc($CURUSER['username']) . ", " . sqlesc($CURUSER['id']) . ", " . sqlesc(get_date_time()) . ", 1)") or sqlerr(__FILE__, __LINE__);
    else
        sql_query("UPDATE attachmentdownloads SET downloads = downloads + 1 WHERE fileid = " . sqlesc($id) . " AND userid = " . sqlesc($CURUSER['id']));

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false); // required for certain browsers
    header("Content-Type: " . $arr['type']);
    header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($filename));
    readfile($filename);
    exit();
} else if ($use_attachment_mod && $action == "whodownloaded") {
    $fileid = (int)$_GET['fileid'];
    if (!is_valid_id($fileid))
        die('Invalid ID!');

    $res = sql_query("SELECT fileid, at.filename, userid, username, atdl.downloads, date, at.downloads AS dl " . "FROM attachmentdownloads AS atdl " . "LEFT JOIN attachments AS at ON at.id=atdl.fileid " . "WHERE fileid = " . sqlesc($fileid) . ($CURUSER['class'] < UC_MODERATOR ? " AND owner=" . $CURUSER['id'] : '')) or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
        die('<h2 align="center">Nothing found!</h2>');
    else {

        ?><html><head><link rel="stylesheet" href="<?php echo $DEFAULTBASEURL;
        ?>/default.css" type="text/css" media="screen" /></head><body>
		<table width='100%' cellpadding='5' border="1">
		<tr align="center">
			<td>File Name</td>
			<td nowrap="nowrap" >Downloaded by</td>
			<td>Downloads</td>
			<td>Date</td>
		</tr><?php

        $dls = 0;
        while ($arr = mysql_fetch_assoc($res)) {
            echo "<tr align='center'>" . "<td>" . safeChar($arr['filename']) . "</td>" . "<td><a class='pointer' onclick=\"opener.location=('/userdetails.php?id=" . (int)$arr['userid'] . "'); self.close();\">" . safeChar($arr['username']) . "</a></td>" . "<td>" . (int)$arr['downloads'] . "</td>" . "<td>" . $arr['date'] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($arr['date'])) . ")</td>" . "</tr>";

            $dls += (int)$arr['downloads'];
        }

        ?><tr><td colspan='4'><b>Total Downloads:</b> <b><?php echo number_format($dls);
        ?></b></td></tr></table></body></html><?php
    }
} else if ($action == "viewforum") { // -------- Action: View forum
        $forumid = (int)$_GET['forumid'];
    if (!is_valid_id($forumid))
        stderr('Error', 'Invalid ID!');

    $page = (isset($_GET["page"]) ? (int)$_GET["page"] : 0);
    $userid = (int)$CURUSER["id"];
    // ------ Get forum details
    $res = sql_query("SELECT f.name AS forum_name, f.minclassread, (SELECT COUNT(id) FROM topics WHERE forumid = f.id) AS t_count " . "FROM forums AS f " . "WHERE f.id = " . sqlesc($forumid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res) or stderr('Error', 'No forum with that ID!');

    if ($CURUSER['class'] < $arr["minclassread"])
        stderr('Error', 'Access Denied!');

    $perpage = (empty($CURUSER['topicsperpage']) ? 20 : (int)$CURUSER['topicsperpage']);
    $num = (int)$arr['t_count'];

    if ($page == 0)
        $page = 1;

    $first = ($page * $perpage) - $perpage + 1;
    $last = $first + $perpage - 1;

    if ($last > $num)
        $last = $num;

    $pages = floor($num / $perpage);

    if ($perpage * $pages < $num)
        ++$pages;
    // ------ Build menu
    $menu1 = "<p class=success align=center>";
    $menu2 = '';

    $lastspace = false;
    for ($i = 1; $i <= $pages; ++$i) {
        if ($i == $page)
            $menu2 .= "<b>[<u>$i</u>]</b>\n";

        else if ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3)) {
            if ($lastspace)
                continue;

            $menu2 .= "... \n";

            $lastspace = true;
        } else {
            $menu2 .= "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid&amp;page=$i><b>$i</b></a>\n";

            $lastspace = false;
        }

        if ($i < $pages)
            $menu2 .= "<b>|</b>";
    }

    $menu1 .= ($page == 1 ? "<b>&lt;&lt;&nbsp;Prev</b>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page - 1) . "><b>&lt;&lt;&nbsp;Prev</b></a>");
    $mlb = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $menu3 = ($last == $num ? "<b>Next&nbsp;&gt;&gt;</b></p>" : "<a href=" . $_SERVER['PHP_SELF'] . "?action=viewforum&amp;forumid=$forumid&amp;page=" . ($page + 1) . "><b>Next&nbsp;&gt;&gt;</b></a></p>");

    $offset = $first - 1;

    $topics_res = sql_query("SELECT t.id, t.userid, t.views, t.locked, t.sticky" . ($use_poll_mod ? ', t.pollid' : '') . ", t.subject, u1.username, r.lastpostread, p.id AS p_id,p2.posticon, p.userid AS p_userid, p.added AS p_added, (SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count, u2.username AS u2_username " . "FROM topics AS t " . "LEFT JOIN users AS u1 ON u1.id=t.userid " . "LEFT JOIN readposts AS r ON r.userid = " . sqlesc($userid) . " AND r.topicid = t.id " . "LEFT JOIN posts AS p ON p.id = (SELECT MAX(id) FROM posts WHERE topicid = t.id) " . "LEFT JOIN posts AS p2 ON p2.id = (SELECT MIN(id) FROM posts WHERE topicid = t.id) " . "LEFT JOIN users AS u2 ON u2.id = p.userid " . "WHERE t.forumid = " . sqlesc($forumid) . " ORDER BY t.sticky, t.lastpost DESC LIMIT $offset, $perpage") or sqlerr(__FILE__, __LINE__);
    // subforums
    $r_subforums = sql_query("SELECT id FROM forums where place=" . $forumid);
    $subforums = mysql_num_rows($r_subforums);

    stdhead("Forum - " . safeChar($arr["forum_name"]));
    begin_main_frame();
    if ($FORUMS_ONLINE == '0')
        stdmsg('Warning', ''.$language['maint'].'');

    if ($subforums > 0) {
        // begin_frame();
        ?>
	<table border=1 cellspacing=0 cellpadding=5 width='<?php echo $forum_width;
        ?>'>
		<tr><td colspan="4" class=colhead align=left><?php echo safeChar($arr["forum_name"]);
        ?><?php echo $language['subf'];?></td></tr>
		<tr>
        <td align=left><?php echo $language['forums'];?></td>
        <td  align=right><?php echo $language['topics'];?></td>
		<td  align=right><?php echo $language['posts'];?></td>
		<td  align=left><?php echo $language['lpost'];?></td>
	</tr>
	<?php
        show_forums($forumid, true);
        end_table();
        // end_frame();
    }

    if (mysql_num_rows($topics_res) > 0) {

        ?><br/><table border=1 cellspacing=0 cellpadding=5 width=<?php echo $forum_width;
        ?>>
		<tr><td colspan="7" class=colhead align=left><?php echo safeChar($arr["forum_name"]);
        ?><?php echo $language['forums1'];?> : Forums</td></tr>
		<tr>
			<td  align=left colspan="3"><?php echo $language['topic'];?></td>
			<td><?php echo $language['reply'];?></td>
			<td><?php echo $language['view'];?></td>
			<td  align=left><?php echo $language['auth'];?></td>
			<td  align=left><?php echo $language['lpost'];?></td>
		</tr>
		<?php
        while ($topic_arr = mysql_fetch_assoc($topics_res)) {
            $topicid = (int)$topic_arr['id'];
            $topic_userid = (int)$topic_arr['userid'];
            $sticky = ($topic_arr['sticky'] == "yes");
            ($use_poll_mod ? $topicpoll = is_valid_id($topic_arr["pollid"]) : null);

            $tpages = floor($topic_arr['p_count'] / $postsperpage);

            if (($tpages * $postsperpage) != $topic_arr['p_count'])
                ++$tpages;

            if ($tpages > 1) {
                $topicpages = "&nbsp;(<img src='" . $pic_base_url . "multipage.gif' alt='Multiple pages' title='Multiple pages' />";
                $split = ($tpages > 10) ? true : false;
                $flag = false;

                for ($i = 1; $i <= $tpages; ++$i) {
                    if ($split && ($i > 4 && $i < ($tpages - 3))) {
                        if (!$flag) {
                            $topicpages .= '&nbsp;...';
                            $flag = true;
                        }
                        continue;
                    }
                    $topicpages .= "&nbsp;<a href=" . $_SERVER['PHP_SELF'] . "?action=viewtopic&amp;topicid=$topicid&amp;page=$i>$i</a>";
                }
                $topicpages .= ")";
            } else
                $topicpages = '';
            $post_icon = ($sticky ? "<img src=\"pic/sticky.gif\" title=\"Sticky topic\"/>" : ($topic_arr["posticon"] > 0 ? "<img src=\"pic/post_icons/icon" . $topic_arr["posticon"] . ".gif\" />" : "&nbsp;"));
            $lpusername = (is_valid_id($topic_arr['p_userid']) && !empty($topic_arr['u2_username']) ? "<a href='$DEFAULTBASEURL/userdetails.php?id=" . (int)$topic_arr['p_userid'] . "'><b>" . $topic_arr['u2_username'] . "</b></a>" : "unknown[$topic_userid]");
            $lpauthor = (is_valid_id($topic_arr['userid']) && !empty($topic_arr['username']) ? "<a href='$DEFAULTBASEURL/userdetails.php?id=$topic_userid'><b>" . $topic_arr['username'] . "</b></a>" : "unknown[$topic_userid]");
            $new = ($topic_arr["p_added"] > (get_date_time(gmtime() - $READPOST_EXPIRY))) ? ((int)$topic_arr['p_id'] > $topic_arr['lastpostread']) : 0;
            $topicpic = ($topic_arr['locked'] == "yes" ? ($new ? "lockednew" : "locked") : ($new ? "unlockednew" : "unlocked"));

            ?>
			<tr>
				<td align="center" nowrap="nowrap" style='padding-right: 5px'><img src='themes/<?=$ss_uri . "/forum/" . $topicpic;
            ?>.png' /></td>
				<td align="center" nowrap="nowrap" style='padding-right: 5px'><?=$post_icon?></td>
				<td align="left" width="100%"><a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=viewtopic&amp;topicid=<?php echo $topicid;
            ?>'><?php echo safeChar($topic_arr['subject']);
            ?></a><?php echo $topicpages;
            ?></td>
				<td align="center" nowrap="nowrap" ><?php echo max(0, $topic_arr['p_count'] - 1);
            ?></td>
				<td align="center" nowrap="nowrap" ><?php echo number_format($topic_arr['views']);
            ?></td>
				<td align="center" nowrap="nowrap"><?php echo $lpauthor;
            ?></td>
				<td align='left'  nowrap="nowrap"><?php echo $topic_arr["p_added"];
            ?><br />by&nbsp;<?php echo $lpusername;
            ?>
			</tr>
			<?php
        }

        end_table();
    } else {

        ?><p align=center><?php echo $language['ntf'];?></p><?php
    }

    echo $menu1 . $mlb . $menu2 . $mlb . $menu3;

    ?>
	<table class=main border=0 cellspacing=0 cellpadding=0 align=center>
	<tr style="vertical-align:middle">
		<td class=embedded><img src='themes/<?=$ss_uri . "/forum/"?>unlockednew.png' style='margin-right: 5px' /></td>
		<td class=embedded><?php echo $language['newpts'];?></td>
		<td class=embedded><img src='themes/<?=$ss_uri . "/forum/"?>locked.png' style='margin-left: 10px; margin-right: 5px' /></td>
		<td class=embedded><?php echo $language['lockedt'];?></td>
	</tr>
	</table>
	<?php
    $arr = get_forum_access_levels($forumid) or die();

    $maypost = ($CURUSER['class'] >= $arr["write"] && $CURUSER['class'] >= $arr["create"]);

    if (!$maypost) {

        ?><p><i><?php echo $language['err1'];?></i></p><?php
    }

    ?>
	<table border=0 class=main cellspacing=0 cellpadding=0 align=center>
	<tr>
	<td class=embedded><form method=get action='<?php echo $_SERVER['PHP_SELF'];
    ?>'><input type=hidden name=action value=viewunread /><input type=submit value='View unread' class=gobutton /></form></td>
	<?php
    if ($maypost) {

        ?>
		<td class=embedded><form method=get action='<?php echo $_SERVER['PHP_SELF'];
        ?>'><input type=hidden name=action value=newtopic /><input type=hidden name=forumid value=<?php echo $forumid;
        ?> /><input type=submit value='New topic' class=gobutton style='margin-left: 10px' /></form></td>
		<?php
    }

    ?></tr></table><?php

    insert_quick_jump_menu($forumid);

    end_main_frame();
    stdfoot();
    exit();
} else if ($action == 'viewunread') { // -------- Action: View unread posts
        if ((isset($_POST[$action . "_action"]) ? $_POST[$action . "_action"] : '') == 'clear') {
            $topic_ids = (isset($_POST['topic_id']) ? $_POST['topic_id'] : array());

            if (empty($topic_ids)) {
                header('Location: ' . $_SERVER['PHP_SELF'] . '?action=' . $action);
                exit();
            }

            foreach ($topic_ids as $topic_id)
            if (!is_valid_id($topic_id))
                stderr('Error...', 'Invalid ID!');

            catch_up($topic_ids);

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=' . $action);
            exit();
        } else {
            $added = sqlesc(get_date_time(gmtime() - $READPOST_EXPIRY));

            $res = sql_query('SELECT t.lastpost, r.lastpostread, f.minclassread ' . 'FROM topics AS t ' . 'LEFT JOIN posts AS p ON t.lastpost=p.id ' . 'LEFT JOIN readposts AS r ON r.userid=' . sqlesc((int)$CURUSER['id']) . ' AND r.topicid=t.id ' . 'LEFT JOIN forums AS f ON f.id=t.forumid ' . 'WHERE p.added < ' . $added) or sqlerr(__FILE__, __LINE__);
            $count = 0;
            while ($arr = mysql_fetch_assoc($res)) {
                if ($arr['lastpostread'] >= $arr['lastpost'] || $CURUSER['class'] < $arr['minclassread'])
                    continue;

                $count++;
            }
            mysql_free_result($res);

            if ($count > 0) {
                list($pagertop, $pagerbottom, $limit) = pager(25, $count, $_SERVER['PHP_SELF'] . '?action=' . $action . '&amp;');

                stdhead();
                if ($FORUMS_ONLINE == '0')
                    stdmsg('Warning', ''.$language['maint'].'');
                begin_main_frame();
                echo '<h1 align=center>Topics with unread posts</h1>';

                echo '<p>' . $pagertop . '</p>';

                ?>
			<script language="javascript" type="text/javascript">
				var checkflag = "false";

				function check(a)
				{
					if (checkflag == "false")
					{
						for(i=0; i < a.length; i++)
							a[i].checked = true;

						checkflag = "true";

						value = "Uncheck";
					}
					else
					{
						for(i=0; i < a.length; i++)
							a[i].checked = false;

						checkflag = "false";

						value = "Check";
					}

					return value + " All";
				};
			</script>

			<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?action=' . $action;
                ?>">
			<input type="hidden" name="<?php echo $action . '_action';
                ?>" value="clear" />
			<?php

                ?>
            <table cellpadding="5" width='<?php echo $forum_width;
                ?>'>
			<tr align="left">
				<td class="colhead" colspan="2"><?php echo $language['topic'];?></td>
				<td class="colhead" width="1%"><?php echo $language['clear'];?></td>
			</tr>
			<?php

                $res = sql_query('SELECT t.id, t.forumid, t.subject, t.lastpost, r.lastpostread, f.name, f.minclassread ' . 'FROM topics AS t ' . 'LEFT JOIN posts AS p ON t.lastpost=p.id ' . 'LEFT JOIN readposts AS r ON r.userid=' . sqlesc((int)$CURUSER['id']) . ' AND r.topicid=t.id ' . 'LEFT JOIN forums AS f ON f.id=t.forumid ' . 'WHERE p.added < ' . $added . ' ' . 'ORDER BY t.forumid ' . $limit) or sqlerr(__FILE__, __LINE__);

                while ($arr = mysql_fetch_assoc($res)) {
                    if ($arr['lastpostread'] >= $arr['lastpost'] || $CURUSER['class'] < $arr['minclassread'])
                        continue;

                    ?>
				<tr>
					<td align="center" width="1%">
						<img src='<?php echo $pic_base_url;
                    ?>unlockednew.gif' />
					</td>
					<td align="left">
						<a href='<?php echo $_SERVER['PHP_SELF'];
                    ?>?action=viewtopic&amp;topicid=<?php echo (int)$arr['id'];
                    ?>&amp;page=last#last'><?php echo safeChar($arr['subject']);
                    ?></a><br />in&nbsp;<font class="small"><a href='<?php echo $_SERVER['PHP_SELF'];
                    ?>?action=viewforum&amp;forumid=<?php echo (int)$arr['forumid'];
                    ?>'><?php echo safeChar($arr['name']);
                    ?></a></font>
					 </td>
					<td align="center">
						<input type="checkbox" name="topic_id[]" value="<?php echo (int)$arr['id'];
                    ?>" />
					</td>
				</tr>
				<?php
                }
                mysql_free_result($res);

                ?>
			<tr>
				<td align="center" colspan="3">
					<input type='button' value="Check All" onClick="this.value = check(form);">&nbsp;<input type="submit" value="Clear selected" />
				</td>
			</tr>
			<?php

                end_table();

                ?></form><?php

                echo '<p>' . $pagerbottom . '</p>';

                echo '<div align="center"><a href="' . $_SERVER['PHP_SELF'] . '?catchup">Mark all posts as read</a></div>';

                end_main_frame();
                stdfoot();
                die();
            } else
                stderr("Sorry...", "There are no unread posts.<br /><br />Click <a href=" . $_SERVER['PHP_SELF'] . "?action=getdaily>here</a> to get today's posts (last 24h).");
        }
    } else if ($action == "getdaily") {
        $res = sql_query('SELECT COUNT(p.id) AS post_count ' . 'FROM posts AS p ' . 'LEFT JOIN topics AS t ON t.id = p.topicid ' . 'LEFT JOIN forums AS f ON f.id = t.forumid ' . 'WHERE ADDDATE(p.added, INTERVAL 1 DAY) > ' . sqlesc(get_date_time()) . ' AND f.minclassread <= ' . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);

        $arr = mysql_fetch_assoc($res);
        mysql_free_result($res);

        $count = (int)$arr['post_count'];
        if (empty($count))
            stderr('Sorry', 'No posts in the last 24 hours.');

        stdhead('Today Posts (Last 24 Hours)');
        if ($FORUMS_ONLINE == '0')
            stdmsg('Warning', ''.$language['maint'].'');
        begin_main_frame();
        list($pagertop, $pagerbottom, $limit) = pager(20, $count, $_SERVER['PHP_SELF'] . '?action=' . $action . '&amp;');

        ?><h2 align="center"><?php echo $language['tdypost'];?></h2><?php
        echo "<p>$pagertop</p>";

        ?>
    <table cellpadding="5" width="<?php echo $forum_width;
        ?>">
    <tr class="colhead" align="center">
		<td width="100%" align="left"><?php echo $language['ttitle'];?></td>
		<td><?php echo $language['view'];?></td>
		<td><?php echo $language['auth'];?></td>
		<td><?php echo $language['pat'];?></td>
	</tr><?php

        $res = sql_query('SELECT p.id AS pid, p.topicid, p.userid AS userpost, p.added, t.id AS tid, t.subject, t.forumid, t.lastpost, t.views, f.name, f.minclassread, f.topiccount, u.username ' . 'FROM posts AS p ' . 'LEFT JOIN topics AS t ON t.id = p.topicid ' . 'LEFT JOIN forums AS f ON f.id = t.forumid ' . 'LEFT JOIN users AS u ON u.id = p.userid ' . 'LEFT JOIN users AS topicposter ON topicposter.id = t.userid ' . 'WHERE ADDDATE(p.added, INTERVAL 1 DAY) > ' . sqlesc(get_date_time()) . ' AND f.minclassread <= ' . $CURUSER['class'] . ' ' . 'ORDER BY p.added DESC ' . $limit) or sqlerr(__FILE__, __LINE__);

        while ($getdaily = mysql_fetch_assoc($res)) {
            $postid = (int)$getdaily['pid'];
            $posterid = (int)$getdaily['userpost'];

            ?><tr><?php
            ?><td align="left"><?php
            ?><a href="<?php echo $_SERVER['PHP_SELF'];
            ?>?action=viewtopic&amp;topicid=<?php echo $getdaily['tid'];
            ?>&amp;page=<?php echo $postid;
            ?>#p<?php echo $postid ?>"><?php echo safeChar($getdaily['subject']);
            ?></a><br /><?php
            ?><b>In</b>&nbsp;<a href="<?php echo $_SERVER['PHP_SELF'];
            ?>?action=viewforum&amp;forumid=<?php echo (int)$getdaily['forumid'];
            ?>"><?php echo safeChar($getdaily['name']);
            ?></a><?php
            ?></td><?php
            ?><td align="center"><?php echo number_format($getdaily['views']);
            ?></td><?php
            ?><td align="center"><?php
            if (!empty($getdaily['username'])) {

                ?><a href="<?php echo $DEFAULTBASEURL;
                ?>/userdetails.php?id=<?php echo $posterid;
                ?>"><?php echo safeChar($getdaily['username']);
                ?></a><?php
            } else {

                ?><b>unknown[<?php echo $posterid;
                ?>]</b><?php
            }

            ?></td><?php
            ?><td  nowrap="nowrap"><?php
            echo $getdaily['added'];

            ?><br /><?php
            echo get_elapsed_time(strtotime($getdaily['added']));

            ?></td><?php
            ?></tr><?php
        }
        mysql_free_result($res);

        end_table();

        echo "<p>$pagerbottom</p>";

        end_main_frame();
        stdfoot();
    } else if ($action == "search")
{
$maxresults = 50;
$cats =genreforumlist();

stdhead("Forum Search");
print("<h1>Forum Search</h1>\n");


$keywords = trim($_GET["keywords"]);

$author= trim($_GET['author']);

if ($author!=""){
$queryusers= "select id from users where username=".sqlesc($author)." limit 1";
$userquery = mysql_query($queryusers);
$num_res = mysql_num_rows($userquery);
if ($num_res<1){
print("<b>Author Does not exist, please recheck you typed his username correctly... Results following exclude username filtering</b><br><br>");
$userfilter="";
}
else {
$userfilterid= mysql_fetch_assoc($userquery);
$userfilterid= $userfilterid['id'];
$userfilter= " AND posts.userid=".$userfilterid;
}
}


$sort = (int) $_GET['sort'];
switch ($sort){
case 0:
$sortSel0 = "selected=\"selected\"";
$order_by="matchweight";
break;
case 1:
$sortSel1 = "selected=\"selected\"";
$order_by="forumid";
break;
case 2:
$sortSel2 = "selected=\"selected\"";
$order_by="subject";
break;
case 3:
$sortSel3 = "selected=\"selected\"";
$order_by="added";
break;
case 4:
$sortSel4 = "selected=\"selected\"";
$order_by="lastpost_time";
break;
case 5:
$sortSel5 = "selected=\"selected\"";
$order_by="views";
break;
case 6:
$sortSel6 = "selected=\"selected\"";
$order_by="replies";
break;
default:
$sortSel0 = "selected=\"selected\"";
$order_by="matchweight";
}


$sort_dir = (int) $_GET['sort_dir'];
if ($sort_dir==1){
$sortDirSel1 = "checked=\"checked\"";
$sort_order= 'ASC';
}
else{
$sortDirSel2 = "checked=\"checked\"";
$sort_order= 'DESC';
}

$numres = (int) $_GET["numres"];
switch ($numres){
case 0:
$numSel1 = "selected=\"selected\"";
$maxresults=25;
break;
case 1:
$numSel2 = "selected=\"selected\"";
$maxresults=50;
break;
case 2:
$numSel3 = "selected=\"selected\"";
$maxresults=100;
break;
case 3:
$numSel4 = "selected=\"selected\"";
$maxresults=200;
break;
case 4:
$numSel5 = "selected=\"selected\"";
$maxresults=300;
break;
default:
$numSel1 = "selected=\"selected\"";
$maxresults=25;
}


$search_time = (int) $_GET["search_time"];
switch ($search_time){
case 0:
$whenSel= "selected=\"selected\"";
$searchWhen="";
break;
case 1:
$whenSel1= "selected=\"selected\"";
$dt24 = gmtime() - 24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 2:
$whenSel2= "selected=\"selected\"";
$dt24 = gmtime() - 2*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 3:
$whenSel3= "selected=\"selected\"";
$dt24 = gmtime() - 3*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 4:
$whenSel4= "selected=\"selected\"";
$dt24 = gmtime() - 4* 24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 5:
$whenSel5= "selected=\"selected\"";
$dt24 = gmtime() - 5*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 6:
$whenSel6= "selected=\"selected\"";
$dt24 = gmtime() - 6 *24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;

case 7:
$whenSel7= "selected=\"selected\"";
$dt24 = gmtime() - 7*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 14:
$whenSel8= "selected=\"selected\"";
$dt24 = gmtime() - 14* 24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 30:
$whenSel9= "selected=\"selected\"";
$dt24 = gmtime() - 30*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
case 90:
$whenSel10= "selected=\"selected\"";
$dt24 = gmtime() - 90*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;

case 180:
$whenSel11= "selected=\"selected\"";
$dt24 = gmtime() - 180* 24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;

case 364:
$whenSel12= "selected=\"selected\"";
$dt24 = gmtime() - 364*24 * 60 * 60;
$searchWhen=" AND added>='".get_date_time($dt24)."'";
break;
default:
$whenSel= "selected=\"selected\"";
$searchWhen="";

}


$category = (int) $_GET["cat"];

if ($category) {
if (!is_valid_id($category)) stderr( ("Error"), ("Invalid category ID") );
$wherecatina[] = $category;
$addparam .= "cat=".$category."&amp;";
}
else {
$all = True;
foreach ($cats as $cat) {
$all &= $_GET["c".$cat['id']];
if ($_GET["c".$cat['id']]) {
$wherecatina[] = $cat['id'];
$addparam .= "c".$cat['id']."=1&amp;";
}
}
}

if ($all) {
$wherecatina = array();
$addparam = "";
}
if ($sort_dir==1) $sort_dir=0;
else $sort_dir=1;

$addparam.= "author=".safeChar($author)."&amp;";
$addparam.= "sort_dir=".$sort_dir."&amp;";
$addparam.= "search_time=$search_time&amp;";
$addparam.= "numres=$numres&amp;";
$addparam.= "keywords=".safeChar($keywords);

if (count($wherecatina) > 1) $wherecatin = implode(",",$wherecatina);
elseif (count($wherecatina) == 1) $wherea[] = "forumid = ".$wherecatina[0];

if (sizeof($wherea)!=0)
$where = implode(" AND ", $wherea);

if ($wherecatin) $where .= ($where ? " AND " : "") . "forumid IN(" . $wherecatin . ")";
if ($where !="") $where = " AND ".$where;

if (($keywords != "")||((($author!="")&&($userfilter!=""))||(($search_time<8)&&($search_time!=0))))
{
print("<p>Query: <b>" . safeChar($keywords) . "</b></p>\n");
// $maxresults = 50;
$kw = sqlesc($keywords);

if ($keywords =="")
$fields=" 'x'='x'";
else{
if (($_GET['body']==1)&&($_GET['topic']==1))
$fields= "(subject like ".sqlesc('%'.$keywords.'%')." OR MATCH (body) AGAINST ($kw) )";
else if ($_GET['topic']==1)
$fields="subject like ".sqlesc('%'.$keywords.'%');
else
$fields="MATCH (body) AGAINST ($kw)";
}

$query = "SELECT posts.id,body,topicid,posts.userid,added,forumid, subject, views,match(body) against ($kw) as matchweight FROM posts,topics WHERE $fields and posts.topicid=topics.id $where $searchWhen $userfilter order by $order_by $sort_order LIMIT " . ($maxresults + 1);
// print($query);
$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);
// search and display results...
$num = mysql_num_rows($res);
if ($num > $maxresults)
{
$num = $maxresults;
print("<p>Found more than $maxresults posts; displaying first $num.</p>\n");
}
else
print("<p>Found $num results</p>\n");

if ($num == 0)
print("<p><b>Sorry, nothing found!</b></p>");
else
{
print("<p><table border=0 cellspacing=0 cellpadding=5>\n");
print("<tr><td class=colhead><a href=forums.php?action=search&$addparam&sort=3>Post</a></td>".
"<td class=colhead align=left><a href=forums.php?action=search&$addparam&sort=2>Topic</a></td>".
"<td class=colhead align=left><a href=forums.php?action=search&$addparam&sort=1>Forum</a></td>".
"<td class=colhead><a href=forums.php?action=search&$addparam&sort=5>Views</a></td>".
"<td class=colhead><a href=forums.php?action=search&$addparam&sort=6>Replies</a></td>".
"<td class=colhead align=left>Posted by</td></tr>\n");
for ($i = 0; $i < $num; ++$i)
{
$post = mysql_fetch_assoc($res);
// $res2 = do_mysql_query("SELECT forumid, subject FROM topics WHERE id=$post[topicid]") or
// sqlerr(__FILE__, __LINE__);
// $topic = mysql_fetch_assoc($res2);
$res2 = mysql_query("SELECT name,minclassread FROM forums WHERE id=$post[forumid]") or
sqlerr(__FILE__, __LINE__);
$forum = mysql_fetch_assoc($res2);
if ($forum["name"] == "" || $forum["minclassread"] > $CURUSER["class"])
continue;
$res2 = mysql_query("SELECT username,id FROM users WHERE id=$post[userid]") or
sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_assoc($res2);
if ($user["username"] == "")
$user["username"] = "[$post[userid]]";
// print("<tr><td>$post[id]</td><td align=left><a href=?action=viewtopic&topicid=$post[topicid]&page=p$post[id]#$post[id]><b>" . safeChar($topic["subject"]) . "</b></a></td><td align=left><a href=?action=viewforum&forumid=$topic[forumid]><b>" . safeChar($forum["name"]) . "</b></a><td align=left><a href=userdetails.php?id=$post[userid]><b>$user[username]</b></a><br>at $post[added]</tr>\n");
++$klap;
$style = "style=\"border:none; ";
$style.=($klap%2==0)?"background: transparent;\"":"background: #eeeeee\"";
$body_print = "<a href=\"javascript: klappe_news('a$klap')\"><b>$post[id]</b>&nbsp;<img width=13 border=0 src=/pic/plus.gif></a>";
print("<tr><td $style>$body_print</td><td align=left $style><a href=?action=viewtopic&highlight=".
urlencode(safeChar($keywords)) . "&topicid=$post[topicid]&page=p$post[id]#$post[id]><b>".
safeChar($post["subject"]) . "</b></a></td><td align=left $style><a href=?action=viewforum&forumid=$post[forumid]><b>". safeChar($forum["name"]).
"</b></a><td align=left $style>$post[views]</td><td align=left $style>$post[replies]</td><td align=left $style><b><a href=userdetails.php?id=$user[id]>$user[username]</a></b><br>at $post[added]</tr>".
"<tr><td colspan=6 $style><div id=\"ka$klap\" style=\"display: none;\">$post[body]</div></td></tr> \n");

}
print("</table></p>\n");
print("<p><b>Search again</b></p>\n");
}
}

$chtopic = ($_GET['topic']==1 ? "checked " : "");
$chbody = ($_GET['body']==1? "checked " :"");

print("<form method=get action=?>\n");
print("<input type=hidden name=action value=search>\n");
print("<table border=0 cellspacing=0 cellpadding=5>\n");

$i = 0;
foreach ($cats as $cat)
{
$catsperrow = 4;
print(($i && $i % $catsperrow == 0) ? "</tr><tr>" : "");
if (sizeof($wherecatina)!=0)
$catCheck= (in_array($cat[id],$wherecatina) ? "checked " : "");
print("<td nowrap class=bottom style=\"vertical-align:baseline;border:none; padding-bottom: 0px;padding-left: 7px;text-align:left\"><input style=\"vertical-align:middle;padding:0px;margin:0px;margin-right:3px;\" name=c$cat[id] type=\"checkbox\" " .$catCheck . "value=1><a style=\"vertical-align:middle;padding:0px;margin:0px;\" class=catlink href=".$GLOBALS['DEFAULTBASEURL']."/forums.php?action=viewforum&forumid=$cat[id]>" . safeChar($cat['name']) . "</a></td>\n");
$i++;
}

print("</table><br><br><table border=1 cellspacing=0 cellpadding=5><tr><td class=rowhead>Search Term</td><td align=left><input type=text size=40 name=keywords value=\"".safeChar($keywords)."\"><br>\n" .
"<font class=small size=-1>Enter one or more words to search for.</font></td></tr>\n");

// Search in author
print("<tr><td class=rowhead>Author:</td><td align=left><input type=text size=15 name=author value=\"".safeChar($author)."\"> Only display posts from this author");

print("<tr><td colspan=2><table border=0 cellspacing=0 cellpadding=5>");

// When to search in
print("<tr><td class=rowhead style=\"border:none\">Search In Last:</td><td style=\"border:none\"> <select name=\"search_time\"><option value=\"0\" $whenSel>All Posts</option><option value=\"1\" $whenSel1>1 Day</option><option value=\"2\" $whenSel2>2 Days</option><option value=\"3\" $whenSel3>3 Days</option><option value=\"4\" $whenSel4>4 Days</option><option value=\"5\" $whenSel5>5 Days</option><option value=\"6\" $whenSel6>6 Days</option><option value=\"7\" $whenSel7>1 Week</option><option value=\"14\" $whenSel8>2 Weeks</option><option value=\"30\" $whenSel9>1 Month</option><option value=\"90\" $whenSel10>3 Months</option><option value=\"180\" $whenSel11>6 Months</option><option value=\"364\" $whenSel12>1 Year</option></select></td></tr>");

// Where to search in
print("<tr><td class=rowhead style=\"border:none\">Search In:</td>".
"<td style=\"border:none\"><table border=0 cellspacing=0 cellpadding-5><tr>".
"<td style=\"border:none\"><input style=\"padding:0px;margin:0px;margin-right:3px;\" name=topic type=\"checkbox\" value=1 $chtopic> Topic Title</td></tr>".
"<tr><td style=\"border:none\"><input style=\"padding:0px;margin:0px;margin-right:3px;\" name=body type=\"checkbox\" value=1 $chbody> Post Body (default if both unchecked)</td></tr>".
"</table></td></tr>");

//Sorting options
print("<tr><td class=rowhead style=\"border:none\">Sort By:</td><td style=\"border:none\">");
print("<select name=\"sort\"><option value=\"0\" $sortSel0>Relevancy</option><option value=\"1\" $sortSel1>Forum Name</option><option value=\"2\" $sortSel2>Topic Name</option><option value=\"3\" $sortSel3>Post Time</option><option value=\"4\" $sortSel4>Last Post Time</option><option value=\"5\" $sortSel5>Topic Views</option><option value=\"6\" $sortSel6>Topic Replies</option></select>&nbsp;<input type=\"radio\" name=\"sort_dir\" value=\"1\" $sortDirSel1/>Ascending&nbsp;<input type=\"radio\" name=\"sort_dir\" value=\"0\" $sortDirSel2/> Descending</select></td></tr>");

// Number of results
print("<tr><td class=rowhead style=\"border:none\">Return First:</td><td style=\"border:none\">");
print("<select name=\"numres\"><option value=\"0\" $numSel1>25</option><option value=\"1\" $numSel2>50</option><option value=\"2\" $numSel3>100</option><option value=\"3\" $numSel4>200</option><option value=\"4\" $numSel5>300</option></select> found results</td></tr>");

//Display posts summary options/topics

print ("</table></td></tr>");

print("<tr><td colspan=2 align=right><input type=submit value='Search' class=btn>&nbsp;</td></tr>\n");
print("</table>\n</form>\n");
stdfoot();
die;


    } else if ($action == 'forumview') {
        $ovfid = (isset($_GET["forid"]) ? (int)$_GET["forid"] : 0);
        if (!is_valid_id($ovfid))
            stderr('Error', 'Invalid ID!');

        $res = sql_query("SELECT name FROM overforums WHERE id = $ovfid") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($res) or stderr('Sorry', 'No forums with that ID!');

        sql_query("UPDATE users SET forum_access = " . sqlesc(get_date_time()) . " WHERE id = {$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);

        stdhead("Forums");
        if ($FORUMS_ONLINE == '0')
            stdmsg('Warning', ''.$language['maint'].'');
        begin_main_frame();

        ?>
	<h1 align="center"><b><a href='<?php echo $_SERVER['PHP_SELF'];
        ?>'>Forums</a></b> -> <?php echo safeChar($arr["name"]);
        ?></h1>

	<table border=1 cellspacing=0 cellpadding=5 width='<?php echo $forum_width;
        ?>'>
		<tr>
        	<td class=colhead align=left>Forums</td>
            <td class=colhead align=right>Topics</td>
		<td class=colhead align=right>Posts</td>
		<td class=colhead align=left>Last post</td>
	</tr>
	<?php

        show_forums($ovfid);

        end_table();

        end_main_frame();
        stdfoot();
        exit();
    } else { // -------- Default action: View forums
            if (isset($_GET["catchup"])) {
                catch_up();

                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }

            sql_query("UPDATE users SET forum_access = '" . get_date_time() . "' WHERE id={$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
            $sub_forums = mysql_query(" SELECT f.id, f2.name, f2.id AS subid,f2.postcount,f2.topiccount, p.added, p.userid, p.id AS pid, u.username, t.subject,t.id as tid,r.lastpostread,t.lastpost
									FROM forums AS f
									LEFT JOIN forums AS f2 ON f2.place = f.id AND f2.minclassread<" . sqlesc($CURUSER["class"]) . "
									LEFT JOIN posts AS p ON p.id = (SELECT MAX(lastpost) FROM topics WHERE forumid = f2.id )
									LEFT JOIN users AS u ON u.id = p.userid
									LEFT JOIN topics AS t ON t.id = p.topicid
									LEFT JOIN readposts AS r ON r.userid =" . sqlesc($CURUSER["id"]) . " AND r.topicid = p.topicid
									ORDER BY t.lastpost ASC, f2.name , f.id ASC
									");
            while ($a = mysql_fetch_assoc($sub_forums)) {
                if ($a["subid"] == 0)
                    $forums[$a["id"]] = false;
                else {
                    $forums[$a["id"]]["lastpost"] = array("postid" => $a["pid"], "userid" => $a["userid"], "user" => $a["username"], "topic" => $a["subject"], "topic" => $a["tid"], "tname" => $a["subject"], "added" => $a["added"]);
                    $forums[$a["id"]]["count"][] = array("posts" => $a["postcount"], "topics" => $a["topiccount"]);
                    $forums[$a["id"]]["topics"][] = array ("id" => $a["subid"], "name" => $a["name"], "new" => ($a["lastpost"]) != $a["lastpostread"] ? 1 : 0);
                }
            }
            stdhead("Forums");

            if ($FORUMS_ONLINE == '0')
                stdmsg('Warning', ''.$language['maint'].'');
            begin_main_frame();

            ?><h1 align="center"><b><?php echo $SITENAME;
            ?> - Forum</b></h1>
	<br />
	<table border=1 cellspacing=0 cellpadding=5 width='<?php echo $forum_width;
            ?>'><?php

            $ovf_res = sql_query("SELECT id, name, minclassview FROM overforums ORDER BY sort ASC") or sqlerr(__FILE__, __LINE__);
            while ($ovf_arr = mysql_fetch_assoc($ovf_res)) {
                if ($CURUSER['class'] < $ovf_arr["minclassview"])
                    continue;

                $ovfid = (int)$ovf_arr["id"];
                $ovfname = $ovf_arr["name"];

                ?><tr>
			<td align='left' class='colhead' width="100%">
				<a href='<?php echo $_SERVER['PHP_SELF'];
                ?>?action=forumview&amp;forid=<?php echo $ovfid;
                ?>'><b><font color='#3333FF'><?php echo safeChar($ovfname);
                ?></font></b></a>
			</td>
			<td align='right' class='colhead'><font color='#3333FF'><b>Topics</b></font></td>
			<td align='right' class='colhead'><font color='#3333FF'><b>Posts</b></font></td>
			<td align='left' class='colhead'><font color='#3333FF'><b>Last post</b></font></td>
		</tr><?php

                show_forums($ovfid, false, $forums,true);
            }
            end_table();

            if ($use_forum_stats_mod)
                forum_stats();

            ?><p align='center'>
	<a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=search'><b><?php echo $language['search'];?></b></a> |
	<a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=viewunread'><b><?php echo $language['newpts'];?></b></a> |
	<a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?action=getdaily'><b><?php echo $language['tdypost'];?></b></a> |
	<a href='<?php echo $_SERVER['PHP_SELF'];
            ?>?catchup'><b><?php echo $language['maar'];?></b></a><?php
            echo ($CURUSER['class'] == MAX_CLASS ? " | <a href='$DEFAULTBASEURL/forummanage.php#add'><b>Forum-Manager</b></a>":"");

            ?></p><?php

            end_main_frame();
            stdfoot();
        }

        ?>