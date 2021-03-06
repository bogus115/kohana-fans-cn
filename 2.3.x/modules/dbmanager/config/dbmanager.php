<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database Manager library configuration.
 *
 * @author icyleaf
 */

/**
 * Backup database data to file path. NEVER append "/" to the head of string.
 */
$config['backup_filepath'] = MODPATH.'dbmanager/backup-db';

/**
 * Maximal number of backup database. By default is 10, set `0` it is no Maximal absoluteness.
 */
$config['backup_maximal'] = 10;

/**
 * Automatic backup database period. Time unit is minites. By default is 5 days.
 */
$config['auto_backup'] = 1; //5 * 60 * 24; // Minites * Hours * Days

/**
 * Automatic backup database compress pattern. set TRUE is using gzip compress sql file. By default is TRUE.
 */
$config['auto_backup_gzip'] = FALSE;

/**
 * Automatic Optimize database period. Time unit is days. By default is 3 days.
 */
$config['auto_optimize'] = 3;

/**
 * Notify By email after backup database. By default it set FALSE.
 */
$config['notify'] = FALSE;

/**
 * Notify email
 */
$config['notify_email'] = 'icyleaf.cn@gmail.com';

?>