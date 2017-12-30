<?PHP

require_once("util.php");

$datatypes = array(
  "INT",
  "DOUBLE",
  "DECIMAL",
  "STRING",
  "DATETIME",
  "IMG"
);
$unittypes = array(
  "NONE",
  "YEN"
);

function registerTitle($title) {
  try {
    $conn = getConnection();
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

function getTableID($title) {
  try {
    $conn = getConnection();
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
    $conn = getConnection();
    //$sql = "CREATE TABLE :tablename (";
    //$param = array( ":tablename" => $tablename );
    $sql = "CREATE TABLE ";
    $sql .= $conn->quote($tablename);
    $sql .= " (";
    $sql .= "created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,";
    $name_db = array();
    foreach ($cols_info as $i => $col_info) {
      $type_db = $col_info["type"];
      //TODO iikanjini ni
      if ($type_db == "STRING") {
        $type_db = "VARCHAR(255)";
      }
      //$sql .= " :nm".$i." ".$type_db.",";
      $sql .= $col_info["name_db"]." ".$type_db.",";
      //$name_db[$i] = $col_info["name_db"];
      //$param[":nm".$i] = $name_db[$i];
    }
    $sql = rtrim($sql, ",");
    $sql .= ")";

    $stmt = execute($conn,$sql);
    //var_dump($stmt);

    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      //print_r($stmt->errorInfo());
      // TODO false return and unregister table_title
      returnError("create table error");
    }
  }catch(PDOException $e){
    returnError($e->getMessage());
  }
}

function getTables() {
  $tables = array();
  try {
    $conn = getConnection();
    //$sql = "SHOW TABLES";
    $sql = "SELECT title, table_id FROM table_title";
    $param = array();
    $stmt = execute($conn,$sql,$param);
    $dberr = $stmt->errorInfo();
    if ($dberr[0] != "00000") {
      returnError("create table error");
    }
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
      $tables[$row[0]] = $row[1];
    }
  }catch(PDOException $e){
    returnError($e->getMessage());
  }
  return $tables;
}

function insertData($table_id, $add_data) {
  $tablename = "table".$table_id;
  try {
    $conn = getConnection();
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

    var_dump($sql);
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

function getData($table_id) {
  $tablename = "table".$table_id;
  $data = array();
  try {
    $conn = getConnection();
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
  }catch(PDOException $e){
    returnError($e->getMessage());
  }
  return $data;
}

?>
