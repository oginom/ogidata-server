<?PHP

require_once(__DIR__."/util.php");
require_once(__DIR__."/../dbconfig.php");

$datatypes = array(
  "INT",
  "DOUBLE",
  "DECIMAL",
  "STRING",
  "TIMESTAMP",
  "DATE",
  "IMG"
);
$unittypes = array(
  "NONE",
  "YEN"
);

function checkValueType($val, $type) {
  if (gettype($type) != "string") {
    throw new Exception("value is not string");
  }
  switch ($type) {
    case "INT":
      if (filter_var($val, FILTER_VALIDATE_INT) === false) {
        throw new Exception("value ".$val." doesn't fit INT");
      }
      break;
    case "DOUBLE":
      if (filter_var($val, FILTER_VALIDATE_FLOAT) === false) {
        throw new Exception("value ".$val." doesn't fit DOUBLE");
      }
      break;
    case "DECIMAL":
      if (filter_var($val, FILTER_VALIDATE_FLOAT) === false) {
        throw new Exception("value ".$val." doesn't fit DECIMAL");
      }
      break;
    case "STRING":
      break;
    case "TIMESTAMP":
      if (!preg_match(
          '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',
          $val, $m)) {
        throw new Exception("value ".$val." doesn't fit TIMESTAMP");
      }
      break;
    case "DATE":
      if (!preg_match(
          '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
          $val, $m)) {
        throw new Exception("value ".$val." doesn't fit DATE");
      }
      break;
    case "IMG":
      if (filter_var($val, FILTER_VALIDATE_INT) === false) {
        throw new Exception("value ".$val." doesn't fit IMG");
      }
      //TODO check image id
      break;
    default:
      throw new Exception("type '".$type."' not found");
      break;
  }
  return;
}

function registerTitle($title) {
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "INSERT INTO table_title (title) VALUES (:title)";
    $param = array(":title" => $title);
    $stmt = execute($conn, $sql, $param);
    //var_dump($stmt);
    //var_dump($param);
    $dberr = $stmt->errorInfo();
    //print_r($dberr);
    if ($dberr[0] != "00000") {
      if ($dberr[0] == "23000") {
        returnError("title already exists");
      }
      returnError("title error");
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
}

function unregisterTitle($title) {
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "DELETE FROM table_title WHERE title = :title";
    $param = array(":title" => $title);
    $stmt = execute($conn, $sql, $param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      returnError("unregister title failed");
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
}

function getTableID($title) {
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "SELECT table_id FROM table_title WHERE title = ";
    $sql .= $conn->quote($title);
    $stmt = execute($conn, $sql);

    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      //print_r($stmt->errorInfo());
      returnError("get table_id error");
    }
    $row = $stmt->fetch();
    if ($row and isset($row["table_id"])) {
      $ret = intval($row["table_id"]);
      if ($ret >= 0) {
        return $ret;
      }
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
  return -1;
}

function createTable($table_id, $cols_info) {
  $tablename = "table".$table_id;
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    //$sql = "CREATE TABLE :tablename (";
    //$param = array( ":tablename" => $tablename );
    $sql = "CREATE TABLE ";
    //$sql .= $conn->quote($tablename);
    $sql .= $tablename;
    $sql .= " (";
    $sql .= "created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,";
    $sql .= "data_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,";
    $name_db = array();
    foreach ($cols_info as $i => $col_info) {
      $type_db = $col_info["type"];
      //TODO iikanjini ni
      if ($type_db == "STRING") {
        $type_db = "VARCHAR(255)";
      } else if ($type_db == "IMG") {
        $type_db = "INT";
      }
      //$sql .= " :nm".$i." ".$type_db.",";
      $sql .= $col_info["name_db"]." ".$type_db.",";
      //$name_db[$i] = $col_info["name_db"];
      //$param[":nm".$i] = $name_db[$i];
    }
    $sql = rtrim($sql, ",");
    $sql .= ")";

    $stmt = execute($conn,$sql);

    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      //print_r($stmt->errorInfo());
      //returnError("create table error");
      return false;
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
  return true;
}

function dropTable($table_id) {
  $tablename = "table".$table_id;
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "DROP TABLE ".$tablename;
    $stmt = execute($conn,$sql);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      //print_r($stmt->errorInfo());
      //returnError("drop table error");
      return false;
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
  return true;
}

function getTables() {
  $tables = array();
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    //$sql = "SHOW TABLES";
    $sql = "SELECT title, table_id FROM table_title";
    $param = array();
    $stmt = execute($conn,$sql,$param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      returnError("get tables error");
    }
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
      $tables[$row[0]] = $row[1];
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
  return $tables;
}

function insertData($table_id, $add_data) {
  $tablename = "table".$table_id;
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "INSERT INTO ";
    //$sql .= $conn->quote($tablename);
    $sql .= $tablename;
    $sql .= " (";
    $sql_val = ") VALUES (";
    foreach ($add_data as $k => $v) {
      //$sql .= $conn->quote($k);
      $sql .= $k;
      $sql .= ",";
      $sql_val .= $conn->quote($v);
      //$sql_val .= $v;
      $sql_val .= ",";
    }
    $sql = rtrim($sql, ",");
    $sql_val = rtrim($sql_val, ",");
    $sql .= $sql_val;
    $sql .= ")";

    $param = array();
    $stmt = execute($conn,$sql,$param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      //var_dump($stmt->errorInfo());
      returnError("insert data error");
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
}

function deleteData($table_id, $data_id) {
  $tablename = "table".$table_id;
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "DELETE FROM ";
    $sql .= $tablename;
    $sql .= " WHERE data_id = :data_id";
    $param = array(
      ":data_id" => $data_id
    );
    $stmt = execute($conn, $sql, $param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      //print_r($stmt->errorInfo());
      returnError("delete data failed");
    }
    $delCount = $stmt->rowCount();
    if ($delCount < 1) {
      returnError("data_id not match");
    } else if ($delCount > 1) {
      returnError("same data_id count was ".$delCount);
    }
  }catch(PDOException $e){
    returnError($e->getMessage());
  }
}

function getData($table_id) {
  $tablename = "table".$table_id;
  $data = array();
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "SELECT * FROM ";
    $sql .= $tablename;
    $param = array();
    $stmt = execute($conn,$sql,$param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      returnError("create table error");
    }
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
      $data[] = $row;
    }
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
  return $data;
}

function getNextImageID() {
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "SELECT COALESCE(MAX(img_id),0) FROM img_info";
    $stmt = execute($conn, $sql);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      return -1;
    }
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
      return (intval($row[0]) + 1);
    }
  } catch (PDOException $e) {
    return -1;
  }
  return -1;
}

function setImageID($img_id, $img_filename, $mime_type, $img_width, $img_height) {
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "INSERT INTO img_info ";
    $sql .= "(img_id,img_filename,mime_type,img_width,img_height) ";
    $sql .= "VALUES ( ";
    $sql .= ":img_id , ";
    $sql .= ":img_filename , ";
    $sql .= ":mime_type , ";
    $sql .= ":img_width , ";
    $sql .= ":img_height )";
    $param = array(
      ":img_id" => $img_id,
      ":img_filename" => $img_filename,
      ":mime_type" => $mime_type,
      ":img_width" => $img_width,
      ":img_height" => $img_height,
    );
    $stmt = execute($conn,$sql,$param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      return false;
    }
  } catch (PDOException $e) {
    return false;
  }
  return true;
}

function removeImage($img_id) {
  try {
    $conn = getConnection(DB_HOST, DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "SELECT img_filename, is_removed FROM img_info WHERE img_id = :img_id";
    $param = array(":img_id" => $img_id);
    $stmt = execute($conn,$sql,$param);
    $row = $stmt->fetchAll(PDO::FETCH_NUM);
    if (count($row) == 1) {
      $img_filename = $row[0][0];
      $is_removed = $row[0][1];
    } else {
      returnError("no match image ID in DB");
    }
    if (intval($is_removed)) {
      returnError("already removed");
    }
    $sql = "UPDATE img_info SET is_removed = TRUE WHERE img_id = :img_id";
    $stmt = execute($conn,$sql,$param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      returnError("remove image info failed");
    }
    $removefilename = __DIR__."/../media/img/".$img_filename;
    unlink($removefilename);
  } catch (PDOException $e) {
    returnError($e->getMessage());
  }
  return true;
}

?>
