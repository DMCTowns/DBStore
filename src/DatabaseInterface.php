<?php
 /**
 * Abstract class to connect to database - requires predefined server object and name string
 * @author Diccon Towns
 */

namespace Reapit\Database;

interface DatabaseInterface{

	/**
	 * @param ServerInterface server
	 * @param string dbName
	 */
	public function __construct(ServerInterface $server, $name);

	/**
	 * Connects to server and selects database - Abstract
	 * @param boolean forceNew - forces new connection
	 * @return object server object
	 */
	public function connect($forceNew);

	/**
	 * closes server connection
	 */
	public function closeConnection();

	/**
	 * performs query - Abstract
	 * @param string query
	 * @return resource
	 */
	public function query($query);

	/** Returns server
	 * @return object server object
	 */
	public function getServer();

	/**
	 * Returns database name
	 * @return string database name
	 */
	public function getName();

	/**
	 * Returns new table object
	 * @return object table object
	 */
	public function getTable($name);

	/**
	 * Returns true if table exists in database
	 * @return boolean
	 */
	public function tableExists($name);

	/**
	 * Returns array of table data
	 * @return array
	 */
	public function getTableList();

}
?>