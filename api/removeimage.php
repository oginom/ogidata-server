<?PHP

require_once(__DIR__."/util.php");
require_once(__DIR__."/OgiData.php");

$img_id = $_POST["img_id"];
if (!is_numeric($img_id)) {
  returnError("img_id is not integer");
}
$img_id = intval($img_id);

$result = removeImage($img_id);

returnJSON($result);

?>
