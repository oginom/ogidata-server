<?PHP
$areax = $_GET['areax'];
$areay = $_GET['areay'];
$timeset = $_GET['timeset'];
//$areax = 101;
//$areay = 150;
//$timeset = 6677;

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

function getConnection(){
	$server = "mysql111.phy.lolipop.lan";
	$user = "LAA0705006";
	$pass = "we64play";
	$database = "LAA0705006-gensodb";
	$pdo = new PDO("mysql:host=".$server."; dbname=".$database."; charset=utf8",$user,$pass);
	return $pdo;
}
function execute($conn,$sql,$param=array()){
	$stmt = $conn->prepare($sql);
	foreach( $param as $key => $value ){
		$stmt->bindValue($key,$value);
	}
	$stmt->execute();
	return $stmt;
}

numcheck($areax,81000);
numcheck($areay,30000);
numcheck($timeset,2000000000);

function getdata($ts,$ax,$ay){
  $areatype = 0;
  $areaver = 0;
  
  $axs = $ax % 1000;
  $ays = $ay % 1000;
  $areaids = $axs + $ays * 1000;
  try{
	$conn = getConnection();
	$sql = "SELECT * FROM areatable WHERE areaid = :areaid";
	$param = array( ":areaid" => $areaids );
	$stmt = execute($conn,$sql,$param);
	
	//var_dump($stmt);
	
	while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ){
		$areatype = intval($row["areatype"]);
		$areaver = intval($row["areaver"]);
	}
  }catch(PDOException $e){
	print $e->getMessage();
	return null;
  }  

  $atoms = array();
  srand($ts+$ax*100+$ay*10039);
  for($i = 0; $i < 200; $i++){
    $at['xy'] = rand(0,1600);
    $at['tl'] = rand(0,300);
    $at['id'] = $areaids * 1000 + $i;
    $at['en'] = rand(0,10);
    
    $rare = rand(0,9);
    if($rare > 0){
      $at['tp'] = rand(0,3);
    }else{
      $at['tp'] = $areatype;
    }
    $atoms[$i] = $at;
  }
  $areadata['ts'] = $ts;
  $areadata['ax'] = $ax;
  $areadata['ay'] = $ay;
  $areadata['at'] = $areatype;
  $areadata['av'] = $areaver;
  $areadata['sd'] = $atoms;
  return $areadata;
}
$data = array();
$data[0] = getdata($timeset,$areax-1,$areay-1);
$data[1] = getdata($timeset,$areax-1,$areay);
$data[2] = getdata($timeset,$areax,$areay-1);
$data[3] = getdata($timeset,$areax,$areay);
$data[4] = getdata($timeset,$areax,$areay+1);
$data[5] = getdata($timeset,$areax+1,$areay);
$data[6] = getdata($timeset,$areax+1,$areay+1);
$data[7] = getdata($timeset+1,$areax-1,$areay-1);
$data[8] = getdata($timeset+1,$areax-1,$areay);
$data[9] = getdata($timeset+1,$areax,$areay-1);
$data[10] = getdata($timeset+1,$areax,$areay);
$data[11] = getdata($timeset+1,$areax,$areay+1);
$data[12] = getdata($timeset+1,$areax+1,$areay);
$data[13] = getdata($timeset+1,$areax+1,$areay+1);

header( "Content-Type: application/json; charset=utf-8");
echo json_encode($data);

?>
