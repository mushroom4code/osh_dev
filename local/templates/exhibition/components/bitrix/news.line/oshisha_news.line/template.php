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
$item_id = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
foreach($arResult["ITEMS"] as $arItem){
    $item_id[] = $arItem['ID'];
}

$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id,$FUser_id);

?>




<div class="news-line">
    <? foreach ($arResult["ITEMS"] as $arItem): ?>
        <?
        foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
            if ($keyLike == $arItem['ID']) {
                $arItem['COUNT_LIKES'] = $count;
            }
        }
        foreach ($count_likes['USER'] as $keyFAV => $count) {
            if ($keyFAV == $arItem['ID']) {
                $arItem['COUNT_LIKE'] = $count['Like'][0];
                $arItem['COUNT_FAV'] = $count['Fav'][0];
            }
        }

        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        $dateStr = explode(' ',$arItem["DATE_CREATE"]);
        $props = CIBlockElement::GetProperty($arItem["IBLOCK_ID"], $arItem['ID'], array(), array('CODE' => 'TAG'));
        $propVal = $props->Fetch();

        ?>

        <div class="row mb-5">
            <div class="col-3 col-md-3">
                <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                    <img class="card-img-top image_news_line" src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                         alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                         title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"/>
                </a>
            </div>
            <div class="col-9 col-md-9 d-flex flex-row  justify-content-between  news-list">
                <div class="d-flex flex-column">
                <div class="d-flex flex-row mb-3">
                    <div class="news-list-view news-list-post-params">
                        <span class="news-list-value link_tag"><?= $propVal['VALUE_ENUM'] ?></span>
                    </div>
                    <div class="news-list-view news-list-post-params prop_line">
                        <span class="news_val_data"><?= $dateStr[0] ?></span>
                    </div>
                </div>
                <? if ($arParams["DISPLAY_NAME"] != "N" && $arItem["NAME"]): ?>
                    <h4 class="card-title mb-2">
                        <? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
                            <a  class="text_news_title" href="<? echo $arItem["DETAIL_PAGE_URL"] ?>"><? echo $arItem["NAME"] ?></a>
                        <? else: ?>
                            <? echo $arItem["NAME"] ?>
                        <? endif; ?>
                    </h4>
                <? endif; ?>
                <?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
                    <p class="card-text"><?echo $arItem["PREVIEW_TEXT"];?></p>
                <?endif;?>
                </div>
                    <div>
                    <div class="box_news">
                        <div class="box_with_net">
                            <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                'templates',
                                array(
                                    'ID_PROD' => $arItem['ID'],
                                    'F_USER_ID' => $FUser_id,
                                    'LOOK_LIKE' => true,
                                    'LOOK_FAVORITE' => true,
                                    'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                    'COUNT_FAV' => $arItem['COUNT_FAV'],
                                    'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                                )
                                ,
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            ); ?>
                            <a href="#" title="Поделиться"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <? endforeach; ?>

</div>
