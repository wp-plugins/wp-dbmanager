<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.8 Plugin: WP-DBManager 2.60								|
|	Copyright (c) 2009 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Uninstall WP-DBManager														|
|	- wp-content/plugins/wp-dbmanager/dbmanager-uninstall.php		|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Database
if(!current_user_can('manage_database')) {
	die('Access Denied');
}


### Variables Variables Variables
$base_name = plugin_basename('wp-dbmanager/database-manager.php');
$base_page = 'admin.php?page='.$base_name;
$mode = trim($_GET['mode']);
$db_settings = array('dbmanager_options');
$backup_options = get_option('dbmanager_options');
$backup_options_path = $backup_options['path'];

### Form Processing 
if(!empty($_POST['do'])) {
	// Decide What To Do
	switch($_POST['do']) {
		//  Uninstall WP-DBManager
		case __('UNINSTALL WP-DBManager', 'wp-dbmanager') :
			if(trim($_POST['uninstall_db_yes']) == 'yes') {
				echo '<div id="message" class="updated fade">';
				echo '<p>';
				foreach($db_settings as $setting) {
					$delete_setting = delete_option($setting);
					if($delete_setting) {
						echo '<font color="green">';
						printf(__('Setting Key \'%s\' has been deleted.', 'wp-dbmanager'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					} else {
						echo '<font color="red">';
						printf(__('Error deleting Setting Key \'%s\'.', 'wp-dbmanager'), "<strong><em>{$setting}</em></strong>");
						echo '</font><br />';
					}
				}
				echo '</p>';
				echo '<p style="color: blue;">';
				_e('The database backup files generated by WP-DBManager <strong>WILL NOT</strong> be deleted. You will have to delete it manually.', 'wp-dbmanager');
				echo '<br />';
				printf(__('The path to the backup folder is <strong>\'%s\'</strong>.', 'wp-dbmanager'), $backup_options_path);
				echo '</p>';
				echo '</div>'; 
				$mode = 'end-UNINSTALL';
			}
			break;
	}
}


### Determines Which Mode It Is
switch($mode) {
		//  Deactivating WP-DBManager
		case 'end-UNINSTALL':
			$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=wp-dbmanager/wp-dbmanager.php';
			if(function_exists('wp_nonce_url')) { 
				$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_wp-dbmanager/wp-dbmanager.php');
			}
			echo '<div class="wrap">';
			echo '<div id="icon-wp-dbmanager" class="icon32"><br /></div>';
			echo '<h2>'.__('Uninstall WP-DBManager', 'wp-dbmanager').'</h2>';
			echo '<p><strong>'.sprintf(__('<a href="%s">Click Here</a> To Finish The Uninstallation And WP-DBManager Will Be Deactivated Automatically.', 'wp-dbmanager'), $deactivate_url).'</strong></p>';
			echo '</div>';
			break;
	// Main Page
	default:
?>
<!-- Uninstall WP-DBManager -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo plugin_basename(__FILE__); ?>">
	<div class="wrap">
		<div id="icon-wp-dbmanager" class="icon32"><br /></div>
		<h2><?php _e('Uninstall WP-DBManager', 'wp-dbmanager'); ?></h2>
		<p>
			<?php _e('Deactivating WP-DBManager plugin does not remove any data that may have been created, such as the database options. To completely remove this plugin, you can uninstall it here.', 'wp-dbmanager'); ?>
		</p>
		<p style="color: red">
			<strong><?php _e('WARNING:', 'wp-dbmanager'); ?></strong><br />
			<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to back up all the data first.', 'wp-dbmanager'); ?>
		</p>
		<p style="color: red">
			<strong><?php _e('NOTE:', 'wp-dbmanager'); ?></strong><br />
			<?php _e('The database backup files generated by WP-DBManager <strong>WILL NOT</strong> be deleted. You will have to delete it manually.', 'wp-dbmanager'); ?><br />
			<?php printf(__('The path to the backup folder is <strong>\'%s\'</strong>.', 'wp-dbmanager'), $backup_options_path); ?>
		</p>
		<p style="color: red">
			<strong><?php _e('The following WordPress Options will be DELETED:', 'wp-dbmanager'); ?></strong><br />
		</p>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e('WordPress Options', 'wp-dbmanager'); ?></th>
				</tr>
			</thead>
			<tr>
				<td valign="top">
					<ol>
					<?php
						foreach($db_settings as $settings) {
							echo '<li>'.$settings.'</li>'."\n";
						}
					?>
					</ol>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		<p style="text-align: center;">
			<input type="checkbox" name="uninstall_db_yes" value="yes" />&nbsp;<?php _e('Yes', 'wp-dbmanager'); ?><br /><br />
			<input type="submit" name="do" value="<?php _e('UNINSTALL WP-DBManager', 'wp-dbmanager'); ?>" class="button" onclick="return confirm('<?php _e('You Are About To Uninstall WP-DBManager From WordPress.\nThis Action Is Not Reversible.\n\n Choose [Cancel] To Stop, [OK] To Uninstall.', 'wp-dbmanager'); ?>')" />
		</p>
	</div>
</form>
<?php
} // End switch($mode)
?>