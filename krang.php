<?php
/**
 * Krang FRAMEWORK
 * ==================================
 * "The evil way to conquer Internet"
 * 
 * This framework has been created for the occasion to expedite an orderly and simple programming websites 
 * thumb with technical requirements. You will not find large classes, functions with efficiency rendering subroutines C. 
 * This is a framework for novice programmers and eager to learn and develop the methodology (M) VC 
 * can change anything you want for this project, including even this market using this framework. 
 * In this file you can see how this application is launched and how alimentantse all configuration files, 
 * routes and other components. No more.
 * 
 * v.0.1
 * MIT License
 * @author Mario Martínez <gameofender@gmail.com>
 */
session_start();



/**
 * Settings
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
 * i18n - Translates and locales
 */
include 'config/i18n.php';
include 'core/i18n.php';

$lang = $_SESSION['lang'];
if(empty($lang) && !isset($_GET['lang'])) $lang = default_lang;
else if(!empty($_GET['lang'])) $lang = $_GET['lang'];

// Save the language on session
define(lang, $lang);
$_SESSION['lang'] = lang;

// Init a i18n class to translate project's strings.
$i18n = new i18n(lang, $locales);
// What is original language?
$i18n->setOriginalLang(original_lang); 

/**
 * Database configuration
 * Require Class DBA and Configuration to create a connection instance and use form ActiveRecord
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
 */
require 'core/Request.php';
require 'core/Response.php';

$request = new Request();
$response = new Response();


/**
 * Helpers
 * Add your helpers here
 */
//ex. include 'app/helpers/helpme.php;'


/**
 * Models
 * Add all models class to project.
 */
$model_files = $request->session('model_files');
if(empty($model_files) || ENV_VAR == "dev") {
	$model_files = scandir(MODELS_PATH);
	$request->session('model_files', $model_files);
}
foreach ($model_files as $file) {
	if($file != '.' && $file != '..' && strpos($file, '.php')) include MODELS_PATH . $file;
}



/**
 * Business 
 * (optional)
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
 * Include all controllers class.
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
 * Help a project use a Log service
 */
include 'core/Logger.php';


/**
 * Middleware 
 * /middleware/index.php
 * We can use middleware to define route policy
 */
include 'app/middleware/index.php';




/**
 * Routes
 * Interprete all routes and execute actions.
 * Retrieve all params to use in views.
 */
require 'core/Router.php';
$Router = new Router();
// Add user routes
require ROUTES_PATH;
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
 * Views and Layouts
 * If the view and layout exists and status code is 200 render a view else render 404 or status code error page.
 */
if($res->status == 200){
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
		if(!empty($res->locals['layout'])) include layaut_path.$res->locals['layout'];
		else include default_layout;
	}
}
else include VIEWS_ERROR_PATH.$res->status.'.php';



/**
 * This section generate i18n strings if the i18n_translate var is defined TRUE.
 */
if(i18n_translate) {
	$i18n->generate(i18n_path);
}
?>
