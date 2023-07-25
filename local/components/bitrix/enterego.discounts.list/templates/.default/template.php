<?php

/** @var $arParams */
/** @var $arResult */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

//$arParamsString = array(
//    "ID" => $arParams['ID'],
//    "IBLOCK_ID" => $arParams['IBLOCK_ID'],
//    'SEF_URL' => $arParams['SEF_URL'],
//    'FIRST_ID' => $arParams['FIRST_ID'],
//); ?>
<div class="mb-5 static mt-3" id="box_boxes">
    <h1>Бренды</h1>
    <p>Розничная дистанционная продажа (доставка) кальянов, табачной, никотинсодержащей продукции на сайте не
        осуществляется.
        Сайт предназначен для потребителей старше 18 лет.</p>
    <div class="d-flex flex-column mb-5" id="box_brands">
        <div class="box_with_brands_parents justify-content-between" id="click_brand_s">
            <?php foreach ($arResult['DISCOUNTS_IBLOCKS'] as $discount_iblock) { ?>
                <div class="box_with_brands d-flex justify-content-center align-items-center col col-sm">
                    <?php if ($discount_iblock['UF_FILE']) {
                        $UF_FILE = CFile::ResizeImageGet(
                            $discount_iblock['UF_FILE'],
                            array('width' => 150, 'height' => 55),
                            BX_RESIZE_IMAGE_PROPORTIONAL, true); ?>
                        <a href="/brands/<?= $discount_iblock['UF_CODE'] ?>/" class="logo_brand">
                            <img src="<?= $discount_iblock['src'] ?>">
                        </a>
                    <?php } else { ?>
                        <a href="/discounts/<?= $discount_iblock['CODE'] ?>/"
                           class="d-flex justify-content-center align-items-center"><?= $discount_iblock['NAME'] ?></a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>