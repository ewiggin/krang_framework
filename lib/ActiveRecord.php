<?php 
/**
* Clase que interactua amb el model -> base de dades -> controlador
* Ens permet cridar a nivell d'objecte diferents funcions que interactuen amb la base de dades.
* Versió actual: v.1.0
* 
* v.3.0 => Compatible amb diferents base de dades.
*/
class ActiveRecord extends DBA {
	
	var $table;
	var $attributes;
	var $object;
	var $list_objects;
	var $arrayObject;
	// database interactions
	var $db 	= null;
	var $Query = array(
		action => 'SELECT',
		fields => '',
		from => '',
		leftjoin => '',
		innerjoin => '',
		where => '',
		where_or => '',
		groupby => '',
		orderby => '',
		first => '0',
		limit => '',
	);
	var $populate_after_query = false;
	var $query 	= null;
	// fields on sql querys
	var $fields = null;
	var $primary_key = 'id';

	// Relationships
	var $has_many = array();
	var $has_one = array();
	var $belongs_to = array();
	var $has_and_belongs_to_many = array();

	public function __construct(){
		$this->db = new DBA(_DB_HOST, _DB_DATABASE, _DB_USER, _DB_PASS, false);
	}


	public function setDebug($value) {
		$this->db->setDebug($value);
	}

	/**
	 * Recupera només un registre de la base de dades
	 * trobat pel seu ID.
	 * 
	 * @param  Integer $id 		Identificador del registre.
	 * @return Object this
	 */
	public function one($id) {

		$this->Query['limit'] = 1;
		$this->Query['where'][] = $this->getPrimaryKeyField().' = '.$id;

		return $this;
	}

	/**
	 * Defineix l'ordenació de la consulta SQL.
	 * 
	 * @param  String $string 
	 * @return Object this
	 */
	public function order($string) {
		$this->Query['orderby'] = $string;

		return $this;
	}


	/**
	 * Afegiex columnes a la Query.
	 * 
	 * @param String $string 	Nom de les columnes separades per coma ','
	 * @return  $this
	 */
	public function setColumn($string) {
		$this->Query['fields'][] = $string;

		return $this;
	}

	/**
	 * Cerca a la base de dades registres amb aquest id.
	 * Accepta un array de id's per poder cercar més d'un registre.
	 * 
	 * @param  String/Array $ids Identificador del registre.
	 * @return Object this;
	 */
	public function find($ids) {
		
		$pk_field = $this->getPrimaryKeyField();
		if(is_array($ids)){
			
			foreach($ids as $id){
				$this->Query['where_or'][] = $pk_field.' = '.$id;	
			}
		}
		else {
			$this->Query['where'][] = $pk_field.' = '.$ids;
			$this->take(1);
		}


		return $this;
	}

	/**
	 * Recupera un registre aleatori.
	 * Afegiex a la Query un "ORDER BY RAND()"
	 * @return Object this
	 */
	public function random() {

		$this->Query['orderby'] = ' RAND() ';
		$this->Query['limit'] = 1;

		return $this;
	}

	/**
	 * Recupera la informació dels models que estan relacionats
	 * amb aquest registre. Aquestes relacions estan definides al Model.
	 * 
	 * @return Object this
	 */
	public function populate() {


		if(empty($this->object) && empty($this->list_objects)) $this->populate_after_query = true;
		else {

			// Belongs_to
			foreach($this->belongs_to as $class => $via) {
				$this->populate_exec($class, $via, 'belongs_to');
			}

			// has_many
			foreach($this->has_many as $class => $via) {
				$this->populate_exec($class, $via, 'has_many');
			}

			// has_one 
			foreach($this->has_one as $class => $via) {
				$this->populate_exec($class, $via, 'has_one');
			}

			// hard! has_and_belongs_to_many 
			foreach($this->has_and_belongs_to_many as $class => $info) {
				$this->populate_exec($class, $info, 'has_and_belongs_to_many');
			}
		}

		return $this;	
	}

	/**
	 * Executa internament el populate. I desa els resultats
	 * al objecte o al arrayList intern.
	 * 
	 * @param  String $class 	Nom de la clase de referencia.
	 * @param  String $via   	Camp amb el que es relacionen els elements.
	 * @param  String $mode  	Tipus de relació.
	 */
	private function populate_exec($class, $via, $mode = 'belongs_to') {

		// Nova instancia del l'objecte
		$Object = new $class;
		// Taula del objecte de referencia
		$table = $Object->table;
		
		// quin es el nom del fk?
		if(is_array($via)) $column_name_fk = $via['via'];
		else $column_name_fk = $via;

		// el camp que representa el primary_key
		$primary_key = $this->getPrimaryKeyField();
		$other_pk = $Object->getPrimaryKeyField();

		//////////////////////////////////////////////////////
		/// all populate modes
		//////////////////////////////////////////////////////
		switch ($mode) {
			/**
			 * Pertany a un altre objecte, per tant el fk el tenim nosaltres
			 */
			case 'belongs_to':
				$items = $Object->findBy($other_pk, $this->arrayObject[$column_name_fk])->take(1);
			break;
			/**
			 * Te en el seu poder un objecte, per tant el fk el té l'altre objecte
			 */
			case 'has_one': 
				$items = $Object->findBy($column_name_fk, $this->arrayObject[$primary_key])->take(1);
			break;
			/**
			 * Tenim en el nostre poder molts registres, per tant el fk el té l'altre objecte
			 */
			case 'has_many': 
				$items = $Object->findBy($column_name_fk, $this->arrayObject[$primary_key]);
			break;
			/**
			 * Som socis amb l'altre Objecte. Amb taula intermitja
			 */
			case 'has_and_belongs_to_many':

				$original_table = $this->table;
				$foreign_table 	= $Object->table;
				$middle_table	= $via['middle'];

				$other_foreign_column = $Object->has_and_belongs_to_many[get_class($this)]['via'];

				if($other_foreign_column != ""){

					$items = $Object->from($foreign_table)
									->from($middle_table)
									->where("{$middle_table}.{$column_name_fk} = {$this->arrayObject[$primary_key]}")
									->where("{$foreign_table}.{$other_pk} = {$middle_table}.{$other_foreign_column}")
									->group("{$foreign_table}.{$other_pk}");
				}
				else echo "L'objecte de referencia no te definit `has_and_belongs_to_many` en el seu Model.";
				
			break;
			
		}


		if(!empty($items)){
			// Execute query and return Object/s
			$items = $items->getObject();

			$this->arrayObject[$table] = $items;
			$this->object->$table = $items;
		}
	}

	/**
	 * Recupera les columnes reals de la base de dades
	 * que fa referencia aquet objecte.
	 * 
	 * @return Array 	Camps reals de la base de dades.
	 */
	public function getDBFields() {
		
		if($this->fields == null) {
			$i = 0;
			foreach ($this->attributes as $column => $props) {

				if(is_array($props)){
					if(!empty($props['primary_key']) && $props['primary_key'] === true) $this->primary_key =  $this->table.'.'.$column;
					if(!empty($props['column_name']) && $props['type'] != 'abstract') $column =  $this->table.'.'.$props['column_name'].' '.$column;
					else if($props['type'] == 'abstract') $column = $props['column_name'].' as '.$column;
				}
				else $column = $this->table.'.'.$column;
				
				// add to fields object
				if($i == 0) $this->fields .= $column.' ';
				else $this->fields .= ', '.$column;

				$i++;
			}
		}

		return $this->fields;
	}

	/**
	 * Comença la sentencia SQL
	 * 
	 * @param  String $action 	'SELECT' || 'UPDATE' || ...
	 * 
	 */
	private function startQuery($action) {
		$this->Query['action'] = $action;
	}

	/**
	 * Recupera registres amb filtre definit.
	 * Basicament afegeix clausules where dins la Query.
	 *
	 *	Users.findBy('id', 330);
	 *	Users.findBy(array(id => 330));
	 * 
	 * @param  String $key   	Nom del camp
	 * @param  String $value 	Valor del camp
	 * @return Object this;
	 */
	public function findBy($key, $value = '') {
		
		$this->startQuery('SELECT');
		$this->where($key, $value);
		
		return $this;
	}

	/**
	 * Recupera quin ha de ser el comparador ideal per 
	 * el tipus de columnes.
	 * 
	 * @param  String $column 	Nom de l'atribut del Model.
	 * @return String         	String del comparador '=' or 'like'
	 */
	private function getComparateFieldValue($column) {
		
		$type = $this->attributes[$column];

		if(is_array($type)){
			$type = $type['type'];
		}

		if(preg_match('/^string/', $type)) $comparate_with = ' LIKE ';
		else if(preg_match('/^int/', $type) || preg_match('/^float/', $type) || preg_match('/^boolean/', $type)) $comparate_with = ' = ';
		else $comparate_with = ' = ';

		return $comparate_with;
	}

	/**
	 * Afegeix restriccions Where a la Query.
	 * 
	 * @param  String $key   Nom del camp
	 * @param  string $value Valor del camp
	 * @return Object this
	 */
	public function where($key, $value = '') {

		if(is_array($key)) {
			foreach ($key as $column => $value) {
				$comparate_with = $this->getComparateFieldValue($key);
				$this->Query['where'][] = $column.' '.$comparate_with."\"".$value."\"";
			}
		}
		else if(!empty($value)) {
			$comparate_with = $this->getComparateFieldValue($key);
			$this->Query['where'][] = $key.$comparate_with."\"".$value."\"";
		}
		else $this->Query['where'][] = $key;

		return $this;
	}

	/**
	 * Alias de la funció where() pero amb operador AND.
	 * 
	 * @param  String $key   Nom del camp
	 * @param  string $value Valor del camp
	 * @return Object this
	 */
	public function wand($key, $value = '') {
		return $this->where($key, $value);
	}

	/**
	 * Alias de la funció where() pero amb operador OR.
	 * 
	 * @param  String $key   Nom del camp
	 * @param  string $value Valor del camp
	 * @return Object this
	 */
	public function wor($key, $value = '') {

		if(is_array($key)) {
			foreach ($key as $column => $value) {
				$comparate_with = $this->getComparateFieldValue($key);
				$this->Query['where_or'][] = $column.' '.$comparate_with."\"".$value."\"";
			}
		}
		else if(!empty($value)) {
			$comparate_with = $this->getComparateFieldValue($key);
			$this->Query['where_or'][] = $key.$comparate_with."\"".$value."\"";
		}
		else $this->Query['where_or'][] = $key;

		return $this;
	}

	/**
	 * Recupera de la base de dades 1 o més registres.
	 * Per defecte recupera només 1 registre.
	 * 
	 * @param  integer 		$limit Estableix el número de registres que recupera 
	 * @return Object this
	 */
	public function take($limit = 1) {
		$this->Query['limit'] = $limit;
		return $this;
	}

	/**
	 * Recupera el primer registre de la base de dades.
	 * Per defecte només recupera 1, pero podem passar per parametre quants necessitem.
	 * 
	 * @param  integer $limit Defineix el nombre de registres a recuperar.
	 * @return Object this
	 */
	public function first($limit = 1) {
		
		$this->Query['limit'] = $limit;
		$this->Query['orderby'] = " id ASC ";

		return $this;
	}

	/**
	 * Recuperem l'ultim registre de la base de dades.
	 * Per defecte recupera 1 pero podem passar per parametre el Limit.
	 * 
	 * @param  integer $limit 	Definexi el nombre de registres a recuperar.
	 * @return Object this
	 */
	public function last($limit = 1) {
		$this->Query['limit'] = $limit;
		$this->Query['orderby'] = ' id DESC ';

		return $this;
	}

	/**
	 * Recupera tots els registres de la base de dades.
	 * 
	 * @return Object this
	 */
	public function all() {
		
		$this->Query['limit'] = '';
		$this->Query['first'] = '';
		$this->Query['where'] = array();

		return $this;
	}

	/**
	 * Una consulta SQL amb group_by
	 *
	 * Ex. $Users->group('poblacio');
	 * 
	 * @param  String $group_by 	Definex el GROUP BY a la Query
	 * @return Object this
	 */
	public function group($group_by = '') {
		$this->Query['group'] = $group_by;
		return $this;
	}

	/**
	 * Recupera tots els registres entre 2 limits.
	 * Molt útil per paginació.
	 * 
	 * @param  integer $first 	Primer element a recuperar.
	 * @param  integer $limit 	Limit d'elements a partir del primer.
	 * @return Object this
	 */
	public function between($first = 0, $last = 20) {
		$this->Query['first'] 	= $first;
		$this->Query['last'] 	= $last;

		return $this;
	}

	/**
	 * Afegeix LEFT JOIN dins la Query
	 * 
	 * @param  string $string 	Consulta "LEFTJOIN.... ON ....."
	 * @return Object this
	 */
	public function join($string='') {
		$this->Query['leftjoin'][] = $value;

		return $this;
	}

	/**
	 * Defineix les taules de la Query
	 * 
	 * @param  String $string 	Nom de les taules separades per coma.
	 * @return Object this
	 */
	public function from($string) {
		$this->Query['from'][] = $string;
		return $this;
	}

	

	/**
	 * Executa una Query directa a la base de dades.
	 * 
	 * @param  String $query  	SQL
	 * @return Object 			Mysql Query
	 */
	public function raw_query($query) {
		return $this->db->query($query);
	}

	/**
	 * Executa la consulta SQL i retorna 1 Objecte o un Array segons 
	 * com s'ha composat l'strSQL.
	 *
	 * @param String $type 		Tipus de resultat que volem.
	 */
	public function exec($type = 'object') {

		$strSQL = $this->Query['action'];

		if(!is_array($this->Query['fields']) || empty($this->Query['fields'])) {

			 $this->Query['fields'][] = $this->getDBFields();	
		}

		$strSQL .= $this->composeFields();

		

		if($this->Query['from'] == '') $strSQL .= ' FROM '.$this->table;
		else $strSQL .= $this->composeFrom();
		
		if($this->Query['leftjoin'] != '') $strSQL .= $this->Query['leftjoin'];
		if($this->Query['innerjoin'] != '') $strSQL .= $this->Query['innerjoin'];
		
		if($this->Query['where'] != '' || $this->Query['where_or'] != '') {
			$strSQL .= $this->composeWhere();
		}
		
		if($this->Query['groupby'] != '') $strSQL .= ' GROUP BY '.$this->Query['groupby']; 
		if($this->Query['orderby'] != '') $strSQL .= ' ORDER BY '.$this->Query['orderby'];
		if($this->Query['limit'] != '') $strSQL .= ' LIMIT '.$this->Query['first'].','.$this->Query['limit'];






		// Query a executar
		$res = $this->db->query($strSQL);
		if($res->num_rows > 0){

			if($this->Query['limit'] != '1'){
				while($row = $res->fetch_assoc()) {
					if($type == 'object') $this->list_objects[] = $this->newInstance($row); 
					else $this->list_objects[] = (array) $this->newInstance($row); 
				}
			}
			else {
				
				$this->createInstance($res->fetch_assoc());
				if($this->populate_after_query) $this->populate();

			}
		}

		return $this;
	}

	

	/**
	 * Creació del SQL. "FROM"
	 * 
	 * @return String
	 */
	private function composeFrom() {
		$strFrom = ' FROM ';

		if(is_array($this->Query['from']) && !empty($this->Query['from'])){
			
			foreach ($this->Query['from'] as $i => $table) {

				if($i == 0) $strFrom .= $table;
				else $strFrom .= ', '.$table;
			}
		}

		return $strFrom;
	}

	/**
	 * Creació del SQL. "FIELDS"
	 * 
	  * @return String
	 */
	private function composeFields() {
		
		$strFields = '';
		foreach ($this->Query['fields'] as $i => $field) {
			
			$field = $this->table.'.'.$field;

			if($i == 0) $strFields .= ' '.$field;
			else $strFields .= ', '.$field;
		}

		return $strFields;

	}

	/**
	 * Creació del SQL. "WHERE"
	 * @return String
	 */
	private function composeWhere() {
		
		$strWhere = '';

		if(!empty($this->Query['where'])) {

			$strWhere = ' WHERE ';
			$i = 0;
			foreach ($this->Query['where'] as $string) {
				if($i == 0) $strWhere .= ' '.$string;
				else $strWhere .= ' AND '.$string;
				$i++;
			}
		}

		if(!empty($this->Query['where_or'])){
			if($strWhere == '') $strWhere = ' WHERE ';
			$i = 0;
			foreach ($this->Query['where_or'] as $string) {
				if($i == 0) $strWhere .= ' '.$string;
				else $strWhere .= ' OR '.$string;
				$i++;	
			}
		}


		return $strWhere;
	}


	/**
	 * Intenta retornar una array de tota la consulta SQL.
	 * 
	 * @return Array 
	 */
	public function getArray() {
		
		$this->exec();

		if($this->Query['limit'] == 1) return (array) $this->object;
		else return $this->list_objects;
	}


	/**
	 * Intenta retornar un objecte de la consulta SQL.
	 * @return Object
	 */
	public function getObject() {
		
		$this->exec();

		if($this->Query['limit'] == 1) return $this->object;
		else return $this->list_objects;
	}


	/**
	 * Crea una instancia individual per poder desar en un array o retornar de forma
	 * efimera, no afecta a l'atribut $this->object.
	 * 
	 * @param  Array $array 
	 * @return Object
	 */
	public function newInstance($array = '') {
		$object = array();

		if(is_array($array)){
			foreach($array as $col => $val){

				if(isset($this->attributes[$col])) $object[$col] = $val;
				else {
					foreach($this->attributes as $key => $props){

						if($key == $col) $object[$col] = $val;
						else if(is_array($props) && $props[$key]['column_name'] == $col) $object[$key] = $val;
					}
				}
			}
		}

		return (object) $object;
	}

	/**
	 * Inicialitza l'instancia del objecte $this->object.
	 * Amb tota la informació que esta definida al model.
	 * 
	 * @param  Array $array 
	 */
	public function createInstance($array) {

		$object = array();

		foreach($array as $col => $val){

			if(isset($this->attributes[$col])) $object[$col] = $val;
			else {
				foreach($this->attributes as $key => $props){

					if($key == $col) $object[$col] = $val;
					else if(is_array($props) && $props['column_name'] == $col) {
						$object[$key] = $val;
					}
				}
			}
		}

		$this->arrayObject = $object;
		$this->object = (object) $object;
	}

	/**
	 * Crea un nou objecte per manipular i desar.
	 * 
	 * @param  Array $array Array associatiu amb els atributs bàsics del Model.
	 * @return Object
	 */
	public function create($array = '') {
		return $this->newInstance($array);
	}

	/**
	 * Elimina l'objecte passat per parametre i com a referencia
	 * l'objecte deixa de existir en el contexte de la crida.
	 * 
	 * @return Boolean 
	 */
	public function remove(&$object) {
		
		$deleted = false;

		if(is_object($object)) $objectArray = (array) $object;

		$pk_field = $this->getPrimaryKeyField();
		$id = $objectArray[$pk_field];

		if($id > 0) {
			$result = $this->db->deleteOne($this->table, $id);
			$object = null; // delete this object instance.
			return ($result == 1);
		}
		
		return $delted;
	}


	/**
	 * Elimina tots els objectes de l'array passat per parametre,
	 * en cas de no passar parametre elimina tots els registres de la 
	 * taula.
	 * 
	 * @param  Array $array Array d'objectes que s'han d'eliminar.
	 * @return Boolean
	 */
	public function removeAll($array = '') {
	
		if($array == '') $this->db->deleteAll($this->table);
		else if(is_array($array)) {
			foreach ($array as $key => $value) {
				$this->remove($value);
			}
		}
	}

	/**
	 * Retorna quin es l'atribut que es el primary key
	 * 
	 * @return String
	 */
	private function getPrimaryKeyField() {
		
		$primary_field = 'id';
		$trobat = false;
		$keys = array_keys($this->attributes);
		
		while(!$trobat && !empty($keys)){
		  $key = array_pop($keys);
		  $trobat = (is_array($key) && $key['primary_key'] === true);
		};

		if($trobat) $primary_field = $key;

		return $primary_field;
	}



	/**
	 * Transforma un array que prové d'un model en 
	 * un array amb els camps reals de la taula que farem 
	 * l'acció.
	 * 
	 * @param  Object $object 	Objecte original definit al Model
	 * @return Array 			Array compatible amb la base de dades.
	 */
	private function to_SQLObject($object) {

		$sql_object = array();

		// Transform Model Array to SQL RAW ARRAY
		foreach ($this->attributes as $column => $props) {

			$value 	= '';
			$type 	= '';
			$key 	= '';

			if($props['ignore'] !== true){

				// Recuperem quin tipus de valor ha de ser.
				// En cas de ser un array hem de mirar el field 'type'
				// si no, es directament el valor que hi ha a $props
				if(is_array($props) && !empty($props['type'])) $type = $props['type'];
				else if(!is_array($props)) $type = $props;

				// Recuperem quin nom real te el camp
				if(is_array($props)){
					// Si existeix el camp I NO ES ABSTRACTE, 'column_name' aquest defineix la columna real de la base de dades
					if(!empty($props['column_name']) && $type != 'abstract') $key = $props['column_name'];
					else if($props['type'] != 'abstract') $key = $column; // en cas contrari, es el mateix key
				}
				else $key = $column;

				// Inicialitzem el valor, si es null.
				if($value == '') $value = $object[$column];

				/// Modifiquem els valors segons el seu tipus
				if(preg_match('/timestamp/', $type)) $value = date('Y-m-d g:i:s');
				else if(preg_match('/float/', $type)) $value = floatval($value);
				else if(preg_match('/int/', $type)) $value = intval($value);

				// Emplenem l'array només si hi ha una key.
				// els camps abstractes intentaran entrar pero no podran... :-)
				if(!empty($key)) {
					$sql_object[$key] = $value;
				}
			}
		}

		return $sql_object;
	}

	/**
	 * Guarda l'objecte actual a la base de dades.
	 *
	 * @param Object $object 		Objecte a desar.
	 * @return Object 				Objecte desat o inserit.
	 */
	public function save(&$object) {

		$pk_field = $this->getPrimaryKeyField();

		if(is_object($object)) $objectArray = (array) $object;

		$id = $objectArray[$pk_field];
		$to_save = $this->to_SQLObject($objectArray);
		
		if(!$id) {

			$this->db->insert($this->table, $to_save);
			$new_id = $this->db->getInsertId();

			if(is_object($object)) $object->$pk_field = $new_id;
			else $object[$pk_field] = $new_id;
			
			return $object;
		}
		else return $this->db->update($this->table, $id, $to_save);
	}
	
}

?>