<?php

namespace DMCTowns\DBStore;


/**
 * @todo Method to set default values
 */
abstract class Record implements RecordInterface{

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
	 * Is set true when data is synched to database, either by load or by save
	 * @var boolean $_synched
	 */
	protected $_synched = false;

	/**
	 * Is set true when data is loaded from database
	 * @var
	 */
	protected $_loadedFromDB = false;

	/**
	 * @param TableInterface $table
	 * @param array $fieldData
	 */
	public function __construct(TableInterface $table=null, array $fieldData=null){
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
	public function setTable(TableInterface $table){
		$this->_table = $table;
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
			$return = array();
			foreach($this->_keyFields as $field){
				$return[$field] = $this->getValue($field);
			}
			return $return;
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
	 * Returns SQL string for WHERE clause
	 * @return string
	 */
	protected function _getKeyWhereClause(){
		if(is_array($this->_keyFields) && count($this->_keyFields)){
			$return = array();
			foreach($this->_keyFields as $field){
				if(($value = $this->getValue($field)) !== null){
					$return[] = $field . '=\'' . $this->getValue($field) . '\'';
				}else{
					//throw new Exception('Error: \DMCTowns\DBStore\MySQL\Record::_getKeyWhereClause - no value set for '.$field);
					return null;
				}
			}
			return implode(' AND ', $return);
		}
		return null;
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
	 * Sets a field's value in the correct format ready for saving. Returns normalised value
	 * @param string $field
	 * @param mixed $value
	 * @return mixed
	 */
	abstract protected function _normalizeField($field,$value);

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
				}else if($columns[$field]->isDate() && ($dateTime = $this->getDateTimeValue($field))){
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
}

?>