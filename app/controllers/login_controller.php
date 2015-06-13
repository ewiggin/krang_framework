<?php 


/**
* Login Controller
*/
class LoginController {
	
	/**
	 * Login 
	 * 
	 * @param  Object $req 	Request
	 * @param  Object $res 	Response
	 * @return void
	 */
	function login($req, $res) {
		
		// Code for login
		
		$res->render('login.php', array(
			body_class => 'login',
		));
	}


	/**
	 * Sortir.
	 * Simplement destrueix la sessió i fa una redirecció 
	 * a la ruta principal /.
	 * 
	 * @param  Object $req 	Request
	 * @param  Object $res 	Response
	 * @return void
	 */
	public function logout($req, $res) {
		
		$req->session_destroy('currentUser');
		$res->redirect('/');

	}
	
}

?>