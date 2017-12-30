<?PHP

require_once(__DIR__."/util.php");
require_once(__DIR__."/OgiData.php");

$tables = getTables();

returnJSON($tables);

?>
