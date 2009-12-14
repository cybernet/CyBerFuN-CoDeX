<?php
if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
// Created for My-Gamebox.com by bAN01TgAZ
// Use :root:/pic/rotatinglogo/*. (jpg / png / gif)
if ($handle = opendir('pic/rotatinglogos')) {

   $count=0;

   while (false !== ($file = readdir($handle))) {

     if(getimagesize("pic/rotatinglogos/".$file)) {

     $count++;

       $dirlist[$count]="pic/rotatinglogos/".$file;

     }

   }

   closedir($handle);

   $filenr = rand(1,$count);

   print($dirlist[$filenr]);

}

?>