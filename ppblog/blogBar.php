<?php
/*
 blogBar.php by martin
 Last modified on 2004/07/21
*/
session_start();
if(ADMIN!=true) _header('admin.php');

include_once('usr/ini.inc.php');
include_once('utils.php');

if(GD!=true) die('This script needs GD library. Please check the GD on your server.');
$_IMGDIR = 'usr/';
$img_file = $_IMGDIR.'blogBar_'.time().'.png';

/* 好きな英字フォント(TrueType)をトップディレクトリに置いとけば，それがタイトル文字に反映 */
$path2font = PATH.'georgiaz.ttf';

if(isset($_COOKIE['blogBar']) && !strstr($_COOKIE['blogBar'],'::') && empty($_POST)){
 list($width,$height,$barcolor,$textcolor,$bar_title,$start_log) = explode(':',$_COOKIE['blogBar']);
} else {
 $width = p_('width')!=''?p_('width') : 140;
 $height = p_('height')!=''?p_('height') : 50;
 $bar_title = p_('bar_title');
 $start_log = p_('start_log')!=''?p_('start_log'):date('Ym01',strtotime('-1 month'));
 $barcolor = p_('barcolor')!=''?p_('barcolor'):'rgb(0,0,0)';
 $textcolor = p_('textcolor')!=''?p_('textcolor'):'rgb(0,0,0)';
}

setcookie('blogBar', join(':',array($width,$height,$barcolor,$textcolor,$bar_title,$start_log))); // cookie

$bc = explode(',', preg_replace('/rgb\((\d+,\d+,\d+)\)/i', '$1', $barcolor) );
$fu = 3; // font_id of title
$tc = explode(',', preg_replace('/rgb\((\d+,\d+,\d+)\)/i', '$1', $textcolor) );
$lc = array(255,255,255); // lucent

$LOGS = my_glob('\d+',LOG, SORT_BY_DATE);
$LOGS = preg_replace('/[^\d{6}]/','', $LOGS);

foreach ($LOGS as $i=>$log){
 $_LOGS = array_merge($_LOGS, $log);
 if($log==substr($start_log,0,6) || $i==5) break;
}

$start = date('F',strtotime($start_log));
$end = date('F Y',strtotime((int)($_LOGS[0].'01')));
$span = ($start_log==$_LOGS[0].'01') ? date('F Y', strtotime($start_log)) : $start.'-'.$end;

$img = imagecreate($width, $height);
$clear = imagecolorallocate($img, $lc[0], $lc[1], $lc[2]);
imagecolortransparent($img, $clear); // 透明にしておこう

$col = imagecolorallocate($img, $tc[0], $tc[1], $tc[2]);

if($path2font!='' && file_exists($path2font)){ // 指定フォントを使う
 ImageTTFText($img, 9, 0, 1, 10, $col, $path2font, $bar_title); // Bar title
 $fb = imagettfbbox(8, 0, $path2font, $span);
 $str_width = (int)($fb[2]-$fb[0]+5);
 $span_posX = $width - $str_width;
 ImageTTFText($img, 8, 0, $span_posX, 10, $col, $path2font, $span);
} else { // 組み込みフォントを使う
 imagestring($img, $fu, 1, 0, $bar_title, $col); // Bar title
 $str_width = imagefontwidth(2)*count(preg_split('//', $span, -1, PREG_SPLIT_NO_EMPTY))-16;
 $span_posX = $width - $str_width - 30;
 imagestring($img, $fu, $span_posX, 0, $span, $col);
}
$col = imagecolorallocate($img, $bc[0], $bc[1], $bc[2]);
imagefilledrectangle($img, 5, 13, $width-6, $height-11, $col); // Bar

$GD2 = function_exists('imagesetthickness') ? true : false;

// バーコードっぽく
if($GD2)imagesetthickness($img, 2);
$col = imagecolorallocate($img, $bc[0], $bc[1], $bc[2]);
imageline($img, 1, 13, 1, $height-5, $col);
if($GD2)imagesetthickness($img, 1);
imageline($img, 3, 13, 3, $height-5, $col);
if($GD2)imagesetthickness($img, 2);
imageline($img, $width-3, 13, $width-3, $height-5, $col);
if($GD2)imagesetthickness($img, 1);
imageline($img, $width-6, 13, $width-6, $height-5, $col);

$_lines = array();

if(is_array($_LOGS)){
 foreach ($_LOGS as $log){
  $lines = my_file(LOG.$log.EXT);
  if(is_array($lines)) $_lines = array_merge($_lines, $lines);
 }
} elseif(file_exists(LOG.$_LOGS.EXT)){
 $_lines = my_file(LOG.$_LOGS.EXT);
}

if($GD2)imagesetthickness($img, 1);

foreach ($_lines as $line){ // エントリーラインの生成
 $uid = array_shift(explode('|', $line));
 $entry_time = date('G', $uid)*60 + date('i', $uid);
 $entry_x = 3 + (int)($entry_time*($width-10)/(24*60));
 imageline($img, $entry_x, 13, $entry_x, $height-10, $clear);
}

$col = imagecolorallocate($img, $tc[0], $tc[1], $tc[2]);
for ($i=0;$i<=24;$i+=2){ // 時間軸の作成
 $_i = ($i==24) ? '' : $i;
 imagestring($img, 1, 4+$i*(int)(($width-6)/24), $height-9, $_i, $col);
}

$d = opendir($_IMGDIR);
while ($file = readdir($d)){ 
 if(preg_match("/^blogBar_.*/", $file)) $former_img = $_IMGDIR.$file;
}
closedir($d);
if(!empty($_POST)){
 if(isset($former_img)) unlink($former_img);
 header ("Content-type: image/png");
 imagepng($img, $img_file); // PNG画像の生成
 if(@filesize($img_file)==0) die($img_file.'s file size is zero!');
 if(!p_('manual_mode')) exit; // update only
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP" /> 
<meta http-equiv="Content-Script-Type" content="text/javascript" /> 
<meta http-equiv="Content-Style-Type" content="text/css" /> 
<link rel="stylesheet" type="text/css" href="./blogBar.css" /> 
<script type="text/javascript" src="webPallete.js"></script> 
<title>++PPBLOG TIMES++</title> 
</head> 
<body onload='makePallette("25%",document.getElementById("wrapper").offsetHeight+40+"px");'> 
<div id="wrapper">
<h1>blogBar</h1>

<?php
$target = file_exists($img_file) ? $img_file : (isset($former_img)?$former_img:null);
$size = @getimagesize($target);
if(empty($size)) $size[3] = 'width="1" height="1"';
$on = 'Generated on '.date('Y/m/d j H:i:s',substr($target,15,10));
echo '<p><img src="'.$target.'" '.$size[3].' alt="blogBar" title="'.$on.'" /></p>'.NL;
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"> 
<fieldset> 
<p><input type="hidden" name="manual_mode" value="1" /></p>
<dl> 
<dt>■ 表示期間の設定（いつからのエントリーを表示するかを選択）</dt> 
<dd>
<select name="start_log" id="start_log">
<?php
foreach ($LOGS as $i=>$log){
 if($i==5) break;
 $sel = substr($start_log,0,6)==$log ? ' selected="selected"' : '';
 echo '<option value="'.$log.'01"'.$sel.'>'.date('Y/m',strtotime($log.'01')).'</option>'.NL;
}
?>
</select>
</dd> 
<dt>■ 横幅(px)</dt> 
<dd><input type="text" name="width" size="6" value="<?php echo $width?>"/></dd> 
<dt>■ 縦幅(px)</dt> 
<dd><input type="text" name="height" size="6" value="<?php echo $height?>" /></dd> 
<dt>■ 上部に表示するテキスト(半角英数のみ)</dt> 
<dd><input type="text" name="bar_title" size="30" value="<?php echo $bar_title?>" /></dd>
<dt>■ バーの色 (横のラジオボタンをチェックして下のスライドバーで選択)</dt> 
<dd><input type="text" name="barcolor" id="barcolor" size="15" value="<?php echo $barcolor?>" /> 
 <input type="radio" name="rcolor" value="barcolor" checked="checked" />
</dd>
<dt>■ テキストの色 (横のラジオボタンをチェックして下のスライドバーで選択)</dt>
<dd><input type="text" name="textcolor" id="textcolor" size="15" value="<?php echo $textcolor?>" />
 <input type="radio" name="rcolor" value="textcolor" />
</dd> 
</dl>
<p><button type="submit">Make TIMES</button>&nbsp;&nbsp;
  <button type="button" title="ブロッグページへ戻ります" onclick='location.href="index.php"'>To BLOG</button></p>
</fieldset>
</form>
</div>
</body> 
</html>
