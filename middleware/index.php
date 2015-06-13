<?php
/**
 * Arxiu de middleware
 * Tots aquestes funcions s'executaràn abans d'arribar a la logica de negoci, modificant el request i el response.
 * Aquestes funcio es criden expressament per l'usuari, aqui no s'hauria d'afegir
 * parts de codi que es repeteixn a totes les peticions http com podria ser un Log.
 *
 * Tot el que vulguis que s'executi durant la petició HTTP s'ha d'incloure a l'arxiu Bella.php
 * 
 * @author Marion Martínez <gameofender@gmail.com>
 */



/**
 * Bloqueja els usuaris que no siguin autentificats.
 * 
 * @param  array  &$req 	request object
 * @param  array  &$res 	response object
 * @return boolean       
 */
function isAuth(&$req, &$res){

	$auth = false;
	$currentUser = $req->session('currentUser');
	if(!empty($currentUser)) $auth = true;

	if(!$auth) $res->redirect('/login');
	else return $auth;
}

?>