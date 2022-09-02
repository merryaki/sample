<?php
/*
TrackBack script by martin
Last modified on 2004/08/17 04:42
*/
session_start();

include_once('usr/ini.inc.php');
include_once('utils.php');
include_once('cache.php');
include_once('modules/mail.inc.php');
if($mb==true){
 mb_language("Japanese");
 mb_internal_encoding(ENCODE);
} else {
 if(file_exists(PATH.'jcode_wrapper.php')){
  include_once(PATH.'jcode_wrapper.php');
 } else die('Multibyte Not Supported.');
}
$mode = $_REQUEST['mode'];
if(trim(MAILTO)!='' && empty($mode)){
 include_once('modules/mail.inc.php');
}

define('ADMIN',(isset($_SESSION['ppBlog_admin']) && $_SESSION['ppBlog_admin']==md5(OPASS)) ? TRUE : FALSE);

if(isset($_REQUEST['excerpt'])){
 $excerpt = $_REQUEST['excerpt'];
 $excerpt = my_substr($excerpt, 255);
}
if($mb==true){
 $info = array(
  'entry'     => time(),
  'url'       => rawurlencode($_REQUEST['url']),
  'title'     => rawurlencode(mb_convert_encoding($_REQUEST['title'], ENCODE, 'auto')),
  'excerpt'   => rawurlencode(mb_convert_encoding($excerpt, ENCODE, 'auto')),
  'blog_name' => rawurlencode(mb_convert_encoding($_REQUEST['blog_name'], ENCODE, 'auto')),
  'ping_url'  => $_REQUEST['ping_url']
 );
} else {
 $info = array(
  'entry'     => time(),
  'url'       => rawurlencode($_REQUEST['url']),
  'title'     => rawurlencode(jcode_convert_encoding($_REQUEST['title'], ENCODE)),
  'excerpt'   => rawurlencode(jcode_convert_encoding($excerpt, ENCODE)),
  'blog_name' => rawurlencode(jcode_convert_encoding($_REQUEST['blog_name'], ENCODE)),
  'ping_url'  => $_REQUEST['ping_url']
 );
}

$target_id = isset($_REQUEST['TBID']) ? $_REQUEST['TBID'] : '';

if(preg_match("/[0-9]+/",$target_id)) define('TB_FILE', TB_DIR.$target_id.TB_EXT);

switch ($mode){
 case 'delete' :
  if(ADMIN==TRUE && defined('TB_FILE')){
   $data = unserialize(get_file_content(TB_FILE));
   $index = $_GET['index'];
   array_splice($data, empty($index) ? 0 : $index, 1);
   rewrite(TB_FILE, serialize($data));
   tb_log_scavenger();
   unlink(DB.'trackback.db');
   make_trackback_DB('', true); // refresh DB
   update_cache();
   _header("index.php?mode=trackback&UID={$target_id}");
  } else _header('admin.php');
 break;

 case 'cast_ping' :
  if(ADMIN==TRUE){
   if($_REQUEST['ping_url']!='') cast_ping_data();
   ping_form_b();
  } else _header('admin.php?mode=cast_ping');
 break;
 
 default :
  if(empty($info['url']) || empty($info['title'])) tb_response("Information Not Enough!", 1);
  
  if(defined('TB_FILE')){
   $data = unserialize(get_file_content(TB_FILE));
   if(!is_array($data)) $data = array();
   array_unshift($data, $info);
   rewrite(TB_FILE, serialize($data));
   unlink(DB.'trackback.db');
   make_trackback_DB($target_id); // pseudoDBにも追加
   update_cache();
   $target = my_file(log_($target_id));
   list($tid,$cat,$title,) = explode('|', $target[get_article_index($target, $target_id)]);
   $trackback_body = NL.NL.'見てみましょう: '.ROOT_PATH.'index.php?mode=trackback&UID='.$target_id.NL
                   . '以下は新しく付いたトラックバックです。'.NL.NL
                   . 'ターゲットエントリー：'.$title.' posted @ '.date('Y-m-d H:i:s', $tid).NL
                   . '　　　　　カテゴリー：'.$cat.NL
                   . '　トラックバック日時：'.date('Y-m-d H:i:s', $info['entry']).NL
                   . '　　　　　　サイト名：'.rawurldecode($info['blog_name']).NL.NL
                   . '              概　要：'.NL.str_replace('<br />', NL, my_wordwrap(rawurldecode($info['excerpt'])));
   if(trim(MAILTO)!='') send_jp_mail("新しいトラックバックがあります:->", $trackback_body);
   tb_log_scavenger();
   tb_response();
  }
 break;
}

function tb_response($msg = "", $error = 0){
 header("Content-Type: text/xml\n\n");
 echo<<<__HTML
<?xml version="1.0" encoding="utf-8" ?>
<response>
<error>$error</error>
<message>$msg</message>
</response>
__HTML;
 exit;
}

function ping_form_b($ping_url='', $blog_name=BLOG_NAME){
 global $DIVISION;
 $url = ROOT_PATH.'index.php';
$body=<<<__HTML
 <div style="font:600 14px arial;margin:auto;width:550px;">
 <form method="post" action="index.php">
  <div class="hidden"><input type="hidden" name="mode" value="cast_ping" /></div>
   <table border="0" cellspacing="3" cellpadding="1" class="ping-form">
    <tr>
     <td class="rt"><label for="ping_url">TrackBack Ping URL</label>:</td>
     <td><input id="ping_url" name="ping_url" size="60" value="$ping_url" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="ttl">Title</label>:</td>
     <td><input id="ttl" name="title" size="60" value="" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="blog_name">Blog name:</label></td>
     <td class="lt"><input id="blog_name" name="blog_name" size="35" value="$blog_name" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="excerpt">Excerpt:</label></td>
     <td><input id="excerpt" name="excerpt" size="60" maxlength="250" value="" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="url">Permalink URL:</label></td>
     <td><input id="url" name="url" size="60" value="$url" /></td>
    </tr>
   </table>
  <div class="center"><input type="submit" value="submit" class="button" /></div>
 </form>
 </div>
__HTML;
 $DIVISION['header'] .= '<h2>ppBlog::PingForm</h2>';
 return $DIVISION['body'] .= $body;
}

function my_wordwrap($string, $width=76){
 if(function_exists('mb_strcut')==true){
  $lines = explode(NL, $string);
  foreach($lines as $line){
   do{
    $trimmed .= mb_strcut($line, 0, $width).NL;
    $line = mb_strcut($line, $width);
   } while (strlen($line) >= $width);
   $trimmed .= $line;
  }
  return $trimmed;
 } else return $string;
}

?>
