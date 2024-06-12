<?php
function disnum($num,$keta){
  $i=1;
  $b = [];
  while($i<=$keta){
    if($num==1){
      $b[] = 1;
      break;
    }elseif($num==0){
      $b[] = 0;
      break;
    }elseif(pow(10,$i-1)<$num && $num<=pow(10,$i)){
      for($j=$i;$j>=1;$j--){
        $a = intdiv($num,pow(10,$j-1));
        $b[] = $a;
        $num = $num-(pow(10,$j-1))*$a;
      }
      break;
    }
    $i++;
  }
  return $b;
}
