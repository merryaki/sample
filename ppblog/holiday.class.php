<?php
/* ��ǯ���������������饹
  Last modified:: 2004/09/11 12:38

*/
class Holiday {
 var $holidays;     // yyyymmdd=>name ����
 var $myholidays;   //       Ʊ����

 function Holiday(){ //���󥹥���
  $year = date("Y");
  $this->add("anniversary.php");
  $this->setHoliday($year);
 }
 function holidayList($y){ // �ǥե���Ȥν˺����Υꥹ�Ȥ�����ʥϥåԡ��ޥ�ǡ��б���
  $this->holidays = array(
   $y."0101"=>"����",
   $y.$this->getMonday($y, 1, 2)=>"���ͤ���",
   $y."0211"=>"����ǰ����",
   $y."03".Intval(20.8431 + 0.242194*($y-1980) - intval(($y-1980)/4))=>"��ʬ����",
   $y."0429"=>"�ߤɤ����",
   $y."0503"=>"��ˡ��ǰ��",
   $y."0504"=>"��̱�ε���",
   $y."0505"=>"���ɤ����",
   $y.$this->getMonday($y, 7, 3)=>"������",
   $y.$this->getMonday($y, 9, 3)=>"��Ϸ����",
   $y."09".Intval(23.2488 + 0.242194*($y-1980) - intval(($y-1980)/4))=>"��ʬ����",
   $y.$this->getMonday($y, 10, 2)=>"�ΰ����",
   $y."1103"=>"ʸ������",
   $y."1123"=>"��ϫ���դ���",
   $y."1223"=>"ŷ��������"
  );
  return $this->holidays;
 }
 function setHoliday($y){ // ���ص������ɲý������θ���������ꥹ�Ȥ����
  $this->holidayList($y);
  foreach($this->holidays as $key=>$v){
   $y = substr($key,0,4);
   $m = substr($key,4,2);
   $d = substr($key,6,2);
   if(date("w", mktime(0,0,0,$m, $d, $y))=="0"){
    $this->holidays[date("Ymd", mktime(0,0,0,$m, $d+1, $y))] = "���ص���";
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
 function add($inc){ // �����ե����뤫��������ɲ�
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
 function getMonday($y, $m, $wk) { // ��wk���ܤη����������դ��֤�
  $utime = strtotime("$wk Monday", mktime(0,0,0,$m,1,$y));
  return date('md', $utime);
 }
}

$hd = new Holiday; // ����ǥ��å�

?>