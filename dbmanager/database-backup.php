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
				$text = "<font color=\"red\">Database Failed To Backup On '$current_date'. Backup Folder Not Writable.</font>";
			} elseif(filesize($backup['filepath']) == 0) {
				unlink($backup['filepath']);
				$text = "<font color=\"red\">Database Failed To Backup On '$current_date'. Backup File Size Is 0KB.</font>";
			} elseif(!is_file($backup['filepath'])) {
				$text = "<font color=\"red\">Database Failed To Backup On '$current_date'. Invalid Backup File Path.</font>";
			} elseif($error) {
				$text = "<font color=\"red\">Database Failed To Backup On '$current_date'.</font>";
			} else {
				$text = "<font color=\"green\">Database Backed Up Successfully On '$current_date'.</font>";
			}
			break;
	}
}


### Backup File Name
$backup['filename'] = $backup['date'].'_-_'.DB_NAME.'.sql';


### MYSQL Base Dir
$mysql_basedir = $wpdb->get_row("SHOW VARIABLES LIKE 'basedir'");
$mysql_basedir = $mysql_basedir->Value;
if($mysql_basedir == '/') { $mysql_basedir = '/usr/'; }
$status_count = 0;
$stats_function_disabled = 0;
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Checking Backup Status -->
<div class="wrap">
	<h2>Checking Backup Status</h2>
	<p>
		Checking Backup Folder (<b><?php echo stripslashes($backup['path']); ?></b>) ...<br />
		<?php
			if(is_dir(stripslashes($backup['path']))) {
				echo '<font color="green">Backup folder exists</font><br />';
				$status_count++;
			} else {
				echo '<font color="red">Backup folder does NOT exist. Please create \'backup-db\' folder in \'wp-content\' folder and CHMOD it to \'777\' or change the location of the backup folder under DB Option.</font><br />';
			}
			if(is_writable(stripslashes($backup['path']))) {
				echo '<font color="green">Backup folder is writable</font>';
				$status_count++;
			} else {
				echo '<font color="red">Backup folder is NOT writable. Please CHMOD it to \'777\'.</font>';
			}
		?>
	</p>
	<p>		
		<?php			
			if(file_exists($mysql_basedir.'bin/'.stripslashes($backup['mysqldumppath']))) {
				echo 'Checking MYSQL Dump Path (<b>'.$mysql_basedir.'bin/'.stripslashes($backup['mysqldumppath']).'</b>) ...<br />';
				echo '<font color="green">MYSQL dump path exists.</font>';
				$status_count++;
			} else if(file_exists(stripslashes($backup['mysqldumppath']))) {
				echo 'Checking MYSQL Dump Path (<b>'.stripslashes($backup['mysqldumppath']).'</b>) ...<br />';
				echo '<font color="green">MYSQL dump path exists.</font>';
				$status_count++;
			} else {
				echo 'Checking MYSQL Dump Path ...<br />';
				echo '<font color="red">MYSQL dump path does NOT exist. Please check your mysqldump path under DB Options. If uncertain, contact your server administrator.</font>';
			}
		?>
	</p>
	<p>
		<?php
			if(file_exists($mysql_basedir.'bin/'.stripslashes($backup['mysqlpath']))) {
				echo 'Checking MYSQL Path (<b>'.$mysql_basedir.'bin/'.stripslashes($backup['mysqlpath']).'</b>) ...<br />';
				echo '<font color="green">MYSQL path exists.</font>';
				$status_count++;
			} else if(file_exists(stripslashes($backup['mysqlpath']))) {
				echo 'Checking MYSQL Path (<b>'.stripslashes($backup['mysqlpath']).'</b>) ...<br />';
				echo '<font color="green">MYSQL path exists.</font>';
				$status_count++;
			} else {
				echo 'Checking MYSQL Path ...<br />';
				echo '<font color="red">MYSQL path does NOT exist. Please check your mysql path under DB Options. If uncertain, contact your server administrator.</font>';
			}
		?>
	</p>
	<p>
		Checking PHP Functions (<b>passthru()</b>, <b>system()</b> and <b>exec()</b>) ...<br />
		<?php
			if(function_exists('passthru')) {
				echo '<font color="green">passthru() enabled.</font><br />';
				$status_count++;
			} else {
				echo '<font color="red">passthru() disabled.</font><br />';
				$stats_function_disabled++;
			}
			if(function_exists('system')) {
				echo '<font color="green">system() enabled.</font><br />';
			} else {
				echo '<font color="red">system() disabled.</font><br />';
				$stats_function_disabled++;
			}
			if(function_exists('exec')) {
				echo '<font color="green">exec() enabled.</font>';
			} else {
				echo '<font color="red">exec() disabled.</font>';
				$stats_function_disabled++;
			}
		?>	
	</p>
	<p>
		<?php
			if($status_count == 5) {
				echo '<b><font color="green">Excellent. You Are Good To Go.</font></b>';
			} else if($stats_function_disabled == 3) {
				echo '<b><font color="red">I\'m sorry, your server administrator has disabled passthru(), system() and exec(), thus you cannot use this backup script. You may consider using the default WordPress database backup script instead.</font></b>';
			} else {
				echo '<b><font color="red">Please Rectify The Error Highlighted In Red Before Proceeding On.</font></b>';
			}
		?>
	</p>
	<p><i>Note: The checking of backup status is still undergoing testing, if you get a 'Good To Go' status but can't perform the backup or you get some errors but still can perform the backup, please drop me an <a href="mailto:gamerz84@hotmail.com?Subject=WP-DBManager: Checking Of Backup Status">email</a>.</i></p>
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
			<td><?php echo stripslashes($backup['path']); ?></td>
		</tr>
		<tr style='background-color: #eee'>
			<th align="left" scope="row">Database Backup Date:</th>
			<td><?php echo gmdate('l, jS F Y @ H:i', $backup['date']); ?></td>
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
			<td><?php echo stripslashes($backup['mysqldumppath']); ?></td>
		</tr>
		<tr style='background-color: #eee'>
			<th align="left" scope="row">GZIP Database Backup File?</th>
			<td><input type="radio" name="gzip" value="1">Yes&nbsp;&nbsp;<input type="radio" name="gzip" value="0" checked="checked" />No</td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" name="do" value="Backup" class="button" />&nbsp;&nbsp;<input type="button" name="cancel" Value="<?php _e('Cancel'); ?>" class="button" onclick="javascript:history.go(-1)" /></td>
		</tr>
	</table>
	</form>
</div>