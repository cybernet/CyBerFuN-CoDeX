<?php
require_once('include/bittorrent.php');
require_once('include/user_functions.php');
require_once('include/bbcode_functions.php');
dbconn(false);
// ID list - Add individual user IDs to this list for access to this script
$allowed_ID = array(1); // coder = 1

// Written by RetroKill to allow scripters to see what scripts have changed since
// they last updated their own list.
//
// This script will create a unique list for each member allowed to access this
// script. It allows them to see what scripts have been updated since they last
// updated their own list, allowing scripters to work together better.
//
// The first run will produce no results, as it will initialise the list for the
// member running the script. Further runs will show the scripter when a script
// has been updated from their original list (someone else, or they, have modified
// a script). When a member updates a script, they should run this script, which
// will show the update, then update their list using the update button, to bring
// their list up to date. If an update appears when the scripter hasn't made any
// changes, then they know that another scripter has modified a script.


unset ($flag);
foreach ($allowed_ID as $x)
if ($CURUSER['id'] == $x) $flag = 1;

if(!logged_in() OR !isset($flag))
noPage();

$file_data = './dir_list/data_'.$CURUSER['username'].'.txt';

if (file_exists($file_data))
{
// Fetch existing data
$data = unserialize(file_get_contents($file_data));
$exist = TRUE;
}
else
{
// Initialise File
$exist = FALSE;
}

$fetch_set = array();

$i=0;

$directories = array();

// Enter directories to log...
$directories[] = './'; // Webroot
$directories[] = './include/';
$directories[] = './bitbucket/';
$directories[] = './avatars/';
$directories[] = './themes/';
$directories[] = './include/cache/';
$directories[] = './cache/';
$directories[] = './dictbreaker/';
$directories[] = './logs/';
$directories[] = './torrents/';
$directories[] = './settings/';
foreach ($directories AS $x)
{
if ($handle = opendir($x)) {
while (false !== ($file = readdir($handle))) {
if ($file != "." && $file != "..") {

if (!is_dir($x.'/'.$file)) {

$fetch_set[$i]['modify'] = filemtime($x.$file);
$fetch_set[$i]['size'] = filesize($x.$file);
$fetch_set[$i]['name'] = $x.$file;
$fetch_set[$i]['key'] = $i;

$i++;
}
}
}
closedir($handle);
}
}

if (!$exist OR (isset($_POST['update']) AND ($_POST['update'] == 'Update')))
{
// Create first disk image of files
// OR update existing data...
$data = serialize($fetch_set);
$handle = fopen($file_data,"w");
fputs($handle, $data);
fclose($handle);
$data = unserialize($data);
}

// We now need to link current contents with stored contents.

reset($fetch_set);
reset($data);

$current = $fetch_set;
$last = $data;

foreach($current as $x)
{
// Search the data sets for differences
foreach ($last AS $y)
{
if ($x['name'] == $y['name'])
{
if (($x['size'] == $y['size']) AND ($x['modify'] == $y['modify']))
{
unset ($current[$x['key']]);
unset ($last[$y['key']]);
}
else
$current[$x['key']]['status'] = 'modified';
}
if (isset($last[$y['key']])) $last[$y['key']]['status'] = 'deleted';
}
if (isset($current[$x['key']]['name']) AND
!isset($current[$x['key']]['status'])) $current[$x['key']]['status'] = 'new';
}

$current += $last; // Add deleted entries to current list

unset ($last);

// $fetch_data contains a current list of directory
// $data contains the last snapshot of the directory
// $current contains a current list of files in the directory that are
// new, modified or deleted...

// Remove lists from current code...
unset ($data);
unset ($fetch_set);

stdhead();
?>
<table width='750' border='1' cellspacing='2' cellpadding='5' align=center>
<br />
<tr>
<td align=center width='70%' bgcolor='orange'><strong>New files added since last check</strong></td>
<td align=center bgcolor='orange'><strong>Added</strong></td>
</tr>
<?php

reset($current);
$count = 0;
foreach ($current AS $x)
{
if ($x['status'] == 'new')
{
?>
<tr>
<td align=center><?php print(safeChar(substr($x['name'],2)));?></td>
<td align=center><?php print(get_date_time($x['modify']));?></td>
</tr>
<?php
$count++;
}
}
if (!$count)
{
?>
<tr>
<td align=center colspan='2'><b>No new files added since last check.</b></td>
</tr>
<?php
}

?>
</table>
<br /><br /><br />
<table width='750' border='1' cellspacing='2' cellpadding='5' align=center>
<br />
<tr>
<td align=center width='70%' bgcolor='orange'><strong>Modified files since last check</strong></td>
<td align=center bgcolor='orange'><strong>Modified</strong></td>
</tr>
<?php

reset($current);
$count = 0;
foreach ($current AS $x)
{
if ($x['status'] == 'modified')
{
?>
<tr>
<td align=center><?php print(safeChar(substr($x['name'],2)));?></td>
<td align=center><?php print(get_date_time($x['modify']));?></td>
</tr>
<?php
$count++;
}
}
if (!$count)
{
?>
<tr>
<td align=center colspan='2'><b>No files modified since last check.</b></td>
</tr>
<?php
}

?>
</table>
<br /><br /><br />
<table width='750' border='1' cellspacing='2' cellpadding='5' align=center>
<br />
<tr>
<td align=center width='70%' bgcolor='orange'><strong>Files deleted since last check</strong></td>
<td align=center bgcolor='orange'><strong>Deleted</strong></td>
</tr>
<?php

reset($current);
$count = 0;
foreach ($current AS $x)
{
if ($x['status'] == 'deleted')
{
?>
<tr>
<td align=center><?php print(safeChar(substr($x['name'],2)));?></td>
<td align=center><?php print(get_date_time($x['modify']));?></td>
</tr>
<?php
$count++;
}
}
if (!$count)
{
?>
<tr>
<td align=center colspan='2'><b>No files deleted since last check.</b></td>
</tr>
<?php
}

?>
</table>
<br /><br /><br />
<form method=post action=?>
<table width='750' border='1' cellspacing='2' cellpadding='5' align=center>
<tr>
<td align=center bgcolor='orange'>
<input name='update' type='submit' value='Update' />
</td>
</tr>
</table>
</form>
<?php

stdfoot();
?>