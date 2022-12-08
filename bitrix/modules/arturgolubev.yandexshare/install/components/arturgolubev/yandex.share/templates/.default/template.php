<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
// echo '<pre>'; print_r($arParams); echo '</pre>';

if(empty($arParams["SERVISE_LIST"])){
	$arParams["SERVISE_LIST"] = array("collections", "vkontakte", "facebook", "odnoklassniki", "moimir", "gplus", "twitter", "blogger", "delicious", "digg", "reddit", "evernote", "linkedin", "lj", "pocket", "qzone", "renren", "sinaWeibo", "surfingbird", "tencentWeibo", "tumblr", "viber", "whatsapp", "skype", "telegram");
}

if(empty($arParams["SERVISE_LIST"]))
	return;

$arParamsShow = array(
	"icons" => '',
	"counters" => 'data-counter=""',
	"iconsmenu" => ' data-limit="'.(($arParams["COUNT_FOR_SMALL"]) ? $arParams["COUNT_FOR_SMALL"] : 3).'"',
	"small" => 'data-size="s"',
);

$dataline = '';
if($arParams["DATA_TITLE"])
	$dataline .= ' data-title="'.$arParams["DATA_TITLE"].'"';
if($arParams["DATA_RESCRIPTION"])
	$dataline .= ' data-description="'.$arParams["DATA_RESCRIPTION"].'"';
if($arParams["DATA_IMAGE"])
	$dataline .= ' data-image="'.$arParams["DATA_IMAGE"].'"';
if($arParams["DATA_URL"])
	$dataline .= ' data-url="'.$arParams["DATA_URL"].'"';
?>

<div class="yandex-share-panel <?=$arParams["TEXT_ALIGN"]?>">
	<?if(trim($arParams["TEXT_BEFORE"])):?><div class="ya-share-text-wrap"><?=$arParams["TEXT_BEFORE"]?></div><?endif;?>
	<div class="ya-share-wrap"><div class="ya-share2" data-services="<?=implode(',', $arParams["SERVISE_LIST"])?>" <?=$arParamsShow[$arParams["VISUAL_STYLE"]]?> <?=$dataline?>></div></div>
</div>
