<?php
class SCDB
{
	public $pdo;
	private $setting;
	private $error;
	function __construct(){
		$this->connect();
	}
	function connect(){

		if (!$this->pdo) {
			$this->setting = parse_ini_file("setting.ini.php");
			//$dsn = 'sqlsrv:server=' . $this->setting["dbName"] . ';host=' . $this->setting["dbHost"] . ';charset=utf8';
			try {
				//$this->pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_PERSISTENT => true)); //ขอเชื่อมต่อแบบถาวร
				//$this->pdo = new PDO($dsn, $this->setting["dbUser"], $this->setting["dbPw"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$this->pdo = new PDO( "sqlsrv:server= ".$this->setting["dbHost"] ."; Database= ".$this->setting["dbName"]."", $this->setting["dbUser"],$this->setting["dbPw"]);  
				
				return true;
			} catch (PDOException $e) {
				$this->error = $e->getMessage();
				die($this->error);
				return false;
			}
		} else {
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			return true;
		}
	}

	function prepare($query)
	{
		return $this->pdo->prepare($query);
	}

	function execute($query, $values  = null)
	{
		if ($values == null) {
			$values = array();
		} else if (!is_array($values)) {
			$values = array($values);
		}
		$stmt = $this->prepare($query);
		$stmt->execute($values);
		$this->error = $stmt->errorInfo()[2];
		if (empty($this->error)) {
			return $stmt;
		} else {
			//die($this->error);
			return false;
		}
	}

	function fetch($query, $values = null)
	{
		if ($values == null) {
			$values = array();
		} else if (!is_array($values)) {
			$values = array($values);
		}
		$stmt = $this->execute($query, $values);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	function fetchAll($query, $values = null, $key = null)
	{
		if ($values == null) {
			$values = array();
		} else if (!is_array($values)) {
			$values = array($values);
		}
		$stmt = $this->execute($query, $values);
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($key != null && $results[0][$key]) {
			$keyed_results = array();
			foreach ($results as $result) {
				$keyed_results[$result[$key]] = $result;
			}
			$results = $keyed_results;
		}
		return $results;
	}

	function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}

	public function beginTransaction()
	{
		return $this->pdo->beginTransaction();
	}

	public function commit()
	{
		return $this->pdo->commit();
	}

	public function rollBack()
	{
		return $this->pdo->rollBack();
	}

	public function getError()
	{
		return $this->error;
	}

	function close()
	{
		$this->pdo = null;
	}
}
