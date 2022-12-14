<?php

use Bitrix\Sale\Fuser;
use DataBase_like;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';
$item_id = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);

foreach ($arResult["ITEMS"] as $arItem) {
    $item_id[] = $arItem['ID'];
}

$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id,$FUser_id);

?>
<h2>Текущие акции</h2>
<div class="promo_items">


    <? foreach ($arResult["ITEMS"] as $arItem): ?>
        <?	//print_r($arItem);
			if( strtotime('NOW') > strtotime($arItem['ACTIVE_TO']) ) continue; 
        $this->AddEditAction(
            $arItem['ID'],
            $arItem['EDIT_LINK'],
            CIBlock::GetArrayByID(
                $arItem["IBLOCK_ID"],
                "ELEMENT_EDIT"
            )
        );
        $this->AddDeleteAction(
            $arItem['ID'],
            $arItem['DELETE_LINK'],
            CIBlock::GetArrayByID(
                $arItem["IBLOCK_ID"],
                "ELEMENT_DELETE"),
            array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))
        );

        ?>

			<div class="promo_item">
				<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="promo_item_image">
					<img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>">
				</a>
			</div>
    <? endforeach; ?>

</div>


<h2 class="last">Прошедшие акции</h2>
<div class="promo_items promo_items_last">
    <? foreach ($arResult["ITEMS"] as $arItem): ?>
        <?
			if( strtotime('NOW') <= strtotime($arItem['ACTIVE_TO']) ) continue;
        $this->AddEditAction(
            $arItem['ID'],
            $arItem['EDIT_LINK'],
            CIBlock::GetArrayByID(
                $arItem["IBLOCK_ID"],
                "ELEMENT_EDIT"
            )
        );
        $this->AddDeleteAction(
            $arItem['ID'],
            $arItem['DELETE_LINK'],
            CIBlock::GetArrayByID(
                $arItem["IBLOCK_ID"],
                "ELEMENT_DELETE"),
            array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))
        );

        ?>

			<div class="promo_item">
				<a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="promo_item_image">
					<img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>">
				</a>
			</div>
    <? endforeach; ?>
</div>