<?php 
/**
 * Configuració d'idiomes per a les parts
 * estatiques de la pagina.
 */

// Path on es troben les traduccions
define(i18n_path, 'locale/');

// Generate translations
// Genera fitxers de traduccions a cada refresc de pagina
// facilitant la traducció dels literals del projecte.
// 
// NOTA: Recomanem posar a flase en Producció.
// 
define(i18n_translate, false); 


// 
// Llista d'idiomes permesos
// 
$locales = array(
	es => 'es_ES', 
);
// Per utilitzar a la plantilla
$languages = array(
	'es' => 'Español',
);

/**
 * Default 
 * Defineix quin es el llenguatge per defecte
 */
define(default_lang, 'es');
/**
 * Original Lang
 * Defineix quin es el llenguatge que utilitzem al programar.
 */
define(original_lang, 'es');

?>