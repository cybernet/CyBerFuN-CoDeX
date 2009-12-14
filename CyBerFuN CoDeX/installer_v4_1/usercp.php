<?php
$page_find = 'usercp';
/**
* Updated usercp.php By Bigjoos + putyn
* Credits: Djlee's code from takeprofileedit.php - Retro for the original idea and not forgetting the original usercp creator :)
*/
require_once("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
echo("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

if ($usergroups['canusercp'] == 'no' OR $usergroups['canusercp'] != 'yes') {
	stderr( "Sorry...", "You are not allowed to edit your profile" );
	exit;
	}

$action = isset($_GET["action"]) ?$_GET["action"] : '';
stdhead ($CURUSER ["username"] . "'s private page", false);

$speed = array(
    '1' => '64kbps',
    '2' => '96kbps',
    '3' => '128kbps',
    '4' => '150kbps',
    '5' => '256kbps',
    '6' => '512kbps',
    '7' => '768kbps',
    '8' => '1Mbps',
    '9' => '1.5Mbps',
    '10' => '2Mbps',
    '11' => '3Mbps',
    '12' => '4Mbps',
    '13' => '5Mbps',
    '14' => '6Mbps',
    '15' => '7Mbps',
    '16' => '8Mbps',
    '17' => '9Mbps',
    '18' => '10Mbps',
    '19' => '48Mbps',
    '20' => '100Mbit'
    );

$tz = array(
    "-720" => "GMT - 12:00 hours (DLW)",
    "-660" => "GMT - 11:00 hours (NT)",
    "-600" => "GMT - 10:00 hours (HST)",
    "-540" => "GMT - 9:00 hours (YST)",
    "-480" => "GMT - 8:00 hours (PST)",
    "-420" => "GMT - 7:00 hours (MST)",
    "-360" => "GMT - 6:00 hours (CST)",
    "-300" => "GMT - 5:00 hours (EST)",
    "-240" => "GMT - 4:00 hours (AST)",
    "-210" => "GMT - 3:30 hours (GST)",
    "-180" => "GMT - 3:00 hours (ADT)",
    "-120" => "GMT - 2:00 hours (FST)",
    "-60" => "GMT - 1:00 hour (WAT)",
    "0" => "GMT (Universal Time)",
    "60" => "GMT + 1:00 hour (CET)",
    "120" => "GMT + 2:00 hours (EET)",
    "180" => "GMT + 3:00 hours (MSK)",
    "210" => "GMT + 3:30 hours (NST)",
    "240" => "GMT + 4:00 hours (GST)",
    "300" => "GMT + 5:00 hours (TMT)",
    "330" => "GMT + 5:30 hours (IST)",
    "360" => "GMT + 6:00 hours (BT)",
    "420" => "GMT + 7:00 hours (ICT)",
    "480" => "GMT + 8:00 hours (CCT)",
    "540" => "GMT + 9:00 hours (JST)",
    "570" => "GMT + 9:30 hours (ACST)",
    "600" => "GMT + 10:00 hours (GST)",
    "660" => "GMT + 11:00 hours (AEDT)",
    "720" => "GMT + 12:00 hours (NZST)"
    );

if (isset($_GET['edited'])) {

    ?><div align="center" style="width:600; background:#bcffbf; border:1px solid #49c24f; color:#333333;padding:5px;font-weight:bold;"><?php echo $language['pupdate'];?></div><?php
}

echo("<h1>".$language['wel']." <a href=userdetails.php?id=$CURUSER[id]>$CURUSER[username]</a> !</h1>\n");
echo("<form method=\"post\" action=\"takeeditusercp.php\" >");
echo("<table border=\"1\" width=\"600\" cellspacing=\"0\" cellpadding=\"3\" align=\"center\"><tr>");
echo("<td width=\"600\" valign=\"top\">");
// begin_frame();
if ($action == "avatar") {
    begin_table(true);
    echo("<tr><td align=\"left\" class=\"colhead\" style=\"height:25px;\" colspan=\"2\"><input type=\"hidden\" name=\"action\" value=\"avatar\" />".$language['avaop']."</td></tr>");
    if ($CURUSER["donor"] == "yes" || ($CURUSER['class'] >= UC_MODERATOR))
    /*
    tr("".$language['title']."", "<input type=\"text\" name=\"title\" size='50' value=\"".(isset($CURUSER["pre_title"])?safeChar($CURUSER["pre_title"]):$CURUSER["title"])."\" /> ", 1);
    */
    if (get_user_class() >= UC_VIP)
    tr("".$language['title']."", "<input size=50 value=\"" . safeChar($CURUSER["title"]) . "\" name=title /><br/>", 1);
    tr("".$language['avaurl']."", "<input name=avatar size=50 value=\"" . safeChar($CURUSER["avatar"]) . "\" /><br/><br/>\n".$language['avaurl1']."".$language['avaurl2']."", 1);
    tr("".$language['vava']."", "<input type=checkbox name=avatars" . ($CURUSER["avatars"] == "yes" ? " checked" : "") . " />".$language['vava1']."", 1);
    echo("<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Submit changes!\" style=\"height: 25px\" /></td></tr>");
    end_table();
} elseif ($action == "signature") {
    begin_table(true);
    echo("<tr><td align=\"left\" class=\"colhead\" style=\"height:25px;\" colspan=\"2\"><input type=\"hidden\" name=\"action\" value=\"signature\" />".$language['sigop']."</td></tr>");
    if (!empty($CURUSER["signature"]))
        echo("<tr title=\"Your signature\"><td colspan=2>" . format_comment($CURUSER["signature"]) . "</td></tr>");
    tr("".$language['sig']."", "<textarea name=signature cols=50 rows=4>" . safeChar($CURUSER["signature"]) . "</textarea><br/><font class=small size=1>".$language['sig1']."</font>\n<br/>", 1);
    tr("".$language['vsig']."", "<input type=checkbox name=signatures" . ($CURUSER["signatures"] == "yes" ? " checked" : "") . " />".$language['vava1']."", 1);
    tr("".$language['info']."", "<textarea name=info cols=50 rows=4>" . $CURUSER["info"] . "</textarea><br/>".$language['info1']."", 1);
    if (!empty($CURUSER["info"]))
        echo("<tr title=\"Your info\"><td colspan=2>" . format_comment($CURUSER["info"]) . "</td></tr>");
    echo("<tr ><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Submit changes!\" style=\"height: 25px\" /></td></tr>");
    end_table();
} else if ($action == "security") {
    begin_table(true);
    echo("<tr><td class=colhead colspan=2 style=\"height:25px;\"><input type=\"hidden\" name=\"action\" value=\"security\" />".$language['secop']."</td></tr>");
    // /parked mod////
    tr("".$language['park']."",
        "<input type=radio name=parked" . ($CURUSER["parked"] == "yes" ? " checked" : "") . " value=yes />yes
<input type=radio name=parked" . ($CURUSER["parked"] == "no" ? " checked" : "") . " value=no />no
<br/><font class=small size=1>".$language['park1']."</font>", 1);
    // /parked mod//// comment out of not required//
    // //annonymous mod//////
    tr("".$language['anon']."", "<input type=checkbox name=anonymous" . ($CURUSER["anonymous"] == "yes" ? " checked" : "") . " />".$language['anon1']."", 1);
    tr("".$language['anontt']."", "<input type=checkbox name=anonymoustopten" . ($CURUSER["anonymoustopten"] == "yes" ? " checked" : "") . " />".$language['anontt1']."", 1);
    // //annonymous mod////comment out if not required
    // ////////////////////////hide snatch lists////////////////
    tr("".$language['hsnatch']."", "<input type=radio name=hidecur" . ($CURUSER["hidecur"] == "yes" ? " checked" : "") . " value=yes />Yes<input type=radio name=hidecur" . ($CURUSER["hidecur"] == "no" ? " checked" : "") . " value=no />No", 1);
    // //////////// Passkey //////////////////
    if (get_user_class() >= UC_VIP AND $usergroups['canresetpasskey'] == 'yes')
    tr("".$language['rpasskey']."", "<input type=checkbox name=resetpasskey value=1 /><br/><font class=small>".$language['rpasskey1']."</font>", 1);
    // ////////////end passkey//////////////////
    tr("".$language['email']."", "<input type=\"text\" name=\"email\" size=50 value=\"" . safechar($CURUSER["email"]) . "\" />", 1);
    echo("<tr><td class=rowhead>*Note:</td><td align=left>automatic<br/>confirmation.</td></tr>\n");

    ?>
<tr><td class="heading" valign="top" align="right" width="20%"><?php echo $language['cpass'];?></td><td valign="top" align="left" width="80%"><input type="password" name="chpassword" size="30" class="keyboardInput" onkeypress="showkwmessage();return false;" /></td></tr>
<tr><td class="heading" valign="top" align="right" width="20%"><?php echo $language['cpassa'];?></td><td valign="top" align="left" width="80%"><input type="password" name="passagain" size="30" class="keyboardInput" onkeypress="showkwmessage();return false;" /></td></tr>
<?php
    $secretqs = "<option value=0>---- None selected ----</option>\n";
    $questions = array(
        array("id" => "1", "question" => "".$language['mbirth'].""),
        array("id" => "2", "question" => "".$language['bcc'].""),
        array("id" => "3", "question" => "".$language['nofp'].""),
        array("id" => "4", "question" => "".$language['fteach'].""),
        array("id" => "5", "question" => "".$language['fhp'].""),
        array("id" => "6", "question" => "".$language['goccp']."")
        );

    foreach($questions as $sctq) {
        $secretqs .= "<option value=" . $sctq['id'] . "" . ($CURUSER["passhint"] == $sctq['id'] ? " selected" : "") . ">" . $sctq['question'] . "</option>\n";
    }

    tr("".$language['sques']."", "<select name=changeq>\n$secretqs\n</select>", 1);
    tr("".$language['sans']."", "<input type=\"text\" name=\"secretanswer\" size=\"40\" />", 1);
    echo("<tr ><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Submit changes!\" style=\"height: 25px\" /></td></tr>");
    end_table();
} elseif ($action == "torrents") {
    begin_table(true);
    echo("<tr><td class=colhead colspan=2  style=\"height:25px;\" ><input type=\"hidden\" name=\"action\" value=\"torrents\" />".$language['topt']."</td></tr>");

    $categories = '';
    $r = mysql_query("SELECT id,name FROM categories ORDER BY name") or sqlerr();
    // $categories = "Default browsing categories:<br/>\n";
    if (mysql_num_rows($r) > 0) {
        $categories .= "<table><tr>\n";
        $i = 0;
        while ($a = mysql_fetch_assoc($r)) {
            $categories .= ($i && $i % 2 == 0) ? "</tr><tr>" : "";
            $categories .= "<td class=bottom style='padding-right: 5px'><input name=cat$a[id] type=\"checkbox\" " . (strpos($CURUSER['notifs'], "[cat$a[id]]") !== false ? " checked" : "") . " value='yes' /> " . safechar($a["name"]) . "</td>\n";
            ++$i;
        }
        $categories .= "</tr></table>\n";
    }
    tr("".$language['emailnotif']."", "<input type=checkbox name=pmnotif" . (strpos($CURUSER['notifs'], "[pm]") !== false ? " checked" : "") . " value=yes /> Notify me when I have received a PM<br/>\n" . "<input type=checkbox name=emailnotif" . (strpos($CURUSER['notifs'], "[email]") !== false ? " checked" : "") . " value=yes />".$language['emailnotif1']."Notify me when a torrent is uploaded in one of <br/> my default browsing categories.\n", 1);
    tr("".$language['bdefault']."<br/>", $categories, 1);
    tr("".$language['cimage']."", "<input type=checkbox name=imagecats" . ($CURUSER["imagecats"] == "yes" ? " checked" : "") . " /> (Enable Category Images on Browse.)", 1);
    if (get_user_class() >= UC_VIP)
        tr("".$language['hlightbrws']."", "<input type=radio name=ttablehl" . ($CURUSER["ttablehl"] == "yes" ? " checked" : "") . " value=yes />yes
<input type=radio name=ttablehl" . ($CURUSER["ttablehl"] == "no" ? " checked" : "") . " value=no />no", 1);
    tr("".$language['stbd']."",
        "<input type=radio name=split" . ($CURUSER["split"] == "yes" ? " checked" : "") . " value=yes />yes
<input type=radio name=split" . ($CURUSER["split"] == "no" ? " checked" : "") . " value=no />no", 1);
    tr("".$language['toh']."",
        "<input type=radio name=tohp" . ($CURUSER["tohp"] == "yes" ? " checked" : "") . " value=yes />yes
<input type=radio name=tohp" . ($CURUSER["tohp"] == "no" ? " checked" : "") . " value=no />no", 1);
    tr("".$language['rtoh']."",
        "<input type=radio name=rohp" . ($CURUSER["rohp"] == "yes" ? " checked" : "") . " value=yes />yes
<input type=radio name=rohp" . ($CURUSER["rohp"] == "no" ? " checked" : "") . " value=no />no", 1);
    tr("".$language['uccob']."", "<input type=checkbox name=view_uclass" . ($CURUSER["view_uclass"] == "yes" ? " checked" : "") . " />".$language['uccob1']."", 1);
    tr("".$language['cntm']."", "<input type=checkbox name=update_new" . ($CURUSER["update_new"] == "yes" ? " checked" : "") . " />".$language['new1']."", 1);
    tr("".$language['delpm']."", "<input type=radio name=deletepm" . ($CURUSER["deletepm"] == "yes" ? " checked" : "") . " value=yes />yes" . "<input type=radio name=deletepm" . ($CURUSER["deletepm"] == "no" ? " checked" : "") . " value=no />no" . "<br />".$language['delpm1']."" . "This default is yes.", 1);
    tr("".$language['torcompm']."", "<input type=radio name=commentpm" . ($CURUSER["commentpm"] == "yes" ? " checked" : "") . " value=yes />yes" . "<input type=radio name=commentpm" . ($CURUSER["commentpm"] == "no" ? " checked" : "") . " value=no />no" . "<br />".$language['torcompm1']."" . "This default is yes.", 1);
    echo("<tr><td class=colhead colspan=2 align=\"center\" style=\"height:18px;\"><a href=mytorrents.php>".$language['mytor']."</a></td></tr>");
    echo("<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Submit changes!\" style=\"height: 25px\" /></td></tr>");
    end_table();
} elseif ($action == "personal") {
    begin_table(true);
    echo("<tr><td class=colhead colspan=2  style=\"height:25px;\" ><input type=\"hidden\" name=\"action\" value=\"personal\" />".$language['popt']."</td></tr>");
    $tor_opt = '';
    $top_opt = '';
    $pos_opt = '';
    $timezone = '';
    // //////torrents per page
    $tor_per_pg = $CURUSER["torrentsperpage"];
    $tor_opt .= "<select name=torrentsperpage>";
    $tor_opt .= "<option value=5" . ($tor_per_pg == 5 ? " selected" : "") . ">5</option>";
    $tor_opt .= "<option value=10" . ($tor_per_pg == 10 ? " selected" : "") . ">10</option>";
    $tor_opt .= "<option value=15" . ($tor_per_pg == 15 ? " selected" : "") . ">15</option>";
    $tor_opt .= "<option value=50" . ($tor_per_pg == 50 ? " selected" : "") . ">50</option>";
    $tor_opt .= "<option value=100" . ($tor_per_pg == 100 ? " selected" : "") . ">100</option>";
    $tor_opt .= "<option value=150" . ($tor_per_pg == 150 ? " selected" : "") . ">150</option>";
    $tor_opt .= "<option value=200" . ($tor_per_pg == 200 ? " selected" : "") . ">200</option>";
    $tor_opt .= "</select>";
    // /////////////////////////////////////////////////////////////////////////////////////
    // //////topics per page
    $top_per_pg = $CURUSER["topicsperpage"];
    $top_opt .= "<select name=topicsperpage>";
    $top_opt .= "<option value=5" . ($top_per_pg == 5 ? " selected" : "") . ">5</option>";
    $top_opt .= "<option value=10" . ($top_per_pg == 10 ? " selected" : "") . ">10</option>";
    $top_opt .= "<option value=15" . ($top_per_pg == 15 ? " selected" : "") . ">15</option>";
    $top_opt .= "<option value=50" . ($top_per_pg == 50 ? " selected" : "") . ">50</option>";
    $top_opt .= "<option value=100" . ($top_per_pg == 100 ? " selected" : "") . ">100</option>";
    $top_opt .= "<option value=150" . ($top_per_pg == 150 ? " selected" : "") . ">150</option>";
    $top_opt .= "<option value=200" . ($top_per_pg == 200 ? " selected" : "") . ">200</option>";
    $top_opt .= "</select>";
    // /////////////////////////////////////////////////////////////////////////////////////
    // //////posts per page
    $pos_per_pg = $CURUSER["postsperpage"];
    $pos_opt .= "<select name=postsperpage>";
    $pos_opt .= "<option value=5" . ($pos_per_pg == 5 ? " selected" : "") . ">5</option>";
    $pos_opt .= "<option value=10" . ($pos_per_pg == 10 ? " selected" : "") . ">10</option>";
    $pos_opt .= "<option value=15" . ($pos_per_pg == 15 ? " selected" : "") . ">15</option>";
    $pos_opt .= "<option value=50" . ($pos_per_pg == 50 ? " selected" : "") . ">50</option>";
    $pos_opt .= "<option value=100" . ($pos_per_pg == 100 ? " selected" : "") . ">100</option>";
    $pos_opt .= "<option value=150" . ($pos_per_pg == 150 ? " selected" : "") . ">150</option>";
    $pos_opt .= "<option value=200" . ($pos_per_pg == 200 ? " selected" : "") . ">200</option>";
    $pos_opt .= "</select>";
    // /////////////////////////////////////////////////////////////////////////////////////
    echo("<tr><td colspan=2 align=\"center\" style=\"height:18px;\">".$language['torpp']."" . $tor_opt . "&nbsp;".$language['toppp']."" . $top_opt . "&nbsp;".$language['pospp']."" . $pos_opt . "</td></tr>");
    // //////////up/down speed//////////////////
    $dlspeed = "<option value=0>---- None selected ----</option>\n";
    foreach ($speed as $key => $value)
    $dlspeed .= "<option value=$key" . ($CURUSER["download"] == $key ? " selected" : "") . ">$value</option>";
    tr("".$language['downsp']."", "<select name=download>$dlspeed</select>", 1);
    reset($speed);
    $ulspeed = "<option value=0>---- None selected ----</option>\n";
    foreach ($speed as $key => $value)
    $ulspeed .= "<option value=$key" . ($CURUSER["upload"] == $key ? " selected" : "") . ">$value</option>";
    tr("".$language['upsp']."", "<select name=upload>$ulspeed</select>", 1);
    // //////////stylesheet//////////////
    $s_list = "<option value=0>---- None selected ----</option>\n";
    $s_list = '';
    include 'include/cache/stylesheets.php';
    foreach ($stylesheets as $stylesheet)
    $s_list .= "<option value=$stylesheet[id]" . ($CURUSER["stylesheet"] == $stylesheet['id'] ? " selected" : "") . ">$stylesheet[name]</option>\n";
    //////////////////cat icons by shd0743x - cached by Bigjoos 
    $ci_list = "<option value=0>---- None selected ----</option>\n";
    $ci_list = '';
    include 'include/cache/categorie_icons.php';
    foreach ($categorie_icons as $categorie_icon)
    $ci_list .= "<option value=$categorie_icon[id]" . ($CURUSER["categorie_icon"] == $categorie_icon['id'] ? " selected" : "") . ">$categorie_icon[name]</option>\n";    
    // ///////////timezone/////
    while (list($key, $value) = each($tz))
    $timezone .= "<option value=$key" . ($CURUSER["timezone"] == $key ? " selected" : "") . ">$value</option>";
    tr("".$language['tzone']."", "<select name=timezone>$timezone</select> <input type=checkbox name=dst" . ($CURUSER["dst"] ? " checked" : "") . " />Observing Daylight Savings Time", 1);
    // ////////////////////end///////
    $c_list = "<option value=0>---- None selected ----</option>\n";

    include 'include/cache/countries.php';
    foreach ($countries as $country)
    $c_list .= "<option value=$country[id]" . ($CURUSER["country"] == $country['id'] ? " selected" : "") . ">$country[name]</option>\n";
    tr("Categorie icon set", "<select name=categorie_icon>\n$ci_list\n</select>", 1);
    tr("".$language['ssheet']."", "<select name=stylesheet>\n$s_list\n</select>", 1);
    tr("".$language['country']."", "<select name=country>\n$c_list\n</select>", 1);
    tr("".$language['gender']."",
        "<input type=radio name=gender" . ($CURUSER["gender"] == "N/A" ? " checked" : "") . " value=N/A />Not Sure
<input type=radio name=gender" . ($CURUSER["gender"] == "Male" ? " checked" : "") . " value=Male />Male
<input type=radio name=gender" . ($CURUSER["gender"] == "Female" ? " checked" : "") . " value=Female />Female", 1);
    tr("".$language['shoutback']."", "<input type=radio name=shoutboxbg" . ($CURUSER["shoutboxbg"] == "1" ? " checked" : "") . " value=1 />white
<input type=radio name=shoutboxbg" . ($CURUSER["shoutboxbg"] == "2" ? " checked" : "") . " value=2 />Grey<input type=radio name=shoutboxbg" . ($CURUSER["shoutboxbg"] == "3" ? " checked" : "") . " value=3 />black", 1);
    tr("".$language['showbirth']."",
        "<input type=radio name=bohp" . ($CURUSER["bohp"] == "yes" ? " checked" : "") . " value=yes />yes
<input type=radio name=bohp" . ($CURUSER["bohp"] == "no" ? " checked" : "") . " value=no />no", 1);
    tr("".$language['ubar']."", "<img src=\"bar.php/" . $CURUSER["id"] . ".png\" border=\"0\" /><br />This is your userbar.You can place it in the signature on the forum.<br />your ratings will be visible<br /><br />Here's the  <b>BB- code</b> for the insert into the signature on the forums:<br /><input type=\"text\" size=65 value=\"[url=$DEFAULTBASEURL][img]$DEFAULTBASEURL/bar.php/" . $CURUSER["id"] . ".png[/img][/url]\" readonly />", 1);
    tr("".$language['fonline']."", "<input type=checkbox name=forumview" . ($CURUSER["forumview"] == "yes" ? " checked" : "") . " />".$language['fonline1']."", 1);
    // /////////////// Birthday mod /////////////////////
    $birthday = $CURUSER["birthday"];
    $birthday = date("Y-m-d", strtotime($birthday));
    list($year1, $month1, $day1) = split('-', $birthday);
    if ($CURUSER['birthday'] == "0000-00-00") {
        $year .= "<select name=year><option value=\"0000\">--</option>\n";
        $i = "1920";
        while ($i <= (date('Y', time())-13)) {
            $year .= "<option value=" . $i . ">" . $i . "</option>\n";
            $i++;
        }
        $year .= "</select>\n";
        $birthmonths = array("01" => "January",
            "02" => "Febuary",
            "03" => "March",
            "04" => "April",
            "05" => "May",
            "06" => "June",
            "07" => "July",
            "08" => "August",
            "09" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December",
            );
        $month = "<select name=\"month\"><option value=\"00\">--</option>\n";
        foreach ($birthmonths as $month_no => $show_month) {
            $month .= "<option value=$month_no>$show_month</option>\n";
        }
        $month .= "</select>\n";
        $day .= "<select name=day><option value=\"00\">--</option>\n";
        $i = 1;
        while ($i <= 31) {
            if ($i < 10) {
                $day .= "<option value=0" . $i . ">0" . $i . "</option>\n";
            } else {
                $day .= "<option value=" . $i . ">" . $i . "</option>\n";
            }
            $i++;
        }
        $day .= "</select>\n";
        tr("".$language['bdate']."", $year . $month . $day , 1);
    }

    echo("<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Submit changes!\" style=\"height: 25px\" /></td></tr>");
    end_table();
} else {
    begin_table(true);
    echo("<tr><td class=colhead colspan=2  style=\"height:25px;\" ><input type=\"hidden\" name=\"action\" value=\"pm\" />".$language['pmopt']."</td></tr>");
    tr("".$language['apm']."",
        "<input type=radio name=acceptpms" . ($CURUSER["acceptpms"] == "yes" ? " checked" : "") . " value=yes />All (except blocks)
<input type=radio name=acceptpms" . ($CURUSER["acceptpms"] == "friends" ? " checked" : "") . " value=friends />Friends only
<input type=radio name=acceptpms" . ($CURUSER["acceptpms"] == "no" ? " checked" : "") . " value=no />Staff only", 1);
    tr("".$language['dpms']."", "<input type=checkbox name=deletepms" . ($CURUSER["deletepms"] == "yes" ? " checked" : "") . " /> (Default value for \"Delete PM on reply\")", 1);
    tr("".$language['spms']."", "<input type=checkbox name=savepms" . ($CURUSER["savepms"] == "yes" ? " checked" : "") . " /> (Default value for \"Save PM to Sentbox\")", 1);
    tr("".$language['pmsubscrip']."", "<input type=radio name=subscription_pm" . ($CURUSER["subscription_pm"] == "yes" ? " checked" : "") . " value=yes />yes <input type=radio name=subscription_pm" . ($CURUSER["subscription_pm"] == "no" ? " checked" : "") . " value=no />no<br/> When someone posts in a subscribed thread, you will be PMed.", 1);
    tr("".$language['mfpublic']."", "<input type=checkbox name=showfriends" . ($CURUSER["showfriends"] == "yes" ? " checked" : "") . " /> (Allow my friends to be publicly shown?)", 1);
    tr("".$language['emailnotif']."", " Select under Torrents Option.", 1);
    echo("<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" value=\"Submit changes!\" style=\"height: 25px\" /></td></tr>");
    end_table();
}

echo("</td><td width=95 valign=\"top\" ><table border=\"1\">");

echo("<tr><td class=colhead width=95  style=\"height:25px;\" >$CURUSER[username]'s Avatar</td></tr>");
if ($CURUSER["avatar"])
    echo("<tr><td><img width=95 src=" . safechar($CURUSER["avatar"]) . " /></td></tr>");
else
    echo("<tr><td><img width=\"95\" src=\"pic/default_avatar.png\" /></td></tr>");
echo("<tr><td class=colhead width=\"95\" style=\"height:18px;\" >$CURUSER[username]'s Menu</td></tr>");

echo("<tr><td align=left> <a href=usercp.php?action=avatar>".$language['avatar']."</a></td></tr>");
echo("<tr><td align=left> <a href=usercp.php?action=signature>".$language['sig']."</a></td></tr>");
echo("<tr><td align=left> <a href=usercp.php>".$language['pms']."</a></td></tr>");
echo("<tr><td align=left> <a href=usercp.php?action=security>".$language['sec']."</a></td></tr>");
echo("<tr><td align=left> <a href=usercp.php?action=torrents>".$language['tor']."</a></td></tr>");
echo("<tr><td align=left> <a href=usercp.php?action=personal>".$language['pers']."</a></td></tr>");
echo("<tr><td align=left> <a href=invite.php>".$language['inv']."</a></td></tr>");
echo("<tr><td align=left>  <a href=tenpercent.php>Lifesaver</a></td></tr>");
echo("<tr><td class=colhead width=95 >$CURUSER[username]'s Entertainment</td></tr>");

if (get_user_class() >= UC_USER) {
    echo("<tr><td align=left>  <a href=topmoods.php>Top Member Mood's</a></td></tr>");
    echo("<tr><td align=left>  <a href=wiki.php>$SITENAME Wiki</a></td></tr>");
    echo("<tr><td align=left>  <a href=shows.php>$SITENAME Tv Shows</a></td></tr>");
    echo("<tr><td align=left>  <a href=avatars/index.php>$SITENAME Avatar Creator</a></td></tr>");
} 

if (get_user_class() >= UC_POWER_USER) {
    echo("<tr><td align=left>  <a href=blackjack.php>BlackJack</a></td></tr>");
    echo("<tr><td align=left>  <a href=casino.php>Casino</a></td></tr>");
    echo("<tr><td align=left>  <a href=tickets.php>$SITENAME Seedbonus Lottery</a></td></tr>");
}
if (get_user_class() >= UC_VIP)
    echo("<tr><td align=left>  <a href=hangman.php>Hangman</a></td></tr>");
    echo("<tr><td align=left>  <a href=catalogue.php>Catalogue</a></td></tr>");
echo("</table>");

echo("</td></tr>");
echo("</table>");
echo("</form>");
stdfoot();

?>
