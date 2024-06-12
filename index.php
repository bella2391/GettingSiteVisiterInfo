<?php
// i using `counter3` table like this
//CREATE TABLE `counter3` (
//  `id` int(11) NOT NULL,
//  `loadcount` int(5) DEFAULT NULL,
//  `ipcount` int(5) DEFAULT NULL,
//  `adloadcount` int(5) DEFAULT NULL,
//  `adipcount` int(5) DEFAULT NULL,
//  `url` text DEFAULT NULL,
//  `ip` tinytext DEFAULT NULL,
//  `time` text DEFAULT NULL,
//  `dtime` timestamp NOT NULL DEFAULT current_timestamp()
//) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

//connect to mysql
try {
    $db = new PDO ("mysql:dbname={$_ENV["DB_NAME"]};host={$_ENV["DB_HOST"]}; charset=utf8", $_ENV["DB_USER"], $_ENV["DB_PASS"]);
} catch (PDOException $e) {
    echo 'DB接続エラー' . $e->getMessage();
}

require_once($_count);

$alart = "";
if(!isset($_SESSION["counter"]["btn"])){
  $_SESSION["counter"]["btn"] = false;
}
if(!empty($_POST)){
  if(isset($_POST["reloadtoken"])){
    if(isset($_SESSION["reloadtoken"]) && $_SESSION["reloadtoken"]===$_POST["reloadtoken"]){
      $_SESSION["counter"]["btn"] = false;
      header("Location: ".$_SERVER["PHP_SELF"]);
      exit();
    }else{
      $alart .= "<br>セッションが無効です。";
    }
  }

  if(isset($_POST["reloadtoken2"])){
    if(isset($_SESSION["reloadtoken2"]) && $_SESSION["reloadtoken2"]===$_POST["reloadtoken2"]){
      $_SESSION["counter"]["btn"] = true;
      header("Location: ".$_SERVER["PHP_SELF"]);
      exit();
    }else{
      $alart .= "<br>セッションが無効です。";
    }
  }

}

$TOKEN_LENGTH = 16;
$tokenByte = openssl_random_pseudo_bytes($TOKEN_LENGTH);
$token = bin2hex($tokenByte);
$_SESSION['reloadtoken'] = $token;

$TOKEN_LENGTH = 16;
$tokenByte = openssl_random_pseudo_bytes($TOKEN_LENGTH);
$token = bin2hex($tokenByte);
$_SESSION['reloadtoken2'] = $token;
?>
<!DOCTYPE html>
 <html lang="ja">
  <head>
   <meta charset="UTF-8">
  <title>
  </title>
  <link rel="stylesheet" href="/assets/css/toggle.php">
  <link rel="stylesheet" href="/assets/css/sch_blog.css">
 </head>
<body>
<br>
<div class="j-flex">
  <div>
    <h2 style="color:white;">&nbsp;&nbsp;推移</h2>
  </div>
  <?php if($_SESSION["counter"]["btn"])://サイト管理者含む ?>
  <counter class="absolute_right font1-5">
    総アクセス数：&emsp;<?=$counter["adallaccess"][0]?><br>
    本日のロード数：&emsp;<?=$counter["todayadloadcount"][0]?><br>
    本日の訪問者数：&emsp;<?=$counter["todayadipcount"][0]?><br>
  </counter>
  <?php else: ?>
    <counter class="absolute_right font1-5">
    総アクセス数：&emsp;<?=$counter["allaccess"][0]?><br>
    本日のロード数：&emsp;<?=$counter["todayloadcount"][0]?><br>
    本日の訪問者数：&emsp;<?=$counter["todayipcount"][0]?><br>
  </counter>
  <?php endif; ?>
</div>
<p class="hr2"></p>          
<?php if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!==$thisurl): ?>
  <a href="<?=$_SERVER['HTTP_REFERER']?>" class='right font1-5 white under'>元のページに戻る</a>
<?php else: ?>
  <a href="/" class='right font1-5 white under'>ホームページに戻る</a>
<?php endif; ?>

<?php if($first): ?>
  <br><div class="font2-0 under center">もう一度リロードすると...</div>
<?php else: ?>

  <?php if(isset($alart)): ?>
  <div class="red font1-5"><?=$alart?></div><br>
<?php endif; ?>

<?php if(isset($_SESSION["counter"]["btn"]) && $_SESSION["counter"]["btn"]): ?>
<div class="j-flex">
  <div>
    &emsp;サイト管理者を含む
  </div>
  <div class="toggle_button">
    <form id="basic_form" method="post" action="">
      <div id="checkbox_id">
        <input type="checkbox" name="reloadtoken" value="<?=$_SESSION['reloadtoken']?>" id="reverse_reload_toggle" class="reverse_toggle_input"/>
        <label for="reverse_reload_toggle" class="reverse_toggle_label" />
      </div>
    </form>
  </div>
</div>
<?php else: ?>
<div class="j-flex">
  <div>
    &emsp;サイト管理者を含まない
  </div>
  <div class="toggle_button">
    <form id="basic_form" method="post" action="">
      <div id="checkbox_id">
        <input type="checkbox" name="reloadtoken2" value="<?=$_SESSION['reloadtoken2']?>" id="reload_toggle" class="toggle_input"/>
        <label for="reload_toggle" class="toggle_label" />
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
<script>
   document.getElementById("checkbox_id").addEventListener("change", function(e){
     document.forms.basic_form.submit();
   });
</script>
<br>
<div class="font2-5 under center">週ごとの推移</div><br>
  <div>
  <canvas id="access"></canvas>
</div>
<div>
<canvas id="loadplusvisiter"></canvas>
</div>

<?php endif; ?>
</div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <!--using chart.js-->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  var ctx4 = document.getElementById("loadplusvisiter");
  var myLineChart4 = new Chart(ctx4, {
    type: 'line',
    data: {
      labels: [<?=$gtimes?>],
      datasets: [
        {
          label: 'ロード回数(回)',
          data: [<?=$whichload?>],
          //data: ["a"],

          borderColor: "rgba(255,0,0,1)",
          backgroundColor: "rgba(0,0,0,0)"
        },
        {
          label: '訪問者数(人)',
          data: [<?=$whichip?>],
          //data: ["a"],

          borderColor: "rgba(0,0,255,1)",
          backgroundColor: "rgba(0,0,0,0)"
        }
      ],
    },
    options: {
      title: {
        display: true,
        text: ''
      },
      scales: {
        yAxes: [{
          ticks: {
            suggestedMax: 40,
            suggestedMin: 0,
            stepSize: 5,
            callback: function(value, index, values){
              return  value +  '人'
            }
          }
        }]
      },
    }
  });
  </script>

  <script>
  var ctx2 = document.getElementById("access");
  var myLineChart2 = new Chart(ctx2, {
    type: 'line',
    data: {
      labels: [<?=$gtimes?>],
      datasets: [
        {
          label: '総アクセス数(回)',
          data: [<?=$whichallaccess?>],
          //data: ["a"],

          borderColor: "rgba(0,255,0,1)",
          backgroundColor: "rgba(0,0,0,0)"
        },
        //{
        //  label: 'ロード数(回)',
        //  data: [$gloadcounts],
        //  borderColor: "rgba(0,0,255,1)",
        //  backgroundColor: "rgba(0,0,0,0)"
        //}
      ],
    },
    options: {
      title: {
        display: true,
        text: ''
      },
      scales: {
        yAxes: [{
          ticks: {
            suggestedMax: 40,
            suggestedMin: 0,
            stepSize: 5,
            callback: function(value, index, values){
              return  value +  '人'
            }
          }
        }]
      },
    }
  });
  </script>
<br>
</body>
</html>
