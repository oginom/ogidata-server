<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

$cols = $_POST["cols"];
$cols = json_decode($cols, true);
if (gettype($cols) != "array") {
  returnError("cols is not a list");
}

api_createtable($title, $cols);

?>
