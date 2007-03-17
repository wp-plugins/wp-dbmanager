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
|	- Database Manager																|
|	- wp-content/plugins/dbmanager/database-manager.php				|
|																							|
+----------------------------------------------------------------+
*/


### Require Database Config
require('database-config.php');


### Get MYSQL Version
$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Database Information -->
<div class="wrap">
	<h2>Database Information</h2>
	<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>
			<th align="left" scope="col">Setting</th>
			<th align="left" scope="col">Value</th>
		</tr>
		<tr>
			<td>Database Host</td>
			<td><?php echo DB_HOST; ?></td>
		</tr>
		<tr>
			<td>Database Name</td>
			<td><?php echo DB_NAME; ?></td>
		</tr>	
		<tr>
			<td>Database User</td>
			<td><?php echo DB_USER; ?></td>
		</tr>
		<tr>
			<td>Database Type</td>
			<td>MYSQL</td>
		</tr>	
		<tr>
			<td>Database Version</td>
			<td>v<?php echo $sqlversion; ?></td>
		</tr>	
	</table>
</div>
<div class="wrap">
	<h2>Tables Information</h2>
	<table width="100%" cellspacing="3" cellpadding="3" border="0">
		<tr>
			<th align="left" scope="col">No.</th>
			<th align="left" scope="col">Tables</th>
			<th align="left" scope="col">Records</th>
			<th align="left" scope="col">Data Usage</th>
			<th align="left" scope="col">Index Usage</th>
			<th align="left" scope="col">Overhead</th>
		</tr>
<?php
// If MYSQL Version More Than 3.23, Get More Info
if($sqlversion >= '3.23') {
	$tablesstatus = $wpdb->get_results("SHOW TABLE STATUS");
	foreach($tablesstatus as  $tablestatus) {
		if($no%2 == 0) {
			$style = 'style=\'background-color: #eee\'';
		} else {
			$style = 'style=\'background-color: none\'';
		}
		$no++;
		echo "<tr $style>\n<td>$no</td>\n";
		echo "<td>$tablestatus->Name</td>\n";
		echo "<td>".number_format($tablestatus->Rows)."</td>\n";
		echo "<td>".format_size($tablestatus->Data_length)."</td>\n";
		echo "<td>".format_size($tablestatus->Index_length)."</td>\n";
		echo "<td>".format_size($tablestatus->Data_free)."</td>\n";
		$row_usage += $tablestatus->Rows;
		$data_usage += $tablestatus->Data_length;
		$index_usage +=  $tablestatus->Index_length;
		$overhead_usage += $tablestatus->Data_free;
	}
	echo "<tr><th align=\"left\" scope=\"row\">Total:</th>\n";
	echo "<th align=\"left\" scope=\"row\">$no Tables</th>\n";
	echo "<th align=\"left\" scope=\"row\">".number_format($row_usage)."</th>\n";
	echo "<th align=\"left\" scope=\"row\">".format_size($data_usage)."</th>\n";
	echo "<th align=\"left\" scope=\"row\">".format_size($index_usage)."</th>";
	echo "<th align=\"left\" scope=\"row\">".format_size($overhead_usage)."</th></tr>";
} else {
	echo '<tr><td colspan="6" align="center"><b>Could Not Show Table Status Due To Your MYSQL Version Is Lower Than 3.23.</b></td></tr>';
}
?>
	</table>
</div>