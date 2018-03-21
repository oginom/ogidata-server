<?PHP

require_once(__DIR__."../src/util.php");
require_once(__DIR__."../src/OgiData.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

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

?>
