<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RESTRequest class
 *
 * @package libraries
 * @author icyleaf
 * @version 0.2
 *
 * Base on Curl Library by Matt Wells (www.ninjapenguin.co.uk)
 *
 **/
class RESTRequest_Core {
	
	private $config = array();
	private $resource = null;
	private static $http_code = null;
	
	/**
	 * Factory Method
	 */
	public static function factory($data = array())
	{
		return new RESTRequest($data);
	}
	
	/**
	 * Constructor
	 */
	public function __construct($data = array())
	{
		if(!function_exists('curl_init'))
			throw new Kohana_User_Exception('A cURL error occurred', 'It appears you do not have cURL installed!');
		
		$config = array(
			CURLOPT_HEADER => false
		);
		
		//Apply any passed configuration
		$data += $config;
		$this->config = $data;
		
		$this->resource = curl_init();
				
		//Apply configuration settings
		foreach ($this->config as $key => $value) {
			$this->set_opt($key, $value);
		}
	}
	
	/**
	 * Set option
	 * @param String 	Curl option to set
	 * @param String	Value for option
	 * @chainable
	 */
	public function set_opt($key, $value)
	{
		curl_setopt($this->resource, $key, $value);
		return $this;
	}
	
	/**
	 * Execute the curl request and return the response
	 * @return String				Returned output from the requested resource
	 * @throws Kohana_User_Exception
	 */
	public function exec()
	{
		$ret = curl_exec($this->resource);
		
		//Wrap the error reporting in an exception
		if($ret === false)
		{
			throw new Kohana_User_Exception("Curl Error", curl_error($this->resource));
		}
		else
		{
			self::$http_code = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);
			return $ret;
		}
	}
	
	/**
	 * Get Error
	 * Returns any current error for the curl request
	 * @return string	The error
	 */
	public function get_error()
	{
		return curl_error($this->resource);
	}
	
	/**
	 * Destructor
	 */
	function __destruct()
	{
		curl_close($this->resource);
	}
	
	
	/**
	 * Get
	 * Execute an HTTP GET request using curl
	 * @param String	url to request
	 * @param Array		additional headers to send in the request
	 * @param Bool		flag to return only the headers
	 * @param Array		Additional curl options to instantiate curl with
	 */
	public static function get($url, Array $headers = array(), $headers_only = false, Array $curl_options = array())
	{
		$ch = RESTRequest::factory($curl_options);
		
		$ch->set_opt(CURLOPT_URL, $url)
		->set_opt(CURLOPT_RETURNTRANSFER, true)
		->set_opt(CURLOPT_NOBODY, $headers_only);
		
		//Set any additional headers
		if( !empty($headers) ) 
			$ch->set_opt(CURLOPT_HTTPHEADER, $headers);
		
		$result['content'] = $ch->exec();
		$result['http_code'] = self::$http_code;
		
		return new RESTResponse($result);
	}
	
	/**
	 * Post
	 * Execute an HTTP POST request, posting the past parameters
	 * @param String	url to request
	 * @param Mix		past data to post to $url
	 * @param Array		additional headers to send in the request
	 * @param Bool		flag to return only the headers
	 * @param Array		Additional curl options to instantiate curl with
	 */
	public static function post($url, $data, Array $headers = array(), $headers_only = false, Array $curl_options = array())
	{
		$ch = RESTRequest::factory($curl_options);
		
		$request = '';
		if ( is_array($data) )
		{
			foreach( $data as $key => $value )
				$request .= $key.'='.$value.'&';
		}
		else
		{
			$request = $data;
		}
		
		$ch->set_opt(CURLOPT_URL, $url)
		->set_opt(CURLOPT_NOBODY, $headers_only)
		->set_opt(CURLOPT_RETURNTRANSFER, true)
		->set_opt(CURLOPT_POST, true)
		->set_opt(CURLOPT_POSTFIELDS, $request);
	  
		//Set any additional headers
		if( !empty($headers) ) 
			$ch->set_opt(CURLOPT_HTTPHEADER, $headers);
		
		$result['content'] = $ch->exec();
		$result['http_code'] = self::$http_code;
		
		return new RESTResponse($result);
	}
}

/**
 * RESTResponse class
 *
 * @package libraries
 * @author icyleaf
 * @version 0.1
 *
 **/
class RESTResponse {
	
	private $content;
	private $http_code;
	
	public function __construct($response)
	{
		$this->content = $response['content'];
		$this->http_code = $response['http_code'];
		
		return $this->content;
	}
	
	public function to_nomral_format()
	{
		return $this->content;
	}
	
	public function to_xml()
	{
		return $this->content;
	}
	
	public function to_json()
	{
		return json_decode($this->content, true);
	}
	
	public function get_http_code()
	{
		return $this->http_code;
	}
	
	public function __toString() {
		return (string) $this->get_http_code();
	}
}
?>
