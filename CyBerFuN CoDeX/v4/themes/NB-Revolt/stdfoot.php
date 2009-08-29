<div align="center">
    <div class="smallfont" align="center">
<?php
if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
echo "<br />Theme By <a href=http://www.nikkbu.com target=_blank><b>Nikkbu</b></a> Ported for the Installer by <b>Hatchet</b>";
echo "<br />Site Powered by <a href=http://tbdev.net target=_blank>TBDEV.NET</a>";
?>
</div>
<br />
<?php
global $queries, $query_stat, $SITENAME, $tstart, $querytime;
$seconds = (timer() - $tstart);
$phptime = $seconds - $querytime;
$query_time = $querytime;
$percentphp = number_format(($phptime / $seconds) * 100, 2);
$percentsql = number_format(($query_time / $seconds) * 100, 2);
$seconds = substr($seconds, 0, 8);

?>

<div align="center">
<br />
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
<p align="center" class="generated"><b>Page generated in <?php echo number_format(array_sum(explode(' ', microtime())) - $GLOBALS['stime'], 5)?> seconds using <?php echo $queries?> queries,<?php echo $percentphp?> &#37; php &#38; <?php echo $percentsql?> &#37; sql</b></p></div>
<?php
if (get_user_class() >= UC_SYSOP) {
    ?>
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
<br />
</div>
</table>

<!-- build the footer -->
    <td style="width:6px; background:url(themes/NB-Revolt/pic/05.png);"><img src="themes/NB-Revolt/pic/blank.gif" width="6" height="1" /></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width:6px; height:6px"><img src="themes/NB-Revolt/pic/06.png" /></td>
    <td style="width:6px;"><img src="themes/NB-Revolt/pic/blank.gif" width="1" height="6" /></td>
    <td style="width:6px; height:6px"><img src="themes/NB-Revolt/pic/blank.gif" width="6" height="6" /></td>
  </tr>
</table>
  </td>
	<td style="width:9px; background:url(themes/NB-Revolt/pic/right.png);"><img src="themes/NB-Revolt/pic/blank.gif" width="9" height="1" /></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width:9px; height:8px"><img src="themes/NB-Revolt/pic/bl.png" /></td>
    <td style="height:8px; background:url(themes/NB-Revolt/pic/bmf.png)repeat-x;" ><img src="themes/NB-Revolt/pic/blank.gif" width="100%" height="8" /></td>
    <td style="width:9px; height:8px"><img src="themes/NB-Revolt/pic/br.png" /></td>
  </tr>
</table>
</td>
</tr>
</table>
<br /><br />
</BODY>
</HTML>