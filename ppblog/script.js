/* JavaScript scripts by martin
   Last Update: 2004/03/12
*/
var d = document;

var notfound = new Image();
notfound.src = 'http://ppblog.martin.bz/Images/notfound-amazon.png';

function insertLink(target){
 var t = document.getElementById(target);
 var link = prompt('��󥯤�����URL��ɤ���: ', 'http://');
 if(link){
  link = link.replace(/^(http\:\/\/)/g,'');
  if(!link.match(/[^;\/?:@&=+\$,A-Za-z0-9\-_.!~*'()%]/)){
   var site = prompt("������̾��ɤ��� ", "");
   if(site) t.value += ('[link:'+link+']' + site + '[/link]');
  } else link = prompt('URL����Ŭ��ʸ�������äƤ���褦��...', link);
 } else {
  return;
 }
}

function googleIt(query){
 window.open(encodeURI("http://www.google.com/search?hl=ja&ie=UTF-8&oe=UTF-8&q="+query));
}
function ToClipBoard(item,data){
 if(document.all){
  if(data) window.clipboardData.setData('Text', data);
  else window.clipboardData.setData('Text', item.parentNode.childNodes(0).innerText);
 } else return;
}

function getDocHeight(){
 if(document.documentElement && document.body){
  return Math.max(
   document.documentElement.scrollHeight,document.documentElement.offsetHeight,document.body.scrollHeight
  );
 } else return document.body.scrollHeight;
}

Cookie = { // ���å��������ꡤ�Ƥӹ��ߡ����
 set : function(name,value,days){
  var exp = "";
  if(days){
   var d = new Date();
   d.setTime(d.getTime()+(days*24*60*60*1000));
   exp = "; expires="+d.toGMTString();
  } else exp = "; expires=Sat, 31-Dec-2005 00:00:00 GMT;";
  document.cookie = name + "=" + escape(value) + exp + "; path=/";
 },
 get : function(name){
  c = document.cookie.split(";");
  for(var i=0;i<c.length;i++){
   index = c[i].indexOf("=");
   if(c[i].substr(0,index)==name||c[i].substr(0,index)==" "+name)return unescape(c[i].substr(index+1));
  }
  return '';
 },
 del : function(name) { Cookie.set(name,'',-1); }
}

toggle = function(el){
 if(el.childNodes[2].style.display=='none'){
  el.firstChild.firstChild.nodeValue = '>>�����򤿤���';
  el.childNodes[2].style.display='block';
 } else if(el.childNodes[2].style.display=='block') {
  el.firstChild.firstChild.nodeValue = '³������>>';
  el.childNodes[2].style.display='none';
 }
}
