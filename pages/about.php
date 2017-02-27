<!DOCTYPE html>
<html>
  <head>
    <title>Desu</title>
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
    <div class="info-text">
      <h2>About</h2>
      <p>
        Desu is a free <strong>temporary</strong> storage service<br/>
        It's basically a remake of <a href="https://file.quad.moe/">QuadFile</a>
        using PHP and MySQL ( you can't call that a fork tho, as I started the
        code from scratch )
      </p>
      <p>
        The idea is simple: when you upload a file, it will be deleted one week
        after it was last accessed. For example if you upload a file on the 10th
        of March and you never access it, it will be deleted on the 17th.<br/>
        But if you access it on the 13th then it will be deleted on the 20th.
        Thoerically speaking, if you access a file every 6 days it will never be
        deleted
      </p>
      <p style="text-align: center">
        This is done to lower the server cost and it puts a layer on privacy
        as links are randomly generated, making it harder to bruteforce them<br/><br/>
        <span style="color: #9ca1ad">The maximum upload size is <?php echo(MAX_UPLOAD_SIZE); ?>MB</span>
      </p>
    </div>
    <?php include('nav.php'); ?>
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
