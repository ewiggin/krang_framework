## Krang Framework
The evil way to conquer internet.

This doc is **Spanish Documentation**, i need help to write in English...

### Empezando
Para empezar solamente necesitas saber dónde estan las configuraciones del proyecto.
Fácil, estan en el directorio `/config/`.

Desde este directorio accedemos a los archivos de configuración del sitio:
  - `database.php` > Configuración de la conexión a la base de datos.
  - `i18n.php` > Configuración de los idiomas y traducciones.
  - `settings.php` > Configuración general de todo el proyecto.
  - `/routes/` > Directorio de rutas.

#### Rutas
Krang funciona como cualquier otro framework, defines rutas que llaman a acciones de un controlador y este al final devuelve unos valores hacia las vistas.

Las rutas se encuentran en `/routes/` y lo puedes dividir en diferentes archivos o usar el `index.php`.

**¿Cómo definir una ruta?**
Para definir una ruta tenemos la ayuda del `$Router`, el cual llega instanciado i solo tenemos que usarlo.

```
// New route
$Router->get('/', 'HomeController::welcome')
      ->post('/login', 'LoginController::login_auth')
      ->get('/login', 'LoginController::login_form')
      ->all('/apps/', 'AppsController::index', array('isAuth'); // with middleware
```

Podemos usar las funciones: 
- get() Peticiones HTTP GET.
- post() Peticiones HTTP POST
- put() Peticiones HTTP PUT
- delete() Peticiones HTTP DELETE
- fetch() Peticiones HTTP FETCH
- all() Peticiones HTTP GET i POST

La ruta tiene que tener de forma **obligatoria**: La ruta (ej. '/route'), la referencia al controlador i la acción a ejecutar *separados por ::*  (ej. 'Controller::action').

De forma opcional podemos añadir middleware para validar o modificar aspectos de la petición HTTP. Este middleware se puede definir directamente con un string si es solo 1 elemento o un array. Si son varias validaciones, tienen que pasar todas para que se ejecute la acción de la ruta. En caso contrario, Krang Framework se encarga de mostrar una página de error 404.

#### Middleware
Se encuentra en `app/middleware/index.php`.
Estos son funciones que reciben 2 parametros, $Request i $Response. Con estos se pueden hacer validaciones para saber si el usuario que hace la petición tiene permisos para ejecutarla o realizar alguna acción especifica cuando se quiera acceder a esa ruta. Estos por norma general deben retornar `true` o `false` para que el proceso pueda continuar.

En caso contrario, se mostrará una página de error 500 o un 404.

Ejemplo:
```
/**
 * Bloquea los usuarios que no esten autentificados.
 * 
 * @param  array  $req 	request object
 * @param  array  $res 	response object
 * @return boolean       
 */
function isAuth($req, $res){

	$auth = false;
	$currentUser = $req->session('currentUser');
	if(!empty($currentUser)) $auth = true;

	return $auth;
}

```

#### Modelos
Fàcil, solo hay que crear una clase que **extienda la clase ActiveRecord** (el nombre ha sido copiado, si copiado, de como funciona RoR, al que tengo una gran admiración). Y de forma obligatoria debemos especificar las siguientes variables:

- `$table`. Nombre real de la tabla de la base de datos.
- `$attributes`. Array con todos los campos de la tabla especificada anteriormente.

###### Attributes
Para ver las diferentes opciones que podemos especificar con la variable $attributes un ejemplo:
```
class Users extends ActiveRecord {
	
	var $table = 'user';

	var $attributes = array(
		id => array(
			type => 'int(12)',
			primary_key => true,
			ignore => true
		),
		fk_concesionario => 'int(12)',
		nombre 	=> 'string',
		password => 'string(6)',
		puntos_excepcionales_disponibles => array(
			type => 'float',
			column_name => 'num_puntos_excepcionales_disponibles'
		),
		fecha_actualizacion => 'timestamp',
		activado => array(
			type => 'boolean',
			defaults_to => false
		),
		img_avatar => 'string',
		puntos_total_disponible => array(
			type => 'abstract',
			column_name => '(comercial.num_puntos_ventas_disponibles + comercial.num_puntos_excepcionales_disponibles)'
		)
	);
}

```
**primary_key** 
Importante, definimos este campo como el primary key, con el que haremos referencia todas las operaciones hacia la base de datos. Debe ser el mismo primary key de la tabla en la base de datos.

**type.** 
Define el tipo de campo de la base de datos. Soportados los siguientes:

- string
- boolean
- timestamp
- int
- float 
- date. 
- abstract. Este campo se define como abstracto ya que no existe propiamente en la tabla sino que es una operación que se define en el `column_name`.

**column_name**
Nos permite redefinir el nombre que usaremos en el objeto y que difiere al que hay en la base de datos.

**ignore** 
Le decimos al ORM que ese campo lo puede ignorar al hacer las operaciones hacia la base de datos.

**defaults_to** 
Permite definir un valor por defecto.


Una vez definido el modelo, podemos llamarlo en cualquier función o controlador usando el activerecord para recuperar el objeto de la base de datos:
```
// UserController.php
function view($req, $res){
  $Users = new Users(); // ActiveRecord 
  $myUser = $Users->find(23)->getObject();
}
```

##### Relaciones
En el modelo también podemos especificiar las relaciones que tiene con otros modelos de la misma base de datos, de momento las relaciones soportadas son:

- belongs_to
- has_one
- has_many
- has_and_belongs_to_many

```
////////////////////////////////////////
// User.php
var $has_many = array(
	"Apps" => "user_fk"
);
var $has_one = array(
	"Token" => "user_fk"
);

///////////////////////////////////////
// Apps.php and Token.php
var $belongs_to = array(
	"Users" => "user_fk"
);

//////////////////////////////////////
// Other (N-M)
var $has_and_belongs_to_many = array(
	'Users' => array(
		middle => 'test_intermedia',
		via => 'fk_concesionario'
	)
);

```

#### Controladores
Los controladores estan definidos por defecto dentro del directorio `/app/controllers` y son clases cuyas funciones son las acciones que definimos en las rutas. El nombre ha de ser el mismo. Todos de forma obligatoria reciben 2 parametros: $request i $response y son los que utilizamos para después renderizar las vistas.

Ejemplo:
```
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

		$nombre = "Krang";

		$res->render('home.php', array(
			msg => $nombre
		));
	}
}
```

#### Vistas
Las vistas estan separadas por archivos y son dependientes de una plantilla central. Si desde el controlador no se especifica otra plantilla se usará la que hay definida en la configuración por defecto. Las plantillas pueden utilizar las variables que se adjuntan al render de cada acción, separando de forma efectiva logica de negocio con presentación.

##### Plantillas
Por defecto la plantilla que se especifica en la configuración de `settings.php` es la que se usara para renderizar las vistas, pero esta puede cambiarse solamente pasando como parametro la variable **layout** 

```
$res->render("view.php", array(
	layout => "other_layout.php"
));
```

#### Request & Response
Las variables que utilizamos en el middleware y en todos los controladores nos permiten tener el control de las cabeceras http y como devolvemos el resultado.

`function action_name($req, $res){}`

> Request
>
> Todo lo que tiene que ver con la petición HTTP.



Funciones que puedes utilizar:

**Paremtros de entrada**
```
//Recupera el parametro GET
->params['GET']['nombre_param'];

//Recupera todos los parametros GET
->params['GET']; 	

//Recupera los parametros POST
->params['POST']; 		
```

**Sesiones**
```
//Recupera una sesión guardada.
->session($id); 	

//Guardas un valor dentro de una sesión.
->session($id, $value); 

//Destruye la sesión que especificamos por parametro.
->session_destroy($id); 
```

**Headers http**
```
// Recupera cualquier parametro de las cabeceras HTTP.
->get($key); 		

// Recupera el metodo HTTP (GET, POST, XHR, ETC.)
->getRequestMethod(); 	

// Devuelve TRUE | FALSE si els servidor acepta o no el contenido.
->accepts($type); 	

// Ip del cliente que hace la petición
->ip; 			

// Devuelve el PATH_INFO
->path; 		

// Devuelve la URI que ha echo la petición.
->originalUrl; 	

// Nombre del host
->hostname; 		

// Array de cookies
->cookies;		
```

> Response
>
> Variable para responder a la petición, es la que utilizamos cuando hemos acabado con la logica del controlador.

Funciones:
```
<?php
// Crea i guarda una cookie
->cookie($name, $value, $options); 

// Modifica las cabeceras para que la respuesta sea un archivo descargable
->download($path, $file_name); 	

// La respuesta no renderiza la vista, es un archivo JSON.
->json($value); 

// Hace una redirección por cabecera "Location: $path";
->location($path); 

// Alias de location()
->redirect($path); 

// Renderiza la vista y guarda los parametros para que puedan usar por esta.
->render($view, $values); 

// Genera un mensaje flash que se puede utilizar en la plantilla
->flash($type, $value); 

// Identifica el HTTP STATUS de la respuesta
->status($code); 

// Printa por pantalla, sin cargar ningúna plantilla, el valor pasado por parametro
->send($body); 

// Setea la cabecera con un header("$clave : $valor");
->set($field, $value); 

// Identifica el tipo de Content-Type de respuesta
->type($value); 

?>
```



### ActiveRecord
Es el encargado de lidiar entre los modelos y las peticiones a la base de datos.
Los siguientes metodos pueden usarse:

Para recuperar registros
- **one($id)** Recupera 1 registro con ese ID.
- **find($ids)** Recupera registros segun los id, acepta array y integer.
- **findBy($key, $value)** Recupera registro segun field > value `ej. ->findBy("nombre", "Mario")`
- **order($sql_string)** Setea el orden de la consulta. `ej. ->order("nombre asc");`
- **random()** Recupera 1 registro random
- **last($limit)** Recuperamos el/los úiltimo/s registro/s de la base de datos, ordenado por id;
- **all()** Recupera todos los registros
- **group($sql_string)** Setea el groupby de la consulta.
- **between($first, $last)** Recupera todos los registros entre 2 limites
- **join($sql_string)** Añade LEFT JOIN para la consulta final
- **form($string)** Añade tablas a la consulta
 
Para ejecutar la consulta:
- **getObject()** Recupera los datos como Objetos del Modelo especificado.
- **getArray()** Recupera los datos como un array asociativo.

Nuevos registros i modificaciones de objetos:
- **create($array)** Crea una instancia del modelo según el array asociativo que le pasamos por parametro.
- **remove($object_model)** Elimina el objeto que pasamos por referencia.
- **save($object_model)** Guarda el objeto en la base de datos. Inserta o Actualiza los datos.
- **removeAll($objects_array)** Elimina todos los objetos instanciados de la base de datos.

SQL Nativa
- **raw_query($strSQL)** Ejecuta una Query SQL nativa y devuelve los resultados como un Array asociativo


### i18n y traducciones
Para usar las traducciones solo debemos activar los idiomas en la configuración: `/config/i18n.php`.
Estas traduccones se generan automáticamente y se guardan en forma de archivos separados por codigo de idioma. Estos archivos disponen de un array asociativo con la clave (texto original) y un valor (traduccion para ese idioma).

En las vistas se puede utilizar el objeto **i18n** para usar el multiidioma.
```
<p><?=$i18n->_("Bienvenido");?></p>
``` 

En los controladores debe llamarse el objeto de traducciones con `global`.
```
function welcome_message($req, $res){
	global $i18n;
	$i18n->_("Bienvenido");
	// code ...
}
```

Con la función _() devolvemos la traducción de la cadena de forma rápida y eficaz. 

> Si en la configuración tenemos activada la directiva: define(i18n_translate, true); esta nos genera automaticamente los archivos en el path que le hemos especificado. 


