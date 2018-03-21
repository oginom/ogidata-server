<?php

require_once(__DIR__."/../src/functions.php");

if ($_FILES['file']) {
  $image_tmpname = $_FILES['file']['tmp_name'];
  $image_filesize = $_FILES['file']['size'];
  api_uploadimage($image_tmpname, $image_filesize);
} else {
  returnError("no available file");
}

?>
