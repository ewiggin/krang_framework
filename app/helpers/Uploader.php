<?php
/**
* Bona practica. Programació Orientada Objectes.
* Clase per poder pujar arxius d'una forma més eficient,
* sense duplicar codi. També s'adjunta clase filla que puja imatges.
* 
* @author Mario Martínez 
* 
* Manual
* ======
* Per poder manipular les propietats de la clase es fa servir un array de 
* configuracions anomenat `config`. Els paths i altres configuracions es guarden en 
* aquesta variable mitjançant el metode $this->set(key, value).
* 
* 	Ex.
* 		$up = new Uploader($files);
* 		$up->set('path', '/upload/');
* 		$up->set('path_thumb', '/upload/thumb/');
* 		$up->Upload();
* 
* Detecta si es un sol arxiu el que es vol pujar o un array d'arxius 
* per pujarlos de forma sequencial.
* 
* 
*/
class Uploader
{
	var $path = '../uploads/';
	var $files = array();
	var $file = null;
	var $conf = array();

	/**
	 * Constructor.
	 * Detecta si es un sol arxiu o un array.
	 * 
	 * @param $file 	- $_FILE or Array($_FILE)
	 */
	function __construct($file)
	{
		if(!empty($file[0])) $this->files = $file;
		else if($file) $this->file = $file;
	}

	/**
	 * Ens permet setejar la configuració
	 * de la clase.
	 * 
	 * 
	 * @param $key 		Clau del array
	 * @param $value 	Valor que es vol desar 
	 */
	public function set($key, $value){
		$this->conf[$key] = $value;
	}

	/**
	 * Recupera el valor de la configuració
	 * que es reclama per clau.
	 * 
	 * @param $key 		Clau del array de configuració
	 * @return String
	 */ 
	public function get($key){
		return $this->conf[$key];
	}

	/**
	 * Acció publica per pujar arxius
	 * Procesa tots els $_FILES per adaptarlos al comportament
	 * de la clase.
	 * 
	 */
	public function Upload(){

		// Es un array de fitxers?
		if(!empty($this->files) && sizeof($this->files) > 1){
			$aux = array();
			$nElem = sizeof($this->files['name']);
			// recorrem totes les propietats
			foreach ($this->files as $key => $value) {
				for ($i=0; $i < $nElem; $i++) {
					if($this->files['name'][$i])  $aux[$i][$key] = $this->files[$key][$i];
				}
			}

			// Exists this name?
			foreach ($aux as $key => $file) {
				// Upload this
				if($this->canUpload($file)) {
					// strlower pels .JPG del paint
					$file['name'] = strtolower($file['name']);
					// Li cambiem el nom => date() + nom fins el primer ".";
					$extension = end(explode(".", $file['name']));
					$file['name'] = date("YmdHis") . '.'.$extension;
					// Executem una funció que ens renombra el fitxer si ja existeix.
					$file['name'] = $this->rename_if_exists($file['name'], $this->conf['path']);
					// Guardem en un array el nom de l'arxiu
					$aux[$key]['name'] = $file['name'];
					// upload!!
					if(!$this->UploadTo($file)) $file = false;
				}
				else throw new Exception("Tipus d'arxiu no permés. ({$file['name']} - {$file['type']}) ", 1);
			}
			// retornem un altre cop l'array
			return $aux;
		}
		else {
			// Només volem pujar un fitxer
			$file = $this->file;
			// El podem pujar? Es una extensió permesa?
			if($this->canUpload($file)){

				// strlower pels .JPG del paint
				$file['name'] = strtolower($file['name']);
				// Li cambiem el nom => date() + nom fins el primer ".";
				$extension = end(explode(".", $file['name']));
				$file['name'] = date("YmdHis") . '.'.$extension;
				// Executem una funció que ens renombra el fitxer si ja existeix.
				$file['name'] = $this->rename_if_exists($file['name'], $this->conf['path']);
				// Procedim a pujar el fitxer.
				if(!$this->UploadTo($file)) $file = false;

				// Retorn del resultat, false si ha anat malament.
				return $file;

			}
			else throw new Exception("Tipus d'arxiu no permés. ({$file['name']} - {$file['type']}) ", 1); // Arxiu amb extensió no permesa
		}
	}

	/**
	 * Metode que comprova si l'arxiu que es vol
	 * pujar te permís per fer-ho. Al atribut `conf` es 
	 * desen tots els tipus d'arxius que es poden pujar.
	 * 
	 * @param $file 	$_FILE
	 */
	private function canUpload($file){

		if(!empty($this->conf['accepted_types'])) return in_array($file['type'], $this->conf['accepted_types']);
		else return true;
	}

	/**
	 * Esbrina si el nom existeix a disc i
	 * el renombra si es afirmatiu.
	 * 
	 * @param $name 	Nom de l'arxiu.
	 * @param $path 	Path on ha de buscar.
	 * 
	 * @return String 	Retorna el nom modificat i únic.
	 */
	function rename_if_exists($name, $path){
		
		while ( file_exists($path. '/'. $name) ){
			$nomSenseExt=explode(".", $name);
			$extensio = end($nomSenseExt);
			$name=$nomSenseExt[0] . "X." . $extensio;		
		}

		return $name;

	}

	/**
	 * Funció privada per cridar el copy()
	 * i pujar definitivament l'arxiu.
	 * 
	 * @param $file 	Arxiu processat per aquesta clase.
	 * @return void
	 */
	private function UploadTo($file){
		return copy($file['tmp_name'], $this->conf['path'].$file['name']);
	}

}


/**
* Classe filla per pujar i redimensionar Imatges.
* 
* @author Mario Martínez
*/
class UploadImages extends Uploader
{
	
	/**
	 * Constructor
	 * Crida al constructor del pare e inicialitza el path per 
	 * defecte dels thumbnails.
	 * 
	 * @param $files 	- $_FILE || Array($_FILE)
	 */
	function __construct($files){
		parent::__construct($files);
		$this->conf['path_thumb'] = 'thumbnails/';
		$this->conf['path_thumb_big'] = 'thumbnailsG/';
	}

	/**
	 * Recupera el Path on es pujaran les imatges
	 * 
	 * @return String 	- Path on puja l'arxiu
	 */
	function getPath(){
		return $this->conf['path'];
	}
	/**
	 * Alies per setejar la configuració.
	 * 
	 * @param $string 	- Path
	 */ 
	function setPath($string){
		$this->conf['path'] = $string;
	}

	function getPathThumbnails(){
		return $this->conf['path_thumb'];
	}

	function setPathThumbnails($string){
		$this->conf['path_thumb'] = $string;
	}

	/**
	 * Crida la funció de pujada del pare
	 * i una vegada completat el procés, si l'usuari ho vol
	 * redimensiona les imatges pujades. 
	 * 
	 */
	function Upload(){
		$uploaded = parent::Upload();
		if(!$uploaded){
			echo 'Error upload image';
		}
		else if($this->conf['thumb']) $this->createThumbs($uploaded);

		return $uploaded;
	}

	/**
	 * Metode que redimensiona les imatges
	 * pujades previament.
	 * 
	 * @param $files
	 */ 
	function createThumbs($files){

		if(!empty($files[0])){
			
			foreach ($files as $key => $value) {
				$this->createThumbnail($this->conf['path'], $this->conf['path'].''.$this->conf['path_thumb'], $value['name'], 198);	
				$this->createThumbnail($this->conf['path'], $this->conf['path'].''.$this->conf['path_thumb_big'], $value['name'], 525);	
			}
		}
		else {
			$this->createThumbnail($this->conf['path'], $this->conf['path'].''.$this->conf['path_thumb'], $files['name'], 198);	
			$this->createThumbnail($this->conf['path'], $this->conf['path'].''.$this->conf['path_thumb_big'], $files['name'], 525);	
		}
	}


	function createThumbnail($path_to_image_directory, $path_to_thumbs_directory, $filename, $final_width_of_image = 98) {
     
	    if(preg_match('/[.](jpg)$/', $filename)) {
	        $im = imagecreatefromjpeg($path_to_image_directory . $filename);
	    } else if (preg_match('/[.](gif)$/', $filename)) {
	        $im = imagecreatefromgif($path_to_image_directory . $filename);
	    } else if (preg_match('/[.](png)$/', $filename)) {
	        $im = imagecreatefrompng($path_to_image_directory . $filename);
	    }

	    $ox = imagesx($im);
	    $oy = imagesy($im);
	     
	    $nx = $final_width_of_image;
	    $ny = floor($oy * ($final_width_of_image / $ox));
	     
	    $nm = imagecreatetruecolor($nx, $ny);

	    // enable alpha blending on the destination image. 
		imagealphablending($nm, true); 
		// save the alpha 
		imagesavealpha($nm,true); 

		// Allocate a transparent color and fill the new image with it. 
		// Without this the image will have a black background instead of being transparent. 
		$transparent = imagecolorallocatealpha( $nm, 0, 0, 0, 127 ); 

		imagefill( $nm, 0, 0, $transparent ); 
	     
	    imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);
	     
	    if(!file_exists($path_to_thumbs_directory)) {
			if(!mkdir($path_to_thumbs_directory)) {
			   die("There was a problem. Please try again!");
			} 
       	}
	 
	    imagepng($nm, $path_to_thumbs_directory . $filename);
	    
	    return true;
	}

}
?>