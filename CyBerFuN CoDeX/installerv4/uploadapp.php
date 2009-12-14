<?php
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
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
// Fill in application
if ($_POST["form"] != "1") {
    $res = sql_query("SELECT status FROM uploadapp WHERE userid = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_assoc($res);
    if ($CURUSER["class"] >= UC_UPLOADER)
        stderr("Access denied", "It appears you are allready part of our uploading team.");
    elseif ($arr["status"] == "pending")
        stderr("Access denied", "It appears you are currently pending confirmation of your uploader application.");
    elseif ($arr["status"] == "rejected")
        stderr("Access denied", "It appears you have applied for uploader before and have been rejected. If you would like a second chance please contact an administrator.");

    else {
        stdhead("Uploader application");
        echo("<h1 align=center>Uploader application</h1>");
        echo("<table width=750 border=1 cellspacing=0 cellpadding=10><tr><td>");
        echo("<form action=uploadapp.php method=post enctype=multipart/form-data>");
        echo("<table border=1 cellspacing=0 cellpadding=5 align=center>");

        if ($CURUSER["downloaded"] > 0)
            $ratio = $CURUSER['uploaded'] / $CURUSER['downloaded'];
        elseif ($CURUSER["uploaded"] > 0)
            $ratio = 1;
        else
            $ratio = 0;

        $res = sql_query("SELECT connectable FROM peers WHERE userid=$CURUSER[id]")or sqlerr(__FILE__, __LINE__);
        if ($row = mysql_fetch_row($res)) {
            $connect = $row[0];
            if ($connect == "yes")
                $connectable = "Yes";
            else
                $connectable = "No";
        } else
            $connectable = "Pending";

        echo("<tr><td class=rowhead>My username is</td><td><input name=userid type=hidden value=" . $CURUSER['id'] . ">" . $CURUSER['username'] . "</td></tr>");
        echo("<tr><td class=rowhead>I have joined at</td><td>" . $CURUSER['added'] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($CURUSER["added"])) . " ago)</td></tr>");
        echo("<tr><td class=rowhead>I have a positive ratio</td><td>" . ($ratio >= 1 ? "Yes" : "No") . "</td></tr>");
        echo("<tr><td class=rowhead>I am connectable</td><td><input name=connectable type=hidden value=$connectable>$connectable</td></tr>");
        echo("<tr><td class=rowhead>My upload speed is</td><td><input type=text name=speed size=19></td></tr>");
        echo("<tr><td class=rowhead>What I have to offer</td><td><textarea name=offer cols=80 rows=1 wrap=VIRTUAL></textarea></td></tr>");
        echo("<tr><td class=rowhead>Why I should be promoted</td><td><textarea name=reason cols=80 rows=2 wrap=VIRTUAL></textarea></td></tr>");
        echo("<tr><td class=rowhead>I am uploader at other sites</td><td><input type=radio name=sites value=yes>Yes
	<input name=sites type=radio value=no checked>No</td></tr>");
        echo("<tr><td class=rowhead>Those sites are</td><td><textarea name=sitenames cols=80 rows=1 wrap=VIRTUAL></textarea></td></tr>");
        echo("<tr><td class=rowhead>I have scene access</td><td><input type=radio name=scene value=yes>Yes
	<input name=scene type=radio value=no checked>No</td></tr>");
        echo("<tr><td colspan=2>");
        echo("<br>&nbsp;&nbsp;I know how to create, upload and seed torrents");
        echo("<br><input type=radio name=creating value=yes>Yes
	<input name=creating type=radio value=no checked>No");
        echo("<br><br>&nbsp;&nbsp;I understand that I have to keep seeding my torrents until there are at least two other seeders");
        echo("<br><input type=radio name=seeding value=yes>Yes
	<input name=seeding type=radio value=no checked>No");
        echo("<br><br><input name=form type=hidden value=1>");
        echo("</td></tr>");
        echo("</table>");
        echo("<p align=center><input type=submit name=Submit value=Send></p>");
        echo("</table></form>");
        echo("</td></tr></table>");
        stdfoot();
    }
    // Process application
} else {
    $userid = 0 + $_POST["userid"];
    $connectable = $_POST["connectable"];
    $speed = $_POST["speed"];
    $offer = $_POST["offer"];
    $reason = $_POST["reason"];
    $sites = $_POST["sites"];
    $sitenames = $_POST["sitenames"];
    $scene = $_POST["scene"];
    $creating = $_POST["creating"];
    $seeding = $_POST["seeding"];

    if (!is_valid_id($userid))
        stderr("Error", "It appears something went wrong while sending your application. Please <a href=uploadapp.php>try again</a>.");
    if (!$speed)
        stderr("Error", "It appears you have left the field with your upload speed blank.");
    if (!$offer)
        stderr("Error", "It appears you have left the field with the things you have to offer blank.");
    if (!$reason)
        stderr("Error", "It appears you have left the field with the reason why we should promote you blank.");
    if ($sites == "yes" && !$sitenames)
        stderr("Error", "It appears you have left the field with the sites you are uploader at blank.");

    $res = sql_query("INSERT INTO uploadapp(userid,applied,connectable,speed,offer,reason,sites,sitenames,scene,creating,seeding) VALUES($userid, " . implode(",", array_map("sqlesc", array(get_date_time(), $connectable, $speed, $offer, $reason, $sites, $sitenames, $scene, $creating, $seeding))) . ")") ;
    if (!$res) {
        if (mysql_errno() == 1062)
            stderr("Error", "It appears you tried to send your application twice.");
        else
            stderr("Error", "It appears something went wrong while sending your application. Please <a href=uploadapp.php>try again</a>.");
    } else {
        $msg = sqlesc("An uploader application has just been filled in by [url=$BASEURL/userdetails.php?id=$CURUSER[id]][b]$CURUSER[username][/b][/url]. Click [url=$BASEURL/uploadapps.php]here[/url] to go to the uploader applications page.");
        $dt = sqlesc(get_date_time());
        $subres = sql_query("SELECT id FROM users WHERE class = 7") or sqlerr(__FILE__, __LINE__);
        while ($arr = mysql_fetch_assoc($subres))
        sql_query("INSERT INTO messages(sender, receiver, added, msg, poster) VALUES(0, $arr[id], $dt, $msg, 0)") or sqlerr(__FILE__, __LINE__);
        stderr("Application sent", "Your application has succesfully been sent to the staff.");
    }
}

?>