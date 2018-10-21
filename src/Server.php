<?php
/**
 * Class to connect to MySQL server
 * @author Diccon Towns
 */

namespace DMCTowns\DBStore;

abstract class Server implements ServerInterface{

	/**
	 * @var string host
	 */
	protected $_host;

	/**
	 * @var string user
	 */
	protected $_user;

	/**
	 * @var string password
	 */
	protected $_password;

	/**
	 * @var string password
	 */
	protected $_port;

	/**
	 * @var resource connection
	 */
	protected $_connection;

	/**
	 * @param string host - server name
	 * @param string user - user name
	 * @param string password - password
	 * @param string port
	 */
	public function __construct($host, $user, $password, $port=null){
		$this->_host = trim($host);
		$this->_user = trim($user);
		$this->_password = trim($password);
		if($port){
			$this->_port = trim($port);
		}
	}

	/**
	 * @return string host name
	 */
	public function getHost(){
		return $this->_host;
	}

}
?>