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
		add_menu_page(__('Database'), __('Database'), 'manage_database', 'database-manager.php');
	}
}


### Function: Database Manager Role
add_action('activate_dbmanager.php', 'dbmanager_role');
function dbmanager_role() {
	if($_GET['action'] == 'activate' && $_GET['plugin'] == 'dbmanager.php') {
		$role = get_role('administrator');
		if(!$role->has_cap('manage_database')) {
			$role->add_cap('manage_database');
		}
	}
}
?>