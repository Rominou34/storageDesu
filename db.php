<?php
/*
* DATABASE INITIALISATION
*/
class DB {

	private $host = DB_HOST;
	private $userName = DB_USER;
	private $password = DB_PASSWORD;
	private $dataBase = DB_DATABASE;
	private $bdd;

	public function __construct($host = null, $userName = null, $password = null, $dataBase = null){
		if ($host != null) {
			$this->host = $host;
			$this->userName = $userName;
			$this->password = $password;
			$this->dataBase = $dataBase;
		}

		try {
			$this->bdd = new PDO('mysql:host='.$this->host.';dbname='.$this->dataBase, $this->userName, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8', PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
		} catch(PDOException $e){
			die('<h1><center>Unable to connect to the database</center></h1>');
		}
	}

	public function query($sql, $data = array()){
		$req = $this->bdd->prepare($sql);
		$req->execute($data);
		return $req->fetchAll(PDO::FETCH_OBJ);
	}

	public function queryOne($sql, $data = array()){
		$req = $this->bdd->prepare($sql);
		$req->execute($data);
		return $req->fetch(PDO::FETCH_OBJ);
	}

	public function queryCount($sql, $data = array()){
		$req=$this->bdd->prepare($sql);
		$req->execute($data);
		return $count = $req->rowCount();
	}

	public function queryEvent($sql, $data = array()){
		$req = $this->bdd->prepare($sql);
		return $req->execute($data);
	}

	public function queryClass($sql, $data = array(), $class){
		$req = $this->bdd->prepare($sql);
		$req->execute($data);
		$req->setFetchMode(PDO::FETCH_CLASS, $class);
		return $req->fetch();
	}
}
?>
