<?PHP

require_once("util.php");
require_once("OgiData.php");

$tables = getTables();

returnJSON($tables);

?>
