<?php
// i using mysql table `hostnotcount` like this
//CREATE TABLE `hostnotcount` (
//  `id` int(11) NOT NULL,
//  `host` text DEFAULT NULL
//) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

//connect to mysql
try {
    $db = new PDO ("mysql:dbname={$_ENV["DB_NAME"]};host={$_ENV["DB_HOST"]}; charset=utf8", $_ENV["DB_USER"], $_ENV["DB_PASS"]);
} catch (PDOException $e) {
    echo 'DB接続エラー' . $e->getMessage();
}

require_once("/function/disnum.php");
$keta = 5;

$iphost = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
$excepip = [
  "you.can.add.except.ip.that.not.count",
];

$sql="SELECT (host) from hostnotcount;";
$stmt=$db->prepare($sql);
$stmt->execute();
$excephostkeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
$excephostkeys = array_column($excephostkeys,"host");
$excephostids = array_column($excephostkeys,"id");

$excephostcheck = true;
for($i=0;$i<=(count($excephostkeys)-1);$i++){
  if(preg_match("/$excephostkeys[$i]/",$iphost)){
    $excephostcheck = false;
    break;
  }
}
if(preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/', $iphost)){
  $excephostcheck = false;
}

$sql="SELECT * from counter3 WHERE time=:time AND ip!='';";
$stmt= $db->prepare($sql);
$stmt->bindValue(":time", date("y/m/d"));
$stmt->execute();
$row=$stmt->fetchALL(PDO::FETCH_ASSOC);

$ips = array_column($row,"ip");
$ids = array_column($row,"id");
$loadcounts = array_column($row,"loadcount");
$ipcounts = array_column($row,"ipcount");
$adipcounts = array_column($row,"adipcount");
$adloadcounts = array_column($row,"adloadcount");
//first
//$sql="SELECT * from counter3 WHERE ip!='192.168.10.1';";

$sql="INSERT INTO counter3 (loadcount,ipcount,adloadcount,adipcount,url,ip,time) 
      VALUES (:loadcount,:ipcount,:adloadcount,:adipcount,:url,:ip,:time);";
$stmt=$db->prepare($sql);

$first = false;
if($row==false){
  $first = true;
  if(count($ids)==0){
    $id_max = 0;
  }else{
    $id_max = intval($ids[count($ids)-1]+1);
  }

  $sql="SELECT * from counter3;";
  $stmt2= $db->prepare($sql);
  $stmt2->execute();
  $row2=$stmt2->fetchALL(PDO::FETCH_ASSOC);
  
  $ids = array_column($row2,"id");
  $d = disnum($id_max,$keta);
  $numimg = "";
  for($i=0;$i<=(count($d)-1);$i++){
    if($d[$i]==0){
      $numimg .= "<img src='/assets/img/icon/numset/part1/b1.png'>";
    }else{
      $numimg .= "<img src='/assets/img/icon/numset/part1/b{$d[$i]}.png'>";
    }
  }
  //alter table counter3 auto_increment=1;
  $counter["allaccess"][0] = $numimg;
  $counter["todayloadcount"][0] = "<img src='/assets/img/icon/numset/part1/b1.png'>";
  $counter["todayipcount"][0] = "<img src='/assets/img/icon/numset/part1/b1.png'>";
  if($excephostcheck){
    if(in_array($iphost,$excepip)){
      $stmt->bindValue(":adipcount", 1, PDO::PARAM_INT);
      $stmt->bindValue(":adloadcount", 1, PDO::PARAM_INT);
      $stmt->bindValue(":loadcount", 0, PDO::PARAM_INT);
      $stmt->bindValue(":ipcount", 0, PDO::PARAM_INT);
    }else{
      $stmt->bindValue(":adipcount", 0, PDO::PARAM_INT);
      $stmt->bindValue(":adloadcount", 0, PDO::PARAM_INT);
      $stmt->bindValue(":loadcount", 1, PDO::PARAM_INT);
      $stmt->bindValue(":ipcount", 1, PDO::PARAM_INT);
    }
  }else{
    $stmt->bindValue(":adipcount", 0, PDO::PARAM_INT);
    $stmt->bindValue(":adloadcount", 0, PDO::PARAM_INT);
    $stmt->bindValue(":loadcount", 0, PDO::PARAM_INT);
    $stmt->bindValue(":ipcount", 0, PDO::PARAM_INT);
  }
  $stmt->bindValue(":url", $_SERVER["PHP_SELF"], PDO::PARAM_STR);
  $stmt->bindValue(":ip", $iphost, PDO::PARAM_STR);
  $stmt->bindValue(":time", date("y/m/d"), PDO::PARAM_STR);
  $stmt->execute();
}else{

  //以下、グラフ作成
//ここでallaccess計算する
$sql="SELECT * from counter3;";
$stmt2= $db->prepare($sql);
$stmt2->execute();
$row2=$stmt2->fetchALL(PDO::FETCH_ASSOC);

$times = array_column($row2,"time");
$vtimes = [];
//for($i=0;$i<=(count($times)-1);$i++){
//後ろから時間の配列を作っていく
//自然にそれぞれの日のmaxを含む行の番号が出てくる
for($i=(count($times)-1);$i>=0;$i--){
  if(!in_array($times[$i],$vtimes)){
    //番号も新しい配列に入れておく
    $c[] = $i;
    $vtimes[] = $times[$i];
  }
}
//総アクセス数だけは7日間だけのものではなく、
//すべての日の和で計算する
$sumadloadcounts = intval(0);
$sumloadcount = intval(0);

$vtimes = array_slice($vtimes,0,7);
$e = array_slice($c,8,count($c));
//var_dump($e);
for($i=(count($e)-1);$i>=0;$i--){
  $sumadloadcounts += $row2[$e[$i]]["adloadcount"];
  $sumloadcount += $row2[$e[$i]]["loadcount"];
}
$c = array_slice($c,0,7);

$gtimes = "";
$gipcounts = "";
$gadipcounts = "";
$gloadcounts = "";
$gadloadcounts = "";
$gadallaccess = "";
$gallaccess = "";
for($i=(count($c)-1);$i>=0;$i--){
  $sumadloadcounts += $row2[$c[$i]]["adloadcount"];
  $sumloadcount += $row2[$c[$i]]["loadcount"];

  $vtimes[$i] = substr($vtimes[$i],3);
  $gtimes .= "'".$vtimes[$i]."'"; 
  $gipcounts .= intval($row2[$c[$i]]["ipcount"]);
  $gadipcounts .= intval($row2[$c[$i]]["adipcount"]); 
  $gloadcounts .= $row2[$c[$i]]["loadcount"];
  $gadloadcounts .= $row2[$c[$i]]["adloadcount"];
  //$gadallaccess .= intval($row2[$c[$i]]["id"]);
  $gadallaccess .= intval($sumadloadcounts);
  //$gallaccess .= intval($row2[$c[$i]]["id"]-$row2[$c[$i]]["adloadcount"]);

  //$gallaccess .= intval($row2[$c[$i]]["loadcount"]);
  $gallaccess .= intval($sumloadcount);

  if($i!==0){
    $gtimes .= ","; 
    $gipcounts .= ",";
    $gadipcounts .= ",";
    $gloadcounts .= ",";
    $gadloadcounts .= ",";
    $gadallaccess .= ",";
    $gallaccess .= ",";
  }
}


//アドミン含むか含まないか
if(!isset($_SESSION["counter"]["btn"])){
  $_SESSION["counter"]["btn"] = false;
}
//アドミンを含む
if(isset($_SESSION["counter"]["btn"]) && $_SESSION["counter"]["btn"]){
  $whichallaccess = $gadallaccess;
  $whichip = $gadipcounts;
  $whichload = $gadloadcounts;
}else{
  $whichallaccess = $gallaccess;
  $whichip = $gipcounts;
  $whichload = $gloadcounts;
}

  $first = false;
  $allaccess = "";
  $todayload = "";
  $todayipload = "";
  //あとで計算する
  //上のグラフ作成の計算で
  //$id_maxからいろいろひかないといけない
  //$allaccess = intval($ids[count($ids)-1]+1-$sumadloadcounts);
  $allaccess = intval($sumloadcount);
  //$adallaccess = intval($ids[count($ids)-1]+1);
  $adallaccess = intval($sumadloadcounts);
  //
  $ipcount_max = intval($ipcounts[count($ipcounts)-1]);
  $loadcount_max = intval($loadcounts[count($loadcounts)-1]);
  $adipcount_max = intval($adipcounts[count($adipcounts)-1]);
  $adloadcount_max = intval($adloadcounts[count($adloadcounts)-1]);

  $counter = [
    "allaccess" => disnum($allaccess,$keta),
    "adallaccess" => disnum($adallaccess,$keta),
    "todayipcount" => disnum($ipcount_max,$keta),
    "todayloadcount" => disnum($loadcount_max,$keta),
    "todayadipcount" => disnum($adipcount_max,$keta),
    "todayadloadcount" => disnum($adloadcount_max,$keta),
  ];
  

  $ckeys = array_keys($counter);
  $numimg = "";
  for($i=0;$i<=(count($ckeys)-1);$i++){
    for($j=0;$j<=(count($counter[$ckeys[$i]])-1);$j++){
      if($counter[$ckeys[$i]][$j]==10){
        $numimg .= "<img src='/assets/img/icon/numset/part1/b1.png'><img src='/assets/img/icon/numset/part1/b0.png'>";
      }else{
        $numimg .= "<img src='/assets/img/icon/numset/part1/b{$counter[$ckeys[$i]][$j]}.png'>";
      }
    }
    array_unshift($counter[$ckeys[$i]],$numimg);
    $numimg = "";
  }

  //以下、loadcount/adloadcount
  //excepipに含まれてたら
  if($excephostcheck){
    if(in_array($iphost,$excepip)){
      //loadcountはそのまま
      $stmt->bindValue(":loadcount", $loadcount_max, PDO::PARAM_INT);
    }else{
      //loadcountを+1
      $stmt->bindValue(":loadcount", $loadcount_max+1, PDO::PARAM_INT);
    }
  }else{
    $stmt->bindValue(":loadcount", $loadcount_max, PDO::PARAM_INT);
  }
  //ここ+1だよね
  if($excephostcheck){
    $stmt->bindValue(":adloadcount", $adloadcount_max+1, PDO::PARAM_INT);
  }else{
    $stmt->bindValue(":adloadcount", $adloadcount_max, PDO::PARAM_INT);
  }
  //以下、ipcount/adipcount
  //本日アクセスしたipが新規だったら
  if(!in_array($iphost,$ips)){
    //新規でもexcepipに含まれてたら      
    if($excephostcheck){
      if(in_array($iphost,$excepip)){
        //先の値と同じ値を入れる
        $stmt->bindValue(":ipcount",$ipcount_max, PDO::PARAM_INT);
        //adminは増やす
        $stmt->bindValue(":adipcount", $adipcount_max+1, PDO::PARAM_INT);
      }else{
        //新規でもexcepipに含まれてなかったら 
        $stmt->bindValue(":ipcount",$ipcount_max+1, PDO::PARAM_INT);
        //ここ+1だよね
        $stmt->bindValue(":adipcount", $adipcount_max+1, PDO::PARAM_INT);
      }
    }else{
      $stmt->bindValue(":ipcount",$ipcount_max, PDO::PARAM_INT);
      //ここ+1だよね
      $stmt->bindValue(":adipcount", $adipcount_max, PDO::PARAM_INT);
    }
  }else{
    $stmt->bindValue(":ipcount",$ipcount_max, PDO::PARAM_INT);
    $stmt->bindValue(":adipcount", $adipcount_max, PDO::PARAM_INT);
  }
  //ipなどは関係なく入れる
  $stmt->bindValue(":ip", $iphost, PDO::PARAM_STR);
  $stmt->bindValue(":time", date("y/m/d"), PDO::PARAM_STR);
  $stmt->bindValue(":url", $_SERVER["PHP_SELF"], PDO::PARAM_STR);

  $stmt->execute();
}
//$sql="CREATE table counter(id int,url text,ipcount int(5),relcount int(5),time timestamp default current_timestamp);";
