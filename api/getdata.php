<?PHP

require_once(__DIR__."../src/util.php");
require_once(__DIR__."../src/OgiData.php");

$title = $_GET["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
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
if ($tableinfo === error) {
  returnError("tableinfo file format error");
}

$data = getData($table_id);

returnJSON($data);

// if failed

//returnJSON($ret);

?>
