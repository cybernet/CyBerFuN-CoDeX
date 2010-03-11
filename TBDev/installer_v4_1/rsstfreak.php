<?php
require_once ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
require_once("xml2array.php");
dbconn();
loggedinorreturn();
stdhead("Torrent Freak News");
begin_frame();	
	$link = "http://feed.torrentfreak.com/Torrentfreak/";
	$xml = file_get_contents($link);
	
	$arr = xml2array($xml, 0);
	
	
	$r = $arr["rss"]["channel"]["item"];

	foreach( $r as $post )
		{
			if(is_array($post["category"])){
				foreach ($post["category"] as $category)
					$cat[] = $category;
				$cats = join(", ",$cat);
			}
			else 
				$cats = "<a href=\"http://torrentfreak.com/category/".$post["category"]."\">".$post["category"]."</a>";
				
			preg_match('#<p>(.*?)</p>#is',$post["description"] , $descr);
			
			print("<h3><u>".$post["title"]."</u></h3><font class=\"small\">by ".$post["dc:creator"]." - ".str_replace("+0000","", $post["pubDate"])."<br/><b>Category</b> : ".$cats."</font><br/><br/>\n");
			print("<b>".$descr[1]."</b><br/>\n");
			print("<a href=\"".$post["feedburner:origLink"]."\"><font class=\"small\">Read more</font></a><br/>\n");
		}
end_frame();		
stdfoot();


?>