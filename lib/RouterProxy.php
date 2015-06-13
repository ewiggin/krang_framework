<?php 
/**
* Router
*/
class Router
{
	
	var $url 				= '';
	var $available_methods 	= array('GET', 'POST', 'PUT', 'DELETE', 'FETCH');
	var $url_patterns		= array();

	function __construct() {
	}

	/**
	 * Recupera la URL
	 * @return [type] [description]
	 */
	public function getUrl() {
        return $this->url;
    }

    /**
     * Defineix la ruta
     * @param [type] $url [description]
     */
    public function setUrl($url) {
        $url = (string) $url;

        // make sure that the URL is suffixed with a forward slash
        /*if (substr($url, -1) !== '/') {
            $url .= '/';
        }*/
        if($url == "") $url = "/";

        $this->url = $url;
    }

	public function get($pattern, $target, $middle = '') {

		if(is_array($target)){
			$this->url_patterns['GET'][$pattern] = $target;	
		}
		else {
			$aux = explode('::', $target);
			$this->url_patterns['GET'][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}

		return $this;
	}

	public function xhr($pattern, $target, $middle = '') {
		if(is_array($target)){
			$this->url_patterns['XHR'][$pattern] = $target;	
		}
		else {
			$aux = explode('::', $target);
			$this->url_patterns['XHR'][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}

		return $this;
	}

	public function post($pattern, $target,  $middle = '') {
		if(is_array($target)){
			$this->url_patterns['POST'][$pattern] = $target;	
		}
		else {
			$aux = explode('::', $target);
			$this->url_patterns['POST'][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}

		return $this;
	}

	public function delete($pattern, $target,  $middle = '') {
		if(is_array($target)){
			$this->url_patterns['DELETE'][$pattern] = $target;	
		}
		else {
			$aux = explode('::', $target);
			$this->url_patterns['DELETE'][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}

		return $this;
	}

	public function put($pattern, $target,  $middle = '') {
		if(is_array($target)){
			$this->url_patterns['PUT'][$pattern] = $target;	
		}
		else {
			$aux = explode('::', $target);
			$this->url_patterns['PUT'][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}
	}

	public function fetch($pattern, $target,  $middle = '') {
		if(is_array($target)){
			$this->url_patterns['FETCH'][$pattern] = $target;	
		}
		else {
			$aux = explode('::', $target);
			$this->url_patterns['FETCH'][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}

		return $this;
	}

	public function all($pattern, $target, $middle = '') {

		if(is_array($target)){
			$target;	
		}
		else {
			$aux = explode('::', $target);
			$target = array(controller => $aux[0], action => $aux[1], middle => $middle);
		}

		foreach ($this->available_methods as $m) {
			$this->url_patterns[$m][$pattern] = $target;
		}

		return $this;
	}

	public function getRegex() {
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->url);
    }

    private function substituteFilter($matches) {
        if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }

        return "([\w-%]+)";
    }

	public function dispatch($url, &$request, &$response){

		$next = true;
		$this->setUrl($url);
		$route = $this->url_patterns[$request->method][$this->url];

		if(!empty($route)) {



			if(!empty($route['middle'])) $next = call_user_func_array($route['middle'], array(&$request, &$response));

			// TODO: fer que el middle només retorni bool
			if(is_array($next)) {
				$response->locals[$route['middle']] = $next;
				$next = $next['next'];
			}

			if($next){
				call_user_func_array(array(new $route['controller'], $route['action']), array(&$request, &$response));
			}
			else $response->status(500);
		}
		else $response->status(404); 
	}
}

?>