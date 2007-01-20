<?php
/*
Plugin Name: WP-DBManager
Plugin URI: http://www.lesterchan.net/portfolio/programming.php
Description: Manages your Wordpress database. Allows you to optimizee, backup, restore, delete backup database and run selected queries.	
Version: 2.10
Author: GaMerZ
Author URI: http://www.lesterchan.net
*/


/*  
	Copyright 2007  Lester Chan  (email : gamerz84@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


### Create Text Domain For Translations
load_plugin_textdomain('wp-dbmanager', 'wp-content/plugins/dbmanager');


### Function: Database Manager Menu
add_action('admin_menu', 'dbmanager_menu');
function dbmanager_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Database', 'wp-dbmanager'), __('Database', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-manager.php');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('dbmanager/database-manager.php', __('Backup DB', 'wp-dbmanager'), __('Backup DB', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-backup.php');
		add_submenu_page('dbmanager/database-manager.php', __('Manage Backup DB', 'wp-dbmanager'), __('Manage Backup DB', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-manage.php');
		add_submenu_page('dbmanager/database-manager.php', __('Optimize DB', 'wp-dbmanager'), __('Optimize DB', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-optimize.php');
		add_submenu_page('dbmanager/database-manager.php', __('Empty/Drop Tables', 'wp-dbmanager'), __('Empty/Drop Tables', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-empty.php');
		add_submenu_page('dbmanager/database-manager.php', __('Run SQL Query', 'wp-dbmanager'), __('Run SQL Query', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-run.php');
		add_submenu_page('dbmanager/database-manager.php',  __('DB Options', 'wp-dbmanager'),  __('DB Options', 'wp-dbmanager'), 'manage_database', 'dbmanager/dbmanager.php', 'dbmanager_options');
	}
}


### Function: Auto Detect MYSQL and MYSQL Dump Paths
function detect_mysql() {
	global $wpdb;
	$paths = array('mysq' => '', 'mysqldump' => '');
	if(substr(PHP_OS,0,3) == 'WIN') {
		$mysql_install = $wpdb->get_row("SHOW VARIABLES LIKE 'basedir'");
		if($mysql_install) {
			$install_path = str_replace('\\', '/', $mysql_install->Value);
			$paths['mysql'] = $install_path.'bin/mysql.exe';
			$paths['mysqldump'] = $install_path.'bin/mysqldump.exe';
		} else {
			$paths['mysql'] = 'mysql.exe';
			$paths['mysqldump'] = 'mysqldump.exe';
		}
	} else {
		if(function_exists('exec')) {
			$paths['mysql'] = exec('which mysql');
			$paths['mysqldump'] = exec('which mysqldump');
		} else {
			$paths['mysql'] = 'mysql';
			$paths['mysqldump'] = 'mysqldump';
		}
	}
	return $paths;
}


### Function: Format Bytes Into KB/MB
if(!function_exists('format_size')) {
	function format_size($rawSize) {
		if($rawSize / 1073741824 > 1) 
			return round($rawSize/1048576, 1) . ' GB';
		else if ($rawSize / 1048576 > 1)
			return round($rawSize/1048576, 1) . ' MB';
		else if ($rawSize / 1024 > 1)
			return round($rawSize/1024, 1) . ' KB';
		else
			return round($rawSize, 1) . ' bytes';
	}
}


### Function: Get File Extension
if(!function_exists('file_ext')) {
	function file_ext($file_name) {
		return substr(strrchr($file_name, '.'), 1);
	}
}


### Function: Check Folder Whether There Is Any File Inside
if(!function_exists('is_emtpy_folder')) {
	function is_emtpy_folder($folder){
	   if(is_dir($folder) ){
		   $handle = opendir($folder);
		   while( (gettype( $name = readdir($handle)) != 'boolean')){
				   $name_array[] = $name;
		   }
		   foreach($name_array as $temp)
			   $folder_content .= $temp;

		   if($folder_content == '...')
			   return true;
		   else
			   return false;
		   closedir($handle);
	   }
	   else
		   return true;
	}
}


### Function: Database Manager Role
add_action('activate_dbmanager/dbmanager.php', 'dbmanager_init');
function dbmanager_init() {
	global $wpdb;
	$auto = detect_mysql();
	// Add Options
	$backup_options = array();
	$backup_options['mysqldumppath'] = $auto['mysqldump'];
	$backup_options['mysqlpath'] = $auto['mysql'];
	$backup_options['path'] = str_replace('\\', '/', ABSPATH).'wp-content/backup-db';
	add_option('dbmanager_options', $backup_options, 'WP-DBManager Options');

	// Create Backup Folder
	if(!is_dir(ABSPATH.'wp-content/backup-db')) {
		mkdir(ABSPATH.'wp-content/backup-db');
	}

	// Set 'manage_database' Capabilities To Administrator	
	$role = get_role('administrator');
	if(!$role->has_cap('manage_database')) {
		$role->add_cap('manage_database');
	}
}


### Function: Download Database
add_action('init', 'download_database');
function download_database() {
	if($_POST['do'] == 'Download' && !empty($_POST['database_file'])) {
		if(strpos($_SERVER['HTTP_REFERER'], get_settings('siteurl').'/wp-admin/admin.php?page=dbmanager/database-manage.php') !== false) {
			$backup_options = get_settings('dbmanager_options');
			$file_path = $backup_options['path'].'/'.$_POST['database_file'];
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment; filename=".basename($file_path).";");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($file_path));
			@readfile($file_path);
		}
		exit();
	}
}


### Function: Database Options
function dbmanager_options() {
	global $wpdb;
	$text = '';
	$backup_options = array();
	$backup_options = get_settings('dbmanager_options');
	if($_POST['Submit']) {
		$backup_options['mysqldumppath'] = trim($_POST['db_mysqldumppath']);
		$backup_options['mysqlpath'] = trim($_POST['db_mysqlpath']);
		$backup_options['path'] = trim($_POST['db_path']);
		$update_db_options = update_option('dbmanager_options', $backup_options);
		if($update_db_options) {
			$text = '<font color="green">'.__('DB Options Updated', 'wp-dbmanager').'</font>';
		}
		if(empty($text)) {
			$text = '<font color="red">'.__('No DB Option Updated', 'wp-dbmanager').'</font>';
		}
	}
	$path = detect_mysql();
?>
<script type="text/javascript">
/* <![CDATA[*/
	function mysqlpath() {
		document.getElementById('db_mysqlpath').value = '<?php echo $path['mysql']; ?>';
	}
	function mysqldumppath() {
		document.getElementById('db_mysqldumppath').value = '<?php echo $path['mysqldump']; ?>';
	}
/* ]]> */
</script>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Database Options -->
<div class="wrap">
	<h2><?php _e('Database Options', 'wp-dbmanager'); ?></h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
			<tr>
				<td valign="top"><strong><?php _e('Path To mysqldump:', 'wp-dbmanager'); ?></strong></td>
				<td>
					<input type="text" id="db_mysqldumppath" name="db_mysqldumppath" size="60" maxlength="100" value="<?php echo stripslashes($backup_options['mysqldumppath']); ?>" />&nbsp;&nbsp;<input type="button" value="Auto Detect" onclick="mysqldumppath();" /><br /><?php _e('The absolute path to mysqldump without trailing slash. If unsure, please email your server administrator about this.', 'wp-dbmanager'); ?>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong><?php _e('Path To mysql:', 'wp-dbmanager'); ?></strong></td>
				<td>
					<input type="text" id="db_mysqlpath" name="db_mysqlpath" size="60" maxlength="100" value="<?php echo stripslashes($backup_options['mysqlpath']); ?>" />&nbsp;&nbsp;<input type="button" value="Auto Detect" onclick="mysqlpath();" /><br /><?php _e('The absolute path to mysql without trailing slash. If unsure, please email your server administrator about this.', 'wp-dbmanager'); ?>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong><?php _e('Path To Backup:', 'wp-dbmanager'); ?></strong></td>
				<td>
					<input type="text" name="db_path" size="60" maxlength="100" value="<?php echo stripslashes($backup_options['path']); ?>" />
					<br /><?php _e('The absolute path to your database backup folder without trailing slash. Make sure the folder is writable.', 'wp-dbmanager'); ?>
				</td>
			</tr>
			<tr>
				<td width="100%" colspan="2" align="center"><input type="submit" name="Submit" class="button" value="<?php _e('Update Options', 'wp-dbmanager'); ?>" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-dbmanager'); ?>" class="button" onclick="javascript:history.go(-1)" /></td>
			</tr>
		</table>
	</form>
	<p>
		<strong><?php _e('Windows Server', 'wp-dbmanager'); ?></strong><br />
		<?php _e('For mysqldump path, you can try \'<strong>mysqldump.exe</strong>\'.', 'wp-dbmanager'); ?><br />
		<?php _e('For mysql path, you can try \'<strong>mysql.exe</strong>\'.', 'wp-dbmanager'); ?><br />
		<br />
		<strong><?php _e('Linux Server', 'wp-dbmanager'); ?></strong><br />
		<?php _e('For mysqldump path, normally is just \'<strong>mysqldump</strong>\'.', 'wp-dbmanager'); ?><br />
		<?php _e('For mysql path, normally is just \'<strong>mysql</strong>\'.', 'wp-dbmanager'); ?><br />
	</p>
	<p>
		<strong><?php _e('Note', 'wp-dbmanager'); ?></strong><br />
		<?php _e('The \'Auto Detect\' function does not work for some servers. If it does not work for you, please contact your server administrator for the MYSQL and MYSQL DUMP paths.'); ?>
	</p>
</div>
<?php
}
?>