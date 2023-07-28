<?php

/** @var $arParams */
/** @var $arResult */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$curDateTime = new \Bitrix\Main\Type\DateTime();
//$arParamsString = array(
//    "ID" => $arParams['ID'],
//    "IBLOCK_ID" => $arParams['IBLOCK_ID'],
//    'SEF_URL' => $arParams['SEF_URL'],
//    'FIRST_ID' => $arParams['FIRST_ID'],
//); ?>
<div class="mb-5 static mt-3" id="box_boxes">
    <h1>Акции</h1>
    <p>Розничная дистанционная продажа (доставка) кальянов, табачной, никотинсодержащей продукции на сайте не
        осуществляется.
        Сайт предназначен для потребителей старше 18 лет.</p>
    <div class="d-flex flex-column mt-4 mb-5" id="box_discounts">
        <div class="box_with_discounts_parents justify-content-between" id="click_discount_s">
            <?php foreach ($arResult['DISCOUNTS_IBLOCKS'] as $discount_iblock) { ?>
                <div class="box_with_discounts d-flex justify-content-center align-items-center col col-sm">
                    <a href="/discounts/<?= $discount_iblock['CODE'] ?>/" class="logo_discount">
                        <?php if ($discount_iblock['PICTURE']) {
                            $iblockImage = CFile::GetByID($discount_iblock['PICTURE'])->fetch(); ?>
                            <img src="<?= $iblockImage['SRC'] ?>">
                        <?php } else { ?>
                            <img src="/local/templates/Oshisha/images/no-photo.gif" class="discount_no_image">
                        <?php } ?>
                        <div class="discount_info_box">
                            <div><?= $arResult['DISCOUNTS'][$discount_iblock['ID']]['NAME'] ?></div>
                            <div>
                                <?php if (!empty($arResult['DISCOUNTS'][$discount_iblock['ID']]['ACTIVE_TO'])) {
                                    if (!empty($arResult['DISCOUNTS'][$discount_iblock['ID']]['ACTIVE_FROM'])
                                        && $arResult['DISCOUNTS'][$discount_iblock['ID']]['ACTIVE_FROM'] <= $curDateTime) {
                                        if ($arResult['DISCOUNTS'][$discount_iblock['ID']]['ACTIVE_TO'] >= $curDateTime) {
                                            echo 'до ' . $arResult['DISCOUNTS'][$discount_iblock['ID']]['ACTIVE_TO']->format('d.m.Y');
                                        } else {
                                            echo 'завершена';
                                        }
                                    } else {
                                        echo 'еще не запущена';
                                    }
                                } else {
                                    echo 'бессрочно';
                                } ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>