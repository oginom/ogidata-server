<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not string");
}

$data_id = $_POST["data_id"];
if (!is_numeric($data_id)) {
  returnError("data_id is not integer");
}
$data_id = intval($data_id);

api_deletedata($title, $data_id);

?>
