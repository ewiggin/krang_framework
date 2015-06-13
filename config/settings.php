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
 * Home view
 * 
 * Definim quina es la pagina principal per carregar-la quan no hi ha 
 * cap URL ni cap controlador.
 */
define(default_view, 'home');


/**
 * Generate Page information
 * 
 * Si tenim definides informació bàsica de les pagines a la base de dades la podem cridar quan vulguem.
 * Aquesta opció només serveix si segueixes un patró a la base de dades que funcioni amb aquesta funcionalitat.
 * La informació es desarà dins de la variable $page
 */
define(page_var, false);

/**
 * Error level
 * 
 * Defineix el nivell d'error que vols que apareixi per pantalla, pot no funcionar ja que a vegades
 * aquesta variable ve del php.ini i en la majoria de hostings es protegida.
 */
define(display_errors, E_ALL);

/**
 * Layout
 * 
 * Localització de la plantilla
 */
define(default_layout, 'views/layout/layout.php');

/**
 * PATHS
 * 
 * Views path
 * Defineix on son les vistes
 * per poder fer els includes
 */
define(VIEWS_PATH, 'views/');

/**
 * Error 404 path
 * 
 * Definexi on son el directori
 * on son els arxius d'error 404 i 500
 */
define(VIEWS_ERROR_PATH, 'views/error/');

/**
 * Upload Path
 * 
 * Defineix on estan guardats els recursos que s'han
 * pujat desde l'administració. 
 *
 * Imatges, Audios, PDF, etc.
 */
define(UPLOAD_PATH, 'gestion/uploads/');

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
define(ROUTES_FILE, 'config/routes.php');


?>