<?php
// bookmark.php - by pdq
require_once "include/bittorrent.php";
require_once "include/user_functions.php";
require_once "include/bbcode_functions.php";
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}

if ($usergroups['canbookmark'] == 'no' OR $usergroups['canbookmark'] != 'yes') {
stderr( "Sorry...", "You are not allowed to bookmark torrents" );
	exit;
}

function bark($msg)
{
    stdhead();
    stdmsg("Failed!", $msg);
    stdfoot();
    exit;
}

if (!mkglobal("torrent"))
    bark("missing form data");

$userid = $CURUSER['id'];
if (!is_valid_id($userid))
    stderr("Error", "Invalid ID.");

if ($userid != $CURUSER["id"])
    stderr("Error", "Access denied.");

$torrentid = 0 + $_GET["torrent"];
if (!is_valid_id($torrentid))
    die();

if (!isset($torrentid))
    bark ("Failed. No torrent selected");

$action = isset($_GET["action"]) ?$_GET["action"] : '';

if ($action == 'add') {
    $torrentid = (int)$_GET['torrent'];
    $sure = safeChar($_GET['sure']);
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");

    $hash = md5('the salt to' . $torrentid . 'add' . 'mu55y');
    if (!$sure)
        stderr("Confirm Bookmark", "Do you really want to add this bookmark? Click\n" . "<a href=?torrent=$torrentid&action=add&sure=1&h=$hash>here</a> if you are sure.", false);

    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    function addbookmark($torrentid)
    {
        global $CURUSER;

        if ((get_row_count("bookmarks", "WHERE userid=$CURUSER[id] AND torrentid = $torrentid")) > 0)
            bark("Torrent already bookmarked");

        mysql_query("INSERT INTO bookmarks (userid, torrentid) VALUES ($CURUSER[id], $torrentid)") or sqlerr(__FILE__, __LINE__);
    }

    addbookmark($torrentid);
    stdhead("Bookmark added!");
    echo '<h2>Bookmark added!</h2>';
}

if ($action == 'delete') {
    $torrentid = (int)$_GET['torrent'];
    $sure = safeChar($_GET['sure']);
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");

    $hash = md5('the salt to' . $torrentid . 'add' . 'mu55y');
    if (!$sure)
        stderr("Confirm Bookmark", "Do you really want to delete this bookmark? Click\n" . "<a href=?torrent=$torrentid&action=delete&sure=1&h=$hash>here</a> if you are sure.", false);

    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    function deletebookmark($torrentid)
    {
        global $CURUSER;
        mysql_query("DELETE FROM bookmarks WHERE torrentid = $torrentid AND userid = $CURUSER[id]");
    }

    deletebookmark($torrentid);
    stdhead("Bookmark deleted!");
    echo '<h2>Bookmark deleted!</h2>';
} elseif ($action == 'public') {
    $torrentid = (int)$_GET['torrent'];
    $sure = safeChar($_GET['sure']);
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");

    $hash = md5('the salt to' . $torrentid . 'add' . 'mu55y');
    if (!$sure)
        stderr("Confirm Bookmark", "Do you really want to mark this bookmark public? Click\n" . "<a href=?torrent=$torrentid&action=public&sure=1&h=$hash>here</a> if you are sure.", false);

    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    function publickbookmark($torrentid)
    {
        global $CURUSER;

        mysql_query("UPDATE bookmarks SET private = 'no' WHERE private = 'yes' AND torrentid = $torrentid AND userid = $CURUSER[id]");
    }

    publickbookmark($torrentid);
    stdhead("Bookmark made public!");
    echo '<h2>Bookmark made public!</h2>';
} elseif ($action == 'private') {
    $torrentid = (int)$_GET['torrent'];
    $sure = safeChar($_GET['sure']);
    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");

    $hash = md5('the salt to' . $torrentid . 'add' . 'mu55y');
    if (!$sure)
        stderr("Confirm Bookmark", "Do you really want to mark this bookmark private? Click\n" . "<a href=?torrent=$torrentid&action=private&sure=1&h=$hash>here</a> if you are sure.", false);

    if ($_GET['h'] != $hash)
        stderr('Error', 'what are you doing?');

    if (!is_valid_id($torrentid))
        stderr("Error", "Invalid ID.");

    function privatebookmark($torrentid)
    {
        global $CURUSER;

        mysql_query("UPDATE bookmarks SET private = 'yes' WHERE private = 'no' AND torrentid = $torrentid AND userid = $CURUSER[id]");
    }

    privatebookmark($torrentid);
    stdhead("Bookmark made private!");
    echo '<h2>Bookmark made private!</h2>';
}

if (isset($_POST["returnto"]))
    $ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Go back to whence you came</a>";
else
    $ret = "<a href=\"bookmarks.php\">Go to My Bookmarks</a><br /><br />
<a href=\"browse.php\">Go to Browse</a>";
echo $ret;
stdfoot();

?>