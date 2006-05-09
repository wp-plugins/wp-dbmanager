<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.0 Plugin: WP-DBManager 2.04								|
|	Copyright (c) 2005 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://www.lesterchan.net													|
|																							|
|	File Information:																	|
|	- Database Optimize																|
|	- wp-content/plugins/dbmanager/database-optimize.php				|
|																							|
+----------------------------------------------------------------+
*/


### Require Database Config
require('database-config.php');


### Form Processing 
if($_POST['do']) {
	// Lets Prepare The Variables
	$optimize = $_POST['optimize'];

	// Decide What To Do
	switch($_POST['do']) {
		case 'Optimize':
			if(!empty($optimize)) {
				foreach($optimize as $key => $value) {
					if($value == 'yes') {
						$tables_string .=  ', '.$key;
					}
				}
			} else {
				$text = '<font color="red">No Tables Selected</font>';
			}
			$selected_tables = substr($tables_string, 2);
			if(!empty($selected_tables)) {
				$optimize2 = $wpdb->query("OPTIMIZE TABLE $selected_tables");
				if(!$optimize2) {
					$text = "<font color=\"red\">Table(s) '$selected_tables' NOT Optimized</font>";
				} else {
					$text = "<font color=\"green\">Table(s) '$selected_tables' Optimized</font>";
				}
			}
			break;
	}
}


### Show Tables
$tables = $wpdb->get_results("SHOW TABLES");
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Optimize Database -->
<div class="wrap">
	<h2>Optimize Database</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
			<tr>
				<th align="left" scope="col">Tables</th>
				<th align="left" scope="col">Options</th>
			</tr>
				<?php
					foreach($tables as $dbtable) {
						if($no%2 == 0) {
							$style = 'style=\'background-color: #eee\'';
						} else {
							$style = 'style=\'background-color: none\'';
						}
						$no++;
						$table_name = '$dbtable->Tables_in_'.DB_NAME;
						eval("\$table_name = \"$table_name\";");
						echo "<tr $style><th align=\"left\" scope=\"row\">$table_name</th>\n";
						echo "<td><input type=\"radio\" name=\"optimize[$table_name]\" value=\"no\" />No&nbsp;&nbsp;<input type=\"radio\" name=\"optimize[$table_name]\" value=\"yes\" checked=\"checked\" />Yes</td></tr>";
					}
				?>
			<tr>
				<td colspan="2" align="center">Database should be optimize once every month.</td>
			</tr>
			<tr>
				<td colspan="2" align="center"><input type="submit" name="do" value="Optimize" class="button" />&nbsp;&nbsp;<input type="button" name="cancel" Value="<?php _e('Cancel'); ?>" class="button" onclick="javascript:history.go(-1)" /></td>
			</tr>
		</table>
	</form>
</div>