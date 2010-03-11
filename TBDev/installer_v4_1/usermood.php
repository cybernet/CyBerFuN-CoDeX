<?php
require_once("include/bittorrent.php");
// require ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
//require_once("include/cache/function_cache.php");
error_reporting(E_ALL ^ E_NOTICE); // :p

dbconn(false);
maxcoder();
if (isset($_GET["mood"]) && (isset($_GET["id"]))) {
    $moodid = (isset($_GET['id'])?0 + $_GET['id']:'');
    $moodname = (isset($_GET['mood'])?safe($_GET['mood']):'');
    $moodhdr = str_replace('+', ' ', $moodname);
    mysql_query("UPDATE users SET mood={$moodid} WHERE id={$CURUSER['id']}") or sqlerr(__FILE__, __LINE__);
    echo '<h3 align="center">' . $CURUSER['username'] . '\'s Mood has been changed to ' . $moodhdr . '!</h3><table><tr><td>';

    ?>
<script language="JavaScript" type="text/javascript">
<!--
opener.location.reload(true);
self.close();
// -->
</script>
<?php
}

?>

<link rel="stylesheet" href="themes/green/green.css" type="text/css">
<?php

echo '<h3 align="center">' . $CURUSER['username'] . '\'s Mood</h3><table><tr><td>';
//cache_start(6000, usermood);
foreach($mood as $key => $value) {
    $change[$value['id']] = array('id' => $value['id'], 'name' => $value['name'], 'image' => $value['image']);
    $moodid = $change[$value['id']]['id'];
    $moodname = $change[$value['id']]['name'];
    $moodurl = str_replace(' ', '+', $moodname);
    $moodpic = $change[$value['id']]['image'];
    echo '<a href="?mood=' . $moodurl . '&amp;id=' . $moodid . '">
<img src="' . $pic_base_url . 'smilies/' . $moodpic . '" border="0" alt="" />' . $moodname . '</a>&nbsp;&nbsp;';
}
//register_shutdown_function("cache_end");
echo '<p><br /></p><a href="javascript:self.close();"><font color="#FF0000">Close window</font></a>';
echo '</td></tr></table>';

?>
