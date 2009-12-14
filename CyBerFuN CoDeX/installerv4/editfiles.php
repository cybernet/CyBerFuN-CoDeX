<?php
include("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
dbconn();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}

if ($CURUSER['class'] != UC_CODER)
	stderr('Error', 'Access Denied!');

$allowed_ids = array(1);
if (!in_array($CURUSER['id'],$allowed_ids))
	stderr('Error', 'Access Denied!');
	
$_folder = safeChar(isset($_GET['folder'])?$_GET['folder']:$_POST['folder']);
$in_root = ($_folder=='@root'||$_folder==''?true:false);
$folder = (!empty($_folder) ? $_folder.'/' : '');
$rootpath = $_SERVER['DOCUMENT_ROOT'].'/';
$path = $rootpath.$folder;
$action = $_GET['action'];
$backup_extension = '.bkp';

$file = safeChar(isset($_GET['filename'])?$_GET['filename']:$_POST['filename']);
if (!preg_match('/^(.+)\.php$/si', $file)&&!empty($file))
	stderr('Error...', 'Not a php file.');

function backup_file($file)
{
	global $path, $backup_extension;
	
	$open = fopen($path.$file, "r+") or die("can't open file");
	$data = fread($open, filesize($path.$file));
	fclose($open);

	$filehandle = fopen($path.$file.$backup_extension, 'w') or die("can't open file");
	fwrite($filehandle, $data);
	fclose($filehandle);
}

function write_file($data, $file)
{
	global $path;

    backup_file($file);

    $open = fopen($path.$file, "w") or die("can't open file");
    fwrite($open, $data);
    fclose($open);
}
  
function open_file($file)
{
	global $path;

    $open = fopen($path.$file, "r+") or die("can't open file");
	$data = fread($open, filesize($path.$file));
	fclose($open);
	
	return $data;
}

function showdir_files()
{
	global $_folder, $in_root, $folder, $file;
	
	$handle = opendir(empty($_folder)||$_folder=='@root'?'.':$_folder);
	while (($filename = readdir($handle)) !== false)
	{
		if (is_dir($filename))
			$dir_folders[] = $filename;
		else
		{
			$ext = explode(".", $filename);
			
			if ($ext[1] == 'php')
				$dir_files[] = $filename;
		}
	}
	closedir($handle);
	?><table width="50%" cellpadding="5"><tr><td class="colhead">Curent folder</td><td class="colhead">File name</td></tr><tr><td><?php
	?><form action="<?php echo $_SERVER['PHP_SELF']; ?>?action=edit" method="post"><select name="folder" onchange="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?folder='+this.options[this.selectedIndex].value"><option disabled="disabled"<?php echo ($in_root?' selected="selected"':'');?>>Select a folder</option><?php
	echo ($_folder&&!$in_root?"<option value=\"$_folder\" selected=\"selected\">$_folder</option>":'');
	foreach ($dir_folders as $dir_folder)
	{
		if ($dir_folder == '..')
			continue;
		
		if ($dir_folder == '.' && $in_root)
			continue;

		echo "<option value=\"".($dir_folder=='.'?'@root':$dir_folder)."\"".($dir_folder==$folder?' selected':'').">".($dir_folder=='.'?'-Root folder':$dir_folder)."</option>";
	}
	?></select><?php
	
	?></td><td><select name="filename"><option disabled="disabled" selected="selected">Select a file</option><?php
	foreach ($dir_files as $dir_file)
		echo "<option value=\"$dir_file\"".($dir_file==$file?' selected':'').">$dir_file</option>";
	?></select><?php
	?></td></tr><?php
    ?><tr><td colspan="2" align="center"><input type="submit" value="Submit"></form></td></tr></table><?php
}


if (isset($action) && !empty($action))
{
	if (empty($file))
		stderr('Error...', 'Please select a file to edit.');
	
	$folder = (empty($folder)||$folder=='@root'?'':$folder);
}

if (empty($action))
{
	stdhead();
	echo "<h2>Open a php file</h2><br />";
	showdir_files();
	stdfoot();
}
elseif ($action == 'edit')
{
	if (!file_exists($path.$file))
		stderr("Error...", "File does not exists.");

	stdhead();
	begin_main_frame("Editing file: <a href=\"".(!empty($_folder) ? $folder:'').$file."\" alt=\"".$file."\" target=_blank title=\"".$path.$file."\">".$file."</a>".(file_exists($path.$file.$backup_extension)?"(click <a href=\"".$_SERVER['PHP_SELF']."?action=restore&filename=".$file.(!empty($_folder) ? '&folder='.$_folder:'')."\">here</a> to restore, or <a href=\"".$_SERVER['PHP_SELF']."?action=deletebkp&filename=".$file.(!empty($_folder) ? '&folder='.$_folder:'')."\">here</a> to delete the backup file.)":''));
	begin_frame("", 1);

	$data = open_file($file);
	echo "<form action=\"".$_SERVER['PHP_SELF']."?action=save\" method=\"post\"><textarea name=\"data\" rows=\"50\" cols=\"150\">".safeChar($data)."</textarea><input type=\"hidden\" name=\"filename\" value=\"".$file."\">".(!empty($_folder)?"<input type=\"hidden\" name=\"folder\" value=\"".$_folder."\">":'')."<div align=\"center\"><input type=\"submit\" value=\"Save File\"></div></form>";
	
	end_frame();
	
	?><br /><div align=center><h2>Edit another file</h2><?php
	showdir_files();
	?></div><?php
	
	end_main_frame();
	stdfoot();
}
elseif($action == 'save')
{
	if (empty($_POST['data']))
		stderr('Error...', 'File cannot be empty.');
	
	write_file($_POST['data'], $file);
	
	header("Location: ".$_SERVER['PHP_SELF']."?action=edit&filename=".$file.(!empty($_folder)?'&folder='.$_folder:''));
	exit();
}
elseif ($action == 'restore')
{
	if (!file_exists($path.$file.$backup_extension))
		stderr('Error...', 'Backup file does not exists.<br />Click <a href="'.$_SERVER['PHP_SELF'].'">here</a> to select a file.');

	if (file_exists($path.$file))
		unlink($path.$file);

	rename($path.$file.$backup_extension, $path.$file);

	stderr('Success...', 'File '.$file.' was successfully restored.<br />Click <a href="'.$_SERVER['PHP_SELF'].'?action=edit&filename='.$file.(!empty($_folder)?'&folder='.$_folder:'').'">here</a> to continue editing it, or <a href="'.$_SERVER['PHP_SELF'].'">here</a> to select a new file.');
}
elseif ($action == 'deletebkp')
{
	$bkp_file = $path.$file.$backup_extension;
	
	if (!file_exists($bkp_file))
		stderr('Error...', 'Backup file does not exists.<br />Click <a href="'.$_SERVER['PHP_SELF'].'">here</a> to select a file.');

	unlink($bkp_file);

	stderr('Success...', 'Backup file '.$file.$backup_extension.' was successfully deleted.<br />Click <a href="'.$_SERVER['PHP_SELF'].'?action=edit&filename='.$file.(!empty($_folder)?'&folder='.$_folder:'').'">here</a> to continue editing the original file, or <a href="'.$_SERVER['PHP_SELF'].'">here</a> to select a new one.');
}
else
	stderr('Error', 'Unknown action!');
?>