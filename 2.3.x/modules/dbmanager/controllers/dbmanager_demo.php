<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database Manager library.
 *
 * @package    DBManager
 * @author     icyleaf
 * @copyright  (c) 2009 icyleaf
 * @license    MIT
 */
class Dbmanager_Demo_Controller extends Template_Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	// Use the default Kohana template
	public $template = 'dbmanager/demo';
	
	public function __construct()
	{
		$this->auto_render = false;
	}
	
	public function index()
	{
		$db = new Database;
		
		$dbmanager = DBManager::instance();
		
		echo $dbmanager->optimize_tables('all');
		
		//echo $dbmanager->next_backup_time().'<br />';
		
		//echo $dbmanager->backup_db();
		/*
		// List tables
		echo '==================<br />List tables<br />==================';
		$tables = $dbmanager->list_tables();
		echo Kohana::debug($tables);
		*/
		// Only list name of tables
		echo '==================<br />Only list name of tables<br />==================';
		$table_name = $db->list_tables();
		echo Kohana::debug($table_name);
/*
		// List backup files
		echo '==================<br />List backup files<br />==================';
		echo Kohana::debug($dbmanager->list_backfiles());

		// Optimize Tables
		echo '==================<br />Optimize Tables<br />==================<br />';
		$result = $dbmanager->optimize_tables($table_name);
		if ( !empty($result) )
			echo $result;
		else
			echo '全部优化完毕';

		// Download backup file. $filename = '1234567890_-_database.sql'
		// $dbmanager->download_backup($filename);

		// Delete backup file. $filename = '1234567890_-_database.sql'
		// $dbmanager->delete_backup($filename);
		*/
		

		
		// Display the demo page
		//$this->template->title = 'Database Manager';
		//$this->template->content = ;
	}
	
}