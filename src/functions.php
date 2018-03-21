<?PHP

require_once(__DIR__."/util.php");
require_once(__DIR__."/OgiData.php");

// 画像サイズ2MBまで
define("IMG_SIZE_MAX", 2000000);

$col_format = array(
  "type" => "dict",
  "unknownkey" => false,
  "contents" => array(
    array(
      "key" => "name",
      "required" => true,
      "format" => array(
        "type" => "string"
      )
    ),
    array(
      "key" => "type",
      "required" => true,
      "format" => array(
        "type" => "string",
        "enum_list" => $datatypes
      )
    ),
    array(
      "key" => "unit",
      "default" => "NONE",
      "format" => array(
        "type" => "string",
        "enum_list" => $unittypes
      )
    )
  )
);

function api_createtable($title, $cols) {

  global $col_format;

  $cols_info = array();
  $col_ind = 0;
  foreach ($cols as $ind => $col) {
    if ($ind != $col_ind) {
      returnError("cols list has keys");
    }
    try {
      $col_info = formatValue($col, $col_format);
    } catch (Exception $e) {
      returnError($e->getMessage());
    }
    $col_info["name_db"] = "col".$col_ind;
//  if (!array_key_exists("name", $col_info)
//      || $col_info["name"] == "") {
//    returnError("col".$col_ind." has no name");
//  }
    $cols_info[] = $col_info;
    $col_ind = $col_ind + 1;
  }

  if (count($cols_info) == 0) {
    returnError("no columns");
  }

  registerTitle($title);

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }

  $success = createTable($table_id, $cols_info);

  if (!$success) {
   unregisterTitle($title);
  }

  $tableinfo_filename = __DIR__."/../tableinfo/table".$table_id.".json";

  $tableinfo = array();
  $tableinfo["title"] = $title;
  $tableinfo["columns"] = $cols_info;

  // needed for Japanese?
  // json_encode( $array , JSON_UNESCAPED_UNICODE)
  $tableinfo_content = json_encode($tableinfo);
  file_put_contents($tableinfo_filename, $tableinfo_content);

  returnJSON($tableinfo);
}

function api_deletedata($title, $data_id) {

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }
  deleteData($table_id, $data_id);

  returnJSON("success");
}

function api_droptable($title) {

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }

  $success = dropTable($table_id);
  if (!$success) {
    returnError("DB error");
  }

  unregisterTitle($title);

  $tableinfo_filename = __DIR__."/../tableinfo/table".$table_id.".json";
  if (!unlink($tableinfo_filename)) {
    returnError("remove tableinfo file failed");
  }

  returnJSON($success);
}

function api_getdata($title) {

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }

  $tableinfo_filename = __DIR__."/../tableinfo/table".$table_id.".json";

  $tableinfo = file_get_contents($tableinfo_filename);
  if ($tableinfo === false) {
    returnError("tableinfo file not found");
  }
  $tableinfo = json_decode($tableinfo, true);
  if ($tableinfo === error) {
    returnError("tableinfo file format error");
  }

  $data = getData($table_id);

  returnJSON($data);

//if failed

}

function api_gettableid($title) {

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }

  returnJSON($table_id);
}

function api_gettableinfo($title) {

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }

  $tableinfo_filename = __DIR__."/../tableinfo/table".$table_id.".json";

  $tableinfo = file_get_contents($tableinfo_filename);
  if ($tableinfo === false) {
    returnError("tableinfo file not found");
  }

  $tableinfo = json_decode($tableinfo, true);
  if ($tableinfo === error) {
    returnError("tableinfo file format error");
  }

  returnJSON($tableinfo);
}

function api_gettables() {

  $tables = getTables();

  returnJSON($tables);
}

function api_insertdata($title, $data) {

  $table_id = getTableId($title);
  if ($table_id < 0) {
    returnError("table_id error");
  }

  $tableinfo_filename = __DIR__."/../tableinfo/table".$table_id.".json";

  $tableinfo = file_get_contents($tableinfo_filename);
  if ($tableinfo === false) {
    returnError("tableinfo file not found");
  }
  $tableinfo = json_decode($tableinfo, true);
  if ($tableinfo === false) {
    returnError("tableinfo file format error");
  }

  $add_data = array();
  foreach ($data as $d_k => $d_v) {
    if (gettype($d_k) != "string") {
      returnError("");
    }
    $name_db = "";
    $col_type = "";
    foreach ($tableinfo["columns"] as $i => $col_info) {
      if ($d_k == $col_info["name"]) {
        $name_db = $col_info["name_db"];
        $col_type = $col_info["type"];
        break;
      }
    }
    if ($name_db == "") {
      returnError("column ".$d_k." not found");
    }
    if (array_key_exists($name_db, $add_data)) {
      returnError("column ".$d_k." doubled");
    }

    try {
      checkValueType($d_v, $col_type);
    } catch (Exception $e) {
      returnError($e->getMessage());
    }

    $d_v = (string) $d_v;
    $add_data[$name_db] = $d_v;
  }

  //TODO Required column check

  if (count($add_data) == 0) {
    returnError("no columns");
  }

  insertData($table_id, $add_data);

  // if failed

  $ret = array(
    "result" => "success",
    "data" => $add_data
  );
  returnJSON($ret);
}

function api_removeimage($img_id) {

  $result = removeImage($img_id);

  returnJSON($result);
}

function api_uploadimage($image_tmpname, $image_filesize) {

  $image_info = getimagesize($image_tmpname);

  if ($image_info['mime'] == 'image/png') {
    $mime_type = "PNG";
    $img_ext = ".png";
  } else if($image_info['mime'] == 'image/jpeg') {
    $mime_type = "JPG";
    $img_ext = ".jpg";
  } else {
    returnError("image not PNG or JPG");
  }

  if ($image_filesize > IMG_SIZE_MAX) {
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
    if (move_uploaded_file($image_tmpname, $savefilename)) {
      $ret = array(
        "result" => "success",
        "img_id" => $img_id
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

}

?>
