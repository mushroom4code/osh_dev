<?php

/** @var $arParams */
/** @var $arResult */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$curDateTime = new \Bitrix\Main\Type\DateTime();
?>
<div class="mb-5 static mt-3" id="box_boxes">
    <h1>Акции</h1>
    <p class="mb-5">Розничная дистанционная продажа (доставка) кальянов, табачной, никотинсодержащей продукции на сайте
        не
        осуществляется.
        Сайт предназначен для потребителей старше 18 лет.</p>
    <div class="d-flex flex-column mt-4 mb-5" id="box_discounts">
        <div class="box_with_discounts_parents justify-content-between" id="click_discount_s">
            <?php foreach ($arResult['DISCOUNTS'] as $iblock_key => $discount) {
                $discount_iblock = $arResult['DISCOUNTS_IBLOCKS'][$iblock_key];
                ?>
                <div class="box_with_discounts d-flex justify-content-center align-items-center col col-sm">
                    <a href="/discounts/<?= $discount_iblock['CODE'] ?>/" class="logo_discount">
                        <?php if ($discount_iblock['PICTURE']) {
                            $iblockImage = CFile::GetByID($discount_iblock['PICTURE'])->fetch(); ?>
                            <img src="<?= $iblockImage['SRC'] ?>">
                        <?php } else { ?>
                            <img src="/local/templates/Oshisha/images/no-photo.gif" class="discount_no_image">
                        <?php } ?>
                        <div class="discount_info_box">
                            <div><?= $discount['NAME'] ?></div>
                            <div>
                                <?php
                                if ($discount['ACTIVE'] != 'Y') {
                                    echo 'не активна';
                                } else {
                                    if (!empty($disount['ACTIVE_TO'])) {
                                        if (!empty($disount['ACTIVE_FROM'])
                                            && $disount['ACTIVE_FROM'] <= $curDateTime) {
                                            if ($disount['ACTIVE_TO'] >= $curDateTime) {
                                                echo 'до ' . $disount['ACTIVE_TO']->format('d.m.Y');
                                            } else {
                                                echo 'завершена';
                                            }
                                        } else {
                                            echo 'еще не запущена';
                                        }
                                    } else {
                                        echo 'бессрочно';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>