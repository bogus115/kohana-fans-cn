<?php defined('SYSPATH') or die('No direct script access.');

/**
 * OAuth library
 *
 * @package libraries
 * @author icyleaf
 * @license http://www.opensource.org/licenses/bsd-license.php
 * 
 */
class OAuth_Core {
	
	public $key = null;
	public $secret = null;
	private $url = null;
	
	public function __construct($key=null, $secret=null)
	{
		if ( empty($key) || empty($secret) )
			throw new Kohana_User_Exception('Missing parameter(s)', '$key or $secret parameters is empty.');
	
		$this->key = $key;
		$this->secret = $secret;
		include Kohana::find_file('vender', 'OAuth', true);
	}
	
	/**
	 * Call API with a GET request
	 */
	public function get($key, $secret, $url, $getData = array()) {
		$accessToken = new OAuthToken($key, $secret);
		$request = $this->prepareRequest($accessToken, 'GET', $url, $getData);

		return $this->doGet($request->to_url());
	}
	
	public function getAccessToken($accessTokenURL, $requestToken, $httpMethod = 'POST', $parameters = array()) {
		$this->url = $accessTokenURL;
		$request = $this->prepareRequest($requestToken, $httpMethod, $accessTokenURL, $parameters);
		
		return $this->doRequest($request);
	}
	
	public function getRequestToken($requestTokenURL, $httpMethod = 'POST', $parameters = array())
	{
		$this->url = $requestTokenURL;
		$request = $this->prepareRequest(null, $httpMethod, $requestTokenURL, $parameters);
		
		return $this->doRequest($request);
	}
	
	protected function createOauthToken($response) {
		if (isset($response['oauth_token']) && isset($response['oauth_token_secret'])) {
			return new OAuthToken($response['oauth_token'], $response['oauth_token_secret']);
		}
		
		return null;
	}
	
	/**
	 * Call API with a POST request
	 */
	public function post($key, $secret, $url, $postData = array()) {
		$accessToken = new OAuthToken($key, $secret);
		$request = $this->prepareRequest($accessToken, 'POST', $url, $postData);
		
		return $this->doPost($url, $request->to_postdata());
	}
	
	private function createConsumer() {
		return new OAuthConsumer($this->key, $this->secret);
	}

	private function doGet($url) {
		return Curl::get($url);
	}
	
	private function doPost($url, $data) {
		return Curl::post($url, $data);
	}
	
	private function doRequest($request) {
		if ($request->get_normalized_http_method() == 'POST') {
			$data = $this->doPost($this->url, $request->to_postdata());
		} else {
			$data = $this->doGet($request->to_url());
		}
		
		return $data;
		
		$response = array();
		parse_str($data, $response);

		return $this->createOauthToken($response);
	}
	
	private function prepareRequest($token, $httpMethod, $url, $parameters) {
		$consumer = $this->createConsumer();
		$request = OAuthRequest::from_consumer_and_token($consumer, $token, $httpMethod, $url, $parameters);
		$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $token);
		
		return $request;
	}
}

?>