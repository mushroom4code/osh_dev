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
    <h1 class="title-heading">Каталог товаров</h1>
    <div id="<?= $visual['ID']; ?>" class="catalog-sections-list">
        <?php
        if ($arParams['SHOW_TITLE'] && !empty($arParams['TITLE_MESSAGE'])) {
            ?><h3 class="catalog-sections-list-title"><?= $arParams['TITLE_MESSAGE'] ?></h3><?php
        }
        ?>
        <ul class="catalog-sections-list-container d-flex flex-row flex-wrap" data-items-container="Y">
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
                    $xResizedImage = \CFile::ResizeImageGet(
                        $section['PICTURE'],
                        [
                            'width' => 200,
                            'height' => 200,
                        ]
                    );

                    $x2ResizedImage = \CFile::ResizeImageGet(
                        $section['PICTURE'],
                        [
                            'width' => 400,
                            'height' => 400,
                        ]
                    );

                    if (!$xResizedImage || !$x2ResizedImage) {
                        $xResizedImage = [
                            'src' => $section['PICTURE']['SRC'],
                        ];
                        $x2ResizedImage = $xResizedImage;
                    }

                    $xResizedImage = \Bitrix\Iblock\Component\Tools::getImageSrc([
                        'SRC' => $xResizedImage['src']
                    ]);
                    $x2ResizedImage = \Bitrix\Iblock\Component\Tools::getImageSrc([
                        'SRC' => $x2ResizedImage['src']
                    ]);

                    $style = "background-image: url('{$xResizedImage}');";
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

                    $style = "background-image: url(" . $section['PICTURE']['SRC'] . ");";
                }
                if (strripos($style, 'empty.svg') !== false) {
                    $style = "background-image: url('/local/assets/images/osh.png');";
                } ?>
                <li id="<?= $this->getEditAreaId($section['ID']) ?>"
                    class="catalog-section-list-item col-lg-4 col-md-4 col-12 p-3"
                    data-item-number="<?= $sectionNumber; ?>">
                    <a class="catalog-section-list-tile-img-container p-4 br-10 bg-gray-white" style="<?= $style ?> background-size: 75%; "
                       href="<?= $section['SECTION_PAGE_URL'] ?>">
                        <span class="catalog-section-list-item-inner">
                                <h3 class="catalog-section-list-item-title"><?= $section['NAME'] ?></h3>
                            <?php if ($arParams['COUNT_ELEMENTS'] && $section['ELEMENT_CNT'] !== null) { ?>
                                <span class="catalog-section-list-item-counter font-14">
										<?= $section['ELEMENT_CNT_TITLE'] ?>
                            </span>
                            <?php } ?>
                        </span>
                    </a>
                </li>
                <?php
                $sectionNumber++;
            }
            unset($section);
            ?>
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
