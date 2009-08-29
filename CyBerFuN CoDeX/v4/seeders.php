<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) 
ob_start("ob_gzhandler"); 
else 
ob_start();
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
stdhead("Seeders");
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_UPLOADER)
{
stdmsg("Get Out",false);

stdfoot();
exit;
}
?>
<style type="text/css">
.sortable {width:400px; border-left:1px solid #000; border-top:1px solid #000; border-bottom:none; margin:0 auto 15px}
.sortable th {background:#242424; text-align:left; color:#FFF; border-right:none;height:20px;padding:3px;cursor:pointer;}
.sortable th h3 {font-size:11px; }
.sortable td {padding:4px 6px 6px; border-bottom:1px solid #000; border-right:1px solid #000}
.sortable .head h3 {background:url(images/sort.gif) 7px center no-repeat; cursor:pointer; padding-left:18px}
.sortable .desc, .sortable .asc {background:#365766;color:#CC571A;}
.sortable .desc h3 {background:url(images/desc.gif) 7px center no-repeat; cursor:pointer; padding-left:18px}
.sortable .asc h3 {background:url(images/asc.gif) 7px  center no-repeat; cursor:pointer; padding-left:18px}
.sortable .head:hover, .sortable .desc:hover, .sortable .asc:hover {color:#fff}
.sortable .evenrow td {background:#fff}
.sortable .oddrow td {background:#ecf2f6}
.sortable td.evenselected {background:#ecf2f6}
.sortable td.oddselected {background:#dce6ee}

#controls {width:700px; margin:0 auto; height:20px}
#perpage {float:left; width:200px}
#perpage select {float:left; font-size:11px}
#perpage span {float:left; margin:2px 0 0 5px}
#navigation {float:left; width:280px; text-align:center}
#navigation img {cursor:pointer}
#text {float:left; width:200px; text-align:right; margin-top:2px}
#currentpage {font-weight:bold;}
#pagelimit {font-weight:bold;}
</style>
<?

echo '<table id="table" class="sortable">
	<thead>
		<tr>
			<th><h3>Most Seeding</h3></th>
			<th><h3>Username</h3></th>
			<th><h3>Torrents seeding</h3></th>
		</tr>
	</thead><tbody>';
//seeders
$afla_seeder = @sql_query("SELECT A.id, A.username, A.status, B.seeder, count(B.userid) as count FROM users A 
INNER JOIN peers B ON A.id=B.userid WHERE A.status='confirmed' AND B.seeder = 'yes' GROUP BY A.id ORDER BY count(B.userid) Desc") or sqlerr(__FILE__, __LINE__);
//echo '&nbsp;(<b>'.mysql_num_rows($afla_seeder).'</b>)</center>';
//echo '<br />';
if(@mysql_num_rows($afla_seeder)>0)
{
$n = 1;
while($rand=@mysql_fetch_array($afla_seeder))
{
echo "<tr><td><b><span style=\"color:#CC571A;\">$n</span></b></td><td><a href=userdetails.php?id=" . $rand["id"] . "><b>" . $rand["username"] . "</a></td><td><b><span style=\"color:#365766;\">".$rand["count"]."</span></b></td>";
++$n;
}
}
else
{
echo "No seeders";
}
echo '</tr></tbody></table>';
$total = mysql_num_rows($afla_seeder);
echo '<center><b>'.$total.' seeders</b></center><br />';
?>
<div id="controls">
		<div id="perpage">
			<select onchange="sorter.size(this.value)">
			<option value="5">5</option>
				<option value="10" selected="selected">10</option>
				<option value="20">20</option>
				<option value="50">50</option>
				<option value="100">100</option>
			</select>
			<span>Entries Per Page</span>
		</div>
		<div id="navigation">
			<img src="images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" />
			<img src="images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" />
			<img src="images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" />
			<img src="images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" />
		</div>
		<div id="text">Displaying Page <span id="currentpage"></span> of <span id="pagelimit"></span></div>
	</div>
	<script type="text/javascript" src="packed.js"></script>
	<script type="text/javascript">
  var sorter = new TINY.table.sorter("sorter");
	sorter.head = "head";
	sorter.asc = "asc";
	sorter.desc = "desc";
	sorter.even = "evenrow";
	sorter.odd = "oddrow";
	sorter.evensel = "evenselected";
	sorter.oddsel = "oddselected";
	sorter.paginate = true;
	sorter.currentid = "currentpage";
	sorter.limitid = "pagelimit";
	sorter.init("table",1);
  </script>
<?
stdfoot();
?>
