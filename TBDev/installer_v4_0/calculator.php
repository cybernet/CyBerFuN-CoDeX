<?php
/////////////////////////////////////////////////
// Script © By Martijns Web Hosting
// www.martijnswebhosting.tk 
// Made for Anouksweb.nl 
//
//
//                                   
////////////////////////////////////////////////////
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");
dbconn(false);
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
maxcoder();
if (get_user_class() < UC_MODERATOR)
hacker_dork("Byte Calculator - Nosey Cunt !");
stdhead ('Calculator');
  print '' . '
<script>
function calc(from) {
gb = document.sizes.gb.value; mb = document.sizes.mb.value; kb = document.sizes.kb.value; b = document.sizes.byte.value;
if(from==\'gb\') { document.sizes.mb.value=""+gb+""; document.sizes.mb.value*="1024"; document.sizes.kb.value=""+gb+""; document.sizes.kb.value*="1024"; document.sizes.kb.value*="1024"; document.sizes.byte.value=""+gb+""; document.sizes.byte.value*="1024"; document.sizes.byte.value*="1024"; document.sizes.byte.value*="1024"; }
else if(from==\'mb\') { document.sizes.gb.value=""+mb+""; document.sizes.gb.value/="1024"; document.sizes.kb.value=""+mb+""; document.sizes.kb.value*="1024"; document.sizes.byte.value=""+mb+""; document.sizes.byte.value*="1024"; document.sizes.byte.value*="1024"; }
else if(from==\'kb\') { document.sizes.gb.value=""+kb+""; document.sizes.gb.value/="1024"; document.sizes.gb.value/="1024"; document.sizes.mb.value=""+kb+""; document.sizes.mb.value/="1024"; document.sizes.byte.value=""+kb+""; document.sizes.byte.value*="1024"; }
else if(from==\'byte\') { document.sizes.gb.value=""+b+""; document.sizes.gb.value/="1024"; document.sizes.gb.value/="1024"; document.sizes.gb.value/="1024"; document.sizes.mb.value=""+b+""; document.sizes.mb.value/="1024"; document.sizes.mb.value/="1024"; document.sizes.kb.value=""+b+""; document.sizes.kb.value/="1024"; }
}
</script>

<form name="sizes">
<table border="0" width="100%" cellspacing="5" cellpadding="2">
<tr>
<td width="6%" class=none align=right>GB&nbsp;</td>
<td width="20%" class=none>&nbsp<input type="text" name="gb" size="20"></td>
<td width="74%" class=none>&nbsp<input onclick="javascript:calc(\'gb\')" type="button" value="Calculate From GB "></td>
</tr>
<tr>
<td width="6%" class=none align=right>MB&nbsp;</td>
<td width="20%" class=none>&nbsp;<input type="text" name="mb" size="20"></td>
<td width="74%" class=none>&nbsp;<input onclick="javascript:calc(\'mb\')" type="button" value="Calculate From MB "></td>
</tr>
<tr>
<td width="6%" class=none align=right>KB&nbsp;</td>
<td width="20%" class=none>&nbsp;<input type="text" name="kb" size="20"></td>
<td width="74%" class=none>&nbsp;<input onclick="javascript:calc(\'kb\')" type="button" value="Calculate From KB "></td>
</tr>
<tr>
<td width="6%" class=none align=right>Byte&nbsp;</td>
<td width="20%" class=none>&nbsp;<input type="text" name="byte" size="20"></td>
<td width="74%" class=none>&nbsp;<input onclick="javascript:calc(\'byte\')" type="button" value="Calculate From Byte"></td>
</tr>
</table>
</form>';
stdfoot ();
?>
