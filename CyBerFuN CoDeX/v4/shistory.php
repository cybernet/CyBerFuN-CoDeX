<?php
// ////////shoutboxhistory by Bigjoos///////////////////////////
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_SYSOP)
    hacker_dork("Shout History - Nosey Cunt !");
stdhead("Admin Shout History Check");
$count1 = number_format(get_row_count("shoutbox"));
print("<h2 align=center>Full Shout History</h2>");
print("<center><font class=small>We currently have " . safeChar($count1) . " shouts on history</font></center>");
begin_main_frame();
$res1 = mysql_query("SELECT COUNT(*) FROM shoutbox $limit") or sqlerr();
$row1 = mysql_fetch_array($res1);
$count = $row1[0];
$shoutsperpage = 30;
list($pagertop, $pagerbottom, $limit) = pager($shoutsperpage, $count, "shistory.php?");
print("$pagertop");
$res = sql_query("SELECT * FROM shoutbox ORDER BY date DESC $limit") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    print("\n");
else {
    print("<table border=0 cellspacing=0 cellpadding=2 width='100%' align='left' class='small'>\n");

    $i = 0;
    while ($arr = mysql_fetch_assoc($res)) {
        $res2 = sql_query("SELECT username,class,donor,warned,downloadpos,chatpost,forumpost,uploadpos,parked FROM users WHERE id=".unsafeChar($arr[userid])."") or sqlerr(__FILE__, __LINE__);
        $arr2 = mysql_fetch_array($res2);
        $resowner = sql_query("SELECT id, username, class FROM users WHERE id=".unsafeChar($arr[userid])."") or sqlerr(__FILE__, __LINE__);
        $rowowner = mysql_fetch_array($resowner);

        if ($rowowner["class"] == "7")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "6")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "5")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "4")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "3")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "2")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "1")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";
        if ($rowowner["class"] == "0")
            $usercolor = " <font color='#" . get_user_class_color($rowowner['class']) . "'>" . safechar($rowowner['username']) . "</font>";

        $pm = "<span class='date'>[<a target=_blank href=sendmessage.php?receiver=$arr[userid]>pm</a>]</span>\n";
        if ($i % 2 == 0)
            $bg = 'bgcolor=#555555';
        else
            $bg = 'bgcolor=#777777';
        print("<tr $bg><td><font color=white>[<span class='date'>" . strftime("%d.%m %H:%M", $arr["date"]) . "]</font></span>\n$del $edit $pm <a href='userdetails.php?id=" . $arr["userid"] . "' target='_blank'>$usercolor</a>\n" .
            ($arr2["donor"] == "yes" ? "<img src=pic/star.gif alt='DONOR'>\n" : "") .
            ($arr2["warned"] == "yes" ? "<img src=" . "pic/warned.gif alt='Warned'>\n" : "") .
            ($arr2["chatpost"] == "no" ? "<img src=pic/chatpos.gif alt='No Chat'>\n" : "") .
            ($arr2["downloadpos"] == "no" ? "<img src=pic/downloadpos.gif alt='No Downloads'>\n" : "") .
            ($arr2["forumpost"] == "no" ? "<img src=pic/forumpost.gif alt='No Posting'>\n" : "") .
            ($arr2["uploadpos"] == "no" ? "<img src=pic/uploadpos.gif alt='No upload'>\n" : "") .
            ($arr2["parked"] == "yes" ? "<img src=pic/parked.gif alt='Account Parked'>\n" : "") . " " . format_comment($arr["text"]) . "\n</td></tr>\n");
        $i++;
    }
    print("</table><br />");
}
print("$pagerbottom");
end_main_frame();
stdfoot();

?>
