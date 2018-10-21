<?php

namespace Reapit\Database;

abstract class Table implements TableInterface{

	/**
	 * @var object database
	 */
	protected $_database;

	/**
	 * @var string name
	 */
	public $name;

	/**
	 * @var string index
	 */
	protected $_primaryIndexField;

	/**
	 * @var array $columns
	 */
	protected $_columns;

	/**
	 * @param object $database
	 * @param string $name
	 */
	public function __construct(DatabaseInterface $database, $name){
		$this->_database = $database;
		$this->name = $name;
	}

	/**
	 * Connects table's database server
	 * @param boolean $force_new - forces a new connection
	 * @return resource $database connection
	 */
	protected function _connect($force_new=false){
		return $this->_database->connect($force_new);
	}

	/**
	 * Sets the primary index field used in the table
	 * @param string $primaryIndex
	 */
	public function setPrimaryIndexField($primaryIndex){
		$this->_primaryIndexField = $primaryIndex;
	}

	/**
	 * Returns primary index field used in the table
	 * @return string
	 */
	public function getPrimaryIndexField(){
		return $this->_primaryIndexField;
	}

	/**
	 * Runs query on table's connection - Abstract
	 * @param string $query
	 */
	public function query($query){
		return $this->_database->query($query);
	}

	/**
	 * Returns column object
	 * @param string $name
	 * @return ColumnInterface
	 */
	public function getColumn($name){
		if(($columns = $this->getColumns()) && isset($columns[$name])){
			return $columns[$name];
		}
		return null;
	}

	/**
	 * Returns database object
	 * @return Database
	 */
	public function getDatabase(){
		return $this->_database;
	}

	/**
	 * Returns table name
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	

}

?>
