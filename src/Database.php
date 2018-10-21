<?php
/**
 * Abstract class to connect to database - requires predefined server object and name string
 * @author Diccon Towns
 */

namespace Reapit\Database;

abstract class Database implements DatabaseInterface{

	/**
	 * @var object Server
	 */
	protected $_server;

	/**
	 * @var string database name
	 */
	public $name;

	/**
	 * @param ServerInterface server
	 * @param string dbName
	 */
	public function __construct(ServerInterface $server, $name){
		$this->_server = $server;
		$this->name = trim($name);
	}

	/**
	 * closes server connection
	 */
	public function closeConnection(){
		if($this->_server){
			$this->_server->closeConnection();
		}
	}

	/** Returns server
	 * @return object server object
	 */
	public function getServer(){
		return $this->_server;
	}

	/**
	 * Returns database name
	 * @return string database name
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Returns new table object
	 * @return object table object
	 */
	public function getTable($name){
		return new Table($this, $name);
	}

}
?>