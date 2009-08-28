<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * DBManager Mysql driver.
 */
class DBManager_Mysql_Driver extends DBManager_Driver {
	
	// Database
	protected $db;
	
	// DBManager configuration
	protected $config;

	/**
	 * Constructor loads the user list into the class.
	 *
	 * @return void
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);
		
		$this->db = new Database;
		
		$this->config = $config;
	}
	
	/**
	 * Get Mysql Version
	 *
	 * @return (string) mysql version
	 */
	public function get_version()
	{
		$result = $this->db->query("SELECT VERSION() AS version")->as_array();
		return $result[0]->version;
	}
	
	/**
	 * List tables information in database of application
	 *
	 * @return (object) tables information
	 */ 
	public function list_tables()
	{
		return $this->db->query("SHOW TABLE STATUS")->as_array();
	}
	
	/**
	 * Total tables number in database of application
	 *
	 * @return (int) tables count
	 */
	public function total_tables()
	{
		return $this->db->query("SHOW TABLE STATUS")->count();
	}
	
	/**
	 * Optimize tables
	 *
	 * @param (array) $tables - need to optimize tables, set 'all' will repair all tables
	 * @return void
	 */
	public function optimize_tables($tables = array())
	{
		if ( $tables=='all' && !is_array($tables) )
			$tables = $this->db->list_tables();
		
		$query = $this->table_query_string($tables);
		if ( $query['type']=='error' )
			return $query['content'];
		
		$this->db->query('OPTIMIZE TABLE '.$query['content'])->as_array();
		
		return;
	}
	
	/**
	 * Repair tables
	 *
	 * @param (array) $tables - need to repair tables, set 'all' will repair all tables
	 * @return void if successs, else return error messages.
	 */
	public function repair_tables($tables = array())
	{
		if ( $tables=='all' && !is_array($tables) )
			$tables = $this->db->list_tables();
			
		$query = $this->table_query_string($tables);
		if ( $query['type']=='error' )
			return $query['content'];
			
		$this->db->query('REPAIR TABLE '.$query['content'])->as_array();
		
		return;
	}
	
	/**
	 * Backup dababase of application
	 *
	 * @param (boolean) $gzip - set TRUE, compress sql file with Gzip. by default, set it FALSE.
	 * @return void if success, else return error messages.
	 */
	public function backup_db($gzip=FALSE)
	{
		$mysql_path = $this->detect_mysql();

		$backup = $this->config;
		$backup += $mysql_path;
		$backup['password'] = Kohana::config('database.default.connection.password');
		$backup['date'] = time();
		$backup['filepath'] = preg_replace('/\//', '/', Kohana::config('dbmanager.backup_filepath'));
		$backup['filename'] = $backup['filepath'].'/'.$backup['date'].'_-_'.$this->config['database'].'.sql';

		if ( $gzip ) {
			$backup['filename'] = $backup['filename'].'.gz';
			$query_string = $backup['mysqldump'].' --host="'.$this->config['host'].'" --user="'.$this->config['user'].'" --password="'.$backup['password'].'" --add-drop-table --skip-lock-tables '.$this->config['database'].' | gzip > '.$backup['filename'];
		} 
		else
		{
			$backup['filename'] = $backup['filename'];
			$query_string = $backup['mysqldump'].' --host="'.$this->config['host'].'" --user="'.$this->config['user'].'" --password="'.$backup['password'].'" --add-drop-table --skip-lock-tables '.$this->config['database'].' > '.$backup['filename'];
		}
		$error = $this->execute_backup($backup, $query_string);
		
		return $error;
	}
	
	/**
	 * Download backup file
	 *
	 * @param (string) $filename - only fule filename without path. the path will include from config file.
	 * @return void
	 */
	public function download_backup($filename)
	{
		$filename = preg_replace('/\//', '/', Kohana::config('dbmanager.backup_filepath')).'/'.$filename;
		if ( !file_exists($filename) )
			return 'file not exist';
			
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment; filename=".basename($filename).";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($filename));
		@readfile($filename);
		exit();
	}
	
	/**
	 * Delete backup file
	 *
	 * @param (string) $filename - only fule filename without path. the path will include from config file.
	 * @return delete status. if file not exist, return error message.
	 */
	public function delete_backup($filename)
	{
		$filename = preg_replace('/\//', '/', Kohana::config('dbmanager.backup_filepath')).'/'.$filename;
		if ( !file_exists($filename) )
			return 'file not exist';
		else
			return unlink($filename);
	}
	
	/**
	 * List backup files
	 *
	 * @return (array) backup files
	 */
	public function list_backfiles()
	{
		$backup['filepath'] = preg_replace('/\//', '/', Kohana::config('dbmanager.backup_filepath'));
		$dir = dir($backup['filepath']);

		$list = array();
		while($file=$dir->read())
		{
			if((is_dir($backup['filepath'].'/'.$file)) && ($file=".") && ($file=".."))
				continue;

			array_push($list, $file);
		}
		$dir->close();

		return $list;
	}
	
	/**
	 * Build query command for backup
	 *
	 * @param (array) $tables - need to query tables
	 * @return (string) $return_string - query command
	 */
	private function table_query_string($tables = array())
	{
		$return_string = array
		(
			'type',
			'content'
		);
		
		$tables_error = null;
		$tables_string = null;
		
		if ( sizeof($tables)<=0 )
		{
			return Kohana::lang('dbmanager.empty_table', $this->config['database']);
		}
		
		if ( is_array($tables) )
		{
			foreach ( $tables as $table_name )
			{
				if ( !version_compare(KOHANA_VERSION, '2.3.2', '>=') )
				{
					$table_prefix = substr($table_name, 0, strlen($this->config['table_prefix']));
					if ( $table_prefix==$this->config['table_prefix'] )
						$table_name = substr($table_name, strlen($this->config['table_prefix']));
				}

				if ( !$this->db->table_exists($table_name) )
					$tables_error .= '`, `<b>'.$table_name.'</b>';
			}
			
			if ( !empty($tables_error) )
			{
				$return_string['type'] = 'error';
				$return_string['content'] = Kohana::lang('dbmanager.notexist_table', $this->config['database'], substr($tables_error, 3).'`');
				return $return_string;
			}
			
			foreach ( $tables as $table_name )
			{
				if ( $table_prefix==$this->config['table_prefix'] )
					$tables_string .=  '`, `'.$table_name;
				else
					$tables_string .=  '`, `'.$this->config['table_prefix'].$table_name;
			}
			$tables_string = substr($tables_string, 3).'`';
		}
		else
		{	
			$table_prefix = substr($tables, 0, strlen($this->config['table_prefix']));
			if ( $table_prefix==$this->config['table_prefix'] )
				$tables = substr($tables, strlen($this->config['table_prefix']));

			//TODO the code could be deleted if Kohana has update above.
				
			if ( !$this->db->table_exists($tables) )
				$tables_error .= '`<b>'.$tables.'</b>`';
				
			if ( !empty($tables_error) )	
			{
				$return_string['type'] = 'error';
				$return_string['content'] = Kohana::lang('dbmanager.notexist_table', $this->config['database'], $tables_error);
				return $return_string;
			}
				
			$tables_string = $this->config['table_prefix'].$tables;
		}
		
		$return_string['type'] = 'query';
		$return_string['content'] = $tables_string;
		return $return_string;
	}
	
	/**
	 * Detect Mysql
	 *
	 * @return (array) $paths - include mysql and mysqldump application's path.
	 */
	private function detect_mysql()
	{
		$db = new Database;
		$paths = array('mysql' => '', 'mysqldump' => '');
		if(substr(PHP_OS,0,3) == 'WIN') {
			$mysql_install = $db->query("SHOW VARIABLES LIKE 'basedir'")->as_array();

			if( is_array($mysql_install) && sizeof($mysql_install)>0 ) {
				$install_path = str_replace('\\', '/', $mysql_install[0]->Value);
				$paths['mysql'] = $install_path.'bin/mysql.exe';
				$paths['mysqldump'] = $install_path.'bin/mysqldump.exe';
			} else {
				$paths['mysql'] = 'mysql.exe';
				$paths['mysqldump'] = 'mysqldump.exe';
			}
		} else {
			if(function_exists('exec')) {
				$paths['mysql'] = @exec('which mysql');
				$paths['mysqldump'] = @exec('which mysqldump');
			} else {
				$paths['mysql'] = 'mysql';
				$paths['mysqldump'] = 'mysqldump';
			}
		}
		return $paths;
	}
	
	/**
	 * Execute backup
	 *
	 * @param (array) $backup - backup configuration.
	 * @param (string) $command - execute backup command
	 * @return void if success, else reutrn error messages.
	 */
	private function execute_backup($backup, $command) {
		$info = $this->check_backup_files($backup);
		if ( !empty($info) )
			return $info;
		
		if(substr(PHP_OS, 0, 3) == 'WIN') {
			$writable_dir = $backup['filepath'];
			$tmpnam = $writable_dir.'/dbmanager_script.bat';
			$fp = fopen($tmpnam, 'w');
			fwrite($fp, $command);
			fclose($fp);
			system($tmpnam.' > NUL', $error);
			unlink($tmpnam);
		} else {
			passthru($command, $error);
		}
		
		return $error;
	}
	
	/**
	 * Check backup file
	 *
	 * @param (array) $backup - backup configuration.
	 * @return void if success, else reutrn error messages.
	 */
	private function check_backup_files($backup)
	{
		$error = array();
		if ( !file_exists($backup['filepath']))
			mkdir($backup['filepath'], 0666);
			
		if ( !file_exists($backup['mysql']) )
			array_push($error, 'file of mysql.exe is not exist');
		
		if ( !file_exists($backup['mysqldump']) )
			array_push($error, 'file of mysqldump.exe is not exist');
			
		return $error;
	}
	
	/**
	 * Check function
	 *
	 * @return void if those functions exists, else reutrn error messages.
	 */
	private function check_fuctions()
	{
		$error = array();
		if ( !function_exists('passthru') )
			array_push($error, 'function passthru not exist');
			
		if ( !function_exists('system') )
			array_push($error, 'function system not exist');
			
		if ( !function_exists('exec') )
			array_push($error, 'function exec not exist');
		
		return $error;
	}
	
} // End DBManager_Mysql_Driver