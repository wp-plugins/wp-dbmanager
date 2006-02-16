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
|	- Database Config																|
|	- wp-content/plugins/dbmanager/database-config.php				|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Database
if(!current_user_can('manage_database')) {
	die('Access Denied');
}


### Variables Variables Variables
$base_name = plugin_basename('dbmanager/database-manager.php');
$base_page = 'admin.php?page='.$base_name;
$backup = array();
$backup_options = get_settings('dbmanager_options');
$backup['date'] = current_time('timestamp');
$backup['mysqldumppath'] = $backup_options['mysqldumppath'];
$backup['mysqlpath'] = $backup_options['mysqlpath'];
$backup['path'] = $backup_options['path'];


### Cancel
if(isset($_POST['cancel'])) {
	Header('Location: '.$base_page);
	exit();
}


### Format Bytes Into KB/MB
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


### Get File Extension
function file_ext($file_name) {
	return substr(strrchr($file_name, '.'), 1);
}


### Check Folder Whether There Is Any File Inside
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
       return true; // folder doesnt exist
}
?>