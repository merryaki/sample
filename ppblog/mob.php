<?php
/* 
 MOBLOG MODULE:: 2004/09/16 19:11
*/
/*
���Ȥ����ϡ�
�����ޤ����Բ�������򤷤����ppBlog�Υǥ��쥯�ȥ겼��index.php��Ʊ����٥��
�������Υե�����򥢥åס�
�����������������������륢�ɤ˵��������(�̿��դ���)������mob.php�˥���������
�������������������ǧ�ڥڡ����˹Ԥ��Τǡ������ǥ֥�å��Υѥ���ɤ����Ϥ���Ʋ�������
����ǧ�ڤ��Ѥ�С�����mob.php�ؤΥ�󥯤��ФƤ���Τǥ���å��������äƤ���������
�����������ꤷ�����������������Υ��ɥ쥹�Ȱ��פ����᡼��Τߤ���Ƶ����Ȥ���
��������ȥ꡼���ޤ���¾�Υ���ꥢ�Ǥ����뤫�ɤ�����Ƥ��ޤ���

*/
session_start();
include_once('usr/ini.inc.php');
include_once('utils.php');
include_once('cache.php');
if($mb==true){
 @mb_language("Japanese");
 @mb_internal_encoding(ENCODE);
} else {
 if(file_exists(PATH.'jcode_wrapper.php')){
  include_once(PATH.'jcode_wrapper.php');
 } else die('Multibyte Not Supported.');
}
include_once('utils.php');

if(isset($_GET['mode']) && $_GET['mode']=='exit'){
 $_SESSION = array();
 session_destroy();
 exit('Log off performed!');
}

define('ADMIN',(isset($_SESSION['ppBlog_admin'])||session_id()==$_GET['PHPSESSID'])?1:0);

if(!defined('ADMIN') || (defined('ADMIN') && ADMIN==0)){
 if(!isset($_POST["mobpwd"])){
  die('
 <form method="post" action="mob.php">
  ENTER BLOG\'S PWD:<input type="text" name="mobpwd" size="8">
  <input type="submit" value="Submit">
 </form>
  ');
 } else {
  if(trim($_POST['mobpwd'])==OPASS){
   $_SESSION['ppBlog_admin'] = md5(OPASS);
  } else {
   header("Location: ".ROOT_PATH."mob.php");
   exit;
  }
 }
}

/* <ɬ�����ꤹ��Ȥ���> */

$category = "moblog"; // ���ƥ��꡼̾(�夫��Ǥ��ɤ��ΤǴ����ԥڡ����Ǥ��Υ��ƥ��꡼̾���ɲä��Ƥ�������)
$server = "mail.XXXXX.XXX";            // �������륢�ɤ�POP3�����С�
$moby = "XXXXXXXXXXXXXXXXXX";          // ���������������Υ��ɥ쥹
$user = "XXXXXXX";                     // POP3�桼����ID
$pass = "XXXXXXX";                     // POP3�ѥ����
$send_ping = 0;                        // ���������������Ƥ��Ф���Ping�����Ȥ�ping���������롩(1�����롡0�����ʤ�)

/* </ɬ�����ꤹ��Ȥ��� �����ޤ�> */

$img_type = "gif|jpe?g|png";  // ���åפǤ������Content-Type
$mov_type = "3gpp|amc|x-mpeg";           // ���åפǤ���ư��Content-Type

// �ޥ���Х��ȴؿ����Ȥ��ʤ�����http://www.spencernetwork.org/ �Ǵ����������Ѵ�(�ʰ���)�򥲥å�
if(!$mb){
 if(file_exists('jcode-LE.php')) require_once('jcode-LE.php'); // jcode-LE.php ������
 else die("jcode-LE.php is necessary for your server! Get here-> http://www.spencernetwork.org/ ");
}

if(!$fp = @fsockopen($server, 110, $errno, $errstr, $timeout=10)){
 die('<p>Connection error ('.$errno.') - '.$errstr.'</p>'.NL);
}
socket_set_timeout($fp, $timeout);
if(!preg_match('/\+OK/',$line=fgets($fp, 512))) die($line);
pop3_cmd($fp, "USER $user");
pop3_cmd($fp, "PASS $pass");
list($count, $size) = sscanf(pop3_cmd($fp, "STAT"), '+OK %d %d');

if($count==0){
 pop3_cmd($fp, "QUIT");
 fclose($fp);
 die('
 <p>No moblog-mail available.</p>
 <p>Continue moblog? -> <a href="moby.php">Enter</a><br />
   or Log off? -> <a href="mob.php?mode=exit">Exit</a>
 </p>
 ');
}
$moby_mail = 0;
for ($i=1; $i<=$count; $i++){
 $line = pop3_cmd($fp, "RETR $i");
 while (!preg_match('/^\.\r\n/', $line)){
  $line = fgets($fp, 512);
  $data[$i].= $line;
 }
 if(eregi("From:[:space:]*<?.*($moby)",$data[$i],$mt_from)){  // ���ꥱ����������Υ᡼��ʤ�
  $moby_mail++;
  if(eregi("Date:[[:space:]]*([^\r\n]+)", $data[$i], $mt_date)){
   $uid = strtotime($mt_date[1]) != -1 ? strtotime($mt_date[1]) : time();
  }
  if(eregi('Content-type:[[:space:]]*multipart/.*;[[:space:]]*boundary="([^"]+)"',$data[$i], $mt_spliter)){//�����Ĥ�
   list($header, $body, $attach) = explode("--$mt_spliter[1]", $data[$i]);
   list(,$body) = spliti(" 7bit", $body);
   $body = convert_str(trim($body));
   
   list($a_h, $a_b) = explode("\r\n\r\n", $attach);
   if(eregi('[[:space:]]*name="([^"]+)"',$a_h, $mt_img) && eregi("image/$img_type", $a_h, $mt_ext)){ // �����ν񤭽Ф�
    $img_name = $uid.'_moby.'.str_replace('e','',$mt_ext[0]);
    $img_file = base64_decode($a_b);
    $imf = fopen(IMG_DIR.$img_name, 'wb');
    fputs($imf, $img_file);
    fclose($imf);
    
    $size = getImageSize(IMG_DIR.$img_name);
    $fsize = (@filesize(IMG_DIR.$img_name) > 0) ? round(filesize(IMG_DIR.$img_name)/1024,2).'KB' : '';
    $pop = $size[0].'(W)��'.$size[1].'(H) '.$fsize;
    $body = '<img src="'.IMG_DIR.$img_name.'" '.$size[3].' alt="" title="'.$pop.'" style="float:right;" />'.$body;
   }
   if(eregi('[[:space:]]*name="([^"]+)"',$a_h, $mt_img) && eregi("(application|video)/($mov_type)", $a_h)){ // ư��ν񤭽Ф�
    $mov_name = $uid.'_movie.'.array_pop(explode(".",$mt_img[1]));
    $mov_file = base64_decode($a_b);
    $movf = fopen(MOV_DIR.$mov_name, 'wb');
    fputs($movf, $mov_file);
    fclose($movf);
    $body .= '[mov:'.$mov_name.'/]';
   }
  } else {                                                                         // �ץ졼��ƥ�����
   list($header, $body,) = explode(" 7bit", $data[$i]);
   $body = convert_str(preg_replace("/^\.\r\n$/m",'',$body));
  }
  if(eregi("Subject:[[:space:]]*([^\r\n]+)", $header, $mt_subject)){
   $subject = preg_replace('/(.*)=\?iso\-2022\-jp\?B\?([^\?]+)\?=(.*)/ie', "'$1'.base64_decode('$2').'$3'", $mt_subject[1]);
   $subject = convert_str($subject);
  }
  $logFormat[$moby_mail] = $uid.'|'.trim($category).'|'.$subject.'|'.$body.'|'.NL; // ���Υե����ޥå�
  if(isset($subject) && isset($body)) pop3_cmd($fp, "DELE $i"); // �᡼��κ��
 }
}
pop3_cmd($fp, "QUIT");
fclose($fp);

if(log_($uid)=='') mk_fl(LOG.date('Ym', $uid).EXT);

$target = file( log_($uid) );// �������åȥ�

if($moby_mail>0){
 for($j=1; $j<=$moby_mail; $j++){
  usort($target, 'sort_by_date'); // ���դο��������
  array_unshift($target, $logFormat[$j]); // ��Ƭ���ɲ�
  rewrite(log_($uid), $target);
  echo "moblog:$j OK!<br>";
 }
 update_cache();
 if(RSS1){
  include_once('modules/rss1.0.inc.php');
  createRSS10(time());
 }
 if(RSS2){
  include_once('modules/rss2.0.inc.php');
  createRSS20(time());
 }
 if($send_ping){
  include('xmlrpc.php');
  send_ping_xmlrpc(BLOG_NAME, ROOT_PATH.'index.php');
 }
} else {
 die('
 <p>No moblog-mail available.</p>
 <p>Continue moblog? -> <a href="moby.php">Enter</a><br />
   or Log off? -> <a href="mob.php?mode=exit">Exit</a>
 </p>
 ');
}

die('
 <p>Moblog registered!</p>
 <p>Check moblogged? -> <a href="moby.php">Enter</a><br />
   or Log off? -> <a href="mob.php?mode=exit">Exit</a>
 </p>
');

function pop3_cmd($fp, $cmd){
 fputs($fp, $cmd."\r\n");
 if(preg_match('/\+OK/',$line=fgets($fp, 512))) {
  return $line;
 } else die($line);
}

function convert_str($str){
 global $mb;
 $str = ($mb) ? mb_convert_encoding($str, ENCODE, 'auto') : jcode_convert_encoding($str, ENCODE);
 $a = array('`'=>'&#96;',','=>'&#44;','$'=>'&#36;','|'=>'&#124;');
 $str = str_replace(array_keys($a), array_values($a), $str);
 return preg_replace("/\r\n|\r|\n/",'`', $str);
}

?>
