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

class Upload {
	private $name;
	private $last_accessed;

	public function getName() {
		return $this->name;
	}

	public function getLastAccessed() {
		return $this->last_accessed;
	}

	public function __construct($name = NULL, $last_accessed = NULL) {
		 if(!is_null($name) && !is_null($last_accessed)) {
			 $this->name = $name;
			 $this->last_accessed = $last_accessed;
		 }
	}
}

$bdd = new DB();

/*
***** FUNCTIONS *****
*/

/*
* Returns the location of the user on the website
*/
function getCurrentUri()
{
	$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
	$uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));
	if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
	$uri = '/' . trim($uri, '/');
	return $uri;
}

/*
* Here we get the uri and transform it into an array for easier routing
*/
function uriToArray($base_url) {
	$base_url = getCurrentUri();
  $routes = array();
  $routes = explode('/', $base_url);
  $rout = array();
	foreach($routes as $route)
	{
    if(trim($route) != '')
			array_push($rout, $route);
	}
	return $rout;
}

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

		if($check == false) {
			die("File type not allowed, sorry");
		}

		// Upload the file
		if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {

      $date = new DateTime(null, new DateTimeZone('Europe/London'));
      $date = $date->format('Y-m-d H:i:s');

			$sql = "INSERT INTO uploads ( name, last_accessed ) VALUES ( :name, :upload_time )";
      $values = array(
        "name" => $file_name,
        "upload_time" => $date
      );
      $bdd->queryEvent($sql, $values);
		} else {
				// ERROR
		}
  }
} else {
  if($_SERVER['REQUEST_METHOD'] == 'GET') {
		// We get the location of the user
		$url = getCurrentUri();
		$route = uriToArray($url);
		// '/'
		switch(count($route)) {
			case 0:
		    ?>
		    <body>
		      <form method="post" action="" name="upload_file" id="upload_file" enctype="multipart/form-data">
		        <input type="file" id="file" name="file">
		        <input type="submit" name="submit" value="Upload">
		      </form>
		    </body>
		    <?php
				break;
			// '/*'
			case 1:
				// '/index.php'
				if($route[0] == 'index.php') {
					?>
					<body>
			      <form method="post" action="" name="upload_file" id="upload_file" enctype="multipart/form-data">
			        <input type="file" id="file" name="file">
			        <input type="submit" name="submit" value="Upload">
			      </form>
			    </body>
					<?php
				} else {
					$file_url = UPLOAD_DIR.$route[0];
					$sql = "SELECT * FROM uploads WHERE name = :name";
					$values = array( "name" => $route[0]);
					$file = $bdd->queryClass($sql, $values, 'Upload');
					if($file == false) {
						die('No such file');
					}
					$date = new DateTime(null, new DateTimeZone('Europe/London'));
		      $date = $date->format('Y-m-d H:i:s');

					$sql = "UPDATE uploads SET last_accessed = :currentdate WHERE name = :filename";
					$values = array(
						"filename" => $file->getName(),
						"currentdate" => $date
					);
					$bdd->queryEvent($sql, $values);

					$ext = pathinfo($file_url, PATHINFO_EXTENSION);
					$content_type = mime_content_type($file_url);
					header("Content-Type: ".$content_type);
          header("Content-Transfer-Encoding: Binary");
          header("Content-Length:".filesize($file_url));
          header("Content-Disposition: inline; filename=".$route[0]);
          readfile($file_url);

				}
				break;
			default:
				die('File not found');
			}
		}
  }
?>
