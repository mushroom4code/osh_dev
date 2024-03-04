<?php
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
// Переменная для убора функционала под мобильное приложение
$showUserContent = Enterego\PWA\EnteregoMobileAppEvents::getUserRulesForContent();

if (empty($arResult)) {
    return;
}

foreach ($arResult as $itemIdex => $arItem) {
    if (in_array($arItem["TEXT"], ['Новинки', 'Дисконт', 'Акции']) || $arItem['DEPTH_LEVEL'] > 1):
        continue;
    endif;

    if ($showUserContent || !$showUserContent && $arItem["TEXT"] === 'Чай') {
        $download = '';
        if (strripos($arItem['LINK'], '.pdf') !== false || strripos($arItem['LINK'], '.xls') !== false) {
            $download = 'download';
        } ?>

        <?php if ($arItem["DEPTH_LEVEL"] == "1" && !empty(htmlspecialcharsbx($arItem["LINK"]))) { ?>
            <li class="col-menu-item">
                <a class="col-menu-link" <?= $download ?>
                   href="<?= htmlspecialcharsbx($arItem["LINK"]) ?>"><?= htmlspecialcharsbx($arItem["TEXT"]) ?>
                </a>
            </li>
        <?php } ?>
    <?php }
}