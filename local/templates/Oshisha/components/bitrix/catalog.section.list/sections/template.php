<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

$this->setFrameMode(true);
$emptyImagePath = $this->getFolder() . '/images/tile-empty.svg';
$arParams['TITLE_MESSAGE'] = $arParams['TITLE_MESSAGE'] ?? Loc::getMessage('CT_BCSL_TITLE_MESSAGE');

if ($arResult['SECTIONS_COUNT'] > 0) {
    $mainId = $this->GetEditAreaId($arResult['SECTION']['ID'] . '_' . $arResult['AREA_ID_ADDITIONAL_SALT']);
    $visual = [
        'ID' => $mainId
    ];
    $obName = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);

    if ($arParams['OFFSET_MODE'] == 'D') {
        $templateData = [
            'JS_OBJ' => $obName,
            'REQUEST_KEY' => $arParams['OFFSET_VARIABLE']
        ];
    }

    ?>
    <h1 class="mb-8 text-3xl font-semibold dark:font-light text-lightGrayBg dark:text-textDarkLightGray">Каталог
        товаров</h1>
    <div id="<?= $visual['ID']; ?>" class="catalog-sections-list">
        <?php
        if ($arParams['SHOW_TITLE'] && !empty($arParams['TITLE_MESSAGE'])) {
            ?><h3 class="catalog-sections-list-title"><?= $arParams['TITLE_MESSAGE'] ?></h3><?php
        }
        ?>
        <ul class="catalog-sections-list-container flex flex-row flex-wrap justify-between" data-items-container="Y">
            <?php
            $sectionEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'SECTION_EDIT');
            $sectionDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'SECTION_DELETE');
            $sectionDeleteParams = [
                'CONFIRM' => Loc::getMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'),
            ];

            $sectionNumber = 0;
            foreach ($arResult['SECTIONS'] as &$section) {
                $this->addEditAction($section['ID'], $section['EDIT_LINK'], $sectionEdit);
                $this->addDeleteAction($section['ID'], $section['DELETE_LINK'], $sectionDelete, $sectionDeleteParams);

                if (!empty($section['PICTURE'])) {
                    $style = "background-image: url('{$section['PICTURE']['SRC']}');background-size: 85%;";
                } else {
                    $section['PICTURE'] = [
                        'SRC' => $emptyImagePath,
                        'ALT' => $section['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_ALT'] !== ''
                            ? $section['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_ALT']
                            : $section['NAME'],
                        'TITLE' => $section['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_TITLE'] !== ''
                            ? $section['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_TITLE']
                            : $section['NAME'],
                    ];

                    $style = "background-image: url(" . $section['PICTURE']['SRC'] . "); background-size: 85%;";
                }
                if (strripos($style, 'empty.svg') !== false) {
                    $style = "background-image: url('/local/assets/images/osh.png'); background-size: 85%;";
                } ?>
                <li id="<?= $this->getEditAreaId($section['ID']) ?>"
                    class="catalog-section-list-item lg:w-[31%] md:w-[48%] w-full p-6 pb-0 rounded-xl h-72 md:mr-3 mb-4
                    bg-textDark dark:bg-darkBox mr-0"
                    data-item-number="<?= $sectionNumber; ?>">
                    <a class="catalog-section-list-tile-img-container bg-bottom w-full h-full block bg-no-repeat"
                       style="<?= $style ?> "
                       href="<?= $section['SECTION_PAGE_URL'] ?>">
                        <span class="catalog-section-list-item-inner">
                                <h3 class="catalog-section-list-item-title hover:text-hover-red dark:hover:text-hover-red
                                text-lightGrayBg dark:text-textDarkLightGray font-medium dark:font-normal text-2xl"
                                    style="color:<?= $section['UF_COLOR_TEXT'] ?? '' ?>"><?= $section['NAME'] ?></h3>
                            <?php if ($arParams['COUNT_ELEMENTS'] && $section['ELEMENT_CNT'] !== null) { ?>
                                <span class="catalog-section-list-item-counter hover:text-hover-red
                                  text-base dark:font-light text-lightGrayBg dark:hover:text-hover-red
                                 dark:text-textDarkLightGray text-medium"
                                      style="color:<?= $section['UF_COLOR_TEXT'] ?? '' ?>">
										<?= $section['ELEMENT_CNT_TITLE'] ?>
                            </span>
                            <?php } ?>
                        </span>
                    </a>
                </li>
                <?php
                $sectionNumber++;
            }
            unset($section); ?>
        </ul>
    </div>
    <?php
    $jsParams = [
        'offsetMode' => $arParams['OFFSET_MODE'],
        'settings' => [
            'maxCount' => $arResult['SECTIONS_COUNT']
        ],
        'visual' => array_change_key_case($visual, CASE_LOWER)
    ];
    if ($arParams['OFFSET_MODE'] == 'F') {
        $jsParams['settings']['offset'] = $arParams['OFFSET_VALUE'];
    }
    ?>
    <script>
        var <?=$obName?> = new JCCatalogSectionListStoreComponent(<?=CUtil::PhpToJSObject($jsParams, false, true, true)?>);
    </script><?php
}
