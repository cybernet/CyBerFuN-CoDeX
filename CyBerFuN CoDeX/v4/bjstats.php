<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
dbconn(false);
maxcoder();	
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
stdhead("Blackjack Stats");

if (get_user_class() < UC_POWER_USER)
{
	stdmsg("Sorry...", "You must be a Power User or above to play Blackjack.");
	stdfoot();
	exit;
}

$mingames = 100;

print("<center><h1>Blackjack Stats</h1></center>");

//print("<br />");

print("<center>Stats are cached and updated every 30 minutes. You need to play at least $mingames games to be included.</center>");

print("<br />");

// BEGIN CACHE ///////////////////////////////////////////////////////////

     $cachefile = "$CACHE/bjstats.txt";
     $cachetime = 60 * 30; // 30 minutes
     // Serve from the cache if it is younger than $cachetime
     if (file_exists($cachefile) && (time() - $cachetime
        < filemtime($cachefile)))
     {
        include($cachefile);
        print("<p align=center><font class=small>This page last updated ".date('Y-m-d H:i:s', filemtime($cachefile)).". </font></p>");

        end_main_frame();
        stdfoot();

        exit;
     }
     ob_start(); // start the output buffer

/////////////////////////////////////////////////////////////////////////

function bjtable($res, $frame_caption)
{
	begin_frame($frame_caption, true);
	begin_table();
	?>
<tr>
<td class="colhead">Rank</td>
<td align="left" class="colhead">User</td>
<td align="right" class="colhead">Wins</td>
<td align="right" class="colhead">Losses</td>
<td align="right" class="colhead">Games</td>
<td align="right" class="colhead">Percentage</td>
<td align="right" class="colhead">Win/Loss</td>
</tr>
<?php
	$num = 0;
	while ($a = mysql_fetch_assoc($res))
	{
		++$num;
		
		//Calculate Win %
		$win_perc = number_format(($a[wins] / $a[games]) * 100, 1);
		
		// Add a user's +/- statistic
		$plus_minus = $a[wins] - $a[losses];
		if ($plus_minus >= 0)
		{
			$plus_minus = mksize(($a[wins] - $a[losses]) * 100*1024*1024);
		}
		else
		{
			$plus_minus = "-";
			$plus_minus .= mksize(($a[losses] - $a[wins]) * 100*1024*1024);
		}
		
		print("<tr><td>$num</td><td align=left><table border=0 class=main cellspacing=0 cellpadding=0><tr><td class=embedded>".
		"<b><a href=userdetails.php?id=" . $a[id] . ">" . $a[username] . "</a></b></td>".
		"</tr></table></td><td align=right>" . number_format($a[wins], 0) . "</td>".
		"</td><td align=right>" . number_format($a[losses], 0) . "</td>".
		"</td><td align=right>" . number_format($a[games], 0) . "</td>".
		"</td><td align=right>$win_perc</td>".
		"</td><td align=right>$plus_minus</td>".
		"</tr>\n");
	}
	end_table();
	end_frame();
}

// Most Games Played
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games FROM users WHERE bjwins + bjlosses > $mingames ORDER BY games DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);

bjtable($res, "<center>Most Games Played</center>","Users");

print("<br /><br />");
// /Most Games Played

// Highest Win %
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins / (bjwins + bjlosses) AS winperc FROM users WHERE bjwins + bjlosses > $mingames ORDER BY winperc DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);

bjtable($res, "<center>Highest Win Percentage</center>","Users");

print("<br /><br />");
// /Highest Win %

// Most Credit Won
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins - bjlosses AS winnings FROM users WHERE bjwins + bjlosses > $mingames ORDER BY winnings DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);

bjtable($res, "<center>Most Credit Won</center>","Users");

print("<br /><br />");
// /Most Credit Won

// Most Credit Lost
$res = mysql_query("SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjlosses - bjwins AS losings FROM users WHERE bjwins + bjlosses > $mingames ORDER BY losings DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);

bjtable($res, "<center>Most Credit Lost</center>","Users");

// /Most Credit Lost

// CACHE END ////////////////////////////////////////////////////////////

      // open the cache file for writing      
      $fp = fopen($cachefile, 'w');
      // save the contents of output buffer to the file    
      fwrite($fp, ob_get_contents());
      // close the file
       fclose($fp);
       // Send the output to the browser
       ob_end_flush();

/////////////////////////////////////////////////////////////////////////

print("<br /><br />");

stdfoot();
?>