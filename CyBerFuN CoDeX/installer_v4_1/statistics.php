<?php
/*
+-----------------------------------------------
|   Generally hacked & abused
|   ============================================
|   by CoLdFuSiOn
|   from a well know & oft' used
|   Bulletin Board
|   ============================================
|
|   Time: Sat, 22 OCT 2005
|
|   Ver. 1.1
+-----------------------------------------------
|
+-----------------------------------------------
*/
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
dbconn(false);
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
stdhead("Statistics Centre");

if (get_user_class() < UC_SYSOP) {
    stdmsg("Sorry...", "You are not authorized.");
    stdfoot();
    exit;
}

$base_url = "$DEFAULTBASEURL/statistics.php"; // You should change this! Doh!!

?>

		<h1>STATISTICS CENTRE</h1>

		<table id='torrenttable' border='1'>
		<tr><td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=reg'>Registration Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=rate'>Rating Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=post'>Post Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=msg'>Personal Message</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=torr'>Torrents Stats</a></td>
		</tr>

		<tr><td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=bans'>Ban Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=comm'>Comment Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=new'>News Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=poll'>Poll Stats</a></td>
		<td>&nbsp;<img src='pic/item.gif' border='0' alt='' valign='absmiddle'>&nbsp;<a href='statistics.php?act=stats&code=rqst'>Request Stats</a></td>
		</tr>
		</table>
		<br />

<?php

if (!isset($_GET['act']) && !isset($_POST['act'])) {
    echo "<div style='background-color: lightgrey; border: grey 2px dashed; font-style: italic;width:700px;'>
		<br />You could put something useful here!! ;)<br /><br /></div>";
}
// --------------------------------------------------------------------
function start_form($hiddens = "", $name = 'theAdminForm', $js = "")
{
    global $base_url;

    $form = "<form action='{$base_url}' method='post' name='$name' $js>
				 ";

    if (is_array($hiddens)) {
        foreach ($hiddens as $k => $v) {
            $form .= "\n<input type='hidden' name='{$v[0]}' value='{$v[1]}'>";
        }
    }

    return $form;
}
// -----------------------------------------
function form_dropdown($name, $list = array(), $default_val = "", $js = "", $css = "")
{
    if ($js != "") {
        $js = ' ' . $js . ' ';
    }

    if ($css != "") {
        $css = ' class="' . $css . '" ';
    }

    $html = "<select name='$name'" . $js . " $css class='dropdown'>\n";

    foreach ($list as $k => $v) {
        $selected = "";

        if (($default_val != "") and ($v[0] == $default_val)) {
            $selected = ' selected';
        }

        $html .= "<option value='" . $v[0] . "'" . $selected . ">" . $v[1] . "</option>\n";
    }

    $html .= "</select>\n\n";

    return $html;
}
// -----------------------------------------
function end_form($text = "", $js = "", $extra = "")
{
    // If we have text, we print another row of TD elements with a submit button
    $html = "";
    $colspan = "";
    $td_colspan = 0;

    if ($text != "") {
        if ($td_colspan > 0) {
            $colspan = " colspan='" . $td_colspan . "' ";
        }

        $html .= "<tr><td align='center' class='form'" . $colspan . "><input type='submit' value='$text'" . $js . " id='button' accesskey='s'>{$extra}</td></tr>\n";
    }

    $html .= "</form>";

    return $html;
}

$month_names = array();
// -----------------------------------------
// Don't ask!!
// -----------------------------------------
$tmp_in = array_merge($_GET, $_POST);

foreach ($tmp_in as $k => $v) {
    unset($$k);
}
// print_r($tmp_in);
// -----------------------------------------
$month_names = array(1 => 'January', 'February', 'March' , 'April' , 'May' , 'June',
    'July' , 'August' , 'September' , 'October', 'November', 'December');

if (isset($tmp_in['code']) && $tmp_in['code'] != "") {
    switch ($tmp_in['code']) {
        case 'show_reg':
            result_screen('reg');
            break;

        case 'show_rate':
            result_screen('rate');
            break;

        case 'rate':
            main_screen('rate');
            break;
        // -----------------------------------------
        case 'show_post':
            result_screen('post');
            break;

        case 'post':
            main_screen('post');
            break;
        // -----------------------------------------
        case 'show_msg':
            result_screen('msg');
            break;

        case 'msg':
            main_screen('msg');
            break;
        // -----------------------------------------
        case 'show_torr':
            result_screen('torr');
            break;

        case 'torr':
            main_screen('torr');
            break;
        // -----------------------------------------
        case 'show_bans':
            result_screen('bans');
            break;

        case 'bans':
            main_screen('bans');
            break;
        // -----------------------------------------
        case 'show_comm':
            result_screen('comm');
            break;

        case 'comm':
            main_screen('comm');
            break;
        // -----------------------------------------
        case 'show_new':
            result_screen('new');
            break;

        case 'new':
            main_screen('new');
            break;
        // -----------------------------------------
        case 'show_poll':
            result_screen('poll');
            break;

        case 'poll':
            main_screen('poll');
            break;
        // -----------------------------------------
        case 'show_rqst':
            result_screen('rqst');
            break;

        case 'rqst':
            main_screen('rqst');
            break;
        // -----------------------------------------
        default:
            main_screen('reg');
            break;
    }
}
// -----------------------------------------
// | Results screen
// -----------------------------------------
function result_screen($mode = 'reg')
{
    global $month_names;

    $page_title = "Statistic Center Results";

    $page_detail = "&nbsp;";
    // -----------------------------------------
    if (! checkdate($_POST['to_month'] , $_POST['to_day'] , $_POST['to_year'])) {
        die("The 'Date To:' time is incorrect, please check the input and try again");
    }

    if (! checkdate($_POST['from_month'] , $_POST['from_day'] , $_POST['from_year'])) {
        die("The 'Date From:' time is incorrect, please check the input and try again");
    }
    // -----------------------------------------
    $to_time = mktime(12 , 0 , 0 , $_POST['to_month'] , $_POST['to_day'] , $_POST['to_year']);
    $from_time = mktime(12 , 0 , 0 , $_POST['from_month'] , $_POST['from_day'] , $_POST['from_year']);
    // $sql_date_to = date("Y-m-d",$to_time);
    // $sql_date_from = date("Y-m-d",$from_time);
    $human_to_date = getdate($to_time);
    $human_from_date = getdate($from_time);
    // -----------------------------------------
    if ($mode == 'reg') {
        $table = 'Registration Statistics';

        $sql_table = 'users';
        $sql_field = 'added';

        $page_detail = "Showing the number of users registered. (Note: All times based on GMT)";
    } else if ($mode == 'rate') {
        $table = 'Rating Statistics';

        $sql_table = 'ratings';
        $sql_field = 'added';

        $page_detail = "Showing the number of ratings. (Note: All times based on GMT)";
    } else if ($mode == 'post') {
        $table = 'Post Statistics';

        $sql_table = 'posts';
        $sql_field = 'added';

        $page_detail = "Showing the number of posts. (Note: All times based on GMT)";
    } else if ($mode == 'msg') {
        $table = 'PM Sent Statistics';

        $sql_table = 'messages';
        $sql_field = 'added';

        $page_detail = "Showing the number of sent messages. (Note: All times based on GMT)";
    } else if ($mode == 'torr') {
        $table = 'Torrent Statistics';

        $sql_table = 'torrents';
        $sql_field = 'added';

        $page_detail = "Showing the number of Torrents. (Note: All times based on GMT)";
    } else if ($mode == 'bans') {
        $table = 'Ban Statistics';

        $sql_table = 'bans';
        $sql_field = 'added';

        $page_detail = "Showing the number of Bans. (Note: All times based on GMT)";
    } else if ($mode == 'comm') {
        $table = 'Comment Statistics';

        $sql_table = 'comments';
        $sql_field = 'added';

        $page_detail = "Showing the number of torrent Comments. (Note: All times based on GMT)";
    } else if ($mode == 'new') {
        $table = 'News Statistics';

        $sql_table = 'news';
        $sql_field = 'added';

        $page_detail = "Showing the number of News Items added. (Note: All times based on GMT)";
    } else if ($mode == 'poll') {
        $table = 'Poll Statistics';

        $sql_table = 'polls';
        $sql_field = 'added';

        $page_detail = "Showing the number of Polls added. (Note: All times based on GMT)";
    } else if ($mode == 'rqst') {
        $table = 'Request Statistics';

        $sql_table = 'requests';
        $sql_field = 'added';

        $page_detail = "Showing the number of Requests made. (Note: All times based on GMT)";
    }

    switch ($_POST['timescale']) {
        case 'daily':
            $sql_date = "%w %U %m %Y";
            $php_date = "F jS - Y";
            // $sql_scale = "DAY";
            break;

        case 'monthly':
            $sql_date = "%m %Y";
            $php_date = "F Y";
            // $sql_scale = "MONTH";
            break;

        default:
            // weekly
            $sql_date = "%U %Y";
            $php_date = " [F Y]";
            // $sql_scale = "WEEK";
            break;
    }

    $sortby = isset($_POST['sortby']) ? mysql_real_escape_string($_POST['sortby']) : "";
    // $sortby = sqlesc($sortby);
    $sqlq = "SELECT UNIX_TIMESTAMP(MAX({$sql_field})) as result_maxdate,
				 COUNT(*) as result_count,
				 DATE_FORMAT({$sql_field},'{$sql_date}') AS result_time
				 FROM {$sql_table}
				 WHERE UNIX_TIMESTAMP({$sql_field}) > '{$from_time}'
				 AND UNIX_TIMESTAMP({$sql_field}) < '{$to_time}'
				 GROUP BY result_time
				 ORDER BY {$sql_field} {$sortby}";

    $res = @mysql_query($sqlq);

    $running_total = 0;
    $max_result = 0;

    $results = array();

    if (mysql_num_rows($res)) {
        while ($row = mysql_fetch_assoc($res)) {
            if ($row['result_count'] > $max_result) {
                $max_result = $row['result_count'];
            }

            $running_total += $row['result_count'];

            $results[] = array('result_maxdate' => $row['result_maxdate'],
                'result_count' => $row['result_count'],
                'result_time' => $row['result_time'],
                );
        }
        include 'chart/php-ofc-library/open-flash-chart.php';
        foreach($results as $pOOp => $data) {
            $counts[] = (int)$data['result_count'];

            if ($_POST['timescale'] == 'weekly')
                $labes[] = "Week #" . strftime("%W", $data['result_maxdate']) . "\n" . date($php_date, $data['result_maxdate']);
            else
                $labes[] = date($php_date, $data['result_maxdate']);
        }

        $title = new title($page_title . "\n" . ucfirst($_POST['timescale']) . " " . $table . " " . $human_from_date['mday'] . " " . $month_names[$human_from_date['mon']] . " " . $human_from_date['year'] . " to "
             . $human_to_date['mday'] . " " . $month_names[$human_to_date['mon']] . " " . $human_to_date['year']);

        $chart = new open_flash_chart();
        $chart->set_title($title);

        $line_1 = new line_hollow();
        $line_1->set_values($counts);
        $line_1->set_key($table . " | Total: " . $running_total, 12);
        $line_1->set_halo_size(1);
        $line_1->set_width(2);
        $line_1->set_colour('#0099FF');
        $line_1->set_dot_size(5);

        $chart->add_element($line_1);

        $x_labels = new x_axis_labels();
        $x_labels->set_steps(2);
        $x_labels->set_vertical();
        $x_labels->set_colour('#000000');
        $x_labels->set_size(12);
        $x_labels->set_labels($labes);

        $x = new x_axis();
        $x->set_colours('#A2ACBA', '#ECFFAF');
        $x->set_steps(2);

        $x->set_labels($x_labels);
        $chart->set_x_axis($x);

        $y = new y_axis();
        $y->set_steps(2);
        $y->set_colour('#A2ACBA');
        $y->set_range(0, max($counts) + 5 , 50);

        $chart->add_y_axis($y);

        $cont = $chart->toPrettyString();
        // toFile($_SERVER["DOCUMENT_ROOT"]."/chart/","chart.json",$cont,false);
        // unset($cont);
        $html = "<script type=\"text/javascript\" src=\"chart/js/json/json2.js\"></script>";
        $html .= "<script type=\"text/javascript\" src=\"chart/js/swfobject.js\"></script>";
        $html .= "<script type=\"text/javascript\">

				function open_flash_chart_data()
				{
				return JSON.stringify(data);
				}

				function findSWF(movieName) {
				  if (navigator.appName.indexOf(\"Microsoft\")!= -1) {
					return window[movieName];
				  } else {
					return document[movieName];
				  }
				}

				var data = " . $cont . ";

					  swfobject.embedSWF(\"chart/open-flash-chart.swf\", \"my_chart\", \"800\", \"" . (max($counts) * 5 < 200 ? "250" : (max($counts) * 5 > 400 ? "400" : max($counts) * 5)) . "\", \"9.0.0\", \"expressInstall.swf\", {\"loading\":\"Please wait while the stats are loaded!\"} );
					 </script>";
        $html .= "<div id=\"my_chart\"></div>";
    } else {
        $html .= "No results found\n" ;
    }

    print $html . "<br />";
}
// -----------------------------------------
// | Date selection screen
// -----------------------------------------
function main_screen($mode = 'reg')
{
    global $month_names;

    $page_title = "Statistic Center";

    $page_detail = "Please define the date ranges and other options below.<br>Note: The statistics generated are based on the information currently held in the database, they do not take into account pruned forums or delete posts, etc.";

    if ($mode == 'reg') {
        $form_code = 'show_reg';

        $table = 'Registration Statistics<br />';
    } else if ($mode == 'rate') {
        $form_code = 'show_rate';

        $table = 'Rating Statistics';
    } else if ($mode == 'post') {
        $form_code = 'show_post';

        $table = 'Post Statistics';
    } else if ($mode == 'msg') {
        $form_code = 'show_msg';

        $table = 'PM Statistics';
    } else if ($mode == 'torr') {
        $form_code = 'show_torr';

        $table = 'Torrent Statistics';
    } else if ($mode == 'bans') {
        $form_code = 'show_bans';

        $table = 'Ban Statistics';
    } else if ($mode == 'comm') {
        $form_code = 'show_comm';

        $table = 'Comment Statistics';
    } else if ($mode == 'new') {
        $form_code = 'show_new';

        $table = 'News Statistics';
    } else if ($mode == 'poll') {
        $form_code = 'show_poll';

        $table = 'Polls Statistics';
    } else if ($mode == 'rqst') {
        $form_code = 'show_rqst';

        $table = 'Request Statistics';
    }

    $old_date = getdate(time() - (3600 * 24 * 90));
    $new_date = getdate(time() + (3600 * 24));
    // -----------------------------------------
    $html = "<table id=torrenttable border=1><tr><td>$table</td></tr>";
    $html .= "<tr><td>$page_title<br />$page_detail</td></tr>";
    $html .= start_form(array(1 => array('code' , $form_code),
            2 => array('act' , 'stats'),
            ));
    // -----------------------------------------
    // Naaaaaaaaaaaah!!
    // $td_header = array();
    // $td_header[] = array( "&nbsp;"  , "40%" );
    // $td_header[] = array( "&nbsp;"  , "60%" );
    // -----------------------------------------
    $html .= "<tr><td><br /><b>Date From</b>" . form_dropdown("from_month" , make_month(), $old_date['mon']) . '&nbsp;&nbsp;' .
    form_dropdown("from_day" , make_day() , $old_date['mday']) . '&nbsp;&nbsp;' .
    form_dropdown("from_year" , make_year() , $old_date['year'])
     . "<br /></td></tr>";

    $html .= "<tr><td><br /><b>Date To</b>" . form_dropdown("to_month" , make_month(), $new_date['mon']) . '&nbsp;&nbsp;' .
    form_dropdown("to_day" , make_day() , $new_date['mday']) . '&nbsp;&nbsp;' . form_dropdown("to_year" , make_year() , $new_date['year']) . "<br /></td></tr>";

    if ($mode != 'views') {
        $html .= "<tr><td><br /><b>Time scale</b>" . form_dropdown("timescale" , array(0 => array('daily', 'Daily'), 1 => array('weekly', 'Weekly'), 2 => array('monthly', 'Monthly'))) . "<br /></td></tr>";
    }

    $html .= "<tr><td><br /><b>Result Sorting</b>" . form_dropdown("sortby" , array(0 => array('asc', 'Ascending - Oldest dates first'), 1 => array('desc', 'Descending - Newest dates first')), 'desc') . "<br /></td></tr>";

    $html .= end_form("Show") . "</table>";

    print $html;
}
// -----------------------------------------
function make_year()
{
    $time_now = getdate();

    $return = array();

    $start_year = 2002;

    $latest_year = intval($time_now['year']);

    if ($latest_year == $start_year) {
        $start_year -= 1;
    }

    for ($y = $start_year; $y <= $latest_year; $y++) {
        $return[] = array($y, $y);
    }

    return $return;
}
// -----------------------------------------
function make_month()
{
    global $month_names;
    reset($month_names);
    $return = array();

    for ($m = 1 ; $m <= 12; $m++) {
        $return[] = array($m, $month_names[$m]);
    }

    return $return;
}
// -----------------------------------------
function make_day()
{
    $return = array();

    for ($d = 1 ; $d <= 31; $d++) {
        $return[] = array($d, $d);
    }

    return $return;
}

stdfoot();

?>
