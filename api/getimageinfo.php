<?PHP

require_once(__DIR__."/../src/functions.php");

$img_id = $_GET["img_id"];
if (!is_numeric($img_id)) {
  returnError("img_id is not integer");
}
$img_id = intval($img_id);

api_getimageinfo($img_id);

?>
