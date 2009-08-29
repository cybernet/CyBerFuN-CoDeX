<?php
/****************************************************************\
* Staff panel for the TBDEV source code                          *
* -------------------------------------------------------------- *
* An easy to config staff panel for different staff classes,     *
* with different options for each class, like add, edit, delete  *
* the pages and to log the actions.                              *
* -------------------------------------------------------------- *
* @author: Alex2005 for TBDEV.NET                                *
* @copyright: Alex2005                                           *
* @package: Staff Panel                                          *
* @category: Staff Tools                                         *
* @version: v1.10 04/07/2008                                     *
* @license: GNU General Public License                           *
\****************************************************************/
include("include/bittorrent.php");
require_once ("include/user_functions.php");
require_once ("include/bbcode_functions.php");
require_once ("include/authenticate.php");
dbconn();
maxcoder();
systemcheck();
if(!logged_in())
{
header("HTTP/1.0 404 Not Found");
// moddifed logginorreturn by retro//Remember to change the following line to match your server
print("<html><h1>Not Found</h1><p>The requested URL /{$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 ".$SITENAME." Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n");
die();
}
parked();
/**
* Staff classes config
*
* UC_XYZ  : integer -> the name of the defined class
*
* Options for a selected class
** add    : boolean -> enable/disable page adding
** edit   : boolean -> enable/disable page editing
** delete : boolean -> enable/disable page deletion
** log    : boolean -> enable/disable the loging of the actions
*
* @result $staff_classes array();
*/
$staff_classes = array(
						UC_MODERATOR 		=> array('add' => false, 	'edit' => false, 	'delete' => false,   	'log' => true),
						UC_ADMINISTRATOR 	=> array('add' => false, 	'edit' => false, 	'delete' => false,   	'log' => true),
						UC_SYSOP 			=> array('add' => false, 	'edit' => true, 	'delete' => true,		'log' => true),
						UC_CODER 			=> array('add' => true, 	'edit' => true, 	'delete' => true,		'log' => true)
					  );

if (!isset($staff_classes[$CURUSER['class']]))
	stderr('Error', 'Access Denied!');

$action = (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : NULL));
$id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : NULL));
$class_color = (function_exists('get_user_class_color') ? true : false);

if ($action == 'delete' && is_valid_id($id) && $staff_classes[$CURUSER['class']]['delete'])
{
	$sure = ((isset($_GET['sure']) ? $_GET['sure'] : '') == 'yes');

	$res = mysql_query('SELECT av_class'.(!$sure || $staff_classes[$CURUSER['class']]['log'] ? ', page_name' : '').' FROM staffpanel WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	$arr = mysql_fetch_assoc($res);
	
	if ($CURUSER['class'] < $arr['av_class'])
		stderr('Error', 'You are not allowed to delete this page.');
	
	if (!$sure)
		stderr('Sanity check', 'Are you sure you want to delete this page: "'.safechar($arr['page_name']).'"? Click <a href="'.$_SERVER['PHP_SELF'].'?action='.$action.'&id='.$id.'&sure=yes">here</a> to delete it or <a href="'.$_SERVER['PHP_SELF'].'">here</a> to go back.');

	mysql_query('DELETE FROM staffpanel WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
	
	if (mysql_affected_rows())
	{
		if ($staff_classes[$CURUSER['class']]['log'])
			write_log('staffaction', 'Page "'.$arr['page_name'].'"('.($class_color ? '<font color="#'.get_user_class_color($arr['av_class']).'">' : '').get_user_class_name($arr['av_class']).($class_color ? '</font>' : '').') was deleted from the staff panel by <a href="/userdetails.php?id='.$CURUSER['id'].'">'.$CURUSER['username'].'</a>('.($class_color ? '<font color="#'.get_user_class_color($CURUSER['class']).'">' : '').get_user_class_name($CURUSER['class']).($class_color ? '</font>' : '').')');
		
		header('Location: '.$_SERVER['PHP_SELF']);
		exit();
	}
	else
		stderr('Error', 'There was a database error, please retry.');
}
else if (($action == 'add' && $staff_classes[$CURUSER['class']]['add']) || ($action == 'edit' && is_valid_id($id) && $staff_classes[$CURUSER['class']]['edit']))
{
	$names = array('page_name', 'file_name', 'description', 'av_class');

	if ($action == 'edit')
	{
		$res = mysql_query('SELECT '.implode(', ', $names).' FROM staffpanel WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
		$arr = mysql_fetch_assoc($res);
	}
	
	foreach ($names as $name)
		$$name = safechar((isset($_POST[$name]) ? $_POST[$name] : ($action == 'edit' ? $arr[$name] : '')));
	
	if ($action == 'edit' && $CURUSER['class'] < $av_class)
		stderr('Error', 'You are not allowed to edit this page.');
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$errors = array();
		
		if (empty($page_name))
			$errors[] = 'The page name cannot be empty.';
		
		if (empty($file_name))
			$errors[] = 'The filename cannot be empty.';
		
		if (empty($description))
			$errors[] = 'The description cannot be empty.';
		
		if (!isset($staff_classes[$av_class]))
			$errors[] = 'The selected class is not a valid staff class.';
		
		if (preg_match('/.php/', $file_name))
			$errors[] = 'Please remove the ".php" extension from the filename.';
		
		if (!is_file($file_name.'.php') && !empty($file_name) && !preg_match('/.php/', $file_name))
			$errors[] = 'Inexistent php file.';
		
		if (strlen($page_name) < 4 && !empty($page_name))
			$errors[] = 'The page name is too short (min 4 chars).';
		
		if (strlen($page_name) > 30)
			$errors[] = 'The page name is too long (max 30 chars).';
		
		if (strlen($file_name) > 30)
			$errors[] = 'The filename is too long (max 30 chars).';
		
		if (strlen($description) > 100)
			$errors[] = 'The description is too long (max 100 chars).';
		
		if (empty($errors))
		{
			if ($action == 'add')
			{
				$res = mysql_query("INSERT INTO staffpanel (page_name, file_name, description, av_class, added_by, added) ".
								   "VALUES (".implode(", ", array_map("sqlesc", array($page_name, $file_name, $description, (int)$av_class, (int)$CURUSER['id'], gmtime()))).")");
				
				if (!$res)
				{
					if (mysql_errno() == 1062)
						$errors[] = "This filename is already submited.";
					else
						$errors[] = "There was a database error, please retry.";
				}
			}
			else
			{
				$res = mysql_query("UPDATE staffpanel SET page_name = ".sqlesc($page_name).", file_name = ".sqlesc($file_name).", description = ".sqlesc($description).", av_class = ".sqlesc((int)$av_class)." WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
				
				if (!$res)
					$errors[] = "There was a database error, please retry.";
			}
			
			if (empty($errors))
			{
				if ($staff_classes[$CURUSER['class']]['log'])
					write_log('staffaction', 'Page "'.$page_name.'"('.($class_color ? '<font color="#'.get_user_class_color($av_class).'">' : '').get_user_class_name($av_class).($class_color ? '</font>' : '').') in the staff panel was '.($action == 'add' ? 'added' : 'edited').' by <a href="/userdetails.php?id='.$CURUSER['id'].'">'.$CURUSER['username'].'</a>('.($class_color ? '<font color="#'.get_user_class_color($CURUSER['class']).'">' : '').get_user_class_name($CURUSER['class']).($class_color ? '</font>' : '').')');
				
				header('Location: '.$_SERVER['PHP_SELF']);
				exit();
			}
		}
	}
	
	stdhead('Staff Panel :: '.($action == 'edit' ? 'Edit "'.$page_name.'"' : 'Add a new').' page'); begin_main_frame();
	
	if (!empty($errors))
	{
		stdmsg('There '.(count($errors)>1?'are':'is').' '.count($errors).' error'.(count($errors)>1?'s':'').' in the form.', '<b>'.implode('<br />', $errors).'</b>');
		?><br /><?php
	}

	?>
    <form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
	<input type="hidden" name="action" value="<?php echo $action; ?>" />
    <?php
	if ($action == 'edit')
	{
		?><input type="hidden" name="id" value="<?php echo $id; ?>" /><?php
	}
	
	?>
    <table cellpadding="5" width="100%" align="center">
        <tr class="colhead">
            <td colspan="2"><?php echo ($action == 'edit' ? 'Edit "'.$page_name.'"' : 'Add a new').' page'; ?></td>
        </tr>
        <tr>
            <td class="rowhead" width="1%">Page name</td><td align='left'><input type='text' size=50 name='page_name' value="<?php echo $page_name; ?>" /></td>
        </tr>
        <tr>
            <td class="rowhead">Filename</td><td align='left'><input type='text' size=50 name='file_name' value="<?php echo $file_name; ?>" /><b>.php</b></td>
        </tr>
        <tr>
            <td class="rowhead">Description</td><td align='left'><input type='text' size=50 name='description' value="<?php echo $description; ?>" /></td>
        </tr>
        <tr>
            <td class="rowhead" nowrap="nowrap">Available for</td>
            <td align='left'>
                <select name='av_class'><?php
                foreach ($staff_classes as $class => $value)
                {
                    if ($CURUSER['class'] < $class)
                        continue;
                    
                    echo '<option'.($class_color? ' style="background-color:#'.get_user_class_color($class).';"' : '').' value="'.$class.'"'.($class == $av_class ? ' selected="selected"' : '').'>'.get_user_class_name($class).'</option>';
                }
                ?></select>
            </td>
        </tr>
        <tr>
            <td align="center" colspan="2">
                <table class="main">
                	<tr>
                		<td style="border:none;">
                        	<input type='Submit' value="Submit" /></form>
						</td>
                		<td style="border:none;">
                        	<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'><input type='Submit' value="Cancel" /></form>
						</td>
                	</tr>
                </table>
            </td>
        </tr>
    </table>
	<?php
	
	end_main_frame(); stdfoot();
}
else
{
	stdhead('Staff Panel'); begin_main_frame();
	
	?><h1 align="center">Welcome <?php echo $CURUSER['username']; ?> to the Staff Panel!</h1><br /><?php
	
	if ($staff_classes[$CURUSER['class']]['add'])
	{
		stdmsg('Options', '<a href="'.$_SERVER['PHP_SELF'].'?action=add" title="Add a new page">Add a new page</a>');
		?><br /><?php
	}
	
	$res = mysql_query('SELECT staffpanel.*, users.username '.
					   'FROM staffpanel '.
					   'LEFT JOIN users ON users.id = staffpanel.added_by '.
					   'WHERE av_class <= '.sqlesc($CURUSER['class']).' '.
					   'ORDER BY av_class DESC, page_name ASC') or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) > 0)
	{
		$db_classes = $unique_classes = $mysql_data = array();
		
		while ($arr = mysql_fetch_assoc($res))
			$mysql_data[] = $arr;
		
		foreach ($mysql_data as $key => $value)
			$db_classes[$value['av_class']][] = $value['av_class'];
		
		$i=1;
		foreach ($mysql_data as $key => $arr)
		{
			$end_table = (count($db_classes[$arr['av_class']]) == $i ? true : false);

			if (!in_array($arr['av_class'], $unique_classes))
			{
				$unique_classes[] = $arr['av_class'];
				
				?>
                <table cellpadding="5" width="100%" align="center"<?php echo (!isset($staff_classes[$arr['av_class']]) ? 'style="background-color:#000000;"' : ''); ?>>
                    <tr>
                        <td colspan="4" align="center">
                            <h2><?php echo ($class_color ? '<font color="#'.get_user_class_color($arr['av_class']).'">' : '').get_user_class_name($arr['av_class']).' Panel'.($class_color ? '</font>' : ''); ?></h2>
                        </td>
                    </tr>
                    <tr align="center">
                        <td class="colhead" align="left" width="100%">Page name</td>
                        <td class="colhead" nowrap="nowrap">Added by</td>
                        <td class="colhead" nowrap="nowrap">Date added</td>
                        <?php
                        if ($staff_classes[$CURUSER['class']]['edit'] || $staff_classes[$CURUSER['class']]['delete'])
                        {
                            ?><td class="colhead">Links</td><?php
                        }
                        ?>
                    </tr>
                <?php
			}

			?>
			<tr align="center">
				<td align="left">
                	<a href="/<?php echo rawurlencode($arr['file_name']); ?>.php"  title="<?php echo safechar($arr['page_name']); ?>"><?php echo safechar($arr['page_name']); ?></a><br /><font class="small"><?php echo safechar($arr['description']); ?></font>
				</td>
                <td>
					<a href="/userdetails.php?id=<?php echo (int)$arr['added_by']; ?>"><?php echo $arr['username']; ?></a>
                </td>
                <td nowrap="nowrap">
                	<?php echo (function_exists('display_date_time') ? display_date_time(get_date_time($arr['added'])) : get_date_time($arr['added'])); ?><br /><font class="small"><?php echo get_elapsed_time($arr['added']); ?> ago</font>
                </td>
                <?php
				if ($staff_classes[$CURUSER['class']]['edit'] || $staff_classes[$CURUSER['class']]['delete'])
				{
					?>
					<td nowrap="nowrap">
                    	<?php
						if ($staff_classes[$CURUSER['class']]['edit'])
						{
							?><b>[</b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=edit&amp;id=<?php echo (int)$arr['id']; ?>" title="Edit">E</a><b>]</b><?php
						}
						
						if ($staff_classes[$CURUSER['class']]['delete'])
						{
							?><b>[</b><a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=delete&amp;id=<?php echo (int)$arr['id']; ?>" title="Delete">D</a><b>]</b><?php
						}
						?>
					</td>
                    <?php
				}
			?>
			</tr>
			<?php
			
			$i++;
			if ($end_table)
			{
				$i=1;
				?></table><br /><?php
			}
		}
	}
	else
		stdmsg('Sorry', 'Nothing found.');

	end_main_frame(); stdfoot();
}
?>