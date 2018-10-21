<?php
/**
 * PDO Record
 * @author Diccon Towns <diccon@also-festival.com>
 */
namespace DMCTowns\DBStore\PDO;

class Record{

	/**
	 * @var array $_fieldData
	 */
	protected $_fieldData = array();

	/**
	 * @var array $_keyFields
	 */
	protected $_keyFields;

	/**
	 * @var TableInterface $_table
	 */
	protected $_table;

	/**
	 * Table joins
	 * @var array
	 */
	protected $_joins;

	/**
	 * Is set true when data is synched to database, either by load or by save
	 * @var boolean $_synched
	 */
	protected $_synched = false;

	protected $_isNew = false;

	/**
	 * Is set true when data is loaded from database
	 * @var
	 */
	protected $_loadedFromDB = false;

	/**
	 * @param TableInterface $table
	 * @param array $fieldData
	 */
	public function __construct(Table $table=null, array $fieldData=null){
		$this->_isNew = true;
		if($table){
			$this->setTable($table);
		}
		if($fieldData){
			$this->setValues($fieldData);
		}
	}

	/**
	 * Sets table
	 * @param TableInterface
	 */
	public function setTable(Table $table){
		$this->_table = $table;
	}

	/**
	 * Sets table join
	 * @param mixed  $table
	 * @param string $field1
	 * @param string $field2
	 * @param string $type
	 */
	public function setJoin($table, $field1, $field2, $type='INNER'){
		if($table instanceof Table){
			$table = $table->getName();
		}
		$this->_joins[] = array('table' => $table, 'field1' => $field1, 'field2' => $field2, 'type' => $type);
	}

	/**
	 * Returns record's table object
	 * @return TableInterface
	 */
	public function getTable(){
		return $this->_table;
	}

	/**
	 * Gets column object for supplied field
	 * @param string $field
	 * @return ColumnInterface
	 */
	public function getFieldColumn($field){
		return $this->_table->getColumn($field);
	}

	/**
	 * Sets field data from array
	 * @param array $fieldData
	 */
	public function setValues(array $fieldData){
		foreach($fieldData as $field=>$data){
			$this->setValue($field,$data);
		}
	}

	/**
	 * Sets field value
	 * @param string $field
	 * @param mixed $data
	 */
	public function setValue($field,$data){
		if(!isset($this->_fieldData[$field]) || $this->_fieldData[$field] !== $data){
			$this->_fieldData[$field] = $data;
			$this->_synched = false;
		}
	}

	/**
	 * Returns field value
	 * @param string $field
	 */
	public function getValue($field){
		return (isset($this->_fieldData[$field])) ? $this->_fieldData[$field] : null;
	}

	/**
	 * Sets Keys - argument is array of field => $value
	 * @param array $keyPairs
	 */
	public function setKeys($keyPairs){
		$this->_keyFields = array();
		foreach($keyPairs as $field=>$value){
			$this->_keyFields[] = $field;
			if($value !== null){
				$this->setValue($field,$value);
			}
		}
	}

	/**
	 *  Gets ID key(s)
	 * @return array
	 */
	public function getKeys(){
		if(is_array($this->_keyFields) && count($this->_keyFields)){
			$keys = array();
			foreach($this->_keyFields as $field){
				if(null !== ($value = $this->getValue($field))){
					$keys[$field] = $value;
				}else{
					return null;
				}
			}
			return $keys;
		}
		return null;
	}

	/**
	 *  Gets ID value
	 * @return string
	 */
	public function getID(){
		if(is_array($this->_keyFields) && count($this->_keyFields)){
			$return = array();
			foreach($this->_keyFields as $field){
				$return[] = $this->getValue($field);
			}
			return implode('-',$return);
		}
		return null;
	}

	/**
	 *  Sets ID value
	 * @param string $id
	 * @return boolean
	 */
	public function setID($id){
		if(is_array($this->_keyFields) && count($this->_keyFields)){
			$this->setValue(reset($this->_keyFields),$id);
			return true;
		}
		return false;
	}

	/**
	 * Clears data from record
	 */
	public function clear(){
		$this->_fieldData = array();
		$this->_synched = false;
		$this->_loadedFromDB = false;
	}

	/**
	 * Returns true if reccord data is synched to database
	 * @param boolean $bool
	 * @return boolean
	 */
	public function isSynched($bool=null){
		if($bool !== null){
			$this->_synched = $bool;
			if($bool){
				$this->_loadedFromDB = true;
			}
		}else{
			return $this->_synched;
		}
	}

	/**
	 * Formats data ready for saving to database
	 */
	protected function _normalizeData(){
		if($columns = $this->_table->getColumns()){
			foreach($columns as $key=>$column){
				if(isset($this->_fieldData[$key])){
					$this->_normalizeField($key,$this->_fieldData[$key]);
				}
			}
		}
	}

	/**
	 * Returns record data in JSON format
	 * @return string
	 */
	public function getJSON(){
		$columns = $this->_table->getColumns();
		$json = array();
		foreach($this->_fieldData as $field=>$value){
			$data = '"'.$field.'": ';
			if($columns[$field] && $columns[$field]->isBoolean()){
				$data .= ($value) ? 'true' : 'false';
			}else if($columns[$field] && $columns[$field]->isNumeric()){
				$data .= $value;
			}else{
				$data .= '"' . addcslashes(preg_replace("/\r?\n/","\\n",$value),'"') . '"';
			}
			$json[] = $data;
		}
		return (count($json)) ? '{' . implode(", ",$json) . '}': null;
	}

	/**
	 * Returns record data as XML string
	 * @return string
	 */
	public function getXML(){

		$node_name = preg_replace('/^.*\\\\/','',get_class($this));

		$xml = "<".$node_name.">";

		$columns = $this->_table->getColumns();

		foreach($this->_fieldData as $field=>$value){

			if($columns[$field]){
				if($columns[$field]->isBoolean()){
					$value = ($value) ? '1' : '0';
				}else if($columns[$field]->isDateTime() && ($dateTime = $this->getDateTimeValue($field))){
					$value = $dateTime->format('Y-m-d\TH:i:sP');
				}else if($columns[$field]->isDate() && ($dateTime = $this->getDateTimeValue($field))){
					$value = $dateTime->format('Y-m-d');
				}else if($columns[$field]->isTime() && ($dateTime = $this->getDateTimeValue($field))){
					$value = $dateTime->format('H:i:s');
				}
			}
			if(stristr($value,'&') || stristr($value,'<') || stristr($value,'>')){
				$value = '<![CDATA[' . $value . ']]>';
			}
			$field = preg_replace('/\W/','',$field);
			$xml .= '<' . $field . '>' . $value . '</' . $field . '>';
		}

		$xml .= "</".$node_name.">";

		return $xml;
	}

	/**
	 * Sets date field value from either string, DateTime object or timestamp
	 * @param string $field
	 * @param mixed $datetime
	 */
	public function setDateTimeValue($field, $datetime){

		if($column = $this->getFieldColumn($field)){

			switch(strtoupper($column->getType())){
				case 'DATE':
					$format = 'Y-m-d';
					break;
				case 'DATETIME':
				case 'TIMESTAMP':
					$format = 'Y-m-d H:i:s';
					break;
				case 'TIME':
					$format = 'H:i:s';
					break;
				case 'YEAR':
					$format = ($column->getLength() == 2) ? 'y' : 'Y';
					break;
				default:
					$format = 'Y-m-d H:i:s';

			}

			if(is_object($datetime) && get_class($datetime) == 'DateTime'){ // we've been supplied DateTime object
				$this->setValue($field,$datetime->format($format));
				return;
			}

			if((integer) $datetime === $datetime){ // we've been supplied integer - assume timestamp
				$this->setValue($field,date($format, $datetime));
				return;
			}

			if($timestamp = strtotime($datetime)){ // we've successfully parsed string
				$this->setValue($field,date($format, $timestamp));
				return;
			}

			// set some default values
			$Y = '1970';
			$y = '70';
			$m = '01';
			$d = '01';
			$H = '00';
			$i = '00';
			$s = '00';

			// try to extract date info from string
			if(preg_match('/([\d]{2,4})(\/|\.|-)([\d]{2})(\/|\.|-)([\d]{2,4})/',$datetime,$matches)){
				if(strlen($matches[1]) > strlen($matches[5])){
					$Y = $matches[1];
					$d = $matches[5];
				}else{
					$Y = $matches[5];
					$d = $matches[1];
				}
				$m = $matches[3];

				if(strlen($y) < 4){
					$Y = ((integer)$Y < 56) ? '20' . $Y : '19' . $Y;
				}

				$y = substr($Y,-2);
			}

			// try to extract time info from string
			if(preg_match('/([\d]{2}):([\d]{2})(:([\d]{2}))?/',$datetime,$matches)){
				$H = $matches[1];
				$i = $matches[2];
				$s = ($matches[3]) ? $matches[4] : $s;
			}

			// replace values
			$searchArray = array('Y','y','m','d','H','i','s');
			$replaceArray = array($Y,$y,$m,$d,$H,$i,$s);

			$this->setValue($field,str_replace($searchArray,$replaceArray,$format));
			return;

		}else{
			$this->setValue($field, $datetime);
		}
	}

	/**
	 * Sets date field value from either string, DateTime object or timestamp
	 * @param string $field
	 * @return \DateTime
	 */
	public function getDateTimeValue($field){
		$value = $this->getValue($field);

		if(is_object($value) && stristr(get_class($value), 'DateTime')){
			return $value;
		}

		if(strtotime($value) && !preg_match('/^0000-00-00/', $value)){
			return new \DateTime($value);
		}

		return null;
	}


	/**
	 * Loads data from database - returns true on success
	 * @param array $options
	 * @return array
	 */
	protected function _getData(array $options=null){

		$options = ($options) ? $options : array();

		if(isset($options['keys'])){
			$this->setKeys($options['keys']);
		}

		if(!($keys = $this->getKeys())){
			return null;
		}

		// get columns
		$columns = (isset($options['columns'])) ? $options['columns'] : '*';

		// construct query
		$query = 'SELECT ' . $columns . ' FROM ' . $this->_getTableSQL() . ' WHERE ';

		$conditions = array();
		$parameters = array();
		foreach($keys as $column=>$value){
			$parameters[':' . $column] = $value;
			$conditions[] = $column . '=:' . $column;
		}
		$query .= implode(' AND ', $conditions);

		if(($result = $this->_table->query($query, $parameters)) && ($row = $result->fetch(\PDO::FETCH_ASSOC))){
			$this->_isNew = false;
			return $row;
		}
		return null;
	}

	/**
	 * Returns table SQL including any joins
	 * @return string
	 */
	protected function _getTableSQL(){
		$sql = $this->_table->getName();
		if(is_array($this->_joins)){
			foreach($this->_joins as $key => $params){
				$field1 = (preg_match('/\w\.\w/',$params['field1'])) ? $params['field1'] : $this->_table->getName() . '.' . $params['field1'];
				$field2 = (preg_match('/\w\.\w/',$params['field2'])) ? $params['field2'] : $params['table'] . '.' . $params['field2'];
				$sql .= ' ' . strtoupper($params['type']) . ' JOIN ' . $params['table'] . ' ON ' . $field1 . '=' . $field2;
			}
		}
		return $sql;
	}

	/**
	 * Loads data from database - returns true on success
	 * @param array $options
	 * @return boolean
	 */
	public function load(array $options=null){

		if($this->_loadedFromDB){
			return true;
		}

		if($row = $this->_getData($options)){
			$this->setValues($row);
			$this->_synched = true;
			$this->_loadedFromDB = true;
			return true;
		}

		return false;
	}

	/**
	 * Saves data to database
	 * @param array $fieldData
	 */
	public function save(array $fieldData=null){

		if($fieldData){
			$this->setValues($fieldData);
		}

		$database = $this->_table->getDatabase();
		$method = 'INSERT';

		// check to see if record exists to determine save type
		if(($keys = $this->getKeys()) && $this->_getData(array('columns' => key($keys)))){
			$method = 'UPDATE';
			foreach($this->_keyFields as $indexField){
				unset($this->_fieldData[$indexField]);
			}
		}

		$this->_normalizeData();

		// create columns and parameters for PDO
		$columns = array();
		$parameters = array();
		foreach($this->_fieldData as $field=>$value){
			if($column = $this->getFieldColumn($field)){
				if($value !== null){
					$marker = ':' . $field;
					$columns[$field] = $marker;
					switch(strtoupper($column->getType())){
						case 'BIT':
						case 'INTEGER':
						case 'INT':
						case 'SMALLINT':
						case 'TINYINT':
						case 'MEDIUMINT':
						case 'BIGINT':
							$parameters[$marker] = (int)$value;
							break;

						case 'DECIMAL':
						case 'NUMERIC':
						case 'FLOAT':
						case 'DOUBLE':
							$parameters[$marker] = (float)$value;
							break;

						default:
							$parameters[$marker] = $value;
					}
				}else{
					$columns[$field] = 'NULL';
				}
			}


		}


		if($method == 'INSERT'){
			$columnList = ($this->_table->getDatabase()->getDriverType() == 'sqlsrv' || $this->_table->getDatabase()->getDriverType() == 'dblib') ? '([' . implode('], [', array_keys($columns)) . '])' : '(' . implode(', ', array_keys($columns)) . ')';
			$query = $method . ' INTO ' . $this->_table->getName() . $columnList . ' VALUES (' . implode(', ', $columns) . ')';
		}else{
			$query = $method . ' ' . $this->_table->getName() . ' SET ';
			$columnData = array();
			foreach($columns as $col=>$marker){
				$columnData[] .= ($this->_table->getDatabase()->getDriverType() == 'sqlsrv' || $this->_table->getDatabase()->getDriverType() == 'dblib') ? '[' . $col . ']=' . $marker : $col . '=' . $marker;
			}
			$query .= implode(', ',$columnData) . ' WHERE ';

			$conditions = array();
			foreach($keys as $column=>$value){
				$parameters[':' . $column] = $value;
				$conditions[] = $column . '=:' . $column;
			}
			$query .= implode(' AND ', $conditions);

		}

		$statement = $this->_table->query($query, $parameters);
		$this->_isNew = false;

		// get ID if we've just run an insert
		if($method == 'INSERT' && count($this->_keyFields) == 1 && !$this->getValue($this->_keyFields[0]) && ($column = $this->getFieldColumn($this->_keyFields[0])) && $column->autoIncrements() && ($id=$this->getTable()->getDatabase()->getLastInsertID())){

			$this->setKeys(array($this->_keyFields[0] => $id));

		}else{
			if(is_array($keys)){
				foreach($keys as $column=>$value){
					$this->setValue($column, $value);
				}
			}
		}

	}

	/**
	 * Deletes record from database
	 * @return boolean
	 */
	public function delete(){
		if($keys = $this->getKeys()){
			$database = $this->_table->getDatabase();
			$query = 'DELETE FROM ' . $this->_table->getName() . ' WHERE ';

			$conditions = array();
			$parameters = array();
			foreach($keys as $column=>$value){
				$parameters[':' . $column] = $value;
				$conditions[] = $column . '=:' . $column;
			}
			$query .= implode(' AND ', $conditions);

			$this->_table->query($query, $parameters);
			$this->_isNew = true;
			return true;
		}
		return false;
	}

	/**
	 * Sets a field's value in the correct format ready for saving. Returns normalised value
	 * @param string $field
	 * @param mixed $value
	 * @return mixed
	 */
	protected function _normalizeField($field,$value){
		if($column = $this->getFieldColumn($field)){
			switch(strtoupper($column->getType())){
				case 'DATE':
				case 'DATETIME':
				case 'TIMESTAMP':
				case 'TIME':
				case 'YEAR':
					$this->setDateTimeValue($field, $value);
					break;
				case 'BIT':
					$this->setValue($field, base_convert((integer)$value, 10, 2));
					break;
				case 'ENUM':
					if($column->getLength() == '1,0' || $column->getLength() == '0,1'){
						$this->setValue($field, ($value) ? '1' : '0');
					}
					break;
				case 'INTEGER':
				case 'INT':
				case 'SMALLINT':
				case 'TINYINT':
				case 'MEDIUMINT':
				case 'BIGINT':
					if(is_numeric($value)){
						$this->setValue($field, (integer)round($value));
					}else{
						$this->setValue($field, null);
					}
					break;
				case 'DECIMAL':
				case 'NUMERIC':
				case 'FLOAT':
				case 'DOUBLE':
					if(is_numeric($value)){
						$this->setValue($field, (float) $value);
					}else{
						$this->setValue($field, null);
					}
					break;
				default:
					$this->setValue($field,$value);
			}

		}else{
			$this->setValue($field,null);
		}
		return $this->getValue($field);
	}

	public function isNew() {
		return $this->_isNew;
	}
}

?>
