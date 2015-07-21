<?php
/**
* The first class
* Say Hello to World!
*/
class HomeController {

	/**
	 * Genera un formulari amb totes les dades de pagament i la compra,
	 * quan s'executa crida a un servei SOAP.
	 *
	 * @param  object $req Request Object
	 * @param  object $res Response Object
	 * @return mixed
	 */
	public function hello($req, $res) {
		
		// traduccions
		global $i18n;
		$name = $req->params['GET']['name'];

		$res->render('home.php', array(
			message => $name." ".$i18n->_("you can dominate the Internet!")
		));

	}


}

?>