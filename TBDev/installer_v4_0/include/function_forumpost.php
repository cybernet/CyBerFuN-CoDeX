<?php
if (!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
function latestforumposts()
{
    global $pic_base_url, $CURUSER, $config, $php_file, $page_find, $lang_off, $language;
    // / FIRST WE MAKE THE HEADER (NON-LOOPED) ///
    ?><table align="center" width="760" cellspacing=0 cellpadding=8><tr><td align="left" class=colhead><b><?php echo $language['topic'];?></b></td><!--<td align="center" class=colhead><b>Author</b></td><td align="center" class=colhead><b>Replies</b></td>--><td align="center" class=colhead><b><?php echo $language['view'];?></b></td><td align="center" class=colhead><b><?php echo $language['post'];?></b></td><!--<td align="center" class=colhead><b>Posted At</b></td>-->
</tr>
<?php
    $page = 1;
    $num = 0;
    // / HERE GOES THE QUERY TO RETRIEVE DATA FROM THE DATABASE AND WE START LOOPING ///
    $topicres = sql_query("SELECT t.id, t.userid, t.subject, t.locked, t.forumid, t.pollid, t.lastpost, t.sticky, t.views, t.forumid, f.minclassread, f.name " . ", (SELECT COUNT(id) FROM posts WHERE topicid=t.id) AS p_count " . ", p.userid AS puserid, p.added " . ", u.id AS uid, u.username " . ", u2.username AS u2_username " . "FROM topics AS t " . "LEFT JOIN forums AS f ON f.id = t.forumid " . "LEFT JOIN posts AS p ON p.id=(SELECT MAX(id) FROM posts WHERE topicid = t.id) " . "LEFT JOIN users AS u ON u.id=p.userid " . "LEFT JOIN users AS u2 ON u2.id=t.userid " . "WHERE t.locked = 'no' AND f.minclassread <= " . $CURUSER['class'] . " " . "ORDER BY t.lastpost DESC LIMIT 11") or sqlerr(__FILE__, __LINE__);
    while ($topicarr = mysql_fetch_assoc($topicres)) {
        $topicid = $topicarr["id"];
        $topic_userid = $topicarr["userid"];
        $perpage = $CURUSER["postsperpage"];;
        if (!$perpage)
            $perpage = 24;
        $posts = $topicarr["p_count"];
        $replies = max(0, $posts - 1);
        $first = ($page * $perpage) - $perpage + 1;
        $last = $first + $perpage - 1;
        if ($last > $num)
            $last = $num;
        $pages = ceil($posts / $perpage);
        // $menu = "\n";
        $menu = "";
        for ($i = 1; $i <= $pages; $i++) {
            if ($i == 1 && $i != $pages) {
                $menu .= "[ ";
            }
            if ($pages > 1) {
                $menu .= "<a href=/forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=$i>$i</a>\n";
            }
            if ($i < $pages) {
                $menu .= "|\n";
            }
            if ($i == $pages && $i > 1) {
                $menu .= "]";
            }
        }
        $added = $topicarr["added"] . '<br /><font class=small>(' . get_elapsed_time(sql_timestamp_to_unix_timestamp($topicarr["added"])) . ' ago)</font>';
        $username = (is_valid_id($topicarr['uid']) ? "<a href=/userdetails.php?id=" . $topicarr["puserid"] . "><b>" . $topicarr['username'] . "</b></a>" : "<i>Unknown[$topic_userid]</i>");
        $author = (!empty($topicarr['u2_username']) ? "<a href=/userdetails.php?id=$topic_userid><b>" . $topicarr['u2_username'] . "</b></a>" : "<i>Unknown[$topic_userid]</i>");
        $staffimg = ($topicarr["minclassread"] > 0 ? "<img src=" . $pic_base_url . "staff.png border=0 />" : "");
        $lockimg = ($topicarr["locked"] == 'yes' ? "<img src=" . $pic_base_url . "lockednew.gif border=0 />" : "");
        $pollimg = (is_valid_id($topicarr["pollid"]) ? "<img src=\"" . $pic_base_url . "poll.gif\" alt=\"Poll:\" width=\"12\" height=\"14\" />&nbsp;&nbsp;&nbsp;" : "");
        $stickyimg = ($topicarr["sticky"] == 'yes' ? "<img src=\"" . $pic_base_url . "sticky.gif\" alt=\"Sticky:\" width=\"12px\" height=\"14px\" />&nbsp;&nbsp;&nbsp;" : "");
        $subject = $pollimg . $stickyimg . "<a href=\"forums.php?action=viewtopic&amp;topicid=$topicid&amp;page=last#" . $topicarr["lastpost"] . "\"><b>" . encodehtml($topicarr["subject"]) . "</b></a>$lockimg&nbsp;&nbsp;&nbsp;$staffimg&nbsp;&nbsp;&nbsp;$menu<br /><font class=small> in <i><a href=\"/forums.php?action=viewforum&amp;forumid=" . $topicarr['forumid'] . "\">" . $topicarr['name'] . "</a></i> by " . $author . "&nbsp;&nbsp;" . get_elapsed_time(sql_timestamp_to_unix_timestamp($topicarr["added"])) . " ago </font>";

        ?><tr><td><?=$subject;
        ?></td><td align="center"><?=number_format($topicarr["views"]);
        ?></td><td align="center"><?=$username;
        ?></td></tr><?php
    }

    ?></table>
<?php
}

?>