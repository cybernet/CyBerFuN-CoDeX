<?php
if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
require "xml2array.php";
function xmlconvert($source)
		{
			$xml = new Xml2Array();
			$xml->setXml($source);
			$array = $xml->get_array();
			return ($array);
		}
	function sort_array($array)
		{
			//check the array 
			if(!is_array($array)) die("no array passed");
			foreach($array as $key=>$value)
				{
					foreach($value as $key2 =>$value2)
						$details[$key] = $value2;
				}
			return $details;
		}
	function get_subtitles($name)
		{
			$link = "http://www.subtitlesource.org/api/xmlsearch/".$name."/all/0";
			$xml = @file_get_contents($link) or die("Can't get the xml back");
			$ra = xmlconvert($xml);
			
			$array =  $ra["subtitlesource"]["xmlsearch"]["sub"];
		
			if(isset($array[0]))
			{
			foreach($array as $k){
				$sort_array[] = sort_array($k);
				}
			$subs = array();
			foreach ($sort_array as $k)
				{
			if(!array_key_exists($k["language"],$subs))
				$subs[strtolower($k["language"])] = $k["id"];
				}
				$return = "";
				foreach($subs as $lang=>$id)
				{
					$return .= "<a href=\"http://www.subtitlesource.org/download/zip/".$id."\"><img src=\"flags/".$lang.".png\" border=\"0\" style=\"padding:3px;\" title=\"".$lang."\"></a>";
				}
				return $return;
			}
			elseif(is_array($array))
			{
				$sub = sort_array($array);
				return "<a href=\"http://www.subtitlesource.org/download/zip/".$sub["id"]."\"><img src=\"flags/".strtolower($sub["language"]).".png\" border=\"0\" style=\"padding:3px;\" title=\"".strtolower($sub["language"])."\"></a>";
			}
			else return "No result found";
		}
		
?>