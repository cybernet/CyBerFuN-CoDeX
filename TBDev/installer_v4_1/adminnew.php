<?php
require "include/bittorrent.php";
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
maxcoder();
if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
}
if (get_user_class() < UC_MODERATOR)
    hacker_dork("Db Stuff - Nosey Cunt !");
$allowed_ids = array(1,2);
if (!in_array($CURUSER['id'], $allowed_ids))
    stderr('Error', 'Access Denied!');
//begin staff secure, comment to turn off, uncomment to turn on//
//secureip(UC_MODERATOR);
//end of staff secure//
stdhead("MySQL Query Editor");
begin_frame();
if (isset($_POST['submitquery'])) {
   //if (unsafeChar()) $_POST['query'] = safeChar($_POST['query']);
    if (get_magic_quotes_gpc()) $_POST['query'] = stripslashes($_POST['query']);
    echo('<p><b>Query:</b><br />' . nl2br($_POST['query']) . '</p>');
    mysql_select_db($_POST['db']);
    $result = mysql_query($_POST['query']);
    if ($result) {
        if (@mysql_num_rows($result)) {

            ?>
                       <p><b>Result Set:</b></p>
                       <table border="1">
                       <thead>
                       <tr>
                       <?php
            for ($i = 0;$i < mysql_num_fields($result);$i++) {
                echo('<th>' . mysql_field_name($result, $i) . '</th>');
            }

            ?>
                       </tr>
                       </thead>
                       <tbody>
                       <?php
            while ($row = mysql_fetch_row($result)) {
                echo('<tr>');
                for ($i = 0;$i < mysql_num_fields($result);$i++) {
                    echo('<td>' . $row[$i] . '</td>');
                }
                echo('</td></tr>');
            }

            ?>
                       </tbody>
                       </table>
                       <?php
        } else {
            echo('<p><b>Query OK:</b> ' .safeChar( mysql_affected_rows()) . ' rows affected.</p>');
        }
    } else {
        echo('<p><b>Query Failed:</b> ' .safeChar( mysql_error()) . '</p>');
    }
    echo('<hr />');
}

?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">

<p>SQL Query:<br />
<textarea onFocus="this.select()" cols="60" rows="5" name="query">
<?=safechar($_POST['query'])?>
</textarea>
</p>
<p><input type="submit" name="submitquery" value="Submit Query (Alt-S)" accesskey="S" /></p>
</form>

<?php
// cpfooter();
end_frame();

stdfoot();

