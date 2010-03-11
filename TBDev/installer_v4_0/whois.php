<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
if (get_user_class() < UC_MODERATOR || get_user_class() > UC_CODER) {
    stderr("Error", "Access denied!");
}

if (phpversion() >= "4.2.0") {
    extract($_POST);
    extract($_GET);
    extract($_SERVER);
    extract($_ENV);
}
stdhead();

?>
<html>
<head>
<title>Tbdev Installer Whois Query</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script>
function m(el) {
  if (el.defaultValue==el.value) el.value = ""
}
</script>
</head>

<body bgcolor="#FFFFFF">
<div align="center">
  <h2>Query (Tbdev InstallerV1)</h2>
  <form method="post" action="<?php echo $PHP_SELF;

?>">
    <table width="70%" border="0" cellspacing="0" cellpadding="1">
      <tr bgcolor="#9999FF">
        <td width="50%" bgcolor="#000000"><font size="2" face="Verdana,
Arial, Helvetica, sans-serif" color="#FFFFFF"><b>Host
          Information </b></font></td>
        <td bgcolor="#000000"><font size="2" face="Verdana, Arial,
Helvetica, sans-serif" color="#FFFFFF"><b>Host
          Connectivities</b></font></td>
      </tr>
      <tr valign="top" bgcolor="#CCCCFF">
        <td bgcolor="#FF0000">
          <p><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
            <input type="radio" name="queryType" value="lookup">
            Resolve/Reverse Lookup<br>
            <input type="radio" name="queryType" value="dig">
            Hole DNS Records<br>
            <input type="radio" name="queryType" value="wwwhois">
            Whois (Web)<br>
            <input type="radio" name="queryType" value="arin">
            Whois (IP owner)</font></p>
        </td>
        <td bgcolor="#FF0000"><font size="2" face="Verdana, Arial, Helvetica, sans-serif">
          <input type="radio" name="queryType" value="checkp">
          Check Port:
          <input
type="text" name="portNum" size="5" maxlength="5" value="80">
          <br>
          <input type="radio" name="queryType" value="p">
          Ping Host<br>
          <input type="radio" name="queryType" value="tr">
          Traceroute to host<br>
          <input type="radio" name="queryType" value="all" checked>
        Check all</font></td>
      </tr>

    </table>
  <table width="70%" border="0" cellspacing="0" cellpadding="1"><tr
bgcolor="#9999FF">
        <td colspan="2" bgcolor="#FFFF00">
          <div align="center">
            <input type="text" name="target"
value="<?=$_GET["ip"]?>" onFocus="m(this)">
            <input type="submit" name="Submit" value="Tbdev Installerv1 Whois Start">
          </div>
        </td>
      </tr>
    </table>
  </form>
</div>
<?php
stdfoot();
// Global kludge for new gethostbyaddr() behavior in PHP 4.1x
$ntarget = "";
// Some functions
function message($msg)
{
    echo "<font face=\"verdana,arial\" size=2>$msg</font>";
    flush();
}

function lookup($target)
{
    global $ntarget;
    $msg = "$target resolved to ";
    if (eregi("[a-zA-Z]", $target))
        $ntarget = gethostbyname($target);
    else
        $ntarget = gethostbyaddr($target);
    $msg .= $ntarget;
    message($msg);
}

function dig($target)
{
    global $ntarget;
    message("<p><b>DNS Query result:</b><blockquote>");
    // $target = gethostbyaddr($target);
    // if (! eregi("[a-zA-Z]", ($target = gethostbyaddr($target))) )
    if ((!eregi("[a-zA-Z]", $target) && (!eregi("[a-zA-Z]", $ntarget))))
        $msg .= "Without host name is the only feasible hard -lol-.";
    else {
        if (!eregi("[a-zA-Z]", $target)) $target = $ntarget;
        if (! $msg .= trim(nl2br(`dig any '$target'`))) // bugfix
            $msg .= "That <i>dig</i> does not run on your system.";
    }
    // TODO: Clean up output, remove ;;'s and DiG headers
    $msg .= "</blockquote></p>";
    message($msg);
}

function wwwhois($target)
{
    global $ntarget;
    $server = "whois.crsnic.net";
    message("<p><b>WWWhois result:</b><blockquote>");
    // Determine which WHOIS server to use for the supplied TLD
    if ((eregi("\.com\$|\.net\$|\.edu\$", $target)) || (eregi("\.com\$|\.net\$|\.edu\$", $ntarget)))
        $server = "whois.crsnic.net";
    else if ((eregi("\.info\$", $target)) || (eregi("\.info\$", $ntarget)))
        $server = "whois.afilias.net";
    else if ((eregi("\.org\$", $target)) || (eregi("\.org\$", $ntarget)))
        $server = "whois.corenic.net";
    else if ((eregi("\.name\$", $target)) || (eregi("\.name\$", $ntarget)))
        $server = "whois.nic.name";
    else if ((eregi("\.biz\$", $target)) || (eregi("\.biz\$", $ntarget)))
        $server = "whois.nic.biz";
    else if ((eregi("\.us\$", $target)) || (eregi("\.us\$", $ntarget)))
        $server = "whois.nic.us";
    else if ((eregi("\.cc\$", $target)) || (eregi("\.cc\$", $ntarget)))
        $server = "whois.enicregistrar.com";
    else if ((eregi("\.ws\$", $target)) || (eregi("\.ws\$", $ntarget)))
        $server = "whois.nic.ws";
    else if ((eregi("\.it\$", $target)) || (eregi("\.it\$", $ntarget)))
        $server = "whois.nic.it";
    else {
        $msg .= "Only .com, .net, .org, .edu, .info, .name, .us, .cc, .ws, and .biz available.</blockquote>";
        message($msg);
        return;
    }

    message("Connect to $server...<br><br>");
    if (! $sock = fsockopen($server, 43, $num, $error, 10)) {
        unset($sock);
        $msg .= "Time-out connection to the $server (port 43)";
    } else {
        fputs($sock, "$target\n");
        while (!feof($sock))
        $buffer .= fgets($sock, 10240);
    }
    fclose($sock);
    if (! eregi("Whois Server:", $buffer)) {
        if (eregi("no match", $buffer))
            message("NOT FOUND: No entry for $target<br>");
        else
            message("Ambiguous query, multiple matches for $target:<br>");
    } else {
        $buffer = split("\n", $buffer);
        for ($i = 0; $i < sizeof($buffer); $i++) {
            if (eregi("Whois Server:", $buffer[$i]))
                $buffer = $buffer[$i];
        }
        $nextServer = substr($buffer, 17, (strlen($buffer)-17));
        $nextServer = str_replace("1:Whois Server:", "", trim(rtrim($nextServer)));
        $buffer = "";
        message("If passed in the next Whois: $nextServer...<br><br>");
        if (! $sock = fsockopen($nextServer, 43, $num, $error, 10)) {
            unset($sock);
            $msg .= "Time-out connection to the $nextServer (port 43)";
        } else {
            fputs($sock, "$target\n");
            while (!feof($sock))
            $buffer .= fgets($sock, 10240);
            fclose($sock);
        }
    }
    $msg .= nl2br($buffer);
    $msg .= "</blockquote></p>";
    message($msg);
}

function arin($target)
{
    $server = "whois.arin.net";
    message("<p><b>IP Whois Record:</b><blockquote>");
    if (!$target = gethostbyname($target))
        $msg .= "Without IP address is the only feasible difficult ;)";
    else {
        message("Connect to $Server ...<br><br>");
        if (! $sock = fsockopen($server, 43, $num, $error, 20)) {
            unset($sock);
            $msg .= "Time-out connection to the $server (port 43)";
        } else {
            fputs($sock, "$target\n");
            while (!feof($sock))
            $buffer .= fgets($sock, 10240);
            fclose($sock);
        }
        if (eregi("RIPE.NET", $buffer))
            $nextServer = "whois.ripe.net";
        else if (eregi("whois.apnic.net", $buffer))
            $nextServer = "whois.apnic.net";
        else if (eregi("nic.ad.jp", $buffer)) {
            $nextServer = "whois.nic.ad.jp";
            // /e suppresses Japanese character output from JPNIC
            $extra = "/e";
        } else if (eregi("whois.registro.br", $buffer))
            $nextServer = "whois.registro.br";
        if ($nextServer) {
            $buffer = "";
            message("Deferred to specific whois server: $nextServer...<br><br>");
            if (! $sock = fsockopen($nextServer, 43, $num, $error, 10)) {
                unset($sock);
                $msg .= "Time-out connection to the $nextServer (port 43)";
            } else {
                fputs($sock, "$target$extra\n");
                while (!feof($sock))
                $buffer .= fgets($sock, 10240);
                fclose($sock);
            }
        }
        $buffer = str_replace(" ", "&nbsp;", $buffer);
        $msg .= nl2br($buffer);
    }
    $msg .= "</blockquote></p>";
    message($msg);
}

function checkp($target, $portNum)
{
    message("<p><b>Check Port $portNum</b>...<blockquote>");
    if (! $sock = fsockopen($target, $portNum, $num, $error, 5))
        $msg .= "Portland $portNum seems to be unreachable.";
    else {
        $msg .= "Portland $portNum is open and accessible.";
        fclose($sock);
    }
    $msg .= "</blockquote></p>";
    message($msg);
}

function p($target)
{
    message("<p><b>Ping result:</b><blockquote>");
    if (! $msg .= trim(nl2br(`ping -c5 '$target'`))) // bugfix
        $msg .= "Ping failed. The host can not be achieved!";
    $msg .= "</blockquote></p>";
    message($msg);
}
/*
function tr($target){
message("<p><b>Traceroute result:</b><blockquote>");
if (! $msg .= trim(nl2br(`/usr/sbin/traceroute '$target'`))) #bugfix
  $msg .= "Traceroute failed. The host can not be achieved!";
$msg .= "</blockquote></p>";
message($msg);
}
*/
// If the form has been posted, process the query, otherwise there's
// nothing to do yet
if (!$queryType)
    exit;
// Make sure the target appears valid
if ((!$target) || (!preg_match("/^[\w\d\.\-]+\.[\w\d]{1,4}$/i", $target))) { // bugfix
    message("Error: You have no valid IP or host.");
    exit;
}
// Figure out which tasks to perform, and do them
if (($queryType == "all") || ($queryType == "lookup"))
    lookup($target);
if (($queryType == "all") || ($queryType == "dig"))
    dig($target);
if (($queryType == "all") || ($queryType == "wwwhois"))
    wwwhois($target);
if (($queryType == "all") || ($queryType == "arin"))
    arin($target);
if (($queryType == "all") || ($queryType == "checkp"))
    echo"<h3>The function port check is not yet finished and will therefore be skipped;)</h3>";
/*checkp($target,$portNum); */
if (($queryType == "all") || ($queryType == "p"))
    p($target);
if (($queryType == "all") || ($queryType == "tr"))
    print($target);

?>

<hr>
<p align="right"><font color="#cccccc">TBdev-Installer</a><br>
<?php echo date('Y');

?></font></p>
<?php
?>
