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
Este son funciones que reciben 2 parametros, $Request i $Response. Con estos se pueden hacer validaciones para saber si el usuario que hace la petición tiene permisos para ejecutarla o realizar alguna acción especifica cuando se quiera acceder a esa ruta. Estos por norma general deben retornar `true` o `false` para que el proceso pueda continuar.

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
**primary_key** Importante, definimos este campo como el primary key, con el que haremos referencia todas las operaciones hacia la base de datos. Debe ser el mismo primary key de la tabla en la base de datos.

**type.** Define el tipo de campo de la base de datos. Soportados los siguientes:

- string
- boolean
- timestamp
- int
- float 
- date. 
- abstract. Este campo se define como abstracto ya que no existe propiamente en la tabla sino que es una operación que se define en el `column_name`.

**column_name** Nos permite redefinir el nombre que usaremos en el objeto y que difiere al que hay en la base de datos.

**ignore** Le decimos al ORM que ese campo lo puede ignorar al hacer las operaciones hacia la base de datos.

**defaults_to** Permite definir un valor por defecto.


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
