<?php

namespace Reapit\Database;

interface ServerInterface{
	/**
	 * @param string host - server name
	 * @param string user - user name
	 * @param string password - password
	 * @param string port
	 */
	public function __construct($host, $user, $password, $port);

	/**
	 * Abstract - returns connection
	 * @param boolean force_new - causes new connection to be made
	 * @return resource database connection
	 */
	public function connect($force_new);

	/**
	 * Closes connection
	 */
	public function closeConnection();

	/**
	 * @return string host name
	 */
	public function getHost();

}

?>