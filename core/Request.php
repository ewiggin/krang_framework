<?php
/**
 * Request class
 */

class Request
{	

	var $baseUrl;
	var $ip 			= '';
	var $hostname 		= '';
	var $originalUrl 	= '';
	var $path 			= '';
	var $protocol 		= '';
	var $xhr 			= false;
	var $method 		= 'GET';

	// Params GET, POST, FILE, etc.
	var $params 		= array();
	//
	var $headers 		= array();
	//
	var $cookies 		= array();
	var $session 		= array();

	function __construct() {
		// get all headers
		$this->headers 		= apache_request_headers();
		// request method
		$this->method 		= $_SERVER['REQUEST_METHOD'];
		// request params
		$this->ip 			= $_SERVER['REMOTE_ADDR'];
		$this->hostname 	= $_SERVER['HTTP_HOST'];
		$this->originalUrl 	= $_SERVER['REQUEST_URI'];
		$this->path 		= $_SERVER['PATH_INFO'];
		$this->protocol 	= $_SERVER['SERVER_PROTOCOL'];
		// is XHR method
		$this->xhr 			= (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
		// cookies
		$this->cookies 		= $_COOKIE; 
		// sessions
		$this->session 		= $_SESSION;
		// params 
		$this->params = array(
			'GET' => $_GET,
			'POST' => $_POST,
			'FILES' => $_FILES
		);
	}

	/**
	 * Check on headers if the content is acceptable
	 * 
	 * @param  string $types 
	 * @return boolean
	 */
	public function accepts($types = 'html') {
		return preg_match($types, $_SERVER['HTTP_ACCEPT']);
	}

	/**
	 * Sets and Gets sessions variable.
	 * 
	 * @param  String 	$key   	Key of session variable
	 * @param  mixed 	$value 	Value stored with this key
	 *
	 * @return  mixed or null
	 */
	public function session($key, $value = null) {
		if($value == null) return $this->session[$key];
		else $_SESSION[$key] = $value;
	}

	/**
	 * Destroy a session!
	 * 
	 * @param  String 	$key 	Key of session var
	 */
	public function session_destroy($key) {
		session_destroy();
		$_SESSION[$key] = null;
	}

	/**
	 * Returns the specified HTTP request header field (case-insensitive match). The Referrer and Referer fields are interchangeable.
	 * 
	 * @param  String $key 
	 * @return String
	 *
	 * req.get('Content-Type');
	 *	// => "text/plain"
	 *
	 *	req.get('content-type');
	 *	// => "text/plain"
	 *
	 *	req.get('Something');
	 *	// => undefined
	 */
	public function get($key) {
		return $this->headers[$key];
	}

	/**
	 * Returns true if the incoming request’s “Content-Type” HTTP header field matches the MIME type specified by the type parameter. Returns false otherwise.
	 * 
	 * @param  string  $key [description]
	 * @return boolean        [description]
	 */
	public function is($key) {
		return $this->headers['Content-Type'] == $key;
	}


}



// TODO: Ficar en un altre lloc
if( !function_exists('apache_request_headers') ) {

/**
 * Return all of HTTP headers
 * 
 * @return Array
 */
function apache_request_headers() {
  $arh = array();
  $rx_http = '/\AHTTP_/';
  foreach($_SERVER as $key => $val) {
    if( preg_match($rx_http, $key) ) {
      $arh_key = preg_replace($rx_http, '', $key);
      $rx_matches = array();
      // do some nasty string manipulations to restore the original letter case
      // this should work in most cases
      $rx_matches = explode('_', $arh_key);
      if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
        foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
        $arh_key = implode('-', $rx_matches);
      }
      $arh[$arh_key] = $val;
    }
  }
  return( $arh );
}
///
}
///




?>