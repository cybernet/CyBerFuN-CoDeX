<?php
require ("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(true);
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

if ($usergroups['cantopten'] == 'no' OR $usergroups['cantopten'] != 'yes') {
stderr( "Sorry...", "You dont have access to this page" );
	exit;
}

stdhead("Top 10");
function mysql_fetch_rowsarr($result, $numass=MYSQL_BOTH) {
  $i=0;
  $keys=array_keys(mysql_fetch_array($result, $numass));
  mysql_data_seek($result, 0);
    while ($row = mysql_fetch_array($result, $numass)) {
      foreach ($keys as $speckey) {
        $got[$i][$speckey]=$row[$speckey];
      }
    $i++;
    }
  return $got;
}
/*
chs		=	widthxheight (adjust if needed)
chco	=	chart colours, a,b,a,b,a,b,a,b
chf		=	7d7d7d = background colour, adjust to your theme background colour

Installer theme colours;
darkblue	=	0b0b0b
large Text	=	ffffff
dotBlue		=	7d7d7d
NB-Revolt	=	000000
default		=	000000
(Aoi)		=	0e0e0f
TheWall		=	373737
*/
$imgstartbar	=	"<img src=\"
http://chart.apis.google.com/chart?
cht=bvg
&chbh=a
&chs=780x300
&chco=4D89F9,4D89F9
&chf=bg,s,000000";
$imgstartpie	=	"<img src=\"
http://chart.apis.google.com/chart?
cht=p3
&chbh=a
&chs=780x300
&chco=4D89F9
&chf=bg,s,000000";
?>
<p><a href="topten.php">Users</a> | <a href="topten.php?view=t">Torrents</a> | <a href="topten.php?view=c">Countries</a></p>
<?php
	if ($_GET['view'] == "t"){
	$view = strip_tags($_GET["t"]);
// Top Torrents
echo "<h2>Top 10 Most Active Torrents</h2>";
	  $result		=	 mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$tor1		=	$arr[0]["name"];
		$tot1		=	$arr[0]["leechers"] + $arr[0]["seeders"];	
		$tor2		=	$arr[1]["name"];
		$tot2		=	$arr[1]["leechers"] + $arr[1]["seeders"];	
		$tor3		=	$arr[2]["name"];
		$tot3		=	$arr[2]["leechers"] + $arr[2]["seeders"];	
		$tor4		=	$arr[3]["name"];
		$tot4		=	$arr[3]["leechers"] + $arr[3]["seeders"];	
		$tor5		=	$arr[4]["name"];
		$tot5		=	$arr[4]["leechers"] + $arr[4]["seeders"];	
		$tor6		=	$arr[5]["name"];
		$tot6		=	$arr[5]["leechers"] + $arr[5]["seeders"];	
		$tor7		=	$arr[6]["name"];
		$tot7		=	$arr[6]["leechers"] + $arr[6]["seeders"];	
		$tor8		=	$arr[7]["name"];
		$tot8		=	$arr[7]["leechers"] + $arr[7]["seeders"];	
		$tor9		=	$arr[8]["name"];
		$tot9		=	$arr[8]["leechers"] + $arr[8]["seeders"];	
		$tor10		=	$arr[9]["name"];
		$tot10		=	$arr[9]["leechers"] + $arr[9]["seeders"];	
echo "$imgstartpie
&chd=t:$tot1,$tot2,$tot3,$tot4,$tot5,$tot6,$tot7,$tot8,$tot9,$tot10
&chl=$tor1($tot1)|$tor2($tot2)|$tor3($tot3)|$tor4($tot4)|$tor5($tot5)|$tor6($tot6)|$tor7($tot7)|$tor8($tot8)|$tor9($tot9)|$tor10($tot10)\" />";	
	  } else {
		echo "<h4>Insufficiant Torrents (" . $counted . ")</h4>";  
	  }
echo "<br /><br /><h2>Top 10 Most Snatched Torrents</h2>";
	  $result		=	 mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY times_completed DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$tor1		=	$arr[0]["name"];
		$tot1		=	$arr[0]["times_completed"];	
		$tor2		=	$arr[1]["name"];
		$tot2		=	$arr[1]["times_completed"];	
		$tor3		=	$arr[2]["name"];
		$tot3		=	$arr[2]["times_completed"];	
		$tor4		=	$arr[3]["name"];
		$tot4		=	$arr[3]["times_completed"];	
		$tor5		=	$arr[4]["name"];
		$tot5		=	$arr[4]["times_completed"];	
		$tor6		=	$arr[5]["name"];
		$tot6		=	$arr[5]["times_completed"];	
		$tor7		=	$arr[6]["name"];
		$tot7		=	$arr[6]["times_completed"];	
		$tor8		=	$arr[7]["name"];
		$tot8		=	$arr[7]["times_completed"];	
		$tor9		=	$arr[8]["name"];
		$tot9		=	$arr[8]["times_completed"];	
		$tor10		=	$arr[9]["name"];
		$tot10		=	$arr[9]["times_completed"];	
echo "$imgstartpie
&chd=t:$tot1,$tot2,$tot3,$tot4,$tot5,$tot6,$tot7,$tot8,$tot9,$tot10
&chl=$tor1($tot1)|$tor2($tot2)|$tor3($tot3)|$tor4($tot4)|$tor5($tot5)|$tor6($tot6)|$tor7($tot7)|$tor8($tot8)|$tor9($tot9)|$tor10($tot10)\" />";	
	  } else {
		echo "<h4>Insufficiant Torrents (" . $counted . ")</h4>";  
	  }
	stdfoot();
	die();
	}
	if ($_GET['view'] == "c"){
	$view = strip_tags($_GET["c"]);
// Top Countries
echo "<h2>Top 10 Countries (users)</h2>";
	  $result		=	 mysql_query("SELECT name, flagpic, COUNT(users.country) as num FROM countries LEFT JOIN users ON users.country = countries.id GROUP BY name ORDER BY num DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$name1		=	$arr[0]["name"];
		$num1		=	$arr[0]["num"];	
		$name2		=	$arr[1]["name"];
		$num2		=	$arr[1]["num"];	
		$name3		=	$arr[2]["name"];
		$num3		=	$arr[2]["num"];	
		$name4		=	$arr[3]["name"];
		$num4		=	$arr[3]["num"];	
		$name5		=	$arr[4]["name"];
		$num5		=	$arr[4]["num"];	
		$name6		=	$arr[5]["name"];
		$num6		=	$arr[5]["num"];	
		$name7		=	$arr[6]["name"];
		$num7		=	$arr[6]["num"];	
		$name8		=	$arr[7]["name"];
		$num8		=	$arr[7]["num"];	
		$name9		=	$arr[8]["name"];
		$num9		=	$arr[8]["num"];	
		$name10		=	$arr[9]["name"];
		$num10		=	$arr[9]["num"];	
echo "$imgstartbar
&chds=0,$num1&chxr=1,0,$num1
&chd=t:$num1,$num2,$num3,$num4,$num5,$num6,$num7,$num8,$num9,$num10
&chxt=x,y,x&chxl=0:|$name1|$name2|$name3|$name4|$name5|$name6|$name7|$name8|$name9|$name10|2:|($num1)|($num2)|($num3)|($num4)|($num5)|($num6)|($num7)|($num8)|($num9)|($num10)\" />";	
	  } else {
		echo "<h4>Insufficiant Countries (" . $counted . ")</h4>";  
	  }
echo "<br /><br /><h2>Top 10 Countries (total uploaded)</h2>";
	  $result		=	 mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded) AS ul FROM users AS u LEFT JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name ORDER BY ul DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$name1		=	$arr[0]["name"];
		$num1		=	$arr[0]["ul"];	
		$name2		=	$arr[1]["name"];
		$num2		=	$arr[1]["ul"];	
		$name3		=	$arr[2]["name"];
		$num3		=	$arr[2]["ul"];	
		$name4		=	$arr[3]["name"];
		$num4		=	$arr[3]["ul"];	
		$name5		=	$arr[4]["name"];
		$num5		=	$arr[4]["ul"];	
		$name6		=	$arr[5]["name"];
		$num6		=	$arr[5]["ul"];	
		$name7		=	$arr[6]["name"];
		$num7		=	$arr[6]["ul"];	
		$name8		=	$arr[7]["name"];
		$num8		=	$arr[7]["ul"];	
		$name9		=	$arr[8]["name"];
		$num9		=	$arr[8]["ul"];	
		$name10		=	$arr[9]["name"];
		$num10		=	$arr[9]["ul"];	
echo "$imgstartbar
&chds=0,$num1&chxr=1,0,$num1
&chd=t:$num1,$num2,$num3,$num4,$num5,$num6,$num7,$num8,$num9,$num10
&chxt=x,y,x&chxl=0:|$name1|$name2|$name3|$name4|$name5|$name6|$name7|$name8|$name9|$name10|1:||||||||||" . prefixed($num1) . "|2:|(" . prefixed($num1) . ")|(" . prefixed($num2) . ")|(" . prefixed($num3) . ")|(" . prefixed($num4) . ")|(" . prefixed($num5) . ")|(" . prefixed($num6) . ")|(" . prefixed($num7) . ")|(" . prefixed($num8) . ")|(" . prefixed($num9) . ")|(" . prefixed($num10) . ")\" />";
	  } else {
		echo "<h4>Insufficiant Countries (" . $counted . ")</h4>";  
	  }
	stdfoot();
	die();
	}
// Default display / Top Users
echo "<br /><br /><h2>Top 10 Uploaders</h2>";
	$result 	=	mysql_query("SELECT username, uploaded FROM users WHERE enabled = 'yes' ORDER BY uploaded DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$user1	 	= $arr[0]['username'];
		$user2 		= $arr[1]['username'];
		$user3 		= $arr[2]['username'];
		$user4 		= $arr[3]['username'];
		$user5 		= $arr[4]['username'];
		$user6	 	= $arr[5]['username'];
		$user7 		= $arr[6]['username'];
		$user8	 	= $arr[7]['username'];
		$user9 		= $arr[8]['username'];
		$user10		= $arr[9]['username'];
		$upped1 	= $arr[0]['uploaded'];
		$upped2 	= $arr[1]['uploaded'];
		$upped3 	= $arr[2]['uploaded'];
		$upped4 	= $arr[3]['uploaded'];
		$upped5 	= $arr[4]['uploaded'];
		$upped6 	= $arr[5]['uploaded'];
		$upped7 	= $arr[6]['uploaded'];
		$upped8 	= $arr[7]['uploaded'];
		$upped9 	= $arr[8]['uploaded'];
		$upped10 	= $arr[9]['uploaded'];	
echo "$imgstartbar
&chds=0,$upped1&chxr=1,0,$upped1
&chd=t:$upped1,$upped2,$upped3,$upped4,$upped5,$upped6,$upped7,$upped8,$upped9,$upped10
&chxt=x,y,x&chxl=0:|$user1|$user2|$user3|$user4|$user5|$user6|$user7|$user8|$user9|$user10|1:||||||||||" . prefixed($upped1) . "|2:|(" . prefixed($upped1) . ")|(" . prefixed($upped2) . ")|(" . prefixed($upped3) . ")|(" . prefixed($upped4) . ")|(" . prefixed($upped5) . ")|(" . prefixed($upped6) . ")|(" . prefixed($upped7) . ")|(" . prefixed($upped8) . ")|(" . prefixed($upped9) . ")|(" . prefixed($upped10) . ")\" />";	
	  } else {
		echo "<h4>Insufficiant Uploaders (" . $counted . ")</h4>";  
	  }
echo "<br /><br /><h2>Top 10 Downloaders</h2>";
	$result 	=	mysql_query("SELECT username, downloaded FROM users WHERE enabled = 'yes' ORDER BY downloaded DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$user1	 	= $arr[0]['username'];
		$user2 		= $arr[1]['username'];
		$user3 		= $arr[2]['username'];
		$user4 		= $arr[3]['username'];
		$user5 		= $arr[4]['username'];
		$user6	 	= $arr[5]['username'];
		$user7 		= $arr[6]['username'];
		$user8	 	= $arr[7]['username'];
		$user9 		= $arr[8]['username'];
		$user10		= $arr[9]['username'];
		$upped1 	= $arr[0]['downloaded'];
		$upped2 	= $arr[1]['downloaded'];
		$upped3 	= $arr[2]['downloaded'];
		$upped4 	= $arr[3]['downloaded'];
		$upped5 	= $arr[4]['downloaded'];
		$upped6 	= $arr[5]['downloaded'];
		$upped7 	= $arr[6]['downloaded'];
		$upped8 	= $arr[7]['downloaded'];
		$upped9 	= $arr[8]['downloaded'];
		$upped10 	= $arr[9]['downloaded'];	
echo "$imgstartbar
&chds=0,$upped1&chxr=1,0,$upped1
&chd=t:$upped1,$upped2,$upped3,$upped4,$upped5,$upped6,$upped7,$upped8,$upped9,$upped10
&chxt=x,y,x&chxl=0:|$user1|$user2|$user3|$user4|$user5|$user6|$user7|$user8|$user9|$user10|1:||||||||||" . prefixed($upped1) . "|2:|(" . prefixed($upped1) . ")|(" . prefixed($upped2) . ")|(" . prefixed($upped3) . ")|(" . prefixed($upped4) . ")|(" . prefixed($upped5) . ")|(" . prefixed($upped6) . ")|(" . prefixed($upped7) . ")|(" . prefixed($upped8) . ")|(" . prefixed($upped9) . ")|(" . prefixed($upped10) . ")\" />";	
	  } else {
		echo "<h4>Insufficiant Downloaders (" . $counted . ")</h4>";  
	  }
echo "<br /><br /><h2>Top 10 Fastest Uploaders</h2>";
	$result 	=	mysql_query("SELECT  username, uploaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS upspeed FROM users WHERE enabled = 'yes' ORDER BY upspeed DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$user1	 	= $arr[0]['username'];
		$user2 		= $arr[1]['username'];
		$user3 		= $arr[2]['username'];
		$user4 		= $arr[3]['username'];
		$user5 		= $arr[4]['username'];
		$user6	 	= $arr[5]['username'];
		$user7 		= $arr[6]['username'];
		$user8	 	= $arr[7]['username'];
		$user9 		= $arr[8]['username'];
		$user10		= $arr[9]['username'];
		$upped1 	= $arr[0]['upspeed'];
		$upped2 	= $arr[1]['upspeed'];
		$upped3 	= $arr[2]['upspeed'];
		$upped4 	= $arr[3]['upspeed'];
		$upped5 	= $arr[4]['upspeed'];
		$upped6 	= $arr[5]['upspeed'];
		$upped7 	= $arr[6]['upspeed'];
		$upped8 	= $arr[7]['upspeed'];
		$upped9 	= $arr[8]['upspeed'];
		$upped10 	= $arr[9]['upspeed'];	
echo "$imgstartbar
&chds=0,$upped1&chxr=1,0,$upped1
&chd=t:$upped1,$upped2,$upped3,$upped4,$upped5,$upped6,$upped7,$upped8,$upped9,$upped10
&chxt=x,y,x&chxl=0:|$user1|$user2|$user3|$user4|$user5|$user6|$user7|$user8|$user9|$user10|1:||||||||||" . prefixed($upped1) . "/s|2:|(" . prefixed($upped1) . "/s)|(" . prefixed($upped2) . "/s)|(" . prefixed($upped3) . "/s)|(" . prefixed($upped4) . "/s)|(" . prefixed($upped5) . "/s)|(" . prefixed($upped6) . "/s)|(" . prefixed($upped7) . "/s)|(" . prefixed($upped8) . "/s)|(" . prefixed($upped9) . "/s)|(" . prefixed($upped10) . "/s)\" />";	
	  } else {
		echo "<h4>Insufficiant Uploaders (" . $counted . ")</h4>";  
	  }
echo "<br /><br /><h2>Top 10 Fastest Downloaders</h2>";
	$result 	=	mysql_query("SELECT username, downloaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS downspeed FROM users WHERE enabled = 'yes' ORDER BY downspeed DESC LIMIT 10");
	  $counted		=	mysql_num_rows($result);
	  if($counted	== "10"){
		$arr = mysql_fetch_rowsarr($result);
		$user1	 	= $arr[0]['username'];
		$user2 		= $arr[1]['username'];
		$user3 		= $arr[2]['username'];
		$user4 		= $arr[3]['username'];
		$user5 		= $arr[4]['username'];
		$user6	 	= $arr[5]['username'];
		$user7 		= $arr[6]['username'];
		$user8	 	= $arr[7]['username'];
		$user9 		= $arr[8]['username'];
		$user10		= $arr[9]['username'];
		$upped1 	= $arr[0]['downspeed'];
		$upped2 	= $arr[1]['downspeed'];
		$upped3 	= $arr[2]['downspeed'];
		$upped4 	= $arr[3]['downspeed'];
		$upped5 	= $arr[4]['downspeed'];
		$upped6 	= $arr[5]['downspeed'];
		$upped7 	= $arr[6]['downspeed'];
		$upped8 	= $arr[7]['downspeed'];
		$upped9 	= $arr[8]['downspeed'];
		$upped10 	= $arr[9]['downspeed'];	
echo "$imgstartbar
&chds=0,$upped1&chxr=1,0,$upped1
&chd=t:$upped1,$upped2,$upped3,$upped4,$upped5,$upped6,$upped7,$upped8,$upped9,$upped10
&chxt=x,y,x&chxl=0:|$user1|$user2|$user3|$user4|$user5|$user6|$user7|$user8|$user9|$user10|1:||||||||||" . prefixed($upped1) . "/s|2:|(" . prefixed($upped1) . "/s)|(" . prefixed($upped2) . "/s)|(" . prefixed($upped3) . "/s)|(" . prefixed($upped4) . "/s)|(" . prefixed($upped5) . "/s)|(" . prefixed($upped6) . "/s)|(" . prefixed($upped7) . "/s)|(" . prefixed($upped8) . "/s)|(" . prefixed($upped9) . "/s)|(" . prefixed($upped10) . "/s)\" />";	
	  } else {
		echo "<h4>Insufficiant Downloaders (" . $counted . ")</h4>";  
	  }
stdfoot();
?>