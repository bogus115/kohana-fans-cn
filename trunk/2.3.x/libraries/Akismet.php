<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Akismet - Anti SPAM library
 *
 * @package libraries
 * @author icyleaf
 * @version 0.1
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * website	 	http://icyleaf.com
 * email	 	icyleaf.cn@gmail.com
 *
 * Original file was come form CodeIgniter Library conversion by Elliot Haughin:
 * http://www.haughin.com/
 *
 *
 * NOTICE:
 * i just conversion CodeIgniter Library to Kohana Library and append several new futures.
 *
 *
 * Useage:
 * You may create a config file named 'akisment.php' in 'application/config' directory or not.
 * 1. If you create it whitch include two variables: $config['blog'] and $config['key'].
 * The 'key' is an Akismet API Key, sign up if you don't have it.
 *
 * 2. If not create if, you also using this library with called 'new Akismet(<key>)', 
 * <key> instead of Akismet API Key. Default, value of 'blog' variable is your application URL.
 *
 * Instantiation it: 
 * // using config file.
 * $akismet = new Akismet();
 *
 * // custom key or overwrite key in config file.
 * $akismet = new Akismet(<key>);
 *
 *
 * !!!IMPORTANT!!!
 * To use the library, you’ll need to get an Akismet API Key:
 * http://akismet.com/personal/
 */
class Akismet_Core {

	// Akismet Services
	private $akismet_config = array
	(
		'version' => '1.1',
		'server' => 'rest.akismet.com',
		'port' => 80
	);
	
	// ignore $_SERVICE variables
	private $ignore = array
	(
		'HTTP_COOKIE', 
		'HTTP_X_FORWARDED_FOR', 
		'HTTP_X_FORWARDED_HOST', 
		'HTTP_MAX_FORWARDS', 
		'HTTP_X_FORWARDED_SERVER', 
		'REDIRECT_STATUS', 
		'SERVER_PORT', 
		'PATH',
		'DOCUMENT_ROOT',
		'SERVER_ADMIN',
		'QUERY_STRING',
		'PHP_SELF'
	);
	
	private $conncet;
	private $config;
	private $comment = array();
	private $errors = array();
	
	/**
	 * On first Akismet instance creation, sets up the config and vaild api key.
	 *
	 * @param string $key - Akismet API Key
	 * @param array/string $config - config file
	 */
	public function __construct($key=NULL, $config=FALSE)
	{
		$config = $config?$config:(Kohana::find_file('config', 'akismet')?Kohana::config('akismet'):NULL);
		
		if ( sizeof($config)<=0 )
		{
			$config['blog'] = url::base();
		}
		
		$config['key'] = empty($key)?(isset($config['key'])?$config['key']:NULL):$key;

		$this->config = $config;
		
		$this->__connect();
		
		if( $this->errors_exist() )
		{
			$this->errors = array_merge($this->errors, $this->get_errors());
		}
		
		// Check if the API key is valid
		if( !$this->valid_key() )
		{
			$this->set_error('AKISMET_INVALID_KEY', "Your Akismet API key is not valid.");
		}
	}

	/**
	 * Get response information
	 *
	 * @param string $request 
	 * @param string $path 
	 * @param string $type 
	 * @param string $response_length 
	 * @return void
	 */
	public function get_response($request, $path, $type='post', $response_length=1160)
	{
		$this->__connect();
		
		if( $this->conncet && !$this->is_error('AKISMET_SERVER_NOT_FOUND') )
		{
			$request  = strToUpper($type).' /'.$this->akismet_config['version'].'/'.$path.' HTTP/1.0'."\r\n".
				'Host: '.$this->config['key'].'.'.$this->akismet_config['server']."\r\n".
				'Content-Type: application/x-www-form-urlencoded; charset=utf-8'."\r\n".
				'Content-Length: '.strlen($request)."\r\n".
				'User-Agent: Akismet Kohana Library'."\r\n\r\n".
				$request;
				
			$response = '';
			
			@fwrite($this->conncet, $request);
		
			while(!feof($this->conncet))
			{
				$response .= @fgets($this->conncet, $response_length);
			}
		
			$response = explode("\r\n\r\n", $response, 2);
			
			return $response[1];
			
		} else $this->set_error('AKISMET_RESPONSE_FAILED', "The response could not be retrieved.");
		
		$this->__disconnect();
	}
	
	/**
	 * Check Comment if it is a spam from Akisemet.com
	 *
	 * @param string $comment 
	 * @return boolean
	 */
	public function is_spam($comment=array())
	{
		$this->set_comment($comment);
		$response = $this->get_response($this->build_query(), 'comment-check');
		
		return ($response == 'true');
	}
	
	/**
	 * Submit spam to Akisemet.com
	 *
	 * @return void
	 */
	public function submit_spam()
	{
		$this->set_comment($comment);
		$this->get_response($this->_get_querystring(), 'submit-spam');
	}
	
	public function submit_ham()
	{
		$this->set_comment($comment);
		$this->get_response($this->_get_query_string(), 'submit-ham');
	}
	
	/**
	 * Open sock connect
	 *
	 * @return void
	 */
	private function __connect()
	{
		if ( !$this->conncet = @fsockopen($this->akismet_config['server'], $this->akismet_config['port']) )
		{
			$this->set_error('AKISMET_SERVER_NOT_FOUND', 'Could not connect to akismet server.');
		}
	}
	
	/**
	 * Close sock connect
	 *
	 * @return void
	 */
	private function __disconnect()
	{
		@fclose($this->conncet);
	}
		
	/**
	 * Valid API key
	 *
	 * @return boolean
	 */
	private function valid_key()
	{
		$key_check = $this->get_response('key='.$this->config['key'].'&blog='.$this->config['blog'], 'verify-key');
	
		return ($key_check == 'valid');
	}
	
	/**
	 * Get Exist errors total
	 *
	 * @return int
	 */
	public function errors_exist()
	{
		return (count($this->errors) > 0);
	}
	
	/**
	 * Get formated comment
	 *
	 * @return array
	 */
	public function get_comment()
	{
		return $this->comment;
	}
	
	/**
	 * Set a error
	 *
	 * @param string $title 
	 * @param string $message 
	 */
	private function set_error($title, $message)
	{
		$this->errors[$title] = $message;
	}
	
	/**
	 * Check error exist
	 *
	 * @param string $title 
	 * @return void
	 */
	private function is_error($title)
	{
		return isset($this->errors[$title]);
	}
	
	/**
	 * Comment complate
	 *
	 * @param string $comment 
	 * @return void
	 */
	private function set_comment($comment=array())
	{
		if(!empty($comment))
		{
			$this->comment = $comment;
			$this->format_comment();
			$this->fill_comment();
		}
	}
	
	/**
	 * Format comment
	 *
	 * @return void
	 */
	private function format_comment()
	{
		$format = array
		(
			'type' => 'comment_type',
			'author' => 'comment_author',
			'email' => 'comment_author_email',
			'website' => 'comment_author_url',
			'content' => 'comment_content'
		);
		
		foreach($format as $short => $long)
		{
			if(isset($this->comment[$short]))
			{
				$this->comment[$long] = $this->comment[$short];
				unset($this->comment[$short]);
			}
		}
	}
	
	/**
	 * Fill comment
	 *
	 * @return void
	 */
	private function fill_comment()
	{
		if(!isset($this->comment['user_ip']))
		{
			$this->comment['user_ip'] = ($_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR')) ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR');
		}
		
		if(!isset($this->comment['user_agent']))
		{
			$this->comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		}
		
		if(!isset($this->comment['referrer']) && !empty($_SERVER['HTTP_REFERER']))
		{
			$this->comment['referrer'] = $_SERVER['HTTP_REFERER'];
		}
		
		if(!isset($this->comment['blog']))
		{
			$this->comment['blog'] = $this->config['blog'];
		}
	}

	/**
	 * Build query string
	 *
	 * @return string
	 */
	private function build_query()
	{
		foreach($_SERVER as $key => $value)
		{
			if(!in_array($key, $this->ignore))
			{
				if($key == 'REMOTE_ADDR')
				{
					$this->comment[$key] = $this->comment['user_ip'];
				}
				else
				{
					$this->comment[$key] = $value;
				}
			}
		}
		
		$query_string = '';

		foreach($this->comment as $key => $data)
		{
			$query_string .= $key . '=' . urlencode(stripslashes(empty($data)?'':$data)) . '&';
		}

		return $query_string;
	}
}
?>