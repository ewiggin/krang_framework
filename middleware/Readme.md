# Middleware
### No implementat en aquesta versió!!
Aquesta carpeta ens serveix per tenir organitzats tots els arxius php que compleixen una funció de middleware, es a dir, programació que es entre el Request i la Resposta.
Al fer una petició HTTP el sistema intenta esbrinar quin Controlador i acció ha de solicitar la resposta. Si volem que abans de produirse aquesta comunicació s'executin una serie 
de processos o afegim informació a aquesta petició podem cridar el middleware. Si l'execució d'aquest retorna un array(next => true) la petició pot continuar cap al controlador, 
en cas contrari es produeix un error controlat.

> Exemple
> Definim al enrutador la url /clients/
> ```
> $Router->get("/clients/", array(controller => "ClientsController", action => "index", middle => "is_Authorized"))
> ```
> Com pots veure a la informació de la ruta definim un key anomenat *middle* aquest pot ser un **string o un array** i defineix quins 
> son els arxius que s'han d'executar abans de realitzar la petició. En aquest cas, is_Authorized es el nom del fitxer d'aquesta carpeta: /middleware/is_Authorized.php Aquest comproba que el 
> usuari que fa la petició es un usuari amb sessió.
> 
> 1. Usuari fa una petició GET cap a /clients/
> 2. Aquesta petició primer passa per is_Authorized
> 3. Si l'usuari esta loguejat el resultat de retorn es array(next => true) en cas contrari array(next => false)
> 4. Tot es OK continuem i entrem a la lògica del controlador.
> 5. Printem un resultat segons la vista.
> 

Aquesta es una menera de tenir netament organitzat el projecte, i poder modificar les variables e informació abans d'entrar a la llogica de negoci.
NOTA: Els arxius middle s'executen amb l'ordre que s'indica a la ruta.
