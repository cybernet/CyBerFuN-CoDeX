<?php
require ("include/bittorrent.php");
require_once("include/bbcode_functions.php");
header("Content-Type: text/html; charset=iso-8859-1");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

$nick = unsafeChar(trim($_GET["text"]));

if(strlen($nick) < 1)
$nick = "a";

$res = mysql_query("SELECT * FROM users WHERE username LIKE '$nick%' ORDER BY username LIMIT 50");

$count = mysql_num_rows($res);

$num = mysql_num_rows($res);

$ut .= "<table border=1 cellspacing=0 cellpadding=5>\n";
$ut .= "<tr><td class=colhead align=left>Username</td><td class=colhead>Registered</td><td class=colhead>Last logged in</td><td class=colhead>Country</td><td class=colhead align=left>Class</td></tr>\n";
for ($i = 0; $i < $num; ++$i)
{
  $arr = mysql_fetch_assoc($res);
  if ($arr['country'] > 0)
  {
    $cres = mysql_query("SELECT name,flagpic FROM countries WHERE id=$arr[country]");
    if (mysql_num_rows($cres) == 1)
    {
      $carr = mysql_fetch_assoc($cres);
      $country = "<td style='padding: 0px' align=center><img src=\"{$pic_base_url}flag/{$carr[flagpic]}\" alt=\"". safeChar($carr[name]) ."\"></td>";
    }
  }
  else
    $country = "<td align=center>---</td>";
  if ($arr['added'] == '0000-00-00 00:00:00')
    $arr['added'] = '-';
  if ($arr['last_access'] == '0000-00-00 00:00:00')
    $arr['last_access'] = '-';
  $ut .= "<tr><td align=left><a href=userdetails.php?id=$arr[id]><b>$arr[username]</b></a>" .($arr["donated"] > 0 ? "<img src=/pic/star.gif border=0 alt='Donor'>" : "")."</td>" .
  "<td>$arr[added]</td><td>$arr[last_access]</td>$country".
    "<td align=left>" . get_user_class_name($arr["class"]). "</td></tr>\n";
}
$ut .= "</table>\n";
$ut .= "<br>" . $count ." Members found";

echo $ut;
?>