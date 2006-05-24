<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.0 Plugin: WP-DBManager 2.05								|
|	Copyright (c) 2005 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://www.lesterchan.net													|
|																							|
|	File Information:																	|
|	- Database Empty																|
|	- wp-content/plugins/dbmanager/database-empty.php				|
|																							|
+----------------------------------------------------------------+
*/


### Require Database Config
require('database-config.php');


### Form Processing 
if($_POST['do']) {
	// Lets Prepare The Variables
	$emptydrop = $_POST['emptydrop'];

	// Decide What To Do
	switch($_POST['do']) {
		case 'Empty/Drop':
			$empty_tables = array();
			if(!empty($emptydrop)) {
				foreach($emptydrop as $key => $value) {
					if($value == 'empty') {
						$empty_tables[] = $key;
					} elseif($value == 'drop') {
						$drop_tables .=  ', '.$key;
					}
				}
			} else {
				$text = '<font color="red">No Tables Selected</font>';
			}
			$drop_tables = substr($drop_tables, 2);
			if(!empty($empty_tables)) {
				foreach($empty_tables as $empty_table) {
					$empty_query = $wpdb->query("TRUNCATE $empty_table");
					$text .= "<font color=\"green\">Table '$empty_table' Emptied</font><br />";
				}
			}
			if(!empty($drop_tables)) {
				$drop_query = $wpdb->query("DROP TABLE $drop_tables");
				$text = "<font color=\"green\">Table(s) '$drop_tables' Dropped</font>";
			}
			break;
	}
}


### Show Tables
$tables = $wpdb->get_col("SHOW TABLES");
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Empty/Drop Tables -->
<div class="wrap">
	<h2>Empty/Drop Tables</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<table width="100%" cellspacing="3" cellpadding="3" border="0">
			<tr>
			<th align="left" scope="col">Tables</th>
			<th align="left" scope="col">Empty</th>
			<th align="left" scope="col">Drop</th>
		</tr>
				<?php
					foreach($tables as $table_name) {
						if($no%2 == 0) {
							$style = 'style=\'background-color: #eee\'';
						} else {
							$style = 'style=\'background-color: none\'';
						}
						$no++;
						echo "<tr $style><th align=\"left\" scope=\"row\">$table_name</th>\n";
						echo "<td><input type=\"radio\" name=\"emptydrop[$table_name]\" value=\"empty\" />&nbsp;Empty</td>";
						echo "<td><input type=\"radio\" name=\"emptydrop[$table_name]\" value=\"drop\" />&nbsp;Drop</td></tr>";
					}
				?>
			<tr>
				<td colspan="3">1. DROPPING a table means deleting the table. This action is not REVERSIBLE.<br />2. EMPTYING a table means all the rows in the table will be deleted. This action is not REVERSIBLE.</td>
			</tr>
			<tr>
				<td colspan="3" align="center"><input type="submit" name="do" value="Empty/Drop" class="button" onclick="return confirm('You Are About To Empty Or Drop The Selected Databases.\nThis Action Is Not Reversible.\n\n Choose \'Cancel\' to stop, \'OK\' to delete.')" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel'); ?>" class="button" onclick="javascript:history.go(-1)" /></td>
			</tr>
		</table>
	</form>
</div>