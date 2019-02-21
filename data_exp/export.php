<?PHP

require_once(__DIR__."/../src/util.php");
require_once(__DIR__."/../src/OgiData.php");

if (array_key_exists("REQUEST_METHOD", $_SERVER)) {
  returnError("not authorized file");
}

$tables = getTables();

var_dump($tables);

foreach ($tables as $title => $table_id) {
  $outfile = __DIR__."/table".$table_id.".csv";
  exportData($table_id, $outfile);
  print("exported table".$table_id." ".$title."\n");
}

exportTable("img_info", __DIR__."/img_info.csv");
print("exported img_info\n");
exportTable("table_title", __DIR__."/table_title.csv");
print("exported table_title\n");

?>
