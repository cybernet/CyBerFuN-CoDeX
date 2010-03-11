<?php
require ("include/bittorrent.php");
require_once ("include/bbcode_functions.php");
require_once ("include/user_functions.php");
dbconn();
// promo mod by putyn 24/2/2009
$do = (isset($_GET["do"]) ? $_GET["do"] : (isset($_POST["do"])? $_POST["do"] : ""));
$id = (isset($_GET["id"]) ? 0 + $_GET["id"] : (isset($_POST["id"])? 0 + $_POST["id"] : "0"));
$link = (isset($_GET["link"]) ? $_GET["link"] : (isset($_POST["link"])? $_POST["link"] : ""));
$sure = (isset($_GET["sure"]) && $_GET["sure"] == "yes" ? "yes" : "no");
// err msg
function err($txt = "Some errors occurred!")
{
    stderr("Err", $txt);
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $do == "addpromo") {
    $promoname = (isset($_POST["promoname"]) ? $_POST["promoname"] : "");
    if (empty($promoname))
        err("No name for the promo");
    $days_valid = (isset($_POST["days_valid"]) ? 0 + $_POST["days_valid"] : 0);
    if ($days_valid == 0)
        err("Link will be valid for 0 days ? I don't think so!");
    $max_users = (isset($_POST["max_users"]) ? 0 + $_POST["max_users"] : 0);
    if ($max_users == 0)
        err("Max users cant be 0 i think you missed that!");

    $bonus_upload = (isset($_POST["bonus_upload"]) ? 0 + $_POST["bonus_upload"] : 0);
    $bonus_invites = (isset($_POST["bonus_invites"]) ? 0 + $_POST["bonus_invites"] : 0);
    $bonus_karma = (isset($_POST["bonus_karma"]) ? 0 + $_POST["bonus_karma"] : 0);
    if ($bonus_upload == 0 && $bonus_invites == 0 && $bonus_karma == 0)
        err("No gift for the new users ?! :w00t: give them some gifts :D");

    $link = md5("promo_link" . time());

    $q = mysql_query("INSERT INTO promo (name,added,days_valid,max_users,link,creator,bonus_upload,bonus_invites,bonus_karma) VALUES (" . implode(",", array_map("sqlesc", array($promoname, time(), $days_valid, $max_users, $link, $CURUSER["id"], $bonus_upload, $bonus_invites, $bonus_karma))) . ") ") or sqlerr(__FILE__, __LINE__);

    if (!$q)
        err("Something wrong happned, please retry");
    else
        stderr("Succes", "The promo link <b>" . $promoname . "</b> was added! here is the link <br/><input type=\"text\" name=\"promo-link\" value=\"" . $DEFAULTBASEURL . $_SERVER["PHP_SELF"] . "?do=signup&amp;link=" . $link . "\" size=\"80\" onclick=\"select();\"  /><br/><a href=\"" . $_SERVER["PHP_SELF"] . "\"><input type=\"button\" value=\"Back to Promos\" />");
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $do == "signup") {
    // err("w00t");
    $r_check = mysql_query("SELECT * FROM promo WHERE link=" . sqlesc($link)) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($r_check) == 0)
        err("The link your using is not a valid link");
    else {
        $ar_check = mysql_fetch_assoc($r_check);

        if ($ar_check["max_users"] == $ar_check["accounts_made"])
            err("Sorry account limit (" . $ar_check["max_users"] . ") on this link has been reached ");
        if (($ar_check["added"] + (86400 * $ar_check["days_valid"])) < time())
            err("This link was valid only till " . date("d/M-Y", ($ar_check["added"] + (86400 * $ar_check["days_valid"]))));
        // some variables for the new user :)
        $username = (isset($_POST["username"])? $_POST["username"] : "");
        if (empty($username))
            err("You must pick a an username");
        if (strlen($username) < 4 || strlen($username) > 12)
            err("Your username is to long or to short (min 4 char , max 12 char)");

        $password = (isset($_POST["password"]) ? $_POST["password"] : "");
        $passwordagain = (isset($_POST["passwordagain"]) ? $_POST["passwordagain"] : "");
        if (empty($password) || empty($passwordagain))
            err("You have to type your passwords twice");
        if ($password != $passwordagain)
            err("The passwords didn't match! Must've typoed. Try again.");
        if (strlen($password) < 6)
            err("Password must be min 6 char");
        // create password
        $secret = mksecret();
        $passhash = md5($secret . $password . $secret);
        $editsecret = mksecret();

        $email = (isset($_POST["mail"]) ? $_POST["mail"] : "");
        if (empty($email))
            err("No email adress, you forgot about that?");
        if (!validemail($email))
            err("That dosen't look like an email adress");
        // check if username or password already exists
        $var_check = mysql_query("SELECT id FROM users where username=" . sqlesc($username) . " OR email=" . sqlesc($email))or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($var_check) == 1)
            err("Username or password already exists");

        $modcomment = gmdate("Y-m-d") . " - Account made using promo link " . $ar_check["name"] . "\n";

        $res = mysql_query("INSERT INTO users(username, passhash, secret, editsecret, email,added,modcomment,uploaded,invites,seedbonus) VALUES (" . implode(",", array_map("sqlesc", array($username, $passhash, $secret, $editsecret, $email, get_date_time(), $modcomment, ($ar_check["bonus_upload"] * 1073741824), $ar_check["bonus_invites"], $ar_check["bonus_karma"]))) . ") ") or sqlerr(__FILE__, __LINE__);

        if ($res) {
            // updating promo table
            $userid = mysql_insert_id();
            $users = (empty($ar_check["users"]) ? $userid : $ar_check["users"] . "," . $userid);
            mysql_query("update promo set accounts_made=accounts_made+1 , users=" . sqlesc($users) . " WHERE id=" . $ar_check["id"]) or sqlerr(__FILE__, __LINE__);
            // email part :)
            $md5 = md5($editsecret);
            $subject = $SITENAME . " user registration confirmation";
            $message = "Hi!
You used the link from promo " . $ar_check["name"] . " and registered a new account at " . $SITENAME . "

To confirm your account click the link below
$DEFAULTBASEURL/confirm.php?id=$userid&secret=$md5

Welcome and enjoy your stay
Staff at $SITENAME
";
            $headers = 'From:' . $SITEEMAIL . "\r\n" . 'Reply-To:' . $SITEEMAIL . "\r\n" . 'X-Mailer: PHP/' . phpversion();

            $mail = @mail($email, $subject, $message, $headers);

            stderr ("Succes!", "Account was created! and an email was sent to <b>" . htmlspecialchars($email) . "</b>, you could use your account only after you confirm it!");
        } else
            err("Something odd happned please retry");
    }
} elseif ($do == "addpromo") {
    if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
    }
    if (get_user_class() < UC_MODERATOR)
        err("There is nothing for you here! Go play somewere else");
    stdhead("Add Promo Link");
    begin_frame("Add Promo Link");

    ?>
				<form action="<?=($_SERVER["PHP_SELF"])?>" method="post" >
					<table width="50%" align="center" style="border-collapse:collapse" border="1" cellpadding="10" cellspacing="0">
					  <tr>
						<td nowrap="nowrap" align="right" colspan="1">Promo Name</td>
						<td align="left" width="100%" colspan="3"><input type="text" name="promoname" size="60"  /></td>
					  </tr>
					  <tr>
					  <td nowrap="nowrap" align="right" >Days valid</td>
						<td align="left" width="100%" colspan="1"><input type="text" name="days_valid" size="15"  /></td>
						<td nowrap="nowrap" align="right" >Max users</td>
						<td align="left" width="100%" colspan="2"><input type="text" name="max_users" size="15"  /></td>
					  </tr>
					  <tr>
						<td align="right" rowspan="3" nowrap="nowrap" valign="top">Bonuses</td>
					  </tr>
					  <tr>
						<td align="center">Upload</td>
						<td align="center">Invites</td>
						<td align="center">Karma</td>
					  </tr>
					  <tr>
						<td align="center"><input type="text" name="bonus_upload" size="15" /></td>
						<td align="center"><input type="text" name="bonus_invites" size="15" /></td>
						<td align="center"><input type="text" name="bonus_karma" size="15" /></td>
					  </tr>
					  <tr><td align="center" colspan="4"><input type="hidden" value="addpromo" name="do"  /><input type="submit" value="Add Promo!" /></td></tr>
					</table>
				</form>
			<?php
    end_frame();
    stdfoot();
} elseif ($do == "signup") {
    if (empty($link))
        err("There is no link found! Please check the link");
    else {
        $r_promo = mysql_query("SELECT * from promo where link=" . sqlesc($link)) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($r_promo) == 0)
            err("There is no promo with that link ");
        else {
            $ar = mysql_fetch_assoc($r_promo);

            if ($ar["max_users"] == $ar["accounts_made"])
                err("Sorry account limit (" . $ar["max_users"] . ") on this link has been reached ");
            if (($ar["added"] + (86400 * $ar["days_valid"])) < time())
                err("This link was valid only till " . date("d/M-Y", ($ar["added"] + (86400 * $ar["days_valid"]))));
            stdhead("Signup for promo :" . $ar["name"]);
            begin_frame();

            ?>
						<form action="<?=($_SERVER["PHP_SELF"])?>" method="post">
						  <table cellpadding="10" width="50%" align="center" cellspacing="0"  border="1" style="border-collapse:collapse" >
						  <tr><td class="colhead" align="center" colspan="2">Promo : <?=($ar["name"])?> </td></tr><tr>
						 <tr> <td nowrap="nowrap" align="right">Bonuses</td>
							  <td align="left" width="100%">
								<?=($ar["bonus_upload"] > 0 ? "<b>upload</b>:&nbsp;" . prefixed($ar["bonus_upload"] * 1073741824) . "<br/>" : "")?>
								<?=($ar["bonus_invites"] > 0 ? "<b>invites</b>:&nbsp;" . (0 + $ar["bonus_invites"]) . "<br/>" : "")?>
								<?=($ar["bonus_karma"] > 0 ? "<b>karma</b>:&nbsp;" . (0 + $ar["bonus_karma"]) . "<br/>" : "")?>

								</td></tr>
							  <td nowrap="nowrap" align="right">Username</td>
							  <td align="left" width="100%"><input type="text" size="40" name="username"  /></td>
							</tr>
							<tr><td nowrap="nowrap" align="right">Password</td><td align="left" width="100%"><input type="password" name="password" size="40" /></td></tr>
							<tr><td nowrap="nowrap" align="right">Password again</td><td align="left" width="100%"><input type="password" name="passwordagain" size="40" /></td></tr>
							<tr><td nowrap="nowrap" align="right">Email</td><td align="left" width="100%"><input type="text" name="mail" size="40"/></td></tr>
							<tr><td colspan="2" class="colhead" align="center"><input type="hidden" name="link" value="<?=($link)?>"/><input type="hidden" name="do" value="signup"/><input type="submit" value="SignUp!"  /></td></tr>
						  </table>
						</form>
						<?php
            end_frame();
            stdfoot();
        }
    }
} elseif ($do == "accounts") {
    if ($id == 0)
        die("Can't find id");
    else {
        $q1 = mysql_query("SELECT name,users FROM promo WHERE id=" . $id) or sqlerr(__FILE__, __LINE__);
        if (mysql_num_rows($q1) == 1) {
            $a1 = mysql_fetch_assoc($q1);
            if (!empty($a1["users"])) {
                $users = explode(",", $a1["users"]);
                if (!empty($users))
                    $q2 = mysql_query("SELECT id,username,added from users where id IN (" . join(",", $users) . ") AND status='confirmed' ") or sqlerr(__FILE__, __LINE__);

                ?>
				 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>Users list for promo : <?=$a1["name"]?> </title>
					<style type="text/css">
					body { background-color:#999999;
					color:#333333;
					font-family:tahoma;
					font-size:12px;
					font-weight:bold;}
					a:link, a:hover , a:visited {
					color:#FFFFFF;
					}
					.rowhead { background-color:#0033FF;
					color:#CCCCCC;}
					</style>
					</head>

					<body>
					<table width="200" cellpadding="10" border="1" align="center" style="border-collapse:collapse">
						<tr><td class="rowhead" align="left" width="100"> User</td><td class="rowhead" align="left" nowrap="nowrap" >Added</td></tr>
						<?php
                while ($ar = mysql_fetch_assoc($q2)) {
                    print("<tr><td align=\"left\" width=\"100\"><a href=\"userdetails.php?id=" . $ar["id"] . "\">" . $ar["username"] . "</a></td><td  align=\"left\" nowrap=\"nowrap\" >" . $ar["added"] . "</td></tr>");
                }

                ?>

					</table>
						<br/>
					<div align="center"><a href="javascript:close()"><input type="button" value="Close" /></a></div>
					</body>
					</html>

				 <?php

            } else die("No users");
        } else die("Something odd happend");
    }
} else {
    if (!logged_in()) {
    header("HTTP/1.0 404 Not Found");
    // moddifed logginorreturn by retro//Remember to change the following line to match your server
    print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at " . $_SERVER['SERVER_NAME'] . " Port 80</address></body></html>\n");
    die();
    }
    if (get_user_class() < UC_MODERATOR)
        err("There is nothing for you here! Go play somewere else");

    $r = mysql_query("SELECT p.*,u.username from promo as p LEFT JOIN users as u on p.creator=u.id ORDER by p.added,p.days_valid DESC") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($r) == 0)
        err("There is no promo if you want to make one click <a href=\"" . $_SERVER["PHP_SELF"] . "?do=addpromo\">here</a>");
    else {
        stdhead("Current Promos");
        begin_frame("Current Promos&nbsp;<font class=\"small\"><a href=\"" . $_SERVER["PHP_SELF"] . "?do=addpromo\">add promo</a></font>");

        ?>
		<script type="text/javascript">
		function link(id)
		{
			wind = window.open('promo.php?do=accounts&id='+id,' ','height=300,width=320,resizable=yes,scrollbars=yes,toolbar=no,menubar=no');
			wind.focus();
		 }
		</script>
		<table align="center" width="100%" cellpadding="5" cellspacing="0" border="1" style="border-collapse:collapse">
			<tr>
				<td align="left" width="100%" rowspan="2">Promo</td>
				<td align="center"  nowrap="nowrap" rowspan="2">Added</td>
				<td align="center"  nowrap="nowrap" rowspan="2">Valid Till</td>
				<td align="center"  nowrap="nowrap" colspan="2">Users</td>
				<td align="center"  nowrap="nowrap" colspan="3" >Bonuses</td>
				<td align="center"  nowrap="nowrap" rowspan="2">Added by</td>
			</tr>
			<tr>
				<td align="center"  nowrap="nowrap">max</td>
				<td align="center"  nowrap="nowrap">till now</td>
				<td align="center"  nowrap="nowrap" >upload</td>
				<td align="center"  nowrap="nowrap" >invites</td>
				<td align="center"  nowrap="nowrap" >karma</td>
			</tr>
		<?php
        while ($ar = mysql_fetch_assoc($r)) {
            $active = (($ar["max_users"] == $ar["accounts_made"]) || (($ar["added"] + (86400 * $ar["days_valid"])) < time())) ? false : true;

            ?>
			<tr <?=(!$active ? "title=\"This promo has ended\"" : "")?>>
				<td nowrap="nowrap" align="center"><?=(htmlspecialchars($ar["name"]))?><br/><input type="text" <?=(!$active ? "disabled=\"disabled\"" : "")?> value="<?=($DEFAULTBASEURL . $_SERVER["PHP_SELF"] . "?do=signup&amp;link=" . $ar["link"])?>" size="60" name="<?=(htmlspecialchars($ar["name"]))?>" onclick="select();" /></td>
				<td nowrap="nowrap" align="center"><?=(date("d/M-Y", $ar["added"]))?></td>
				<td nowrap="nowrap" align="center"><?=(date("d/M-Y", ($ar["added"] + (86400 * $ar["days_valid"]))))?></td>
				<td nowrap="nowrap" align="center"><?=(0 + $ar["max_users"])?></td>
				<td nowrap="nowrap" align="center"><?=($ar["accounts_made"] > 0 ? "<a href=\"javascript:link(" . $ar["id"] . ")\" >" . $ar["accounts_made"] . "</a>" : 0)?></td>
				<td nowrap="nowrap" align="center"><?=(prefixed($ar["bonus_upload"] * 1073741824))?></td>
				<td nowrap="nowrap" align="center"><?=(0 + $ar["bonus_invites"])?></td>
				<td nowrap="nowrap" align="center"><?=(0 + $ar["bonus_karma"])?></td>
				<td nowrap="nowrap" align="center"><a href="userdetails.php?id=<?=$ar["creator"]?>"><?=$ar["username"]?></a></td>
			</tr>


		<?php
        }
        print("</table>");
        end_frame();
        stdfoot();
    }
}

?>