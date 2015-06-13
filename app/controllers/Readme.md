## Controladors
Els controladors ens permeten processar les peticions HTTP de l'usuari i respondre amb un resultat.
Els controladors son objectes amb diferents *accions*, es a dir, funcions que es cridaràn segons com hem configurat les rutes.

Ex.
```
class ClientsController {
	
	// Recuperem tots els clients i renderitzem el resultat a una vista
	public function index($req, $res){
		
		// ... El nostre codi

		// Retornem el resultat i la vista a carregar
		return array(
			clients => $clients,
			view_file => '/views/clients.index.php'
		);
	}
}
```
La funció index() ha de retornar una llista de tots els clients que hi ha la base de dades. La programació donarà com a resultat una variable $clients on guardem un array de tots els clients.
Amb el **return** de la mateix funció renderitzarem la vista que ha de carregar i aquesta podrà fer servir la variable $clients per mostrar amb html el resultat.

> NOTA: Al controladors podem tenir la nostre llogica de negoci, pero en aquest framework recomanem utilizar la capa de business per les interaccions amb la base de daddes.


## Controladors + Business
La opció recomanada, i opcional, es crear una segona capa anomenada *business* aquesta capa serà l'encarregada de interactuar amb la base de dades i retornar resultats als controladors.
D'aquesta manera podem aillar més codi i reutilitzar-lo en tots els controladors que volguem.

ex.
```
class ClientsController {
	
	/**
	 * Funció que ha de retornar tots els llibres d'aquest clients
	 * @param  obj 		$req 	Request Object
	 * @param  obj 		$res 	Response Object
	 * @return mixed
	 */
	public function view_clients_books($req, $res){

		$userId = $req['get']['id'];

		// Agafem tota la informació de l'usuari
		$userBusiness = new UserBusiness();
		$user = $userBusiness->getById($userId);

		// Cridem a la capa business
		$booksBusiness = new BooksBusiness();
		$user_books = $booksBusiness->getByUser($userId);

		// retornem els resultats
		return array(
			user => $user, // informació del usuari
			books => $user_books // els seus llibres
		);

	}
}
```