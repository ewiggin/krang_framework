<?php 
/**
 * Sistema de traduccio
 * Aquest objecte s'encarrega d'administrar
 * de forma mes entenedora totes les traduccions 
 * de la pagina.
 *
 * @author Mario Martínez <mario@javajan.com>
 */

class i18n
{
	var $strings 		= array();
	var $new_strings 	= array();
	var $original_lang;
	var $current_lang;
	var $locale_file;
	// Codi d'idiomes que es fan servir.
	// També son el noms dels fitxers.
	var $locales = array(
		ca => 'es_CA', 
		es => 'es_ES', 
		en => 'en_GB', 
		fr => 'fr_FR', 
		zh => 'zh_TW'
	);


	/**
	 * Constructor del objecte
	 * @param string $lang 	Idioma que s'ha triat
	 */
	function __construct($lang, $locales = '') {
		// Desem el idioma actual
		$this->current_lang = $lang;

		if(is_array($locales)) $this->locales = $locales;

		// Muntem on es el fitxer de traduccions
		$this->locale_file = i18n_path.$this->locales[$lang].'.php';
		// Mirem si existeix el fitxer de traduccions
		if(file_exists($this->locale_file)){
			// Incloem l'array de traduccions
			include $this->locale_file;
			// Guardem aquest array al objecte.
			$this->strings = $strings;
		}
		# else echo 'Aquest idioma no te traduccions. Generar: '.$this->locale_file;
	}

	/**
	 * Defineix els locales que es faran servir.
	 * 
	 * @param Array $array 
	 */
	public function setLocales($array) {
		$this->locales = $array;
	}

	/**
	 * Recupera la traducció del fitxer de traduccions.
	 * 
	 * @param  string $key 	Valor que es cercará a les traduccions
	 * @return string      	Traducció corresponent a aquest valor
	 */
	public function gettext($key)
	{
		$string = $key;
		// si hi ha traducció la mostrem
		if(!empty($this->strings[$key]) && $this->current_lang != $this->original_lang) $string = $this->strings[$key];

		// log
		$this->new_strings[$key] = $string;

		return $string;
	}

	/**
	 * Alias de la funció gettext()
	 * 
	 * @param  string $key 	Valor que es cercará a les traduccions
	 * @return string      	Traducció corresponent a aquest valor
	 */
	public function _($key) {
		return $this->gettext($key);
	}

	/**
	 * Seteja quin es el idioma amb que s'ha programat 
	 * la aplicació web.
	 * 
	 * @param string $lang  	Idioma original a la programació
	 */
	public function setOriginalLang($lang){
		$this->original_lang = $lang;
	}

	/**
	 * Genera els fitxer per començar a traduir.
	 * Fa servir la variable new_strings que enregistra totes les traduccions
	 * que es fan servir a la web, i d'aquesta informació preparem els fitxers 
	 * per a la traduccions.
	 * 
	 * @return void
	 */
	public function generate($path){

		// TODO: Neteja ja!
		
		if($this->original_lang != "" && !empty($this->new_strings)){
			
			// generate files
			foreach ($this->locales as $lang => $file_name) {




				$mode = 'x+';
				if(file_exists($path.$file_name.'.php')) {
					$mode = 'w+';
					include $path.$file_name.'.php';

					$old_strings = $strings;
				}

				$fp = fopen($path.$file_name.'.php', $mode);

				fwrite($fp, "<?php \n");
				fwrite($fp, "/**");
				fwrite($fp, "\n* Modifica només la segona linia de cada string. \n* Recorda que el generador de traduccions sobrescriu cada vegada aquest fitxer, quan la web sigui acabada s'ha de desactivar! \n*\n*\n* Per desactivar modifica la variable define(i18n_translate, true); de /config/i18n.php ");
				fwrite($fp, "\n* @author Mario Martinez <mario@javajan.com> \n");
				fwrite($fp, "*/ \n\n ");
				fwrite($fp, "\$strings = array(\n");

				foreach ($this->new_strings as $key => $value) {
					
					if($this->original_lang == $lang) $value = "";

					// tots els strings que hem captat en aquesta carrega
					// l'hem de ficar dins del fitxer i que no es repeteixi
					//  el key => value

					if(empty($old_strings[$key])) {
						$old_strings[$key] = $value; // new value!
					}
				}


				foreach($old_strings as $key => $str){
					fwrite($fp, "\n \n");
					fwrite($fp, "\"".$key."\" \n => \"". $str."\", ");	
					fwrite($fp, "\n \n");
				}


				fwrite($fp, "\n);");
				fwrite($fp, "\n \n \n ?>");
				// close file
				fclose($fp);
			}
			
		}

	}
}
?>