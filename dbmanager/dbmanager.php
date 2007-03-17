<?php
/*
Plugin Name: WP-DBManager
Plugin URI: http://www.lesterchan.net/portfolio/programming.php
Description: Manages your Wordpress database. Allows you to optimizee, backup, restore, delete backup database and run selected queries.	
Version: 2.02
Author: GaMerZ
Author URI: http://www.lesterchan.net
*/


/*  Copyright 2005  Lester Chan  (email : gamerz84@hotmail.com)

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


### Function: Database Manager Menu
add_action('admin_menu', 'dbmanager_menu');
function dbmanager_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Database'), __('Database'), 'manage_database', 'dbmanager/database-manager.php');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('dbmanager/database-manager.php', __('Backup DB'), __('Backup DB'), 'manage_database', 'dbmanager/database-backup.php');
		add_submenu_page('dbmanager/database-manager.php', __('Manage Backup DB'), __('Manage Backup DB'), 'manage_database', 'dbmanager/database-manage.php');
		add_submenu_page('dbmanager/database-manager.php', __('Optimize DB'), __('Optimize DB'), 'manage_database', 'dbmanager/database-optimize.php');
		add_submenu_page('dbmanager/database-manager.php', __('Empty/Drop Tables'), __('Empty/Drop Tables'), 'manage_database', 'dbmanager/database-empty.php');
		add_submenu_page('dbmanager/database-manager.php', __('Run SQL Query'), __('Run SQL Query'), 'manage_database', 'dbmanager/database-run.php');
		add_submenu_page('dbmanager/database-manager.php',  __('DB Options'),  __('DB Options'), 'manage_database', 'dbmanager/dbmanager.php', 'dbmanager_options');
	}
}


### Function: Database Manager Role
add_action('activate_dbmanager/dbmanager.php', 'dbmanager_init');
function dbmanager_init() {
	// Add Options
	$backup_options = array();
	$backup_options['mysqldumppath'] = 'mysqldump';
	$backup_options['mysqlpath'] = 'mysql';
	$backup_options['path'] = ABSPATH.'wp-content/backup-db';
	add_option('dbmanager_options', $backup_options, 'WP-DBManager Options');

	// Create Backup Folder
	if(!is_dir(ABSPATH.'/wp-content/backup-db')) {
		mkdir(ABSPATH.'/wp-content/backup-db');
	}

	// Set 'manage_database' Capabilities To Administrator	
	$role = get_role('administrator');
	if(!$role->has_cap('manage_database')) {
		$role->add_cap('manage_database');
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
			$text = '<font color="green">'.__('DB Options Updated').'</font>';
		}
		if(empty($text)) {
			$text = '<font color="red">'.__('No DB Option Updated').'</font>';
		}
	}
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Database Options -->
<div class="wrap">
	<h2>Database Options</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
			<tr>
				<td valign="top"><b>Path To mysqldump:</b></td>
				<td>
					<input type="text" name="db_mysqldumppath" size="100" maxlength="100" value="<?php echo $backup_options['mysqldumppath']; ?>" /><br />The absolute path to mysqldump without trailing slash. If unsure, please email your server administrator about this.
				</td>
			</tr>
			<tr>
				<td valign="top"><b>Path To mysql:</b></td>
				<td>
					<input type="text" name="db_mysqlpath" size="100" maxlength="100" value="<?php echo $backup_options['mysqlpath']; ?>" /><br />The absolute path to mysql without trailing slash. If unsure, please email your server administrator about this.
				</td>
			</tr>
			<tr>
				<td valign="top"><b>Path To Backup:</b></td>
				<td>
					<input type="text" name="db_path" size="100" maxlength="100" value="<?php echo $backup_options['path']; ?>" />
					<br />The absolute path to your database backup folder without trailing slash. Make sure the folder is writable.
				</td>
			</tr>
			<tr>
				<td width="100%" colspan="2">
					<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options'); ?> &raquo;" /></p>
				</td>
			</tr>
		</table>
	</form>
	<p>
		<b>Windows Server</b><br />
		For mysqldump path, you can try '<b>mysqldump.exe</b>'.<br />
		For mysql path, you can try '<b>mysql.exe</b>'.<br />
		<br />
		<b>Linux Server</b><br />
		For mysqldump path, normally is just '<b>mysqldump</b>'.<br />
		For mysql path, normally is just '<b>mysql</b>'.<br />
	</p>
</div>
<?php
}
?>