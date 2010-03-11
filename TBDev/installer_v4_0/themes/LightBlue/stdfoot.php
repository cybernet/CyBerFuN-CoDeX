<?php
if (!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
global $queries, $query_stat, $SITENAME, $tstart, $querytime, $config, $page_find, $lang_off, $language;
$seconds = (timer() - $tstart);
$phptime = $seconds - $querytime;
$query_time = $querytime;
$percentphp = number_format(($phptime / $seconds) * 100, 2);
$percentsql = number_format(($query_time / $seconds) * 100, 2);
$seconds = substr($seconds, 0, 8);

?>

</td>
    <td class="mr"></td>

</tr>
<tr>
	<td class="bl"></td>
    <td class="bm"></td>
    <td class="br"></td>

</tr>
</table>

<span>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Arabic">Arabic</a>	
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Danish">Danish</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=English">English</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=French">Français</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=German">Deutsch</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Italian">Italiano</a>		    <b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Hungarian">Hungarian</a>		    <b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Hebrew">Hebrew</a>		    <b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Latvian">Latvian</a>		    <b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Portuguese">Português</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Romanian">Romanian</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Spanish">Español</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Swedish">Swedish</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Finnish">Finnish</a>
			</span>
	</p> 
	<br />
	<p align="center" class="generated"><b>Page generated in <?php echo number_format(array_sum(explode(' ', microtime())) - $GLOBALS['stime'], 5)?> seconds using <?php echo $queries?> queries, <?php echo $percentphp?> &#37; php &#38; <?php echo $percentsql?> &#37; sql</b></p></div>
<?php
if (get_user_class() >= UC_SYSOP) {
    ?>
    <div align="center">
<a href="http://tbdev.net" target="_blank"><img src="themes/LightBlue/pic/tbdev.png" width="80" height="15" border="0"/></a>  <a href="http://chat2pals.co.uk" target="_blank"><img src="themes/LightBlue/pic/theme.png" width="80" height="15" border="0"/></a>
</div>
</br>
<h2><a href="javascript:klappe('query')"><?php echo $SITENAME?> Query's</a></h2>

<div id="kquery" class="generated" style="display:none; text-align:left;">
<?php
    if (get_user_class() >= UC_SYSOP) {
        if (DEBUG_MODE && $query_stat) {
            foreach ($query_stat as $key => $value) {
                print("[" . safechar(($key + 1)) . "] => <b>" . ($value["seconds"] > 0.01 ? "<font color=\"red\" title=\"You should optimize this query.\">" . safechar($value["seconds"]) . "</font>" : "<font color=\"green\" title=\"This query doesn't need optimized.\">" . safechar($value["seconds"]) . "</font>") . "</b> [" . str_replace("&", "&amp;", $value["query"]) . "]<br />\n");
            }
        }
    }
}

?>
</body>
</html>
