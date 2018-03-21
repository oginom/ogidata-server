<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_POST["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

api_droptable($title);

?>
