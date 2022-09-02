<?php
/* ADMIN.PHP by martin
   LAST MODIFIED: 2004/07/23
*/
 session_start();

include_once('usr/ini.inc.php');
define('ROOT_PATH', 'http://'.str_replace('//','/',$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'));

if(($_GET['mode']=='admin'||$_GET['mode']=='logout') && !empty($_SESSION['ppBlog_admin'])){
 $_SESSION = array();
 session_destroy();
 $url =  ROOT_PATH.'index.php';
 $meta = "<meta http-equiv=\"refresh\" content=\"1;URL=$url\">";

 echoXHTML('<div style="margin-top:240px;"><strong>ログアウトしました。自動的にトップページに戻ります。</strong></div>', $meta);
}

$form = '
<form action="admin.php" method="post">
 <div style="display:none;">
  <input type="hidden" name="mode" value="login" />
  <input type="hidden" name="UID" value="'.$UID.'" />
 </div>
 <table border="0" align="center" summary="LOGIN">
  <tr>
   <th colspan="2"><img src="Images/go.png" alt="" width="19" height="20" align="middle" />
      ppBlog :: Login
   </th>
  </tr>
  <tr>
   <td align="right"><strong>Admin Name</strong></td>
   <td align="left"><input type="text" size="8" id="aN" name="adminName" size="8" maxlength="8" style="width:80px" /></td>
  </tr>
  <tr>
   <td align="right"><strong>Password</strong></td>
   <td align="left"><input type="password" size="8" id="aP" name="adminPass" size="8" maxlength="8" style="width:80px" /></td>
  </tr>
  <tr>
   <td align="right"><label for="si"><small>Save Info</small></label></td>
   <td align="left"><input type="checkbox" size="8" name="saveInfo" value="1" id="si" onclick="Cookie.set(\'ppBlog_js_cookie\',14);return true;" /></td>
  </tr>
  <tr><td colspan="2" align="center"><br /><input type="submit" value="Login" class="button" /></td></tr>
  <tr>
   <td colspan="2" align="center" style="height:120px"><br />
    <a href="index.php" title="トップページに戻る">
     <img src="Images/return.png" width="14" height="14" border="0" alt="TopPage" /></a>
   </td>
  </tr>
 </table>
</form>
</div>
';

if(!isset($_POST["adminName"])){
 $form = "<div>\n" . $form;
 echoXHTML($form);
}
if(isset($_POST)){
 $pName = trim($_POST['adminName']);
 $pPass = trim($_POST['adminPass']);
 if($pName=='' || $pPass==''){
  $e = "<div>\n<img src=\"Images/alert.png\" alt=\"alert\" align=\"top\" />"
     . " ログインID, もしくはパスワードが不正です。空文字列はダメです。";
  echoXHTML($e.$form);
 }
 if(isset($pName) && isset($pPass)){
  if($pName==OWNER && $pPass==OPASS){
   session_start();
   $_SESSION['ppBlog_admin'] = md5($pPass);
  }
  if(isset($_SESSION['ppBlog_admin'])){
   $url =  ROOT_PATH.'index.php';
   $_uid = (isset($_POST['UID']) && !empty($_POST['UID'])) ? '?UID='.$_POST['UID'] : '';
   $meta = "<meta http-equiv=\"refresh\" content=\"1;URL=$url$_uid\">";
   echoXHTML('<div style="margin-top:280px;"><strong>認証完了しました。ログイン中です。</strong></div>', $meta);
  } else {
   $e = '<div style="margin-top:240px;"><p>そのパスワードは正しくないようです。<br />'
      . '<button onclick="return history.go(-1);">Back</button></p></div>'.NL;
   echoXHTML($e);
  }
 }
}

function echoXHTML($body, $meta='', $encoding=ENCODE){
 echo <<<_HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<!--\xFD\xFE-->
<head profile="http://purl.org/net/uriprofile/">
 <title>ppBlog::admin</title>
 {$meta}
 <meta http-equiv="content-type" content="text/html; charset={$encoding}" />
 <meta http-equiv="content-script-type" content="text/javascript" />
 <meta http-equiv="content-style-type" content="text/css" />
 <style type="text/css">
  body{background:url("./Images/back.png");}
  div{width:100%;margin:100px 0 auto 0;font:500 13px Tahoma,'MS UI Gothic';color:#000051;text-align:center;}
  input{ height:17px;}
  .link{cursor:pointer;}
  input.button,button{border:solid 1px #aaa; font:600 11px Arial;background:#EEE; text-align:center; cursor:pointer;}
  a{color:#000051;font:600 13px Arial;}
  th{font:bold 30px/80px Times New Roman;padding-bottom:1.5em;color:#223271;}
  td span{font-size:13px;color:#555;}
  td small{font:600 11px arial; color:#333;}
  strong{font:600 14px 'MS UI Gothic', sans-serif;color:#443F63;}
 </style>
 <script type="text/javascript">
 var dc = document;
 Cookie = { // クッキーの設定，呼び込み，削除
  set : function(name, days){
   if(dc.getElementById("aN").value=='' || dc.getElementById("aP").value==''){
    alert("先に入力して下さい↑");
    dc.getElementById('si').checked = false;
    return;
   }
   var exp = "";
   if(days){
    var d = new Date();
    d.setTime(d.getTime()+(days*24*60*60*1000));
    exp = "; expires="+d.toGMTString();
   } else exp = "; expires=Sat, 31-Dec-2005 00:00:00 GMT;";
   dc.cookie = name+"="+escape(dc.getElementById("aN").value)+","+escape(dc.getElementById("aP").value)+exp+"; path=/";
  },
  get : function(name){
   c = dc.cookie.split(";");
   for(var i=0;i<c.length;i++){
    index = c[i].indexOf("=");
    if(c[i].substr(0,index)==name||c[i].substr(0,index)==" "+name)return c[i].substr(index+1);
   }
   return '';
  },
   del : function(name) { Cookie.set(name,'',-1); }
 }
 window.onload = function(){
  var ck = Cookie.get("ppBlog_js_cookie");
  var dc = document;
  if(ck!='' && dc.getElementById("aN")){
   dc.getElementById("aN").value = unescape(ck.split(',')[0]);
   dc.getElementById("aP").value = unescape(ck.split(',')[1]);
  }
 }
 </script>
</head>
<body>
_HTML;
echo $body."\n";
echo "</body>\n</html>\n";
exit;
}

?>