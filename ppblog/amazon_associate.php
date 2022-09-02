<?php
define('URL_PATH', 'http://'.str_replace('//','/',$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'));
$loc = URL_PATH.'aap.xsl'; // ppBlog1.3に付属していたaap.xslまでのパス。
/*
以下のIDの設定をしておくこと。
*/

$amazonID = 'ppblog-22'; // アソシエイトプログラムのIDを指定

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
<title>ppblog</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-jp">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<script type="text/javascript">
 function _enc(fm){
  fm.KeywordSearch.value = encodeURI(fm.KeywordSearch.value);
  return true;
 }
</script>
<style type="text/css">
body{ font-size:13px; background:#fff;}
.center{ text-align:center; width:100%; margin:auto; }
.button {border:1px solid #333;font:600 13px arial;background:#FAD132;height:20px;cursor:pointer;}
img {border:solid 1px #c0c0c0;}
</style>
</head>
<body>
<p class="center"><img src="<?php echo URL_PATH?>Images/amazon-banner.png" border="0" alt="amazon.com" /></p>
<div class="center">
 <form action="http://xml.amazon.co.jp/onca/xml3" method="get" name="AAP" onSubmit="_enc(this);">
  <input type="text" name="KeywordSearch" value="" style="width:100px;" />
  <select name="mode">
   <option value="books-jp">和書</option>
   <option value="books-us">洋書</option>
   <option value="books-jp:add-us">和書と洋書</option>
   <option value="electronics-jp">エレクトロニクス</option>
   <option value="music-jp">ポピュラー音楽</option>
   <option value="classical-jp">クラシック音楽</option>
   <option value="dvd-jp">DVD</option>
   <option value="vhs-jp">ビデオ</option>
   <option value="software-jp">ソフトウェア</option>
   <option value="videogames-jp">ゲーム</option>
  </select>
 <br />
  <select name="sort">
   <option value="">オススメの順番</option>
   <option value="+SalesRank">売れている順</option>
   <option value="+reviewrank">評価の高い順</option>
   <option value="+pricerank">価格の安い順</option>
   <option value="+inverse-pricerank">価格の高い順</option>
   <option value="+daterank">出版年月の順</option>
   <option value="+titlerank">タイトル名：昇順</option>
   <option value="-titlerank">タイトル名：降順</option>
  </select>
   <input type="hidden" name="page" value="1" />
   <input type="hidden" name="locale" value="jp" />
   <input type="hidden" name="t" value="<?php echo $amazonID;?>" />
   <input type="hidden" name="type" value="heavy" />
   <input type="hidden" name="dev-t" value="D2BUEA9DCZZ5YB" />
   <input type="hidden" name="f" value="<?php echo $loc;?>" />
   <input value="search" type="submit" class="button" title="これで検索" />
  </form>
</div>
 <p class="center">このエリアを隠すには下の「* close *」ボタンをクリックして下さい。</p>
</body>
</html>