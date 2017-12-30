<?php

require_once(__DIR__."/util.php");
require_once(__DIR__."/OgiData.php");

// 画像サイズ2MBまで
define("IMG_SIZE_MAX", 2000000);

if ($_FILES['file']) {
  $image_info = getimagesize($_FILES['file']['tmp_name']);
} else {
  returnError("no available file");
}

if ($image_info['mime'] == 'image/png') {
  $mime_type = "PNG";
  $img_ext = ".png";
} else if($image_info['mime'] == 'image/jpeg') {
  $mime_type = "JPG";
  $img_ext = ".jpg";
} else {
  returnError("image not PNG or JPG");
}

if ($_FILES['file']['size'] > IMG_SIZE_MAX) {
  returnError("image size bigger than ".IMG_SIZE_MAX);
}

$img_id = getNextImageID();
if ($img_id < 0) {
  returnError("image DB inner error");
}
$img_filename = "img-".$img_id.$img_ext;
$img_width = $image_info[0];
$img_height = $image_info[1];
$savefilename = __DIR__."/../media/img/".$img_filename;

if (setImageID($img_id, $img_filename, $mime_type, $img_width, $img_height)) {
  if (move_uploaded_file($_FILES['file']['tmp_name'], $savefilename)) {
    $ret = array(
      "result" => "success",
      "img_id" => $img_id;
    );
    returnJSON($ret);
  } else {
    //TODO
    //removeImageID from DB
    returnError("image upload failed");
  }
} else {
  returnError("image DB update failed");
}

?>
