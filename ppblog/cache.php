<?php
/*
 cache.php by martin
  ｜予め「Recently」「Recent Comments」「Recent TrackBack」「Category」「Archives」のエントリーや
  ｜数をシリアル化してcache.dbファイルに保存して，ページ作成時には，ここから読み取る。
*/

$cache_file = 'cache/cache.db';
mk_fl($cache_file);

$_cache = unserialize(get_file_content($cache_file));
if(empty($_cache)) update_cache(); else return;

function make_cache(){
 $LOGS = my_glob("\d+", LOG, SORT_BY_DATE);
 foreach($LOGS as $log){ // 空のログは削除
  $c = get_file_content($log);
  if(empty($c)) unlink(PATH.$log);
 }
 
 if(ENTRIES){
  if(empty($_cache['recently'])){
   if(count($LOGS) > 0){
    $_lines = array();
    for($j=0;$j<count($LOGS);$j++){
     $lines = my_file($LOGS[$j]);
     $_lines = array_merge($_lines, $lines);
     if(count($_lines) > RECENT_ENTRIES) break;
    }
   }
   if(!empty($_lines)) $LINES = array_slice($_lines, 0, RECENT_ENTRIES);
   if(!empty($LINES)){
    foreach($LINES as $line){
     list($id,,$title,) = explode('|', $line);
     $_cache['recently'][$id] = $title;
    }
   }
  }
 }

 if(ENABLE_COMMENT){
  if(empty($_cache['recent_comments'])){
   $_cache['recent_comments'] = get_recent_comments();
  }
 }

 if(ENABLE_TRACKBACK){
  if(empty($_cache['recent_trackbacks'])){
   $lines_all = get_all_articles();
   $files = my_glob('\d{10}'.TB_EXT, TB_DIR, SORT_BY_DATE);
   foreach ($files as $file){
    if(preg_match("/(\d{10})/", $file, $mt)){
     $tb_contents = unserialize( get_file_content($file) );
     if($reg=array_values(preg_grep("/^$mt[1]\|/", $lines_all))){
      list(,,$title,) = explode('|', $reg[0]);
      $_title = my_substr($title,13);
      if(count($tb_contents)>0){
       $tbs[$mt[1]][0] = count($tb_contents).'|'.($_title==$title?$title:$_title.'|'.$title);
      }
     }
     foreach ($tb_contents as $i=>$tbc){
      $blog_name = !empty($tbc['blog_name']) ? rawurldecode($tbc['blog_name']) : rawurldecode($tbc['url']);
      $blog_name = get_magic_quotes_gpc() ? stripslashes($blog_name) : $blog_name;
      $blog_name = my_substr($blog_name,17);
      if(isset($tbs[$mt[1]][0])) $tbs[$mt[1]][$i+1] = $tbc['entry'].'|'.$blog_name;
     }
     if(count($tbs)>=RECENT_TRACKBACKS){
      break;
     }
    }
   }
   $_cache['recent_trackbacks'] = $tbs;
  }
 }

 if(CATEGORIES){
  $cat_list = @file(CATEGORY_LIST);
  if(empty($_cache['category'])){
   foreach ($cat_list as $sub){
    $_sub = trim($sub);
    $no_articles = count(get_lines_by_category($_sub));
    if($no_articles==0) continue;
    $_cache['category'][$_sub] = $no_articles;
   }
  }
 }

 if(ARCHIVES){
  $arc = array();
  $LOGDATE = preg_replace("/.*?(\d{6}).*/","$1", $LOGS);
  foreach($LOGDATE as $i=>$log){
   $arc[$log] = count(my_file($LOGS[$i], true));
  }
  $_cache['archives'] = $arc;
 }
 
 return $_cache;
}

function update_cache(){
 global $cache_file;
 unlink($cache_file);
 $cache = make_cache();
 if(!empty($cache)){
  rewrite($cache_file, serialize($cache));
 }
}
?>
