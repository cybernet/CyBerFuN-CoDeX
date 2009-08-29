<?php
// if(!defined('PUBLIC_ACCESS')) die('Fuck off - You cant access scripts directly fool !');
// -------- Begins a main frame
function begin_main_frame()
{
    print("<table class=\"main\" width=\"720\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">" . "<tr><td class=\"embedded\">\n");
}
// -------- Ends a main frame
function end_main_frame()
{
    print("</td></tr></table>\n");
}

function begin_frame($caption = "", $center = true, $padding = 10, $full_width = "100%")
{
    $tdextra = "";

    if ($caption)
        print("<h2>$caption</h2>\n");

    if ($center)
        $tdextra .= " align=\"center\"";

    if ($full_width == true) $width_code = "width=\"$full_width\"";

    print("<table $width_code border=\"1\" cellspacing=\"0\" cellpadding=\"$padding\">");
    print("<tr><td$tdextra>\n");
}

function attach_frame($padding = 10)
{
    print("</td></tr><tr><td style='border-top: 0px'>\n");
}

function end_frame()
{
    print("</td></tr>");
    print("</table>\n");
}

function begin_table($fullwidth = false, $padding = 5)
{
    $width = "";

    if ($fullwidth)
        $width .= " width=\"756\"";
    print("<table class=\"main\"$width border=\"1\" cellspacing=\"0\" cellpadding=\"$padding\">\n");
}

function end_table()
{
    // print("</td></tr></table>\n");			// --> TBDev bug here
    print("</table>\n");
}

// -------- Inserts a smilies frame
// (move to globals)
function insert_smilies_frame()
{
    global $smilies, $BASEURL;

    begin_frame("Smilies", true);

    begin_table(false, 5);

    print("<tr><td class=colhead>Type...</td><td class=colhead>To make a...</td></tr>\n");

    while (list($code, $url) = each($smilies))
    print("<tr><td>$code</td><td><img src=$BASEURL/pic/smilies/$url></td>\n");

    end_table();

    end_frame();
}

?>