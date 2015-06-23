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
		// dades de pagament que ens arriben per POST
		

		$res->render('home.php', array(
			message => $i18n->_("Welcome!")
		));

	}


}

?>