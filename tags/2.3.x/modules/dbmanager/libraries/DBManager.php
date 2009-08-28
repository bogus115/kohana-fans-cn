<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Database Manager library.
 * password hashing.
 *
 * @author icyleaf (http://icyleaf.com)
 * @license The MIT License
 * @version 0.1
 *
 * ----------------------------------------------------------
 * This Modules Inspired by WP-DBManager of Wordpress plugins 
 * ----------------------------------------------------------
 *
 * The MIT License
 *
 * Copyright (c) 2009 icyleaf
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 * TODO List
 * 1. auto backup [F]
 * 2. auto optimize [F]
 * 3. limit backup files numbers
 * 4. nofity by email 
 * 5. support for other available database driver of application
 *
 */
class DBManager_Core {
	
	// DBManager configuration
	protected $config;

	/**
	 * Return a static instance of DBManager.
	 *
	 * @return  object
	 */
	public static function instance()
	{
		static $instance;

		// Load the Auth instance
		empty($instance) and $instance = new DBManager();

		return $instance;
	}

	/**
	 * Configuration options.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$config = array();
		
		// Append application database configuration
		$config['host'] = Kohana::config('database.default.connection.host');
		$config['user'] = Kohana::config('database.default.connection.user');
		$config['type'] = Kohana::config('database.default.connection.type');
		$config['database'] = Kohana::config('database.default.connection.database');
		$config['table_prefix'] = Kohana::config('database.default.table_prefix');
		
		// Save the config in the object
		$this->config = $config;
		
		// Set the driver class name
		$driver = 'DBManager_'.$config['type'].'_Driver';
		
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('core.driver_not_found', $config['type'], get_class($this));

		// Load the driver
		$driver = new $driver($config);

		if ( ! ($driver instanceof DBManager_Driver))
			throw new Kohana_Exception('core.driver_implements', $config['type'], get_class($this), 'DBManager_Driver');

		// Load the driver for access
		$this->driver = $driver;
		
		Kohana::log('debug', 'DBManager Library loaded');

		Event::add('system.display', array($this, 'auto_backup'));
		
		//Event::add('system.display', array($this, 'auto_optimize'));
	}
	
	/**
	 * Get Mysql Version
	 *
	 * @return (string) mysql version
	 */
	public function get_version()
	{
		return $this->driver->get_version();
	}
	
	/**
	 * List tables information in database of application
	 *
	 * @return (object) tables information
	 */ 
	public function list_tables()
	{
		$table_info = array();
		$i = 0;
		$tables = $this->driver->list_tables();
		
		foreach($tables as $table)
		{
			$table_info[$i]['name'] = $table->Name;
			$table_info[$i]['records'] = number_format($table->Rows);
			$table_info[$i]['data_length'] = $table->Data_length;
			$table_info[$i]['data_usage'] = $this->format_size($table->Data_length);
			$table_info[$i]['index_length'] = $table->Index_length;
			$table_info[$i]['index_usage'] = $this->format_size($table->Index_length);
			$table_info[$i]['data_free'] = $table->Data_free;
			$table_info[$i]['overhead'] = $this->format_size($table->Data_free);
			
			$i++;
		}
		
		return $table_info;
	}
	
	/**
	 * Total tables number in database of application
	 *
	 * @return (int) tables count
	 */
	public function total_tables()
	{
		return $this->driver->total_tables();
	}
	
	/**
	 * Optimize tables
	 *
	 * @param (array) $tables - need to optimize tables
	 * @return void
	 */
	public function optimize_tables($tables = array())
	{
		return $this->driver->optimize_tables($tables);
	}
	
	/**
	 * Repair tables
	 *
	 * @param (array) $tables - need to repair tables
	 * @return void if successs, else return error messages.
	 */
	public function repair_tables($tables = array())
	{
		return $this->driver->repair_tables($tables);
	}
	
	/**
	 * Backup dababase of application
	 *
	 * @param (boolean) $gzip - set TRUE, compress sql file with Gzip. by default, set it FALSE.
	 * @return void if success, else return error messages.
	 */
	public function backup_db($gzip=FALSE)
	{
		return $this->driver->backup_db($gzip);
	}
	
	/**
	 * Download backup file
	 *
	 * @param (string) $filename - only fule filename without path. the path will include from config file.
	 * @return void
	 */
	public function download_backup($filename)
	{
		return $this->driver->download_backup($filename);
	}
	
	/**
	 * Delete backup file
	 *
	 * @param (string) $filename - only fule filename without path. the path will include from config file.
	 * @return delete status. if file not exist, return error message.
	 */
	public function delete_backup($filename)
	{
		return $this->driver->delete_backup($filename);
	}
	
	/**
	 * List backup files
	 *
	 * @return (array) backup files
	 */
	public function list_backfiles()
	{
		return $this->driver->list_backfiles();
	}
	
	/**
	 * Automatic backup dababase of application
	 *
	 * @param (boolean) $gzip - set TRUE, compress sql file with Gzip. by default, set it FALSE.
	 * @return void.
	 */
	public function auto_backup()
	{
		$cycle = Kohana::config('dbmanager.auto_backup');
		if ( $cycle==0 )
			return FALSE;
		
		$time = $this->read_time();
		
		if ( !is_numeric($time['backup']) )
		{
			$time['backup'] = time() + $this->format_time($cycle);
			$this->write_time($time['backup'], 'backup');
		}
		
		$gzip = Kohana::config('dbmanager.auto_backup_gzip');
		
		if ( $gzip )
			$sqlfile = MODPATH.'backup-db/'.$time['backup'].'_-_'.$this->config['database'].'.sql.gz';
		else
			$sqlfile = MODPATH.'backup-db/'.$time['backup'].'_-_'.$this->config['database'].'.sql';
			
		if ( $time['backup']<=time() && !file_exists($sqlfile) )
		{
			$next_time = time() + $this->format_time($cycle);
			$this->write_time($next_time, 'backup');
			Kohana::log('debug', 'Auto backup is starting at '.date('Y-m-d H:i', time()).'.');
			
			return $this->driver->backup_db($gzip);
		} else return 'FALSE';
	}
	
	/**
	 * Automatic optimize dababase of application
	 *
	 * @param (boolean) $gzip - set TRUE, compress sql file with Gzip. by default, set it FALSE.
	 * @return void.
	 */	
	public function auto_optimize()
	{
		$cycle = Kohana::config('dbmanager.auto_optimize');
		if ( $cycle==0 )
			return FALSE;
		
		$time = $this->read_time();
		
		if ( !is_numeric($time['optimize']) )
		{
			$time['optimize'] = time() + $this->format_time($cycle);
			$this->write_time($time['optimize'], 'optimize');
		}

		if ( $time['optimize']<=time() && !file_exists($sqlfile) )
		{
			$next_time = time() + $this->format_time($cycle);
			$this->write_time($next_time, 'backup');
			Kohana::log('debug', 'Auto Optimize is starting at '.date('Y-m-d H:i', time()).'.');
			
			return $this->dirver->optimize_tables('all');
		} else return 'FALSE';
	}
	
	/**
	 * Show Next time of Backup database
	 *
	 * @return string YYYY-MM-DD hh:mm.
	 */	
	public function next_backup_time()
	{
		$time = $this->read_time();
		return date('Y-m-d H:i', $time['backup']);
	}
	
	/**
	 * Show Next time of Backup database
	 *
	 * @return string.
	 */	
	public function next_optimize_time()
	{
		$time = $this->read_time();
		return date('Y-m-d H:i', $time['optimize']);
	}
	
	
	/**
	 * Formate string
	 *
	 * @param (string) $string - need to format string
	 * @param (stting) $type - format type
	 * @return (string) formated string
	 */
	private function format_srting($string, $type='uc')
	{
		switch($type)
		{
			case 'lower':
				return strtolower($string);
			case 'upper':
				return strtoupper($string);
			default:
			case 'ucfirst':
				return ucfirst(strtolower($string));
		}
	}
	
	/**
	 * Formate size
	 *
	 * @param (string) $rawSize - row of table length in database
	 * @return (string) row size.
	 */
	private function format_size($rawSize) {
		if($rawSize / 1073741824 > 1) 
			return round($rawSize/1048576, 1) . ' '.Kohana::lang('dbmanager.GiB');
		else if ($rawSize / 1048576 > 1)
			return round($rawSize/1048576, 1) . ' '.Kohana::lang('dbmanager.MiB');
		else if ($rawSize / 1024 > 1)
			return round($rawSize/1024, 1) . ' '.Kohana::lang('dbmanager.KiB');
		else
			return round($rawSize, 1) . ' '.Kohana::lang('dbmanager.bytes');
	}
	
	/**
	 * Formate time
	 *
	 * @param (int) $days - days
	 * @return (int) days in seconds unit.
	 */
	private function format_time($minites=null)
	{
		$minite = 60; // 1 minite
		
		if ( !empty($minites) )
			return $minite * $minites;
	}
	
	/**
	 * Read 'automatic.txt' in backup-db
	 *
	 * @return  string content of file.
	 */
	private function read_time()
	{
		$file = MODPATH.'/dbmanager/backup-db/automatic.txt';
		
		$fp = fopen($file, "r");
		$line = '';
		$content = array();
		while (!feof($fp))
		{
			$line = fgets($fp, 4096);
			if ( preg_match('/NEXT_BACKUP_TIME=(.*)/i', $line, $match1) )
				$content['backup'] = $match1[1];
				
			if ( preg_match('/NEXT_BACKUP_TIME=(.*)/i', $line, $match2) )
				$content['optimize'] = $match2[1];
		}
		fclose($fp);
		
		return $content;
	}

	/**
	 * Write 'automatic.txt' in backup-db
	 *
	 * @return  string content of file.
	 */
	private function write_time($time, $type)
	{
		if ( empty($time) || !is_numeric($time) || empty($type))
			return FALSE;
			
		$file = MODPATH.'/dbmanager/backup-db/automatic.txt';
		
		$fp = fopen($file, "r");
		$line = '';
		$content = '';
		while (!feof($fp))
		{
			$line = fgets($fp, 4096);
			$content .= $line;
		}
		fclose($fp);
		
		switch($type)
		{
			case 'backup':
				$new_content = preg_replace('/NEXT_BACKUP_TIME=(.*)/','NEXT_BACKUP_TIME='.$time, $content);
				break;
			case 'optimize':
				$new_content = preg_replace('/NEXT_BACKUP_TIME=(.*)/','NEXT_BACKUP_TIME='.$time, $content);
				break;
		}
		
		$fp = fopen($file, "w");
		fwrite($fp, $new_content);
		fclose($fp);
		
/*
!! DON'T DELETE OR REMOVE THIS FILE !!
		
NEXT_BACKUP_TIME=N/A
		
NEXT_OPTIMIZE_TIME=N/A
*/
		return TRUE;
	}

} // End DBManager