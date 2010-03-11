<?php
// if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
global $queries, $query_stat, $SITENAME, $tstart, $querytime;
$seconds = (timer() - $tstart);
$phptime = $seconds - $querytime;
$query_time = $querytime;
$percentphp = number_format(($phptime / $seconds) * 100, 2);
$percentsql = number_format(($query_time / $seconds) * 100, 2);
$seconds = substr($seconds, 0, 8);

?>

<div align="center">
<p align="center" class="generated"><b>Page generated in <?php echo number_format(array_sum(explode(' ', microtime())) - $GLOBALS['stime'], 5)?> seconds using <?php echo $queries?> queries,<?php echo $percentphp?> &#37; php &#38; <?php echo $percentsql?> &#37; sql</b></p></div>
<?php
if (get_user_class() >= UC_SYSOP) {
    ?>
<h2><a href="javascript:klappe('query')"><?php echo $SITENAME?> Query's</a></h2>
<div align="center" width="700" border="0" id="kquery" class="generated" style="display:none; text-align:left;">

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
<?php
////////////modified torrentstrike function stdfoot by bigjoos for tbdev installer////////
global $SITENAME,$BASEURL,$CURUSER;    
print("</center><td class=\"cHs\"></td></tr><td class=\"cHs\" height=\"21\" align=\"left\" valign=\"top\"></th><td class=\"cHs\" height=\"21\" background=\"themes/blackred/pic/bg3.png\"></td><td class=\"cHs\" height=\"21\" align=\"right\" valign=\"top\"></td></tr></table></body><html>");
print("</td></tr></table></center>\n");
/////////////end stdfoot////////////////////
?>
	&nbsp;
	<table background="themes/blackred/pic/bgnav2.gif"  class="cHs" width="800"  height="35"border="0" align="center" cellpadding="0" cellspacing="0"><tr><td><div align="center"><a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Arabic">Arabic</a>	
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Danish">Danish</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=English">English</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=French">Francais</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=German">Deutsch</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Italian">Italiano</a>		    <b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Hungarian">Hungarian</a>		    <b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Portuguese">Portugues</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Romanian">Romanian</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Spanish">Espanol</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Swedish">Swedish</a>
		<b>·</b>
		<a href="<?php echo $config['site']['DEFAULTBASEURL'];?>?lang=Finnish">Finnish</a></div></td></tr></table>
		&nbsp;
		<center>

Theme designed by <b><font color="#ffffff">VOLKERMORD</font></b><br>
Powered by <b><font color="#ffffff"><a href="http://tbdev.net">tbdev</a></font></b></center>
	
&nbsp;

