<?php
require_once("include/bittorrent.php");
require_once "include/user_functions.php";
$sha = sha1($_SERVER['REMOTE_ADDR']);
if (is_file('' . $dictbreaker . '/' . $sha) && filemtime('' . $dictbreaker . '/' . $sha) > (time()-8)) {
    @fclose(@fopen('' . $dictbreaker . '/' . $sha, 'w'));
    die('Minimum 8 seconds between login attempts :)');
}

if (!mkglobal("username:password:captcha"))
    die();

session_start();
if (empty($captcha) || $_SESSION['captcha_id'] != strtoupper($captcha)) {
    header('Location: login.php');
    exit();
}

dbconn();
maxcoder();
// == Function redirect -- by mistero
function fix_url ($url)
{
    $url = htmlspecialchars ($url);
    $f[0] = '&';
    $f[1] = ' ';
    $f[2] = '  ';
    $r[0] = '&';
    $r[1] = '&nbsp;';
    $r[2] = '&nbsp;&nbsp;';
    return str_replace ($f, $r, $url);
}
function redirect($url, $message = '', $title = '', $wait = 3, $usephp = false, $withbaseurl = true)
{
    global $SITENAME, $BASEURL;
    if (empty($message))
        $message = "You will now be redirected...";
    if (empty($title))
        $title = $SITENAME;
    $url = fix_url($url);
    if ($withbaseurl)
        $url = $BASEURL . (substr($url, 0, 1) == '/' ? '' : '/') . $url;
    if ($usephp) {
        @header ('Location: ' . $url);
        exit;
    }
    ob_start();

    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DT...tional.dtd">
<html lang="en">
<head>
<title><?=$title;
    ?></title>
<meta http-equiv="refresh" content="<?=$wait;
    ?>;URL=<?=$url;
    ?>">
<link rel="stylesheet" href="<?=$BASEURL;
    ?>/themes/green/green.css" type="text/css" media="screen">
</head>
<body>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<div  align="center">
<table width="755" border="2" cellspacing="1" cellpadding="4" >
<tr>
<td bgcolor="#660000"><strong><a href="<?=$BASEURL;
    ?>"><center><?=$title;
    ?></a></center></strong></td>
</tr>
<tr>
<td align="center"><p><font color="#white"><?=$message;
    ?></font></p></td>
</tr>
<tr>
<td bgcolor="#660000" ><a href="<?=$url;
    ?>">
<span class="smalltext">Click here if you don't want to wait any longer.</span></a></td>
</tr>
</table>
</div>
</body>
</html>
<?php
    ob_end_flush();
    exit;
}

function bark($text = "Username or password incorrect")
{
    @fclose(@fopen('dictbreaker/' . sha1($_SERVER['REMOTE_ADDR']), 'w'));
    stderr("Login failed!", $text);
}

failedloginscheck ();

$res = sql_query("SELECT id, passhash, secret, enabled, logout FROM users WHERE username = " . sqlesc($username) . " AND status = 'confirmed'");
$row = mysql_fetch_assoc($res);

if (!$row) {
    $ip = sqlesc(getip());
    $added = sqlesc(get_date_time());
    $a = (@mysql_fetch_row(@mysql_query("select count(*) from loginattempts where ip=$ip"))) or sqlerr(__FILE__, __LINE__);
    if ($a[0] == 0)
        sql_query("INSERT INTO loginattempts (ip, added, attempts) VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
    else
        sql_query("UPDATE loginattempts SET attempts = attempts + 1 where ip=$ip") or sqlerr(__FILE__, __LINE__);
    @fclose(@fopen('' . $dictbreaker . '/' . sha1($_SERVER['REMOTE_ADDR']), 'w'));
    bark();
}

if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"])) {
    $ip = sqlesc(getip());
    $added = sqlesc(get_date_time());
    $a = (@mysql_fetch_row(@sql_query("select count(*) from loginattempts where ip=$ip"))) or sqlerr(__FILE__, __LINE__);
    if ($a[0] == 0)
        sql_query("INSERT INTO loginattempts (ip, added, attempts) VALUES ($ip, $added, 1)") or sqlerr(__FILE__, __LINE__);
    else
        sql_query("UPDATE loginattempts SET attempts = attempts + 1 where ip=$ip") or sqlerr(__FILE__, __LINE__);
    @fclose(@fopen('' . $dictbreaker . '/' . sha1($_SERVER['REMOTE_ADDR']), 'w'));
    $to = ($row["id"]);
    $msg = "[color=red]SECURITY[/color]\n Account: ID=" . $row['id'] . " Somebody (probably you, " . $username . "!) tried to login but failed!" . "\nTheir [b]IP ADDRESS [/b] was : " . $ip . " (" . @gethostbyaddr($ip) . ")" . "\n If this wasn't you please report this event to a staff \n - Thank you.\n";
    $sql = "INSERT INTO messages (sender, receiver, msg, added) VALUES('$from', '$to', " . sqlesc($msg) . ", $added);";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

    bark();
}

if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"]))
    bark();

if ($row["enabled"] == "no")
    bark("This account has been disabled.");

$duration = isset($_POST['logout']) ? 'yes' : 'no';
if ($duration != $row['logout'])
sql_query("UPDATE users SET logout = '$duration' WHERE id= $row[id]")or sqlerr(__file__, __line__);
$dt = get_date_time();
$ip = getip();
$ipf = $_SERVER['REMOTE_ADDR'];
sql_query('UPDATE users SET ip=' . sqlesc($ip) . ', ipf=' . sqlesc($ipf) . ', last_access='.sqlesc($dt).' WHERE id=' . $row['id']);
$passh = md5($row["passhash"] . $_SERVER["REMOTE_ADDR"]);
logincookie($row["id"], $passh);
$ip = sqlesc(getip());
sql_query("DELETE FROM loginattempts WHERE ip = $ip");

if (!empty($_POST["returnto"]))
    redirect ($_POST[returnto], "Please Wait While you are logged in....");
else
    $url = "index.php";
redirect ($url, "Please Wait While you are logged in....");
stdfoot();

?>