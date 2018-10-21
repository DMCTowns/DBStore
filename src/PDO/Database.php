<?php
/**
 * PDO Database class
 * @author Diccon Towns <diccon@also-festival.com>
 */
namespace DMCTowns\DBStore\PDO;

/**
 * Class to access database using PDO
 * @package Reapit_Database_PDO
 */
class Database{

	/**
	 * Config set
	 * @var Config $_config
	 */
	protected $_config;

	/**
	 * PDO
	 * @var \PDO $_pdo
	 */
	protected $_pdo;

	/**
	 * Database name
	 * @var string $name
	 */
	public $name;

	/**
	 * Last query
	 * @var string $_lastQuery
	 */
	protected $_lastQuery;

	/**
	 * Last parameters
	 * @var array
	 */
	protected $_lastParams;

	/**
	 * Last error
	 * @var string
	 */
	protected $_lastErrorString;

	/**
	 * Constructor
	 * @param Config $config
	 */
	public function __construct(Config $config){
		$this->_config = $config;
		$this->name = $this->_config->database;
	}

	/**
	 * Destructor
	 */
	public function __destruct(){
		$this->_pdo = null;
	}

	/**
	 * Sleep method - clean up PDO object and omit from serialisation
	 * @return array
	 */
	public function __sleep(){
		if(is_object($this->_pdo)){
			$this->_pdo = null;
		}
		// returns array of properties to serialise
		return array('_config', 'name');
	}

	/**
	 * Sets character set
	 * @param string $set
	 */
	public function setCharacterSet($set){
		$this->_config->characterSet = $set;
	}

	/**
	 * Returns character set
	 * @return string
	 */
	public function getCharacterSet(){
		return $this->_config->characterSet;
	}

	/**
	 * Returns database name
	 * @return string
	 */
	public function getName(){
		return $this->_config->database;
	}

	/**
	 * Returns driver type
	 * @return string
	 */
	public function getDriverType(){
		return $this->_config->driver;
	}

	/**
	 * Executes statement
	 * @param string $statement
	 * @return integer
	 */
	public function execute($statement){
		if($pdo = $this->_getPDO()){
			try {
				$results = $pdo->exec($statement);
			}
			catch(\PDOException $e) {
				if (Env()->isEnabled('pdo_errors')) {
					\Reapit\ReapitWeb\MVC\Controllers\Error\PDOInternalErrorHandler::error(array(
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'message' => $statement . "<br /><br />". $e->getMessage(),
						)
					);


					\Reapit\ReapitWeb\MVC\Controllers\Error\PDOInternalErrorHandler::processPDOErrors();
				}
			}
			return $results;
		}
		return null;
	}

	/**
	 * Runs query
	 * @param string $query
	 * @param array $parameters
	 * @return \PDOStatement
	 */
	public function query($query, $parameters = array()){

		if($pdo = $this->_getPDO()){
			$this->_lastQuery = $query;
			$this->_lastParams = $parameters;
			$this->_lastErrorString = null;
			$statement = $this->getPreparedStatement($query);
			try{
				$success = $statement->execute($parameters);
			}catch(\Exception $e){

				$this->_lastErrorString = $e->getMessage();

				if (Env()->isEnabled('pdo_errors')) {
					\Reapit\ReapitWeb\MVC\Controllers\Error\PDOInternalErrorHandler::error(array(
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'message' => $statement->queryString . "<br /><br />". $e->getMessage(),
						)
					);

					\Reapit\ReapitWeb\MVC\Controllers\Error\PDOInternalErrorHandler::processPDOErrors();
				}
			}
			return $statement;
		}
		return null;
	}

	/**
	 * Returns prepared statement
	 * @param string $query
	 * @return \PDOStatement
	 */
	public function getPreparedStatement($query){
		if($pdo = $this->_getPDO()){
			$statement = $pdo->prepare($query, $this->_getDriverOptions());
			return $statement;
		}
		return null;
	}

	/**
	 * Returns last insert ID
	 * @param string $name
	 * @return string
	 */
	public function getLastInsertID($name=null){
		if($pdo = $this->_getPDO()){
			return $pdo->lastInsertId($name);
		}
		return null;
	}

	/**
	 * Returns PDO connection
	 * @return \PDO
	 */
	public function getConnection(){
		return $this->_getPDO();
	}


	/**
	 * Returns PDO object
	 * @return \PDO
	 */
	protected function _getPDO(){
		if(is_object($this->_pdo)){
			return $this->_pdo;
		}
		if($this->_config->username && $this->_config->password && ($dsn = $this->_getDSN())){
			try{
				$this->_pdo = new \PDO($dsn, $this->_config->username, $this->_config->password, $this->_getConectionOptions());
			}catch (\PDOException $e) {

				$this->_lastErrorString = $e->getMessage();
				return null;
			    //throw new \Exception('Could not connect to database ' . $dsn);
			}
			if(version_compare(PHP_VERSION, '5.3.6', '<') && strtolower($this->_config->driver) == 'mysql'){
				$this->_pdo->exec("set names " . $this->_config->characterSet);
			}
			return $this->_pdo;
		}

		throw new \Exception("Please define a host, database, username and password before attempting a connection.", 1);

		return null;
	}

	/**
	 * Returns DSN
	 * @return string
	 */
	protected function _getDSN(){
		if($this->_config->host){
			switch(strtolower($this->_config->driver)){
				case 'mysql':
					return 'mysql:host=' . $this->_config->host . (($this->_config->port) ? ';port=' . $this->_config->port  : '' ) . (($this->_config->database) ? ';dbname=' . $this->_config->database  : '' ) . ';charset=' . $this->_config->characterSet;
				case 'sqlsrv':
					return 'sqlsrv:Server=' . $this->_config->host . (($this->_config->port) ? ',' . $this->_config->port  : '' ) . (($this->_config->database) ? ';Database=' . $this->_config->database  : '' );
				case 'dblib':
					$port = '';
					if($this->_config->port){
						$port = (strtolower(substr(PHP_OS, 0, 3)) == 'win') ? ',' . $this->_config->port : ':' . $this->_config->port;
					}
					return 'dblib:host=' . $this->_config->host . $port . (($this->_config->database) ? ';dbname=' . $this->_config->database  : '' ) . ';charset=' . $this->_config->characterSet;
			}
		}
	}

	/**
	 * Returns connection options
	 * @return array
	 */
	protected function _getConectionOptions(){
		switch(strtolower($this->_config->driver)){
			case 'mysql':
				return array_merge(array(
 					\PDO::MYSQL_ATTR_COMPRESS => true
				), $this->_config->options);

			case 'dblib':
			case 'sqlsrv':
				return $this->_config->options;

		}
		return null;
	}

	/**
	 * Returns driver options
	 * @return array
	 * @todo These need attending to
	 */
	protected function _getDriverOptions(){
		switch(strtolower($this->_config->driver)){
			case 'mysql':
				$charset = strtolower($this->_config->characterSet);
				return array(
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $charset . "; SET character_set_results = '" . $charset . "', character_set_client = '" . $charset . "', character_set_connection = '" . $charset . "', character_set_database = '" . $charset . "', character_set_server = '" . $charset . "'"
				);
			case 'dblib':
			case 'sqlsrv':
				return array();

		}
		return array();
	}

	/**
	 * Returns true of table exists
	 * @param string $name
	 * @return boolean
	 */
	public function tableExists($name){
		switch(strtolower($this->_config->driver)){
			case 'mysql':
				$query = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema=:schema AND table_name=:name";
				break;
			case 'dblib':
			case 'sqlsrv':
				$query = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_catalog=:schema AND table_name=:name";
				break;
		}
		$parameters = array(':schema' => $this->name, ':name'=>$name);
		return (($result = $this->query($query, $parameters)) && ($row = $result->fetch()));
	}

	/**
	 * Returns true of procedure exists
	 * @param string $name
	 * @return boolean
	 */
	public function procedureExists($name){
		$query = "SHOW PROCEDURE STATUS WHERE Name = :name;";
 		$parameters = array(':name'=>$name);
		return (($result = $this->query($query, $parameters)) && ($row = $result->fetch()));
	}

	/**
	 * Returns new table object
	 * @return object Table
	 */
	public function getTable($name){
		return new Table($this, $name);
	}

	/**
	 * Tests data connection quietly
	 * @return boolean
	 */
	public function testConnection(){
		try{
			$this->query('SELECT 1');
		}catch(\Exception $e){
			return false;
		}
		return ($this->getLastErrorString()) ? false: true;
	}

	/**
	 * Returns last error
	 * @return string
	 */
	public function getLastErrorString(){
		if($this->_lastErrorString){
			return $this->_lastErrorString;
		}
		if($error = $this->getLastError()){
			return $error[2];
		}
		return null;
	}

	/**
	 * Returns last error
	 * @return string
	 */
	public function getLastError(){
		if(($pdo = $this->_getPDO()) && ($error = $pdo->errorInfo()) && $error[0] != '00000'){
			return $error;
		}
		return null;
	}

	/**
	 * Returns last query
	 * @return string
	 */
	public function getLastQuery(){
		return $this->_lastQuery;
	}

	/**
	 * Returns last params
	 * @return string
	 */
	public function getLastParameters(){
		return $this->_lastParams;
	}

	/**
	 * sets the PDO attribute
	 * @param int attribute
	 * @return int value
	 */
	public function setAttribute($attribute, $value) {
		$pdo = $this->_getPDO();

		$pdo->setAttribute($attribute, $value);
	}
}
?>
