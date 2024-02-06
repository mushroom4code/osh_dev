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
            <li class="li_menu_top mb-3" data-role="bx-menu-item">
                <?php $active = '';
                if ($arResult["ALL_ITEMS"][$itemID]["TEXT"] == 'Дисконт') {
                    continue;
                } ?>
                <div class="wrap_block_menu <?= $class ?> openMenuMobile closed flex flex-row items-center justify-between">
                    <a class="link_menu_top <?= $active ?>" href="javascript:void(0)">
                        <span class="text_catalog_link font-semibold dark:font-light text-dark dark:text-white text-base">
                            <?= htmlspecialcharsbx($arResult["ALL_ITEMS"][$itemID]["TEXT"], ENT_COMPAT, false) ?>
                        </span>
                    </a>
                    <?php if (is_array($arColumns) && count($arColumns) > 0): ?>
                        <svg width="27" height="8" viewBox="0 0 37 18"
                             class="fa_icon fa fa-angle-right fill-lightGrayBg dark:fill-textDarkLightGray" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.805345 0.707087C-0.268449 1.63834 -0.268449 3.14835 0.805345 4.07958L14.2568 15.7347C16.4048 17.5957 19.8852 17.595 22.0321 15.7332L35.4784 4.071C36.5524 3.13977 36.5524 1.62976 35.4784 0.698479C34.4047 -0.232826 32.6636 -0.232826 31.5899 0.698479L20.0813 10.68C19.0075 11.6115 17.2665 11.6112 16.1928 10.68L4.69383 0.707087C3.62006 -0.224218 1.87911 -0.224218 0.805345 0.707087Z"
                            />
                        </svg>
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
                        <ul class="bx-nav-list-2-lvl hidden px-3">
                            <li class="bx-nav-2-lvl mb-2">
                                <a class="bx-nav-2-lvl-link text-sm"
                                   href=" <?= $arResult["ALL_ITEMS"][$itemID]["LINK"] ?>"
                                    <?php if ($existPictureDescColomn): ?>
                                        onmouseover="window.obj_<?= $menuBlockId ?> && obj_<?= $menuBlockId ?>.changeSectionPicure(this, '<?= $id ?>');"
                                    <?php endif ?>
                                   data-picture="<?= $item["PARAMS"]["picture_src"] ?>"
                                   <?php if ($item["SELECTED"]): ?>class="bx-active"<?php endif ?>
                                >
                                    <span class="bx-nav-2-lvl-link-text font-semibold text-sm ">Все</span>
                                </a>
                            </li>
                            <?php foreach ($newSort as $id => $item): ?>
                                <li class="bx-nav-2-lvl mb-1">
                                    <a class="bx-nav-2-lvl-link"
                                       href="<?= $item["LINK"] ?>"
                                        <?php if ($existPictureDescColomn): ?>
                                            onmouseover="window.obj_<?= $menuBlockId ?> && obj_<?= $menuBlockId ?>.changeSectionPicure(this, '<?= $id ?>');"
                                        <?php endif ?>
                                       data-picture="<?= $item["PARAMS"]["picture_src"] ?>"
                                       <?php if ($item["SELECTED"]): ?>class="bx-active"<?php endif ?>
                                    >
                                        <span class="bx-nav-2-lvl-link-text text-sm dark:font-light text-lightGrayBg
                                        dark:text-textDarkLightGray font-normal"><?= $item["TEXT"] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php } ?>
                <?php endif; ?>
            </li>
        <?php } ?>
    </ul>
</nav>
