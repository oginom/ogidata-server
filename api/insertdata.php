<?PHP

require_once(__DIR__."../src/util.php");
require_once(__DIR__."../src/OgiData.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

$data = $_POST["data"];
$data = json_decode($data, true);
if (gettype($data) != "array") {
  returnError("data is not a dict");
}

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
  foreach ($tableinfo["columns"] as $i => $col_info) {
    if ($d_k == $col_info["name"]) {
      $name_db = $col_info["name_db"];
      break;
    }
  }
  if ($name_db == "") {
    returnError("column ".$d_k." not found");
  }
  if (array_key_exists($name_db, $add_data)) {
    returnError("column ".$d_k." doubled");
  }

  //TODO Type Check
  //if (gettype($d_v) != "string") {
  //  returnError("");
  //}

  $d_v = (string) $d_v;
  $add_data[$name_db] = $d_v;
}

//TODO Required column check

if (count($add_data) == 0) {
  returnError("no columns");
}

insertData($table_id, $add_data);

// if failed

//returnJSON($ret);

?>
