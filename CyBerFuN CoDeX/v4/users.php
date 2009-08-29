<?php
require "include/bittorrent.php";
require "include/bbcode_functions.php";
dbconn();
maxcoder();
parked();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}


stdhead("Users");


$res = mysql_query("SELECT * FROM users WHERE username LIKE 'a%' ORDER BY username LIMIT 50");

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
      $country = "<td style='padding: 0px' align=center><img src=\"{$pic_base_url}flag/{$carr[flagpic]}\" alt=\"". htmlspecialchars($carr[name]) ."\"></td>";
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
    "<td align=left>" . get_user_class_name($arr["class"]) . "</td></tr>\n";
}
$ut .= "</table>\n";

?>
<script language="JavaScript">
  
var lasttext = "";

function usearch(hehe) {
  
  var texxt = document.getElementById("usearch").value;

  if(texxt != lasttext || hehe == 1)
  {
    document.getElementById("loading").innerHTML = '<img src="pic/loading.gif" width="16" height="16">';
    lasttext = texxt;
    window.location.href = '#usearch=' + escape(texxt);
    
    try{
    uajax.abort()
    }
    catch(e){
    }

    var url = 'ajax_user.php?text=' + escape(texxt) + '&rand=' + Math.random();;
    if(window.XMLHttpRequest)
    {
      uajax = new XMLHttpRequest();
    }
    else
    {
      uajax = new ActiveXObject("Microsoft.XMLHTTP");
    }
    uajax.open("GET", url, true);    
    uajax.onreadystatechange = ugo;
    uajax.send(null);
    
	}
	
	
}

function ugo() {
  if (uajax.readyState == 4) {
	  if (uajax.status == 200) {
		
      var urespons = uajax.responseText;
      document.getElementById("userdiv").innerHTML = urespons;
      
      document.getElementById("loading").innerHTML = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	  }
  }
}
</script>
<h1>Users</h1>
<table>
<tr><td class="clear2" valign="top" height="20">
Search: <input type="text" size="30" name="usearch" onkeyup="usearch();" id="usearch"></td>
<td class="clear2" valign="top" width="30">&nbsp;&nbsp;<var id="loading"></var></td></tr></table>
<br>

<div id="userdiv"><?=$ut?></div>

<script type="text/javascript">
var url = window.location.href;
var pos = url.indexOf('#usearch=');
if(pos > -1)
{
  var ord = url.substr(pos+9);

  if(ord.length > 1)
  {
    document.getElementById('usearch').value = ord;
    usearch(ord);
  }
}
</script>

<?php
stdfoot();
?>