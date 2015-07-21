<?php 
/**
 * Configuració d'idiomes per a les parts
 * estatiques de la pagina.
 */

// Path on es troben les traduccions
define(i18n_path, 'resources/locales/');

// Generate translations
// Genera fitxers de traduccions a cada refresc de pagina
// facilitant la traducció dels literals del projecte.
// 
// NOTA: Recomanem posar a flase en Producció.
// 
define(i18n_translate, true); 


// 
// Llista d'idiomes permesos
// 
$locales = array(
	ca => 'ca_CA', 
	es => 'es_ES',
	en => 'en_EN'
);
// Per utilitzar a la plantilla
$languages = array(
	'ca' => 'Català',
	'es' => 'Español',
	'en' => 'English'
);

/**
 * Default 
 * Defineix quin es el llenguatge per defecte
 */
define(default_lang, 'en');
/**
 * Original Lang
 * Defineix quin es el llenguatge que utilitzem al programar.
 */
define(original_lang, 'en');

?>