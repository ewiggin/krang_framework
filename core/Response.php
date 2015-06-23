<?php 
/**
* Response
*
* The res object represents the HTTP response that an Express app sends when it gets an HTTP request.
* In this documentation and by convention, the object is always referred to as res (and the HTTP request is req) but its actual name is determined by the parameters to the callback function in which you’re working.
*/
class Response
{
	
	// Boolean property that indicates if the app sent HTTP headers for the response.
	var $headersSent 	= '';

	// An object that contains response local variables scoped to the request, and therefore available only to the view(s) rendered during that request / response cycle (if any). Otherwise, this property is identical to app.locals.
	// This property is useful for exposing request-level information such as the request path name, authenticated user, user settings, and so on.
	var $locals 		= array();
	var $charset 		= 'utf-8';

	// view_file
	var $view 			= '';
	var $contentType 	= "text/html";
	var $status 		= 200;

	// flash messages
	var $flash 			= '';

	function __construct() {
		# code...
	}

	/**
	 * Sets cookie name to value. The value parameter may be a string or object converted to JSON.
     * The options parameter is an object that can have the following properties.
     * 
	 * @param  [type] $name  [description]
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public function cookie($name, $value, $options = null) {
		// bool setcookie ( string $name [, string $value [, int $expire = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httponly = false ]]]]]] )
		return setcookie($name, $value, $options['expire'], $options['path'], $options['domain'], $options['secure']);
	}


	/**
	 * Clears the cookie specified by name. For details about the options object, see res.cookie().
	 * @param  [type] $name    [description]
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	public function clearCookie($name, $options = null) {
		# code...
	}

	/**
	 * Transfers the file at path as an “attachment”. 
	 * Typically, browsers will prompt the user for download. By default, the Content-Disposition header “filename=” parameter is path (this typically appears in the brower dialog). Override this default with the filename parameter.
	 *
	 * When an error ocurrs or transfer is complete, the method calls the optional callback function fn. This method uses res.sendFile() to transfer the file.
	 * 
	 * @param  [type] $path     [description]
	 * @param  string $filename [description]
	 * @return [type]           [description]
	 */
	public function download($path, $filename = '', $errorHandler = '') {
		
		try {
		
			if(!empty($filename)) $path = $filename;

			// Headers for an download:
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$path.'"'); 
			header('Content-Transfer-Encoding: binary');

			// load the file to send:
			readfile($path);

		} catch (Exception $e) {
			call_user_func($errorHandler, $e);
		}
	}


	/**
	 * Sends a JSON response. This method is identical to res.send() with an object or array as the parameter. 
	 * However, you can use it to convert other values to JSON, such as null, and undefined. (although these are technically not valid JSON).
	 * 
	 * @param  [type] $body [description]
	 * @return [type]       [description]
	 */
	public function json($body = null) {
		$this->type('json');
		echo json_encode($body);
	}


	/**
	 * Sets the response Location HTTP header field based on the specified path parameter
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	public function location($path) {
		header("Location: ".$path);
		exit;
	}

	/**
	 * Alias location method
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	public function redirect($path) {
		$this->location($path);
	}



	/**
	 * Renders a view and sends the rendered HTML string to the client. Optional parameters:
	 *  - locals, an object whose properties define local variables for the view.
	 *  
	 * @param  [type] $view   [description]
	 * @param  [type] $locals [description]
	 * @return [type]         [description]
	 */
	public function render($view, $locals = null) {
		
		$this->view = $view;

		try {
			
			if($locals != null && is_array($locals)) $this->locals = $locals;
			else if($locals != null) throw new Exception("Locals always to be Array()", 1);	

		} catch (Exception $e) {
			echo $e->getMessage();
		}

		return $this;
	}

	/**
	 * Permet enviar una variable global a la vista.
	 * 
	 * @param  String $key   	Clasificació del missatge ex.Error, Warning, etc.
	 * @param  String $value 	Missatge
	 */
	public function flash($key, $value = '') {
		if($value == '') $this->flash = $key;	
		else $this->flash[$key] = $value;

		return $this;
	}

	/**
	 * Set the response HTTP status code to statusCode and send its string representation as the response body.
	 *
	 * res.status(200); // equivalent to res.status(200).send('OK')
     * res.status(403); // equivalent to res.status(403).send('Forbidden')
     * res.status(404); // equivalent to res.status(404).send('Not Found')
     * res.status(500); // equivalent to res.status(500).send('Internal Server Error')
	 * 
	 * @param  [type] $code [description]
	 * @return [type]             [description]
	 */
	public function status($code) {

		$status_codes = array(
			200 => 'OK',
			404 => 'Not Found',
			403 => 'Forbidden',
			301 => 'Moved Permanently',
			500 => 'Internal Server Error'
		);

		$this->status = $code;
		header('HTTP/1.1 '.$code.' '.$status_codes[$code]);


		return $this;
	}


	/**
	 * Send a content of response
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public function send($value) {
		echo $value;
		exit();
	}

	/**
	 * Sets the response’s HTTP header field to value. To set multiple fields at once, pass an object as the parameter.
	 * @param [type] $field [description]
	 * @param [type] $value [description]
	 */
	public function set($field, $value) {
		header("{$field}: {$value}");
	}


	/**
	 * Sets the Content-Type HTTP header to the MIME type as determined by mime.lookup() for the specified type. 
	 * If type contains the “/” character, then it sets the Content-Type to type.
	 *
	 * res.type('.html');              // => 'text/html'
     * res.type('html');               // => 'text/html'
     * res.type('json');               // => 'application/json'
     * res.type('application/json');   // => 'application/json'
     * res.type('png');                // => image/png:
	 * 
	 * @param  [type] $value [description]
	 * @return [type]        [description]
	 */
	public function type($value) {

		$types = array(
			'html' 	=> 'text/html; charset='.$this->charset,
			'jpg' 	=> 'image/jpeg',
			'png'	=> 'image/png',
			'gif'	=> 'image/gif',
			'plain'	=> 'text/plain',
			'json'	=> 'application/json',
			'zip'	=> 'application/zip',
			'mpeg'	=> 'audio/mpeg',
			'pdf'	=> 'application/pdf'
		);

		try {
			// get a contentype
			$content_type = $types[$value];

			// set content type:	
			if(!empty($content_type)) {
				$this->contentType = $content_type;
				header('Content-Type: '.$types[$value]); 
			}
			else throw new Exception("Content-type is not accepted", 1);	

		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}

?>