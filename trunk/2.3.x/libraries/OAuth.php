<?php defined('SYSPATH') or die('No direct script access.');
/**
 * OAuth library
 *
 * @package libraries
 * @author icyleaf
 * @version 0.3
 * 
 */
class OAuth_Core {
	
	public $key = null;
	public $secret = null;
	private $url = null;
	
	/**
	 * Construct function
	 *
	 * @param string $key - API key
	 * @param string $secret - API secret
	 */
	public function __construct($key=null, $secret=null)
	{
		// include OAuth classes
		include_once Kohana::find_file('vendor', 'OAuth', true);
		
		$this->key = $key;
		$this->secret = $secret;
	}
	
	/**
	 * Get Requset token
	 *
	 * @param string $requestTokenURL  - requset token url
	 * @param string $httpMethod - http sending method
	 * @param string $parameters - sending parameters
	 * @return string
	 */
	public function getRequestToken($requestTokenURL, $httpMethod='POST', $parameters=array())
	{
		$this->url = $requestTokenURL;
		$request = $this->prepareRequest(null, $httpMethod, $requestTokenURL, $parameters);
		
		return $this->doRequest($request);
	}
	
	/**
	 * Ger access token
	 *
	 * @param string $accessTokenURL - access token url
	 * @param string $requestToken - request token
	 * @param string $httpMethod - http sending method
	 * @param string $parameters - sending parameters
	 * @return string
	 */
	public function getAccessToken($accessTokenURL, $requestToken, $httpMethod='POST', $parameters=array())
	{
		$this->url = $accessTokenURL;
		$request = $this->prepareRequest($requestToken, $httpMethod, $accessTokenURL, $parameters);
		
		return $this->doRequest($request);
	}
	
	/**
	 * OAuth validate method
	 *
	 * @param string $type - activate in HMAC_SHA1, RSA_SHA1, PLAINTEXT
	 * @return object
	 */
	public function generalSignMethod($type='HMAC_SHA1')
	{
		switch(strtoupper($type))
		{
			default:
			case 'HMAC_SHA1':
				return new OAuthSignatureMethod_HMAC_SHA1();
			case 'RSA_SHA1':
				return new OAuthSignatureMethod_RSA_SHA1();
			case 'PLAINTEXT':
				return new OAuthSignatureMethod_PLAINTEXT();
		}
	}
	
	/**
	 * Get request/access token
	 *
	 * @param string $key - request/access token
	 * @param string $secret - request/access secret
	 * @return object
	 */
	public function getOAuthToken($key, $secret)
	{
		return new OAuthToken($key, $secret);
	}
	
	/**
	 * Get request url
	 *
	 * @param string $method - http sending method
	 * @param string $url - http sending url
	 * @param string $parameters - http sending parameters
	 * @return object
	 */
	public function getRequest($method, $url, $parameters)
	{
		return new OAuthRequest($method, $url, $parameters);
	}
	
	/**
	 * Get request url from Consumer
	 *
	 * @param string $consumer - OAuth consumer
	 * @param string $token - request token
	 * @param string $method - http sending url
	 * @param string $url - http sending method
	 * @return object
	 */
	public function getRequestFromConsumer($consumer=null, $token=null, $method=null, $url=null, $parameters=null)
	{
		return OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $parameters);
	}
		
	/**
	 * Create OAuth token
	 *
	 * @param string $response - requset token
	 * @return object
	 */
	protected function createOauthToken($response)
	{
		if (isset($response['oauth_token']) && isset($response['oauth_token_secret'])) {
			return $this->getOAuthToken($response['oauth_token'], $response['oauth_token_secret']);
		}
		
		return null;
	}
	
	/**
	 * Create OAuth Consumer
	 *
	 * @return object
	 */
	private function createConsumer()
	{
		return new OAuthConsumer($this->key, $this->secret);
	}
	
	/**
	 * Call API with a GET request
	 *
	 * @param string $key 
	 * @param string $secret 
	 * @param string $url 
	 * @param string $getData 
	 * @param string $header 
	 * @param string $headers_only 
	 * @return Object
	 */
	public function get($key, $secret, $url, $getData=array(), $header=array(), $headers_only=false)
	{
		$accessToken = $this->getOAuthToken($key, $secret);
		$request = $this->prepareRequest($accessToken, 'GET', $url, $getData);
		$header = array
		(
			$request->to_header()
		);

		return $this->doGet($request->to_url(), $header, $headers_only);
	}
	
	/**
	 * Call API with a POST request
	 *
	 * @param string $key 
	 * @param string $secret 
	 * @param string $url 
	 * @param string $postData 
	 * @param string $header 
	 * @param string $headers_only 
	 * @return Object
	 */
	public function post($key, $secret, $url, $data=null, $header=null, $headers_only=false)
	{
		@$header or $header = array();
		$accessToken = $this->getOAuthToken($key, $secret);
		$request = $this->prepareRequest($accessToken, 'POST', $url);
		$header = array_merge(array($request->to_header()), $header);

		return $this->doPost($url, $data, $header, $headers_only);
	}
	
	public function delete($key, $secret, $url, $headers_only=false)
	{
		$accessToken = $this->getOAuthToken($key, $secret);
		$request = $this->prepareRequest($accessToken, 'DELETE', $url);
		$header = array
		(
			$request->to_header()
		);

		return RESTRequest::delete($url, $header, $headers_only);
	}

	/**
	 * Sending get method to http
	 *
	 * @param string $url 
	 * @param string $header 
	 * @param string $headers_only 
	 * @return Object
	 */
	private function doGet($url, $header=array(), $headers_only=false)
	{
		return RESTRequest::get($url, $header, $headers_only);
	}
	
	/**
	 * Sending get method to http
	 *
	 * @param string $url 
	 * @param string $data 
	 * @param string $header 
	 * @param string $headers_only 
	 * @return Object
	 */
	private function doPost($url, $data=null, $header=array(), $headers_only=false)
	{
		return RESTRequest::post($url, $data, $header, $headers_only);
	}
	
	/**
	 * Sending method to http
	 *
	 * @param string $request 
	 * @param string $header 
	 * @param string $headers_only 
	 * @return Object
	 */
	private function doRequest($request)
	{
		if ( $request->get_normalized_http_method()=='POST' )
		{
			$result = $this->doPost($this->url, $request->to_postdata());
		}
		else
		{
			$result = $this->doGet($request->to_url(), $header, $headers_only);
		}
		
		return $result;
	}
	
	/**
	 * Prepere Request
	 *
	 * @param string $token 
	 * @param string $httpMethod 
	 * @param string $url 
	 * @param string $parameters 
	 * @return void
	 */
	private function prepareRequest($token, $httpMethod, $url, $parameters=null)
	{
		$consumer = $this->createConsumer();
		$request = $this->getRequestFromConsumer($consumer, $token, $httpMethod, $url, $parameters);
		$request->sign_request($this->generalSignMethod('HMAC_SHA1'), $consumer, $token);
		
		return $request;
	}	
}

?>
