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
|	- Database Run Query															|
|	- wp-content/plugins/dbmanager/database-run.php					|
|																							|
+----------------------------------------------------------------+
*/


### Require Database Config
require('database-config.php');


### Form Processing 
if($_POST['do']) {
	// Decide What To Do
	switch($_POST['do']) {
		case 'Run':
			$sql_queries2 = trim($_POST['sql_query']);
			$totalquerycount = 0;
			$successquery = 0;
			if($sql_queries2) {
				$sql_queries = array();
				$sql_queries2 = explode("\n", $sql_queries2);
				foreach($sql_queries2 as $sql_query2) {
					$sql_query2 = trim(stripslashes($sql_query2));
					$sql_query2 = preg_replace("/[\r\n]+/", '', $sql_query2);
					if(!empty($sql_query2)) {
						$sql_queries[] = $sql_query2;
					}
				}
				if($sql_queries) {
					foreach($sql_queries as $sql_query) {			
						if (preg_match("/^\\s*(insert|update|replace|delete|create|alter) /i",$sql_query)) {
							$run_query = $wpdb->query($sql_query);
							if(!$run_query) {
								$text .= "<font color=\"red\">$sql_query</font><br />";
							} else {
								$successquery++;
								$text .= "<font color=\"green\">$sql_query</font><br />";
							}
							$totalquerycount++;
						} elseif (preg_match("/^\\s*(select|drop|show|grant) /i",$sql_query)) {
							$text .= "<font color=\"red\">$sql_query</font><br />";
							$totalquerycount++;						
						}
					}
					$text .= "<font color=\"blue\">$successquery/$totalquerycount Query(s) Executed Successfully</font>";
				} else {
					$text = "<font color=\"red\">Empty Query</font>";
				}
			} else {
				$text = "<font color=\"red\">Empty Query</font>";
			}
			break;
	}
}
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Run SQL Query -->
<div class="wrap">
	<h2>Run SQL Query</h2>
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<p><b>Seperate Multiple Queries With A New Line</b><br /><font color="green">Use Only INSERT, UPDATE, REPLACE, DELETE, CREATE and ALTER statements.</font></p>
		<p align="center"><textarea cols="150" rows="30" name="sql_query"></textarea></p>
		<p align="center"><input type="submit" name="do" Value="Run" class="button" />&nbsp;&nbsp;<input type="button" name="cancel" Value="<?php _e('Cancel'); ?>" class="button" onclick="javascript:history.go(-1)" /></p>
		<p>1. CREATE statement will return an error, which is perfectly normal due to the database class. To confirm that your table has been created check the Manage Database page.<br />2. UPDATE statement may return an error sometimes due to the newly updated value being the same as the previous value.<br />3. ALTER statement will return an error because there is no value returned.</font></p>
	</form>
</div>