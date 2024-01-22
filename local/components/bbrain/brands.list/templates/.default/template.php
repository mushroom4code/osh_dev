<?php

/** @var $arParams */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$maxItemsLength = 16;
$arParamsString = array(
    "ID" => $arParams['ID'],
    "IBLOCK_ID" => $arParams['IBLOCK_ID'],
    'SEF_URL' => $arParams['SEF_URL'],
    'FIRST_ID' => $arParams['FIRST_ID'],
); ?>
<div class="mb-5 static mt-3" id="box_boxes">
    <h1 class="md:text-3xl text-2xl my-5 font-bold dark:font-medium text-textLight dark:text-textDarkLightGray">
        Бренды</h1>
    <?php $rsSections = CIBlockSection::GetList(
        array('SORT' => 'ASC'),
        array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y', 'GLOBAL_ACTIVE' => 'Y', 'DEPTH_LEVEL' => 1),
        false,
        array('DEPTH_LEVEL', 'ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME'));

    while ($arSection = $rsSections->GetNext()) {
        if (empty($arResult[$arSection['NAME']])) {
            continue;
        } ?>
        <div class="flex flex-col mb-5" id="box_brands">
            <div class="flex flex-row mt-5 mb-7 items-center justify-between">
                <h5 class="md:text-2xl text-lg font-semibold dark:font-normal text-textLight dark:text-textDarkLightGray">
                    <?= $arSection['NAME'] ?>
                </h5>
                <?php if (count($arResult[$arSection['NAME']]) > $maxItemsLength) { ?>
                    <a href="javascript:void(0)"
                       class="text-textDark text-center shadow-md dark:bg-dark-red text-md bg-light-red py-1 px-5 rounded-md w-fit"
                       data-id="click_brand_<?= $arSection['ID']; ?>" onclick="showHideBoxBrands(this)">Показать все</a>
                <?php } ?>
            </div>
            <div class="flex flex-row flex-wrap max-h-[27.5rem] overflow-hidden"
                 id="click_brand_<?= $arSection['ID']; ?>">
                <?php foreach ($arResult[$arSection['NAME']] as $_brands) { ?>
                    <div class="flex mb-3 justify-center items-center md:w-1/4 w-1/2">
                        <div class="mr-3 rounded-lg border-textDark border-2 bg-white h-full w-full p-5">
                            <?php if ($_brands['UF_FILE']) {
                                $UF_FILE = CFile::ResizeImageGet(
                                    $_brands['UF_FILE'],
                                    array('width' => 150, 'height' => 55),
                                    BX_RESIZE_IMAGE_PROPORTIONAL, true); ?>
                                <a href="/brands/<?= $_brands['UF_CODE'] ?>/"
                                   class="w-full h-full flex justify-center items-center">
                                    <img src="<?= $UF_FILE['src'] ?>"
                                         class="max-w-40 w-auto h-auto object-contain" alt="osh-brands"/>
                                </a>
                            <?php } else { ?>
                                <a href="/brands/<?= $_brands['UF_CODE'] ?>/"
                                   class="flex justify-center items-center"><?= $_brands['UF_NAME'] ?></a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
<script>
    function showHideBoxBrands(item) {
        const boxBrands = document.querySelector('[id="' + item.getAttribute('data-id') + '"]');
        if (boxBrands.classList.contains('overflow-hidden')) {
            boxBrands.classList.remove('overflow-hidden')
            boxBrands.classList.remove('max-h-[27.5rem]')
            item.innerHTML = 'Скрыть'
        } else {
            boxBrands.classList.add('overflow-hidden')
            boxBrands.classList.add('max-h-[27.5rem]')
            item.innerHTML = 'Показать все'
        }
    }
</script>