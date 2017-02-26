<!DOCTYPE html>
<html>
  <head>
    <title>Page title</title>
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
    <form method="post" action="" name="form" id="form" enctype="multipart/form-data">
      <div id="upload-button">
        <p id="uploadText">Upload a file</p>
        <input type="file" id="file" name="file">
      </div>
    </form>
    <p style="text-align: center">
      All files will be deleted one week after they were last accessed</br>
      More details about that will come soon<br/><br/>
      Max upload size is <?php echo(MAX_UPLOAD_SIZE); ?>MB
    </p>
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
