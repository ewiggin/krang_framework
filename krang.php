<?php
/**
 * Krang FRAMEWORK
 * "Coding evil apps in a easy way"
 * 
 * * * * * * * * * * * * * * * * * *
 * 
 * Aquest framework s'ha creat per motiu d'agilitzar de forma ordenada i senzilla la programació de llocs web
 * amb poces necessitats tecniques. No trobaràs grans classes, funcions d'eficiencia renderitzant subrutines amb C. 
 * Aquest es un framework per programadors novells i amb ganes d'apendre i desenvolupar amb la metodología (M)VC, pots 
 * modificar tot el que vulguis per aquest projecte, fins i tot comercialitzar aquest utilitzant aquest framework.
 *
 * En aquest arxiu pots veure com es posa en marxa aquesta aplicació i com va alimentantse de tots els arxius de configuració,
 * rutes i altres components. No hi ha més.
 * 
 * v.0.1
 * MIT License
 * @author Mario Martínez <gameofender@gmail.com>
 */
session_start();



/**
 * Configuracions varies
 */
require 'config/settings.php';

/**
 * Errors handler
 * 
 */
ini_set('display_errors', display_errors);
function error_handler($errno, $errstr, $errfile, $errline){
	header('Location: '.VIEWS_ERROR_PATH.'500.php?error=['.$errno.'] '.$errstr.' in '.$errfile.' line '.$errline);
}
set_error_handler("error_handler", E_ERROR); // Només amb errors fatals


/**
 * Configuració del idioma
 * Aquest bloc de codi serveix per definir i 
 * instanciar l'objecte encarregat d'administrar i mostrar
 * totes les traduccions de la pagina web d'una forma entenedora.
 *
 * Es fa servir la clase i18n i es requreix del arxiu de configuració 
 * config/i18n.php
 *
 * Els arxius de traduccions de forma predeterminada seran a locale/{code_lang} ex. es_ES
 */
include 'config/i18n.php';
include 'core/i18n.php';

$lang = $_SESSION['lang'];
if(empty($lang) && !isset($_GET['lang'])) $lang = default_lang;
else if(!empty($_GET['lang'])) $lang = $_GET['lang'];

// Desem les variables com a globals i de sessió
define(lang, $lang);
$_SESSION['lang'] = lang;

// Inicialitzem l'objecte de traduccions
$i18n = new i18n(lang, $locales);
// Li diem quin es l'idioma original
$i18n->setOriginalLang(original_lang); 

/**
 * Configuració de la base de dades
 * Iniciem el objecte de la base de dades que utilitzarem
 * per tota la programació dels controladors.
 *
 * Molt important que les dades de connexió siguin correctes.
 * Per defecte, el charset serà de UTF8
 */
require 'config/database.php';
require 'core/DBA.php';
$db = new DBA(_DB_HOST, _DB_DATABASE, _DB_USER, _DB_PASS);



/**
 * ActiveRecord an ORM
 */
require 'core/ActiveRecord.php';

/**
 * Request handler Security
 * Agafem totes les variables que ens arriben de l'exterior
 * i els hi passem un filtre de seguretat.
 * Seguidament les empaquetem en una variable anomenada $req que 
 * podrem fer servir als nostres controladors.
 */
require 'core/Request.php';
require 'core/Response.php';

$request = new Request();
$response = new Response();


/**
 * Helpers
 */
/*$helpers_files = $request->session('helpers_files');
if(empty($helpers_files) || ENV_VAR == "dev") {
	$helpers_files = scandir(HELPERS_PATH);
	$request->session('helpers_files', $helpers_files);
}
foreach ($helpers_files as $file) {
	if($file != '.' && $file != '..' && strpos($file, '.php')) include HELPERS_PATH . $file;
}*/


/**
 * Models
 * Incloem tots els controladors
 * que tenim definits a la carpeta controllers
 */
$model_files = $request->session('model_files');
if(empty($model_files)) {
	$model_files = scandir(MODELS_PATH);
	$request->session('model_files', $model_files);
}
foreach ($model_files as $file) {
	if($file != '.' && $file != '..' && strpos($file, '.php')) include MODELS_PATH . $file;
}



/**
 * Business 
 */
$business_files = $request->session('business_files');
if(empty($business_files) || ENV_VAR == "dev") {
	$business_files = scandir(BUSINESS_PATH);
	$request->session('business_files', $business_files);
}
foreach ($business_files as $file) {
	if($file != '.' && $file != '..' && strpos($file, '.php')) include BUSINESS_PATH . $file;
}



/**
 * Controllers
 * Incloem tots els controladors
 * que tenim definits a la carpeta controllers
 */
$controllers_files = $request->session('controllers_files');
if(empty($controllers_files) || ENV_VAR == "dev") {
	$controllers_files = scandir(CONTROLLERS_PATH);
	$request->session('controllers_files', $controllers_files);
}
foreach ($controllers_files as $file) {
	if($file != '.' && $file != '..' && strpos($file, '.php')) include CONTROLLERS_PATH . $file;
}



/**
 * Logger 
 * Llibreria que ens permet utilitzar un logger
 * i enregistrar totes els peticions http.
 */
include 'core/Logger.php';


/**
 * Middleware 
 * /middleware/index.php
 * 
 * Incloem arxiu de funcions que ens faran de filtre.
 * Aquestes funcions es declaren a l'arxiu rutes i s'executaràn abans d'arribar al controlador.
 * Per exemple, si una funció concreta només la poden utilitzar usuaris autentificats, podem crear un 
 * filtre anomenat `isAuthorized()` que ens filtri la peticíó HTTP i esbrinar si el que fa la petició
 * te sesió d'usuari.
 */
include 'app/middleware/index.php';




/**
 * Routes
 * Arxiu on tenim totes les rutes
 */
require 'core/RouterProxy.php';
$Router = new Router();
// Add user routes
require 'config/routes/index.php';
// dispatch HTTP petition
$Router->dispatch($request->params['GET']['u'], $request, $response);
$res = $response;
if(!empty($res->locals)) extract($res->locals);

// Flash messages are optional
if(!empty($res->flash)) $flash = $res->flash;
if(is_array($flash)) {
    $flash_type = key($flash);
    $flash_message = $flash[$flash_type];
}
else {
	$flash_type = 'info';
	$flash_message = $flash;
}




/**
 * Vistes i Plantilla
 * Segons el response status carreguem una vista o un arxiu d'error.
 * Si l'arxiu de vista no existeix en aquest moment, s'inclou la vista del 404.
 */
if($res->status != 200) include VIEWS_ERROR_PATH.$res->status.'.php';
else if($res->status == 200){
	if($res->xhr && $res->view != '') include VIEWS_PATH.$res->view; //without template
	else if(!$res->xhr  && $res->contentType == "text/html") {

		// set view file 
		$view_file = $res->view;
		if(empty($view_file) || !file_exists(VIEWS_PATH.$view_file)) {
			// arxiu 404 rep l'arxiu que falta
			$lost_file = VIEWS_PATH.$view_file;
			include VIEWS_ERROR_PATH.'404.php';
			exit();
		}
		else $view_file = VIEWS_PATH.$view_file;

		// add a layout
		if(!empty($res->locals['layout'])) include $res->locals['layout'];
		else include default_layout;
	}
} 



/**
 * Aquest petit troç de codi genera les traduccions en temps real
 * en els idiomes introduits al arxiu de configuració d'idiomes.
 */
if(i18n_translate) {
	$i18n->generate(i18n_path);
}
?>