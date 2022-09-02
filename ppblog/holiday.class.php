<?php
/* 万年祝日カレンダークラス
  Last modified:: 2004/09/11 12:38

*/
class Holiday {
 var $holidays;     // yyyymmdd=>name 形式
 var $myholidays;   //       同　上

 function Holiday(){ //インスタンス
  $year = date("Y");
  $this->add("anniversary.php");
  $this->setHoliday($year);
 }
 function holidayList($y){ // デフォルトの祝祭日のリストを作成（ハッピーマンデー対応）
  $this->holidays = array(
   $y."0101"=>"元日",
   $y.$this->getMonday($y, 1, 2)=>"成人の日",
   $y."0211"=>"建国記念の日",
   $y."03".Intval(20.8431 + 0.242194*($y-1980) - intval(($y-1980)/4))=>"春分の日",
   $y."0429"=>"みどりの日",
   $y."0503"=>"憲法記念日",
   $y."0504"=>"国民の休日",
   $y."0505"=>"こどもの日",
   $y.$this->getMonday($y, 7, 3)=>"海の日",
   $y.$this->getMonday($y, 9, 3)=>"敬老の日",
   $y."09".Intval(23.2488 + 0.242194*($y-1980) - intval(($y-1980)/4))=>"秋分の日",
   $y.$this->getMonday($y, 10, 2)=>"体育の日",
   $y."1103"=>"文化の日",
   $y."1123"=>"勤労感謝の日",
   $y."1223"=>"天皇誕生日"
  );
  return $this->holidays;
 }
 function setHoliday($y){ // 振替休日や追加祝日も考慮した祝日リストを作成
  $this->holidayList($y);
  foreach($this->holidays as $key=>$v){
   $y = substr($key,0,4);
   $m = substr($key,4,2);
   $d = substr($key,6,2);
   if(date("w", mktime(0,0,0,$m, $d, $y))=="0"){
    $this->holidays[date("Ymd", mktime(0,0,0,$m, $d+1, $y))] = "振替休日";
   }
  }
  if(isset($this->myholidays)){
   foreach($this->myholidays as $key=>$v){
    if(array_key_exists($key, $this->holidays)) $v = $v ." ".$this->holidays[$key];
    $this->holidays[$key] = $v;
   }
  }
  ksort($this->holidays, SORT_NUMERIC);
  return $this->holidays;
 }
 function add($inc){ // 外部ファイルから休日の追加
  if(file_exists($inc)){
   $lines = file($inc);
  } else return;
  $this->myholidays = array();
  foreach($lines as $v){
   if(ereg("[0-9]{8}\|", $v)){
    list($date, $name) = explode("|", $v);
    $this->myholidays[$date] = trim($name);
   }
  }
  return $this->myholidays;
 }
 function getMonday($y, $m, $wk) { // 第wk週目の月曜日の日付を返す
  $utime = strtotime("$wk Monday", mktime(0,0,0,$m,1,$y));
  return date('md', $utime);
 }
}

$hd = new Holiday; // これでセット

?>