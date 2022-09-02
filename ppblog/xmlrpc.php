<?php
/*
 XML-RPC module by Masayuki AOKI, martin
 $ 2004/08/08 11:36
*/
function my_convert_encoding($string, $to, $from=ENCODE){
 global $mb;
 if($mb==false){
  include_once(PATH.'jcode_wrapper.php');
  return jcode_convert_encoding($string, $to, $from);
 } else return mb_convert_encoding($string, $to, $from);
}
function send_ping_xmlrpc($blog_name, $top_url){
 global $PING_TARGETS, $DIVISION;
 $ping_sites = array();
 if(is_array($PING_TARGETS)){
  foreach ($PING_TARGETS as $pt){
   $ping_sites[] = ($pt==1) ? 1 : 0;
  }
 } else return $DIVISION['header'] .= '<h4>ConfigurationでPingサイトの設定をして下さい。</h4>';
 
 $target_sites = array(// 順序は変えない
  array('ping.bloggers.jp','/rpc/'), 
  array('ping.cocolog-nifty.com','/xmlrpc'),
  array('bulkfeeds.net','/rpc'),
  array('www.blogpeople.net','/servlet/weblogUpdates'),
  array('blog.goo.ne.jp','/XMLRPC'),
  array('ping.myblog.jp','/')
 );
 if (isset($_COOKIE['ppBlog_ping_cookie'])){
  $ps = explode(',', $_COOKIE['ppBlog_ping_cookie']);
  foreach ($ps as $p){
   if(empty($p)) continue;
   $host = array_shift(explode('/',$p=trim($p)));
   $path = preg_replace("/$host/",'',$p);
   $target_sites = array_merge($target_sites, array(array(0=>$host,1=>$path)));
  }
 }
 $request = '
<?xml version="1.0"?>
<methodCall>
  <methodName>weblogUpdates.ping</methodName>
  <params>
    <param>
      <value>'.my_convert_encoding($blog_name,'utf-8', ENCODE).'</value>
    </param>
    <param>
      <value>'.$top_url.'</value>
    </param>
  </params>
</methodCall>
';
 $request = trim(str_replace("\n","\r\n",$request));
 $result = ''; 
 foreach ($target_sites as $i=>$target){
  if($ping_sites[$i]==1 || $i>=6){
   $server = $target[0];
   $path = $target[1];
   $header = "POST $path HTTP/1.0\r\n"
           . "User-Agent: ppBlog XML-RPC 1.0\r\n"
           . "Host: $server\r\n"
           . "Content-Type: text/xml\r\n"
           . "Content-Length: ".strlen($request)
           . "\r\n\r\n".$request;
   $fp = @fsockopen($server, $port=80, $errno, $errstr, $timeout=5);
   socket_set_timeout($fp, $timeout);
   if(!$fp) {
    $result .= '<h5>Connection error ('.$errno.') - $errstr</h5>'.NL;
   }
   @fwrite($fp, $header);
   $reply = '';
   while (!feof($fp)) {
    $line = fgets($fp, 4096);
    $reply .= $line;
   }
   fclose($fp);
   if(preg_match('/<params>/', $reply)){ // did it
    if(preg_match('/<value>(<string>|)([^<>]+)(<\/string>|)<\/value>/s', $reply, $m)){
     $result .= '<h4>Success! - '.$server.': '.$m[2].'</h4>'.NL;
    }
   } else if(preg_match('/<fault>/', $reply)){ // failed
    if(preg_match('/<value>(<string>|)([^<>]+)(<\/string>|)<\/value>/s', $reply, $m)){
     $result .= '<h4>Not pinged - '.$server.': '.$m[2].'</h4>'.NL;
    }
   } else $result .= '<h4>Error - '.$server.'</h4>'.NL;
  }
 }
 return $result;
}

?>