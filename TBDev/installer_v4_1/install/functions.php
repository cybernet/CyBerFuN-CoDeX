<?php
error_reporting(E_ALL ^ E_NOTICE);

function update_config()
{

	$config_data = '<?php'."\n\n";
	$config_data .= '$mysql_host = \'' . $_POST['server'] . '\';' . "\n";
	$config_data .= '$mysql_db = \'' . $_POST['dbname'] . '\';' . "\n";
	$config_data .= '$mysql_user = \'' . $_POST['dbuser'] . '\';' . "\n";
	$config_data .= '$mysql_pass = \'' . $_POST['dbpass'] . '\';' . "\n";
  $config_data .= '$BASEURL = \'' . $_POST['baseurl'] . '\';' . "\n\n";

	$config_data .= 'define(\'TB_INSTALLED\', true);'."\n\n";
	$config_data .= '?' . '>'; // Done this to prevent highlighting editors getting confused!


	if( !mysql_connect($_POST['server'],$_POST['dbuser'],$_POST['dbpass']) )
	{
		die('Cant connect to databaseserver');
	}
	if( !mysql_select_db($_POST['dbname']) )
	{
		die('Cant select database');
	}
	if(!($fp = fopen('secrets.php', 'w')))
	{
		die('Make secrets.php writable -> 777');
	}
	else
	{
			$result = @fputs($fp, $config_data, strlen($config_data));

			@fclose($fp);
	}

}


function basic_query()
{

	$sql_lines = implode(' ', file(dirname(__FILE__) . '/install.sql'));
	$sql_lines = explode("\n", $sql_lines);

	include('../install/secrets.php');

	if( !mysql_connect($mysql_host,$mysql_user,$mysql_pass) )
	{
		die('Cant connect to databaseserver');
	}
	if( !mysql_select_db($mysql_db) )
	{
		die('Cant select database');
	}

	// Execute the SQL.
	$current_statement = '';
	$failures = array();
	$exists = array();
	foreach ($sql_lines as $count => $line)
	{
		// No comments allowed!
		if (substr($line, 0, 1) != '#')
			$current_statement .= "\n" . rtrim($line);

		// Is this the end of the query string?
		if (empty($current_statement) || (preg_match('~;[\s]*$~s', $line) == 0 && $count != count($sql_lines)))
			continue;

		// Does this table already exist?  If so, don't insert more data into it!
		if (preg_match('~^\s*INSERT INTO ([^\s\n\r]+?)~', $current_statement, $match) != 0 && in_array($match[1], $exists))
		{
			$current_statement = '';
			continue;
		}

if (!mysql_query($current_statement))
{
 $error_message = 'wooOOpsie';


//		if (!mysql_query($current_statement))
//		{
//			$error_message = mysql_error('woOOpsie');

			// Error 1050: Table already exists!
			if (strpos($error_message, 'already exists') === false)
				$failures[$count] = $error_message;
			elseif (preg_match('~^\s*CREATE TABLE ([^\s\n\r]+?)~', $current_statement, $match) != 0)
				$exists[] = $match[1];
		}

		$current_statement = '';
	}
}
function insert_coder()
{
	if( $_POST['coderpass'] != $_POST['coderpass2'] )
	{
		die('error:  The coder passwords do not match!');
	}

	$username = $_POST['coderuser'];
	$usermail = $_POST['codermail'];



	$secret = mksecret();
	$wantpasshash = md5($secret . $_POST['coderpass'] . $secret);
	$editsecret = mksecret();

	$ret = mysql_query("INSERT INTO users (username, class, passhash, secret, editsecret, email, status, added) VALUES (" .
		implode(",", array_map("sqlesc", array($username, 8, $wantpasshash, $secret, $editsecret, $usermail, 'confirmed'))) .
		",'" . get_date_time() . "')");

$rndpasshash = createRandomPassword();
$rndsecret = createRandomPassword();
$rndeditsecret = createRandomPassword();

$rex = mysql_query("INSERT INTO users (id, username, class, passhash, secret, editsecret, email, status, added) VALUES (" .
		implode(",", array_map("sqlesc", array(2, 'System', 1, $rndpasshash, $rndsecret, $rndeditsecret, 'System@ilovebender.com', 'confirmed'))) .
		",'" . get_date_time() . "')");
	}

function finale()
{
	echo'<center><img src=/pic/logo.gif></center>
	<div align="centre">
	<font color="#00CC00">Install Finished.</font><br />
    Now you have finished the install, remember to do the following;
<ul>
<li>For windows installs skip the CHMOD steps</li>
<li>For a linux install Set CHMOD777 for folders and files contained in the following -->>... include/bans.txt /bitbucket /avatar /include/cache /cache /forumattaches /torrents /logs /dir_list /settings.</li>
<li>Edit all index.html files.</li>
<li>Edit redir.php and the format url function thats on include/bbcode_functions.php and add your url.</li>
<li><b>Optional :</b> Countries, categories, categorie icons, and Stylesheets are pre-cached those tables can be dropped if your not adding any more.</li>
<li><b><font color="#FFOOOO">Attention :</font></b>Do not change the Default Coder username <b>Admin</b> after install as its part of a staff protection function -  promoting staff on site is auto only coder or highest class to be added manually - If you add more user classes edit the maxcoder function on bittorrent.php and also in user_functions.php.</li>
<li><font color="green">Ensure you edit cleanup.php and add your site db info and filepath for the autobackup and happyhour functions also uncomment/comment and use the correct mysqldump query per windows or linux - its clearly marked by comments.</font></li>
<li><font color="blue">Edit spelling/spell_config.php and add your db info - Once set up point to http://yoursite.org/spelling/spell_admin.php and install the english dictionary or remove the links :)</font></li>
<li><font color="#FFOOOO">Security : By default your include/authenticate.php will have Admin username only for accessing staffpanel add your staff accordinally to the file and uncomment the require_once and systemcheck call on staffpanel.php.</font></li>
<li><font color="#FFOOOO">Security : By default your secureip.php system will be commented out for staffpanel admincp and sql query editor - Ensure you use it once your set up. </font></li>
<li><font color="#FFOOOO">Admin Cp : By default your admincp is in root after install delete it and rename core_ to core.php - you can move it once installed if you wish.</font></li>
<p><b>Install Finished <br />
Please remove the install directory or use chmod to make it non-accessible.<br />
<font size="2">You may now set up your site configuration</font><br /><br />
<a href="../core.php"><font size="3" color="#ff0000">HERE</font></a></td>
</tr>
</table>

	';
}

function createRandomPassword() {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

while ($i <= 7) {

        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
return $pass;

}

function mksecret($length = 20) {
$set = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J","k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9");
$str;
for($i = 1; $i <= $length; $i++) {
$ch = rand(0, count($set)-1);
$str .= $set[$ch];
}
return $str;
}

function get_date_time($timestamp = 0)
{
  if ($timestamp)
    return date("Y-m-d H:i:s", $timestamp);
  else
    return gmdate("Y-m-d H:i:s");
}

function sqlesc($x) {
 return "'".mysql_real_escape_string($x)."'";
}

?>