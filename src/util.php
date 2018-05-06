<?PHP

function numcheck(&$val,$max){
  if(empty($val) || !is_numeric($val)){
    $val = 0;
  }else{
    $val = intval($val);
    if($val < 0 || $val > $max){
      $val = 0;
    }
  }
}

function formatValue($raw, $format) {
  if ($format["type"] == "dict") {
    $ret = array();
    if (gettype($raw) != "array") {
      throw new Exception("not dict");
    }
    $unknownkey = true;
    if (array_key_exists("unknownkey", $format)) {
      $unknownkey = (boolean) $format["unknownkey"];
    }
    $contents = array();
    if (array_key_exists("contents", $format)
        && gettype($format["contents"]) == "array") {
      $contents = $format["contents"];
    }

    if ($unknownkey) {
      $ret = $raw;
    } else {
      foreach ($contents as $v) {
        if (array_key_exists($v["key"], $raw)) {
          $ret[$v["key"]] = $raw[$v["key"]];
        }
      }
    }

    foreach ($contents as $v) {
      if (array_key_exists("default", $v)) {
        if (!array_key_exists($v["key"], $ret)) {
          //TODO adapt to empty
          $ret[$v["key"]] = $v["default"];
        }
      }
      $required = false;
      if (array_key_exists("required", $v)) {
        $required = (boolean) $v["required"];
      }
      if (array_key_exists($v["key"], $ret)) {
        if (array_key_exists("format", $v)) {
          $ret[$v["key"]] = formatValue($ret[$v["key"]], $v["format"]);
        }
      } else {
        if ($required) {
          throw new Exception("value of key '".$v["key"]."' doesn't exists");
        }
      }
    }
    return $ret;
  } else if ($format["type"] == "int") {
    if (!preg_match('/^-?[1-9][0-9]*$/', strval($raw))) {
      throw new Exception("int format error");
    }
    $ret = intval($raw);
    if (array_key_exists("int_min", $format)) {
      if ($ret < $format["int_min"]) {
        throw new Exception("int out of range");
      }
    }
    if (array_key_exists("int_max", $format)) {
      if ($ret < $format["int_max"]) {
        throw new Exception("int out of range");
      }
    }
    return $ret;
  } else if ($format["type"] == "string") {
    $ret = (string) $raw;
    if (array_key_exists("enum_list", $format)) {
      $string_enum_list = $format["enum_list"];
      if (!in_array($ret, $string_enum_list, true)) {
        throw new Exception("enum not match");
      }
    }
    return $ret;
  } else {
    throw new Exception("type '".$format["type"]."' unavailable");
  }
}

function getConnection($host, $database, $user, $password) {
  $pdo = new PDO("mysql:host=".$host."; dbname=".$database."; charset=utf8", $user, $password);
  return $pdo;
}
function execute($conn, $sql, $param=array()) {
  $stmt = $conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  //foreach( $param as $key => $value ){
  //  //$stmt->bindValue($key,$value);
  //  $stmt->bindParam($key,$value,PDO::PARAM_STR);
  //}
  //$stmt->execute();
  $stmt->execute($param);
  return $stmt;
}

function returnJSON($ar) {
  $content = json_encode($ar);
  header( "Content-Type: application/json; charset=utf-8");
  echo $content."\n";
  exit();
}

function returnError($errmsg) {
  $errorinfo = array("ErrorMessage" => $errmsg);
  returnJSON($errorinfo);
}

?>
