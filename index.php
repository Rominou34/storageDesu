<?php

require('config.php');

/*
* DATABASE INITIALISATION
*/
class DB {

	private $host = DB_HOST;
	private $userName = DB_USER;
	private $password = DB_PASSWORD;
	private $dataBase = DB_DATABASE;
	private $bdd;

	// private $host = '10.0.216.66';
	// private $userName = 'qgandcom';
	// private $password = '10Qgandcom';
	// private $dataBase = 'site_qgandcom';
	// private $bdd;

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
		$req->execute($data);
		return $req;
	}

	public function queryClass($sql, $data = array(), $class){
		$req = $this->bdd->prepare($sql);
		$req->execute($data);
		$req->setFetchMode(PDO::FETCH_CLASS, $class);
		return $req->fetch();
	}
}

$bdd = new DB();

/*
* File upload
*/
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	// We check that a file was sent
	if(!empty($_FILES['file']['name'])) {
			 // Verifie si l'image est valide
		$target_dir = UPLOAD_DIR;
		$target_file = $target_dir . basename($_FILES["file"]["name"]);
		$file_name = basename($_FILES["file"]["name"]);
		$ext = pathinfo($target_file,PATHINFO_EXTENSION);
    $check = false;
    switch(FILTER_FILES) {
      case 'allow':
        // If the server only allows some files we check if the extension is in the allowed list
        if(in_array($ext, unserialize(ALLOWED_EXTENSIONS))) {
          $check = true;
        }
        break;
      case 'ban':
          if(!in_array($ext, unserialize(BANNED_EXTENSIONS))) {
            $check = true;
          }
          break;
      case 'all':
        $check = true;
        break;
      default:
        die("You must specify what type of files filtering you want");
    }

		if($check !== false) {
			$name = $file_name;
		} else {
      die("File type not allowed, sorry");
		}

		// Upload the file
		if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {

      $date = new DateTime(null, new DateTimeZone('Europe/London'));
      $date = $date->format('Y-m-d H:i:s');

			$sql = "INSERT INTO uploads ( name, last_accessed ) VALUES ( :name, :upload_time )";
      $values = array(
        "name" => $name,
        "upload_time" => $date
      );
      $bdd->queryEvent($sql, $values);
		} else {
				// ERROR
		}
  }
} else {
  if($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <body>
      <form method="post" action="" name="upload_file" id="upload_file" enctype="multipart/form-data">
        <input type="file" id="file" name="file">
        <input type="submit" name="submit" value="Upload">
      </form>
    </body>
    <?php
  }
}
?>
