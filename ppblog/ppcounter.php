<?php
/*
 LAST MODIFIED: 2004/09/11 15:05
 + 当日と前日，トータルのアクセスを表示。
 + オプションでオンラインの人数を表示します。
 + 数字を画像で指定することもできます。
 + 一定時間内の重複IPはカウントしません。また除外IPも設定できます。
 + 数字画像の大きさはそろえて下さい。また名前は0.png〜9.pngとか0.gif〜9.gifとかつけて10枚用意。

 + バージョン1.3未満をお使いの方は<div class="pp-counter">...</div> というHTML
 + を生成するので各自テーマにそって ”Edit Template”セクションでCSSファイル
 + に例えば以下のように(テーマbasicの場合)指定をして下さい。(あとindex.phpも書き換えが必要ですが)

.pp-counter{
    position:absolute; 
    top: 105px; right:21px;
    font:500 10px arial;
    color: #5353A6;
    text-align:right;
}
 
*/

define('COUNT_LOG', UD.'count.log'); // カウントログの名前
define('IMAGE_DIR', './Images/');   // 数字画像指定時のディレクトリ
define('IMG_EXT', '.png');          // 画像の拡張子(ピリオドも含める)
$ip_escape = array('');  // 除外したいIPを指定(複数はカンマで区切る)
// 例：$ip_escape = array('127.0.0.1','123.4.5.6');
$show_online = 1;                   // オンライン人数を表示する？(する:1，しない:0)
define('TIME_SPAN', 150);           // 何秒以内をオンラインとカウントするか，と重複IPのチェック期間。

mk_fl(COUNT_LOG);
$logfile = COUNT_LOG;
$lines = file($logfile);
$ip = getenv('REMOTE_ADDR') ? getenv('REMOTE_ADDR') : $_SERVER['REMOTE_ADDR'];
$timestamp = time();

list($total, $latest_date, $today_cnt, $yesterday_cnt) = explode('|', $lines[0]);
list($latest_ip,$latest_visit) = explode('@', $lines[count($lines)-1]);

if(!in_array($ip,$ip_escape) && ADMIN==false){
 if($ip!=$latest_ip){
  if((int)($timestamp-$latest_visit) < TIME_SPAN){
   $lines = array_merge($lines, "$ip@$timestamp\n");
  } else {
   $lines = array($lines[0],"$ip@$timestamp\n");
  }
  if((int)(date('Ymd',$timestamp)-$latest_date)>=1){
   $yesterday_cnt = $today_cnt;
   $today_cnt = 1;
   $latest_date = date('Ymd',$timestamp);
  } else {
   $today_cnt++;
  }
  $total++;
 }
 $data = sprintf("%06d|%08d|%03d|%03d\n", $total, $latest_date, $today_cnt, $yesterday_cnt);
 array_splice($lines, 0,1,$data);
 if(!empty($lines)) rewrite($logfile, $lines);
} else {
 $data = sprintf("%06d|%08d|%03d|%03d\n", $total, $latest_date, $today_cnt, $yesterday_cnt);
}

$datum = explode('|', $data);
$users = count($lines)-1;

if(USE_IMG_COUNTER){
 $size = getimagesize(IMAGE_DIR.'0'.IMG_EXT);
 $size = $size[3].' alt=""';
 $td_sp = preg_split('//', $datum[2], -1, PREG_SPLIT_NO_EMPTY);
 for($i=0;$i<count($td_sp); $i++){
  $_today .= '<img src="'.IMAGE_DIR.$td_sp[$i].'.png" '.$size.' />';
 }
 $yd_sp = preg_split('//', $datum[3], -1, PREG_SPLIT_NO_EMPTY);
 for($i=0;$i<count($yd_sp); $i++){
  $_yesterday .= '<img src="'.IMAGE_DIR.$yd_sp[$i].'.png" '.$size.' />';
 }
 $tl_sp = preg_split('//', $datum[0], -1, PREG_SPLIT_NO_EMPTY);
 for($i=0;$i<count($tl_sp); $i++){
  $_total .= '<img src="'.IMAGE_DIR.$tl_sp[$i].'.png" '.$size.' />';
 }
 $ol_sp = preg_split('//', sprintf("%02d",$users), -1, PREG_SPLIT_NO_EMPTY);

 for($i=0;$i<count($ol_sp); $i++){
  $_online .= '<img src="'.IMAGE_DIR.$ol_sp[$i].'.png" '.$size.' />';
 }
} else { 
 $_today = sprintf("%03d", $today_cnt);
 $_yesterday = sprintf("%03d", $yesterday_cnt);
 $_total = sprintf("%06d", $total);
 $_online = sprintf("%02d", $users);
}

return $pp_counter = '
<div class="pp-counter">
Today: '.$_today.' Yesterday: '.$_yesterday.'Total: '.$_total.'
'.($show_online?' Online: '.$_online:'').'
</div>
';

?>