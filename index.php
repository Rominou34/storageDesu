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
* File upload
*/
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	include('head.php');
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
        "name" => $file_name,
        "upload_time" => $date
      );
      $req = $bdd->queryEvent($sql, $values);
			if($req) {
				$file_url = WEBSITE_URL.$file_name;
				?>
				<h2>File uploaded</h2>
				<a href="<?php echo($file_url)?>"><?php echo($file_url)?></a>
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
				include('head.php');
		    include('form.html');
				break;
			// '/*'
			case 1:
				// '/index.php'
				if($route[0] == 'index.php') {
					include('head.php');
					include('form.html');
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
	<script>
	  document.getElementById("file").onchange = function() {
		  document.getElementById("uploadText").innerHTML = "Uploading...";
	    var button = document.getElementById("upload-button");
	    button.className = " loading";
	    document.getElementById("form").submit();
	  };
	</script>
	</body>
</html>
