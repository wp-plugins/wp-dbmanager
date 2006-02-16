<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.0 Plugin: WP-DBManager 2.02								|
|	Copyright (c) 2005 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://www.lesterchan.net													|
|																							|
|	File Information:																	|
|	- Database Backup																|
|	- wp-content/plugins/dbmanager/database-backup.php				|
|																							|
+----------------------------------------------------------------+
*/


### Require Database Config
require('database-config.php');


### Form Processing 
if($_POST['do']) {
	// Decide What To Do
	switch($_POST['do']) {
		case 'Backup':
			$gzip = intval($_POST['gzip']);
			if($gzip == 1) {
				$backup['filename'] = $backup['date'].'_-_'.DB_NAME.'.sql.gz';
				$backup['filepath'] = $backup['path'].'/'.$backup['filename'];
				$backup['command'] = $backup['mysqldumppath'].' --host='.DB_HOST.' --user='.DB_USER.' --password='.DB_PASSWORD.' --add-drop-table '.DB_NAME.' | gzip > '.$backup['filepath'];
			} else {
				$backup['filename'] = $backup['date'].'_-_'.DB_NAME.'.sql';
				$backup['filepath'] = $backup['path'].'/'.$backup['filename'];
				$backup['command'] = $backup['mysqldumppath'].' --host='.DB_HOST.' --user='.DB_USER.' --password='.DB_PASSWORD.' --add-drop-table '.DB_NAME.' > '.$backup['filepath'];
			}
			passthru($backup['command'], $error);
			if(!is_writable($backup['path'])) {
				$text = "<font color=\"red\">Database Failed To Backup On '".date('l, jS F Y @ H:i')."'. Backup Folder Not Writable</font>";
			} elseif(filesize($backup['filepath']) == 0) {
				unlink($backup['filepath']);
				$text = "<font color=\"red\">Database Failed To Backup On '".date('l, jS F Y @ H:i')."'. Backup File Size Is 0KB</font>";
			} elseif(!is_file($backup['filepath'])) {
				$text = "<font color=\"red\">Database Failed To Backup On '".date('l, jS F Y @ H:i')."'. Invalid Backup File Path</font>";
			} elseif($error) {
				$text = "<font color=\"red\">Database Failed To Backup On '".date('l, jS F Y @ H:i')."'</font>";
			} else {
				$text = "<font color=\"green\">Database Backed Up Successfully On '".date('l, jS F Y @ H:i')."'</font>";
			}
			break;
	}
}


### Backup File Name
$backup['filename'] = $backup['date'].'_-_'.DB_NAME.'.sql';


### MYSQL Base Dir
$mysql_basedir = $wpdb->get_row("SHOW VARIABLES LIKE 'basedir'");
$mysql_basedir = $mysql_basedir->Value;
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Checking Backup Status -->
<div class="wrap">
	<h2>Checking Backup Status</h2>
	<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr style='background-color: #eee'>
			<th width="40%" valign="top" align="left" scope="row">Is Backup Folder Valid?</th>
			<td width="60%">
				<?php
					if(is_dir($backup['path'])) {
						echo '<font color="green">Yes</font>';
					} else {
						echo '<font color="red">No. Please create \'backup-db\' folder in \'wp-content\' folder and CHMOD it to \'777\' or change the location of the backup folder under DB Option.</font>';
					}
				?>
			</td>
		</tr>
		<tr style='background-color: none'>
			<th width="40%" valign="top" align="left" scope="row">Backup Folder Writable?</th>
			<td width="60%">
				<?php
					if(is_writable($backup['path'])) {
						echo '<font color="green">Yes</font>';
					} else {
						echo '<font color="red">No. Please CHMOD it to \'777\'.</font>';
					}
				?>
			</td>
		</tr>
		<tr style='background-color: #eee'>
			<th width="40%" valign="top" align="left" scope="row">Is mysqldump Path Valid?</th>
			<td width="60%">
				<?php
					if(file_exists($mysql_basedir.'bin/'.$backup['mysqldumppath']) || file_exists($backup['mysqldumppath'])) {
						echo '<font color="green">Yes</font>';
					} else {
						echo '<font color="red">No. Please check your mysqldump path under DB Option.</font>';
					}
				?>
				<br />
				Ignore this if you are on a Linux Server, I am still trying to learn how to detect mysqldump on Linux Server
			</td>
		</tr>
		<tr style='background-color: none'>
			<th width="40%" valign="top" align="left" scope="row">Is mysql Path Valid?</th>
			<td width="60%">
				<?php
					if(file_exists($mysql_basedir.'bin/'.$backup['mysqlpath']) || file_exists($backup['mysqlpath'])) {
						echo '<font color="green">Yes</font>';
					} else {
						echo '<font color="red">No. Please check your mysql path under DB Option.</font>';
					}
				?>
				<br />
				Ignore this if you are on a Linux Server, I am still trying to learn how to detect mysql on Linux Server
			</td>
			<tr style='background-color: #eee'>
			<th width="40%" valign="top" align="left" scope="row">Is passthru() Enabled?</th>
			<td width="60%">
			</td>
		</tr>
		</tr>
	</table>
</div>
<!-- Backup Database -->
<div class="wrap">
	<h2>Backup Database</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr style='background-color: #eee'>
			<th align="left" scope="row">Database Name:</th>
			<td><?php echo DB_NAME; ?></td>
		</tr>
		<tr style='background-color: none'>
			<th align="left" scope="row">Database Backup To:</th>
			<td><?php echo $backup['path']; ?></td>
		</tr>
		<tr style='background-color: #eee'>
			<th align="left" scope="row">Database Backup Date:</th>
			<td><?php echo date('jS F Y', $backup['date']); ?></td>
		</tr>
		<tr style='background-color: none'>
			<th align="left" scope="row">Database Backup File Name:</th>
			<td><?php echo $backup['filename']; ?></td>
		</tr>
		<tr style='background-color: #eee'>
			<th align="left" scope="row">Database Backup Type:</th>
			<td>Full (Structure and Data)</td>
		</tr>
		<tr style='background-color: none'>
			<th align="left" scope="row">MYSQL Dump Location:</th>
			<td><?php echo $backup['mysqldumppath']; ?></td>
		</tr>
		<tr style='background-color: #eee'>
			<th align="left" scope="row">GZIP Database Backup File?</th>
			<td><input type="radio" name="gzip" value="1">Yes&nbsp;&nbsp;<input type="radio" name="gzip" value="0" checked="checked" />No</td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" name="do" value="Backup" class="button" />&nbsp;&nbsp;<input type="submit" name="cancel" Value="Cancel" class="button" /></td>
		</tr>
	</table>
	</form>
</div>