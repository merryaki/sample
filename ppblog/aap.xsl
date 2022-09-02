<?xml version="1.0"?>
<xsl:stylesheet 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  version="1.0">
  <xsl:output method="html" encoding="utf-8"/>
<xsl:template match="/">
<html lang="ja">
<head>
<script type="text/javascript">
  <xsl:comment>
    <![CDATA[
 var notfound = new Image();
 notfound.src = 'http://php.martin.bz/ppBlog/Images/notfound-amazon.png';
 function _enc(fm){
  fm.KeywordSearch.value = encodeURI(fm.KeywordSearch.value);
  return true;
 }
 function showTags(ob){
  var msg = 'WinIEなら生成されたタグはクリップボードにコピーされているのでそれを'
          + 'テキストエリアに貼り付けて下さい。そうでなければタグを手動でコピー（CTRL+C）して下さい。';
  var tags = '<a href="'+ob.nextSibling.getAttribute('href')+'">'
           + '<img src="'+ob.src+'" style="float:left;" alt="" title="この関連商品も見てみる" /></a>';
  var parent = ob.parentNode;

  parent = parent.innerHTML.replace(/<img [^>]*><a [^>]*?><\/a>/i,'');
  parent = parent.replace(/(<a [^>]*)(>)([^<]*?<\/a>)(.*)/i,'$1 title="アマゾンで詳しく"$2$3');
  parent = parent.replace(/<strong>リリース.*/igm,'');
  parent = parent.replace(/<BR>/g,'<br />').replace(/<A/g,'<a').replace(/\/A>/g,'/a>').replace(/target="?_blank"?/g,'');
  var asin = parent.replace(/.*\/ASIN\/([a-zA-Z0-9]+)\/.*/, '$1');
  var tags = '<p style="font-size:12px;text-align:left;line-height:150%;">'
           + tags + parent + ('<!--AMAZON:'+asin+'--></p>');
  var p = prompt(msg, tags);
  if(document.all) window.clipboardData.setData('Text', tags);
 }
   ]]>
  </xsl:comment>
</script>
<style type="text/css">
 <xsl:comment>
  <![CDATA[
  body {font-size:13px; background:#fff;margin-left:40px;}
  .button { border:1px solid #333;font:600 13px arial;background:#FAD132;height:20px; }
  .result { text-align:center; color:#000048; font-size:14px; margin:10px;line-height:250%;}
  .aap-html{width:420px;border:2px solid #aaa;background:#eee;}
  .inbox {background:#FDFDFF;width:400px;margin:10px;font-size:13px;clear:both; border:dashed 1px #aaa;}
  .inbox strong { font:600 13px; color:#333;}
  .inbox span {color:#BC3012;}
  fieldset {width:270px;margin:auto;}
  img {cursor:pointer;
       margin:3px 7px 3px 7px;
       padding:5px;
       background-color:white;
       border:solid 1px #c0c0c0;
      }
  ]]>
 </xsl:comment>
</style>
</head>
<body>
<div align="center">
 <p>画像をクリックするとアマゾンアソシエイト用のHTMLタグを表示します。</p>
  <fieldset>
   <legend><img src="http://php.martin.bz/ppBlog/Images/amazon-banner.png" border="0" alt="amazon.com" /></legend>
    <form action="http://xml.amazon.co.jp/onca/xml3" method="GET" name="AAP" onSubmit="_enc(this);">
      <input type="text" name="KeywordSearch" value="" style="width:120px;" />
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
      <xsl:apply-templates select="ProductInfo/Request/Args/Arg" mode="input_hidden" />
      <input value="search" type="submit" class="button" />
    </form>
  </fieldset>
</div>

  <xsl:call-template name="pageInfo" />
  <xsl:apply-templates select="ProductInfo/Details" />
  <hr />
  <xsl:if test="number(ProductInfo/TotalResults)!='NaN'">
    <xsl:call-template name="pageInfo" />
  </xsl:if>
</body>
</html>

</xsl:template>

<xsl:template match="Arg" mode="getValue">
  <xsl:value-of select="@value" />
</xsl:template>

<xsl:template name="getCount">
  <xsl:param name="currentPage"><xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='page']" mode="getValue" /></xsl:param>
  <xsl:choose>
    <xsl:when test="number($currentPage + 1) = number(ProductInfo/TotalPages)">
      <xsl:value-of select="number(ProductInfo/TotalResults) - number($currentPage * 10)" />
    </xsl:when>
    <xsl:otherwise>
      <xsl:text>10</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="Arg" mode="input_hidden">
  <xsl:if test="@name!='searchWord' and @name!='page' and @name!='KeywordSearch' and @name!='mode' and @name!='sort'">
    <xsl:element name="input">
      <xsl:attribute name="type">hidden</xsl:attribute>
      <xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute>
      <xsl:attribute name="value"><xsl:value-of select="@value" /></xsl:attribute>
    </xsl:element>
  </xsl:if>
</xsl:template>

<xsl:template name="pageInfo">
  <xsl:param name="currentPage"><xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='page']" mode="getValue" /></xsl:param>
  <xsl:choose>
  <xsl:when test="$currentPage &gt; 0 and number(ProductInfo/TotalResults) &gt; 0">
    <div class="result">
      <xsl:value-of select="number(ProductInfo/TotalResults)" />
      <xsl:text> 件ヒット </xsl:text>
      <xsl:text> [ </xsl:text>
      <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='page']" mode="getValue" />
      <xsl:text> / </xsl:text>
      <xsl:value-of select="number(ProductInfo/TotalPages)" />
      <xsl:text> Pages ]　</xsl:text>
      <br />
      <xsl:call-template name="prevPage" /> | <xsl:call-template name="nextPage" />
    </div>
  </xsl:when>
  <xsl:otherwise>
    <div class="result">
      <xsl:text>  </xsl:text>
    </div>
  </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="nextPage">
  <xsl:param name="currentPage"><xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='page']" mode="getValue" /></xsl:param>
  <xsl:param name="totalPage"><xsl:value-of select="ProductInfo/TotalPages" /></xsl:param>
  <xsl:choose>
    <xsl:when test="$currentPage &lt; $totalPage">
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:text>http://xml.amazon.co.jp/onca/xml3?</xsl:text>
          <xsl:text>page=</xsl:text>
          <xsl:value-of select="$currentPage + 1" />
          <xsl:text>&amp;KeywordSearch=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='KeywordSearch']" mode="getValue" />
          <xsl:text>&amp;locale=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='locale']" mode="getValue" />
          <xsl:text>&amp;t=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='t']" mode="getValue" />
          <xsl:text>&amp;dev-t=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='dev-t']" mode="getValue" />
          <xsl:text>&amp;f=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='f']" mode="getValue" />
          <xsl:text>&amp;type=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='type']" mode="getValue" />
        </xsl:attribute>
        <xsl:text>次の</xsl:text>
        <xsl:call-template name="getCount" />
      <xsl:text> 件 &gt;&gt;</xsl:text>
      </xsl:element>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="prevPage">
  <xsl:param name="currentPage"><xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='page']" mode="getValue" /></xsl:param>
  <xsl:choose>
    <xsl:when test="$currentPage &gt; 1">
      <xsl:element name="a">
        <xsl:attribute name="href">
          <xsl:text>http://xml.amazon.co.jp/onca/xml3?</xsl:text>
          <xsl:text>page=</xsl:text>
          <xsl:value-of select="$currentPage - 1" />
          <xsl:text>&amp;KeywordSearch=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='KeywordSearch']" mode="getValue" />
          <xsl:text>&amp;locale=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='locale']" mode="getValue" />
          <xsl:text>&amp;t=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='t']" mode="getValue" />
          <xsl:text>&amp;dev-t=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='dev-t']" mode="getValue" />
          <xsl:text>&amp;f=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='f']" mode="getValue" />
          <xsl:text>&amp;type=</xsl:text>
          <xsl:apply-templates select="ProductInfo/Request/Args/Arg[@name='type']" mode="getValue" />
        </xsl:attribute>
        <xsl:text> &lt;&lt; 前の10件</xsl:text>
      </xsl:element>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template match="Details">
<xsl:variable name="asin" select="Asin" />
<xsl:variable name="name" select="ProductName" />
<xsl:variable name="img" select="ImageUrlMedium" />
<xsl:variable name="price" select="OurPrice" />
<xsl:variable name="ranking" select="SalesRank" />
<xsl:variable name="baseurl">http://www.amazon.co.jp/exec/obidos/ASIN/</xsl:variable>

<div class="inbox">

 <img style="float:left;" onload="if(this.width==1)this.src=(this.src.match(/\.01\./))?notfound.src:this.src.replace('.09.','.01.');" onclick="showTags(this);return false;">
     <xsl:attribute name="src">
     <xsl:value-of select="$img"/>
     </xsl:attribute>
 </img>
<a>
 <xsl:attribute name="href">
  <xsl:value-of select="@url" />
 </xsl:attribute>
</a>
<br />
   <a>
   <xsl:attribute name="href">
   <xsl:value-of select="concat($baseurl, $asin, '/ref=nosim/ppblog-22/')" />
   </xsl:attribute>
 <xsl:attribute name="target">_blank</xsl:attribute>
 <xsl:value-of select="$name"/>
 </a>
 <br />
 <strong>リリース: <xsl:value-of select="ReleaseDate"/></strong><br />
 <strong>定価: <xsl:value-of select="substring(ListPrice, 2)"/> 円</strong><br />
 <strong>アマゾン価格: <xsl:value-of select="substring(OurPrice, 2)"/> 円</strong><br />
 <strong>売り上げランキング: <xsl:value-of select="SalesRank"/> 位</strong><br />
 <span>☆<xsl:value-of select="Availability"/></span>
 <br clear="both" />
</div>
</xsl:template>

</xsl:stylesheet>

