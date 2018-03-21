<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

$data = $_POST["data"];
$data = json_decode($data, true);
if (gettype($data) != "array") {
  returnError("data is not a dict");
}

api_insertdata($title, $data);

?>
