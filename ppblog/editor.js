/*
 editor.js by Masayuki AOKI, martin.
 Last modified :2004/08/02 04:46
*/

var UNDO_BUF = [];
var CLIP_BOARD = [''];
var d = document;
var undo_count = 0;
var ie = d.selection ? 1 : 0;
var moz = (d.getSelection && !window.opera) ? 1 : 0;
var WPON = 0;
var el;

function InitEditor(){
 if((""+location.href).match(/mode=page/) && document.getElementById('wp_mode')){
  document.getElementById('wp_mode').style.display = 'none';
 }
 if(d.getElementById('edit')){
  el = d.getElementById('edit');
 } else return;
 el.onclick = function(){
  if(d.getElementById("palette").style.visibility!="hidden"){
   d.getElementById("palette").style.visibility = "hidden";
  }
  setPosition();
 }
 el.onselect = function(){setPosition();}
 el.onfocus = function(){setPosition();}
 divs = d.getElementsByTagName('DIV');
  for (var i=0; i < divs.length; i++) {
   if (divs[i].className == "imgbtn") {
    divs[i].onmouseover = function (){
     this.style.borderColor = '#0000db';
     if(this.id=="color" || this.id=="marker") return;
     else this.style.backgroundColor = '#cacaff';
    }
    divs[i].onmouseout = function(){ 
     this.style.border = 'solid 1px #c0c0c0';
     if(this.id=="color" || this.id=="marker") return;
     else this.style.backgroundColor = '#f5f5f5';
    }
    divs[i].onmousedown = function(){
      this.style.borderColor = '#909090 #f0f0f0 #f0f0f0 #909090';
   }
    divs[i].onmouseup = function(){
     this.style.border = 'solid 1px #c0c0c0';
    }
   }
 }
}
function updateForm(){
 if(WPON){
  preView();
  tk = setTimeout("updateForm()", 350);
 } else clearTimeout(tk);
}
function Palette(mode,e){
 var btn = d.getElementsByTagName('td');
 for (var i=0;i<btn.length;i++){
  if(btn[i].className=="color-cell"){
   btn[i].onmouseover = function(){this.style.border="2px solid #1c0574";};
   btn[i].onmouseout = function(){this.style.border="1px solid #ddd"};
   btn[i].onclick = function(){
    if(mode=='color') fontColor(this.id);
    if(mode=='marker') Marker(this.id);
    d.getElementById("palette").style.visibility = "hidden";
   };
  }
 }
 var sT = ie ? document.documentElement.scrollTop : 0;
 d.getElementById("palette").style.left = parseInt(e.clientX-10) + 'px';
 d.getElementById("palette").style.top = parseInt(sT+e.clientY+16) + 'px';
 d.getElementById("palette").style.visibility = "visible";
}

function Marker(color){
 if(!el.selected) return;
 UNDO_BUF.push(el.value);
 var selected = ie ? el.selected.text : el.selected;
 insert2Selection('<span style="background:' + color + ';">'+selected+'</span>');
 d.getElementById('marker').style.backgroundColor = color;
}
function fontColor(color){
 if(!el.selected) return;
 UNDO_BUF.push(el.value);
 var selected = ie ? el.selected.text : el.selected;
 insert2Selection('<span style="color:' + color + ';">'+selected+'</span>');
 d.getElementById('color').style.backgroundColor = color;
}
function fontSize(sz){
 if(!el.selected) return;
 UNDO_BUF.push(el.value);
 var selected = ie ? el.selected.text : el.selected;
 insert2Selection('<span style="font-size:' + sz + ';">'+selected+'</span>');
}
function cut(){
 if(!el.selected) return;
 var selected = ie ? el.selected.text : el.selected;
 UNDO_BUF.push(el.value);
 CLIP_BOARD[0] = selected;
 if(ie || moz) insert2Selection('');
}
function copy(){
 if(!el.selected) return;
 var selected = ie ? el.selected.text : el.selected;
 if(selected!=' ') CLIP_BOARD[0] = selected;
}
function paste(){
 if(!el.selected) return;
 UNDO_BUF.push(el.value);
 var selected = ie ? el.selected.text : el.selected;
 insert2Selection(CLIP_BOARD[0]);
}
function undo(){
 if(undo_count >= UNDO_BUF.length || UNDO_BUF[0]==''){
  alert("これ以上のアンドゥはありません。");
  return;
 } else {
 undo_count++;
 el.value = UNDO_BUF.slice(UNDO_BUF.length-undo_count, UNDO_BUF.length-(undo_count-1));
 }
}
function redo(){
 if(undo_count > 1){
  undo_count--;
  el.value = UNDO_BUF.slice(UNDO_BUF.length-undo_count, UNDO_BUF.length-(undo_count-1));
 }
}
function google_it(){
 if(!el.selected) return;
 var selected = ie ? el.selected.text : el.selected;
 if(selected!=' '){
  insert2Selection('[g]'+selected+'[/g]');
 }
}
function googleIt(query){
 window.open(encodeURI("http://www.google.com/search?hl=ja&ie=UTF-8&oe=UTF-8&q="+query));
}
function setPosition(){
 if(el.createTextRange){
  el.selected = document.selection.createRange().duplicate();
  el.focus();
 } else if(document.getSelection && moz){
  el.selected = el.value.substring(el.selectionStart, el.selectionEnd);
 } else el.selected = ' ';
}
function insert2Selection(string){
 el = document.getElementById('edit');
 if(typeof(el.selected)=='undefined') {el.focus(); setPosition();}
 if(el.createTextRange){
  if(el.selected.text.length>0 && (el.value!=el.selected.text)){
   el.selected.text = string;
   var range = document.selection.createRange().duplicate();
   range.moveStart('textedit',-1);
  } else {
   el.selected.text += string;
   el.createTextRange().moveStart('textedit',-1)
   el.selected.select();
  }
 } else if(document.getSelection && el.selected.length>=0 && el.selectionStart){
   var s = el.selectionStart;
   el.value = el.value.slice(0,s)+el.value.slice(s).replace(el.selected, string);
   el.setSelectionRange(s+string.length, s+string.length);
 } else el.value += string;
 //el.focus();
}
function sentenceFormat(f){
 if(!el.selected) return;
 var selected = ie ? el.selected.text : el.selected;
 if(f=='q'){
  f = 'div class="quote"'; var _f = 'div';
 } else var _f = f;
 if(f==''){
  d.getElementById('edit').value += ('<'+f+'>\n\n</'+_f+'>');
 } else {
  if(f==''){
   return;
  } else if(f!='normal'){
   insert2Selection('\n<'+f+'>'+selected+'</'+_f+'>\n');
  } else {
   insert2Selection(rep.replace(/(\<)(p|h1|h3|q|pre)(\>)(.*?)(\<\/)(p|h1|h3|q|pre)(\>)/im,"$4"));
  }
 }
 d.getElementById('format').selectedIndex = 0;
}

function insertAttachedFile(index){
 var el = d.getElementById('edit');
 var max_wh = d.getElementById('max_wh') ? d.getElementById('max_wh').value : 250;
 var imgExt = "bmp|png|jpeg|jpg|gif";
 UNDO_BUF.push(el.value);
 if(d.getElementById('upload'+index).value !=""){
  var uf = "file:///" + d.getElementById('upload'+index).value.replace(/\\/g, "/");
  var suf = uf.split("\/");
  if(imgExt.indexOf(uf.split('.')[uf.split('.').length-1].toLowerCase()) >= 0){
   var upImg = new Image();
   upImg.src = uf;
   if(upImg && upImg.width == 0){
    var tk = setTimeout('insertAttachedFile('+index+')',100);
   } else {
    clearTimeout(tk);
    var w = upImg.width;
    var h = upImg.height;
    var ratio = max_wh / Math.max(w, h); // デカイやつは縮小表示
    w  = (ratio<1) ? Math.round(w * ratio) : w;
    h = (ratio<1) ? Math.round(h * ratio) : h;
    var _alt = suf[suf.length-1].split('.')[0];
    var alt = prompt('画像の代替テキスト(alt属性)をどうぞ',_alt);
    if(alt){
     el.value += '\n<img src="'+uf+'" alt="'+alt+'" width="'+w+'" height="'+h+'" />';
    } else {
     el.value += '\n<img src="'+uf+'" alt="'+_alt+'" width="'+w+'" height="'+h+'" />';
    }
   }
  } else {
   el.value += '\n[file:'+suf[suf.length-1]+'/]';
  }
 } else alert("まず添付ファイルを選んで下さい。");
}

function algn(where){
 if(!el.selected) return;
 var selected = ie ? el.selected.text : el.selected;
 insert2Selection('\n<div style="text-align:'+where+';">'+selected+'</div>\n');
}
function ULFormat(){ UNDO_BUF.push(el.value);insert2Selection('\n<ul>\n<li> </li>\n<li> </li>\n<li> </li>\n</ul>\n');}
function OLFormat(){ UNDO_BUF.push(el.value);insert2Selection('\n<ol>\n<li> </li>\n<li> </li>\n<li> </li>\n</ol>\n');}
function DLFormat(){ UNDO_BUF.push(el.value);insert2Selection('\n<dl>\n <dt> </dt>\n  <dd> </dd>\n <dt> </dt>\n  <dd> </dd>\n</dl>\n');}

function fontProp(tag){
 if(!el.selected) return;
 UNDO_BUF.push(el.value);
 var selected = ie ? el.selected.text : el.selected;
 if (tag=='u') return insert2Selection('<span class="underline">'+selected+'</span>');
 insert2Selection('<'+tag+'>'+selected+'</'+tag+'>');
}
function createLink(){
 if(!el.selected) return;
 var selected = ie ? el.selected.text : el.selected;
 var link = prompt('リンクしたいアドレスを記入して下さい: ', 'http://');
 if(link){
  if(!link.match(/[^;\/?:@&=+\$,A-Za-z0-9\-_.!~*'()%]/)){
   if(selected!=' '){
    insert2Selection('<a href="'+link+'">'+selected+'</a>');
   } else {
    var site = prompt("サイト名をどうぞ ", "");
    if(site) insert2Selection('<a href="'+link+'">'+site+'</a>');
   }
  } else link = prompt('URLに不適な文字が入っているような...', link);
 } else {
  return;
 }
}

function ping_form(){
 if(d.getElementById('pingform').style.display=="none" && d.getElementById('sp').checked==true){
  d.getElementById('pingform').style.display = "block";
 } else d.getElementById('pingform').style.display = "none";
}

function strip_html_tags(str){
 str = str.replace(/\[g\](.*?)\[\/g\]/ig,
       '<a href="#" onclick="return googleIt(\'$1\');">$1</a><span class="google-it">G</span>');
 str = str.split('\r\n').join('†');
 if(m = str.match(/(\[\[.*?\]\])/g)){
  for(var i=0;i<m.length;i++){
   str = str.replace(m[i], m[i].replace(/</g,'&lt;'));
  }
 }
 str = str.replace(/\[\[|\]\]/g,'');
 str = str.replace(/([^\'"=]|^)(https?|ftp)(:\/\/[;\/\?:@&=\+\$,A-Za-z0-9\-_\.!~%#\|]+)/ig,'$1<a href="$2$3">$2$3</a>');
 return str;
}

function preView(){
 var html = strip_html_tags(d.getElementById('edit').value);
 html = html.replace(/†/g, '\n†');
 html = html.replace(/(div|pre|ol|li|ul|dl|p|form|blockquote|fieldset|table)([^>]*?>)†+/ig,'$1$2').replace(/†/gm, '');;
 d.getElementById('preview').style.display = 'block';
 d.getElementById('pv1').innerHTML = html;
}
function preViewOff(){
 d.getElementById('preview').style.display = 'none';
 WPON = 0;
 if(typeof(tk)!="undefined") clearTimeout(tk);
 d.getElementById('wp').checked = false;
}

amazon = function(){
 document.getElementById("amazon_window").style.display = 'block';
 if(document.getElementById("amazon").src==""){
  document.getElementById("amazon").src = "amazon_associate.php";
 }
}

function writeToolBar(){
 var code =
   '<div id="preview" style="display:none;white-space:pre;">\n'
 + '<div id="pv1"></div>\n'
 + '<h3><button onclick="preViewOff();return false;">Preview Off</button></h3>\n'
 + '</div><br />\n'
 + '<div id="toolbar">\n'
 + '<table style="background:#eee;" cellpadding="1" cellspacing="0" >\n'
 + '<tr>\n'
 + ' <td>\n'
 + ' <div><span class="help">文の整形</span>\n'
 + ' <select id="format" onchange="sentenceFormat(this.options[this.selectedIndex].value)">\n'
 + '  <option value="" selected="selected">下から選択</option>\n'
 + '  <option value="p">段 落</option>\n'
 + '  <option value="h1">見出し 1</option>\n'
 + '  <option value="h3">見出し 3</option>\n'
 + '  <option value="q">引用ブロック</option>\n'
 + '  <option value="pre">整形ブロック</option>\n'
 + ' </select>\n'
 + ' </div>\n'
 + ' </td>\n'
 + ' <td>\n'
 + ' <div><span class="help"> 文字サイズ</span>\n'
 + ' <select id="size" onchange="fontSize(this.options[this.selectedIndex].value)">\n'
 + '  <option value="8pt">8pt</option>\n'
 + '  <option value="9pt">9pt</option>\n'
 + '  <option value="10pt" selected="selected">10pt</option>\n'
 + '  <option value="10.5pt">10.5pt</option>\n'
 + '  <option value="12pt">12pt</option>\n'
 + '  <option value="14pt">14pt</option>\n'
 + '  <option value="16pt">16pt</option>\n'
 + '  <option value="24pt">24pt</option>\n'
 + '  <option value="30pt">30pt</option>\n'
 + '  <option value="36pt">36pt</option>\n'
 + ' </select>\n'
 + ' </div>\n'
 + ' <td id="wp_mode">　　<label for="wp"><span class="help">ワープロモードを有効にする</span></label>\n'
 + ' <input id="wp" type="checkbox" onclick="WPON=(WPON==1)?0:1;updateForm();return true;" />\n'
 + ' </td>\n'
 + '</tr>\n</table>\n'
 + '<table style="background:#eee;" cellpadding="1" cellspacing="0" >\n'
 + ' <tr>\n'
 + ' <td><div id="cut" class="imgbtn"><img src="button/cut.png" alt="" title="切り取り" onclick="cut()" /></div></td>\n'
 + ' <td><div id="copy" class="imgbtn"><img src="button/copy.png" alt="" title="コピー" onclick="copy()" /></div></td>\n'
 + ' <td><div id="paste" class="imgbtn"><img src="button/paste.png" alt="" title="貼り付け" onclick="paste()" /></div></td>\n'
 + ' <td><div id="undo" class="imgbtn"><img src="button/undo.png" alt="" title="アンドゥ" onclick="undo()" /></div></td>\n'
 + ' <td><div id="redo" class="imgbtn"><img src="button/redo.png" alt="" title="リドゥ" onclick="redo()" /></div></td>\n'
 + ' <td>\n'
 + ' <div id="createlink" class="imgbtn"><img src="button/link.png" alt="" title="リンクの作成" onclick="createLink()" /></div>\n'
 + '</td>\n'
 + ' <td><div id="bold" class="imgbtn"><img onclick="fontProp(\'strong\')" src="button/bold.png" alt="" title="太字" /></div></td>\n'
 + ' <td><div id="italic" class="imgbtn"><img onclick="fontProp(\'em\')" src="button/italic.png" alt="" title="斜体" /></div></td>\n'
 + ' <td><div id="underline" class="imgbtn"><img onclick="fontProp(\'u\')" src="button/underline.png" alt="" title="下線" /></div></td>\n'
 + ' <td><div id="del" class="imgbtn"><img onclick="fontProp(\'del\')" src="button/del.png" alt="" title="取り消し線"></div></td>\n'
 + ' <td><div id="color" class="imgbtn" style="background:#777;"><img src="button/color.png" onclick="Palette(\'color\',event);" alt="" title="文字の色" /></div></td>\n'
 + ' <td><div id="marker" class="imgbtn" style="background:#ffff00;"><img onclick="Palette(\'marker\',event)" src="button/marker2.png" alt="" title="マーカー"></div></td>\n'
 + ' <td><div id="google" class="imgbtn"><img onclick="google_it()" src="button/google.png" alt="" title="GoogleIt!"></div></td>\n'
 + ' <td><div id="left" class="imgbtn"><img src="button/left.png" alt="" title="左寄せ" onclick="algn(\'left\')" /></div></td>\n'
 + ' <td><div id="center" class="imgbtn"><img src="button/center.png" alt="" title="センタリング" onclick="algn(\'center\')" /></div></td>\n'
 + ' <td><div id="right" class="imgbtn"><img src="button/right.png" alt="" title="右寄せ" onclick="algn(\'right\')" /></div></td>\n'
 + ' <td><div id="list" class="imgbtn"><img src="button/unorderedlist.png" alt="" title="リスト形式" onclick="ULFormat()" /></div></td>\n'
+ ' <td><div id="olist" class="imgbtn"><img src="button/orderedlist.png" alt="" title="番号付リスト形式" onclick="OLFormat()" /></div></td>\n'
+ ' <td><div id="dlist" class="imgbtn"><img src="button/definedlist.png" alt="" title="定義リスト形式" onclick="DLFormat()" /></div></td>\n'
+ ' <td><div id="continue" class="imgbtn"><img src="button/continue.png" alt="" title="「続きを読む」タグの挿入" onclick="document.getElementById(\'edit\').value +=\'<!--HIDE-->\'" /></div></td>\n'
 + '</tr>\n'
 + '</table>\n'
 + '</div>\n';
 d.write(createPalette() + code);
}


function createPalette(){
 var colors = [
"#333333","#006633","#333333","#336633","#339933","#33cc33","#663333","#666633","#669933",
"#666666","#006666","#333366","#336666","#339966","#33cc66","#663366","#666666","#669966",
"#999999","#006699","#333399","#336699","#339999","#33cc99","#663399","#666699","#669999",
"#cccccc","#0066cc","#3333cc","#3366cc","#3399cc","#33cccc","#6633cc","#6666cc","#6699cc",
"#ffffff","#0066ff","#3333ff","#3366ff","#3399ff","#33ccff","#6633ff","#6666ff","#6699ff",
"#ff0000","#999900","#cc6600","#cc9900","#cccc00","#ff3300","#ff9900","#ffcc00","#ffff00",
"#0000ff","#999966","#cc6666","#cc9966","#cccc66","#ff3366","#ff9966","#ffcc66","#ffff66",
"#ffff00","#999999","#cc6699","#cc9999","#cccc99","#ff3399","#ff9999","#ffcc99","#ffff99",
"#00ffff","#9999cc","#cc66cc","#cc99cc","#cccccc","#ff33cc","#ff99cc","#ffcccc","#ffffcc",
"#ff00ff","#9999ff","#cc66ff","#cc99ff","#ccccff","#ff33ff","#ff99ff","#ffccff","#ffffff"
 ];

 var cols = '';
 cols += '<table><tr>';
 for (i=1; i<=colors.length;i++){
  cols += '<td id="'+colors[i-1]+'" class="color-cell" style="background-color:'+colors[i-1]+'"><img src="button/cell.png" /></td>';
  if(i%9==0) cols += '</tr>\n<tr>';
 }
 cols += '</table>\n';
 var p = '<div id="palette" style="\n'
       + '         position:absolute;\n'
       + '         visibility:hidden;\n'
       + '         background-color: menu;\n'
       + '         border: 2px solid;\n'
       + '         border-color: #f0f0f0 #909090 #909090 #f0f0f0;\n'
       + '         width:176px;"\n'
       + '>\n';
 return (p + cols + '</div>');
}
window.onload = function () {
 if(d.getElementById('edit')) InitEditor();
}