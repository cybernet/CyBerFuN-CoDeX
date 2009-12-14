<?php
ob_start("ob_gzhandler");
require_once("include/bittorrent.php");
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
stdhead("$SITENAME links");

?>
<script>
function flipBox(who) {
var tmp;
if (document.images['b_' + who].src.indexOf('_on') == -1) {
tmp = document.images['b_' + who].src.replace('_off', '_on');
document.getElementById('box_' + who).style.display = 'none';
document.images['b_' + who].src = tmp;
} else {
tmp = document.images['b_' + who].src.replace('_on', '_off');
document.getElementById('box_' + who).style.display = 'block';
document.images['b_' + who].src = tmp;
}
}
</script>
<?php

$res = sql_query("SELECT r. * , c.rcat_name, IF( (
UNIX_TIMESTAMP( ) > ctime ) , IF( (
UNIX_TIMESTAMP( ) - mtime ) < ( 3600 *48 ) , 1, 0
), 2
) AS updated
FROM links r
LEFT JOIN links_categories c ON c.cid = r.cid
GROUP BY cid, id");

$cat_placeholder = '';
echo"<h1>$SITENAME links</h1>";
begin_table();
while ($arr = mysql_fetch_assoc($res)) {
    if ($arr['cid'] != $cat_placeholder)
        // if(get_user_class() >= $arr2["min_class_read"]){ //=== start class filter
        echo"<br /><h2>$arr[rcat_name]</h2><br />";
    $updated = ($arr['updated'] == 1 ? "&nbsp;<img src='pic/updated.png' />" : (($arr['updated'] == 2) ? "&nbsp;<img src='pic/new.png' />": ""));
    echo "<div class=faqhead align=left>&nbsp;<img onclick=\"javascript:flipBox('" . $arr['id'] . "')\" src='pic/panel_off.gif' name='b_" . $arr['id'] . "' style='vertical-align:middle;' />&nbsp;<a class=altlink href=\"#\" onclick=\"javascript:flipBox('" . $arr['id'] . "')\" src='pic/panel_off.gif' name='b_" . $arr['id'] . "'>" . $arr['heading'] . $updated . "</a></div>" . "<div align='left' id='box_" . $arr['id'] . "' style='display:block' class='faqbody'><p>" . format_comment($arr['body']) . "</p></div>";
    // }//===end class filter
    $cat_placeholder = $arr['cid'];
}
echo "<br /><p align='center' style='color:orange;font-weight:bold;'>links system based on FAQ System 2006 © CoLdFuSiOn</p>";
end_table();
stdfoot();

?>