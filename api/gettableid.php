<?PHP

require_once(__DIR__."../src/util.php");
require_once(__DIR__."../src/OgiData.php");

$title = $_GET["title"];
if (gettype($title) != "string") {
  returnError("title is not string");
}

$table_id = getTableId($title);
if ($table_id < 0) {
  returnError("table_id error");
}

returnJSON($table_id);

?>
