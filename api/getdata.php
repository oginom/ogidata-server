<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_GET["title"];
if (gettype($title) != "string") {
  returnError("title is not a string");
}

api_getdata($title);

?>
