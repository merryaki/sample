<?php
define('URL_PATH', 'http://'.str_replace('//','/',$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'));
$loc = URL_PATH.'aap.xsl'; // ppBlog1.3����°���Ƥ���aap.xsl�ޤǤΥѥ���
/*
�ʲ���ID������򤷤Ƥ������ȡ�
*/

$amazonID = 'ppblog-22'; // �����������ȥץ�����ID�����

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
   <option value="books-jp">�½�</option>
   <option value="books-us">�ν�</option>
   <option value="books-jp:add-us">�½���ν�</option>
   <option value="electronics-jp">���쥯�ȥ�˥���</option>
   <option value="music-jp">�ݥԥ�顼����</option>
   <option value="classical-jp">���饷�å�����</option>
   <option value="dvd-jp">DVD</option>
   <option value="vhs-jp">�ӥǥ�</option>
   <option value="software-jp">���եȥ�����</option>
   <option value="videogames-jp">������</option>
  </select>
 <br />
  <select name="sort">
   <option value="">��������ν���</option>
   <option value="+SalesRank">���Ƥ����</option>
   <option value="+reviewrank">ɾ���ι⤤��</option>
   <option value="+pricerank">���ʤΰ¤���</option>
   <option value="+inverse-pricerank">���ʤι⤤��</option>
   <option value="+daterank">����ǯ��ν�</option>
   <option value="+titlerank">�����ȥ�̾������</option>
   <option value="-titlerank">�����ȥ�̾���߽�</option>
  </select>
   <input type="hidden" name="page" value="1" />
   <input type="hidden" name="locale" value="jp" />
   <input type="hidden" name="t" value="<?php echo $amazonID;?>" />
   <input type="hidden" name="type" value="heavy" />
   <input type="hidden" name="dev-t" value="D2BUEA9DCZZ5YB" />
   <input type="hidden" name="f" value="<?php echo $loc;?>" />
   <input value="search" type="submit" class="button" title="����Ǹ���" />
  </form>
</div>
 <p class="center">���Υ��ꥢ�򱣤��ˤϲ��Ρ�* close *�ץܥ���򥯥�å����Ʋ�������</p>
</body>
</html>