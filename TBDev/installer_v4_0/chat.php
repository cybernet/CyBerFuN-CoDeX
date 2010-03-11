<?php
ob_start("ob_gzhandler");
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
stdhead("Chat");
begin_table();
?>
<center>
<head>
<SCRIPT LANGUAGE="JavaScript">
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=980,height=500,left = 155,top = 262');");
}
</script>
</head>
<body>
<center><tr><b><font size=2px><font color="blue">W</font><font color="lightblue">elcome to </font><font color="blue"></font><font color="green">(</font><font color="lightgreen">XXXXXXXXXX !!!!</font><font color="green">)</font><font color="lightblue">.</font></b><br>
<font color=blue>T</font><font color="lightblue">he </font><font color="blue">O<font color="lightblue">fficial </font>I<font color="blue">RC C</font><font color="lightblue">hannel is</font> <a href=irc://irc.xxxxxx.xxx:6667/scenebase><b><u>#xxxxxx</b></u></a><font color="lightblue"> for </font><font color="lightgreen">Mirc</font><font color="lightblue">,</font><font color="lightgreen"> irssi </font><font color="lightblue">or</font> <font color="lightgreen">XChat</font><font color="lightblue"> usage.</font></font><br>
</tr></center><br>
<tr>
<u><a onClick="javascript:popUp('jirc.php')"><b>Click Here to open the  Java IRC</b></a></u>
</body></center>
<br />
<br />
</tr>
<?php
end_table();
?>
<?php
stdfoot();
?>