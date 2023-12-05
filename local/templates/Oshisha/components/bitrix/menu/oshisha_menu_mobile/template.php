<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

if (empty($arResult["ALL_ITEMS"]))
    return;

CUtil::InitJSCore();
$menuBlockId = "catalog_menu_" . $this->randString(); ?>
<div class="header_top_panel">
    <a class="box_for_menu" data-toggle="collapse" href="#MenuHeader" aria-controls="MenuHeader" aria-expanded="true">
        <div id="icon" class="Icon">
            <svg width="48" height="37" viewBox="0 0 48 37" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.68994 34.4987H21.2093M2.68994 2.75122H45.0199H2.68994ZM2.68994 18.625H45.0199H2.68994Z"
                      stroke="black" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </a>
</div>
<nav class="box_with_menu_header">
    <ul class="ul_menu">
        <?php foreach ($arResult["MENU_STRUCTURE"] as $itemID => $arColumns) {

            $existPictureDescColomn = ($arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"] ||
                $arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]) ? true : false;
            $class = '';
            $class = "bx-nav-1-lvl bx-nav-list-" . (($existPictureDescColomn)
                    ? count($arColumns) + 1 : count($arColumns)) . "-col";

            if ($arResult["ALL_ITEMS"][$itemID]["SELECTED"]) {
                $class .= " bx-active link_menu_top_active";
            }

            if (is_array($arColumns) && count($arColumns) > 0) {
                $class .= " bx-nav-parent";
            } ?>
            <li class="li_menu_top " data-role="bx-menu-item">
                <?php $active = '';
                if ($arResult["ALL_ITEMS"][$itemID]["LINK"] == '/catalog/diskont/') {
                    continue;
                } ?>
                <div class="wrap_block_menu <?= $class ?> openMenuMobile closed">
                    <a class="link_menu_top <?= $active ?>" href="javascript:void(0)">
                        <span class="text_catalog_link">
                            <?= htmlspecialcharsbx($arResult["ALL_ITEMS"][$itemID]["TEXT"], ENT_COMPAT, false) ?>
                        </span>
                    </a>
                    <?php if (is_array($arColumns) && count($arColumns) > 0): ?>
                        <i class="fa_icon fa fa-angle-right" aria-hidden="true"></i>
                    <?php endif; ?>
                </div>
                <?php if (is_array($arColumns) && count($arColumns) > 0):
                    foreach ($arColumns as $key => $arRow) {
                        $newSort = [];

                        foreach ($arRow as $key => $elem) {
                            $newSort[$key] = $arResult["ALL_ITEMS"][$key];
                        }

                        uasort($newSort, function ($a, $b) {
                            if ($a["TEXT"] == $b["TEXT"]) {
                                return 0;
                            }
                            return ($a["TEXT"] < $b["TEXT"]) ? -1 : 1;
                        }); ?>
                        <ul class="bx-nav-list-2-lvl">
                            <li class="bx-nav-2-lvl">
                                <a class="bx-nav-2-lvl-link"
                                   href=" <?= $arResult["ALL_ITEMS"][$itemID]["LINK"] ?>"
                                    <?php if ($existPictureDescColomn): ?>
                                        onmouseover="window.obj_<?= $menuBlockId ?> && obj_<?= $menuBlockId ?>.changeSectionPicure(this, '<?= $id ?>');"
                                    <?php endif ?>
                                   data-picture="<?= $item["PARAMS"]["picture_src"] ?>"
                                   <?php if ($item["SELECTED"]): ?>class="bx-active"<?php endif ?>
                                >
                                    <span class="bx-nav-2-lvl-link-text">Все</span>
                                </a>
                            </li>
                            <?php foreach ($newSort as $id => $item): ?>
                                <li class="bx-nav-2-lvl">
                                    <a class="bx-nav-2-lvl-link"
                                       href="<?= $item["LINK"] ?>"
                                        <?php if ($existPictureDescColomn): ?>
                                            onmouseover="window.obj_<?= $menuBlockId ?> && obj_<?= $menuBlockId ?>.changeSectionPicure(this, '<?= $id ?>');"
                                        <?php endif ?>
                                       data-picture="<?= $item["PARAMS"]["picture_src"] ?>"
                                       <?php if ($item["SELECTED"]): ?>class="bx-active"<?php endif ?>
                                    >
                                        <span class="bx-nav-2-lvl-link-text"><?= $item["TEXT"] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php } ?>
                <?php endif; ?>
            </li>
            <?
        }
        ?>
    </ul>
</nav>
