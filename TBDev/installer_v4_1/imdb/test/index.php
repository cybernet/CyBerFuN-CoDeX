<?php
 #############################################################################
 # IMDBPHP                              (c) Giorgos Giagas & Itzchak Rehberg #
 # written by Giorgos Giagas                                                 #
 # extended & maintained by Itzchak Rehberg <izzysoft AT qumran DOT org>     #
 # http://www.izzysoft.de/                                                   #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see doc/LICENSE)       #
 #############################################################################

 /* $Id: index.php 161 2008-08-06 10:27:29Z izzy $ */

#====================================================[ Output HTML Header ]===
echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
echo "<HTML><HEAD>\n"
   . " <TITLE>IMDBAPI Checks</TITLE>\n"
   . " <STYLE TYPE='text/css'>body,td,th { font-size:12px; font-family:sans-serif; }</STYLE>\n"
   . "</HEAD><BODY>\n";

#==========================================[ Define what should be tested ]===
$check_movie = false;
$check_name  = false;
switch ($_REQUEST["check"]) {
  case "movie" : $check_movie = true; break;
  case "name"  : $check_name  = true; break;
  default:
    $check_movie = true;
    $check_name  = true;
}

#=========================================================[ Run the tests ]===
if ($check_movie) require ("imdb.inc");
if ($check_name)  require ("imdb_person.inc");

#===============================================[ Summary and HTML footer ]===
$passed  = $methods - $failures;
$percent = round(100*$passed/$methods)."%";
echo "<H3>Test Results:</H3><UL><LI>Methods: $methods</LI><LI>Passed: $passed</LI><LI>Failures: $failures</LI><LI>Success: $percent</LI></UL>";
if ($percent == "100%") echo "<b>Congratulations!</b> Looks like the complete API is working perfectly.";
echo "</BODY></HTML>\n";
?>