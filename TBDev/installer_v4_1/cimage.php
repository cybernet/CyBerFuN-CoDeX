<?php
// original idea from hellix modified by stonebreth later by putyn :)
//$CACHE = "C://AppServ/www/cache"; // for windows *change* appserv if you use xammp
$CACHE = "/home/mindless/public_html/cache"; //for unix based
$sitename = "Installer v4";
function middle_W($txt)
{
    GLOBAL $imgW, $fontW;
    return ($imgW / 2) - ((strlen($txt) * $fontW) / 2);
}

$img = imagecreatefrompng("pic/countdown.png");
imagealphablending($img, true);
imagesavealpha($img, true);
// some colors
$white = imagecolorallocate($img, 255, 255, 255);
$red = imagecolorallocate($img, 255, 0, 0);
$grey = imagecolorallocate($img, 128, 128, 128);
// background dimension
$imgW = imagesx($img);
$imgH = imagesy($img);
// font load
$font = imageloadfont("fonts/terminal.tbdevfont");
$fontH = ImageFontHeight($font);
$fontW = ImageFontWidth($font);

imagestring ($img, $font , 10, 9, $sitename . " & tbdev.net", $grey);
imagestring ($img, $font , 10, 10, $sitename . " & tbdev.net", $white);

if (!file_exists($CACHE . "/countdown.txt") || (!is_array($arr = unserialize(@file_get_contents($CACHE . "/countdown.txt")))))
    imagestring ($img, $font , middle_W($errmsg), ($imgH / 2) - $fontH, $errmsg, $red);
else {
    $target = mktime(0, 0, 0, $arr["month"], $arr["day"], $arr["year"]);
    if ($target > time()) {
        $diff = $target - time();

        $days = ($diff - ($diff % 86400)) / 86400;
        $diff = $diff - ($days * 86400);
        $hours = ($diff - ($diff % 3600)) / 3600;
        $diff = $diff - ($hours * 3600);
        $minutes = ($diff - ($diff % 60)) / 60;
        $diff = $diff - ($minutes * 60);
        $seconds = ($diff - ($diff % 1)) / 1;
        $till = "[ $days day(s) ] [ $hours hour(s) ] [ $minutes minute(s) ] [ $seconds second(s) ]";
        imagestring ($img, $font , middle_W($arr["comment"]), ($imgH / 2) - ($fontH), $arr["comment"], $white);
        imagestring ($img, $font , middle_W($till), ($imgH / 2) + ($fontH / 2), $till, $white);
    } else {
        $event_comp = "The event has ended!";
        imagestring ($img, $font , middle_W($event_comp), ($imgH / 2) - ($fontH / 2), $event_comp, $red);
    }
}
// Output to browser
header('Content-type: image/png');
imagepng($img);
imagedestroy($img);

?>