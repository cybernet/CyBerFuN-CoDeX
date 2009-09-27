<?php
global $queries, $query_stat, $SITENAME, $tstart, $querytime;
$seconds = (timer() - $tstart);
$phptime = $seconds - $querytime;
$query_time = $querytime;
$percentphp = number_format(($phptime / $seconds) * 100, 2);
$percentsql = number_format(($query_time / $seconds) * 100, 2);
$seconds = substr($seconds, 0, 8);

?>						
						</div>
                    </td>

                	<td class="rightcenter"></td>
            	</tr>
                <tr>
                	<td class="bottomleft"></td>
                    <td class="bottomcenter"></td>

                    <td class="bottomright"></td>
                </tr>
            </table>

	<br />
  <div align="center">
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
			</div>
	</p>
    	<table align="center" width="100%" cellpadding="0" cellspacing="0" border="0">
<p align="center" class="generated"><b>Page generated in <?php echo number_format(array_sum(explode(' ', microtime())) - $GLOBALS['stime'], 5)?> seconds using <?php echo $queries?> queries,<?php echo $percentphp?> &#37; php &#38; <?php echo $percentsql?> &#37; sql</b></p></div>
<?php
if (get_user_class() >= UC_SYSOP) {
    ?>
<h2 align="center"><a href="javascript:klappe('query')" text-align="center"><?php echo $SITENAME?> Query's</a></h2></table>
<div id="kquery" class="generated" style="display:none; text-align:center;">

<?php
    if (get_user_class() >= UC_SYSOP) {
        if (DEBUG_MODE && $query_stat) {
            foreach ($query_stat as $key => $value) {
                print("[" . safeChar(($key + 1)) . "] => <b>" . ($value["seconds"] > 0.01 ? "<font color=\"red\" title=\"You should optimize this query.\">" . safeChar($value["seconds"]) . "</font>" : "<font color=\"green\" title=\"This query doesn't need optimized.\">" . safeChar($value["seconds"]) . "</font>") . "</b> [" . str_replace("&", "&amp;", $value["query"]) . "]<br />\n");
            }
        }
    }
}
?>        
            </div>
            <div id="bottommenu">
                <img src="themes/<?=$ss_uri?>/images/bottombar.png" border="0" usemap="#bottomlinks" />
                <map name="bottomlinks" id="bottomlinks">
                    

                    <area shape="rect" coords="712,115,773,149" href="upload.php" alt="Upload Torrent" />
                    <area shape="rect" coords="392,105,446,116" href="http://www.tbdev.net" alt="TBDev.Net" target="_blank" />
                    <area shape="rect" coords="81,115,142,149" href="topten.php" alt="Top 10 Users" />
                </map>
            </div>
        </div>
        <br />
        <br />
	</body>
</html>