<?php 
/**
* Router
*
*	$Router
*		->get('/app/')
*		->get('/app/:id')
*
*
* 
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
     * 
     * @param [type] $url [description]
     */
    public function setUrl($url) {
        $url = (string) $url;
        if($url == "") $url = "/";
        $this->url = $url;
    }

    /**
     * Afegeix els patrons en un array de patrons 
     * que posteriorment es compararan amb la URL de navegació
     * 
     * @param String $method  Metode de Petició (GET | POST | FETCH ... )
     * @param String $pattern Patró per identificar la ruta
     * @param String $target  Quin controlador i acció executar
     * @param string $middle  Middleware abans d'executar el codi del controlador
     */
    public function add($method, $pattern, $target, $middle = ''){

    	if(is_array($target)) $this->url_patterns[$method][$pattern] = $target;
    	else {
    		$aux = explode('::', $target);
			$this->url_patterns[$method][$pattern] = array(controller => $aux[0], action => $aux[1], middle => $middle);
    	}
    }



	public function get($pattern, $target, $middle = '') {
		$this->add("GET", $pattern, $target, $middle);
		return $this;
	}

	public function xhr($pattern, $target, $middle = '') {
		$this->add("GET", $pattern, $target, $middle);
		return $this;
	}

	public function post($pattern, $target,  $middle = '') {
		$this->add("POST", $pattern, $target, $middle);

		return $this;
	}

	public function delete($pattern, $target,  $middle = '') {
		$this->add("DELETE", $pattern, $target, $middle);
		return $this;
	}

	public function put($pattern, $target,  $middle = '') {
		$this->add("PUT", $pattern, $target, $middle);
		return $this;
	}

	public function fetch($pattern, $target,  $middle = '') {
		$this->add("FETCH", $pattern, $target, $middle);
		return $this;
	}

	public function all($pattern, $target, $middle = '') {
		foreach ($this->available_methods as $m) {
			$this->add($m, $pattern, $target, $middle);
		}
		return $this;
	}

    /**
     * Executa la funció del controlador corresponent a la ruta.
     * 
     * @param  String $url       HTTP URL que fa la petició
     * @param  Object $request  Request Object
     * @param  Object $response Response Object
     * @return void
     */
	public function dispatch($url, &$request, &$response){
		// Seteja la URL que volem cercar 
		$this->setUrl($url);
		// Inicialitzem variables
		$route = array();
		$i = 0;
		// Agafem tots els patrons del method que s'utilitza per fer la petció HTTP (GET | POST | ... )
		$patterns = array_keys($this->url_patterns[$request->method]);
		$nroutes = sizeof($this->url_patterns[$request->method]);
		// Cerquem fins trobar coincidencies amb els nostres patrons
		while(!$trobat && $i < $nroutes) {
			
			$pattern = new Pattern(array_shift($patterns));
			if($pattern->haveRegEx()) {
				$trobat = preg_match("/^".$pattern->to_String()."$/", $this->url);
			}
			else $trobat = $pattern->to_String() == $this->url;
			
			$i++;
		}



		// Extreiem de la URL els parametres que hem definit al patró
		// els desem dins de l'objecte Request.
		if($trobat && $pattern->haveRegEx()) {
			$params = $pattern->getRegExParams($url);	
			foreach ($params as $key => $value) {
				$request->params['GET'][$key] = $value;
			}
		}


		// Set the route if founded
		if($trobat) $route = $this->url_patterns[$request->method][$pattern->original];

		// Call Controller and Action, if $route not defined show 404
		$this->call($route, $request, $response);
	}


	/**
	 * Executa el controlador i l'acció que pertoquen a la RUTA.
	 * 
	 * @param  Array  $route    
     * @param  Object $request  Request Object
     * @param  Object $response Response Object
     * @return void
	 */
	private function call($route, &$request, &$response) {

		if(!empty($route)) {
			$next = true;
			if(!empty($route['middle'])){
				$all_middleware = array();
				if(is_array($route['middle'])) $all_middleware = $route['middle'];
				else $all_middleware[] = $route['middle'];

				// Recorregut de tots els middleware
				$i = 0;
				while ($next && $i < sizeof($all_middleware)) {
					$middle = $all_middleware[$i];
					$middle_result = call_user_func_array($middle, array(&$request, &$response));
					
					if(is_array($middle_result)) {
						$response->locals[$middle] = $middle_result;
						$next = $middle_result['next'];
					}
					else $next = $middle_result;
					$i++;	
				}
			}

			if($next){
				call_user_func_array(array(new $route['controller'], $route['action']), array(&$request, &$response));
			}
			else $response->status(500);
		}
		else $response->status(404); 
	}
}




/**
* Patró de ruta
*/
class Pattern
{
	var $original 	= "";
	var $regex 		= "";
	var $params 	= array();

	function __construct($pattern) {
		$this->original = $pattern;
		$this->regex = $this->getRegex();
	}

	/**
	 * Retorna l'expressió que s'ha de comparar amb 
	 * la ruta per saber si es vol cridar al controlador es definit 
	 * es dins d'aquest patró.
	 * 
	 * @return [type] [description]
	 */
	public function to_String() {
		
	
		if($this->haveRegEx()) $route = $this->escapeRegEx($this->regex);
		else $route = $this->original;

		return $route;
	}

	/**
	 * Escapa els / del patró per poder utilitzar l'expressió regular
	 * \/routa\/cap\/algun\/lloc
	 * 
	 * @param  [type] $regex [description]
	 * @return [type]        [description]
	 */
	private function escapeRegEx($regex) {
    	return eregi_replace('/', "\/", $regex);
	}

	/**
	 * Extreu els valors de la url i que hem definit al patró de la ruta
	 * aquest genera un array de clau valor.
	 *
	 *	Route: /apps/view/:id-:name
	 *	URL: /apps/view/88-Genius
	 *
	 *  result: Array ( [id] => 88 [name] => Genius )
	 * 
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function getRegExParams($url) {
		$matches = array();
		$this->regex = $this->escapeRegEx($this->regex);
		$nMatches = preg_match("/".$this->regex."/", $url, $matches);
		
		$i = 1;
		foreach ($this->params as $key => $value) {
			$get_vars[$key] = $matches[$i];
			$i++;
		}

		return $get_vars;
	}

	/**
	 * Substitueix el patró definit per l'usuari amb :nom_var 
	 * per expresions regulars funcionals.
	 * 
	 * @return [type] [description]
	 */
	public function getRegex() {
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->original);
    }

    /**
     * Funció privada que es crida per cada match del 
     * preg_replace_callback()
     * 
     * @param  [type] $matches [description]
     * @return [type]          [description]
     */
    private function substituteFilter($matches) {
    	// Inicialitzem les variables que espera l'usuari per aquest patró
    	$this->params[$matches[1]] = "";
    	
        if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }
        
        return "([\w ]+)"; // Accepta espais
    }

	/**
	 * Recupera els parametres d'una URL 
	 * @return [type] [description]
	 */
	public function getParams($url) {
		return array();
	}

	/**
	 * Diu si un patró te una expressió regular.
	 * @return [type] [description]
	 */
	public function haveRegEx() {

		$have = false;
		if($this->original != "/") $have = preg_match("/:(\w+)/", $this->original);

		return $have;
	}
}
?>