<?php
if (!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
function begin_main_frame() {
    ?>
	<table width="95%" cellpadding="0" cellspacing="0" style="border-collapse:collapse" border="0">
	<tr>
    	<td style="background:url(themes/NB-Revolt/pic/09.png) no-repeat; height:28px; width:120px;">&nbsp;</td>
        <td style="background:url(themes/NB-Revolt/pic/10.png) repeat-x; height:28px;">&nbsp;</td>
        <td style="background:url(themes/NB-Revolt/pic/11.png) no-repeat; height:28px; width:120px;">&nbsp;</td>
    </tr>
    <tr><td valign="top" colspan="3" style="padding:5px" class="f-body">
	<?php
}
// -------- Ends a main frame
function end_main_frame()
{
    echo("</td></tr></table>\n");
}

function begin_frame($caption = "", $center = true, $padding = 10, $full_width = "100%")
{
    $tdextra = "";
    if ($caption)
        echo("<h2>$caption</h2>\n");

    if ($center)
        $tdextra .= " align=\"center\"";

    if ($full_width == true) $width_code = "width=\"$full_width\"";

    echo("<table $width_code border=\"1\" cellspacing=\"0\" cellpadding=\"$padding\">");
    echo("<tr><td $tdextra>\n");
}

function attach_frame($padding = 0)
{
    echo("\n");
}

function end_frame()
{
    echo("</td></tr>");
    echo("</table>\n");
}

function begin_table($fullwidth = false, $padding = 5)
{
    $width = "";

    if ($fullwidth)
        $width .= " width=\"100%\"";
    echo("<table class=\"main\"$width border=\"1\" cellspacing=\"0\" cellpadding=\"$padding\">\n");
}

function end_table()
{
    echo("</table>\n");
}
// -------- Inserts a smilies frame
// (move to globals)
function insert_smilies_frame()
{
    global $smilies, $BASEURL;

    begin_frame("Smilies", true);

    begin_table(false, 5);

    echo("<tr><td class=colhead>Type...</td><td class=colhead>To make a...</td></tr>\n");

    while (list($code, $url) = each($smilies))
    echo("<tr><td>$code</td><td><img src=$BASEURL/pic/smilies/$url></td>\n");

    end_table();

    end_frame();
}

?>