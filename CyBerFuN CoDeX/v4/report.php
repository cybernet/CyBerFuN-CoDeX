<?php
require_once("include/bittorrent.php");
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
parked(); // comment out if you don't use this :P

// === now all reports just use a single var $id and a type thanks dokty... again :P
$id = ($_GET["id"] ? $_GET["id"] : $_POST["id"]);
$type = ($_GET["type"] ? $_GET["type"] : $_POST["type"]);
if (!is_valid_id($id))
    stderr("Error", "Bad ID!");
$typesallowed = array("User", "Comment", "Request_Comment", "Offer_Comment", "Request", "Offer", "Torrent", "Hit_And_Run", "Post");
if (!in_array($type, $typesallowed))
    stderr("Error", "What you are trying to report doesn't exist!");
// === still need a second value passed for stuff like hit and run where you need two id's :P
if ((isset($_GET["id_2"])) || (isset($_POST["id_2"]))) {
    $id_2 = ($_GET["id_2"] ? $_GET["id_2"] : $_POST["id_2"]);
    if (!is_valid_id($id_2))
        stderr("Error", "I smell a rat!");
    $id_2b = "&id_2=$id_2";
}

if ((isset($_GET["do_it"])) || (isset($_POST["do_it"]))) {
    $do_it = ($_GET["do_it"] ? $_GET["do_it"] : $_POST["do_it"]);
    if (!is_valid_id($do_it))
        stderr("Error", "I smell a rat!");
    // == make sure the reason is filled out and is set
    $reason = sqlesc($_POST["reason"]);
    if (!$reason)
        stderr("Error", "You MUST enter a reason for this report! Use your back button and fill in the reason");
    // === check if it's been reported already
    $res = mysql_query("SELECT id FROM reports WHERE reported_by = $CURUSER[id] AND reporting_what = $id AND reporting_type = '$type'") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) != 0)
        stderr("Report Failure!", "You have allready reported <b>" . str_replace("_" , " ", $type) . "</b> with id: <b>$id</b>!");
    // === ok it's not been reported yet let's go on
    $dt = sqlesc(get_date_time());
    mysql_query("INSERT into reports (reported_by, reporting_what, reporting_type, reason, added, 2nd_value) VALUES ($CURUSER[id], '$id', '$type', $reason, $dt, '$id_2')") or sqlerr(__FILE__, __LINE__);

    stdhead("Confirm");
    echo("<table width=650><tr><td class=colhead><h1>Success!</h1></td></tr>" . "<tr><td class=clearalt6 align=center>Successfully Reported <b>" . str_replace("_" , " ", $type) . "</b> with id: <b>$id</b>!<br><b>Reason:</b> $reason</td></tr></table>");

    stdfoot();
    die();
} //=== end do_it
// === starting main page for reporting all...
stdhead("Report");
echo("<form method=post action=report.php?type=$type$id_2b&id=$id&do_it=1><table width=650><tr><td class=colhead colspan=2><h1>Report: " . str_replace("_" , " ", $type) . "</h1></td></tr>" . "<tr><td class=clearalt6 colspan=2 align=center><img src=pic/warned.gif alt=warned> Are you sure you would like to report <b>" . str_replace("_" , " ", $type) . "</b> with id: <b>$id</b>" . "<img src=pic/warned.gif alt=warned><br>to the Staff for violation of the <a class=altlink href=rules.php target=_blank>rules</a>?</td></tr>" . "<tr><td class=clearalt6 align=right><b>Reason:</b></td><td class=clearalt6><textarea name=reason cols=70 rows=5></textarea> [ required ]<br></td></tr>" . "<tr><td class=clearalt6 colspan=2 align=center><input type=submit class=button value=\"Confirm Report\"></form></td></tr>");
end_table();
stdfoot();
die;

?>