<?php
require_once("include/bittorrent.php");
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
parked();

if (get_user_class() < UC_ADMINISTRATOR)
    hacker_dork("Ratio Cheaters - Nosey Cunt !");

stdhead("Cheaters");

begin_main_frame();
begin_frame("Cheating Users:", true);
// Will: added this for page links
$res = sql_query("SELECT COUNT(*) FROM cheaters $limit") or sqlerr();
$row = mysql_fetch_array($res);
$count = $row[0];

list($pagertop, $pagerbottom, $limit) = pager(30, $count, "cheaters.php?");
echo("<table border=0 width=\"100%\" cellspacing=0 cellpadding=0><tr><td align=right>$pagertop</td></tr></table><br />");
// end

?>
<script type="text/javascript" src="java_klappe.js"></script>

<form action="takecheaters.php" method=post>

<script language="JavaScript" type="text/javascript">
<!-- Begin
var checkflag = "false";
function check(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
return "Uncheck All Disable"; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
return "Check All Disable"; }
}

function check2(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
return "Uncheck All Remove"; }
else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
return "Check All Remove"; }
}
// End -->
</script>

<?php

echo("<table width=\"100%\">\n");

echo("<td class=\"tableb\" width=10 align=center valign=middle>#</td>
<td class=\"tableb\">Username</td>
<td class=\"tableb\" width=10 align=center valign=middle>D</td>
<td class=\"tableb\" width=10 align=center valign=middle>R</td>");

$res = sql_query("SELECT * FROM cheaters ORDER BY added DESC $limit") or sqlerr(__FILE__, __LINE__);
while ($arr = mysql_fetch_assoc($res)) {
    $rrr = sql_query("SELECT id, username, class, downloaded, uploaded FROM users WHERE id = $arr[userid]");
    $aaa = mysql_fetch_assoc($rrr);

    $rrr2 = sql_query("SELECT name FROM torrents WHERE id = $arr[torrentid]");
    $aaa2 = mysql_fetch_assoc($rrr2);

    if ($aaa["downloaded"] > 0) {
        $ratio = number_format($aaa["uploaded"] / $aaa["downloaded"], 3);
    } else {
        $ratio = "---";
    }
    $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";

    $uppd = prefixed($arr["upthis"]);

    $cheater = "<b><a href=userdetails.php?id=$aaa[id]>$aaa[username]</a></b> has been caught cheating!<br /><br />Uploaded: <b>$uppd</b><br />Speed: <b>$arr[rate]/s</b><br />Within: <b>$arr[timediff] seconds</b><br />Using Client: <b>$arr[client]</b><br />IP Address: <b>$arr[userip]</b>";

    echo("<tr><td class=\"tableb\" width=\"10\" align=center>$arr[id]</td>");
    echo("<td class=\"tableb\" align=left><a href=\"javascript: klappe_news('a$arr[id]')\">$aaa[username]</a> - Added: $arr[added]");
    echo("<div id=\"ka$arr[id]\" style=\"display: none;\"><font color=\"red\">$cheater</font></div></td>");
    echo("<td class=\"tableb\" valign=\"top\" width=10><input type=\"checkbox\" name=\"desact[]\" value=\"" . $aaa["id"] . "\"/></td>");
    echo("<td class=\"tableb\" valign=\"top\" width=10><input type=\"checkbox\" name=\"remove[]\" value=\"" . $arr["id"] . "\"/></td></tr>");
}
if (get_user_class() >= UC_MODERATOR) {

    ?>
<tr>
<td class="tableb" colspan="4" align="right">
<input type="button" value="Check All Disable" onclick="this.value=check(this.form.elements['desact[]'])"/> <input type="button" value="Check All Remove" onclick="this.value=check(this.form.elements['remove[]'])"/> <input type="hidden" name="nowarned" value="nowarned"><input type="submit" name="submit" value="Apply Changes">
</td>
</tr>
</table></form>
<?php
}
// will: added this for page links
echo("<br /><table border=0 width=\"100%\" cellspacing=0 cellpadding=0><tr><td align=right>$pagertop</td></tr></table>");
// end
end_frame();
end_main_frame();
stdfoot();
die;

?>