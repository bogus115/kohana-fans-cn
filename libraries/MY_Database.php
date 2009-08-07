<?php defined('SYSPATH') or die('No direct script access.');
 /**
 * Extending Kohana Database library 
 *
 * Feture:
 *		- Added multiway configures of Database 
 *
 * Notice:
 *	It MUST changed Kohana databse config file, added $config['environment'], 
 *  $config['development'], $config['test'], $config['production'].
 *
 *  It also keep down $config['default'] to make sure unkown error.
 *
 *
 * @package    Core
 * @author     icyleaf (icyleaf.cn@gmail.com)
 */
 
class Database extends Database_Core {
 	
	/**
	 * Multiway profile of Database configures
	 */
	public function __construct()
	{
		$config = array();
		
		// custom environment key to databse config.
		$environment = Kohana::config('database.environment');
		// available: development, test, pro
		
		if ( !is_array($environment) && !empty($environment) )
		{
			$config = Kohana::config('database.'.$environment);
		}
		
		parent::__construct($config);
	}
}
 
?>