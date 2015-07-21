<?php 
/**
 * Base URL
 * 
 * Defineix quina es la URL base per fer funcionar correctament
 * les rutes de l'aplicació.
 * Per defecte, hauria de estar a l'arrel del web. En cas contrari es pot 
 * definir quina es la url base d'on partiran totes les urls internes.
 * 
 * define(base_url, '/');
 */
define(base_url, '/');

/**
 * Enviroment Var
 * 'dev' => development 
 * 'pro' => production
 *
 * Afecta a:
 * =========
 * CONTROLADORS I MODELS
 * Si aquesta variable te valor 'dev', cada petició HTTP comproba
 * els arxius que hi ha a les carpetes controladors i models, i els 
 * inclou un a un. En cas contrari, si tenim la variable amb valor 'pro' aquests
 * directoris es miren un sol cop i la resta de peticions HTTP es guarden a 
 * sessió. 
 *
 * TODO: La performance estaria molt millor si fem un arxiu amb un array de tots els controladors i models que farem servir.
 */
define(ENV_VAR, 'dev');



/**
 * Error level
 * 
 * Defineix el nivell d'error que vols que apareixi per pantalla, pot no funcionar ja que a vegades
 * aquesta variable ve del php.ini i en la majoria de hostings es protegida.
 */
define(display_errors, 0);

/**
 * Layout
 * 
 * Localització de la plantilla
 */
define(layaut_path, 'resources/views/layout/');
define(default_layout, layaut_path.'default.php');

/**
 * PATHS
 * 
 * Views path
 * Defineix on son les vistes
 * per poder fer els includes
 */
define(VIEWS_PATH, 'resources/views/');

/**
 * Error 404 path
 * 
 * Definexi on son el directori
 * on son els arxius d'error 404 i 500
 */
define(VIEWS_ERROR_PATH, 'resources/views/error/');

/**
 * Upload Path
 * 
 * Defineix on estan guardats els recursos que s'han
 * pujat desde l'administració. 
 *
 * Imatges, Audios, PDF, etc.
 */
define(UPLOAD_PATH, 'uploads/');

/**
 * Controllers
 * 
 * Defineix el path on son els 
 * controladors
 */
define(CONTROLLERS_PATH, 'app/controllers/');


/**
 * Models
 * 
 * Defineix el path on son els 
 * models
 */
define(MODELS_PATH, 'app/models/');

/**
 * Middleware
 * Definim el path on sera el middleware
 */
define(MIDDLEWARE_PATH, 'app/middleware/');

/**
 * Business Path
 */
define(BUSINESS_PATH, 'app/business/');

/**
 * Helpers
 */
define(HELPERS_PATH, 'app/helpers/');


/**
 * Routes
 * 
 * Indica si les rutes estan definides a través
 * de la base dades o es fa servir un arxiu de rutes
 * que no passa per la base de dades.
 *
 * ROUTES_DB true => Les rutes es defineixen a la base de dades.
 * ROUTES_DB false => Les rutes son definides amb un arxiu.
 * ROUTES_FILE => Funciona si la opció anterior es = False i defineix on son les rutes.
 */
define(ROUTES_DB, false);
define(ROUTES_PATH, 'config/routes/index.php');


?>