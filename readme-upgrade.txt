-> Upgrade Instructions For Version 1.0x To Version 2.02
------------------------------------------------------------------
// Open wp-admin folder

Delete:
------------------------------------------------------------------
database-manager.php
------------------------------------------------------------------


// Open wp-content/plugins folder

Delete:
------------------------------------------------------------------
dbmanager.php
------------------------------------------------------------------

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