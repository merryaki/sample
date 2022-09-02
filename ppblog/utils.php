<?php
/*
 Last modified:: 2004/09/19 12:06
*/
define('V_URL', '/(?<!["\'=])(https?|ftp)(:\/\/[;\/\?:@&=\+\$,\w\-\.!~%#\|]+)/');
define('ROOT_PATH', 'http://'.str_replace('//','/',$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'));
define('PATH', str_replace('//','/',dirname($_SERVER['SCRIPT_FILENAME']).'/'));
                            /*******************************************
                                         ユーティリティー関数群
                            *******************************************/

function echoHTML(){
 global $DIVISION, $D, $theme, $hd, $ua, $mode;
 $s1 = mtime();
 $theme_css = '<link rel="stylesheet" id="ppBlogCSS" href="theme/'.$theme.'/'.$theme.'.css" type="text/css" />';
 if($mode=='template'){
  if(p_('preview')!=''){
   switch (p_('sub')){
    case 'html' :
     $template = get_magic_quotes_gpc() ? stripslashes(p_('editarea')) : p_('editarea');
     $html = explode('\n',$template); break;
    case 'css' :
     $css = get_magic_quotes_gpc() ? stripslashes(p_('editarea')) : p_('editarea');
     $theme_css = "<style type=\"text/css\">\n".preg_replace('/\.?\/?Images/s','theme/'.$theme.'/Images',$css)."\n</style>\n";
     $template = 'theme/'.$theme.'/template.html';
     $html = file($template); break;
    case 'box' :
     $template = 'theme/'.$theme.'/template.html';
     $html = file($template); break;
   }
  } else {
   $template = 'theme/'.$theme.'/template.html';
   $html = file($template);
  }
 } else {
  $template = 'theme/'.$theme.'/template.html';
  $html = @file($template) or _header('index.php?theme=simple');
 }
 if(!preg_match("/msie 6/i", $ua)) echo '<?xml version="1.0" encoding="'.ENCODE.'"?>'.NL;
 if(ADMIN==true){
  if($mode=='edit'||$mode=='write'||$mode=='page'){
   $script = ' <script type="text/javascript" src="editor.js" charset="'.strtolower(ENCODE).'"></script>'.NL
            .' <link rel="stylesheet" href="editor.css" type="text/css" />';
  } elseif($mode=='config'||$mode=='template'){
   $script = ' <link rel="stylesheet" href="config.css" type="text/css" />';
  } 
 } else $script = '';
 $rss1 = (RSS1) ? '<link rel="alternate" type="application/rss+xml" title="RSS" href="'.ROOT_PATH.'rss/rss1.0.rdf" />' : '';
 $rss2 = (RSS2) ? '<link rel="alternate" type="application/rss+xml" title="RSS" href="'.ROOT_PATH.'rss/rss2.0.xml" />' : '';
 if($html!=str_replace('<!--USE_LINE_CALENDAR:1-->','',$html)){
  $line_calendar = LineCalendar($D['mon'],$D['year']);
  $box_calendar = '';
 } elseif($html!=str_replace('<!--BOX_CALENDAR:SELECTOR:1-->','',$html)){
  $line_calendar = '';
  $box_calendar = Calendar($D['mon'],$D['year'], true); // 月日のセレクトボックス付き
 } else {
  $line_calendar = '';
  $box_calendar = Calendar($D['mon'],$D['year'], false);
 }

 $divisions = array(
  '%_THEME_CSS_%'=>$theme_css,
  '%_RSS1_%'=>$rss1,
  '%_RSS2_%'=>$rss2,
  '%_SCRIPT_%'=>$script,
  '%_TITLE_%'=>$DIVISION['title'],
  '<!--USE_LINE_CALENDAR:1-->'=>$line_calendar,
  '<!--BOX_CALENDAR:SELECTOR:1-->'=>$box_calendar,
  '<!--BOX_CALENDAR:SELECTOR:0-->'=>$box_calendar,
  '%_RECENTLY_%'=>$DIVISION['recently'],
  '%_MENU_%'=>$DIVISION['menu'],
  '%_COMMENTS_%'=>$DIVISION['comments'],
  '%_TRACKBACKS_%'=>$DIVISION['trackbacks'],
  '%_CATEGORIES_%'=>$DIVISION['categories'],
  '%_ARCHIVES_%'=>$DIVISION['archives'],
  '%_OTHERS_%'=>$DIVISION['others'],
  '%_HEADER_%'=>$DIVISION['header'],
  '%_BODY_%'=>$DIVISION['body'],
  '%_TIME_%'=>substr(mtime()-$s1,0,7),
  '&UID'=>'&amp;UID'
 );
 $html = str_replace(array_keys($divisions), array_values($divisions), $html);
 echo implode('',$html);
}
function g_($query){ return $_GET[$query]=='' ? '' : trim($_GET[$query]);}
function p_($query){ return $_POST[$query]=='' ? '' : trim($_POST[$query]);}
function v_($query){global $vars; return $vars=='' ? '' : trim($vars[$query]);}

function log_($uid){// yyyymm形式のログファイルを返す
 global $mode;
 if(!file_exists(LOG.date('Ym', $uid).EXT)){
  if($mode=='submit' || $mode=='update') mk_fl(LOG.date('Ym', $uid).EXT);
  else return '';
 }
 return LOG.date('Ym', $uid).EXT; 
}

function get_article_index($logline, $entry){ // $entryのインデックスを検出
 foreach($logline as $i=>$val){
  if(preg_match("/^$entry\|/", $val)){
   return $i;
   break;
  }
 }
 return -1;
}

function catch_data(){ // 最終的なデータ処理. このppBlogでキモの関数
 global $mb, $mode, $maxsize, $DIVISION;
 if(ADMIN!=true) _header('admin.php');
 $draft = array_key_exists('draft',$_POST) ? true : false;
 $com = trim(p_('com'));
 $title = trim(p_('title'));
 $title =($title=='') ? 'タイトルなし' : $title;
 if($mode!='delete'){
  if($com==''){
   page_back();
   return $DIVISION['body'] = '内容は空白ではいけません。';
  }
 }
 $id = p_('UID')=='' ? time() : p_('UID');
 $new_id = -1;
 if( p_('mod_date') ){ // 時刻の修正時
  $_Y = $mb ? sprintf('%04d', mb_convert_kana(p_('Y'), "n", ENCODE)) : sprintf('%04d', p_('Y'));
  $_m = $mb ? sprintf('%02d', mb_convert_kana(p_('m'), "n", ENCODE)) : sprintf('%02d', p_('m'));
  $_d = $mb ? sprintf('%02d', mb_convert_kana(p_('d'), "n", ENCODE)) : sprintf("%02d", p_('d'));
  $_H = $mb ? sprintf('%02d', mb_convert_kana(p_('H'), "n", ENCODE)) : sprintf('%02d', p_('H'));
  $_i = $mb ? sprintf('%02d', mb_convert_kana(p_('i'), "n", ENCODE)) : sprintf('%02d', p_('i'));
  $_s = $mb ? sprintf('%02d', mb_convert_kana(p_('s'), "n", ENCODE)) : sprintf('%02d', p_('s'));
  $new_id = strtotime($_Y.'-'.$_m.'-'.$_d.' '.$_H.':'.$_i.':'.$_s);
  foreach (get_all_articles() as $a){
   if(preg_match("/^$new_id\|/", $a)){
    page_back(2000);
    return $DIVISION['header'] .= '<p class="alert">同一時刻の記事が既にあるようです。別の時刻に変えて下さい。</p>';
   }
  }
 }
 $target = file( log_($id) );// 元のターゲットログ
 if($new_id > 0){
  $target_new = (log_($id) != log_($new_id)) ? file( log_($new_id) ) : $target;
  $com = str_replace($id, $new_id, $com); // !!!
 }
 $_id = ($new_id > 0 ) ? $new_id : str_replace('d','',$id); // final id
 if($draft==true) $_id = $_id.'d';
 if($mode=='submit' && preg_match("/$id\|/", $target[0])){
  page_back();
  return $DIVISION['body'] = '<p class="alert">重複投稿です！</p>'.NL;
 }
 $back = '<button onclick="history.go(-1)" title=" 前に戻る ">Back</button>'.NL;
 
 for($i=0; $i<=5;$i++){                             // 添付ファイル処理
  if($_FILES['src']['name'][$i]!=''){
   if($_FILES['src']['error'][$i] >0 ){
    page_back(2000);
    if($_FILES['src']['error'][$i]==2){
     return $DIVISION['header'] .= '<p class="alert">ファイルサイズが設定値 '.round($maxsize/1024).' KB より大きいようです。</p>';
    } else return $DIVISION['header'] .= '<p class="alert">添付ファイルのアップロードエラー！</p>';
   }
   if($_FILES['src']['tmp_name'][$i]!=''){ // upload suceeded
    if(preg_match('/bmp|png|gif|p?jpe?g/i',$_FILES['src']['type'][$i])){
     $target_dir = IMG_DIR;
    } elseif(preg_match('/asf|avi|amc|3gp|mp4|nor|mpg|mpeg/i',$_FILES['src']['type'][$i])){
     $target_dir = MOV_DIR;
    } else $target_dir = ATTACHED_DIR;
    $attached_file = $target_dir.$_id.($i<3?'':'e').'_'.basename($_FILES['src']['name'][$i]);
    move_uploaded_file($_FILES['src']['tmp_name'][$i], $attached_file);
    if(isset($attached_file)){ // 添付ファイルが無事にupされたら
     if($target_dir==IMG_DIR){ // image file?
      $com = preg_replace("{file:///.*?/([^/]+\.)(bmp|png|gif|p?jpe?g)}i",IMG_DIR.$_id.($i<3?'':'e').'_$1$2',$com);
     } elseif($target_dir==MOV_DIR){
      $com = preg_replace("{\[file:($_id|)_?([^/]+)/]}i",'[mov:'.$_id.'_$2/]',$com);
     } else { // not bmp|png|jpg|gif
      $com = preg_replace("{\[file:($_id|)_?([^/]+)/]}i",'[file:'.$_id.'_$2/]',$com);
     }
    }
   }
  }
 }
 
 $com = sanitize_data($com);
 $title = sanitize_data($title);
 
 $com = preg_replace('{<img .*?src="file:///[^>]+/>}i','',$com); // この時点でfile:///は操作ミスなのでそのimgタグ消去
 if($mode=='update'){
  $com = ($draft==true) ?
   preg_replace('{([/|:]\d{10})(e?_)}','$1d$2',$com) : preg_replace('/([\/|:]\d{10})d(e?_)/','$1$2',$com);
  if($id!=$_id){
   rename_upfile($id, $_id, IMG_DIR);
   rename_upfile($id, $_id, IMG_DIR.THUMB1);
   rename_upfile($id, $_id, IMG_DIR.THUMB2);
   rename_upfile($id, $_id, ATTACHED_DIR);
   rename_upfile($id, $_id, MOV_DIR);
  }
 }
 $logFormat = $_id.'|'.trim(p_('category')).'|'.$title.'|'.$com.'|'.NL; // ログのフォーマット

 if(p_('send_ping_auto')==1){ // do auto-trackback
  $links = get_links(autolink($com));
  $tb_uris = array();
  if(!empty($links)){
   foreach ($links as $link){
    $tb_uris = array_merge($tb_uris, my_auto_discovery($link));
   }
  }
  foreach ($tb_uris as $tb_uri) cast_ping_data($com, $tb_uri);
 }

 if(p_('send_ping_manu')==1){ // manual trackback
  cast_ping_data($com);
 }

 if($mode=='update'){
  $index = get_article_index($target, $id);
  array_shift($org_comments=explode('|,', $target[$index]));
  $org_comments = count($org_comments)>=1 ? '|,'.implode('|,', $org_comments) : '';
  $logFormat = rtrim($logFormat).$org_comments.NL;

  if(!isset($target_new)){ // 日付の修正なしなら
   array_splice($target, $index, 1, $logFormat);// もとのログを新たに入れ替え
   rewrite(log_($_id), $target);
   update_cache();
  } else {                 // 日付の修正ありなら
   array_splice($target,$index, 1);// $idの配列要素をカット
   // アップされたファイルのIDを変更
   rename_upfile($id, $_id, IMG_DIR);
   rename_upfile($id, $_id, IMG_DIR.THUMB1);
   rename_upfile($id, $_id, IMG_DIR.THUMB2);
   rename_upfile($id, $_id, ATTACHED_DIR);
   rename_upfile($id, $_id, MOV_DIR);
   // トラックバックのIDも変更
   $ori_tb = my_glob("^$id",TB_DIR);
   if(!empty($ori_tb)){
    $new_tb = preg_replace("/([0-9]{10})([^0-9]+)/","$new_id$2",$ori_tb[0]);
    rename($ori_tb[0], $new_tb); 
   }
   make_trackback_DB('', TRUE);
   if(log_($id)==log_($new_id)){ // ログファイルが同じ
    array_unshift($target, $logFormat);
    usort($target, 'sort_by_date');
    rewrite(log_($id), $target);
    update_cache();
   } else {                      // ログファイルが別
    array_unshift($target_new, $logFormat);
    usort($target_new, 'sort_by_date');
    rewrite(log_($id), $target);
    rewrite(log_($_id), $target_new);
    update_cache();
   }
  }
  if(preg_match_all('/<img .*?src="([^"]+)"/i', $com, $img_files)){ // 余分画像の処理
   image_scavenger($_id, $img_files[1]);
   image_scavenger($_id, $img_files[1], THUMB1);
   image_scavenger($_id, $img_files[1], THUMB2);
  } else {
   image_scavenger($_id,'');
   image_scavenger($_id,'', THUMB1);
   image_scavenger($_id,'', THUMB2);
  }
  if(preg_match_all('/\[(file|mov):([^\/]+)\/\]/i', $com, $attached_files)){ // 余分添付ファイルも削除
   attached_file_scavenger($_id, $attached_files[2]);
  } else attached_file_scavenger($_id,'');
  if($draft!=true){
   if(RSS1){
    include_once('modules/rss1.0.inc.php');
    createRSS10(time());
   }
   if(RSS2){
    include_once('modules/rss2.0.inc.php');
    createRSS20(time());
   }
  }
  refresh_page("index.php?UID=$_id",1500);
  return $DIVISION['body'] = '<h4 class="center"> 更 新 完 了！ <br />すぐにページを更新します。</h4>';
  
 } elseif($mode=="submit"){  // 新規に書き庫
  if(!isset($target_new)){ // 日付の修正なしなら
   usort($target, 'sort_by_date'); // 日付の新しい順に
   array_unshift($target, $logFormat); // 先頭に追加
   rewrite(log_($_id), $target);
  } else {                 // 日付の修正ありなら
   usort($target_new, 'sort_by_date');
   array_unshift($target_new, $logFormat);
   usort($target, 'sort_by_date');
   rewrite(log_($_id), $target_new);
  }
  if($draft!=true){
   if(RSS1){
    include_once('modules/rss1.0.inc.php');
    createRSS10(time());
   }
   if(RSS2){
    include_once('modules/rss2.0.inc.php');
    createRSS20(time());
   }
  }
  update_cache();
  refresh_page("index.php?UID=$_id",1500);
  return $DIVISION['header'] .= '<h4 class="center">書き庫 完 了！ <br />すぐにページを更新します。</h4>';
 } elseif($mode=="delete"){
  if(ADMIN!=true) _header('admin.php');
  $tindex = get_article_index($target, $id);
  list($uid,,,$com) = explode('|', $target[$tindex]);
  array_splice($target,$tindex, 1);// $idの配列要素をカット
  rewrite(log_($id), $target);
  if(preg_match_all('/\[file:([^\/]+)\/\]/i', $com, $attached_files)){ // 余分添付ファイルも削除
   foreach($attached_files[1] as $af) @unlink(PATH.$af);
  }
  if(preg_match_all('/<img src="([^"]+)"/i', $com, $img_files)){ // 画像も削除
   foreach($img_files[1] as $img){
    $_img = basename($img);
    @unlink(PATH.$img);
    if(file_exists(IMG_DIR.THUMB1.$_img)) @unlink(PATH.IMG_DIR.THUMB1.$_img);
    if(file_exists(IMG_DIR.THUMB2.$_img)) @unlink(PATH.IMG_DIR.THUMB2.$_img);
   }
  }
  if(RSS1){
   include_once('modules/rss1.0.inc.php');
   createRSS10(time());
  }
  if(RSS2){
   include_once('modules/rss2.0.inc.php');
   createRSS20(time());
  }
  update_cache();
  refresh_page();
  $DIVISION['header'] .= '<h4 class="center">選択した記事を削除しました。<br />すぐにページを更新します。</h4>';
 }
}

function get_adjacent_article($uid){ // 隣接記事をゲット Revised in ver1.3.4
 $target = my_file(log_($uid));
 $index = get_article_index($target, $uid);
 $triplet = array();
 $triplet[1] = $target[$index];
 if(!empty($target[$index+1])){
  $triplet[0] = $target[$index+1];
 } else {
  $LOGS = my_glob("\d{6}", LOG, SORT_BY_DATE);
  foreach ($LOGS as $i=>$log){
   if($log==log_($uid)){
    $hit = $i; break;
   }
  }
  if(file_exists($LOGS[$hit+1])) $triplet[0] = array_shift(my_file($LOGS[$hit+1]));
 }
 if($index > 0 && !empty($target[$index-1])){
  $triplet[2] = $target[$index-1];
 } else {
  if(!isset($hit)){
   $LOGS = my_glob("\d{6}", LOG, SORT_BY_DATE);
   foreach ($LOGS as $i=>$log){
    if($log==log_($uid)){
     $hit = $i; break;
    }
   }
  }
  if($hit>0 && file_exists($LOGS[$hit-1])) $triplet[2] = array_pop(my_file($LOGS[$hit-1]));
 }
 unset($LOGS);
 $triplet = preg_replace('/(^\d{10})\|.*?\|(.*?)\|.*$/','$1|$2',$triplet);
 return $triplet;
}

function show_box($id=''){ // ボックス表示
 global $DIVISION, $_self, $D, $mode, $back;
 $url =  _SELF;
 $main = '<button onclick="location.href=\''.ROOT_PATH.$_self.'\';return false;" title="メインページへ">Main</button>';
 
 if(file_exists(log_($id))){
  $target = file(log_($id));
 } else $DIVISION['header'] .= '<h3>ファイル:'.log_($id).'は存在しません</h3>'.NL.$back;

 foreach($target as $i=>$line){
  if(preg_match("/^$id\|/", $line)){
   $triplet = get_adjacent_article($id);
   if(isset($triplet[2]))$nxt_ent = explode('|', $triplet[2]);
   if(isset($triplet[0]))$prv_ent = explode('|', $triplet[0]);
   if(isset($nxt_ent)){
    $next_entry = '<a href="'.$_self.'?UID='.$nxt_ent[0].'" title="次の記事:'.date('Y-m-d',$nxt_ent[0]).'">'.$nxt_ent[1].'≫</a>';
   } else $next_entry = '';
   if(isset($prv_ent)){
    $prev_entry = '<a href="'.$_self.'?UID='.$prv_ent[0].'" title="前の記事:'.date('Y-m-d',$prv_ent[0]).'">≪ '.$prv_ent[1].'</a>';
   } else $prev_entry = '';
   $DIVISION['header'] .= '<h2 class="nav1">'.trim($prev_entry).' | '.$next_entry.NL.'</h2>';
   $DIVISION['header'] .= '<h3 class="nav2"> Entry: '.date('Y年m月d日', $id).' </h3>'.NL;
   $comments = explode(',', $line);
   if(count($comments) > 1 && ENABLE_COMMENT && $mode!='trackback'){
    include_once('modules/comment.inc.php');
    $_com = '<p class="center"><strong style="color:#511339;">上の記事に対するコメント(ツッコミ)です。</strong></p>'.NL.' <div class="comment-div">'.NL;
    $_com .= '  <ul class="mark2">'.NL;
    foreach ($comments as $i => $c){
     list($c_id, $c_name, $c_color, $c_com) = explode('|', $c);
     if($i > 0){
      $del = (ADMIN) ? '<span class="del">&nbsp;&nbsp; <a href="'.$_self.'?mode=delete_comment&amp;TID='.$id.'&amp;CID='.$c_id.'">[Del]</a></span>' : '';
      $li = ($c_name==OWNER && $i!=1) ? NL.'    <ul>'.NL.'     <li class="nest2" style="color:#'.$c_color.';"> ' :
                                           '   <li style="color:#'.$c_color.';">';
      $_li = ($c_name==OWNER && $i!=1) ? '</li>'.NL.'    </ul>'.NL.'   </li>'.NL : '';
      $_com .= ($li.trim($c_com).'<span class="georgia"><a id="c'.$i.'"> &#8212; '
           .$c_name.'</a> @ '.date('h:iA Y-m-d', $c_id).'</span>'.$del.$_li);
     }
    }
    $_com .= '   </ul>'.NL.' </div>'.NL;
    $_com = preg_replace("/<\/span>   </", "</span></li>\n   <", $_com); // HTMLの整形
    $_com = autolink($_com);
   }
   list($id,$cat,$title,$com) = explode('|',$line);
   $com = preg_replace('{(<p>|)<!\-\-HIDE\-\->(.*?)(<!\-\-\/HIDE\-\->(<\/p>|)|$)}s',
      '<div><a href="#" onclick="toggle(this.parentNode);return false;">もっと読む&gt;&gt;</a>
        <div style="display:none">$1$2$4
         <a href="#" onclick="toggle(this.parentNode.parentNode);return false;" title="たたむ">[↑]</a>
        </div>
   </div>', $com);
   $com = my_parser($com, ENABLE_BR);
   $DIVISION['header'] .= put_RDF($id, $title, $cat, $com);
   $DIVISION['title'] = $title;
   $ttl = IE ? "クリックするとこの記事へのPermaLinkがクリップボードにコピーされます。" : "PermaLink";
   $posted = '<!--END-->'."\n     ".'<div class="post-foot"> &#8212;  posted by '.OWNER.' @ '.date('h:iA', $id).NL;
   $link = " | <a href=\"#\" title=\"$ttl\" onclick='return ToClipBoard(\"\",\"$url?UID=$id\")'>LinkMe</a>\n ";
   if(ENABLE_COMMENT){
    $com_count = count($comments)-1;
    $tips = ($com_count > 0) ? 'コメントの展開' : 'コメントの投稿';
    $comment = ' | <a title="'.$tips.'" href="'.$_self.'?mode=comment&amp;TID='.$id.'#comment_form">Comment('.$com_count.')</a> '.NL;
   } else $comment = '';
   if(ENABLE_TRACKBACK){
    $trackback = ' | <a href="'.$_self.'?mode=trackback&amp;UID='.$id.'">TrackBack('.get_tb_count($id).')</a></div>'.NL;
   } else $trackback = '</div>'.NL;
   $com = $com.$posted.$link.$comment.$trackback;
   return $DIVISION['body'] = _box($id,$cat,$title,$com, ADMIN, ADMIN) . $_com .NL. '<p class="center">'.$back.NL.'&nbsp;'.$main.'</p>';
   break;
  }
 }
 return $DIVISION['header'] .= '<p class="center">その記事はないようです。</p>'.NL.$back;
}

function show_box_all($LINES = ''){ // ボックス表示（マルチ）
 global $_self, $back, $mode, $DIVISION, $D;
 $url =  _SELF;
 $main = '<button onclick="return location.href=\''.ROOT_PATH.$_self.'\'" title="メインページへ">Main</button>';

 if(!is_array($LINES)){
  if(g_('date')!=''){ // date is set
   if(log_($D[0])!=''){ // log file is.
    $date = ($mode=='show') ? $D['year'].'年'.$D['mon'].'月'.$D['mday'].'日' : $D['year'].'年'.$D['mon'].'月';
    $DIVISION['header'] .= '<h3> ::: '.$date.' のエントリー :::</h3>'.NL;
    $LINES = ($mode=='show') ? get_lines_by_date(g_('date')) : my_file(log_($D[0]));
    $date_mode = ($mode=='show'&&count($LINES)>1) ? true : false;
   } else {
    $date_mode = false;
    return $DIVISION['body'] .= '<h3 class="alert">'.$D['year'].'年'.$D['mon'].'月のエントリーはありません。'.NL
                             . '<p class="center">'.$back.'</p>'.NL.'</h3>'.NL;
   }
  } else $LINES = page_info();
 }
 if(count($LINES) > ENTRY_LIST && !empty($_GET)) return show_lists($LINES);
 list($header, $LINES) = page_info($LINES);
 $DIVISION['header'] .= $header;
 $length = count($LINES);
 $body = '';
 
 for($i=0; $i < $length; $i++){
  list($id,$cat,$title,$com) = explode('|', $LINES[$i]);
  if($i<$length){
   list($next_id,) = explode('|',$LINES[$i+1]);
  } else $next_id = '';
  if($i>0){
   list($pre_id,) = explode('|',$LINES[$i-1]);
  } else $pre_id = '';
  $comments = explode(',', $LINES[$i]); // コメントデータ
  if($length==1){
   $triplet = get_adjacent_article($id);
   if(isset($triplet[2]))$nxt_ent = explode('|', $triplet[2]);
   if(isset($triplet[0]))$prv_ent = explode('|', $triplet[0]);
   if(isset($nxt_ent)){
    $next_entry = '<a href="'.$_self.'?UID='.$nxt_ent[0].'" title="次の記事:'.date('Y-m-d',$nxt_ent[0]).'">'.$nxt_ent[1].'≫</a>';
   } else $next_entry = '';
   if(isset($prv_ent)){
    $prev_entry = '<a href="'.$_self.'?UID='.$prv_ent[0].'" title="前の記事:'.date('Y-m-d',$prv_ent[0]).'">≪ '.$prv_ent[1].'</a>';
   } else $prev_entry = '';
   $DIVISION['header'] .= '<h2 class="nav1">'.trim($prev_entry).' | '.$next_entry.NL.'</h2>'.NL;

   if(count($comments) > 1 && ENABLE_COMMENT && $mode!='trackback'){
    include_once('modules/comment.inc.php');
    $_com = '<p class="center"><strong style="color:#511339;">上の記事に対するコメント(ツッコミ)です。</strong></p>'.NL.' <div class="comment-div">'.NL;
    $_com .= '  <ul class="mark2">'.NL;
    foreach ($comments as $i => $c){
     list($c_id, $c_name, $c_color, $c_com) = explode('|', $c);
     if($i > 0){
      $del = (ADMIN) ? '<span class="del">&nbsp;&nbsp; <a href="'.$_self.'?mode=delete_comment&amp;TID='.$id.'&amp;CID='.$c_id.'">[Del]</a></span>' : '';
      $li = ($c_name==OWNER && $i!=1) ? NL.'    <ul>'.NL.'     <li class="nest2" style="color:#'.$c_color.';"> ' :
                                           '   <li style="color:#'.$c_color.';">';
      $_li = ($c_name==OWNER && $i!=1) ? '</li>'.NL.'    </ul>'.NL.'   </li>'.NL : '';
      $_com .= ($li.trim($c_com).'<span class="georgia"><a id="c'.$i.'"> &#8212; '
           .$c_name.'</a> @ '.date('h:iA Y-m-d', $c_id).'</span>'.$del.$_li);
     }
    }
    $_com .= '   </ul>'.NL.' </div>'.NL;
    $_com = preg_replace("/<\/span>   </", "</span></li>\n   <", $_com); // HTMLの整形
    $_com = autolink($_com);
   }
  }
  $ttl = IE ? "クリックするとこの記事へのPermaLinkがクリップボードにコピーされます。" : "PermaLink";
  $com = preg_replace('{(<p>|)<!\-\-HIDE\-\->(.*?)(<!\-\-\/HIDE\-\->(<\/p>|)|$)}s',
       '<div><a href="#" onclick="toggle(this.parentNode);return false;">もっと読む&gt;&gt;</a>
         <div style="display:none">$1$2$4
          　<a href="#" onclick="toggle(this.parentNode.parentNode);return false;" title="たたむ">[↑]</a>
         </div>
        </div>', $com);
  $com = my_parser($com, ENABLE_BR);
  $com = str_replace('<p></p>','',$com);
  $posted = '<!--END-->'."\n     ".'<p class="post-foot"> &#8212;  posted by '.OWNER.' @ '.date('h:iA', $id).NL;
  $copy = IE ? " onclick='ToClipBoard(\"\",\"$url?UID=$id\");return true;'" : '';
  $link = " | <a href=\"$url?UID=$id\" title=\"$ttl\"$copy>LinkMe</a> ".NL;
  if(ENABLE_COMMENT){
   $com_count = count($comments)-1;
   $tips = ($com_count > 0) ? 'コメントを展開します' : 'コメントの投稿';
   $comment = ' | <a title="'.$tips.'" href="'.$_self.'?mode=comment&amp;TID='.$id.'#comment_form">Comment ('.$com_count.')</a> '.NL;
  } else $comment = '';

  if(ENABLE_TRACKBACK){
   $trackback = ' | <a href="'.$_self.'?mode=trackback&amp;UID='.$id.'">TrackBack ('.get_tb_count($id).')</a> | &nbsp;<a href="#container">top&uarr;</a></p>'.NL;
  } else $trackback = ' | <a href="#container">top&uarr;</a></p>'.NL;
  $__com = $com.$posted.$link.$comment.$trackback;
  
  $body .= put_RDF($id, $title, $cat, $com)
        ._box($id,$cat,$title,$__com, ADMIN, ADMIN, $date_mode, $i, ($i==$length-1)?true:false, $pre_id, $next_id);
 }
 return $DIVISION['body'] = $body.$_com.NL.$header.'<p class="center">'.$back.NL.'&nbsp;'.$main.'</p>'.NL;
}

function show_lists($lines = ''){ // リスト表示
 global $_self, $back, $mode, $DIVISION, $D, $LOGS;
 $main = '<button onclick="location.href=\''.ROOT_PATH.$_self.'\';return false;" title="メインページへ">Main</button>';
 list($header, $LINES) = page_info($lines, ENTRY_LIST);
 $DIVISION['header'] .= $header;
 $body = '<ul class="mark1" style="width:80%;margin:auto;">'.NL;
 foreach ($LINES as $line){
  list($id,$cat,$title,$com) = explode('|', $line);
  if(preg_match('/d/',$id) && ADMIN!=true) continue;
  $comment_num = (ENABLE_COMMENT) ? '('.(count(explode(',',$line))-1).')' : '';
  $trackback_num = (ENABLE_TRACKBACK) ? '('.get_tb_count($id).')' : '';
  $cat = (g_('mode')=='category') ? '' : '<span class="category">'.$cat.'</span>';
  $body .= '   <li><a href="'.$_self.'?UID='.$id.'">'.$title.'</a> &mdash; '.$cat.' <span class="date">'
        .date('F d, Y', $id).$comment_num.$trackback_num.' </span></li>'.NL;
 }
 $body .= '</ul>'.NL;
 return $DIVISION['body'] = $body.NL.'<p class="center">'.$back.NL.'&nbsp;'.$main.'</p>'.NL;
}

function my_file($logfile, $forced=false){
 if(!file_exists($logfile)) return array();
 $lines = file($logfile);
 $_lines = array();
 if(ADMIN==true && $forced==false) return $lines;
 foreach ($lines as $line){
  if(preg_match('/^\d+?d\|/',$line)) continue;
  $_lines[] = $line;
 }
 return $_lines;
}

function page_info($LINES='', $entry=ENTRY_BOX){ // これもキモです
 $offset = g_('offset')!='' ? g_('offset') : 0;

 if(!is_array($LINES)){
  $LOGS = my_glob("\d+",LOG, SORT_BY_DATE);
  $length = count($LOGS);
  if($length> 0){
   $LINES = my_file( $LOGS[0] );
   $total = count($LINES);
   if($total>$entry) return $LINES;
   for($j=0;$j<$length;$j++){
    $_lines = array_merge($_lines, my_file($LOGS[$j]));
    if(count($_lines) > $entry){
     $_lines = array_slice($_lines, 0, $entry); break;
    }
   }
   return $_lines; // revised on ver1.3.3rv
  } else return;
 } else {
  foreach ($LINES as $line){
   if(preg_match('/^\d+?d\|/',$line) && ADMIN!=true) continue;
   $_lines[] = $line;
  }
 }
 $total = isset($total) ? $total : count($_lines);
 $query = preg_replace('/&?offset=[0-9]+/', '', $_SERVER['QUERY_STRING']);
 $query = ($query=='') ? '' : $query.'&amp;';
 
 $pre = $offset - $entry;
 if(count($_lines) > 0) $LINES = array_slice($_lines, $offset, $entry);
 
 $upper = ($offset+$entry)<$total ? $offset+$entry : $total;
 
 if($total>1){
  $header = '<h4>'.NL;
  $latest = empty($_GET) ? '最新のエントリー ' : '';
  if($offset>=$entry){
   $header .= " <a href=\"index.php?{$query}offset=$pre\">"
          .  '<img src="Images/prev.png" width="15" height="12" alt="Prev" style="margin:0;" /></a>'.NL;
  }
  $header .= "$latest $total 件中の".($offset+1)."-".$upper."件を表示\n";
  if($entry+$offset<$total){
   $header .= '<a href="index.php?'.$query.'offset='.($offset+$entry).'">'
           .  '<img src="Images/next.png" width="15" height="12" alt="Next" style="margin:0;" /></a>'.NL;
  }
  
  $header .= '</h4>'.NL;
 }
 return array($header, $LINES);
}

function my_parser($string, $br_enabled=false){ // from ver1.3.3rv
 // [[<*>]] : HTMLタグ変換
 $string = preg_replace('/(\[\[)(.*?)(\]\])/e',"''.stripslashes(preg_replace('/</','&lt;','$2')).''",$string);
 
 if(!$br_enabled){ // 強制改行なし
  // 閉じブロック前の`(改行)は消去(\n)
  $string = preg_replace(
            '{(`+[[:space:]]*?)</(div|pre|ol|ul|dl|p|form|blockquote|fieldset|table)>}i',
            NL.'</$2>', $string
  );
  // 閉じブロック後の`(改行)は消去(\n)
  $string = preg_replace(
            '{</(div|pre|ol|ul|dl|p|form|blockquote|fieldset|table)>(`*)}i',
            '</$1>'.NL, $string
  );
  // ブロック要素間の`(改行)は消去(\n)
  $string = preg_replace(
            '/<(div|pre|ol|ul|dl|p|form|blockquote|fieldset|table)([^>]*?)>(.*?)(<\/\1>)/ise', // s!
            "'<$1$2>'.str_replace('`',NL,'$3').'$4'", $string
  );
  $string = preg_replace('/(.*?)(`+)/','<p>$1</p>'.NL,str_replace('\"','"',$string));
   // 最初のPだけ
  if(IE==true){ // :first-letter IEバグ対策
   $string = preg_replace('{^<p>(.*?)</p>}i','$1', $string);
  } else {
   $string = preg_replace('/^<p>(?!<)/i','<p class="cap">', $string);
  }
  return $string = str_replace('<p></p>','',$string);// remove extra '<p></p>'s, if any...
 } else { // 強制改行有効
  // ブロック前後の`(改行)は消去(\n)
  $string = preg_replace(
            '{(`[[:space:]]*?)</(div|pre|ol|ul|dl|p|form|blockquote|fieldset|table)>}i',
            NL.'</$2>', $string
  );
  $string = preg_replace(
            '{<(/?)(div|pre|ol|ul|dl|p|form|blockquote|fieldset|table)([^>]*?)>([[:space:]]*?`)}i',
            '<$1$2$3>'.NL, $string
  );
  $string = preg_replace( // PREタグの中の改行記号は削除
            '{<(pre)([^>]*?)>(.*?)(</\1>)}ise', // s is point
            "'<$1$2>'.str_replace('`',NL,'$3').'$4'", $string
  );
  return $string = preg_replace('/(.*?)`/','$1<br />'.NL,str_replace('\"','"',$string));
 }
}

function _box($id,$cat,$title,$com,$mod=false,$form=true,$date_mode=false,$index=0,$end=0,$pre_id='',$next_id=''){ // これもキモ
 global $ua, $hd, $mb, $theme, $mode, $tmp_box;
 if(preg_match_all('/<img .*?src="([^"]*?)"([^>]*?)\/>/i', $com, $mt)){ // 画像を含むとき
  foreach ($mt[1] as $i=>$imgf){
   if(!file_exists($imgf)){
    if(!preg_match("/\/P\/([[:alnum:]]+)\..*/", $imgf, $asin)){
     preg_match('/[:=" ](left|right)/i',$mt[0][$i], $align); 

     $align = empty($align) ? 'none' : $align[1];
     $com = str_replace(
      $mt[0][$i],
      '<img src="Images/notfound.png" alt="404 File Not Found" style="float:'.$align.';" />', $com);
    } else {
     if($mode!='mht'){
      $_asin = trim($asin[1]);
      $com = str_replace("<!--AMAZON:$_asin-->",get_http10($_asin), $com);
     }
    }
   } else {
    $_img = basename($imgf);
    $size = getImageSize($imgf);
    $ratio = MAX_ISIZE / max($size[0], $size[1]);
    $w = ($ratio<1) ? round($size[0] *$ratio) : $size[0];
    $h = ($ratio<1) ? round($size[1]*$ratio) : $size[1];
    $_size[3] = 'width="'.$w.'" height="'.$h.'"';
    if(MAX_ISIZE < max($size[0], $size[1])){
     preg_match('/alt="([^"]*?)"/',$mt[0][$i], $alt);
     preg_match('/[:=" ](left|right)/i',$mt[0][$i], $align);
     $align = empty($align) ? 'none' : $align[1];
     switch ($size[2]){
      case 1 : //gif image
       if(!GD2){ // if GIF acceptable
        if(GD==true){
         if(!file_exists(IMG_DIR.THUMB2.$_img)){
          create_thumbnail($imgf, IMG_DIR.THUMB2.$_img, max($w, $h), $size);
         }
         $img = 'src="'.IMG_DIR.THUMB2.$_img.'"'; // new image
        } else $img = 'src="'.IMG_DIR.$_img.'"'; // orig image
       } else {
        $img = 'src="'.IMG_DIR.$_img.'"';
       }
       break;
      case 6 : // BMP image
       $img = 'src="'.IMG_DIR.$_img.'"';
       break;
      default :
       if(!file_exists(IMG_DIR.THUMB2.$_img)){
        if(GD==true){
         create_thumbnail($imgf, IMG_DIR.THUMB2.$_img, max($w, $h), $size);
        } else $img = 'src="'.IMG_DIR.$_img.'"'; // orig image
       }
       $img = (GD==true) ? 'src="'.IMG_DIR.THUMB2.$_img.'"' : 'src="'.IMG_DIR.$_img.'"';
       break;
     }
     $com = preg_replace(
      '/'.preg_quote($mt[0][$i], "/").'/',
      '<a href="'.$imgf.'"><img '.$img.' '.$alt[0].' '.$_size[3].' class="photo" style="float:'.$align.';" /></a>', $com);
    }
   }
  }
 }
 
 // 動画ファイル表示処理
 if(preg_match_all('/\[mov:([^\/]+)\/\]/', $com, $movs)){
  $movies = '<div style="clear:both;color:#003264;margin-top:20px;">'.NL;
  foreach ($movs[1] as $_file){
   $_fname = array_pop(explode('_',$_file));
   $_fsize = @filesize(MOV_DIR.$_file) ? round(@filesize(MOV_DIR.$_file)/1024,1).'KB' : '';
   $_movf = MOV_DIR.$_file;
   if(preg_match('/mode=show|UID/',$_SERVER['QUERY_STRING'])){
    $clsid = preg_match('/msie|opera/i', $ua) ?
    'classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
     codebase="http://www.apple.com/qtactivex/qtplugin.cab"' : 'type="video/3gpp" data="'.$_movf.'"';
    $movies .= '
     <div style="margin:auto;width:145px;">
     <h6>::<a href="'.$_movf.'" title="動画へ直リンク">'.$_fname.'('.$_fsize.')</a>::</h6>
     <object '.$clsid.' width="144" height="176"> 
      <param name="src" value="'.$_movf.'" />
      <param name="autoplay" value="false" />
     </object>
     </div>
    ';
   } else {
    $movies .= 'Movie:<a href="'.$_movf.'" title="'.$_fname.' '.$_fsize.'"><img src="Images/movie.png" style="margin:0;vertical-align:middle;" alt="" /> '.$_fname.'</a>&nbsp;'.NL;
   }
   $com = str_replace('[mov:'.$_file.'/]','',$com);
  }
  $movies .= '</div>'.NL;
  $com = str_replace('<!--END-->','<!--END-->'.$movies, $com);
 }
 
 // 添付ファイル表示処理
 if(preg_match_all('/\[file:([^\/]+)\/\]/', $com, $files)){
  $attached = '<div style="clear:both;color:#003264;margin-top:20px;">'.NL.'添付ファイル： ';
  foreach ($files[1] as $_file){
   $_fname = array_pop(explode('_',$_file));
   $_fsize = @filesize(ATTACHED_DIR.$_file) ? round(@filesize(ATTACHED_DIR.$_file)/1024,1).'KB' : '';
   $attached .= $_fname.'<a href="index.php?mode=click&amp;loc='.$_file.'"><img src="Images/attached.png" style="margin:0;vertical-align:middle;" title="'.$_fname.' '.$_fsize.'" alt="" /></a>&nbsp;'.NL;
   $com = str_replace('[file:'.$_file.'/]','',$com);
  }
  $attached .= '</div>'.NL;
  $com = str_replace('<!--END-->','<!--END-->'.$attached, $com);
 }
 $com = preg_replace(V_URL,'<a href="$1$2">$1$2</a>', $com);
 // グーグル検索処理
 $com = preg_replace('/\[g\](.*?)\[\/g\]/i',
        '<a href="#" title="Google it!" onclick=\'return googleIt("$1");\'>$1</a><span class="google-it">G</span>', $com);
 
 $dat = date('Ymd', $id);
 $date = date('F d, Y', $id);
 $hd = checkHoliday($dat) ? '&nbsp;<span class="holiday">'.$hd->holidays[$dat].'</span>' : '';
 $mod = $mod ?
 ' <p>
  <input accesskey="E" title=" この内容を修正 " name="mode" value="Edit" type="submit" class="button" />&nbsp;
  <input accesskey="D" title=" 削除します " name="mode" value="Del" type="submit" class="button" />
 </p>' : '';
 $pre = '
<form method="post" action="index.php">
 <div class="hidden"><input type="hidden" name="UID" value="'.$id.'" /></div>
 ';
 $subcat = '<a href="index.php?mode=category&amp;sub='.urlencode($cat).'"> '.$cat.'</a>';
  $r = array(
  "%subcat%" => $subcat,
  "%hd%" => $hd,
  "%date%" => $date,
  "%title%" => $title,
  "%id%" => substr($id,0,10),
  "%com%" => NL.$com,
  "%mod%" => $mod
  );

 if($date_mode==false){
  $_tmp_box = $tmp_box;
  if(date('Ymd',$id)==date('Ymd',$next_id)){
   $_tmp_box = preg_replace('{(<h2 class="date">)(.*?)(%hd%%date%)(.*?)</h2>}s','$1$3</h2>$2',$_tmp_box);
   $_tmp_box = preg_replace('{</div><!--box.tmp-->}','<hr class="separator" />',$_tmp_box);
  }
  if(date('Ymd',$id)==date('Ymd',$pre_id)){
   $_tmp_box = preg_replace('{(<h2 class="date">)(.*?)(%hd%%date%)(.*?)</h2>}s','$1$3</h2>$2',$_tmp_box);
   $_tmp_box = preg_replace('{<div class="article">(.*?)%hd%%date%}s','$1',$_tmp_box);
  }
  $_tmp_box = str_replace(array_keys($r),array_values($r),$_tmp_box);
  $post = '
  </form>
  ';
  if(strstr($id,'d')){
   $pre = '<div style="border:dashed 2px #bd005f;"><p class="alert">未公開の記事です</p>'.$pre;
   $post = $post.'</div>';
  }
  return ($form) ? $pre.$_tmp_box.$post : $_tmp_box;
 } else {
  if(file_exists('theme/'.$theme.'/box2.tmp')){
   $_tmp_box = NL.get_file_content('theme/'.$theme.'/box2.tmp');
   if($index==0){
     $_pre = '<div class="article">'.$pre;
     $_tmp_box = preg_replace('/(<div class="bottom2">%mod%<\/div>)/i',"$1</form>",$_tmp_box);
   } else {
    $_tmp_box = preg_replace('/<h2 class="date2">%date%<\/h2>/i','',$_tmp_box);
    $_tmp_box = '<hr class="separator" />'.$pre.$_tmp_box.'</form>';
   }
   if($end==true) $_tmp_box .= '<div class="bottom"></div></div>'.NL;
   $_tmp_box = str_replace(array_keys($r),array_values($r),$_tmp_box);
  } else {
   $_tmp_box = ($index==0) ? '<div class="article">'.NL.'<h2 class="date2">'.$hd.$date.'</h2>'.NL :
             '<hr class="separator" />';
   $_tmp_box .= $pre.'
  <span class="cat2"><a href="index.php?mode=category">カテゴリー </a>≫ '.$subcat.'</span>
 <h3 class="title2">'.$title.'<code>ID:'.substr($id,0,10).'</code></h3>
 <div class="content">'.$com.'</div>
 <div class="bottom2">'.$mod.'</div></form>';
   if($end==true) $_tmp_box .= '<div class="bottom"></div>'.NL.'</div>'.NL;
  }
  if(strstr($id,'d')){
   $_pre = '<div style="border:dashed 2px #bd005f;"><p class="alert">未公開の記事です</p>'.$_pre;
   $_tmp_box = $_tmp_box.'</div>';
  }
  return $_pre.$_tmp_box;
 }
}

function show_category(){ // カテゴリーの一覧表示
 global $DIVISION;
 $cat_list = @file(CATEGORY_LIST);
 if(g_('sub')){
  $lines = get_lines_by_category(urldecode(g_('sub')), SORT_BY_DATE);
  $DIVISION['header'] .= '<h4>[ <a href="index.php?mode=category" style="text-decoration:underline;">カテゴリー</a> &gt;&gt; '
                      .urldecode(g_('sub')).' ]</h4>'.NL;
                      
  return $DIVISION['body'] = show_box_all($lines);
 } else {
  $DIVISION['header'] .= '<h4>現在，'.count($cat_list).' 個のカテゴリーがあります。</h4>';
 }
 $body = ' <ul class="mark1" style="width:180px;margin:auto;">'.NL;
 foreach ($cat_list as $sub){
  $no_articles = count(get_lines_by_category(trim($sub)));
  $sub_cat = ($no_articles>0) ?
   '<a href="index.php?mode=category&amp;sub='.urlencode($sub).'">'.trim($sub).' [ '.$no_articles.' ] </a>' :
   trim($sub).' [ 0 ]';
   if(trim($sub)==MISC && $no_articles==0) $sub_cat = '';
  if(!empty($sub_cat)) $body .= "   <li>$sub_cat</li>\n";
 }
 $body .= " </ul>\n";
 return $DIVISION['body'] = $body;
}

function show_archives(){ // エントリーのallクリック時 or mode==archives
 global $DIVISION;
 $LOGS = my_glob("^\d+", LOG, SORT_BY_DATE);
 $minus = 0;
 $_backup = 0;
 $MHTS = my_glob("^\d+", MHT_DIR, SORT_BY_DATE);
 $body = '<ul class="mark1" style="margin-top:5px;margin:auto;width:'.(ADMIN==true?'220px':'110px').';">'.NL;
 foreach($LOGS as $i=>$logs){
  $_logs[] = basename($logs);
  $_count = count( my_file($logs) );
  if($_count==0){
   $minus++;
   continue;
  }
  $__logs = substr($_logs[$i], 0,4)."年".substr($_logs[$i],4,2)."月 [".$_count."]";
  $makefile = NL.'&nbsp; <a href="index.php?mode=mht&amp;tlog='.substr($_logs[$i],0,6).'" title="MHTファイルを作成します">'
            . '[未バックアップ]</a>'.NL;
  if(ADMIN==true){
   if(!empty($MHTS)){
    foreach ($MHTS as $mht){
     $_mht = basename($mht);
     if(substr($_logs[$i],0,6)==substr($_mht,0,6)){
      $__mht[$i] = NL.'&nbsp; <a href="index.php?mode=mht&amp;tlog='.substr($_mht,0,6).'" title="最終作成日: '
      .date('Y/m/d H:i:s',@filemtime($mht)).'" style="color:#aaa;">[バックアップ済]</a>';
      $_backup++;
      break;
     } else $__mht[$i] = $makefile;
    }
   } else $__mht[$i] = $makefile;
  } 
  if(g_('archives')!=''){
   if(g_('archives')==substr($_logs[$i],0,6)) $body .= '<li>$__logs</li>'.NL;
   else $body .= '<li><a href="index.php?date='.substr($_logs[$i],0,6)."01\">$__logs</a></li>\n";
  } else $body .= '<li><a href="index.php?date='.substr($_logs[$i],0,6)."01\">$__logs</a>$__mht[$i]</li>\n";
 }
  $body .= "  </ul>\n";
  $DIVISION['header'] .= '<h4>::: 現在のアーカイブ数は '.(count($LOGS)-$minus).' です :::</h4>'.NL;
  if(ADMIN==true){
   $notyet=count($LOGS)-$_backup;
   if($notyet > 0){
    $DIVISION['header'] .= '<p class="alert">'.$notyet.' 件のアーカイブが未バックアップです。バックアップをお勧めします。</p>'.NL;
   }
  }
  return $DIVISION['body'] = $body;
}

function refresh_page($dest='index.php', $timer=1000){ // JavaScriptでのページ更新
 global $DIVISION;
 $DIVISION['header'] .= '<script type="text/javascript">setTimeout("self.location.href=\''.$dest.'\'",'.$timer.');</script>';
}

function page_back($timer=1000){ // JavaScriptでのページ更新
 global $DIVISION;
 $DIVISION['header'] .= '<script type="text/javascript">setTimeout("history.back(-1);",'.$timer.');</script>';
}

function cat_select($cat='全 般'){ // for select menu
 $cat_list = @file(CATEGORY_LIST);
 $c = '<select name="category">'.NL;
 foreach ($cat_list as $line){
  $s = ($cat==trim($line)) ? 'selected="selected"' : '';
  $c .= " <option value=\"".trim($line)."\" $s>".rtrim($line)."</option>\n";
 }
 $c .= "</select>\n";
 return $c;
}

function get_lines_by_date($date){ // yyyymmdd形式の日付に一致する記事を配列で返す
 $target = file(LOG.substr($date,0,6).EXT);
 $hits = array();
 foreach ($target as $line){
  list($id,) = explode('|', $line);
  if(date('Ymd', $id)==$date) $hits[] = $line;
 }
 return $hits;
}

function get_lines_by_category($cat, $sort=false){ // 指定したカテゴリーの記事を配列で返す
 $articles = array();
 $lines_all = get_all_articles();
 foreach ( $lines_all as $line ){
  if(preg_match("/^\d{10}\|".preg_quote(trim($cat),'/')."\|/", $line)){
   $articles = array_merge($articles, $line);
  }
 }
 unset($lines_all);
 if($sort=='SORT_BY_DATE') usort($articles, 'sort_by_date');
 return $articles;
}

function get_all_articles($max=''){ // 全ての記事を配列で返す. $maxが指定されたらその数まで
 $lines_all = array();
 foreach ($LOGS=my_glob("\d{6}",LOG) as $logs){
  $lines_all = array_merge($lines_all, my_file($logs));
  if($max!=''){
   if(count($lines_all) > $max) break;
  }
 }
 return $lines_all;
}

function get_recent_comments($limit=RECENT_COMMENTS){ /* 最近のコメントの取得(新しい順かつコメント順に) */
 $LOGS = my_glob("\d+",LOG, SORT_BY_DATE);
 $comments = $_comments = array();
 foreach ($LOGS as $log){
  $lines = my_file($log);
  $lines = preg_replace('/(\d+\|)(.*?\|)(.*?\|)(.*?\|),(.*?)$/', '$1$3,$5', $lines);
  if(count($lines) > 0){
   foreach ($lines as $line){
    if(preg_match('/,(.*?)/',$line)){
     $comments[] = preg_replace('/(\d+\|.*?\|).*/','$1',explode('|,',$line));
     if(count($comments)>=$limit) break 2;
    }
   }
  }
 }
 foreach ($comments as $i=>$cm){
  list($d,) = explode('|',$cm[count($cm)-1]);
  $_comments[$d] = $cm;
 }
 krsort($_comments);
 return $_comments;
}

function autolink($link){
 $rep = array(
  V_URL => "<a href=\"$1$2\">$1$2</a>",
  '/\[link:([;\/\?@&=\+\$,\w\-\.!~%#\|]+)(\])(.*?)\[\/link\]/i' => '<a href="http://$1">$3</a>'
 );
 return $link = preg_replace(array_keys($rep), array_values($rep), $link);
}

function sanitize_data($str){ // logに入れるデータの整形
 $str = get_magic_quotes_gpc() ? stripslashes($str) : $str;
 // 文中の|(パイプ), カンマ, $ は変換
 $a = array('`'=>'&#96;',','=>'&#44;','$'=>'&#36;','|'=>'&#124;');
 $str = str_replace(array_keys($a), array_values($a), $str);
 return preg_replace("/\r\n|\r|\n/",'`', $str);
}
function mtime(){ // for timekeeper
 list($usec, $sec) = explode(" ",microtime()); 
 return ((float)$sec + (float)$usec); 
}
function sort_by_date($a, $b) {
 if($a == $b) return 0;
 return ($a > $b) ? -1 : 1;
}
function mk_fl($name){ // ファイル自動生成
 if(!file_exists($name)){
  rewrite($name, '');
  chmod($name, 0606);
 } else return '';
}
function rewrite($file, $data='', $add=false){ // fopen($file, "w")してデータ$dataを書き込む
 global $DIVISION;
 if($add)$data = array_merge(file($file), $data);
 $fp = fopen($file, "wb") or $DIVISION['body'] = "パーミッションの設定はあっていますか？";
 flock($fp, LOCK_EX);
 if(is_array($data)){
  foreach($data as $value){
   fputs($fp, rtrim($value)."\n");
  }
 } else fputs($fp, $data);
 flock($fp, LOCK_UN);
 fclose($fp);
}

function get_file_content($file){
 return (PHP_VERSION >= '4.3.0') ? @file_get_contents($file) : @implode('', @file($file));
 /*
 if(PHP_VERSION >= '4.3.0') {
  return file_get_contents($file);
 } else {
  $fd = fopen ($file, "rb");
  while (!feof ($fd)){
   $buffer = fgets($fd, 4096);
   $lines[] = $buffer;
  }
  fclose ($fd);
  return implode('', $lines);
 }
 */
}

function image_scavenger($uid, $img_array, $dir=''){ // $uid の画像配列 $img_array に含まれない画像は削除
 $IMGS = my_glob("$uid",IMG_DIR.$dir);
 if(is_array($IMGS)){
  $extra_imgs = ($img_array!='') ? array_diff($IMGS, $img_array) : $IMGS;
 }
 if(!empty($extra_imgs)){
  foreach($extra_imgs as $img){
   @unlink(PATH.$img); // remove extra file
  }
 }
}

function attached_file_scavenger($uid, $file_array){ // $uid のファイル配列 $file_array に含まれないファイルは削除
 $FILES = array_merge(my_glob("^$uid", ATTACHED_DIR), my_glob("^$uid", MOV_DIR));
 if(!empty($FILES)){
  $extra_files = ($file_array!='') ? array_diff($FILES, $file_array) : $FILES;
 }
 if(!empty($extra_files)){
  foreach($extra_files as $file){
   @unlink(PATH.ATTACHED_DIR.$file); // remove extra file
  }
 }
}

function rename_upfile($uid, $new_id, $dir=''){ // 日付修正時に使用. 添付ファイルの付加IDを変える
 $ori_files = my_glob("$uid", PATH.$dir);
 if(!empty($ori_files)){
  foreach ($ori_files as $file){
   if(preg_match("/\d{10}d_/",$file) && preg_match('/d/',$new_id)) continue;
   rename($file, str_replace($uid, $new_id, $file)); // ID変更
  }
 }
}

function tb_log_scavenger(){ // 空のTBファイルは削除
 $TBS = my_glob("\d+", TB_DIR);
 if(is_array($TBS)){
  foreach($TBS as $tb){
   $tb_content = get_file_content($tb);
   if(!empty($tb_content)){
    $a = unserialize($tb_content);
    if(empty($a)) unlink($tb);
   }
  }
 }
}

function checkHoliday($date){ // 休日チェック
 global $hd;
 if($hd->holidays[$date]!='') return 1;
 else return 0;
}

function _header($to){
 header("Location: ".ROOT_PATH.$to);
 exit;
}
function P($s){//Debug
 echo '<pre style="height:100px;">';
 print_r($s);
 echo '</pre>'.NL;
}

function create_thumbnail($input, $output, $wh_size, $inputsize=''){
 $GD2 = function_exists('ImageCreateTrueColor') ? true : false;
 $size = ($inputsize=='') ? GetImageSize($input) : $inputsize;
 if($size[2]==1 && $GD2==true){
  return; // GD2.0 and *.gif is invalid
 }
 
 $ratio = $wh_size / max($size[0], $size[1]); // 大きい方に比率を合わせる
 $_w  = ($ratio<1) ? round($size[0] *$ratio) : $size[0];
 $_h = ($ratio<1) ? round($size[1]*$ratio) : $size[1];
 if(GD==true && $size[2]!=6){
  switch ($size[2]){ 
   case 1 : $img_in = ImageCreateFromGIF($input); break;
   case 2 : $img_in = ImageCreateFromJPEG($input); break;
   case 3 : $img_in = ImageCreateFromPNG($input);  break;
  }
 
  $img_out = ($GD2==true) ? ImageCreateTrueColor($_w, $_h) : ImageCreate($_w, $_h);
  if(function_exists('ImageCopyResampled')==true){
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

function get_tb_count($uid){ // 2004/06/06 revised
 $tb_DB = DB.'trackback.db';
 if(file_exists($tb_DB)){
  $tb_content = get_file_content($tb_DB);
  if(!empty($tb_content)){
   $tb_db_data = unserialize($tb_content);
   if(array_key_exists("$uid", $tb_db_data))return $tb_db_data[$uid];
   else return '0';
  }
 }
 $file = TB_DIR.$uid.TB_EXT;
 if(file_exists($file)){
  if(@filesize($file) > 0 ){
   return count( unserialize( get_file_content($file) ) );
  } else return '0';
 } else return '0';
}
function make_trackback_DB($uid='',$forced=FALSE){
 $tb_DB = DB.'trackback.db';
 mk_fl($tb_DB);
 $tb_content = get_file_content($tb_DB);
 if(empty($tb_content) || $forced==TRUE){
  $TBS = my_glob("\d{10}",TB_DIR);
  $tb_db = array();
  $TBID = preg_replace("/.*?(\d{10}).*/","$1", $TBS);
  foreach ($TBID as $i=>$tb_id){
   $count = count(unserialize(get_file_content(TB_DIR.$tb_id.TB_EXT)));
   if($count==0){
    unlink($TBS[$i]);
    continue;
   }
   $tb_db[$tb_id] = $count;
  }
  if(!empty($tb_db)) rewrite($tb_DB, serialize($tb_db));
 }
 if(!empty($uid)){
  if(!empty($tb_content)) $tb_db_data = unserialize($tb_content);
  if(array_key_exists($uid, $tb_db_data)){
   $tb_db_data[$uid] = $tb_db_data[$uid] + 1;
  } else $tb_db_data[$uid] = 1;
  uksort($tb_db_data, 'sort_by_date');
  rewrite($tb_DB, serialize($tb_db_data));
  unset($tb_db_data);
 }
}

/* Glob function */
function my_glob($pattern, $dir='./', $sort_flag=false){   //Implemented from ver1.3
 $result = array();
 $d = opendir ($dir);
 $p = str_replace(array(".","*"),array("\.",".*"),$pattern);
 while ($file = readdir ($d)) {
  if(preg_match("/$p/", $file)) $result[] = $dir.$file; 
 }
 closedir ($d);
 if($sort_flag=='SORT_BY_DATE') usort($result, 'sort_by_date');
 return $result;
}

function my_substr($string, $length){   //Implemented from ver1.3
 global $mb;
 if($mb){
  $string = (mb_strlen($string)>$length) ? mb_substr($string, 0, $length, ENCODE)."..." : $string;
 } else {
  $_len = $length*2;
  $string = (strlen($string)>$_len) ? substr($string, 0, ($_len - strlen($string)%2))."..." : $string;
 }
 return $string;
}

function cast_ping_data($com='', $ping_target=''){
 global $back, $mb, $DIVISION;
 $excerpt = p_('excerpt')=='' ? str_replace('`','',$com) : p_('excerpt');
 $excerpt = strip_tags($excerpt);
 $excerpt = preg_replace("/\[g\](.*?)\[\/g\]/",'$1',$excerpt);
 $excerpt = my_substr($excerpt,255);
 if($mb==true){
  $info = array(
   'entry'     => time(),
   'url'       => mb_convert_encoding($_REQUEST['url'], 'utf-8', 'auto'),
   'title'     => mb_convert_encoding($_REQUEST['title'], 'utf-8', 'auto'),
   'excerpt'   => mb_convert_encoding($excerpt, 'utf-8', 'auto'),
   'blog_name' => mb_convert_encoding($_REQUEST['blog_name'], 'utf-8', 'auto'),
   'ping_url'  => $_REQUEST['ping_url']
  );
 } else {
  if(file_exists(PATH.'jcode_wrapper.php')){
   include_once(PATH.'jcode_wrapper.php');
   $info = array(
    'entry'     => time(),
    'url'       => jcode_convert_encoding($_REQUEST['url'], 'utf-8'),
    'title'     => jcode_convert_encoding($_REQUEST['title'], 'utf-8'),
    'excerpt'   => jcode_convert_encoding($excerpt, 'utf-8', 'auto'),
    'blog_name' => jcode_convert_encoding($_REQUEST['blog_name'], 'utf-8'),
    'ping_url'  => $_REQUEST['ping_url']
   );
  }
 }
 if(empty($info['url'])) $info['url'] = 'http://'.$_SERVER['REMOTE_ADDR'];
 $ping_url = !empty($info['ping_url']) ? parse_url(stripslashes($info['ping_url'])) : parse_url($ping_target);
 $info['title'] = stripslashes($info['title']);
 $info['blog_name'] = stripslashes($info['blog_name']);
 $info['excerpt'] = stripslashes($info['excerpt']);
 if(empty($ping_url)){
  return $DIVISION['header'] .= '<div class="alert">Ping-url is empty!<br />'.$back.'</div>';
 }
 $put_data = "url=".$info['url']."&title=".$info['title']."&blog_name=".$info['blog_name']."&excerpt=".$info['excerpt']; 

 $response = tb_http($ping_url['host'], $ping_url['path'], "$put_data&".$ping_url['query']);
 if(!$response[0]){
  $DIVISION['header'] .= '<h4 class="center alert">'.$response[1].'</h4>'.NL;
  $DIVISION['header'] .= '<h4 class="center alert">トラックバック送信失敗！</h4>'.NL;
 } else {
  $DIVISION['header'] .= '<h4 class="center alert">トラックバック送信成功！</h4>'.NL;
 }
}

function tb_http($host, $path, $data){
 $fp = @fsockopen($host, 80, $errno='', $errstr, $timeout=15);
 if(!$fp){
  return array(0, "相手のサーバーエラーです。");
 }
 if(socket_set_timeout($fp, $timeout)){
  $req = "POST $path HTTP/1.0\n";
  $req .= "Host: $host\n";
  $req .= "Content-type: application/x-www-form-urlencoded\n";
  $req .= "Content-length: ". strlen($data) ."\n";
  $req .= "Connection: close\n\n";
  $req .= "$data\n";
  fputs($fp, $req);
  while(!feof($fp)){
   $res .= @fgets($fp, 128);
  }
  fclose($fp);
  if(preg_match('{<error>0</error>}s', $res)) return array(1, $res);
  return array(0, "指定されたトラックバックURIは無効かあるいは何かしらの送信エラーの可能性があります。");
 }
}

function put_RDF($uid, $title, $category, $body){ // revised at ver1.3
 global $_self;
 $body = my_substr(str_replace('`','',strip_tags($body)),255);
 return '
<!--
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
         xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
<rdf:Description
    rdf:about="'.ROOT_PATH.$_self.'?UID='.$uid.'"
    trackback:ping="'.ROOT_PATH.'trackback.php?TBID='.$uid.'"
    dc:identifier="'.ROOT_PATH.$_self.'?UID='.$uid.'"
    dc:title="'.str_replace('"','\"',$title).'"
    dc:subject="'.str_replace('"','\"',$category).'"
    dc:description="'.str_replace('"','\"',$body).'"
    dc:creator="'.OWNER.'"
    dc:date="'.date('D, d M Y H:i:s+09:00', $uid).'" />
</rdf:RDF>
-->
';
}

function get_links($c, $isURI=FALSE){
 $contents = $isURI ? get_file_content($c) : $c;
 preg_match_all('/<a.*?href=("|\')(http:\/\/[^"\']+)("|\')[^>]*>/i', $contents, $links);
 return array_unique($links[2]);
}

function my_auto_discovery($uri){
 $page = get_file_content($uri);
 preg_match_all("/(<rdf:RDF xmlns:rdf.*?<\/rdf:RDF>)/s", $page, $RDF);
 return preg_replace('/(.+)trackback:ping="(http:\/\/[^"]+)".*/s','$2',$RDF[1]);
}

function ping_form($ping_url='', $blog_name=BLOG_NAME){
 global $_self;
 $url = p_('UID')=='' ? ROOT_PATH.$_self : ROOT_PATH.$_self.'?UID='.p_('UID');
 $str =<<<__HTML
  <div id="pingform" style="display:none;">
   <table border="0" cellspacing="3" cellpadding="1">
    <tr>
     <td class="rt"><label for="ping_url">TrackBack Ping URL</label>(手動では必須)</td>
     <td><input id="ping_url" name="ping_url" size="60" value="" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="blog_name">Blog name:</label></td>
     <td><input id="blog_name" name="blog_name" size="35" value="$blog_name" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="excerpt">Excerpt（概要。空白でもOK）</label></td>
     <td><input id="excerpt" name="excerpt" size="60" maxlength="250" value="" /></td>
    </tr>
    <tr>
     <td class="rt"><label for="url">Permalink URL:</label></td>
     <td><input id="url" name="url" size="60" value="$url" /></td>
    </tr>
   </table>
  </div>
__HTML;
 return $str;
}

function get_referrer(){
 $LOG = UD."referrer.txt";
 mk_fl($LOG);
 $agent = $_SERVER['HTTP_USER_AGENT'];
 $ref = $_SERVER['HTTP_REFERER'];
 $ip = $_SERVER['REMOTE_ADDR'];
 $LINES = file($LOG);
 list($_time,$_ip,$_ref,) = explode('|', $LINES[0]);
 $log = empty($ref) ? '' : time().'|'.$ip.'|'.$ref.'|'.$agent;

 if( date('Ymd', $_time)==date('Ymd',time())){
  if(!empty($log)){
   if($ip!=$_ip) array_unshift($LINES, trim($log)."\n");
  } 
 } else {
  $LINES = array();
  if(!empty($log)) array_unshift($LINES, trim($log)."\n");
 }
 rewrite($LOG, $LINES);
}
function read_referrer(){
 $LINES = file(UD."referrer.txt");
 $ref = '<div class="referrer">'.NL
      . '<h3><em>Today\'s Referrer</em></h3>'.NL;
 foreach ($LINES as $line){
 
  list(,,$referrer,$agent) = explode('|', $line);
  if(preg_match("/opera 7\./i",$agent)) $agent = 'Opera7.x';
  elseif(preg_match("/opera 6\./",$agent)) $agent = 'Opera6.x';
  elseif(preg_match("/msie 6\./i",$agent)) $agent = 'MSIE6.0';
  elseif(preg_match("/msie 5\./i",$agent)) $agent = 'MSIE5.x';
  elseif(preg_match("/msie 4\./i",$agent)) $agent = 'MSIE4.x';
  elseif(preg_match("/netscape/i",$agent)) $agent = 'Netscape6+';
  elseif(preg_match("/gecko/i",$agent)) $agent = 'Mozilla';
  elseif(preg_match("/safari/i",$agent)) $agent = 'Safari';
  elseif(preg_match("/lunascpe 1\./i",$agent)) $agent = 'Lunascape 1.x';
  else $agent = 'Another';
  $referrer = str_replace(ROOT_PATH."index.php",BLOG_NAME.'/',$referrer);
  $ref .= "$agent :: $referrer <br />\n";
 }
 $ref .= '</div>'.NL;
 return $ref;
}

function get_http10($asin, $devt='D2BUEA9DCZZ5YB',$aID='ppblog-22'){
 global $mb;
 $url = 'http://xml-jp.amznxslt.com/onca/xml3?dev-t='.$devt.'&f=xml&t='.$aID.'&locale=jp&type=heavy&AsinSearch='.$asin;
 $url = parse_url($url);
 $url['query'] = isset($url['query']) ? '?'.$url['query'] : '';
 $url['port'] = isset($url['port']) ? $url['port'] : 80;

 $request  = "GET ".$url['path'].$url['query']." HTTP/1.0\r\n";
 $request .= "Host: ".$url['host']."\r\n";
 $request .= "User-Agent: PHP/".phpversion()."\r\n\r\n";

 if(!$fp = fsockopen($url['host'], $url['port'])){
  $reply .= '';
 }
 fputs($fp, $request);

 $reply = '';
 while (!feof($fp)) {
  $reply .= fgets($fp, 4096);
  if(preg_match('/<\/Availability>/',$reply)) break;
 }
 fclose($fp);
 $info = '';
 if(preg_match('/<ReleaseDate>(.*?)<\/ReleaseDate>.*?<ListPrice>(.*?)<\/ListPrice>.*?<OurPrice>(.*?)<\/OurPrice>.*?<SalesRank>(.*?)<\/SalesRank>.*?<Availability>(.*?)<\/Availability>/s', $reply, $mt)){
  if($mb==true){
   $info = '<br />
 リリース: '.mb_convert_encoding($mt[1],'euc-jp','utf-8').'<br />
 定価: '.str_replace('￥','',mb_convert_encoding($mt[2],'euc-jp','utf-8')).' 円 <br />
 アマゾン価格: '.str_replace('￥','',mb_convert_encoding($mt[3],'euc-jp','utf-8')).' 円 <br />
 売り上げランキング: '.mb_convert_encoding($mt[4],'euc-jp','utf-8').' 位 <br />
 ☆'.mb_convert_encoding($mt[5],'euc-jp','utf-8').'<br style="clear:both;" /></p>
  ';
  } else {
   if(file_exists(PATH.'jcode_wrapper.php')){
    include_once(PATH.'jcode_wrapper.php');
    $info = '<br />
 リリース: '.jcode_convert_encoding($mt[1],'euc-jp','utf-8').'<br />
 定価: '.str_replace('￥','',jcode_convert_encoding($mt[2],'euc-jp','utf-8')).' 円 <br />
 アマゾン価格: '.str_replace('￥','',jcode_convert_encoding($mt[3],'euc-jp','utf-8')).' 円 <br />
 売り上げランキング: '.jcode_convert_encoding($mt[4],'euc-jp','utf-8').' 位 <br />
 ☆'.jcode_convert_encoding($mt[5],'euc-jp','utf-8').'<br style="clear:both;" /></p>
  ';
   }
  }
 }
 return $info;
}

function theme_selector(){
 $themelist_file = UD.'themelist.txt';
 $c = '<div id="styleSwitch">
 <form action="index.php" method="get">
 <div>
 <select name="theme" onchange="location.href=\'index.php?theme=\'+this.options[this.selectedIndex].value;this.blur();">
 ';
 $theme_lists = file($themelist_file);
 $c .= '  <option value="" selected="selected">■テーマ</option>'.NL;
 foreach($theme_lists as $theme){
  list(,$theme,) = explode('/', $theme);
  $c .= '  <option value="'.$theme.'">'.$theme.'</option>'.NL;
 }
 $c .= ' </select>
 </div>
 </form>
</div>'.NL;
 return $c;
}
?>