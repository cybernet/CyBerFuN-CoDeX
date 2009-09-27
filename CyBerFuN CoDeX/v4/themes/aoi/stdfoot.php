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
</div>
<td class="right_side"></td>
<table style="background-image:url(themes/aoi/images/footer.jpg);width:912px;height:92px" align="center">
<td>
<table style="background:transparent" border="0" cellpadding="0" cellspacing="0" align=center>
&nbsp;
<td>&nbsp;&nbsp;&nbsp;</td>
<?php
if ( $CURUSER ) {
?>
<td><img src="themes/aoi/images/box-left.png"></td>
<td class=low_link><a href=shows.php id=shows>Shows</a></td>
<td class=low_link><a href=helpdesk.php id=help>Help</a></td>
<td class=low_link><a href=rules.php id=rules>Rules</a></td>
<td class=low_link><a href=faq.php id=faq>FAQ</a></td>
<td class=low_link><a href=topten.php id=topten>Top10</a></td>
<td class=low_link><a href=useragreement.php id=disclaimer>Disclaimer</a></td>
<td class=low_link><a href=donate.php id=donate>Donate</a></td>
<td class=low_link><a href=links.php id=links>Links</a></td>
<td><img src="themes/aoi/images/box-right.png"></td>
<?php
}
?>
<td>&nbsp;&nbsp;&nbsp;</td>

<td><img src="themes/aoi/images/box-left.png"></td>
<td class=tbdev><marquee><font color=#037bb9 size=2>Powered By <a href="http://www.tbdev.net"><b>TBDev.net</b></a> / GFX by <a href="mailto:ShadoW69.KaGe69@hotmail.com"><b>ShadoW69</b></font></marquee></td>
<td><img src="themes/aoi/images/box-right.png"></td>
<td>&nbsp;&nbsp;&nbsp;</td>

</table>

</td>
</table>
</td></tr></table></body><html>

</td></tr></table>


<?php
// ////////////////////end stdfoot
?>
