<?php
/*
Plugin Name: WP-DBManager
Plugin URI: http://www.lesterchan.net/portfolio/programming.php
Description: Manages your Wordpress database. Allows you to optimize database, repair database, backup database, restore database, delete backup database , drop/empty tables and run selected queries. Supports automatic scheduling of backing up and optimizing of database.
Version: 2.11
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
		add_submenu_page('dbmanager/database-manager.php', __('Repair DB', 'wp-dbmanager'), __('Repair DB', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-repair.php');
		add_submenu_page('dbmanager/database-manager.php', __('Empty/Drop Tables', 'wp-dbmanager'), __('Empty/Drop Tables', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-empty.php');
		add_submenu_page('dbmanager/database-manager.php', __('Run SQL Query', 'wp-dbmanager'), __('Run SQL Query', 'wp-dbmanager'), 'manage_database', 'dbmanager/database-run.php');
		add_submenu_page('dbmanager/database-manager.php',  __('DB Options', 'wp-dbmanager'),  __('DB Options', 'wp-dbmanager'), 'manage_database', 'dbmanager/dbmanager.php', 'dbmanager_options');
	}
}


### Funcion: Database Manager Cron
add_filter('cron_schedules', 'cron_dbmanager_reccurences');
add_action('dbmanager_cron_backup', 'cron_dbmanager_backup');
add_action('dbmanager_cron_optimize', 'cron_dbmanager_optimize');
function cron_dbmanager_backup() {
	global $wpdb;
	$backup_options = get_option('dbmanager_options');
	$backup_email = stripslashes($backup_options['backup_email']);
	if(intval($backup_options['backup']) > 0) {
		$current_date = gmdate('l, jS F Y @ H:i', (time() + (get_option('gmt_offset') * 3600)));
		$backup = array();
		$backup['date'] = current_time('timestamp');
		$backup['mysqldumppath'] = $backup_options['mysqldumppath'];
		$backup['mysqlpath'] = $backup_options['mysqlpath'];
		$backup['path'] = $backup_options['path'];
		$backup['filename'] = $backup['date'].'_-_'.DB_NAME.'.sql';
		$backup['filepath'] = $backup['path'].'/'.$backup['filename'];
		$backup['command'] = $backup['mysqldumppath'].' --host="'.DB_HOST.'" --user="'.DB_USER.'" --password="'.DB_PASSWORD.'" --add-drop-table '.DB_NAME.' > '.$backup['filepath'];
		passthru($backup['command']);
		if(!empty($backup_email)) {
				// Get And Read The Database Backup File
				$file_path = $backup['filepath'];
				$file_size = format_size(filesize($file_path));
				$file_date = gmdate('l, jS F Y @ H:i', substr($backup['filename'], 0, 10));
				$file = fopen($file_path,'rb');
				$file_data = fread($file,filesize($file_path));
				fclose($file);
				$file_data = chunk_split(base64_encode($file_data));
				// Create Mail To, Mail Subject And Mail Header
				$mail_subject = sprintf(__('%s Database Backup File For %s', 'wp-dbmanager'), get_bloginfo('name'), $file_date);
				$mail_header = 'From: '.get_bloginfo('name').' Administrator <'.get_option('admin_email').'>';
				// MIME Boundary
				$random_time = md5(time());
				$mime_boundary = "==WP-DBManager- $random_time";
				// Create Mail Header And Mail Message
				$mail_header .= "\nMIME-Version: 1.0\n" .
										"Content-Type: multipart/mixed;\n" .
										" boundary=\"{$mime_boundary}\"";
				$mail_message = __('Website Name:', 'wp-dbmanager').' '.get_bloginfo('name')."\n".
										__('Website URL:', 'wp-dbmanager').' '.get_bloginfo('siteurl')."\n".
										__('Backup File Name:', 'wp-dbmanager').' '.$backup['filename']."\n".
										__('Backup File Date:', 'wp-dbmanager').' '.$file_date."\n".
										__('Backup File Size:', 'wp-dbmanager').' '.$file_size."\n\n".
										__('With Regards,', 'wp-dbmanager')."\n".
										get_bloginfo('name').' '. __('Administrator', 'wp-dbmanager')."\n".
										get_bloginfo('siteurl');
				$mail_message = "This is a multi-part message in MIME format.\n\n" .
										"--{$mime_boundary}\n" .
										"Content-Type: text/plain; charset=\"utf-8\"\n" .
										"Content-Transfer-Encoding: 7bit\n\n".$mail_message."\n\n";				
				$mail_message .= "--{$mime_boundary}\n" .
										"Content-Type: application/octet-stream;\n" .
										" name=\"$database_file\"\n" .
										"Content-Disposition: attachment;\n" .
										" filename=\"$database_file\"\n" .
										"Content-Transfer-Encoding: base64\n\n" .
										$file_data."\n\n--{$mime_boundary}--\n";
			mail($backup_email, $mail_subject, $mail_message, $mail_header);
		}
	}
	return;
}
function cron_dbmanager_optimize() {
	global $wpdb;
	$backup_options = get_option('dbmanager_options');
	$optimize = intval($backup_options['optimize']);
	if($optimize > 0) {
		$optimize_tables = array();
		$tables = $wpdb->get_col("SHOW TABLES");
			foreach($tables as $table_name) {
				$optimize_tables[] = $table_name;
		}
		$wpdb->query('OPTIMIZE TABLE '.implode(',', $optimize_tables));
	}
	return;
}
function cron_dbmanager_reccurences() {
	$backup_options = get_option('dbmanager_options');
	$backup = intval($backup_options['backup']);
	$optimize = intval($backup_options['optimize']);
	if($backup == 0) {
		$backup = 1;
	}
	if($optimize == 0) {
		$optimize = 1;
	}
	return array(
		'dbmanager_backup' => array('interval' => $backup*86400, 'display' => 'WP-DBManager Backup Schedule'),
		'dbmanager_optimize' => array('interval' => $optimize*86400, 'display' => 'WP-DBManager Optimize Schedule')
	);
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
	$backup_options['backup'] = 7;
	$backup_options['backup_email'] = get_option('admin_email');
	$backup_options['optimize'] = 3;
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
		if(strpos($_SERVER['HTTP_REFERER'], get_option('siteurl').'/wp-admin/admin.php?page=dbmanager/database-manage.php') !== false) {
			$backup_options = get_option('dbmanager_options');
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
	$backup_options = get_option('dbmanager_options');
	if($_POST['Submit']) {
		$backup_options['mysqldumppath'] = trim($_POST['db_mysqldumppath']);
		$backup_options['mysqlpath'] = trim($_POST['db_mysqlpath']);
		$backup_options['path'] = trim($_POST['db_path']);
		$backup_options['backup'] = intval($_POST['db_backup']);
		$backup_options['backup_email'] = trim(addslashes($_POST['db_backup_email']));
		$backup_options['optimize'] = intval($_POST['db_optimize']);
		$update_db_options = update_option('dbmanager_options', $backup_options);
		if($update_db_options) {
			$text = '<font color="green">'.__('DB Options Updated', 'wp-dbmanager').'</font>';
		}
		if(empty($text)) {
			$text = '<font color="red">'.__('No DB Option Updated', 'wp-dbmanager').'</font>';
		}
		wp_clear_scheduled_hook('dbmanager_cron_backup');
		if (!wp_next_scheduled('dbmanager_cron_backup')) {
			wp_schedule_event(time(), 'dbmanager_backup', 'dbmanager_cron_backup');
		}
		wp_clear_scheduled_hook('dbmanager_cron_optimize');
		if (!wp_next_scheduled('dbmanager_cron_optimize')) {
			wp_schedule_event(time(), 'dbmanager_optimize', 'dbmanager_cron_optimize');
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
				<td valign="top"><strong><?php _e('Automatic Backing Up Of DB:', 'wp-dbmanager'); ?></strong></td>
				<td>
					<?php _e('Every', 'wp-dbmanager'); ?>&nbsp;<input type="text" name="db_backup" size="3" maxlength="5" value="<?php echo intval($backup_options['backup']); ?>" />&nbsp;<?php _e('days', 'wp-dbmanager'); ?>&nbsp;&nbsp;&nbsp;<?php _e('(0 to disable this feature)', 'wp-dbmanager'); ?><br /><?php _e('E-mail backup to:', 'wp-dbmanager'); ?> <input type="text" name="db_backup_email" size="30" maxlength="50" value="<?php echo stripslashes($backup_options['backup_email']) ?>" />&nbsp;&nbsp;&nbsp;<?php _e('(Leave black to disable this feature)', 'wp-dbmanager'); ?>
					<br /><?php _e('WP-DBManager can automatically backup your database after every X number of days.', 'wp-dbmanager'); ?>
				</td>
			</tr>
			<tr>
				<td valign="top"><strong><?php _e('Automatic Optimizing Of DB:', 'wp-dbmanager'); ?></strong></td>
				<td>
					<?php _e('Every', 'wp-dbmanager'); ?>&nbsp;<input type="text" name="db_optimize" size="3" maxlength="5" value="<?php echo intval($backup_options['optimize']); ?>" />&nbsp;<?php _e('days', 'wp-dbmanager'); ?>&nbsp;&nbsp;&nbsp;<?php _e('(0 to disable this feature)', 'wp-dbmanager'); ?>
					<br /><?php _e('WP-DBManager can automatically optimize your database after every X number of days.', 'wp-dbmanager'); ?>
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