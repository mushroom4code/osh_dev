<?php

/** @var $arParams */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arParamsString = array(
    "ID" => $arParams['ID'],
    "IBLOCK_ID" => $arParams['IBLOCK_ID'],
    'SEF_URL' => $arParams['SEF_URL'],
    'FIRST_ID' => $arParams['FIRST_ID'],
); ?>
<div class="mb-5 static mt-3" id="box_boxes">
    <h1>Бренды</h1>
    <?php $rsSections = CIBlockSection::GetList(
        array('SORT' => 'ASC'),
        array('IBLOCK_ID' => IBLOCK_CATALOG, 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1),
        false,
        array('DEPTH_LEVEL', 'ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME'));

    while ($arSection = $rsSections->GetNext()) {
        if (empty($arResult[$arSection['NAME']])) {
            continue;
        } ?>
        <div class="d-flex flex-column mb-5" id="box_brands">
            <div class="d-flex flex-row mt-5 mb-3 align-items-center justify-content-between">
                <h5 class="mb-0"><?= $arSection['NAME'] ?></h5>
                    <a href="javascript:void(0)"
                       class="color-redLight text-decoration-underline link_brand font-weight-bold"
                       data-id="click_brand_<?=
                       $arSection['ID']; ?>">Посмотреть все</a>
            </div>
            <div class="box_with_brands_parents justify-content-between" id="click_brand_<?= $arSection['ID']; ?>">
                <?php foreach ($arResult[$arSection['NAME']] as $_brands) { ?>
                    <div class="box_with_brands d-flex justify-content-center align-items-center col col-sm">
                        <?php if ($_brands['UF_FILE']) {
                            $UF_FILE = CFile::ResizeImageGet(
                                $_brands['UF_FILE'],
                                array('width' => 150, 'height' => 55),
                                BX_RESIZE_IMAGE_PROPORTIONAL, true); ?>
                            <a href="/brands/<?= $_brands['UF_CODE'] ?>/" class="logo_brand">
                                <img src="<?= $UF_FILE['src'] ?>">
                            </a>
                        <?php } else { ?>
                            <a href="/brands/<?= $_brands['UF_CODE'] ?>/"
                               class="d-flex justify-content-center align-items-center"><?= $_brands['UF_NAME'] ?></a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>