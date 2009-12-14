<?php
ob_start("ob_gzhandler");
require_once ("include/bittorrent.php");


dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

?>
<html>
<head>
<title>Official JavaIRC</title>
</head>
<body bgcolor="#353535">
<applet CODEBASE="./javairc/" code=IRCApplet.class archive="irc.jar,pixx.jar" width=960 height=480>
<param name="CABINETS" value="irc.cab,securedirc.cab,pixx.cab">
<param name="nick" value="<?=str_replace(' ', '_', $CURUSER['username'])?>">
<param name="alternatenick" value="<?=str_replace(' ', '_', $CURUSER['username'])?>???">
<param name="name" value="java user">
<param name="host" value="">
<param name="gui" value="pixx">
<param name="quitmessage" value="Scene-Base out! Cya.....">
<param name="multiserver" value="false">
<param name="fingerreply" value="A lucky iRC user">
<param name="userinforeply" value="A happy iRC user">
<param name="allowdccfile" value="false">
<PARAM name="font_name" value="Arial">
<param name="fileparameter" value="twl-black.ini">
<param name="style:bitmapsmileys" value="true">
<param name="style:smiley1" value=":ras: img/ras.gif">
<param name="style:smiley2" value=":rofl2: img/rofl2.gif">
<param name="style:smiley3" value=":fart: img/fart.gif">
<param name="style:smiley4" value=":spliffy: img/spliffy.gif">
<param name="style:smiley5" value=":whistle: img/whistle.gif">
<param name="style:smiley6" value=":o img/OH-1.gif">
<param name="style:smiley7" value=":-P img/langue.gif">
<param name="style:smiley8" value=":p img/langue.gif">
<param name="style:smiley9" value=";-) img/clin-oeuil.gif">
<param name="style:smiley10" value=";) img/clin-oeuil.gif">
<param name="style:smiley11" value=":-( img/triste.gif">
<param name="style:smiley12" value=":( img/triste.gif">
<param name="style:smiley13" value=":-| img/OH-3.gif">
<param name="style:smiley14" value=":| img/OH-3.gif">
<param name="style:smiley15" value=":'( img/pleure.gif">
<param name="style:smiley16" value=":$ img/rouge.gif">
<param name="style:smiley17" value=":-$ img/rouge.gif">
<param name="style:smiley18" value="(H) img/cool.gif">
<param name="style:smiley19" value="(h) img/cool.gif">
<param name="style:smiley20" value=":-@ img/enerve1.gif">
<param name="style:smiley21" value=":@ img/enerve2.gif">
<param name="style:smiley22" value=":-S img/roll-eyes.gif">
<param name="style:smiley23" value=":s img/roll-eyes.gif">
<param name="style:maximumlinecount" value="256">
<param name="style:highlightlinks" value="true">
<param name="highlight" value="true">
<param name="pixx:timestamp" value="true">
<param name="pixx:highlightcolor" value="11">
<param name="pixx:showdock" value="false">
<param name="pixx:showconnect" value="true">
<param name="pixx:showchanlist" value="false">
<param name="pixx:showchannelmodeapply" value="true">
<param name="pixx:showabout" value="false">
<param name="pixx:showhelp" value="false">
<param name="pixx:showclose" value="false">
<param name="pixx:showstatus" value="true">
<param name="pixx:showdock" value="false">
<param name="pixx:setfontonstyle" value="true">
<param name="pixx:nickprefix" value="<\b">
<param name="pixx:displaychannelmode" value="false">
</applet>
</body>
</html>
<?php
?>