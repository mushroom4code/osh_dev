<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

$this->setFrameMode(true);

if (!$arResult["NavShowAlways"]) {
    if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
        return;
}

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"] . "&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?" . $arResult["NavQueryString"] : ""); ?>
<div class="bx-pagination">
    <div class="bx-pagination-container">
        <ul class="flex flex-row items-center">
            <?php if ($arResult["bDescPageNumbering"] === true): ?>
                <?php if ($arResult["NavPageNomer"] < $arResult["NavPageCount"]): ?>
                    <?php if ($arResult["bSavePage"]): ?>
                        <li class="bx-pag-prev">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] + 1) ?>">
                                <span class="font-medium dark:font-light text-md mr-2">Назад</span></a>
                        </li>
                        <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] + 1) ?>">
                                <span class="font-medium">1</span></a>
                        </li>
                    <?php else: ?>
                        <?php if (($arResult["NavPageNomer"] + 1) == $arResult["NavPageCount"]): ?>
                            <li class="bx-pag-prev">
                                <a href="<?= $arResult["sUrlPath"] ?><?= $strNavQueryStringFull ?>">
                                    <span class="font-medium dark:font-light text-md mr-2">Назад</span></a>
                            </li>
                        <?php else: ?>
                            <li class="bx-pag-prev">
                                <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] + 1) ?>">
                                    <span class="font-medium dark:font-light text-md mr-2">Назад</span></a>
                            </li>
                        <?php endif ?>
                        <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?><?= $strNavQueryStringFull ?>"><span class="font-medium">1</span></a>
                        </li>
                    <?php endif ?>
                <?php else: ?>
                    <li class="bx-active px-3.5 py-2 rounded-full text-sm font-medium mx-1 dark:bg-grayButton bg-lightGrayBg
                    text-white"><span class="font-medium">1</span></li>
                <?php endif ?>

                <?php $arResult["nStartPage"]--;
                while ($arResult["nStartPage"] >= $arResult["nEndPage"] + 1): ?>
                    <?php $NavRecordGroupPrint = $arResult["NavPageCount"] - $arResult["nStartPage"] + 1; ?>

                    <?php if ($arResult["nStartPage"] == $arResult["NavPageNomer"]): ?>
                        <li class="bx-active px-3.5 py-2 rounded-full text-sm font-medium mx-1 dark:bg-grayButton
                        bg-lightGrayBg text-white"><span class="font-medium"><?= $NavRecordGroupPrint ?></span></li>
                    <?php else: ?>
                        <li class="px-3.5 py-2 rounded-full text-sm mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $arResult["nStartPage"] ?>">
                                <span class="font-medium"><?= $NavRecordGroupPrint ?></span></a>
                        </li>
                    <?php endif ?>

                    <?php $arResult["nStartPage"]-- ?>
                <?php endwhile ?>

                <?php if ($arResult["NavPageNomer"] > 1): ?>
                    <?php if ($arResult["NavPageCount"] > 1): ?>
                        <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=1">
                                <span class="font-medium"><?= $arResult["NavPageCount"] ?></span></a>
                        </li>
                    <?php endif ?>
                    <li class="bx-pag-next">
                        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] - 1) ?>">
                            <span class="font-medium dark:font-light text-md ml-2">Далее</span></a>
                    </li>
                <?php else: ?>
                    <?php if ($arResult["NavPageCount"] > 1): ?>
                        <li class="bx-active px-3.5 py-2 rounded-full text-sm font-medium mx-1 dark:bg-grayButton
                        bg-lightGrayBg text-white"><span class="font-medium"><?= $arResult["NavPageCount"] ?></span></li>
                    <?php endif ?>
                <?php endif ?>
            <?php else: ?>
                <?php if ($arResult["NavPageNomer"] > 1): ?>
                    <?php if ($arResult["bSavePage"]): ?>
                        <li class="bx-pag-prev">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] - 1) ?>">
                                <span class="font-medium dark:font-light text-md mr-2">Назад</span></a>
                        </li>
                        <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=1">
                                <span class="font-medium">1</span></a>
                        </li>
                    <?php else: ?>
                        <?php if ($arResult["NavPageNomer"] > 2): ?>
                            <li class="bx-pag-prev">
                                <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] - 1) ?>">
                                    <span class="font-medium dark:font-light text-md mr-2">Назад</span></a>
                            </li>
                        <?php else: ?>
                            <li class="bx-pag-prev">
                                <a href="<?= $arResult["sUrlPath"] ?><?= $strNavQueryStringFull ?>">
                                    <span class="font-medium dark:font-light text-md mr-2">Назад</span>
                                </a>
                            </li>
                        <?php endif ?>
                        <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?><?= $strNavQueryStringFull ?>"><span class="font-medium">1</span></a>
                        </li>
                    <?php endif ?>
                <?php else: ?>
                    <li class="bx-active px-3.5 py-2 rounded-full text-sm font-medium mx-1 dark:bg-grayButton
                        bg-lightGrayBg text-white"><span class="font-medium">1</span></li>
                <?php endif ?>

                <?php
                $arResult["nStartPage"]++;
                while ($arResult["nStartPage"] <= $arResult["nEndPage"] - 1):
                    ?>
                    <?php if ($arResult["nStartPage"] == $arResult["NavPageNomer"]): ?>
                    <li class="bx-active px-3.5 py-2 rounded-full text-sm font-medium mx-1 dark:bg-grayButton
                        bg-lightGrayBg text-white"><span class="font-medium"><?= $arResult["nStartPage"] ?></span></li>
                <?php else: ?>
                    <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $arResult["nStartPage"] ?>">
                            <span class="font-medium"><?= $arResult["nStartPage"] ?></span></a>
                    </li>
                <?php endif ?>
                    <?php $arResult["nStartPage"]++ ?>
                <?php endwhile ?>

                <?php if ($arResult["NavPageNomer"] < $arResult["NavPageCount"]): ?>
                    <?php if ($arResult["NavPageCount"] > 1): ?>
                        <li class="px-3.5 py-2 rounded-full text-sm font-medium mx-1 bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= $arResult["NavPageCount"] ?>">
                                <span class="font-medium"><?= $arResult["NavPageCount"] ?></span></a>
                        </li>
                    <?php endif ?>
                    <li class="bx-pag-next">
                        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>PAGEN_<?= $arResult["NavNum"] ?>=<?= ($arResult["NavPageNomer"] + 1) ?>">
                            <span class="font-medium dark:font-light text-md ml-2">Далее</span></a>
                    </li>
                <?php else: ?>
                    <?php if ($arResult["NavPageCount"] > 1): ?>
                        <li class="bx-active px-3.5 py-2 rounded-full text-sm font-medium mx-1 dark:bg-grayButton
                        bg-lightGrayBg text-white"><span class="font-medium"><?= $arResult["NavPageCount"] ?></span></li>
                    <?php endif ?>
                <?php endif ?>
            <?php endif ?>

            <?php if ($arResult["bShowAll"]): ?>
                <?php if ($arResult["NavShowAll"]): ?>
                    <li class="bx-pag-all">
                        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>SHOWALL_<?= $arResult["NavNum"] ?>=0"
                           rel="nofollow">
                            <span class="font-medium"><?= GetMessage("round_nav_pages") ?></span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="bx-pag-all">
                        <a href="<?= $arResult["sUrlPath"] ?>?<?= $strNavQueryString ?>SHOWALL_<?= $arResult["NavNum"] ?>=1"
                           rel="nofollow">
                            <span class="font-medium"><?= GetMessage("round_nav_all") ?></span></a></li>
                <?php endif ?>
            <?php endif ?>
        </ul>
        <div style="clear:both"></div>
    </div>
</div>
