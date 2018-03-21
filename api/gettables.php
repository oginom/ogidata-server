<?PHP

require_once(__DIR__."../src/util.php");
require_once(__DIR__."../src/OgiData.php");

$tables = getTables();

returnJSON($tables);

?>
