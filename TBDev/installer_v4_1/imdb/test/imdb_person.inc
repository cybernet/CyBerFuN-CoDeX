<?php
 #############################################################################
 # IMDBPHP                              (c) Giorgos Giagas & Itzchak Rehberg #
 # written by Giorgos Giagas                                                 #
 # extended & maintained by Itzchak Rehberg <izzysoft AT qumran DOT org>     #
 # http://www.izzysoft.de/                                                   #
 # ------------------------------------------------------------------------- #
 # Checking class imdb_person                                                #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see doc/LICENSE)       #
 #############################################################################

 /* $Id: imdb_person.inc 163 2008-08-07 16:25:36Z izzy $ */

require_once("imdb_person.class.php");
require_once(dirname(__FILE__)."/helpers.inc");

echo "<H3>Checking class imdb_person...</H3>\n";
$imdbp = new imdb_person("0000197"); // Jack Nicholson
$imdbp->cachedir = "../cache";
switch ($_REQUEST["cache"]) {
  case "false":
  case "off"  :
  case "0"    : $imdbp->usecache = 0; $imdbp->storecache = 0; 
                echo "<p>Caching turned off.</p>";
                break;
  case "true" :
  case "on"   :
  case 1      : $imdbp->usecache = 1; $imdbp->storecache = 1;
                echo "<p>Caching turned on.</p>";
                break;
}

page("Name");
 setid($imdbp,"0000197");
 method($imdbp,"name");
 method($imdbp,"born");
 method($imdbp,"photo");
 method($imdbp,"movies_all");
 setid($imdbp,"0000402"); // Carrie Fisher
 method($imdbp,"movies_actress");
 setid($imdbp,"0000197");
 method($imdbp,"movies_actor");
 method($imdbp,"movies_director");
 method($imdbp,"movies_producer");
 method($imdbp,"movies_crew");
 method($imdbp,"movies_soundtrack");
 method($imdbp,"movies_thanx");
 method($imdbp,"movies_self");
 method($imdbp,"movies_archive");
 setid($imdbp,"0000008"); // Marlon Brando
 method($imdbp,"died");
endpage();
page("Bio");
 setid($imdbp,"0000197");
 method($imdbp,"birthname");
 method($imdbp,"nickname");
 method($imdbp,"height");
 method($imdbp,"spouse");
 method($imdbp,"bio");
 method($imdbp,"trivia");
 method($imdbp,"quotes");
 method($imdbp,"trademark");
 method($imdbp,"salary");
endpage();
page("Publicity");
 method($imdbp,"pubprints");
 method($imdbp,"pubmovies");
 method($imdbp,"pubportraits");
 method($imdbp,"interviews");
 method($imdbp,"articles");
 method($imdbp,"pictorials");
 method($imdbp,"magcovers");
endpage();
?>