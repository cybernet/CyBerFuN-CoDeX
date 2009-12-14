<?php
require_once("include/bittorrent.php");
require_once("include/user_functions.php");
require_once("include/bbcode_functions.php");

dbconn(false);
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
parked();

stdhead();

?>
<style type="text/css">

#dhtmltooltip{
color: black;
position: absolute;
width: 150px;
border: 2px solid black;
padding: 2px;
background-color: lightyellow;
visibility: hidden;
z-index: 100;
/*Remove below line to remove shadow. Below line should always appear last within this CSS*/
filter: progid:DXImageTransform.Microsoft.Shadow(color=gray,direction=135);
}

</style>

<div id="dhtmltooltip"></div>

<script type="text/javascript">

/***********************************************
* Cool DHTML tooltip script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var offsetxpoint=-60 //Customize x offset of tooltip
var offsetypoint=20 //Customize y offset of tooltip
var ie=document.all
var ns6=document.getElementById && !document.all
var enabletip=false
if (ie||ns6)
var tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : ""

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function ddrivetip(thetext, thecolor, thewidth){
if (ns6||ie){
if (typeof thewidth!="undefined") tipobj.style.width=thewidth+"px"
if (typeof thecolor!="undefined" && thecolor!="") tipobj.style.backgroundColor=thecolor
tipobj.innerHTML=thetext
enabletip=true
return false
}
}

function positiontip(e){
if (enabletip){
var curX=(ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
var curY=(ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
//Find out how close the mouse is to the corner of the window
var rightedge=ie&&!window.opera? ietruebody().clientWidth-event.clientX-offsetxpoint : window.innerWidth-e.clientX-offsetxpoint-20
var bottomedge=ie&&!window.opera? ietruebody().clientHeight-event.clientY-offsetypoint : window.innerHeight-e.clientY-offsetypoint-20

var leftedge=(offsetxpoint<0)? offsetxpoint*(-1) : -1000

//if the horizontal distance isn't enough to accomodate the width of the context menu
if (rightedge<tipobj.offsetWidth)
//move the horizontal position of the menu to the left by it's width
tipobj.style.left=ie? ietruebody().scrollLeft+event.clientX-tipobj.offsetWidth+"px" : window.pageXOffset+e.clientX-tipobj.offsetWidth+"px"
else if (curX<leftedge)
tipobj.style.left="5px"
else
//position the horizontal position of the menu where the mouse is positioned
tipobj.style.left=curX+offsetxpoint+"px"

//same concept with the vertical position
if (bottomedge<tipobj.offsetHeight)
tipobj.style.top=ie? ietruebody().scrollTop+event.clientY-tipobj.offsetHeight-offsetypoint+"px" : window.pageYOffset+e.clientY-tipobj.offsetHeight-offsetypoint+"px"
else
tipobj.style.top=curY+offsetypoint+"px"
tipobj.style.visibility="visible"
}
}

function hideddrivetip(){
if (ns6||ie){
enabletip=false
tipobj.style.visibility="hidden"
tipobj.style.left="-1000px"
tipobj.style.backgroundColor=''
tipobj.style.width=''
}
}

document.onmousemove=positiontip

</script>
<?php

echo"<table width=80%><tr><td class=colhead align=center><h1>Hangman</h1></td></tr><tr><td class=clearalt6 align=center>" . "" . ($_GET['categories'] ? "" : "<br><h1>categories</h1><p>pick a category from the selections below.</p><p><a class=altlink href=?categories=1 onMouseover=\"ddrivetip('General horror movie stuff... could be film name, actors, directors... anything.','yellow', 380)\" onMouseout=\"hideddrivetip()\">Horror Movies general</a> | <a class=altlink href=?categories=2 onMouseover=\"ddrivetip(' A Scream Queen is a slang term for an actress who is known for appearing in horror films. Known for that really high pitched scream upon getting killed by or first witnessing the monster in said horror film, a Screem Queen is usually attacked, killed, or the one who finally slays the monster at the end of the film. A Scream Queen may or may not be seen in the nude in said film. Nudity isn t a requirement but IT DOESN T HURT EITHER!!!','yellow', 380)\" onMouseout=\"hideddrivetip()\">Screem Queens!</a> " . " | <a class=altlink href=?categories=3 onMouseover=\"ddrivetip('Test your knowledge challenge yourself... who did write that horror story? Writers big and small...','yellow', 380)\" onMouseout=\"hideddrivetip()\">Horror Writers!</a> | <a class=altlink href=?categories=4 onMouseover=\"ddrivetip('The Slasher Film... who stalks and graphically murders a series of victims in a random, unprovoked fashion, usually teenagers or young adults, who are way from mainstream civilization or far away from help and often involved in sexual activities and/or illegal-drug use.','yellow', 380)\" onMouseout=\"hideddrivetip()\">Slasher Films!</a> | <a class=altlink href=?categories=5 onMouseover=\"ddrivetip('A serial killer is someone who murders three or more people in three or more separate events over a period of time. Many serial killers suffer from Antisocial Personality Disorder and usually not psychosis, and thus appear to be quite normal and often even charming, a state of adaptation which Hervey Cleckley calls the  mask of sanity.  There is sometimes a sexual element to the murders. The murders may have been completed/attempted in a similar fashion and the victims may have had something in common, for example occupation, race, sex, etc. In the United States the majority (73%) of serial killers have been white males in their 20s, and usually from working class or poor backgrounds.','yellow', 380)\" onMouseout=\"hideddrivetip()\">Serial Killers!</a>" . " | <a class=altlink href=?categories=6 onMouseover=\"ddrivetip('Any movie made during the Hollywood studio system era which has also received significant recognition, either at the time it was released or subsequently, by connoisseurs of great movies.','yellow', 380)\" onMouseout=\"hideddrivetip()\">Classic Horror Films!</a></p><br></td></tr><tr><td class=clearalt6 align=center>") . "";
/*
#####################################################################
# PHP Hangman Game #
# Version 1.2.0 #
# ©2002,2003 0php.com - Free PHP Scripts #
#####################################################################

#####################################################################
# #
# Author : 0php.com #
# Created : July 12, 2002 #
# Modified : March 22, 2004 #
# E-mail : webmaster@0php.com #
# Website : http://www.0php.com/ #
# License : FREE (GPL); See Copyright and Terms below #
# #
# Donations accepted via PayPal to webmaster@0php.com #
# #
#####################################################################

>> Copyright and Terms:

This software is copyright © 2002-2004 0php.com. It is distributed
under the terms of the GNU General Public License (GPL). Because it is licensed
free of charge, there is NO WARRANTY, it is provided AS IS. The author can not
be held liable for any damage that might arise from the use of this software.
Use it at your own risk.

All copyright notices and links to 0PHP.com website MUST remain intact in the scripts and in the HTML for the scripts.

For more details, see http://www.0php.com/license_GNU_GPL.php (or http://www.gnu.org/).

>> Installation
Copy the PHP script and images to the same directory. You can optionally edit the category and list of words/phrases to solve below. You can also add additional characters to $additional_letters and/or $alpha if you want to use international (non-English) letters or other characters not included by default (see further instructions below for those).

To prevent Google from playing hangman, add the line below between <HEAD> and </HEAD>:
<META NAME="robots" CONTENT="NOINDEX,NOFOLLOW">


================================================================================
=======*/
// === general horror
$list_1 = "BLOOD AND GUTS
JASON VOORHEES
THE NIGHT STALKER
CAMP CRYSTAL LAKE
FREDDY KRUGER
EDGAR ALLAN POE
THE ADDAMS FAMILY
CHRISTOPHER LEE
PETER CUSHING
NIGHTMARE ON ELM STREET
AMITYVILLE HORROR
TALES FROM THE CRYPT
ROSEMARY'S BABY
LAST HOUSE ON THE LEFT
VINCENT PRICE
THE SILENCE OF THE LAMBS
PETER LORRE
ANTHONY PERKINS
BORIS KARLOFF
ALFRED HITCHCOCK
BRUCE CAMPBELL
LEATHERFACE
DIAL M FOR MURDER
THE EVIL DEAD";
// === screem queens
$list_2 = "BRINKE STEVENS
ADRIENNE KING
LINNEA QUIGLEY
MICHELLE BAUER
KATHERINE ISABELLE
ASHLEY LAURENCE
MISTY MUNDAE
JOY THOMPSON
JAMIE LEE CURTIS
SARAH MICHELLE GELLAR
SOLEDAD MIRANDA
BARBARA SHELLEY
JAIMIE ALEXANDE
ANDREA BOGART
MERCEDES MCNAB
TIFFANY SHEPIS
CERINA VINCENT
JANET LEIGH
BARBARA STEELE
KATHARINE ISABELLE
DEBBIE ROCHON
KARI WUHNER
BRINKE STEVENS
NANCY ALLEN
ADRIENNE BARBEAU
JESSICA BIEL
ANGELA BETTIS
LINDY BOOTH
MARILYN BURNS N
EVE CAMPBELL
TIPPI HEDREN
HEATHER LANGENKAMP
ROSE MCGOWAN
KIM NOVAK
EMILY PERKINS
INGRID PITT
JILL SCHOELEN
AMY STEEL
LILI TAYLOR
NAOMI WATTS
JOBETH WILLIAMS
SHERI MOON ZOMBIE";
// === horror writers
$list_3 = "CLIVE BARKER
ALGERNON BLACKWOOD
WILLIAM PETER BLATTY
ROBERT BLOCH
POPPY Z. BRITE
NANCY A. COLLINS
HOWARD PHILLIPS LOVECRAFT
S»PHERA GIR”N
NATHANIEL HAWTHORNE
NANCY KILPATRICK
JACK KETCHUM
STEPHEN KING
DEAN R. KOONTZ
EDGAR ALLAN POE
ANNE RICE
ANN RADCLIFFE
JOHN RUSSO
MARY WOLLSTONECRAFT SHELLEY
BRAM STOKER
PETER STRAUB
NANCY HOLDER
AMBROSE BIERCE
GARY A. BRAUNBECK";
// === slasher films
$list_4 = "A NIGHT TO DISMEMBER
A NIGHTMARE ON ELM STREET
ADAM & EVIL
BLACK CHRISTMAS
BLEED
BLOOD AND LACE
BLOOD LEGACY
BLOOD RAGE
BLOODMOON
THE BOOGEYMAN
CAMP BLOOD
CAMP SLAUGHTER
CANDYMAN
CHEERLEADER CAMP
CHEERLEADER MASSACRE
CHERRY FALLS
THE CHRISTMAS SEASON MASSACRE
CITY IN PANIC
COMMUNION
CURTAINS
DARK HERITAGE
DEAD & BREAKFAST
DEAD MARY
DEADLY INTRUDER
DOOM ASYLUM
EVIL LAUGH
THE FLESH AND BLOOD SHOW
FREDDY VS. JASON
FRIDAY THE 13TH
FRIGHTMARE
THE FUNHOUSE
GRADUATION DAY
GRANNY
HALLOWEEN
HANDS OF THE RIPPER
HARVEST OF FEAR
HEEBIE JEEBIES
HELL HIGH
HELL NIGHT
HIDE AND GO SHRIEK
HOSPITAL MASSACRE
I, MADMAN
INTRUDER
JASON X
JIGSAW
JUST BEFORE DAWN
KILLER PARTY
LIGHTHOUSE
MACHINED
MADMAN
THE MAJORETTES
MANIAC MEMORIAL
VALLEY MASSACRE
MOUNTAINTOP MOTEL MASSACRE
THE MOVIE HOUSE MASSACRE
MY BLOODY VALENTINE
THE NAIL GUN MASSACRE
NIGHT SCHOOL
NIGHTMARE BEACH
PIECES
POPCORN
PROM NIGHT
THE PROWLER
SCHIZO
SCREAM
SEE NO EVIL
SHOCKER
SILENT NIGHT, BLOODY NIGHT
SINYSTER
SLASHERS
SLAUGHTER HIGH
SLEEPAWAY CAMP
SLEEPOVER NIGHTMARE
THE SLUMBER PARTY MASSACRE
SPLATTER UNIVERSITY
TERROR NIGHT
TERROR TRAIN
THE TOOLBOX MURDERS
TRESPASSING
VALENTINE
WAXWORK
WRESTLEMANIAC
WRONG TURN
YOU BETTER WATCH OUT";
// === serial killers + akas
$list_5 = "PAUL BERNARDO
KARLA HOMOLKA
CLIFFORD OLSON
DAVID BERKOWITZ
SON OF SAM
THE .44 CALIBER KILLER
TED BUNDY
BLOODY BENDERS
HILLSIDE STRANGLERS
THE SHOE-FETISH SLAYER
JERRY BRUDOS
RICHARD CHASE
VAMPIRE OF SACRAMENTO T
HE CANDY MAN
JUAN VALLEJO CORONA
CHARLES CULLEN
JEFFREY DAHMER
THE BOSTON STRANGLER
ALBERT DESALVO
THE GIGGLING GRANNY
NANNIE DOSS
LONELY HEARTS KILLERS
ALBERT FISH
JOHN WAYNE GACY
THE KILLER CLOWN
ED GEIN
ANGEL OF DEATH
DONALD HARVEY
HARRY HOWARD HOLMES
H. H. HOLMES
BROOKLYN STRANGLER
PATRICK KEARNEY
DERRICK TODD LEE
THE BATON ROUGE SERIAL KILLER
DENNIS RADER
THE BTK KILLER
RICHARD RAMIREZ
THE NIGHT STALKER
GARY RIDGWAY
THE GREEN RIVER KILLER
RIPPER CREW
THE BIKE PATH KILLER
GERARD JOHN SCHAEFER
THE KILLER COP
THE FLORIDA SEX BEAST
ARTHUR SHAWCROSS
THE GENESEE RIVER KILLER
CARY STAYNER
THE YOSEMITE MURDERER
WILLIAM SUFF
THE RIVERSIDE KILLER
CORAL EUGENE WATTS
THE SUNDAY MORNING SLASHER
WAYNE WILLIAMS
THE ATLANTA CHILD MURDERER
RANDALL WOODFIELD
THE I-5 KILLER
ALPHABET KILLER
AXEMAN OF NEW ORLEANS
THE MAD BUTCHER OF KINGSBURY RUN
SUFFOLK RIPPER
RED LIGHT RIPPER
JACK THE RIPPER
ZODIAC KILLER
JOHN CHRISTIE
THE NECROPHILE
ACID BATH MURDERER
THE VAMPIRE OF LONDON
HOUSE OF HORRORS MURDERERS
HAROLD SHIPMAN
FRED WEST
ROSEMARY WEST";
// === classic Horror Films
$list_6 = "THE BAD SEED
THE BIRDS
THE BLOB
THE BODYSNATCHER
BRIDE OF FRANKENSTEIN
THE CAT AND THE CANARY
THE CABINET OF DOCTOR CALIGARI
THE CREATURE FROM THE BLACK LAGOON
DOCTOR JEKYLL AND MISTER HYDE
DRACULA
FRANKENSTEIN
HOUSE OF FRANKENSTEIN
THE HOUSE OF WAX
INVASION OF THE BODY SNATCHERS
THE INVISIBLE MAN
KING KONG
MARK OF THE VAMPIRE
THE MUMMY
NOSFERATU
THE PHANTOM OF THE OPERA
PSYCHO
THE THING
THE WOLFMAN
CAT PEOPLE
I WALKED WITH WITH A ZOMBIE
THE SEVENTH VICTIM
THE LODGER
THE UNINVITED
THE PICTURE OF DORIAN GRAY";

$categories = isset($_GET['categories']) ? (int)$_GET['categories'] : 0;

switch ($categories) {
    case 1:
        $list = $list_1;
        $cat = 'Horror Movies general';
        break;
    case 2:
        $list = $list_2;
        $cat = 'Screem Queens';
        break;
    case 3:
        $list = $list_3;
        $cat = 'Horror Writers';
        break;
    case 4:
        $list = $list_4;
        $cat = 'Slasher Films';
        break;
    case 5:
        $list = $list_5;
        $cat = 'Serial Killers';
        break;
    case 6:
        $list = $list_6;
        $cat = 'Classic Horror Films';
        break;
    default:
        return false;
}
// make sure that any characters to be used in $list are in either
// $alpha OR $additional_letters, but not in both. It may not work if you change fonts.
// You can use either upper OR lower case of each, but not both cases of the same letter.
// below ($alpha) is the alphabet letters to guess from.
// you can add international (non-English) letters, in any order, such as in:
// $alpha = "¿¡¬√ƒ≈∆«»… ÀÃÕŒœ—“”‘’÷ÿŸ⁄€‹›üABCDEFGHIJKLMNOPQRSTUVWXYZ";
$alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
// below ($additional_letters) are extra characters given in words; '?' does not work
// these characters are automatically filled in if in the word/phrase to guess
$additional_letters = " -.,;'!?%&0123456789»“”";
// ========= do not edit below here ======================================================
echo<<<endHTML
endHTML;

$len_alpha = strlen($alpha);

if (isset($_GET["n"])) $n = $_GET["n"];
if (isset($_GET["letters"])) $letters = $_GET["letters"];
if (!isset($letters)) $letters = "";

if (isset($PHP_SELF)) $self = $PHP_SELF;
else $self = $_SERVER["PHP_SELF"];

$links = "";

$max = 6; # maximum number of wrong
// error_reporting(0);
$list = strtoupper($list);
$words = explode("\n", $list);
srand ((double)microtime() * 1000000);
$all_letters = $letters . $additional_letters;
$wrong = 0;

if (!isset($n)) {
    $n = rand(1, count($words)) - 1;
}
$word_line = "";
$word = trim($words[$n]);
$done = 1;
for ($x = 0; $x < strlen($word); $x++) {
    if (strstr($all_letters, $word[$x])) {
        if ($word[$x] == " ") $word_line .= "&nbsp; ";
        else $word_line .= $word[$x];
    } else {
        $word_line .= "_<font size=1>&nbsp;</font>";
        $done = 0;
    }
}

if (!$done) {
    for ($c = 0; $c < $len_alpha; $c++) {
        if (strstr($letters, $alpha[$c])) {
            if (strstr($words[$n], $alpha[$c])) {
                $links .= "\n<B>$alpha[$c]</B> ";
            } else {
                $links .= "\n<FONT color=\"red\">$alpha[$c] </font>";
                $wrong++;
            }
        } else {
            $links .= "\n<A HREF=\"$self?letters=$alpha[$c]$letters&n=$n&categories=$categories\">$alpha[$c]</A> ";
        }
    }
    $nwrong = $wrong;
    if ($nwrong > 6) $nwrong = 6;
    echo "\n<p><BR>\n<IMG SRC=\"pic/hangman_$nwrong.gif\" ALIGN=\"MIDDLE\" BORDER=0 WIDTH=300 ALT=\"Wrong: $wrong out of $max\">\n";

    if ($wrong >= $max) {
        $n++;
        if ($n > (count($words)-1)) $n = 0;
        echo "<BR><BR><H1><font size=5>\n$word_line</font></H1>\n";
        echo '<p><BR><FONT color=red><h1>SORRY, YOU ARE HANGED!!!</h1></FONT><BR><BR>';
        if (strstr($word, " ")) $term = "phrase";
        else $term = "word";
        echo "The $term was \"<B>$word</B>\"<BR><BR>\n";
        echo '<a class=altlink href=?>Play again?</a>';
        echo"<br><br>[ category was: <a class=altlink href=?categories=$categories><b>$cat</b></a> ]<br>";
    } else {
        echo " &nbsp; number of guesses left: <B>" . ($max - $wrong) . "</B><BR>\n";
        echo "<H1><font size=5>\n$word_line</font></H1>\n";
        echo '<P><BR>Choose a letter:<BR><BR>';
        echo "$links\n";
        echo"<br><br>[ category is: <b>$cat</b> ]<br>";
    }
} else {
    $n++; # get next word
    if ($n > (count($words)-1)) $n = 0;
    echo "<BR><BR><H1><font size=5>\n$word_line</font></H1>\n";
    echo '<P><BR><BR><B>Congratulations!!! You win!!!</B><BR><BR><BR>';
    echo '<a class=altlink HREF=?>Play again?</a>';
    echo"<br><br>[ category was: <a class=altlink href=?categories=$categories><b>$cat</b></a> ]<br>";
}
echo<<<endHTML
endHTML;
echo'<br><br><br></td></tr></table>';
stdfoot();

?>