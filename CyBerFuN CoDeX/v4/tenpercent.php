<?php
include_once("include/bittorrent.php");
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

$uploaded = $CURUSER["uploaded"];
$downloaded = $CURUSER["downloaded"];
$newuploaded = ($uploaded * 1.1);

if ($downloaded > 0) {
$ratio = number_format($uploaded / $downloaded, 3);
$newratio = number_format($newuploaded / $downloaded, 3);
$ratiochange = number_format(($newuploaded / $downloaded) - ($uploaded / $downloaded), 3);
} elseif ($uploaded > 0)
$ratio = $newratio = $ratiochange = "Inf.";
else
$ratio = $newratio = $ratiochange = "---";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

if ($CURUSER["tenpercent"] == "yes")
stderr("Used", "It appears that you have already used your 10% addition.");

$sure = $_POST["sure"];

if (!$sure)
stderr("Are you sure?", "It appears that you are not yet sure whether you want to add 10% to your upload or not. Once you are sure you can <a href=tenpercent.php>return</a> to the 10% page.");

$time = date("F j Y");
$subject = "10% Addition";
$msg = "Today, $time, you have increased your total upload amount by 10% from ".prefixed($uploaded)." to ".prefixed($newuploaded).", which brings your ratio to ".$newratio.".";

$res = sql_query("UPDATE users SET uploaded = uploaded * 1.1, tenpercent = 'yes' WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
$res1 = sql_query("INSERT INTO messages (sender, poster, receiver, subject, msg, added) VALUES (0, 0, $CURUSER[id], ".sqlesc($subject).", ".sqlesc($msg).", '".get_date_time()."')") or sqlerr(__FILE__, __LINE__);

if (!$res)
stderr("Error", "It appears that something went wrong while trying to add 10% to your upload amount.");
else
stderr("10% Added", "Your total upload amount has been increased by 10% from <b>".prefixed($uploaded)."</b> to <b>".prefixed($newuploaded)."</b>, which brings your ratio to <b>$newratio</b>.");
}


stdhead("");
echo("<h1>10%</h1>\n");

if ($CURUSER["tenpercent"] == "yes")
echo("<h2>It appears that you have already used your 10% addition</h2>\n");

echo("<p><table width=700 border=0 cellspacing=0 cellpadding=5><tr><td\n");
echo("<table width=700 border=0 cellspacing=0 cellpadding=10><tr><td style='padding-bottom: 0px'>\n");
echo("<p><b>How it works:</b></p>");
echo("<p class=sub>From this page you can <b>add 10%</b> of your current upload amount to your upload amount bringing it it to <b>110%</b> of its current amount. More details about how this would work out for you can be found in the tables below.</p>");
echo("<br><p><b>However, there are some things you should know first:</b></p>");
echo("<li>This can only be done <b>once</b>, so chose your moment wisely.");
echo("<li>The staff will <b>not</b> reset your 10% addition for any reason.");
echo("</td></tr></table>\n");
echo("</td></tr></table></p>\n");
echo("<p><table width=630 class=main align=center border=0 cellspacing=0 cellpadding=5>\n");
echo("<tr><td class=normalrowhead>Current&nbsp;upload&nbsp;amount:</td><td class=normal>".str_replace(" ", "&nbsp;", prefixed($uploaded))."</td><td class=embedded width=5%></td><td class=normalrowhead>Increase:</td><td class=normal>".str_replace(" ", "&nbsp;", prefixed($newuploaded - $uploaded))."</td><td class=embedded width=5%></td><td class=normalrowhead>New&nbsp;upload&nbsp;amount:</td><td class=normal>".str_replace(" ", "&nbsp;", prefixed($newuploaded))."</td></tr>\n");
echo("<tr><td class=normalrowhead>Current&nbsp;download&nbsp;amount:</td><td class=normal>".str_replace(" ", "&nbsp;", prefixed($downloaded))."</td><td class=embedded width=5%></td><td class=normalrowhead>Increase:</td><td class=normal>".str_replace(" ", "&nbsp;", prefixed(0))."</td><td class=embedded width=5%></td><td class=normalrowhead>New&nbsp;download&nbsp;amount:</td><td class=normal>".str_replace(" ", "&nbsp;", prefixed($downloaded))."</td></tr>\n");
echo("<tr><td class=normalrowhead>Current&nbsp;ratio:</td><td class=normal>$ratio</td><td class=embedded width=5%></td><td class=normalrowhead>Increase:</td><td class=normal>$ratiochange</td><td class=embedded width=5%></td><td class=normalrowhead>New&nbsp;ratio:</td><td class=normal>$newratio</td></tr>\n");
echo("</table></p>\n");
echo("<p><table align=center border=0 cellspacing=0 cellpadding=5><form name=tenpercent method=post action=tenpercent.php>\n");
echo("<tr><td align=center><b>Yes please </b><input type=checkbox name=sure value=yes onclick='if (this.checked) enablesubmit(); else disablesubmit();'></td></tr>\n");
echo("<tr><td align=center><input type=submit name=submit value='Add 10%' class=btn disabled></td></tr>\n");
echo("</form></table></p>\n");
stdfoot();
?>