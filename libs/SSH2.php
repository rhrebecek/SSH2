<?php

/**
 * SSH2 libs
 * 
 * @author Radek Hřebeček <rhrebecek@gmail.com>
 * @copyright  Copyright (c) 2012 Radek Hřebeček (https://github.com/rhrebecek)
 * @license New BSD License
 * @link https://github.com/rhrebecek/SSH2
 *
 * @example
 * 	Working with an object is very simple
 * 		
 * ////////////////////////////////////////////////
 * 
 * 		include 'SSH2.php';
 * 
 * 		$ssh = new \SSH2;
 * 		$ssh->login('root', 'password', 'example.com');
 * 		$ssh->exec('ls --all');
 * 
 * 		echo $ssh->getOutput();
 * 
 * /////////////////////////////////////////////////
 */

class SSH2 {
	/**
	 * default port to ssh port
	 * 
	 * @var int
	 */
	const PORT = 22;

	/**
	 * connection
	 */
	private $connection;

	/**
	 * stream
	 */
	private $stream = array();

	/**
	 * count exec
	 * 
	 * @var int
	 * @static
	 */
	private static $countExec = 0;

	/**
	 * class initialization
	 */
	public function __construct()
	{
		if(!function_exists('ssh2_connect')) {
			throw new NotSupportedException('Not supported SSH2 libs, please install the SSH2 extension for PHP5');
		}
	}

	/**
	 * login function
	 * 
	 * @param string $user
	 * @param string $password
	 * @param string $hostname
	 * @param int $port|22
	 * @return this
	 */
	public function login($user, $password, $hostname, $port = self::PORT)
	{
		$this->connect($hostname, $port);
		$this->authPassword($user, $password);

		return $this;
	}

	/**
	 * connect to ssh2 server
	 * 
	 * @param string $hostname
	 * @param int $port|22
	 * @return $this
	 */
	public function connect($hostname, $port = self::PORT) 
	{
		$this->connection = @ssh2_connect($hostname, $port);
		
		// no connection
		if(!$this->connection) {
			throw new NotConnectionException("Can not connected to '$hostname:$port'");
		}

		return $this;
	}

	/**
	 * authenticator user to ssh server
	 * 
	 * @param string $user
	 * @param string $password
	 * @return $this
	 */
	public function authPassword($user, $password)
	{
		if(!@ssh2_auth_password($this->connection, $user, $password)) {
			throw new NotAuthorizationException('Authorization failed');
		}

		return $this;
	}

	/**
	 * exec command to send server
	 * 
	 * @param string $command
	 * @return $this
	 */
	public function exec($command)
	{
		$this->stream[self::$countExec] = ssh2_exec($this->connection, $command);

		stream_set_blocking($this->stream[self::$countExec], TRUE);

		self::$countExec++;

		return $this;
	}

	/**
	 * count exec command
	 * 
	 * @return int
	 */
	public function count()
	{
		return self::$countExec;
	}

	/**
	 * get output ssh server
	 * 
	 * @return string
	 */
	public function getOutput($line = NULL)
	{
		$lines = array();

		foreach ($this->stream as $stream) {
			while ($result = fgets($stream)) {
				$lines[] = $result;
			}
		}

		return !is_null($line) ? $lines[$line] : $lines;
	}

	/**
	 * get output to html
	 * 
	 * @return string
	 */
	public function getOutputBlackScreen()
	{
		echo "<html><body style=\"background-color:black; color:white; font-family:monospace;\">";

		foreach($this->stream as $stream) {

			echo "<table style=\"border-bottom:3px #CECECE dotted; width:100%;\">";
			while ($result = fgets($stream)) {
				$result = htmlSpecialChars($result); 

				echo "<tr><td>" .  trim($result) . "</td></tr>";
			}

			echo "</table>";
		}

		echo "</html></body>";
	}

	/**
	 * disconnect from SSH2 server
	 */
	public function disconnect()
	{	
		if (function_exists('ssh2_disconnect')) {
			ssh2_disconnect($this->connection);
		} else {
			@fclose($this->connection);
			unset($this->connection);
		}
	}

	/**
	 * destruct
	 */
	public function __destruct()
	{
		$this->disconnect();
	}
}