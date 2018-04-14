<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_GET["title"];
if (gettype($title) == "NULL") {
  returnError("no title specified");
} else if (gettype($title) != "string") {
  returnError("title is not a string");
}

$limit = $_GET["limit"];
if (gettype($limit) != "NULL") {
  if (!is_numeric($limit)) {
    returnError("limit is not integer");
  }
  $limit = intval($limit);
}

$columns = $_GET["columns"];
if ($columns != NULL){
  $columns = json_decode($columns, true);
  if (gettype($columns) != "array") {
    returnError("columns is not an array");
  }
}

api_getchoice($title, $columns, $limit);

?>
