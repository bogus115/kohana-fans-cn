<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Module Manager Library
 *
 * @package libraries
 * @author icyleaf
 * @copyright http://www.opensource.org/licenses/bsd-license.php
 * @version 0.1
 *
 *
 * About:
 *
 * By icyleaf (http://icyleaf.com)
 * February 26, 2009
 * Contact me: icyleaf.cn@gmail.com
 *
 *
 * Usage:
 *
 * This library could show modules of application information about
 * all modules, active modules, incative modules. it also could to
 * add module, active module and inactive module. it is a quick way
 * to manage modules.
 *
 *
 * Notice: 
 *
 * This library will append one line comment to  $config['modules'] section in application/config/config.php.
 *
 *
 * Full class signature:
 *
 * public static function instance()
 * public function __construct()
 * public function list_all()
 * public function list_inactive()
 * public function list_active()
 * public function add($name, $desc='')
 * public function inactive($name)
 * public function active($name)
 * public function modules_markup()
 * private function read_file($isAll=TRUE)
 * private function write_file($content)
 * private function check_module($name)
 *
 */
class Module_Core {
	
	// Application configuretion file path
	private $filepath;
	
	// Head singleton
	private static $instance;

	/**
	 * Returns a singleton instance of Module.
	 *
	 * @return  Module_Core
	 */
	public static function instance()
	{
		// Create the instance if it does not exist
		empty(self::$instance) AND new Module;

		return self::$instance;
	}

	/**
	 * Seting configuretion of applcation file path and validates markup.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->filepath = APPPATH.'config/config.php';
		
		$this->modules_markup();
		
		self::$instance = $this;
	}
	
	/**
	 * List all modules
	 *
	 * @return  array
	 */
	public function list_all()
	{	
		$content = $this->read_file(FALSE);
		
		$all_modules = array();
		
		preg_match_all('/MODPATH\s*.\s*\'(.*)\',\s*\/\/\s*(.*)/', $content, $all);
		
		for ( $i=0;$i<sizeof($all[0]);$i++ )
		{
			$all_modules[$i]['name'] = $all[1][$i];
			$all_modules[$i]['desc'] = $all[2][$i];
			$all_modules[$i]['path'] = MODPATH.$all[1][$i].'/';
		}

		return $all_modules;
	}
	
	/**
	 * List inactive modules
	 *
	 * @return  array
	 */
	public function list_inactive()
	{
		$content = $this->read_file(FALSE);
		
		$inactive_modules = array();
		
		preg_match_all('/\/\/\s*MODPATH\s*.\s*\'(.*)\',\s*\/\/\s*(.*)/', $content, $inactive);
		
		for ( $i=0;$i<sizeof($inactive[0]);$i++ )
		{
			$inactive_modules[$i]['name'] = $inactive[1][$i];
			$inactive_modules[$i]['desc'] = $inactive[2][$i];
			$inactive_modules[$i]['path'] = MODPATH.$inactive[1][$i].'/';
		}
		
		return $inactive_modules;
	}

	/**
	 * List active modules
	 *
	 * @return  array
	 */
	public function list_active()
	{
		$all_modules = $this->list_all();
		$inactive_modules = $this->list_inactive();
		$active_modules = array();
		
		foreach ( $all_modules as &$all )
					if ( !in_array($all, $inactive_modules) )
						array_push($active_modules, $all);
		
		return $active_modules;
	}

	/**
	 * Add modules to configuretion of application
	 *
	 * @param string $name - module name
	 * @param string $desc - module description. by default, it empty.
	 * @return  boolean TRUE if success, else return FALSE.
	 */
	public function add($name, $desc='')
	{
		if ( empty($name) ) 
			return FALSE;
			
		if ( $this->check_module($name) )
			return FALSE;
			
		$content = $this->read_file();
		if ( !preg_match('/\/\/ Modules Makrup/i', $content) )
			return FALSE;
		
		$desc = empty($desc)?'':'   // '.$desc;
		$add_module = '// Modules Makrup
	MODPATH.\''.$name.'\','.$desc;
	
		$new_content = preg_replace('/\/\/ Modules Makrup/i', $add_module, $content);

		return $this->write_file($new_content);
	}
	
	/**
	 * Inactive modules from configuretion of application
	 *
	 * @param string $name - module name
	 * @return  boolean TRUE if success, else return FALSE.
	 */
	public function inactive($name)
	{
		if ( empty($name) ) 
			return FALSE;
			
		$content = $this->read_file();
		
		if ( preg_match('/\/\/\s*MODPATH\s*.\s*\''.$name.'\',/i', $content) )
			return FALSE;
			
		if ( !preg_match('/MODPATH\s*.\s*\''.$name.'\',/i', $content) )
			return FALSE;
		
		$new_content = preg_replace('/MODPATH\s*.\s*\''.$name.'\',/i', '// MODPATH.\''.$name.'\',', $content);
		
		return $this->write_file($new_content);
	}
	
	/**
	 * Active modules from configuretion of application
	 *
	 * @param string $name - module name
	 * @return  boolean TRUE if success, else return FALSE.
	 */
	public function active($name)
	{
		if ( empty($name) ) 
			return FALSE;
			
		if ( !$this->check_module($name) )
			return FALSE;
		
		$content = $this->read_file();
		
		if ( !preg_match('/\/\/\s*MODPATH\s*.\s*\''.$name.'\',/i', $content) )
			return FALSE;
		
		$new_content = preg_replace('/\/\/\s*MODPATH\s*.\s*\''.$name.'\',/i', 'MODPATH.\''.$name.'\',', $content);
		
		return $this->write_file($new_content);
	}
	
	/**
	 * Markup modules
	 *
	 * @return  boolean TRUE if success, else return FALSE.
	 */
	public function modules_markup()
	{
		$content = $this->read_file();
		
		if ( preg_match('/\/\/ Modules Makrup/i', $content) )
			return FALSE;
		 
		$markup ='$config[\'modules\'] = array
(
	// Modules Makrup
';
		$new_content = preg_replace('/\$config\[\'modules\'\] = array\s*\(/i', $markup, $content);
		
		return $this->write_file($new_content);
	}
	
	/**
	 * Read file
	 *
	 * @param boolean $isAll - TRUE is read full file, else only read MODULES section.
	 * @return  string content of file.
	 */
	private function read_file($isAll=TRUE)
	{
		$fp = fopen($this->filepath, "r");
		$line = '';
		$content = '';
		$read = FALSE;
		while (!feof($fp))
		{
			$line = fgets($fp, 4096);
			if ( !$isAll )
			{
				if ( !$read && preg_match('/\$config\[\'modules\'\] = array/i', $line) )
					$read = TRUE;
					
				if ( $read )
					$content .= $line;
				else
					continue;
			} else $content .= $line;
		}
		fclose($fp);
		
		return $content;
	}

	/**
	 * Write file
	 *
	 * @param string $content - write content.
	 * @return  boolean TRUE if success, else return FALSE.
	 */
	private function write_file($content)
	{
		if ( empty($content) )
			return FALSE;

		if ( !is_writeable($this->filepath) )
			return FALSE;

		$fp = fopen($this->filepath, "w");
		fwrite($fp, $content);
		fclose($fp);

		return TRUE;
	}

	/**
	 * Check module
	 *
	 * @param string $name - module name
	 * @return  boolean TRUE if success, else return FALSE.
	 */
	private function check_module($name)
	{
		if ( empty($name) ) 
			return FALSE;
		
		$active_modules = $this->list_all();
		$exit = FALSE;
		foreach( $active_modules as $module )
		{
			if ( $module['name']==$name)
				$exit = TRUE;
			else
				continue;
		}
		
		if ( !$exit )
			return FALSE;
		else
			return TRUE;
	}
}

?>