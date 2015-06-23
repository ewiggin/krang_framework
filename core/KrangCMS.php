<?php 
/**
 * Bella 
 * a newest CMS system for simple and beautiful sites.
 *
 * @author Mario Martínez <mario@javajan.com>
 */

class KrangCMS
{
	var $page;
	var $db;
	var $lang;

	function __construct($debug = false)
	{
		global $db;
		$this->db = $db;
		$this->lang = lang;
		
		// debug
		$this->setDebug($debug);
	}

	public function setDebug($op=false)
	{
		$this->db->setDebug($op);
	}

	public function get($key){
		return $this->page[$key];
	}

	/**
	 * Recupera una pagina de la base de dades
	 * amb totes les seves propietats i la converteix en 
	 * un objecte standard. 
	 * 
	 * @param  integer $id 	Identificador de la pagina.
	 * @return [type]      	Object
	 */
	public function getPage($id=0){
		
		if($this->db != null){
			if(!$id && isset($_GET['page_id']) && !empty($_GET['page_id'])) $id = $this->db->escape_string($_GET['page_id']);
			else if(!$id && isset($_GET['u'])) $slug = $this->db->escape_string($_GET['u']);
			else if(is_string($id)) $slug = $this->db->escape_string($id);

			if($slug) $object = $this->db->getObject('pages', '*', 'WHERE url_'.$this->lang.' LIKE "'.$slug.'"');
			else $object = $this->db->getObjectById('pages', $id);

			if($object != null) {
				// Aixo s'ha d'arreclar afegint un camp a la taula "lang" => 'es'
				$aux = get_object_vars($object);
				foreach ($aux as $key => $value) {
					$clean_field = strstr($key, '_'.$this->lang, true);
					if($clean_field){
						$key = $clean_field;
					}

					$result[$key] = html_entity_decode($value);
				}

				$this->page = $result;
			}
		}

		return $this;
	}

	/**
	 * Recupera un objecte d'un tipus concret.
	 * 
	 * @param  [type] $className [description]
	 * @param  [type] $id        [description]
	 * @return [type]            [description]
	 */
	public function getObject($className, $id){
		return $this->db->getObject($className, $id);
	}

	/**
	 * Recupera un registre de la base de dades
	 * filtrat per $id
	 * 
	 * @param  [type] $tableName [description]
	 * @param  [type] $id        [description]
	 * @return [type]            [description]
	 */
	public function getOne($tableName, $id) {
		return $this->db->getOne($tableName, $id);
	}

	/**
	 * Recupera una llista d'objectes que no son pagines.
	 * 
	 * @param  [type] $className [description]
	 * @return [type]            [description]
	 */
	public function getAll($className){
		return $this->db->getAll($className, '*');
	}

	/**
	 * Retorna un array associatiu amb totes les dades
	 * que son dins de la consulta.
	 * 
	 * @param  [type] $tableName [description]
	 * @param  string $fields    [description]
	 * @param  string $where     [description]
	 * @return [type]            [description]
	 */
	public function getArray($tableName, $fields = '*', $where = '') {
		return $this->db->getArray($tableName, $fields, $where);
	}


	public function getMedia($table, $table_fk, $type=0, $limit=0){

		if($type) $type_where = ' AND media.type LIKE "'.$type.'" ';
		if($limit > 0) $limit_where = ' LIMIT '.$limit;

		$strSQL = 'SELECT * FROM media WHERE media.table_fk = '.$table_fk.' AND media.table LIKE "'.$table.'" '.$type_where.' '.$limit_where;
		$res = $this->db->query($strSQL);
		while ($row = $res->fetch_assoc()) {
			$media[$row['type']][] = $row;
		}

		return $media;
	}
}

?>