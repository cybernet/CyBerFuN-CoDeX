<?

// CyBerFuN.Ro
// By CyBerNe7
//            //
// http://cyberfun.ro/
// http://xlist.ro/

require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    stderr(_("Error"), _("Permission denied."));
stdhead();

?>
<script language="Javascript">
function fuellen(f,longsource,text)
{
txtobj = document.getElementById(longsource);
f.bookmcomment.value = text;
}
function fuellen2(f,longsource,text)
{
txtobj = document.getElementById(longsource);
f.disreason.value = text;
}
</script>
<?php
$secs = 86400;
$dt = sqlesc(get_date_time(gmtime() - $secs));
$week = 604800;
$wdt = sqlesc(get_date_time(gmtime() - $week));
if ($_GET["done"] == "yes")
    $add = "AND addbookmark='ratio'";
elseif ($_GET["done"] == "no")
    $add = "AND addbookmark='no'";
else
    $add = "AND addbookmark='no'";
$res = mysql_query("SELECT * FROM users WHERE uploaded / downloaded <= 0.70 AND enabled = 'yes' AND immun='no' AND added < $wdt $add ORDER BY (uploaded/downloaded) ASC") or sqlerr(__FILE__, __LINE__);
echo "<table><tr><h1>" . ($_GET["done"] == "yes"?"already bookmarked":"not yet bookmarked") . " Bad Ratio User:</h1></tr>";
echo "" . ($_GET["done"] == "yes"?"<table><tr style=\"border:none;\"><td style=\"border:none;\"><a href=\"" . $_SERVER['PHP_SELF'] . "?done=no\">show non bookmarked users</a></td></tr></table><br />":"<table><tr style=\"border:none;\"><td style=\"border:none;\"><a href=\"" . $_SERVER['PHP_SELF'] . "?done=yes\">show bookmarked</a></td></tr></table><br />") . "";
if (mysql_num_rows($res) == 0)
    echo "<table><tr><td class=colhead><font size=3>Cant believe the list is empty!</font></td></tr></table>";
else {
    echo "<table width=550><tr><td class=colhead>Member</td><td class=colhead>Time until incl.</td><td class=colhead>Upload</td><td class=colhead>Download</td><td class=colhead>Ratio</td><td class=colhead>last activity</td></tr>";
    while ($row = mysql_fetch_assoc($res)) {
        if ($row["downloaded"] > 0) {
            $ratio = number_format($row["uploaded"] / $row["downloaded"], 3);
            $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
        } else
        if ($row["uploaded"] > 0)
            $ratio = "Inf.";
        else
            $ratio = "---";
        echo "<tr><td><a href=\"javascript:ajaxpage('inpagebadratio.php?id=" . $row["id"] . "', 'badratio');\">" . $row["username"] . "</a></td><td>" . ($row["addbookmark"] == "ratio"?"" . substr($row["bookmcomment"], -10, 10) . "":"not bookmarked") . "</td><td>" . prefixed($row["uploaded"]) . "</td><td>" . prefixed($row["downloaded"]) . "</td><td>" . $ratio . "</td><td>" . date("d.m.Y - H:i:s", strtotime($row["last_access"])) . "</td></tr>";
    }
    echo "</table>";

    echo "<div id=\"badratio\"></div>";
}

stdfoot();

?>
