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
|	- Database Restore																|
|	- wp-content/plugins/dbmanager/database-restore.php				|
|																							|
+----------------------------------------------------------------+
*/


### Download Database
if(!empty($_GET['file'])) {
	require('../../../wp-config.php');
	require(ABSPATH.'wp-admin/admin.php');
	if(strpos($_SERVER['HTTP_REFERER'], get_settings('siteurl').'/wp-admin/admin.php?page=dbmanager/database-manage.php') !== false) {
		$backup_options = get_settings('dbmanager_options');
		$file_path = $backup_options['path'].'/'.$_GET['file'];
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


### Require Database Config
require('database-config.php');


### Form Processing 
if($_POST['do']) {
	// Lets Prepare The Variables
	$database_file = trim($_POST['database_file']);
	$nice_file_date = date('l, jS F Y @ H:i', substr($database_file, 0, 10));

	// Decide What To Do
	switch($_POST['do']) {
		case 'Restore':
			if(!empty($database_file)) {
				if(stristr($database_file, '.gz')) {
					$backup['command'] = 'gunzip < '.$backup['path'].'/'.$database_file.' | '.$backup['mysqlpath'].' --host='.DB_HOST.' --user='.DB_USER.' --password='.DB_PASSWORD.' '.DB_NAME;
				} else {
					$backup['command'] = $backup['mysqlpath'].' --host='.DB_HOST.' --user='.DB_USER.' --password='.DB_PASSWORD.' '.DB_NAME.' < '.$backup['path'].'/'.$database_file;
				}
				passthru($backup['command'], $error);
				if($error) {
					$text = "<font color=\"red\">Database On '$nice_file_date' Failed To Restore</font>";
				} else {
					$text = "<font color=\"green\">Database On '$nice_file_date' Restored Successfully</font>";
				}
			} else {
				$text = '<font color="red">No Backup Database File Selected</font>';
			}
			break;
		case 'E-Mail':
			if(!empty($database_file)) {
				// Get And Read The Database Backup File
				$file_path = $backup['path'].'/'.$database_file;
				$file_size = format_size(filesize($file_path));
				$file_date = date('jS F Y', substr($database_file, 0, 10));
				$file = fopen($file_path,'rb');
				$file_data = fread($file,filesize($file_path));
				fclose($file);
				$file_data = chunk_split(base64_encode($file_data));
				// Create Mail To, Mail Subject And Mail Header
				if(!empty($_POST['email_to'])) {
					$mail_to = trim($_POST['email_to']);
				} else {
					$mail_to = get_settings('admin_email');
				}
				$mail_subject = get_bloginfo('name').' Database Backup File For '.$file_date;
				$mail_header = 'From: '.get_bloginfo('name').' Administrator <'.get_settings('admin_email').'>';
				// MIME Boundary
				$random_time = md5(time());
				$mime_boundary = "==WP-DBManager- $random_time";
				// Create Mail Header And Mail Message
				$mail_header .= "\nMIME-Version: 1.0\n" .
										"Content-Type: multipart/mixed;\n" .
										" boundary=\"{$mime_boundary}\"";
				$mail_message = "Website Name: ".get_bloginfo('name')."\nWebsite URL: ".get_bloginfo('siteurl')."\nBackup File Name: $database_file\nBackup File Date: $file_date\nBackup File Size: $file_size\n\nWith Regards,\n".get_bloginfo('name')." Administrator\n".get_bloginfo('siteurl');
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
				if(mail($mail_to, $mail_subject, $mail_message, $mail_header)) {
					$text .= "<font color=\"green\">Database Backup File For $file_date Successfully E-Mailed To $mail_to</font><br />";
				} else {
					$text = "<font color=\"red\">Unable To E-Mail Database Backup File For $file_date To $mail_to</font>";
				}
			} else {
				$text = '<font color="red">No Backup Database File Selected</font>';
			}
			break;
		case 'Download':
			if(!empty($database_file)) {
				header('Location: '.get_settings('siteurl').'/wp-content/plugins/dbmanager/database-manage.php?file='.$database_file);
			} else {
				$text = '<font color="red">No Backup Database File Selected</font>';
			}
			break;
		case 'Delete':
			if(!empty($database_file)) {
				$nice_file_date = date('l, jS F Y @ H:i', substr($database_file, 0, 10));
				if(is_file($backup['path'].'/'.$database_file)) {
					if(!unlink($backup['path'].'/'.$database_file)) {
						$text .= "<font color=\"red\">Unable To Delete Database Backup File On '$nice_file_date'</font><br />";
					} else {
						$text .= "<font color=\"green\">Database Backup File On '$nice_file_date' Deleted Successfully</font><br />";
					}
				} else {
					$text = "<font color=\"red\">Invalid Database Backup File On '$nice_file_date'</font>";
				}
			} else {
				$text = '<font color="red">No Backup Database File Selected</font>';
			}
			break;
	}
}
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Manage Backup Database -->
<div class="wrap">
	<h2>Manage Backup Database</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
			<tr>
				<th align="left" scope="row" colspan="5">Choose A Backup Date To E-Mail, Restore, Download Or Delete</th>
			</tr>
			<tr>
				<th align="left" scope="col">No.</th>
				<th align="left" scope="col">Database File</th>
				<th align="left" scope="col">Date/Time</th>
				<th align="left" scope="col">Size</th>
				<th align="left" scope="col">Select</th>
			</tr>
			<?php
				if(!is_emtpy_folder($backup['path'])) {
					if ($handle = opendir($backup['path'])) {
						$database_files = array();
						while (false !== ($file = readdir($handle))) { 
							if ($file != '.' && $file != '..' && (file_ext($file) == 'sql' || file_ext($file) == 'gz')) {
								$database_files[] = $file;
							} 
						}
						closedir($handle);
						for($i = (sizeof($database_files)-1); $i > -1; $i--) {
							if($no%2 == 0) {
								$style = 'style=\'background-color: #eee\'';
							} else {
								$style = 'style=\'background-color: none\'';
							}
							$no++;
							$database_text = substr($database_files[$i], 13);
							$date_text = date('l, jS F Y @ H:i', substr($database_files[$i], 0, 10));
							$size_text = filesize($backup['path'].'/'.$database_files[$i]);
							echo "<tr $style>\n<td>$no</td>";
							echo "<td>$database_text</td>";
							echo "<td>$date_text</td>";
							echo '<td>'.format_size($size_text).'</td>';
							echo "<td><input type=\"radio\" name=\"database_file\" value=\"$database_files[$i]\" /></td>\n</tr>\n";
							$totalsize += $size_text;
						}
					} else {
						echo '<tr><td align="center" colspan="5">There Are No Database Backup Files Available</td></tr>';
					}
				} else {
					echo '<tr><td align="center" colspan="5">There Are No Database Backup Files Available</td></tr>';
				}
			?>
			</tr>
			<tr>
				<th align="left" colspan="3"><?php echo $no; ?> Backup File(s)</th>
				<th align="left"><?php echo format_size($totalsize); ?></th>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="5">E-mail database backup file to: <input type="text" name="email_to" size="30" maxlength="50" value="<?php echo get_settings('admin_email'); ?>" />&nbsp;&nbsp;<input type="submit" name="do" value="E-Mail" class="button" /></td>
			</tr>
			<tr>
				<td colspan="5" align="center"><input type="submit" name="do" value="Download" class="button" />&nbsp;&nbsp;<input type="submit" class="button" name="do" value="Restore" onclick="return confirm('You Are About To Restore A Database.\nThis Action Is Not Reversible.\nAny Data Inserted After The Backup Date Will Be Gone.\n\n Choose \'Cancel\' to stop, \'OK\' to restore.')" class="button" />&nbsp;&nbsp;<input type="submit" class="button" name="do" value="Delete" onclick="return confirm('You Are About To Delete The Selected Database Backup Files.\nThis Action Is Not Reversible.\n\n Choose \'Cancel\' to stop, \'OK\' to delete.')" />&nbsp;&nbsp;<input type="submit" name="cancel" Value="Cancel" class="button" /></td>
			</tr>					
		</table>
	</form>
</div>