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

if (empty($arResult))
    return;
?>
<nav class="d-block">
    <ul class="nav flex-column">
        <?php foreach ($arResult as $itemIdex => $arItem):
            if ($arItem["TEXT"] == 'Новинки' || $arItem["TEXT"] == 'Дисконт' || $arItem["TEXT"] == 'Акции') continue;
            ?>
            <?php if ($arItem["DEPTH_LEVEL"] == "1" && !empty(htmlspecialcharsbx($arItem["LINK"]))): ?>
            <li class="nav-item li_link_footer">
                <a href="<?= htmlspecialcharsbx($arItem["LINK"]) ?>"
                   class="text_link_footer "><?= htmlspecialcharsbx($arItem["TEXT"], ENT_COMPAT, false) ?></a>
            </li>
        <?php endif ?>
        <?php endforeach; ?>
    </ul>
</nav>