<?php
/**
 * Clase per fer servir la 
 * base de dades correctament.
 */
class DBA {


	var $server = '';
	var $dbuser = '';
	var $dbpassword = '';
	var $database = '';
	var $charset = 'utf8'; 	
	var $debug = false;
	var $db = null;

	/**
	 * Constructor
	 * 
	 * @param String  $server     
	 * @param String  $database   
	 * @param String  $dbuser     
	 * @param String  $dbpassword 
	 * @param boolean $debug      Mostra tota la informació de les peticions SQL.
	 */
	public function __construct($server, $database, $dbuser, $dbpassword, $debug = false){

		$this->setDebug($debug);
		
		if(!empty($server) && !empty($database) && !empty($dbuser) && !empty($dbpassword)){		

			$this->dbg('Connection', 'Intentant connectar amb el servidor.');
			//
			$mysqli = new mysqli($server, $dbuser, $dbpassword, $database);

			/* comprobar la conexió */
			if ($mysqli->connect_errno) {
			    $this->dbg('Connection',"Falló la conexión: %s\n", $mysqli->connect_error);
			    exit();
			}
			$this->dbg('Connection', 'Connexió correcte (OK)');
			/* cambiar el conjunto de caracteres a utf8 */
			if (!$mysqli->set_charset("utf8")) $this->dbg('Connection',"Error cargando el conjunto de caracteres utf8: %s\n", $mysqli->error);
			else $this->dbg('Info',"Conjunt de caracteres actual: ".$mysqli->character_set_name());
			
			//
			$this->db = $mysqli;
		}
		else $this->dbg('info','Database no inicialitzada');

	}

	/**
	 * Retorna el sha1 d'un string passat per parametre.
	 * 
	 * @param String 	$string 	String que volem encriptar.
	 * @return String 				String encriptada
	 */
	public function SHA1($string){
		return sha1($string); 
	}

	/**
	 * Recupera la informació d'un registre de la base de dades.
	 * Com a diferencia amb get(), aquest retorna l'array i no la referencia
	 * de la Query.
	 * 
	 * @param  String 	$table  	Nom de la taula
	 * @param  Integer 	$id     	Identificador del registre
	 * @param  String 	$fields 	Camps que volem recuperar, per defecte *
	 * 
	 * @return Array
	 */
	public function getOne($table, $id, $fields = '*'){

		$table = $this->db->escape_string($table);
		$id = $this->db->escape_string($id);

		return $this->get($table, $fields, 'WHERE id = '.$id)->fetch_assoc();
	}
	
	/**
	 * Recupera la referencia Query de buscar 1 sol registre
	 * a la base de dades.
	 * 
	 * @param  String 	$table  	Nom del taula
	 * @param  String 	$fields 	Camps que volem recuperar
	 * @param  String 	$where  	Condicions per seleccionar el registre
	 * 
	 * @return query_instance 		Instancia de la Query
	 */
	public function get($table, $fields = '*', $where = ''){

		$table = $this->db->escape_string($table);

		$strSQL = 'SELECT '.$fields.' FROM '.$table.' '.$where.' LIMIT 1';
		return $this->query($strSQL);

	}

	/**
	 * Contador de registres d'una taula.
	 * 
	 * @param  String 	$table 		Nom de la taula
	 * @param  String 	$where 		Condicions per seleccionar els registres.
	 * @return int
	 */
	public function count($table, $where = ''){

		$table = $this->db->escape_string($table);
		$res = $this->query('SELECT COUNT(id) count FROM '.$table.' '.$where);
		
		$return = $res->fetch_assoc();

		return $return['count'];

	}


	/**
	 * Permet paginar una colecció de registres que es troben
	 * a la base de dades.
	 * 
	 * @param  String  	$strSQL  	Consulta SQL
	 * @param  integer 	$start   	Comença per ...
	 * @param  integer 	$limit   	Fins ...
	 * 
	 * @return Array
	 */
	public function paginate($strSQL, $page = 1, $limit = 20){

		$page = intval($page);
		if($page == 0) $page = 1;
		
		$total = $this->query($strSQL)->num_rows;
		$start = ($page-1) * $limit; // 0 * 20 // 1 * 20 // 2 * 20 .... 
		$strSQL .= " LIMIT {$start},{$limit} ";

		$res = $this->query($strSQL);
		while ($row = $res->fetch_assoc()) {
			$array['items'][$row['id']] = $row;
		}

		$array['pagination'] = array(
			pagination_start => $start,
			pagination_limit => $limit,
			pagination_total => $total,
			pagination_current => $page
		);

		return $array;

	}

	/**
	 * Recupera un Objecte segons el seu identificador.
	 * 
	 * @param  String 	$table 		Nom de la taula
	 * @param  integer 	$id    		Identificador del registre
	 * 
	 * @return Object
	 */
	public function getObjectById($table, $id){
		return $this->getObject($table, '*', 'WHERE id = '.$id);
	}

	/**
	 * Recupera de la base de dades un registre en forma d'objecte
	 * segons una consulta SQL.
	 * 
	 * @param  String 	$table  	Nom de la taula
	 * @param  String 	$fields 	Camps que volem que composin l'objecte
	 * @param  String 	$where  	Condicions per trobar el registre
	 * 
	 * @return Object
	 */
	public function getObject($table, $fields = '*', $where = ''){

		$table = $this->db->escape_string($table);
		$strSQL = 'SELECT '.$fields.' FROM '.$table.' '.$where.' LIMIT 1';
		//
		return $this->query($strSQL)->fetch_object();		
	}

	/**
	 * Recupera tots els registres de la base de dades que compleixen
	 * amb les condicions.
	 * 
	 * @param  String 	$table  	Nom de la taula
	 * @param  String 	$fields 	Camps que volem
	 * @param  String 	$where  	Condicions per trobar el registre.
	 * 
	 * @return query_instance
	 */
	public function getAll($table, $fields = '*', $where =''){

		$table = $this->db->escape_string($table);

		return $this->query('SELECT '.$fields.' FROM '.$table.' '.$where);
	}

	/**
	 * Recupera registres de la base de dades i els transforma en un 
	 * array associatiu llest per utilitzar.
	 * 
	 * @param  String 	$table  	Nom de la taula
	 * @param  String 	$fields 	Camps que volem
	 * @param  String 	$where  	Condicions per trobar el registre.
	 * 
	 * @return Array
	 */
	public function getArray($table, $fields = '*', $where=''){
		
		$array = array();

		if(is_array($where)){
			$strWhere = ' WHERE ';
			$nElem = sizeof($where);
			$i = 0;
			foreach ($where as $key => $value) {

				$strWhere .= $key.' = '.$value;

				if(++$i < $nElem) $strWhere .= ' AND ';
			}
		}
		else $strWhere = $where;

		$res = $this->getAll($table, $fields, $strWhere);
		while ($row = $res->fetch_assoc()) {
			$array[] = $row;
		}

		return $array;
	}

	/**
	 * Executa una Query
	 * 
	 * @param  String 	$strSQL 	Query a executar.
	 * 
	 * @return query_instance
	 */
	public function query($strSQL){

		$this->dbg('Query', 'Executant: '.$strSQL);

		// And finally exec the query
		$q = $this->db->query($strSQL);
		if(!$q) {
			$this->dbg('Query', 'Error al realitzar la query. (KO)');
			$this->dbg('Query', 'Error: '.$this->db->error); 
		}
		else $this->dbg('Query', 'Query executada correctament. (OK)');
		
		return $q;
	}

	/**
	 * Elimina un registre de la base de dades.
	 * 
	 * @param  String 	$table 		Nom de la taula
	 * @param  String 	$where 		Condicions per trobar registres
	 * 
	 * @return query_instance
	 */
	public function delete($table, $where = ''){

		$table = $this->db->escape_string($table);
		$strSQL = 'DELETE FROM '.$table.' '.$where;
		//
		$this->query($strSQL);
		return $this->db->affected_rows;
	}

	/**
	 * Elimina 1 registre de la base de dades.
	 * 
	 * @param  String 	$table 		Nom de la taula
	 * @param  Integer 	$id    		Indentificador del registre
	 * 
	 * @return query_instance
	 */
	public function deleteOne($table, $id){

		$id = $this->db->escape_string($id);
		$table = $this->db->escape_string($table);

		$strSQL = 'DELETE FROM '.$table.' WHERE id = '.$id;
		//
		$this->query($strSQL);

		return $this->db->affected_rows;
	}

	/**
	 * Elimina tots els registres d'una taula de la base de dades.
	 * 
	 * @param  String 	$table    		Nom de la taula
	 * 
	 * @return query_instance
	 */
	public function deleteAll($table){
		return $this->delete($table, '');
	}

	/**
	 * Actualitza les dades d'un registre de la base de dades.
	 * 
	 * @param  String 	$table 		Nom de la taula
	 * @param  Integer 	$id    		Indentificador del registre
	 * @param  Array 	$array 		Array amb dades noves.
	 * 
	 * @return query_instance
	 */
	public function update($table, $id, $array, $primary_key = "id"){

		$id = $this->db->escape_string($id);
		$table = $this->db->escape_string($table);

		foreach ($array as $key => $value) {
			if($value != 'NULL') $update .= ' '.$key.' = "'.$value.'",';
			else $update .= ' '.$key.' = '.$value.',';
		}

		$update = rtrim($update, ",");

		$strSQL = 'UPDATE '.$table.' SET '.$update.' WHERE '.$primary_key.' = '.$id;
		return $this->query($strSQL);
	}

	/**
	 * Actualitza diferents registres segons les condicions.
	 * 
	 * @param  String $table 	Nom de la taula
	 * @param  String $where 	Condicions que apliquen a diferents registres.
	 * @param  Array $array 	Array a desar
	 * 
	 * @return query_instance
	 */
	public function updateWhere($table, $where, $array){

		$table = $this->db->escape_string($table);

		foreach ($array as $key => $value) {
			if($value != 'NULL') $update .= ' '.$key.' = "'.$value.'",';
			else $update .= ' '.$key.' = '.$value.',';
		}

		$update = rtrim($update, ",");

		$strSQL = 'UPDATE '.$table.' SET '.$update.' '.$where;
		return $this->query($strSQL);
	}

	/**
	 * Protegeix la Query aplicant el mysqli_escape_string();
	 * 
	 * @param  String 	$string 	Variable a filtrar.
	 * 
	 * @return String
	 */
	public function escape_string($string){
		if($this->db) return $this->db->escape_string($string);
		else return $string;
	}
	
	/**
	 * Recupera l'identificador de l'ultim Insert fet a la base 
	 * de dades.
	 * 
	 * @return Integer
	 */
	public function getInsertId(){ 
		return $this->db->insert_id;
	}

	/**
	 * Insereix a la base de dades un registre.
	 * 
	 * @param  String 	$table 		Nom de la taula
	 * @param  Array 	$array 		Array amb tota l'estructura de la taula.
	 * 
	 * @return query_instance
	 */
	public function insert($table, $array){
		
		$table = $this->db->escape_string($table);

		foreach ($array as $key => $value) {

			$key = $this->db->escape_string($key);
			$value = $this->db->escape_string($value);

			$columns .= $table.'.'.$key.',';
			if($value != "NULL") $values .= '"'.$value.'",';
			else $values .= $value.',';

		}
		// delete last char
		$columns = rtrim($columns, ",");
		$values = rtrim($values, ",");

		$strSQL = 'INSERT INTO '.$table.' ('.$columns.') VALUES ('.$values.') ';

		return $this->query($strSQL);
	}

	/**
	 * Mostra per pantalla el debug de cada operació.
	 * 
	 * @param  String $title   		Label de l'operació
	 * @param  String $message 		Descripció detallada
	 * 
	 */
	private function dbg($title, $message){
		if($this->debug) echo '<span class="debug"><strong>[DEBUG] '.$title.'</strong> '.$message.' <br /></span>';
	}

	/**
	 * Activa o desactiva el degug de totes les operacions
	 * d'aquesta instancia.
	 * 
	 * @param boolean $bool 
	 */
	public function setDebug($bool){
		$this->debug = $bool;
	}


}
?>
