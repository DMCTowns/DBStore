<?php
/**
 * Abstract Column class
 * @author Diccon Towns <dtowns@reapit.com>
 */

namespace Reapit\Database;

/**
 * Abstract class to describe column in database table
 * @package ReapitWeb_database
 */
abstract class Column implements ColumnInterface{

	/**
	 * Column's name
	 * @var string $name
	 */
	public $name;

	/**
	 * Column's type
	 * @var string $_type
	 */
	protected $_type;

	/**
	 * Column's length
	 * @var integer $_length
	 */
	protected $_length;

	/**
	 * Column's default value
	 * @var mixed $_default
	 */
	protected $_default;

	/**
	 * Column's index type
	 * @var string $_indexType
	 */
	protected $_indexType = null;

	/**
	 * Defines whether column will accept a null value
	 * @var boolean $_nullAllowed
	 */
	protected $_nullAllowed = false;

	/**
	 * Defines whether a column auto-increments
	 * @var boolean $_autoIncrements
	 */
	protected $_autoIncrements = false;

	/**
	 * Columns's attributes
	 * @var string $_attributes
	 */
	protected $_attributes;

	/**
	 * Column's extra data
	 * @var string extra
	 */
	protected $_extra;

	/**
	 * Constructor
	 * @param array $data
	 */
	public function __construct(array $data=null){
		if($data){
			$this->setData($data);
		}
	}

	/**
	 * Sets column data from array
	 * @param array $data
	 */
	public function setData(array $data){
		foreach($data as $key=>$value){
			$method_name = "set".ucfirst(strtolower($key));
			if(method_exists($this, $method_name)){
				$this->$method_name($value);
			}
		}
	}

	/**
	 * Sets column name
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
	}

	/**
	 * Returns column name
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Sets column type
	 * @param string $type
	 */
	public function setType($type){
		$this->_type = $type;
	}

	/**
	 * Returns column type
	 * @return string
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Sets column length
	 * @param integer length
	 */
	public function setLength($length){
		$this->_length = $length;
	}

	/**
	 * Gets column length
	 * @return mixed
	 */
	public function getLength(){
		return $this->_length;
	}

	/**
	 * Sets column default
	 * @param mixed $default
	 */
	public function setDefault($default){
		$this->_default = $default;
	}

	/**
	 * Gets column default
	 * @return mixed
	 */
	public function getDefault(){
		return $this->_default;
	}

	/**
	 * Sets column default
	 * @param string $indexType
	 */
	public function setIndexType($indexType){
		$this->_indexType = $indexType;
	}

	/**
	 * Gets column default
	 * @return string
	 */
	public function getIndexType(){
		return $this->_indexType;
	}

	/**
	 * Sets/Gets primary status
	 * @param boolean $boolean
	 * @return boolean
	 */
	public function isPrimary($boolean=null){
		if($boolean === null){
			return $this->_primary;
		}else{
			$this->_primary = (boolean) $boolean;
		}
	}

	/**
	 * Sets/Gets null allowed
	 * @param boolean $boolean
	 * @return boolean
	 */
	public function allowNull($boolean=null){
		if($boolean === null){
			return $this->_nullAllowed;
		}else{
			$this->_nullAllowed = (boolean) $boolean;
		}
	}

	/**
	 * Sets/Gets whether column auto increments
	 * @param boolean $boolean
	 * @return boolean
	 */
	public function autoIncrements($boolean=null){
		if($boolean === null){
			return $this->_autoIncrements;
		}else{
			$this->_autoIncrements = (boolean) $boolean;
		}

	}

	/**
	 * Sets column attributes
	 * @param string attributes
	 */
	public function setAttributes($attributes){
		$this->_attributes = $attributes;
	}

	/**
	 * Gets column attributes
	 * @return string
	 */
	public function getAttributes(){
		return $this->_attributes;
	}

	/**
	 * Sets column extra settings
	 * @param string $extra
	 */
	public function setExtra($extra){
		$this->_extra = $extra;
	}

	/**
	 * Gets column extra settings
	 * @return string
	 */
	public function getExtra(){
		return $this->_extra;
	}

}

?>