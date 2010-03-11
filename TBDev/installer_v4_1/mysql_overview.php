<?php
/* $Id: mysql_overview.php,v 1.01 2005/08/05 19:11:48 CoLdFuSiOn Exp $ */
// vim: expandtab sw=4 ts=4 sts=4:
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
/**
* Checks if the user is allowed to do what he tries to...
*/
if (get_user_class() < UC_SYSOP)
    hacker_dork("Mysql Overview - Nosey Cunt !");
// Do we wanna continue here, or skip to just the overview?
if (isset($_GET['Do']) && isset($_GET['table'])) {
    $Do = ($_GET['Do'] === "T") ? sqlesc($_GET['Do']) : ""; //for later use!
    // Make sure the GET only has alpha letters and nothing else
    if (!ereg('[^A-Za-z_]+', $_GET['table'])) {
        $Table = '`' . $_GET['table'] . '`'; //add backquotes to GET or we is doomed!
    } else {
        print("Pig Dog!"); //Silly boy doh!!
        exit;
    }

    $sql = "OPTIMIZE TABLE $Table";
    // preg match the sql incase it was hijacked somewhere!(will use CHECK|ANALYZE|REPAIR|later
    if (preg_match('@^(CHECK|ANALYZE|REPAIR|OPTIMIZE)[[:space:]]TABLE[[:space:]]' . $Table . '$@i', $sql)) {
        // all good? Do it!
        @mysql_query($sql) or die("<b>Something was not right!</b>.\n<br />Query: " . $sql . "<br />\nError: (" . mysql_errno() . ") " . htmlspecialchars(mysql_error()));
        // all done, redirect back to calling page
        $return_url = "mysql_overview.php?Do=F";
        header("Location: http://" . $_SERVER['HTTP_HOST']
             . dirname($_SERVER['PHP_SELF'])
             . "/" . $return_url);
        exit;
    }
}
// byteunit array to prime formatByteDown function
$GLOBALS["byteUnits"] = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
// //////////////// FUNCTION LIST /////////////////////////
function formatByteDown($value, $limes = 2, $comma = 0)
{
    $dh = pow(10, $comma);
    $li = pow(10, $limes);
    $return_value = $value;
    $unit = $GLOBALS['byteUnits'][0];

    for ($d = 6, $ex = 15; $d >= 1; $d--, $ex -= 3) {
        if (isset($GLOBALS['byteUnits'][$d]) && $value >= $li * pow(10, $ex)) {
            $value = round($value / (pow(1024, $d) / $dh)) / $dh;
            $unit = $GLOBALS['byteUnits'][$d];
            break 1;
        } // end if
    } // end for
    if ($unit != $GLOBALS['byteUnits'][0]) {
        $return_value = number_format($value, $comma, '.', ',');
    } else {
        $return_value = number_format($value, 0, '.', ',');
    }

    return array($return_value, $unit);
} // end of the 'formatByteDown' function
// //////////////// END FUNCTION LIST /////////////////////////
stdhead("Stats");

/**
* Displays the sub-page heading
*/
echo '<h2>' . "\n"
 . ' Mysql Server Table Status' . "\n"
 . '</h2>' . "\n";

?>

<!-- Start table -->

<table id="torrenttable" border="1" cellpadding="3">

<!-- Start table headers -->
<tr>


<th>Name</th>

<th>Size</th>

<th>Rows</th>

<th>Avg row length</th>

<th>Data length</th>

<!-- <th>Max_data_length</th> -->

<th>Index length</th>

<th>Overhead</th>

<!-- <th>Auto_increment</th> -->

<!-- <th>Timings</th> -->

</tr>

<!-- End table headers -->

<?php
$count = 0;
/**
* Sends the query and buffers the result
*/
$res = @mysql_query('SHOW TABLE STATUS FROM `' . $mysql_db . '`') or Die(mysql_error());
while ($row = mysql_fetch_array($res)) {
    list($formatted_Avg, $formatted_Abytes) = formatByteDown($row['Avg_row_length']);
    list($formatted_Dlength, $formatted_Dbytes) = formatByteDown($row['Data_length']);
    list($formatted_Ilength, $formatted_Ibytes) = formatByteDown($row['Index_length']);
    list($formatted_Dfree, $formatted_Fbytes) = formatByteDown($row['Data_free']);
    $tablesize = ($row['Data_length']) + ($row['Index_length']);
    list($formatted_Tsize, $formatted_Tbytes) = formatByteDown($tablesize, 3, ($tablesize > 0) ? 1 : 0);

    $thispage = "?Do=T&table=" . $row['Name'];
    $overhead = ($formatted_Dfree > 0) ? "<a href=mysql_overview.php" . $thispage . "><font color='red'><b>" . $formatted_Dfree . " " . $formatted_Fbytes . "</b></font></a>" : $formatted_Dfree . " " . $formatted_Fbytes;

    echo "<tr align=\"right\"><td align=\"left\">{$row['Name']}</td>" . "<td>{$formatted_Tsize} {$formatted_Tbytes}</td>" . "<td>{$row['Rows']}</td>" . "<td>{$formatted_Avg} {$formatted_Abytes}</td>" . "<td>{$formatted_Dlength} {$formatted_Dbytes}</td>" . "<td>{$formatted_Ilength} {$formatted_Ibytes}</td>" . "<td>{$overhead}</td></tr>" . "<tr><td colspan=\"7\" align=\"right\"><i><b>Row Format:</b></i> {$row['Row_format']}" . "<br /><i><b>Create Time:</b></i> {$row['Create_time']}" . "<br /><i><b>Update Time:</b></i> {$row['Update_time']}" . "<br /><i><b>Check Time:</b></i> {$row['Check_time']}</td></tr>";
    // do sums
    $count++;
} //end while
echo "<tr><td><b>Tables: {$count}</b></td><td colspan=\"6\" align=\"right\">If it's <font color=\"red\"><b>RED</b></font> it probably needs optimising!!</td></tr>";

?>
<!-- End table -->
</table>

<?php
stdfoot();

?>