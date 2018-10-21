<?php
/**
 * PDO Config
 * @author Diccon Towns <dtowns@reapit.com>
 */
namespace Reapit\Database\PDO;

class Config{

	/**
	 * Character set
	 * @var string $_characterSet
	 */
	public $characterSet = 'utf8';

	/**
	 * Host
	 * @var string $_host
	 */
	public $host;

	/**
	 * Host
	 * @var string $_port
	 */
	public $port = '3306';

	/**
	 * Database
	 * @var string $_database
	 */
	public $database;

	/**
	 * Driver
	 * @var string $_driver
	 */
	public $driver = 'mysql';

	/**
	 * Username
	 * @var string $_username
	 */
	public $username;

	/**
	 * Password
	 * @var string $_password
	 */
	public $password;

	/**
	 * Options
	 * @var array $_options
	 */
	public $options = array();

	/**
	 * Constructor
	 * @param array $config
	 * Array of config options including:
	 * host - hostname of server
	 * port - port service is available on
	 * database - name of database
	 * username
	 * password
	 * driver - PDO driver to use
	 * options - Array of PDO options
	 * characterSet - the chqracter set in use by the connection
	 */
	public function __construct($config){
		if(isset($config['host'])){
			$this->host = $config['host'];
		}
		if(isset($config['port'])){
			$this->port = $config['port'];
		}
		if(isset($config['database'])){
			$this->database = $config['database'];
		}
		if(isset($config['username'])){
			$this->username = $config['username'];
		}
		if(isset($config['password'])){
			$this->password = $config['password'];
		}
		if(isset($config['driver'])){
			$this->driver = $config['driver'];
		}
		if(isset($config['options']) && is_array($config['options'])){
			$this->options = $config['options'];
		}
		if(isset($config['characterSet'])){
			$this->characterSet = $config['characterSet'];
		}
	}


}
