<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class DBManager_Driver {

	// Configuration
	protected $config;
	
	/**
	 * Configuration options.
	 *
	 * @return  void
	 */
	public function __construct(array $config)
	{
		// Store config
		$this->config = $config;
	}
	
	/**
	 * Get Mysql Version
	 *
	 * @return (string) mysql version
	 */
	abstract function get_version();
	
	/**
	 * List tables information in database of application
	 *
	 * @return (object) tables information
	 */ 
	abstract function list_tables();
	
	/**
	 * Total tables number in database of application
	 *
	 * @return (int) tables count
	 */
	abstract function total_tables();
	
	/**
	 * Optimize tables
	 *
	 * @param (array) $tables - need to optimize tables
	 * @return void
	 */
	abstract function optimize_tables($tables = array());
	
	/**
	 * Repair tables
	 *
	 * @param (array) $tables - need to repair tables
	 * @return void if successs, else return error messages.
	 */
	abstract function repair_tables($tables = array());
	
	/**
	 * Backup dababase of application
	 *
	 * @param (boolean) $gzip - set TRUE, compress sql file with Gzip. by default, set it FALSE.
	 * @return void if success, else return error messages.
	 */
	abstract function backup_db($gzip=FALSE);
	
	/**
	 * Download backup file
	 *
	 * @param (string) $filename - only fule filename without path. the path will include from config file.
	 * @return void
	 */
	abstract function download_backup($filename);
	
	/**
	 * Delete backup file
	 *
	 * @param (string) $filename - only fule filename without path. the path will include from config file.
	 * @return delete status. if file not exist, return error message.
	 */
	abstract function delete_backup($filename);
	
	/**
	 * List backup files
	 *
	 * @return (array) backup files
	 */
	abstract function list_backfiles();
	
} // End DBManager_Driver