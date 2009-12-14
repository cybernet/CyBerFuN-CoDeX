<?php
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
dbconn();
$page_find = 'staff';
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
parked();
stdhead("Staff");
// Get current datetime
$dt = gmtime() - 60;
$dt = sqlesc(get_date_time($dt));
// Search User Database for Moderators and above and display in alphabetical order
$res = mysql_query("SELECT * FROM users WHERE class>=" . UC_UPLOADER . " AND status='confirmed' ORDER BY username") or sqlerr();

while ($arr = mysql_fetch_assoc($res)) {
    $staff_table[$arr['class']] = $staff_table[$arr['class']] . "<td class=staffembedded><a class=altlink href=userdetails.php?id=" . $arr['id'] . ">" . $arr['username'] . "</a></td><td class=staffembedded> " . ("'" . $arr['last_access'] . "'" > $dt?"<img src=" . $pic_base_url . "user_online.gif border=0 alt=\"online\" />":"<img src=" . $pic_base_url . "user_offline.gif border=0 alt=\"offline\" />") . "</td>" . "<td class=staffembedded><a href=sendmessage.php?receiver=" . $arr['id'] . ">" . "<img src=" . $pic_base_url . "pm.gif border=0 /></a></td>" . " ";
    // Show 3 staff per row, separated by an empty column
    ++ $col[$arr['class']];
    if ($col[$arr['class']] <= 2)
        $staff_table[$arr['class']] = $staff_table[$arr['class']] . "<td class=staffembedded>&nbsp;</td>";
    else {
        $staff_table[$arr['class']] = $staff_table[$arr['class']] . "</tr><tr style=\"height:15px\">";
        $col[$arr['class']] = 0;
    }
}
begin_main_frame();

?>
<table width=725 cellspacing=0 align="center">
<tr>
<td class=staffembedded colspan=11><?php echo $language['asup'];?></td></tr>
<!-- Define table column widths -->
<tr>
<td class=staffembedded width="105">&nbsp;</td>
<td class=staffembedded width="25">&nbsp;</td>
<td class=staffembedded width="35">&nbsp;</td>
<td class=staffembedded width="85">&nbsp;</td>
<td class=staffembedded width="105">&nbsp;</td>
<td class=staffembedded width="25">&nbsp;</td>
<td class=staffembedded width="35">&nbsp;</td>
<td class=staffembedded width="85">&nbsp;</td>
<td class=staffembedded width="105">&nbsp;</td>
<td class=staffembedded width="25">&nbsp;</td>
<td class=staffembedded width="35">&nbsp;</td>
</tr>
<tr><td class=staffembedded colspan=10>&nbsp;</td></tr>
<tr><td class=staffembedded colspan=10><b><?php echo $language['coder'];?></b></td></tr>
<tr><td class=staffembedded colspan=10><hr style="color:#A83838" size=1 /></td></tr>
<tr style="height:15px">
<?=$staff_table[UC_CODER]?></tr>

<tr><td class=staffembedded colspan=10>&nbsp;</td></tr>
<tr><td class=staffembedded colspan=10><b>Designer</b></td></tr>
<tr><td class=staffembedded colspan=10><hr style="color:#A83838" size=1 /></td></tr>
<tr style="height:15px">
<?=$staff_table[UC_DESIGNER]?></tr>

<tr><td class=staffembedded colspan=10>&nbsp;</td></tr>
<tr><td class=staffembedded colspan=10><b><?php echo $language['sysop'];?></b></td></tr>
<tr><td class=staffembedded colspan=10><hr style="color:#A83838" size=1 /></td></tr>
<tr style="height:15px">
<?=$staff_table[UC_SYSOP]?></tr>

<tr><td class=staffembedded colspan=10>&nbsp;</td></tr>
<tr><td class=staffembedded colspan=10><b><?php echo $language['admin'];?></b></td></tr>
<tr><td class=staffembedded colspan=10><hr style="color:#A83838" size=1 /></td></tr>
<tr style="height:15px">
<?=$staff_table[UC_ADMINISTRATOR]?></tr>


<tr><td class=staffembedded colspan=10>&nbsp;</td></tr>
<tr><td class=staffembedded colspan=10><b><?php echo $language['mods'];?></b></td></tr>
<tr><td class=staffembedded colspan=10><hr style="color:#A83838" size=1 /></td></tr>
<tr style="height:15px">
<?=$staff_table[UC_MODERATOR]?></tr>

<tr><td class=staffembedded colspan=10>&nbsp;</td></tr>
<tr><td class=staffembedded colspan=10><b><?php echo $language['uppers'];?></b></td></tr>
<tr><td class=staffembedded colspan=10><hr style="color:#A83838" size=1 /></td></tr>
<tr style="height:15px">
<?=$staff_table[UC_UPLOADER]?></tr>

</table>
<?php
end_main_frame();
print("<br/>");
begin_main_frame();
$dt = gmtime() - 180;
$dt = sqlesc(get_date_time($dt));
// LIST ALL FIRSTLINE SUPPORTERS
// Search User Database for Firstline Support and display in alphabetical order
$res = sql_query("SELECT id,username, last_access,supportfor,country FROM users WHERE support='yes' AND status='confirmed' ORDER BY username LIMIT 10") or sqlerr();
while ($arr = mysql_fetch_assoc($res)) {
    require_once("include/cache/countries.php");
    foreach ($countries as $c)
    if ($arr["country"] == $c["id"]) {
        $flag = $c["flagpic"];
        $cname = $c["name"];
    }

    $firstline .= "<tr style=\"height:15px\"><td class=embedded><a class=altlink href=userdetails.php?id=" . $arr['id'] . ">" . $arr['username'] . "</a></td>
<td class=embedded> " . ("'" . $arr['last_access'] . "'" > $dt?"<img src=" . $pic_base_url . "user_online.gif border=0 alt=\"online\" />":"<img src=" . $pic_base_url . "user_offline.gif border=0 alt=\"offline\" />") . "</td>" . "<td class=embedded><a href=sendmessage.php?receiver=" . $arr['id'] . ">" . "<img src=" . $pic_base_url . "pm.gif border=0 /></a></td>" . "<td class=embedded><img src=" . $pic_base_url . "flag/$flag alt=\"" . $cname . "\" title=\"" . $cname . "\" border=0 /></td>" . "<td class=embedded>" . $arr['supportfor'] . "</td></tr>\n";
}

?>

<table width="100%" cellspacing=0>
<tr>
<td class=embedded colspan=11><?php echo $language['asup1'];?><br/><br/></td></tr>
<!-- Define table column widths -->
<tr>
<td class=embedded width="30"><b><?php echo $language['uname'];?></b></td>
<td class=embedded width="5"><b><?php echo $language['activ1'];?></b></td>
<td class=embedded width="5"><b><?php echo $language['contact'];?></b></td>
<td class=embedded width="85"><b><?php echo $language['language'];?></b></td>
<td class=embedded width="200"><b><?php echo $language['supfor'];?></b></td>
</tr>
<tr><td class=embedded colspan=11><hr style="color:#999999" size=1 /></td></tr>
<?=$firstline?>
</table>
<?php
end_main_frame();

stdfoot();

?>