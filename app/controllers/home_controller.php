<?php 


/**
* The first class
* Say Hello to World!
*/
class HomeController {

	/**
	 * Say Hello!
	 * 
	 * @param  Object $req 	Request
	 * @param  Object $res 	Response
	 * @return void
	 */
	public function hello($req, $res) {

		$res->render('home.php', array(
			hello => "Hello World!"
		));

	}
}

?>