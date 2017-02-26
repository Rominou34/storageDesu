<?php

require('config.php');
require('db.php');

$bdd = new DB();

/* We calculate the date from 1 week */
$limit_date = new DateTime(null, new DateTimeZone('Europe/London'));
$limit_date->modify('-1 week');
$limit_date = $limit_date->format('Y-m-d H:i:s');

/* We request all the files that are too old */
$sql = "SELECT * FROM uploads WHERE last_accessed <= :limit_date";
$values = array("limit_date" => $limit_date);
$files_to_delete = $bdd->query($sql, $values);

/* We delete each one of them */
$total_deleted = 0;
$failed = 0;
foreach($files_to_delete as $file) {
  $deleted = unlink(dirname(__FILE__)."/".UPLOAD_DIR.$file->name);
  /* If we failed to delete it from the server, we don't delete it in the db */
  if($deleted) {
    $sql = "DELETE FROM uploads WHERE name = :filename";
    $values = array("filename" => $file->name);
    $bdd->queryEvent($sql, $values);
    $total_deleted++;
  } else {
    $failed++;
  }
}

echo("Total files deleted: ".$total_deleted.", Total deletions failed: ".$failed);
