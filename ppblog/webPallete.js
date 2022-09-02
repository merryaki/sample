function makePallette(x,y){
 makeDom("DIV","Pallette","",[x,y,450,100,0,"","0px solid #333"]);
 makeDom("DIV","Box0","Pallette",[320,22,100,50,1,"#000","1px solid #333"]);
 
 setSlider("R",50,20,"red");
 setSlider("G",50,40,"green");
 setSlider("B",50,60,"blue");
}

function setSlider(id,x,y,bgc){
 draglyr = null;
 var r = g = b = Y = 0;
 var min = x;
 var max = x + 255;
 makeDom("DIV",id,"Pallette",[x,y,10,15,5,"#ddd","1px solid #333","","pointer"]);
 makeDom("DIV",id+"Info","Pallette",[10,y-3,50,30,2,"","","600 18px arial"]);
 dom(id+"Info").innerHTML = (""+id);
 makeDom("DIV",id+"BarBack","Pallette",[x,y+6,265,3,0,"#000"]);
 makeDom("DIV",id+"Bar",id+"BarBack",[0,0,265,3,1,bgc,"","","",true]);
 
 var id = dom(id);
 eval(id).dragable = function(){
  scrollX = document.body.scrollLeft;
  this.onmousedown = function(){draglyr=eval(id);return false;}
  document.onmousedown = function(e){
   var e = e||event;
   if(draglyr){
    this.offsetX=parseInt(draglyr.offsetLeft)-(scrollX+e.clientX);
    return false;
   } else return true;
  }
  document.onmousemove = function(e){
   var e = e||event;
   if(draglyr){
    draglyr.style.left=scrollX+e.clientX+this.offsetX+'px';
    _x = parseInt(draglyr.offsetLeft);
    if(_x<=min){draglyr.style.left=min;}
    if(_x>=max){draglyr.style.left=max;}
    if(draglyr.id=='R'){r=_x-x;r=(r<0)?0:(r>=255)?255:r;}
    if(draglyr.id=='G'){g=_x-x;g=(g<0)?0:(g>=255)?255:g;}
    if(draglyr.id=='B'){b=_x-x;b=(b<0)?0:(b>=255)?255:b;}
    dom('Box0').style.backgroundColor = rgbColor(r,g,b)
    if(document.forms[0].rcolor[0].checked) dom('barcolor').value = rgbColor(r,g,b);
    if(document.forms[0].rcolor[1].checked) dom('textcolor').value = rgbColor(r,g,b);
    return false;
   } else return true;
  }
  document.onmouseup = function(){draglyr=null;return false;}
 }
 eval(id).dragable();
}
function rgbColor(r,g,b){return "RGB("+r+","+g+","+b+")";}
function dom(id){return document.getElementById(id);}
function makeDom(elmType, elmID, elmParent, elmArray){
 var elm = document.createElement(elmType);
 if(elmID) elm.id = elmID;
 if(elmArray){
  with(elm.style){
   position = "absolute";
   fontSize = "0px";
   textAlign = "center";
   left = (""+elmArray[0]).match(/[0-9]+$/) ? elmArray[0]+'px' : elmArray[0];
   top = (""+elmArray[1]).match(/[0-9]+$/) ? elmArray[1]+'px' : elmArray[1];
   width = (""+elmArray[2]).match(/[0-9]+$/) ? elmArray[2]+'px' : elmArray[2];;
   height = (""+elmArray[3]).match(/[0-9]+$/) ? elmArray[3]+'px' : elmArray[3];
   zIndex = elmArray[4];
   backgroundColor = elmArray[5];
   if(elmArray[6]) border = elmArray[6];
   if(elmArray[7]) font = elmArray[7];
   if(elmArray[8]) cursor = elmArray[8];
   if(elmArray[9]) filter = "alpha(style=1,startx=70,finishx=0)";
  }
 }
 if(elmParent!=""){
  dom(elmParent).appendChild(elm);
 }else document.body.appendChild(elm);
 return elm;
}
