<?php
require_once("include/bittorrent.php");
require_once("include/bbcode_functions.php");
dbconn();

if(!empty($_POST['verify'])){
$name = safeChar($_POST['staffname']);
$tempip = getip();
$ass = mysql_query("SELECT email,class FROM users WHERE username=" . sqlesc($name));
$asshole = mysql_fetch_array($ass);
if ($asshole['class'] < UC_MODERATOR) {
$naughtyboy = getip();
$msg = "Someone is trying to cheat the confirm ip add page with the name $name and ip $naughtyboy";
$subject = "ALERT restrected access attempt";
// change id to your id to recieve a pm if someone tried to access or just comment it out
mysql_query("INSERT INTO messages (sender, receiver, added, msg, poster) VALUES (0, 1, '" . get_date_time() . "', " . sqlesc($msg) . ", 0)") or sqlerr(__FILE__, __LINE__);
stderr("Error", "WARNING ! You just been caught");
die();
}


$staffemail = $asshole["email"];
$editsecret = mksecret();
$psecret = md5($editsecret);
$body = <<<EOD
You have requested to add a TEMPORARY IP $tempip on $SITENAME.

If you did not do this, please notify the staff IMMEDIATELY!!

To confirm your Temorary IP, Click this link:

$BASEURL/conipadd.php?requested=$name&secret=$psecret

After you do this, you will be able to log into your account and the IP will be good for 12 Hours Only.
EOD;

switch ($_POST['verify'])
{
                case 'yes':
                $trackingyou = ip2long($tempip);
mail($staffemail, "$SITENAME Add Temp IP confirmation", $body, "From: $SITEEMAIL");
$letsdoit = mysql_query("SELECT * FROM secureiptable WHERE username=".sqlesc($name)) or sqlerr(__FILE__,__LINE__);
if (mysql_num_rows($letsdoit) > 0) {
        mysql_query("UPDATE secureiptable SET eticket=".sqlesc($editsecret)." WHERE username=".sqlesc($name)) or sqlerr(__FILE__, __LINE__);
        }
else {
mysql_query("INSERT INTO secureiptable VALUES (0, ".sqlesc($name).", ".sqlesc($trackingyou).", 0,'" . get_date_time() . "',".sqlesc($editsecret).")") or sqlerr(__FILE__, __LINE__);
}




break;

default:

stderr("Ok", "You can add it later");
die();
break;



  }
  stderr("Almost Done", "Check your email account to confirm addition of the ip, NOTE: Check your spam folder");
  }

if (! empty ($_GET['requested']) && ! empty($_GET['secret'])){
$confirmname = safeChar($_GET['requested']);
$secretsauce = $_GET["secret"];
$added = sqlesc(get_date_time());



if (!$confirmname)
        httperr();

dbconn();


$res23 = mysql_query("SELECT eticket,ip FROM secureiptable WHERE username = ".sqlesc($confirmname));
$row23 = mysql_fetch_array($res23);

$userip = $row23["ip"];

if (!$row23)
        stderr("Ok", "query not matchin");


$sec = hash_pad($row23["eticket"]);
if ($secretsauce != md5($sec))
        stderr("Ok", "eticket not matching");

mysql_query("INSERT INTO ipsecureip (added, addedby, first, last, ipof, temp) VALUES($added, 0, ".sqlesc($userip).", ".sqlesc($userip).", ".sqlesc($confirmname).", 'yes')") or sqlerr(__FILE__, __LINE__);
mysql_query("DELETE FROM secureiptable WHERE username=".sqlesc($confirmname));

if (!mysql_affected_rows())
        httperr();

 stderr("Success", "Your IP is good for 12 hours");
       }
       else {
       // //change next line to your server specs
               header("HTTP/1.0 404 Not Found");
      print("<html><body><h1>Not Found</h1><br>The requested URL /conipadd.php was not found on this server.<hr><address>Apache/2.0.53 (Fedora) Server at www.sitenamehere.com Port 80</address></body></html>\n");
  die;
  }


?>