<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
maxcoder();	
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_ADMINISTRATOR)
hacker_dork("Secure Ip - Nosey Cunt !");

$remove = $_GET['remove'];
if (is_valid_id($remove))
{
  mysql_query("DELETE FROM ipsecureip WHERE id=$remove") or sqlerr();
  // write_log("Staff IP $remove was removed by $CURUSER[id] ($CURUSER[username])");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_ADMINISTRATOR)
{
        $first1 = trim($_POST["first"]);
        $last1 = trim($_POST["last"]);
        $inputip = safeChar($_POST["ipof"]);
        if (!$first1 || !$last1 || !$inputip)
                stderr("Error", "Missing form data.  Fill all Fields");
        $first = ip2long($first1);
        $last = ip2long($last1);
        if ($first == -1 || $last == -1)
                stderr("Error", "Bad IP address.");
        $doubleip = mysql_query("SELECT * FROM ipsecureip WHERE $first >= first AND $last <= last") or sqlerr(__FILE__, __LINE__);
   if (mysql_num_rows($doubleip) > 0){
   stderr("Error", "This IP is already in there");
   }
   else {

        $added = sqlesc(get_date_time());
        mysql_query("INSERT INTO ipsecureip (added, addedby, first, last, ipof) VALUES($added, $CURUSER[id], $first, $last, '$inputip')") or sqlerr(__FILE__, __LINE__);
        header("Location: $BASEURL$_SERVER[REQUEST_URI]");
        die;
        }
}

ob_start("ob_gzhandler");

$ipof = "<option value=system>Who's IP is this</option>\n";
$ipof2 = mysql_query("SELECT username FROM users WHERE class>=" . UC_MODERATOR) or die;
while ($ipof3 = mysql_fetch_array($ipof2))
  $ipof .= "<option name=$ipof3[username]>$ipof3[username]</option>\n";

$res = mysql_query("SELECT * FROM ipsecureip ORDER BY added DESC") or sqlerr();

stdhead("Staff IPs");

print("<h1>Current list of IPs for staff</h1>\n");

if (mysql_num_rows($res) == 0)
  print("<p align=center><b>Nothing found</b></p>\n");
else
{
////Change permission if you want here, allowed staff to add ip(s)////
 $remove1 = (get_user_class() >= UC_ADMINISTRATOR)?"<td class=colhead>Remove</td>":"";
  print("<table border=1 cellspacing=0 cellpadding=5>\n");
  print("<tr><td class=colhead>Added</td><td class=colhead align=left>First IP</td><td class=colhead align=left>Last IP</td>".
    "<td class=colhead align=left>IP of</td><td class=colhead align=left>Added By</td><td class=colhead>Type of IP</td>$remove1</tr>\n");

  while ($arr = mysql_fetch_assoc($res))
  {
          $r2 = mysql_query("SELECT username FROM users WHERE id=$arr[addedby]") or sqlerr(__FILE__, __LINE__);
          $a2 = mysql_fetch_assoc($r2);
          $r3 = mysql_query("SELECT id FROM users WHERE username = '$arr[ipof]'") or sqlerr(__FILE__, __LINE__);
          $a3 = mysql_fetch_assoc($r3);
        $arr1["first"] = long2ip($arr["first"]);
        $arr1["last"] = long2ip($arr["last"]);
         $addedby = ($arr[addedby] == 0)?"System":"<a href=userdetails.php?id=$arr[addedby]>$a2[username]";
         $status = ($arr[temp] == "yes")?"<font color=red>Temporary</font>":"Permanent";
         $remove = (get_user_class() >= UC_SYSOP)?"<td><a href=secureip.php?remove=$arr[id]>Remove</a></td>":"";
           print("<tr><td>$arr[added]</td><td align=left>$arr1[first]</td><td align=left>$arr1[last]</td><td align=left><a href=userdetails.php?id=$a3[id]>$arr[ipof]</a></td><td align=left>$addedby".
             "</a></td><td>$status</td>$remove</tr>\n");
  }
  print("</table>\n");
}
////Change permission if you want here, allowed staff to add ip(s)////
if (get_user_class() >= UC_ADMINISTRATOR)
{
        print("<h2>Add a Staffs IP</h2>\n");
        print("<table border=1 cellspacing=0 cellpadding=5>\n");
        print("<form method=post action=secureip.php>\n");
        print("<tr><td class=rowhead>First IP</td><td><input type=text name=first size=40></td>\n");
        print("<tr><td class=rowhead>Last IP</td><td><input type=text name=last size=40></td>\n");
        print("<tr><td class=rowhead>IP of :</td><td><select name=ipof>$ipof</select></td>\n");
        print("<tr><td colspan=2><input type=submit value='Okay' class=btn></td></tr>\n");
        print("</form>\n</table>\n");
}

stdfoot();

?>