-> Upgrade Instructions For Version 1.0x To Version 2.02
------------------------------------------------------------------
// Deactivate WP-DBManager plugin


// Delete these file if exists

Delete:
------------------------------------------------------------------
wp-admin/database-manager.php
wp-content/plugins/dbmanager.php
------------------------------------------------------------------


// Open wp-content/plugins folder

Put:
------------------------------------------------------------------
Folder: dbmanager
------------------------------------------------------------------


// Activate WP-DBManager Plugin


// Open wp-content/backup-db folder

Put:
------------------------------------------------------------------
.htaccess
------------------------------------------------------------------

Note:
------------------------------------------------------------------
The script will automatically create a folder called 'backup-db'
in 'wp-content' folder if that folder is writable. If it is not created,
please create it and CHMOD it to 777
------------------------------------------------------------------