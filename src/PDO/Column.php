<?php
/**
 * PDO Column
 * @author Diccon Towns <diccon@also-festival.com>
 */
namespace DMCTowns\DBStore\PDO;

class Column extends \DMCTowns\DBStore\Column{

	/**
	 * Gets column SQL
	 * @param array $options
	 * @return string
	 */
	public function getSQL(array $options=null){
		$sql = ''.$this->getName().' ';

		$type = ($this->getType()) ? strtoupper($this->getType()) : 'VARCHAR';

		$sql .= $type;

		$fixedLengthFields = array('TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT');

		if(($length = $this->getLength()) && !in_array($type, $fixedLengthFields)){
			if($type == 'ENUM'){
				$length = preg_replace('/(?<=,|\A)([^"\',]+)(?=,|\Z)/','\'$1\'',$length);
			}
			$sql .= '('.$length.')';
		}else if($type == 'VARCHAR' || $type == 'CHAR'){
			$sql .= '(11)';
		}

		if($attributes = $this->getAttributes()){
			$sql .= ' '.$attributes;
		}

		$sql .= ($this->allowNull()) ? ' NULL' : ' NOT NULL';

		if($this->autoIncrements()){
			$sql .= ' auto_increment';
		}

		if($extra = $this->getExtra()){
			$sql .= ' '.$extra;
		}

		if($options['include_index'] && $this->getIndexType() == 'PRIMARY'){
			$sql .= ' PRIMARY KEY';
		}

		if($default = $this->getDefault()){
			$sql .= ' DEFAULT ';
			switch(strtoupper($default)){
				case 'NULL':
				case 'CURRENT_TIMESTAMP':
					$sql .= strtoupper($default);
					break;
				default:
					$sql .= '\''.$default.'\'';
			}
		}

		return $sql;
	}

	/**
	 * Returns whether column is a boolean field
	 * @return boolean
	 */
	public function isBoolean(){
		return (($this->getType() == 'ENUM' && ($this->getLength() == '0,1' || $this->getLength() == '1,0')) || ($this->getType() == 'BIT' && $this->getLength() == 1));
	}

	/**
	 * Returns true if column is numeric
	 * @return boolean
	 */
	public function isNumeric(){
		switch(strtolower($this->getType())){
			case "int":
			case "tinyint":
			case "smallint":
			case "mediumint":
			case "int":
			case "bigint":
			case "float":
			case "double":
			case "double precision":
			case "real":
			case "decimal":
			case "numeric":
			case "bit":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns true if column is date
	 * @return boolean
	 */
	public function isDate(){
		switch(strtolower($this->getType())){
			case "date":
			case "datetime":
			case "timestamp":
			case "time":
			case "year":
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns true if column is date-time
	 * @return boolean
	 */
	public function isDateTime(){
		switch(strtolower($this->getType())){
			case 'datetime':
			case 'timestamp':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns true if column is time
	 * @return boolean
	 */
	public function isTime(){
		switch(strtolower($this->getType())){
			case 'datetime':
			case 'timestamp':
			case 'time':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns true if column is text
	 * @return boolean
	 */
	public function isText(){
		switch(strtolower($this->getType())){
			case "longtext":
			case "mediumtext":
			case "text":
			case "tinytext":
				return true;
			default:
				return false;
		}
	}


}
?>