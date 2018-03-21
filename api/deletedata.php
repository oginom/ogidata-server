<?PHP

require_once(__DIR__."../src/util.php");
require_once(__DIR__."../src/OgiData.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not string");
}

$data_id = $_POST["data_id"];
if (!is_numeric($data_id)) {
  returnError("data_id is not integer");
}
$data_id = intval($data_id);

$table_id = getTableId($title);
if ($table_id < 0) {
  returnError("table_id error");
}

deleteData($table_id, $data_id);

returnJSON("success");

?>
