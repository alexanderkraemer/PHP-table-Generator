<?php 
class Database
{
	public $isConnected;
	public $conn;
	
	public function __construct()
	{
		$this->isConnected = true;
		try 
		{ 
			$username 	= 'root';
			$passwort	= 'password';
			$host 		= 'localhost';
			$db 		= 'testdatenbank';
			$this->conn = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $username, $passwort); 
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
			$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		} 
		catch(PDOException $e) 
		{ 
			$this->isConnected = false;
			throw new Exception($e->getMessage());
		}
	}
	
	public function disconnect()
	{
		$this->conn = null;
		$this->isConnected = false;
	}
}
?>
    