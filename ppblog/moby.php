<?php
/*
��moby.php version 1.3 by Masayuki AOKI, martin
��LastUpdated: 2004/09/11 02:51

�������������鵭���α����������Ԥ��Խ����������ǽ
����������Ƥϥ���������������ꡣmoblog��Ƹ塤���Τޤޤ����˰ܹԤǤ��롣
�������Ȥ���ơ������ԤϺ�����ǽ
�������ԥ�����ѥ���ppBlog�Ρ֥ѥ���ɡפ�Ʊ���Ǥ���
��FOMA��900x/WAP2.0�б���KDDI/Vodafone�ʳ���ɽ����������뤫�⡣
��To Do List
  * �����ȿ���¿���ʤä��Ȥ���ʬ��
  * �ȥ�å��Хå���ɽ���⤤�����ɤ��ޤ���פϤʤ���
*/

session_start();
include_once('usr/ini.inc.php');
header ("Content-Type: text/html; charset=SHIFT_JIS");
if($mb==true){
 @mb_language("Japanese");
 @mb_internal_encoding(ENCODE);
} else {
 if(file_exists(PATH.'jcode_wrapper.php')){
  include_once(PATH.'jcode_wrapper.php');
 } else makeXHTML_mobile('Multibyte Not Supported.');
}
define('ROOT_PATH', 'http://'.str_replace('//','',$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'));
define('PATH', str_replace('//','',dirname($_SERVER['SCRIPT_FILENAME']).'/'));
define('ENTRY_NUM', 10);

if(isset($_POST['mobpwd'])){ // ������
 if(trim($_POST['mobpwd'])==OPASS){
  if(array_key_exists('login', $_POST)) $_SESSION['ppBlog_admin'] = md5(OPASS);
 } else {
  makeXHTML_mobile('
 <form method="post" action="moby.php">
  ENTER BLOG\'S PWD:<input type="text" name="mobpwd" size="8" />
  <input type="submit" value="Submit" />
 </form>
 ');
 }
}

if(array_key_exists('logoff', $_POST)){// ��������
 if(substr($_SESSION['ppBlog_admin'],2,8)==$_POST['_mobpwd']){
  $_SESSION = array();
  session_destroy();
 }
}

$body = $nav = '';

$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
$upto = $offset + ENTRY_NUM * 2;

// �إå��������������ȥ�ȥ��ƥ��꡼��󥯤Υ��쥯�ȥܥå���
$body = ' 
<div style="text-align:center;">
<h1>'.BLOG_NAME.'</h1>
<div style="text-align:center;">
 <form action="moby.php" method="get">
  <input type="hidden" name="mode" value="category" />
  <select name="sub">
   <option value="all"'.(isset($_GET['sub'])?'':' selected="selected"').'>���٤�</option>
';

$cat_list = @file(CATEGORY_LIST);

foreach ($cat_list as $cat){
 $sel = (urldecode($_GET['sub'])==trim($cat)) ? ' selected="selected"' : '';
 $body .= '<option value="'.urlencode(trim($cat)).'"'.$sel.'>'.trim($cat).'</option>'.NL;
}
$body .= '
 </select>
 <input type="submit" value="�� ��">
 </form>
</div>
</div>
';

/* ����ȥ꡼��ɽ�� */

if($_GET['mode']=='' || $_GET['sub']=='all'){
 $LOGS = my_glob_moby("\d{6}", LOG, SORT_BY_DATE);
 $LINES = array();
 if(count($LOGS) > 0){
  for($j=0;$j<count($LOGS);$j++){
   $LINES = array_merge($LINES, my_file_moby($LOGS[$j]));
   if(count($LINES) > $upto) break;
  }
 }
 $LINES = array_slice($LINES, $offset, ENTRY_NUM);
 list($nav, $LINES) = page_info_moby($LINES, '');
 foreach($LINES as $i=>$line){
  list($id,,$title,) = explode('|', $line);
  $comments = explode(',', $line);
  if(count($comments) > 1 && ENABLE_COMMENT){
    $com_link = '<a href="moby.php?mode=show_comment&amp;UID='.$id.'">['.(count($comments)-1).']</a>';
  } else $com_link = '';
  $_title = my_substr_moby($title, 18);
  $body .= '['.($i+1).']<a href="moby.php?mode=show&amp;UID='.$id.'" accesskey="'.($i+1).'">'.$_title.'</a> '.date('Y/m/d',$id).$com_link.'<br />'.NL;
 }
}

if($_GET['mode']=='category' && $_GET['sub']!=''){ // ���ƥ��꡼����
 $target = get_lines_by_category_moby(urldecode(trim($_GET['sub'])));
 $target = array_slice($target, $offset, ENTRY_NUM);
 list($nav, $target) = page_info_moby($target, 'mode=category&amp;sub='.urlencode($_GET['sub']).'&amp;');
 foreach($target as $i=>$line){
  list($id,,$title,) = explode('|', $line);
  $_title = my_substr_moby($title, 18);
  $body .= '['.($i+1).']<a href="moby.php?mode=show&amp;UID='.$id.'" accesskey="'.($i+1).'">'.$_title.'</a> '.date('Y/m/d',$id).'<br />'.NL;
 }
}

if($_GET['mode']=='show' && $_GET['UID']!=''){ // ��������
 $UID = $_GET['UID'];
 $LINES = my_file_moby(log_moby($UID));
 foreach($LINES as $line){
  if(preg_match("/^$UID\|/", $line)){
   $comments = explode(',', $line);
   if(count($comments) > 1 && ENABLE_COMMENT){
    $com_link = '<a href="moby.php?mode=show_comment&amp;UID='.$UID.'">[������:'.(count($comments)-1).']</a>';
   } else $com_link = '';
   list($id,$cat,$title,$com) = explode('|',$line);
   
   if(preg_match_all('/<img .*?src="([^"]+)" /', $com, $mt)){ // ������ޤ�Ȥ�
    $num = count($mt[1]);
    foreach ($mt[1] as $i => $imgf){
     if(!file_exists($imgf)){
      $com = preg_replace('/<img .*?src="'.preg_quote($imgf, "/").'" ([^\/]*?)\/>/','', $com);
     } else {
      $_img = basename($imgf);
      $size = getImageSize($imgf);
      $ratio = MAX_ISIZE / max($size[0], $size[1]);
      $w  = ($ratio<1) ? round($size[0] *$ratio) : $size[0];
      $h = ($ratio<1) ? round($size[1]*$ratio) : $size[1];
      if(MAX_ISIZE < max($size[0], $size[1])){
       switch ($size[2]){
        case 1 : //gif image
         if(!GD2){ // if GIF acceptable
          if(GD==true){
           if(!file_exists(IMG_DIR.THUMB2.$_img)){
            create_thumbnail_moby($imgf, IMG_DIR.THUMB2.$_img, max($w, $h), $size);
           }
           $img = IMG_DIR.THUMB2.$_img; // new image
          } else $img = IMG_DIR.$_img; // orig image
         } else $img = IMG_DIR.$_img;
         break;
        case 6 : // BMP image
         $img = IMG_DIR.$_img;
         break;
        default :
         if(!file_exists(IMG_DIR.THUMB2.$_img)){
          if(GD==true){
           create_thumbnail_moby($imgf, IMG_DIR.THUMB2.$_img, max($w, $h), $size);
          } else $img = IMG_DIR.$_img; // orig image
         }
         $img = (GD==true) ? IMG_DIR.THUMB2.$_img : IMG_DIR.$_img;
         break;
       } 
       $imgsrc .= '&nbsp; <a href="'.$img.'">[����'.($num==1?'':($i+1)).']</a>';
       $com = preg_replace(
        '/<img .*?src="'.preg_quote($imgf, "/").'" [^\/]*?\/>/','', $com);
      } else $imgsrc .= '&nbsp; <a href="'.$imgf.'">[����'.($num==1?'':($i+1)).']</a>';
     }
    }
   } else $imgsrc = '';
   $com = preg_replace(
        '/(?<!["|\'])(https?|ftp)(:\/\/[;\/\?:@&=\+\$,A-Za-z0-9\-_\.!~%#\|]+)/i',
        '<a href="$1$2">-&gt;&gt;��� </a>', $com);//
   
   $_body = str_replace('`','<br />',strip_tags($com,'<a>'));
   $_body = preg_replace('/\[file:(.*?)\/\]/','',$_body);
   $_body = preg_replace('/\[g\](.*?)\[\/g\]/i',
        "<a href=\"http://www.google.com/search?hl=ja&ie=SHIFT_JIS&oe=SHIFT_JIS&q=$1\">[G:$1]</a>", $_body);
   $previous = preg_match('/\//',array_pop(explode("?",$_SERVER['HTTP_REFERER']))) ? '' :
            '?'.array_pop(explode("?",$_SERVER['HTTP_REFERER'])); 
   $post = '<a href="moby.php'.$previous.'">[�������]</a>';
   $post .= '&nbsp; <a href="moby.php">[�ȥåפ�]</a><br />';
   if($mb){
    $post .= '<a href="moby.php?mode=post_comment&amp;TID='.$UID.'">[�����Ȥ���]</a>';
   }
   if(isset($_SESSION['ppBlog_admin'])){
    if($mb) $post .= '&nbsp; <a href="moby.php?mode=edit&amp;UID='.$UID.'">[Edit]</a> ';
    $post .= '<a href="moby.php?mode=delete&amp;UID='.$UID.'">[Del]</a>';
   }
   $body .= '<h3>'.$title.'</h3><br />'.$_body.NL.$imgsrc.NL.'<p>'.$com_link.NL.$post.'</p>';
   break;
  }
 }
}

if($_GET['mode']=='edit' && $_GET['UID']!=''){ // �������Խ�
 if(!isset($_SESSION['ppBlog_admin'])) exit('admin?');
 $UID = $_GET['UID'];
 $LINES = file(log_moby($UID));
 foreach($LINES as $line){
  if(preg_match("/^$UID\|/", $line)){
   list($id,$category,$title,$com,) = explode('|', $line); 
   break;
  }
 }
 $com = str_replace("`", "\n", $com);
 $com = rtrim($com);
 $body = '
 <div>
  <form action="moby.php" method="post">
  <input type="hidden" name="UID" value="'.$UID.'" />
  <input type="hidden" name="hint" value="��������������" />
  �����ȥ롧<input type="text" name="title" value="'.$title.'" /><br />
  ���ƥ��ꡧ<select name="category">
 ';
 foreach ($cat_list as $cat){
  $sel = ($category==trim($cat)) ? ' selected="selected"' : '';
  $body .= '<option value="'.trim($cat).'"'.$sel.'>'.trim($cat).'</option>'.NL;
 }
 $body .= '
  </select><br />
  �Խ����ơ�<br />
  <textarea name="edit" rows="4">'.$com.'</textarea><br />
  <input type="submit" name="post_article" value="��Ƥ���" /> 
  </form>
 </div>
 ';
}

if(array_key_exists('post_article',$_POST)){ // �Խ���������ƽ���
 if(!isset($_SESSION['ppBlog_admin'])) exit('admin?');
 if(!$mb) makeXHTML_mobile('�ޥ���Х��ȴؿ������ݡ��Ȥ���Ƥ��ޤ���');
 $UID = $_POST['UID'];
 $LOGF = log_moby($UID);
 $LINES = file($LOGF);
 $title = sanitize_data_moby(trim($_POST['title']));
 $com = sanitize_data_moby(rtrim($_POST['edit']));
 $logFormat = $UID.'|'.trim($_POST['category']).'|'.$title.'|'.$com.'|'.NL; // ���Υե����ޥå�
 $index = get_article_index_moby($LINES, $UID);
 array_shift($org_comments=explode('|,', $LINES[$index]));
 $org_comments = count($org_comments)>=1 ? '|,'.implode('|,', $org_comments) : '';
 if($mb==true){
  $encode = mb_detect_encoding($_POST['hint']);
 }
 $logFormat = ($mb==true) ? mb_convert_encoding(rtrim($logFormat), mb_internal_encoding(), $encode).$org_comments.NL :
                            jcode_convert_encoding(rtrim($logFormat), ENCODE).$org_comments.NL;
 array_splice($LINES, $index, 1, $logFormat);// ��ȤΥ��򿷤��������ؤ�
 rewrite_moby($LOGF, $LINES);
 $body = '
  <p>�� �� �� λ��</p>
  <a href="moby.php?mode=show&amp;UID='.$UID.'">[�Խ����������򸫤�]</a>
 ';
 include_once('utils.php');
 include_once('cache.php');
 update_cache();
 if(RSS1){
  include_once('modules/rss1.0.inc.php');
  createRSS10(time());
 }
 if(RSS2){
  include_once('modules/rss2.0.inc.php');
  createRSS20(time());
 }
 $nav = '';
}

if($_GET['mode']=='delete' && $_GET['UID']!=''){ // �����κ��
 if(!isset($_SESSION['ppBlog_admin'])) exit('admin?');
 $UID = $_GET['UID'];
 $body = '
 <div style="text-align:center">
 <p>��ۤɤε����������ޤ�����</p>
 <form action="moby.php" method="post">
  <input type="hidden" name="UID" value="'.$UID.'" />
  <input type="submit" name="do_delete" value="�� ��" /> 
  <input type="submit" name="cancel_delete" value="�� ��" />
 </form>
 </div>
 ';
 
}

if(array_key_exists('do_delete',$_POST)){ // �����κ���¹�
 if(!isset($_SESSION['ppBlog_admin'])) exit('admin?');
 $UID = $_POST['UID'];
 $LOGF = log_moby($UID);
 $LINES = file($LOGF);
 if(!is_array($LINES)) return;
 $tindex = get_article_index_moby($LINES, $UID);
 list(,,,$com) = explode('|', $LINES[$tindex]);
 array_splice($LINES,$tindex, 1);// $id���������Ǥ򥫥å�
 rewrite_moby($LOGF, $LINES);
 if(preg_match_all('/\[file:([^\/]+)\/\]/i', $com, $attached_files)){ // ;ʬź�եե��������
  foreach($attached_files[1] as $af) @unlink(PATH.$af);
 }
 if(preg_match_all('/<img .*?src="([^"]+)"/i', $com, $img_files)){ // ��������
  foreach($img_files[1] as $img){
   $_img = basename($img);
   @unlink(PATH.$img);
   if(file_exists(IMG_DIR.THUMB1.$_img)) @unlink(PATH.IMG_DIR.THUMB1.$_img);
   if(file_exists(IMG_DIR.THUMB2.$_img)) @unlink(PATH.IMG_DIR.THUMB2.$_img);
  }
 }
 $body = '
 <p>�����������ޤ�����</p>
 <a href="moby.php">[�ȥåפ�]</a>
 ';
 include_once('utils.php');
 include_once('cache.php');
 update_cache();
 if(RSS1){
  include_once('modules/rss1.0.inc.php');
  createRSS10(time());
 }
 if(RSS2){
  include_once('modules/rss2.0.inc.php');
  createRSS20(time());
 }
 $nav = '';
}

if(array_key_exists('cancel_delete',$_POST)){ // �����Υ���󥻥�
 header('Location: '.ROOT_PATH.'moby.php?mode=show&UID='.$_POST['UID']);
 exit;
}

if($_GET['mode']=='show_comment' && $_GET['UID']!=''){ // ������ɽ��
 $UID = $_GET['UID'];
 $LINES = my_file_moby(log_moby($UID));
 foreach($LINES as $line){
  if(preg_match("/^$UID\|/", $line)){
   list(,$cat,$title,) = explode('|',$line);
   $header = '<h3>'.$title.' ���Ф��륳����</h3>'.NL;
   $comments = explode(',', $line);
   $_com = '';
   foreach ($comments as $i => $c){
    if($i > 0){
     list($c_id, $c_name, $c_color,$c_com) = explode('|', $c);
     $c_com = '<div style="color:#'.$c_color.';">'.$c_com;
     $c_com = str_replace('~','<br />',$c_com);$del = isset($_SESSION['ppBlog_admin']) ?
            '<a href="moby.php?mode=delete_comment&amp;TID='.$UID.'&amp;CID='.$c_id.'">[Del]</a>' : '';
     $by = '<br />&mdash;'.$c_name.' @ '.date('h:i A Y-m-d', $c_id).'<br />'.$post.NL.$del;
     $c_com = autolink_moby($c_com).$by.'</div>';
     $_com .= $c_com;
    }
   }
   $post = ($mb) ? '<br /><a href="moby.php?mode=post_comment&amp;TID='.$UID.'">[�����Ȥ���]</a>' : '<br />';
   $post .= '&nbsp; <a href="moby.php">[�ȥåפ�]</a>';
   $body = $header.$_com.NL.$post;
   break;
  }
 }
}

if($_GET['mode']=='post_comment' && $_GET['TID']!=''){ // ���������
 $h = '
 <form method="post" action="moby.php">
  <input type="hidden" name="hint" value="��������������" />
  <input type="hidden" name="TID" value="'.$_GET['TID'].'" />
  <input type="hidden" name="CID" value="'.time().'" />
   ̾��: <input type="text" name="c_name" value="" /><br />
   ���������ơ�<br />
   <textarea name="c_com" id="c_com" rows="4"></textarea><br />
   <input type="submit" name="post_comment" value="�������� ��" />
 </form>
 ';
 $body = $h;
}

if(array_key_exists('post_comment',$_POST)){ // ��������ƽ���
 $TID = $_POST['TID'];
 $CID = $_POST['CID'];
 $LOGF = log_moby($TID);
 $LINES = @file($LOGF);
 if(!is_array($LINES)) return;
 $c_com = sanitize_data_moby($_POST['c_com']);     // ʸ������
 $c_name = $_POST['c_name']=='' ? 'anonymous' : sanitize_data_moby(trim($_POST['c_name']));
 
 if($c_com=='') makeXHTML_mobile('���������Ƥ�����Τ褦�Ǥ���');
 if($mb==true) $encode = mb_detect_encoding($_POST['hint']);
 $logFormat = ','.$CID.'|'.$c_name.'|73596d|'.trim($c_com)."|\n"; // ���Υե����ޥå�
 $logFormat = ($mb==true) ? mb_convert_encoding($logFormat, mb_internal_encoding(), $encode) : jcode_convert_encoding($logFormat, ENCODE);
 foreach ($LINES as $i => $val){
  if(preg_match("/^$TID\|/", $val)){
   if(preg_match("/$CID\|/",$val)) {
    $body = '::2����ƤΤ褦�Ǥ�::';
    break;
   } else {
    $newFormat = trim($val).$logFormat;
    array_splice ($LINES, $i, 1, $newFormat);
    break;
   }
  }
 }
 rewrite_moby($LOGF, $LINES);
 if(trim(MAILTO)!=''){
  include_once('modules/mail.inc.php');
  $comment_body = NL.NL.'���Ƥߤޤ��礦: '.ROOT_PATH.'index.php?UID='.$TID.NL
                 . '�ʲ��Ͽ������դ��������ȤǤ���'.NL.NL
                 . '������������'.date('Y-m-d H:i:s', $CID).' by '.$c_name.NL.NL
                 . str_replace('<br />', NL, $c_com);
  send_jp_mail("�����������Ȥ�����ޤ�:->", $comment_body);
 }
 $body = '
  <p>������ �� λ�� </p>
  <a href="moby.php?mode=show_comment&amp;UID='.$TID.'">[��Ϣ�Υ����Ȥ򸫤�]</a>
 ';
 include_once ('utils.php');
 include_once ('cache.php');
 update_cache();
 $nav = '';
}

if($_GET['mode']=='delete_comment'){ // �����Ⱥ������
 if(!isset($_SESSION['ppBlog_admin'])) exit();
 $TID = trim($_GET['TID']);
 $CID = $_GET['CID'];
 $LOGF = log_moby($TID);
 $LINES = @file($LOGF);
 if(!is_array($LINES)) return;
 
 foreach ($LINES as $i => $val){
  if(preg_match("/^$TID\|/", $val)){
   $comments = explode(',', $val);
   $new_comments = array();
   foreach ($comments as $j => $cmt){
    if(preg_match("/^$CID\|/", $cmt)){
     array_splice ($comments, $j, 1);
     break;
    }
   }
   array_splice ($LINES, $i, 1, implode(',',$comments));
   break;
  }
 }
 rewrite_moby($LOGF, $LINES);
 $body = '
 <p>���򤷤������Ȥ������ޤ�����</p>
 <a href="moby.php?mode=show_comment&amp;UID='.$TID.'">[��Ϣ�Υ����Ȥ򸫤�]</a>
 ';
 include_once('utils.php');
 include_once('cache.php');
 update_cache();
}

if(isset($_SESSION['ppBlog_admin'])){
 $mobpwd = substr($_SESSION['ppBlog_admin'],2,8);
 $logform = '
 <form method="post" action="moby.php">
  <input type="hidden" name="_mobpwd" value="'.$mobpwd.'" />
  <input type="submit" name="logoff" value="��������" />
 </form>
 ';
} else {
 $logform = '
 <form method="post" action="moby.php">
  <input type="text" name="mobpwd" size="8" />
  <input type="submit" name="login" value="������" />
 </form>
 ';
}

$footer .= '<hr />'.$nav.$logform;

$body .= trim($footer);

makeXHTML_mobile($body);


/* Useful function library */

function makeXHTML_mobile($body='', $title=BLOG_NAME, $css=''){ // about 500bytes with body empty
 global $mb;
 $xml = preg_match('/docomo\/1.0/i', $_SERVER['HTTP_USER_AGENT']) ? '' :
      '<?xml version="1.0" encoding="shift_jis"?>';
 $h = $xml.'
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
 <meta http-equiv="content-type" content="text/html;charset=shift_jis" />
 <meta http-equiv="content-style-type" content="text/css" />
 <title>'.($mb==true?mb_convert_encoding($title, "SJIS"):jcode_convert_encoding($title, "SJIS")).'</title>
</head>
<body>
'.($mb==true?mb_convert_encoding($body, "SJIS", ENCODE):jcode_convert_encoding($body, "SJIS")).'
</body>
</html>
';
 die(trim($h));
}

function log_moby($uid){// yyyymm�����Υ��ե�������֤�
 global $body;
 if(!file_exists(LOG.date('Ym', $uid).EXT)){
  $body .= '����ID�ε����Ϥ���ޤ���';
 }
 return LOG.date('Ym', $uid).EXT; 
}

function page_info_moby($LINES='', $query=''){
 global $nav, $upto;
 $entry = ENTRY_NUM;
 $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
 $pre = $offset - $entry;
 $total = count($LINES);
 if($offset>=$entry){
  $nav .= ' <a href="moby.php?'.$query.'offset='.$pre.'" accesskey="*">&lt;&lt;����'.$entry.'��</a>';
 }
 $upper = ($offset+$entry) < $total ? $offset+$entry : $total;
 $range = ($upper==1) ? 1 : ($offset+1)."-".$upper;
 if($entry+$offset<=$upto && $total >= ENTRY_NUM){
  $nav .= ' <a href="moby.php?'.$query.'offset='.($offset+$entry).'" accesskey="#">����'.$entry.'��&gt;&gt;</a>'.NL;
 }
 return array($nav, $LINES);
}

function create_thumbnail_moby($input, $output, $wh_size, $inputsize=''){
 $GD2 = function_exists('ImageCreateTrueColor') ? true : false;
 $size = ($inputsize=='') ? GetImageSize($input) : $inputsize;
 if($size[2]==1 && GD2==1){
  return; // GD2.0 and *.gif is invalid
 }
 
 $ratio = $wh_size / max($size[0], $size[1]); // �礭��������Ψ���碌��
 $_w  = ($ratio<1) ? round($size[0] *$ratio) : $size[0];
 $_h = ($ratio<1) ? round($size[1]*$ratio) : $size[1];
 if(GD==true && $size[2]!=6){
  switch ($size[2]){ 
   case 1 : $img_in = ImageCreateFromGIF($input); break;
   case 2 : $img_in = ImageCreateFromJPEG($input); break;
   case 3 : $img_in = ImageCreateFromPNG($input);  break;
  }
 
  $img_out = ($GD2==true) ? ImageCreateTrueColor($_w, $_h) : ImageCreate($_w, $_h);
  if($GD2==true){
   ImageCopyResampled($img_out, $img_in, 0, 0, 0, 0, $_w, $_h, $size[0], $size[1]);
  } else ImageCopyResized($img_out, $img_in, 0, 0, 0, 0, $_w, $_h, $size[0], $size[1]);
  switch ($size[2]) { 
   case 1 : ImageGIF($img_out, $output);  break;
   case 2 : ImageJPEG($img_out, $output); break;
   case 3 : ImagePNG($img_out, $output);  break;
  } 
  ImageDestroy($img_in);
  ImageDestroy($img_out);
 }
}

function get_lines_by_category_moby($cat){ // ���ꤷ�����ƥ��꡼�ε�����������֤�
 $articles = array();
 $lines_all = get_all_articles_moby();
 foreach ( $lines_all as $line ){
  if(preg_match("/\|".preg_quote(trim($cat),'/')."\|/", $line)){
   $articles = array_merge($articles, $line);
  }
 }
 return $articles;
}
function get_all_articles_moby($max=''){ // ���Ƥε�����������֤�. $max�����ꤵ�줿�餽�ο��ޤ�
 $LOGS = my_glob_moby("\d+", LOG, SORT_BY_DATE);
 $lines_all = array();
 foreach ($LOGS as $logs){
  $lines_all = array_merge($lines_all, my_file_moby($logs));
  if($max!=''){
   if(count($lines_all) > $max) break;
  }
 }
 return $lines_all;
}

function get_article_index_moby($logline, $entry){ // $entry�Υ���ǥå����򸡽�
 foreach($logline as $i=>$val){
  if(preg_match("/^$entry\|/", $val)){
   return $i;
   break;
  }
 }
 return -1;
}

function sort_by_date_moby($a, $b) {
 if($a == $b) return 0;
 return ($a > $b) ? -1 : 1;
}

function autolink_moby($link){
 $rep = array(
  '/(?<!["|\'])(https?|ftp)(:\/\/[;\/\?:@&=\+\$,A-Za-z0-9\-_\.!~%#\|]+)/i' => '<a href="$1$2">$1$2</a>',
  '/(\[link:)([[:alnum:]\S\+\$\?\.%,!#~*\/:@&=_-]+)(\])(.*?)(\[\/link\])/i' => "<a href=\"http://$2\">$4</a>"
 );
 return $link = preg_replace(array_keys($rep), array_values($rep), $link);
}

function sanitize_data_moby($str){ // log�������ǡ���������
 $str = get_magic_quotes_gpc() ? stripslashes($str) : $str;
 $str = array_key_exists('post_comment',$_POST) ? htmlspecialchars($str): $str;
 $str = str_replace('`', '&#96;', $str); // Ⱦ��`���Ѵ�
 $str = array_key_exists('post_comment',$_POST) ? nl2br($str) : $str;
 $rep = array_key_exists('post_comment',$_POST) ? '' : '`';
 $str = preg_replace("/\r\n|\r|\n/", $rep, $str);
 return str_replace('|', '&#124;', str_replace(',', '&#44;', $str));// ʸ���|(�ѥ���), ����ޤ��Ѵ�
}

function rewrite_moby($file, $data=''){ // fopen($file, "w")���ƥǡ���$data��񤭹���
 global $body;
 $fp = fopen($file, "wb") or $body = "�ѡ��ߥå���������Ϥ��äƤ��ޤ�����";
 flock($fp, LOCK_EX);
 if(is_array($data)){
  foreach($data as $value){
   fputs($fp, rtrim($value)."\n");
  }
 } else fputs($fp, $data);
 flock($fp, LOCK_UN);
 fclose($fp);
}
function my_substr_moby($string, $length){   //Implemented from ver1.3
 global $mb;
 if($mb){
  $string = (mb_strlen($string)>$length) ? mb_substr($string, 0, $length, ENCODE)."..." : $string;
 } else {
  $_len = $length*2;
  $string = (strlen($string)>$_len) ? substr($string, 0, ($_len - strlen($string)%2))."..." : $string;
 }
 return $string;
}
function my_file_moby($logfile, $forced=false){   //Implemented from ver1.3
 $lines = file($logfile);
 $_lines = array();
 if(isset($_SESSION['ppBlog_admin']) && $forced==false) return $lines;
 foreach ($lines as $line){
  if(preg_match('/^\d{10}d\|/',$line)) continue;
  $_lines[] = $line;
 }
 return $_lines;
}
function my_glob_moby($pattern, $dir='./', $sort_flag=false){   //Implemented from ver1.3
 $result = array();
 $d = opendir ($dir);
 $p = str_replace(array(".","*"),array("\.",".+"),$pattern);
 while ($file = readdir ($d)) {
  if(preg_match("/$p/", $file)) $result[] = $dir.$file; 
 }
 closedir ($d);
 if($sort_flag=='SORT_BY_DATE') usort($result, 'sort_by_date_moby');
 return $result;
}
?>
