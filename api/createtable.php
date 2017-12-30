<?PHP

require_once("util.php");
require_once("OgiData.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

$cols = $_POST["cols"];
$cols = json_decode($cols, true);
if (gettype($cols) != "array") {
  returnError("cols is not a list");
}

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

createTable($table_id, $cols_info);

// TODO
// if createTable failed
// unregisterTitle

$tableinfo_filename = "tableinfo/table".$table_id.".json";

$tableinfo = array();
$tableinfo["title"] = $title;
$tableinfo["columns"] = $cols_info;

// needed for Japanese?
// json_encode( $array , JSON_UNESCAPED_UNICODE)
$tableinfo_content = json_encode($tableinfo);
file_put_contents($tableinfo_filename, $tableinfo_content);

returnJSON($tableinfo);

?>
