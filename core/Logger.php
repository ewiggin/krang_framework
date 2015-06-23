<?php 

/**
* Logger
* Clase que ens permet tenir un log eficient 
* a qualsevol part de la web.
*/
class Logger {
	
	var $levels 	= array('i' => 'Info', 'w' => 'Warning', 'e' => 'Error');
	var $path 		= './logs/';
	var $file 		= '';
	var $enabled 	= false;
	var $ignore 	= array();
	// database 
	// TODO: Hauria de ser una subclase o un modul
	var $to_database = false;
	var $db;
	var $logModel;

	/**
	 * Void constructor
	 */
	function __construct($file = '', $path = '') {
		
		if($path != '') $this->path = $path;
		if($file != '') $this->file = $file;
		
		return $this;
	}

	/**
	 * Especifica que el log s'ha de guardar a la base de dades.
	 * 
	 * @param  boolean $enabled 
	 * @return void
	 */
	public function toDatabase($enabled) {
		$this->to_database = $enabled;
	}

	public function setDatabaseModel($model) {
		$this->LogModel = new $model;
	}


	public function start() {
		$this->enabled = true;
	}

	public function stop(){
		$this->enabled = false;
	}


	public function addIgnore($regex) {
		$this->ignore[] = $regex;
	}

	/**
	 * Enregistra el log
	 * 
	 * @param  [type] $level   Nivell del log
	 * @param  [type] $message [description]
	 * @return [type]          [description]
	 */
	public function log($level, $message){
		
		if($this->enabled && !$this->isInIgnoreList($message)){

			// Nivell de log
			$level = $this->levels[$level];
			
			// Output
			// Level :: missatge :: datetime
			$datetime = date('c');

			// to file
			if(!$this->to_database){

				$output = "[$level] :: $message :: {$datetime} \n";
				// A quin fitxer escribiem
				if($this->file == '') $fileName = $this->getFileName(); // get new name
				else $fileName = $this->file;

				// desem el contingut al fitxer
				file_put_contents($this->path.$fileName, $output, FILE_APPEND);
			}
			else {
				// to Database
				$obj = $message;
				$log = $this->LogModel->create($obj);
				$this->LogModel->save($log);
			}
		}
	}

	/**
	 * Comprova si aquest missatge esta en una llista
	 * per ignorar.
	 * 
	 * @param  [type]  $message [description]
	 * @return boolean          [description]
	 */
	public function isInIgnoreList($message) {
		$is = false;
		$i = 0;
		if(!empty($this->ignore)){
			while(!$is && $i < sizeof($this->ignore)){
				if(is_array($message)){
					foreach ($message as $key => $value) {
						$is = (preg_match($this->ignore[$i], $value));
					}
				}
				else $is = (preg_match($this->ignore[$i], $message));
				$i++;
			}
		}
		return $is;
	}

	/**
	 * Alias to log()
	 * @param [type] $level   [description]
	 * @param [type] $message [description]
	 */
	public function add($level, $message) {
		$this->log($level, $message);
	}

	private function getFileName() {
		$date = date('Y-m-d');
		//
		return 'http-log-'.$date.'.txt';
	}

	public function setPath($path) {
		$this->path = $path;
	}

	public function getPath() {
		return $this->path;
	}

	public function setFile($fileName) {
		$this->file = $fileName;
	}

}


?>