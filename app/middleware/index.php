<?php
/**
 * Arxiu de middleware
 * Tots aquestes funcions s'executaràn abans d'arribar a la logica de negoci, modificant el request i el response.
 * Aquestes funcio es criden expressament per l'usuari, aqui no s'hauria d'afegir
 * parts de codi que es repeteixn a totes les peticions http com podria ser un Log.
 *
 * Tot el que vulguis que s'executi durant la petició HTTP s'ha d'incloure a l'arxiu Bella.php
 * 
 * @author Marion Martínez <mario@javajan.com>
 */



/**
 * Bloqueja els usuaris que no siguin autentificats.
 * 
 * @param  array  &$req 	request object
 * @param  array  &$res 	response object
 * @return boolean       
 */
function isAuth($req, $res){

	$auth = false;
	if(!empty($req->session['user'])) $auth = true;
	
	return $auth;
}


/**
 * Comproba si el usuari que fa la petició te 
 * rol `admin`
 * 
 * @param  obj  $req 
 * @param  obj  $res 
 * @return boolean 
 */
function isAdmin($req, $res){

	$next = false;

	if(isAuth() && $req->session['user']['role'] === "admin") $next = true;
	return $next;
}
?>