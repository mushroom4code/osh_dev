<?// $_REQUEST['isFromOpt'] - если печать идет из таблицы штрихкодов, делаем возможность скрыть ненужные при клике?>
<style>
.barcode {
  width: 360px;
  min-height: 60px;
  border: 2px solid #000;
  border-radius: 45px;
  float:left;
  margin: 5px;
}

.barcode__wrapper {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
}

.barcode__block-left {
  padding-left: 10px;
}

.barcode-logotype {
  padding: 10px 0 0 22px;
  height:45px;
}

.barcode__block-right .barcode-logotype{
    padding: 10px 0px 0 50px;
}

.barcode-logotype img{
	max-height: 45px;
	max-width: 105px;
}

.barcode-catalogue {
  margin: 0;
  padding: 0;
}

.barcode__list {
  list-style-type: none;
  padding-top: 5px;
  width: 150px;
}

.barcode__list-type {
  list-style-type: none;
  padding-top: 4px;
  width: 150px;
}

.barcode__pt20 {
  padding-top: 20px;
}

.barcode__subtitle {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  padding-left: 10px;
  padding-top: 5px;
  color: #000;
  text-decoration: none;
}

.barcode__block-right {
  margin-left: 10px;
}

.barcode__descr {
  display: block;
  padding-top: 5px;
  color: #000;
  text-decoration: none;
}

.barcode__block-asaid {
  -webkit-transform: rotate(90deg);
          transform: rotate(90deg);
  -webkit-transform-origin: left top 0;
          transform-origin: left top 0;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
  display: block;
  position: relative;
  top: 50px;
  left: 30px;
}

.barcode-support {
  vertical-align: middle;
  width: 300px;
}

.barcode-support a {
  height: auto;
  width: 100%;
  color: #000;
  text-decoration: none;
}

.barcode__marker {
  text-align: center;
  padding-top: 10px;
}

.barcode__marker-fon span {
  background-color: #000;
  height: 50px;
  width: 10px;
}

.barcode__marker img {
	width: 300px;
}

.barcode-symbol {
  padding-top: 10px;
  padding-bottom: 10px;
  font-weight: 700;
}

.barcode-symbol span {
  position: relative;
  right: 10px;
}

@media print {
  /* здесь будут стили для печати */
  .barcode {
    width: 92mm;
    height: auto;
    border: 2px solid #000;
    border-radius: 45px;
  }
  .barcode__block-left {
    padding-left: 3mm;
  }
  .barcode-logotype {
	height: 10mm;
    padding: 5mm 0 0 2mm;
  }
  .barcode-logotype img{
	max-height: 10mm;
    max-width: 25mm;
  }
  .barcode__block-right .barcode-logotype{
	padding: 5mm 0px 0 10mm;
  }
  .barcode-catalogue {
    margin: 0;
    padding: 0;
  }
  .barcode__list {
    list-style-type: none;
    padding-top: 2mm;
    width: 40mm;
  }
  .barcode__list-type {
    list-style-type: none;
    padding-top: 2mm;
    width: 40mm;
  }
  .barcode__pt20 {
    padding-top: 7mm;
  }
  .barcode__subtitle {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    color: #000;
    text-decoration: none;
  }
  .barcode__block-right {
    margin-left: 2mm;
  }
  .barcode__descr {
    display: block;
    color: #000;
    text-decoration: none;
  }
  .barcode__block-asaid {
    -webkit-transform: rotate(90deg);
            transform: rotate(90deg);
    -webkit-transform-origin: left top 0;
            transform-origin: left top 0;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
        -ms-flex-direction: column;
            flex-direction: column;
    display: block;
    position: relative;
    top: 15mm;
    left: 5mm;
  }
  .barcode-support {
    vertical-align: middle;
    width: 300mm;
  }
  .barcode-support a {
    height: auto;
    width: 100%;
    color: #000;
    text-decoration: none;
  }
  .barcode__marker {
    text-align: center;
    padding-top: 5mm;
  }
  .barcode-symbol {
    padding-top: 2mm;
    padding-bottom: 5mm;
    font-weight: 700;
  }
  .barcode-symbol span {
    position: relative;
    right: 2mm;
  }
}
@media print{#hint{display:none;}}
</style>
<?
$j=0;
foreach($arBKs['orders'] as $arBK){
	?>
<div class="barcode" <?if($_REQUEST['isFromOpt']){?> onclick='this.parentNode.removeChild(this);' <?}?>>
        <div class="barcode__wrapper">
            <div class="barcode__block-left">
                <div class="barcode-logotype">
                    <?=($arBKs['logoCompany']) ? '<img src="'.$arBKs['logoCompany'].'"/>' : ''?>
                </div>
                <ul class="barcode-catalogue">
                    <li class="barcode__list"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['orderId']?>:</a></li>
                    <li class="barcode__list"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['pointId']?>:</a></li>
                    <li class="barcode__list"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['pointAddr']?>:</a></li>
                    <li class="barcode__list barcode__pt20"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['seller']?>:</a></li>
                    <li class="barcode__list"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['company']?>:</a></li>
                    <li class="barcode__list"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['receiver']?>:</a></li>
                    <li class="barcode__list"><a href="#" class="barcode__subtitle"><?=$arBKs['lang']['phone']?>:</a></li>
                </ul>
            </div>
            <div class="barcode__block-right">
                <div class="barcode-logotype">
                    <a href="#"><img src="<?=$arBKs['logo5post']?>" alt="logotype"></a>
                </div>
                <ul class="barcode-catalogue">
                    <li class="barcode__list-type"><a href="#" class="barcode__descr"><?=$arBK['NUMBER']?></a></li>
                    <li class="barcode__list-type"><a href="#" class="barcode__descr"><?=$arBK['POINT_NAME']?></a></li>
                    <li class="barcode__list-type"><a href="#" class="barcode__descr"><?=$arBK['POINT_ADDRESS']?></a></li>
                    <li class="barcode__list-type"><a href="#" class="barcode__descr"><?=$arBKs['shopName']?></a></li>
                    <li class="barcode__list-type"><a href="#" class="barcode__descr">5Post</a></li>
                    <li class="barcode__list-type"><a href="#" class="barcode__descr"><?=$arBK['CLIENT_NAME']?></a></li>
                    <li class="barcode__list-type"><a href="tell:<?=str_replace('-','',$arBK['CLIENT_PHONE'])?>" class="barcode__descr"><?=$arBK['CLIENT_PHONE']?></a></li>
                </ul>
            </div>
            <div class="barcode__block-asaid">
                <div class="barcode-support">
                    <a href="tell:88005555505"><?=$arBKs['lang']['hotline']?>: 8(800)555 55 05</a>
                </div>
            </div>
        </div>
        <div class="barcode__marker">
            <img src='<?=$arBKs['path']?>?<?=$arBKs['actionName']?>=getBarcode&barcode=<?=$arBK['FIVEPOST_ID']?>' alt="barcode-marker"/>
			<div class="barcode-symbol"><?=$arBK['FIVEPOST_ID']?></div>
        </div>
    </div>
	
<?
	$j++;
	if($j%2==0){
		echo "<div style='clear:both'></div>";
		echo '<div style="page-break-after:always"/></div>';
	}
}?>
<?if($_REQUEST['isFromOpt']){?>
<div id='hint' style='clear:both'><?=GetMessage('IPOLIML_SHTIHCOD')?></div>
<?}?>
<div style='clear:both'></div>