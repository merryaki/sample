<?php
$ua = $_SERVER['HTTP_USER_AGENT'];
if(preg_match('/docomo|up\.browser|j\-phone|vodafone|pdxgw|astel|l\-mode/i',$ua)){
 header('Location: http://'.str_replace('//','/',$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/moby.php'));
 exit;
}
session_start();
include_once('usr/ini.inc.php');
header ("Content-Type: text/html; charset=".ENCODE);

if($mb){
 mb_language('Japanese');
 mb_internal_encoding(ENCODE);
} else {
 if(!file_exists(PATH.'jcode_wrapper.php')){
  die('Firstly, please get jcode_1.35a or higher at http://www.spencernetwork.org/ .');
 }
}

define('ADMIN',(isset($_SESSION['ppBlog_admin']) && $_SESSION['ppBlog_admin']==md5(OPASS)) ? TRUE : FALSE);
include_once('utils.php');
include_once('cache.php');

$DIVISION = array(
 'title'=>'','header'=>'','body'=>'','menu'=>'',
 'recently'=>'','comments'=>'','trackbacks'=>'','categories'=>'','archives'=>'',
 'others'=>''
);

if(USE_THEME_CHANGER==true){
 if(isset($_COOKIE['ppBlog_cookie'])){
  list($cc_name, $cc_col, $theme) = explode(',', $_COOKIE['ppBlog_cookie']);
 } else {
  $cc_name = ''; $cc_col = '333';
 }
 if(empty($theme)) $theme = DEFAULT_THEME;
 if(g_('theme')!=''){
  $theme = g_('theme');
  setcookie('ppBlog_cookie', "$cc_name,$cc_col,".$theme, time()+30*24*3600);
 } else setcookie('ppBlog_cookie', "$cc_name,$cc_col,".$theme, time()+30*24*3600);
} else { // USE_THEME_CHANGER==false
 if(isset($_COOKIE['ppBlog_cookie'])){
  list($cc_name, $cc_col,) = explode(',', $_COOKIE['ppBlog_cookie']);
 } else {
  $cc_name = ''; $cc_col = '333';
 }
 $theme = DEFAULT_THEME;
 setcookie('ppBlog_cookie', "$cc_name,$cc_col,".$theme, time()+30*24*3600);
}

if(file_exists('theme/'.$theme.'/box.tmp')){
 if(p_('sub')=='box'){
  $tmp_box = get_magic_quotes_gpc() ? stripslashes(p_('editarea')) : p_('editarea');
 } else $tmp_box = get_file_content('theme/'.$theme.'/box.tmp');
} else {
 $tmp_box = '
 <div class="article">
  <h2 class="date">
   <span class="cat"><a href="index.php?mode=category">���ƥ��꡼ </a>�� %subcat%</span>%hd%%date%
  </h2>
  <h3 class="title">%title% <code>ID:%id%</code></h3>
  <div class="content">%com%</div>
  <div class="bottom">%mod%</div>
 </div><!--box.tmp-->
 ';
}

if(USE_THEME_CHANGER) $DIVISION['header'] .= theme_selector();   // �ơ����ڤ��ؤ��ܥå���ɽ��

if(USE_COUNTER){   // �����󥿡�ɽ��
 include_once('ppcounter.php');
 $DIVISION['header'] .= $pp_counter;
}

/* ��󥯥С��Υ������� */
$bookmarks_icon = 'theme/'.$theme.'/Images/bookmark.png';
$category_icon = 'theme/'.$theme.'/Images/category.png';
$archives_icon = 'theme/'.$theme.'/Images/archives.png';
$refresh_icon = 'theme/'.$theme.'/Images/refresh.png';
$search_icon = 'theme/'.$theme.'/Images/search.png';
$comment_icon = 'theme/'.$theme.'/Images/comment.png';
$trackback_icon = 'theme/'.$theme.'/Images/trackback.png';
$gallery_icon = 'theme/'.$theme.'/Images/gallery.png';
$login_icon = 'theme/'.$theme.'/Images/login.png';


$DIVISION['header'] .= '
<div class="headline">
 <ul>
  <li><a href="index.php?mode=bookmarks"><img src="'.$bookmarks_icon.'" alt="" title="�֥å��ޡ���" /></a></li>
  <li><a href="index.php?mode=category"><img src="'.$category_icon.'" alt="" title="���ƥ��꡼��" /></a></li>
  <li><a href="index.php?mode=archives"><img src="'.$archives_icon.'" alt="" title="������������" /></a></li>
  <li><a href="index.php"><img src="'.$refresh_icon.'" alt="" '
 .(g_('UID')!=''?'title="�ȥåץڡ�����"':'title=" �� �� "').' /></a></li>'.NL
 .'  <li><a href="index.php?mode=search"><img src="'.$search_icon.'" alt="" title="����" /></a></li>'
 .(ENABLE_COMMENT?NL.'  <li><a href="#comments"><img src="'.$comment_icon.'" alt="" title="�Ƕ�Υ�����" /></a></li>':NL)
 .(ENABLE_TRACKBACK?NL.'  <li><a href="#trackbacks"><img src="'.$trackback_icon.'" alt="" title="�Ƕ�Υȥ�å��Хå�" /></a></li>':NL)
 .NL.'  <li><a href="index.php?mode=gallery"><img src="'.$gallery_icon.'" alt="" title="���������꡼" /></a></li>'.NL
 .(ADMIN==true?'  <li><a href="admin.php?mode=logout"><img src="'.$login_icon.'" alt="" title="��������" /></a></li>':
               '  <li><a href="admin.php?mode=login"><img src="'.$login_icon.'" alt="" title="������" /></a></li>')
 .'
 </ul>
</div>
';

/** BLOG BAR **/
if(is_array($img=my_glob('blogBar*.png',UD)) && !empty($img) && USE_BLOG_BAR){
  $size = getimagesize($img[0]);
  $on = 'Generated on '.date('l j, Y',substr($img[0],15,10));
  if(ADMIN==true){
   $blogbar = '
  <div class="blog-bar">
   <a href="blogBar.php"><img src="'.$img[0].'" '.$size[3].' alt="blogBar" title="BLOG BAR���Խ�" /></a>
  </div>
   ';
  } else {
  $blogbar = '
  <div class="blog-bar">
   <img src="'.$img[0].'" '.$size[3].' alt="blogBar" title="'.$on.'" />
  </div>
  ';
  }
} else $blogbar = '';
/** END_OF_BLOG BAR **/

if(ADMIN==true){
 $DIVISION['header'] .='
<div id="control">
 <ul>
  <li><a href="index.php?mode=write" title="���������������">New Post</a></li>
  <li><a href="index.php?mode=page" title="�������ڡ����κ������Խ�">Page Cont.</a></li>
  <li><a href="index.php?mode=config" title="�٤���������">Configuration</a></li>
  <li><a href="index.php?mode=section" title="���������ƥ��꡼���ɲä���">Edit Category</a></li>
  <li><a href="index.php?mode=template" title="�ƥ�ץ졼�Ȥ��Խ�">Edit Template</a></li>
 </ul>
</div>
';
}
if(RECENTLY){ // ����ȥ꡼
 $recently .= '
   <h5 class="box-cap" title="�Ƕ�ε���">Recently</h5>
   <div class="box-body">
    <ul class="mark1">'.NL;
 if(!empty($_cache['recently'])){
  foreach($_cache['recently'] as $id=>$title){
   if(preg_match('/d/',$id)) continue;
   $_title = my_substr($title,13);
   $recently .= '     <li>'
   . '<a href="index.php?mode=show&amp;UID='.$id.'" title="'.htmlspecialchars($title).' '.date('Y/m/d',$id).'">'.$_title.'</a></li>'.NL;
  }
 }
 $recently .= '    </ul>
   </div>
   <div class="box-foot"></div>'.NL;
}

if(MENU){ // ��˥塼���ꥢ
  $menu .= '
   <h5 class="box-cap" title="��˥塼">Menu</h5>
   <div class="box-body">
    <ul class="center">'.NL;
 $page_list = @file(UD.'page_list.txt');
 if(is_array($page_list)){
  foreach ($page_list as $page){
   list($lastmod,$page_name,) = explode('|', $page);
   $menu .= '     <li><a title="��������'.date('Y-m-d H:i:s', $lastmod).'" href="'.$_self.'?mode=page&amp;target='.strtoupper(join('',unpack('H*0',$page_name))).'">'.$page_name.'</a></li>'.NL;
  }
 }
 $menu .= '    </ul>
   </div>
   <div class="box-foot"></div>'.NL;
}

if(ENABLE_COMMENT){  // �Ƕ�Υ�����ɽ��
 $comments = '
   <h5 class="box-cap" title="�Ƕ�Υ�����">Recent Comments</h5>
   <div class="box-body" style="line-height:105%;">
    <ul class="mark2">'.NL;
 if(!empty($_cache['recent_comments'])){
  foreach($_cache['recent_comments'] as $_comments){
   $parent = array_shift($_comments);
   list($uid,$title) = explode('|', $parent);
   if(preg_match('/d/',$uid)) continue;
   $_title = my_substr($title, 11);
   $comments .= "     <li><a title=\"$title ".date('Y/m/d',$uid)."\" href=\"$_self?mode=comment&amp;TID=$uid\">$_title</a>\n";
   $comments .= '      <ul>'.NL;
   foreach ($_comments as $i=>$_c){
    list($t,$p) = explode('|', $_c);
    $aw = ($p==OWNER) ? '      <li class="nest2">' : '       <li class="nest1">';
    $comments .=  "$aw <a href=\"$_self?mode=comment&amp;TID=$uid#c".($i+1)."\" title=\"".date('m/d H:iA', $t)."\">$p</a></li>\n";
   }
   $comments .= '      </ul>'.NL.'     </li>'.NL;
  }
 }
 $comments .= '    </ul>
   </div>
   <div class="box-foot"></div>'.NL;
}

if(ENABLE_TRACKBACK){ // �Ƕ�Υȥ�å��Хå�ɽ��
  $trackbacks .= '
   <h5 class="box-cap" style="letter-spacing:0;" title="�Ƕ�Υȥ�å��Хå�">Recent TrackBacks</h5>
   <div class="box-body">
    <ul class="mark2">'.NL;
 $recent_trackback = $_cache['recent_trackbacks'];
 if(!empty($recent_trackback)){
  foreach ($recent_trackback as $tid=>$tb){
   if(preg_match('/d/',$tid)) continue;
   list(,$title,$ftitle) = explode('|', $tb[0]);
   $trackbacks .=  "     <li><a href=\"$_self?mode=trackback&amp;UID=$tid\" title=\"".$ftitle."\">$title</a>\n";
   for($i = 1; $i < count($tb); $i++){
    $trackbacks .= '      <ul class="mark2">'.NL;
    list($entry,$blog_name) = explode('|',$tb[$i]);
    $trackbacks .= '       <li class="nest1"><a href="'.$_self.'?mode=trackback&amp;UID='.$tid.'#t'.$i.'" title="Tracked on '.date('Y/m/d H:iA', $entry).'">'.$blog_name.'</a></li>'.NL;
    $trackbacks .= '      </ul>'.NL;
   }
   $trackbacks .= '     </li>'.NL;
  }
 }
 $trackbacks .= '    </ul>
   </div>
   <div class="box-foot"></div>'.NL;
}

if(CATEGORIES){ // ���ƥ��꡼
 $categories = '
   <h5 class="box-cap" title="���ƥ��꡼����">Categories</h5>
   <div class="box-body">
    <ul>'.NL;
 if(!empty($_cache['category'])){
  foreach (array_keys($_cache['category']) as $i=>$_category){
   if($_category==MISC && $_cache['category'][$i]==0){
    $categories .= '';
   } else {
    $categories .= '     <li><a href="'.$_self.'?mode=category&amp;sub='.urlencode($_category).'">'.$_category
                .  ' [ '.$_cache['category'][$_category].' ] </a></li>'.NL;
   }
  }
 }
 $categories .= '    </ul>
   </div>
   <div class="box-foot"></div>'.NL;
 $categories .= $blogbar;
}

if(ARCHIVES){ // ����������
 $archives = '
   <h5 class="box-cap">Archives</h5>
   <div class="box-body">
    <ul class="center">'.NL;
 if(!empty($_cache['archives'])){
  $i = 0;
  foreach($_cache['archives'] as $date=>$count){
   if($i==ARCHIVES_ENTRY){
    $archives .= '     <li style="float:right;padding-right:1em;"><a href="'.$_self.'?mode=archives" title="���٤ƤΥ��������֤�ɽ��">all</a></li>
   ';
    break;
   }
   if($count==0) continue;
   $_log = substr($date, 0,4).'ǯ'.substr($date,4,2).'�� ['.$count.']';
   $archives .= "     <li><a href=\"$_self?date={$date}01\">$_log</a></li>\n";
   $i++;
  }
 }
 $archives .= ' </ul>
   </div>
   <div class="box-foot"></div>'.NL;
}

if(OTHERS){
 $rss10 = (RSS1) ? '    <li><a href="rss/rss1.0.rdf"><img src="Images/rss10.png" alt="RSS1.0" class="micro-banner" /></a></li>' : '';
 $rss20 = (RSS2) ? '     <li><a href="rss/rss2.0.xml"><img src="Images/rss20.png" alt="RSS2.0" class="micro-banner" /></a></li>' : '';
 $xhtml10 = '     <li><a href="http://validator.w3.org/check/referer"><img src="Images/xhtml10.png" alt="valid XHTML1.1" class="micro-banner" /></a></li>';
 $css2 = (CSS2) ? '     <li><a href="http://jigsaw.w3.org/css-validator/"><img src="Images/css.png" alt="valid CSS2" class="micro-banner" /></a></li>' : '';
 $copyright = '     <li><a href="http://www.martin.bz"><img src="Images/banner.png" alt="" class="micro-banner" title="ppBlog'.PPBLOG_VERSION.' powered" /></a></li>
 ';
 $others = '
   <h5 class="box-cap">Others</h5>
   <div class="box-body">
    <ul class="center">
 '.$rss10.NL.$rss20.NL.$xhtml10.NL.$css2.NL.$copyright.NL;
 $others .= '    </ul>
   </div>
   <div class="box-foot"></div>'.NL;
}

switch ($mode){
 case 'login' : case 'logout' : _header('admin.php');break;
 case 'show' : g_('date') ? show_box_all() : show_box(g_('UID')); break;
 case 'category' : show_category(); break;
 case 'gallery' : include_once('modules/gallery.inc.php'); img_gallery(); break;
 case 'archives' : show_archives(); break;
 case 'trackback' : include_once('modules/trackback.inc.php'); break;
 case 'cast_ping' : include_once('trackback.php'); break;
 case 'rss1.0' : _header('rss/rss1.0.rdf'); break;
 case 'rss2.0' : _header('rss/rss2.0.xml'); break;
 case 'click' : include_once('modules/click.inc.php'); my_click_count(g_('loc')); break;
 case 'bookmarks' : include_once('modules/bookmarks.inc.php'); break;
 case 'search' : include_once('modules/search.inc.php'); do_search(g_('words')); break;
 case 'comment' : case 'cast_comment' : case 'delete_comment' :
       include_once('modules/comment.inc.php');
       $mode=='comment' ? comment_form(g_('TID')) : catch_comment($mode); break;
 case 'write' : include_once('modules/write.inc.php'); write_form(); break;
 case 'edit' : include_once('modules/edit.inc.php'); edit_form(p_('UID')); break;
 case 'del' : include_once('modules/delete.inc.php'); del_form(p_('UID')); break;
 case 'config' : include_once('modules/config.inc.php'); break;
 case 'section' : include_once('modules/category.inc.php'); edit_category(); break;
 case 'mht' : include_once('modules/mht.inc.php'); html2mht(g_('tlog')); break;
 case 'submit' : case 'update' : case 'delete' : catch_data(); break;
 case 'template' : include_once('modules/template.inc.php'); break;
 case 'page' : include_once('modules/page.inc.php'); break;
 default : g_('UID') ? show_box(g_('UID')) : show_box_all(); break;
}

include_once('holiday.class.php');        // �������饹�θƤӹ���
include_once('modules/calendar.inc.php'); // $LINES�θ�ǥ���������ƤӽФ�

$DIVISION['title'] = g_('UID') ? $DIVISION['title'] : BLOG_NAME;
$DIVISION['recently'] = $recently;
$DIVISION['menu'] = $menu;
$DIVISION['comments'] = $comments;
$DIVISION['trackbacks'] = $trackbacks;
$DIVISION['categories'] = $categories;
$DIVISION['archives'] = $archives;
$DIVISION['others'] = $others;

if(REFERRER){ // ��ե��顼��ɽ��
 get_referrer();
 if(empty($vars)) $DIVISION['body'] .= read_referrer();
}
if(p_('xmlrpc_ping')==1){ // send update ping
 include('xmlrpc.php');
 $DIVISION['header'] .=
  send_ping_xmlrpc(BLOG_NAME,ROOT_PATH.'index.php',p_('title'),ROOT_PATH.'index.php?UID='.p_('UID'));
}

echoHTML(); // ����� HTML�����

?>
