<?PHP

require_once(__DIR__."/../src/functions.php");

$title = $_GET["title"];
if (gettype($title) == "NULL") {
  returnError("no title specified");
} else if (gettype($title) != "string") {
  returnError("title is not a string");
}

$start_index = $_GET["start_index"];
if (gettype($start_index) != "NULL") {
  if (!is_numeric($start_index)) {
    returnError("start_index is not integer");
  }
  $start_index = intval($start_index);
}

$limit = $_GET["limit"];
if (gettype($limit) != "NULL") {
  if (!is_numeric($limit)) {
    returnError("limit is not integer");
  }
  $limit = intval($limit);
}

$asc = $_GET["asc"];
if (gettype($asc) != "NULL") {
  $yes_choice = ["YES", "TRUE"];
  $no_choice = ["NO", "FALSE"];
  if (in_array($asc, $yes_choice, true)) {
    $asc = true;
  } else if (in_array($asc, $no_choice, true)) {
    $asc = false;
  } else {
    returnError("asc is not bool");
  }
}

api_getdata($title, $start_index, $limit, $asc);

?>
