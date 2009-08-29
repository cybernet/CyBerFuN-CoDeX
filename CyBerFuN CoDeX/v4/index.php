<?php
//$C_USER_FIELDS = 'users.*';
$page_find = 'index';
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(true);
getpage();
loggedinorreturn();
// /////////latest user - comment out if not required/////
if ($CURUSER) {
    $cache_newuser = "$CACHE/newuser.txt";
    $cache_newuser_life = 2 * 60 ; //2 min
    if (file_exists($cache_newuser) && is_array(unserialize(file_get_contents($cache_newuser))) && (time() - filemtime($cache_newuser)) < $cache_newuser_life)
        $arr = unserialize(@file_get_contents($cache_newuser));
    else {
        $r_new = sql_query("select id , username FROM users order by id desc limit 1 ") or sqlerr(__FILE__, __LINE__);
        $arr = mysql_fetch_assoc($r_new);
        $handle = fopen($cache_newuser, "w+");
        fwrite($handle, serialize($arr));
        fclose($handle);
    }
    $new_user = "<font class=small><b>&nbsp;<a href=\"userdetails.php?id=" . $arr["id"] . "\">" . safechar($arr["username"]) . "</a></b> !</font>\n";
}
// end latest user///////////
// cache for stats
$cache_stats = "$CACHE/stats.txt";
$cache_stats_life = 5 * 60; // 5min
if (file_exists($cache_stats) && is_array(unserialize(file_get_contents($cache_stats))) && (time() - filemtime($cache_stats)) < $cache_stats_life)
    $row = unserialize(@file_get_contents($cache_stats));
else {
    $stats = sql_query("SELECT *, seeders + leechers AS peers, seeders / leechers AS ratio, unconnectables / (seeders + leechers) AS ratiounconn FROM stats WHERE id = '1' LIMIT 1") or sqlerr(__FILE__, __LINE__);
    $row = mysql_fetch_assoc($stats);
    $handle = fopen($cache_stats, "w+");
    fwrite($handle, serialize($row));
    fclose($handle);
}
$seeders = number_format($row['seeders']);
$leechers = number_format($row['leechers']);
$registered = number_format($row['regusers']);
$unverified = number_format($row['unconusers']);
$male = number_format($row['male']);
$female = number_format($row['female']);
$torrents = number_format($row['torrents']);
$torrentstoday = number_format($row['torrentstoday']);
$ratiounconn = $row['ratiounconn'];
$unconnectables = $row['unconnectables'];
$ratio = round(($row['ratio'] * 100));
$peers = number_format($row['peers']);
$numactive = number_format($row['numactive']);
$donors = number_format($row['donors']);
$forumposts = number_format($row['forumposts']);
$forumtopics = number_format($row['forumtopics']);

$file1 = "$CACHE/active.txt";
$expire = 30; // 30 seconds
if (file_exists($file1) && filemtime($file1) > (time() - $expire)) {
    $active3 = unserialize(file_get_contents($file1));
} else {
    $dt = gmtime() - 180;
    $dt = sqlesc(get_date_time($dt));
    $active1 = sql_query("SELECT id, username, class, warned, donor FROM users WHERE last_access >= " . unsafeChar($dt) . " ORDER BY class DESC") or sqlerr(__FILE__, __LINE__);
    while ($active2 = mysql_fetch_array($active1)) {
        $active3[] = $active2;
    }
    $OUTPUT = serialize($active3);
    $fp = fopen($file1, "w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else
$activeusers = '';
if (is_array($active3))
foreach ($active3 as $arr) {
    if ($activeusers) $activeusers .= ",\n";
    $activeusers .= "<span style=\"white-space: nowrap;\">";
    switch ($arr["class"]) {
        case UC_CODER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . safeChar($arr['username']) . "</font>";
            break;
        case UC_SYSOP:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . safeChar($arr['username']) . "</font>";
            break;
        case UC_ADMINISTRATOR:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_MODERATOR:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_UPLOADER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_VIP:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_POWER_USER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_USER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
    }

    $donator = $arr["donor"] === "yes";
    $warned = $arr["warned"] === "yes";

    if ($CURUSER)
        $activeusers .= "<a href=userdetails.php?id={$arr["id"]}><b>{$arr["username"]}</b></a>";
    else
        $activeusers .= "<b>{$arr["username"]}</b>";
    if ($donator)
        $activeusers .= "<img src={$pic_base_url}star.gif alt='Donated' />";
    if ($warned)
        $activeusers .= "<img src={$pic_base_url}warned.gif alt='Warned' />";
    $activeusers .= "</span>";
}

if (!$activeusers)
    $activeusers = "".$language['act']."";
stdhead();
// /////comment-out to disable latest member display/////
echo("<img src='cimage.php'>");
echo'<br />';
echo "".$language['wel'].",$new_user\n";
// Start of Last X torrents with poster mod
$query = "SELECT id, name, poster FROM torrents WHERE poster <> '' ORDER BY added DESC limit 15";
$result = mysql_query( $query );
$num = mysql_num_rows( $result );
// count rows
if ( $CURUSER['tohp'] == "yes" ) {
    echo( "<table width=754><tr><td class=\"colhead\"><h2>".$language['ltor']."</h2></td></tr>" );
    echo '<tr><td><marquee scrollAmount=3 onMouseover="this.scrollAmount=0" onMouseout="this.scrollAmount=3" scrolldelay="0" direction="right">';
    $i = 20;
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $id = unsafeChar( $row['id'] );
        $name = safeChar( $row['name'] );
        $poster = safeChar( $row['poster'] );
        $name = str_replace( '_', ' ' , $name );
        $name = str_replace( '.', ' ' , $name );
        $name = substr( $name, 0, 50 );
        if ( $i == 0 )echo'</marquee><marquee scrollAmount=3 onMouseover="this.scrollAmount=0" onMouseout="this.scrollAmount=3" scrolldelay="0" direction="right">';
        echo "<a href=$BASEURL/details.php?id=$id title=\"$name\"><img src=\"" . safeChar( $poster ) . "\" width=\"100\" height=\"120\" title=\"$name\" border=0 /></a>";
        $i++;
    }
    echo "</marquee></td></tr></table>";
}
// ////////End poster mod
// ////////////recommeded torrents///////////////
if ( $CURUSER['rohp'] == "yes" ) { 
    echo( "<table width=754><tr><td class=\"colhead\" colspan=\"4\"><h2>".$language['rtor']."</h2></td></tr>" ); {
        $res1 = mysql_query( "SELECT torrents.id AS torrentid, torrents.size, torrents.name, torrents.filename, torrents.leechers, torrents.seeders, torrents.times_completed, torrents.poster, torrents.countstats, torrents.owner, users.username AS username, users.class AS class FROM torrents INNER JOIN users ON torrents.owner = users.id WHERE torrents.recommended='yes' ORDER BY torrents.times_completed DESC LIMIT 4" );
        if ( mysql_num_rows( $res1 ) > 0 ) {
            echo "<tr>";
            while ( $arr1 = mysql_fetch_assoc( $res1 ) ) {
                $dispname = trim( $arr1["name"] );

                if ( strlen( $dispname ) > 30 ) {
                    $torlinkalt = " title=\"$dispname\"";
                    $dispname = substr( $dispname, 0, 30 ) . "...";
                } else
                    $torlinkalt = " title=\"$dispname\"";
                echo "<td width=\"188px\" align=\"center\"" . ( $arr1['countstats'] == "no" ? " style='background-color:green'" : ( $arr1['countstats'] == "yes"?" style='background-color:orange'":"" ) ) . ">
<br />
<strong><a href=\"./details.php?id=$arr1[torrentid]&hit=1\"$torlinkalt><img src=\"" . safeChar( $arr1['poster'] ) . "\" border=\"0\" width=\"100\" height=\"120\"></a></strong>
<br />
<strong><a href=\"./details.php?id=$arr1[torrentid]&hit=1\"$torlinkalt>" . safeChar( $dispname ) . "</a></strong><br /><br />
<strong><a href=\"./details.php?id=$arr1[torrentid]&dllist=1#seeders\"><font color=yellow>Seeders " . safeChar( $arr1['seeders'] ) . "</font> <font color=red>Leechers " . safeChar( $arr1['leechers'] ) . "</font></a></strong><br />
<strong><em>Size:</em></strong> " . safeChar( prefixed( $arr1['size'] ) ) . "<br />
</td>";
            }
        }
        echo "</tr></table><br />";
    }
    mysql_free_result( $res1 );
    unset( $arr1 );
}
// ////////////////////////////////////////
// /////////////Birthday cache///////////////////////////////////
$file2 = "$CACHE/birthday.txt";
$expire = 21600; // 6 hours
if (file_exists($file2) && filemtime($file2) > (time() - $expire)) {
    $res3 = unserialize(file_get_contents($file2));
} else {
    $today = date("'%'-m-d");
    $current_date = getdate();
    list($year1, $month1, $day1) = split('-', $currentdate);
    $res1 = sql_query("SELECT id, username, birthday, class, gender, bohp, donor FROM users WHERE MONTH(birthday) = '" . unsafeChar($current_date['mon']) . "' AND DAYOFMONTH(birthday) = '" . unsafeChar($current_date['mday']) . "' ORDER BY class DESC ") or sqlerr(__FILE__, __LINE__);
    while ($res2 = mysql_fetch_array($res1)) {
        $res3[] = $res2;
    }
    $OUTPUT = serialize($res3);
    $fp = fopen($file2, "w");
    fputs($fp, $OUTPUT);
    fclose($fp);
} // end else
$birthdayusers = '';
if (is_array($res3))
    foreach ($res3 as $arr) {
    $birthday = date($arr["birthday"]);
    if ($birthdayusers) $birthdayusers .= ",\n";
    $birthdayusers .= "<span style=\"white-space: nowrap;\">";
    switch ($arr["class"]) {
        case UC_CODER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_SYSOP:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_ADMINISTRATOR:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_MODERATOR:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_UPLOADER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_VIP:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_POWER_USER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
        case UC_USER:
            $arr["username"] = " <font color='#" . get_user_class_color($arr['class']) . "'> " . SafeChar($arr['username']) . "</font>";
            break;
    }

    $donator = $arr["donor"] === "yes";
    $warned = $arr["warned"] === "yes";

    if ($CURUSER)
        $birthdayusers .= "<a href=userdetails.php?id={$arr["id"]}><b>{$arr["username"]}</b></a>";
    else
        $birthdayusers .= "<b>{$arr["username"]}</b>";
    if ($donator)
        $birthdayusers .= "<img src={$pic_base_url}star.gif alt='Donated' />";
    if ($warned)
        $birthdayusers .= "<img src={$pic_base_url}warned.gif alt='Warned' />";
    $birthdayusers .= "</span>";
}
if (!$birthdayusers)
     $birthdayusers = "".$language['bday2']."";
// //////////////////////////
// ////////////////news cache/////////////
$cachefile = "cache/news" . ($CURUSER['class'] >= UC_ADMINISTRATOR ? 'staff' : '') . ".html";
if (file_exists($cachefile)) {
    include($cachefile);
} else {
    ob_start(); // start the output buffer
?>
<table width="758" class='main' border='0' cellspacing='0' cellpadding='0'>
<tr><td class='embedded'><h2><?php echo $language['news'];?><?php
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {

        ?>&nbsp;-&nbsp;<font class='small'>[<a class='altlink' href='/news.php'><b><?php echo $language['news_edit'];?></b></a>]</font><?php
    }

    ?></h2>
</td></tr>
<tr><td  class='embedded'>
<?php
    $res = mysql_query("SELECT n.id, n.userid, n.added, n.title, n.body, n.sticky, u.username " . "FROM news AS n " . "LEFT JOIN users AS u ON u.id = n.userid " . "WHERE ADDDATE(n.added, INTERVAL 45 DAY) > NOW() " . "ORDER BY sticky, n.added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        for ($i = 0; $arr = mysql_fetch_assoc($res); ++$i) {
?>
<table width='100%' border='1' cellspacing='0' cellpadding='10'>

<tr><td class="colhead"><?php echo gmdate("d/M-Y", strtotime($arr['added']));

            ?>&nbsp;&nbsp;
<a href="javascript:klappe_descr('news<?=$arr['id']?>')"><?=safeChar($arr['title'])?></a><?php
            if ($CURUSER['class'] >= UC_ADMINISTRATOR) {

                ?>&nbsp;<font size="-2"> &nbsp; [<a class='altlink' href='/news.php?action=edit&amp;newsid=<?php echo $arr['id'];

                ?>&amp;returnto=<?php echo urlencode($_SERVER['PHP_SELF']);

                ?>'><b>E</b></a>]</font><?php
                ?>&nbsp;<font size="-2">[<a class='altlink' href='/news.php?action=delete&amp;newsid=<?php echo $arr['id'];

                ?>&amp;returnto=<?php echo urlencode($_SERVER['PHP_SELF']);

                ?>'><b>D</b></a>]</font><?php
            }

            ?>
</td></tr>
<tr id="knews<?php echo $arr['id'];

            ?>" style="display:<?=($arr["sticky"] == "yes" ? "" : "none")?>;"><td >
<?=format_comment($arr["body"], true) ?><br/>
    Added by <a href="userdetails.php?id=<?=$arr["userid"]?>"><b><?=$arr["username"]?></b></a>
		</td></tr>
	</table>
<br/>
<?php
        }
    }
    echo("</td></tr></table>");
    $fp = fopen($cachefile, 'w');
    // save the contents of output buffer to the file
    fwrite($fp, ob_get_contents());
    // close the file
    fclose($fp);
    // Send the output to the browser
    ob_flush();
}

?><?php
// /////////////news end////////////////////////
// //////////////changelog cache////////////////
$cachefile = "cache/changelog" . ($CURUSER['class'] >= UC_ADMINISTRATOR ? 'staff' : '') . ".html";
if (file_exists($cachefile))
    include($cachefile);
else {
    ob_start(); // start the output buffer
    ?>
<table width="758" class='main' border='0' cellspacing='0' cellpadding='0'>
<tr><td class='embedded'>
<h2><?php echo $language['log'];?>
<?php
  if ($CURUSER['class'] >= UC_ADMINISTRATOR) {

        ?>&nbsp;-&nbsp;<font class='small'>[<a class='altlink' href='/changelog.php'><b><?php echo $language['log_edit'];?></b></a>]</font><?php
    }

    ?>
</h2></td></tr>
<tr><td>
<?php
    $res = mysql_query("SELECT cl.id, cl.userid, cl.added, cl.title, cl.body, cl.sticky, u.username " . "FROM changelog AS cl " . "LEFT JOIN users AS u ON u.id = cl.userid " . "WHERE ADDDATE(cl.added, INTERVAL 30 DAY) > NOW() " . "ORDER BY sticky, cl.added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        for ($i = 0; $arr = mysql_fetch_assoc($res); ++$i) {

            ?>
	<table width='100%' border='1' cellspacing='0' cellpadding='10'>
		<tr><td class='colhead'>&nbsp;<?=(gmdate("d M/Y", strtotime($arr['added'])))?>&nbsp;&nbsp;<a href="javascript:klappe_descr('changelog<?=$arr['id']?>')"><?=(safeChar($arr['title']))?></a>
			<?php
            if ($CURUSER['class'] >= UC_SYSOP) {

                ?>&nbsp;<font size="-2"> &nbsp; [<a class='altlink' href='/changelog.php?action=edit&amp;changelogid=<?php echo $arr['id'];

                ?>&amp;returnto=<?php echo urlencode($_SERVER['PHP_SELF']);

                ?>'><b>E</b></a>]</font><?php
                ?>&nbsp;<font size="-2">[<a class='altlink' href='/changelog.php?action=delete&amp;changelogid=<?php echo $arr['id'];

                ?>&amp;returnto=<?php echo urlencode($_SERVER['PHP_SELF']);

                ?>'><b>D</b></a>]</font><?php
            }
            ?>
		</td></tr>
		<tr id="kchangelog<?php echo $arr['id'];

            ?>" style="display:<?=($arr["sticky"] == "yes" ? "" : "none")?>;" ><td class="embdded">
		<?=format_comment($arr["body"], true);
            ?>
		<br/>
		Added by <a href="userdetails.php?id=<?=$arr["userid"]?>"><b><?=$arr["username"]?></b></a>
		</td></tr>
	</table>
<br/>
<?php
        }
    }
    echo("</td></tr></table>");
    $fp = fopen($cachefile, 'w');
    // save the contents of output buffer to the file
    fwrite($fp, ob_get_contents());
    // close the file
    fclose($fp);
    // Send the output to the browser
    ob_flush();
}

echo("<br/>");

?>
<?php
require_once("include/function_forumpost.php");
latestforumposts();
?>

<?php
if ($CURUSER) {

    ?>
<script type="text/javascript" src="poll/jquery.js"></script>
<script type="text/javascript" src="poll/poll.core.js"></script>
<link href="poll/poll.core.css" type="text/css" rel="stylesheet" />
<script type="text/javascript">
	var jq = jQuery.noConflict();
	jq(document).ready(function(){
		loadpoll();
	});</script>
<h2><?php echo $language['poll'];?>
<?php
if ($CURUSER['class'] >= UC_ADMINISTRATOR) {

        ?>&nbsp;-&nbsp;<font class='small'>[<a href=makepoll.php?returnto=/index.php><b><?php echo $language['new'];?></b></a>]</font><?php
    }
    ?>
</h2>
<table width="758" border="1" cellspacing="0" cellpadding="10">
<tr>
<td align="center">
<div id="poll_container">
<div id="loading_poll" style="display:none"></div>
<noscript>
<b>This requires javascript enabled</b>
</noscript>
</div>
<br/>
</td>
</tr>
</table>
<?php
}
?>
<br />
<!--online users block -->
<h2 align="center"><?php echo $language['users'];?><font class="small"><b><a href="#activeusers" onclick="closeit('div2');"> [Hide</a> | <a href="#activeusers" onclick="showit('div2');">Show]</a></b></font></h2>
<div id="div2" style="display: none;">
	<table border="1" cellpadding="10" cellspacing="0" width="758">
		<tr class="ttable">
			<td class="text"><?=$activeusers?></td>
		</tr>
	</table>
</div>
<?php
$bohp = '';
if ($CURUSER['bohp'] == "yes") {

    ?>
<h2 align="center"><?php echo $language['bday'];?><font class="small"><b><a href="#birthdayusers" onclick="closeit('div3');"> [Hide</a> | <a href="#birthdayusers" onclick="showit('div3');">Show]</a></b></font></h2>
<div id="div3" style="display: none;">
<table border="1" cellpadding="10" cellspacing="0" width="760">
		<tr class="ttable">
			<td class="text"><?=$birthdayusers?></td>
		</tr>
	</table>
</div>
<?php }

?>
<h2 align="center"><a href="javascript:klappe('stats')"><?php echo $language['stats'];?></a></h2>
<div id="kstats" style="display:none">
	<table border="1" cellpadding="10" cellspacing="0" width="758">
		<tr>
			<td align="center">
			<table border="1" cellpadding="10" cellspacing="0" width="50%">
				<tr>
    <td class="rowhead"><?php echo $language['rusers'];?></td><td align="right"><?= $registered;

?>/<?= $maxusers;

?></td>
    <td class="rowhead"><?php echo $language['musers'];?></td><td align="right"><?= $male;

?></td></tr><tr>
    <td class="rowhead"><?php echo $language['fusers'];?></td><td align="right"><?= $female;

?></td>
    <td class="rowhead"><?php echo $language['users'];?></td><td align="right"><?= $numactive;

?></td>
</tr>
<tr>
    <td class="rowhead"><?php echo $language['uusers'];?></td><td align="right"><?= $unverified;

?></td>
    <td class="rowhead"><?php echo $language['dusers'];?></td><td align="right"><?= $donors;

?></td>
</tr>
<tr>
    <td colspan="4"> </td>
</tr>
<tr>
    <td class="rowhead"><?php echo $language['ftopics'];?></td><td align="right"><?= $forumtopics;

?></td>
    <td class="rowhead"><?php echo $language['tor'];?></td><td align="right"><?= $torrents;

?></td>
</tr>
<tr>
    <td class="rowhead"><?php echo $language['fposts'];?></td><td align="right"><?= $forumposts;

?></td>
    <td class="rowhead"><?php echo $language['ttor'];?></td><td align="right"><?= $torrentstoday;

?></td>
</tr>
<tr>
    <td colspan="4"> </td>
</tr>
<tr>
    <td class="rowhead"><?php echo $language['peers'];?></td><td align="right"><?= $peers;

?></td>
    <td class="rowhead"><?php echo $language['uncon'];?></td><td align="right"><?= $unconnectables;

?></td>
</tr>
<tr>
    <td class="rowhead"><?php echo $language['seed'];?></td><td align="right"><?= $seeders;

?></td>
    <td class="rowhead"><b><?php echo $language['uncon1'];?></b></td><td align="right"><b><?= round($ratiounconn * 100);

?>%</b></td>
</tr>
<tr>
    <td class="rowhead"><?php echo $language['leech'];?></td><td align="right"><?= $leechers;

?></td>
    <td class="rowhead"><?php echo $language['slleech'];?></td><td align="right"><?= $ratio;?>%</td>
</tr>
</table>
</td></tr>
</table>
</div>
<!--
<p>Donations</p>
<a href="donate.php">
<img border=0 src="pic/makedonation.gif" style="opacity:0.4;filter:alpha(opacity=40)"
onmouseover="this.style.opacity=1;this.filters.alpha.opacity=100"
onmouseout="this.style.opacity=0.4;this.filters.alpha.opacity=40" /></a>-->

<?php
/*
//====donation progress bar by snuggles enjoy
$total_funds1 = sql_query("SELECT sum(cash) as total_funds FROM funds");
$arr_funds = mysql_fetch_array($total_funds1);
$funds_so_far = $arr_funds["total_funds"];
$totalneeded = "264";    //=== set this to your monthly wanted amount
$funds_difference = $totalneeded - $funds_so_far;
$Progress_so_far = number_format($funds_so_far / $totalneeded * 100, 1);
if($Progress_so_far >= 100)
$Progress_so_far = "100";
echo"<table width=140 height=20 border=2><tr><td bgcolor=blue align=center valign=middle width=$Progress_so_far%>$Progress_so_far%</td><td bgcolor=grey align=center valign=middle</td></tr></table>";
//end
*/
?>
<!--
<p>FreeLeech Pot</p>
<a href="mybonus.php">
<img border=0 src="pic/cat_free.gif" style="opacity:0.4;filter:alpha(opacity=40)"
onmouseover="this.style.opacity=1;this.filters.alpha.opacity=100"
onmouseout="this.style.opacity=0.4;this.filters.alpha.opacity=40" /></a>
-->
<?php
/*
//====points pool progress by Bigjoos - cheers snuggs :)
$total_points1 = sql_query("SELECT sum(pointspool) as total_points FROM bonus");
$arr_points = mysql_fetch_array($total_points1);
$points_so_far = $arr_points["total_points"];
$totalneeded = "100000";    //=== set this to your monthly wanted amount
$points_difference = $totalneeded - $points_so_far;
$Progress_so_far = number_format($points_so_far / $totalneeded * 100, 1);
if($Progress_so_far >= 100)
$Progress_so_far = "100";
echo"<table width=140 height=20 border=2><tr><td bgcolor=red align=center valign=middle width=$Progress_so_far%>$Progress_so_far%</td><td bgcolor=grey align=center valign=middle</td></tr></table>";
//end
*/
?>
<?php
//echo("<p align=center><font class=small>" . date('Y-m-d H:i:s', filemtime($file1)) . "</font></p>");
stdfoot();
?>