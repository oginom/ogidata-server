<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

$data_id = $_POST["data_id"];
if (!is_numeric($data_id)) {
  returnError("data_id is not integer");
}
$data_id = intval($data_id);

$data = $_POST["data"];
$data = json_decode($data, true);
if (gettype($data) != "array") {
  returnError("data is not a dict");
}

api_updatedata($title, $data_id, $data);

?>
