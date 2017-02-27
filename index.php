<?php

require('config.php');
require('db.php');

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
* Cleans the string so we don't have problems with the database or the URLs
*/
function clean($string) {
   $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

/*
* Generates a random name for the file
*/
function randomName() {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < 8; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

/*
* File upload
*/
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	// We check that a file was sent
	if(!empty($_FILES['file']['name'])) {
		if($_FILES['file']['size'] > (1000000*MAX_UPLOAD_SIZE)) {
			die('Maximum file size allowed is '.MAX_UPLOAD_SIZE.' MB');
		}
		// We clean the name of the file
		$target_dir = UPLOAD_DIR;
		$ext = pathinfo(basename($_FILES["file"]["name"]),PATHINFO_EXTENSION);
		// IF we want a random name we generate it, else we clean the original name of the file
		if(GENERATE_RANDOM_NAME) {
			$file_name = randomName();
		} else {
			$file_name = clean(basename($_FILES["file"]["name"], ".".$ext));
		}
		$file_upload_name = $file_name.".".$ext;
		$target_file = $target_dir.$file_name.".".$ext;

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
        die("If you're the administrator of the website, you must specify what type of files filtering you want");
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
        "name" => $file_upload_name,
        "upload_time" => $date
      );
      $req = $bdd->queryEvent($sql, $values);
			if($req) {
				$file_url = WEBSITE_URL.$file_upload_name;
				?>
				<!DOCTYPE html>
				<html>
				  <head>
				    <title>File uploaded !</title>
				    <meta charset="UTF-8">
				    <meta name="viewport" content="width=device-width, initial-scale=1.0">
				    <link rel="stylesheet" type="text/css" href="style.css">
						<script async="" src="https://www.google-analytics.com/analytics.js"></script>
				    <!-- DELETE THIS ANALYTIC LINK BEFORE USING IT ON YOUR WEBSITE !-->
				    <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
				    <!-- !-->
					</head>
					<body>
						<a href="<?php echo(WEBSITE_URL)?>">
				      <h1>storage<i>~Desu~</i></h1>
				    </a>
						<h2>File uploaded</h2>
						<a href="<?php echo($file_url)?>"><?php echo($file_url)?></a>
					</body>
				</html>
				<?php
			}
		} else {
				die("An error occured during the upload of the file");
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
				include('pages/main.php');
				break;
			// '/*'
			case 1:
				// '/index.php'
				switch($route[0]) {
					case 'index.php':
						include('pages/main.php');
						break;
					case 'about':
						include('pages/about.php');
						break;
					case 'privacy':
						include('pages/privacy.php');
						break;
					default:
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

						$content_type = mime_content_type($file_url);
						header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
						header('Cache-Control: must-revalidate');
						header("Content-Type: ".$content_type);
	          header("Content-Transfer-Encoding: Binary");
	          header("Content-Length:".filesize($file_url));
	          header("Content-Disposition: inline; filename=".basename($file_url));
						header("Test-header: ".$file_url);
						header("Test-bis: ".file_exists($file_url));
	          readfile($file_url);
						die();
				}
				break;
			default:
				die('File not found');
			}
		}
  }
?>
