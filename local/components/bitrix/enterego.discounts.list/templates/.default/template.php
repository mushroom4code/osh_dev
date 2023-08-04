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
        <div class="box_with_discounts_parents <?= count($arResult['DISCOUNTS']) <= 2 ? 'discounts_flex' : 'discounts_grid'?>
         justify-content-between" id="click_discount_s">
            <?php foreach ($arResult['DISCOUNTS'] as $iblock_key => $discount) {
                $discount_iblock = $discount['DISCOUNT_IBLOCK'];

                if ($discount['ACTIVE'] != 'Y') {
                    $discount_status = 'не активна';
                } else {
                    if (!empty($discount['ACTIVE_TO'])) {
                        if (!empty($discount['ACTIVE_FROM'])
                            && $discount['ACTIVE_FROM'] <= $curDateTime) {
                            if ($discount['ACTIVE_TO'] >= $curDateTime) {
                                $discount_status = 'до ' . $discount['ACTIVE_TO']->format('d.m.Y');
                            } else {
                                $discount_status = 'завершена';
                            }
                        } else {
                            $discount_status = 'еще не запущена';
                        }
                    } else {
                        $discount_status = 'бессрочно';
                    }
                }
                ?>

                <div class="box_with_discounts d-flex justify-content-center align-items-center col col-sm">
                    <a href="/akcii/<?= $discount_iblock['CODE'] ?>/" class="logo_discount">
                        <?php if ($discount_iblock['PICTURE']) {
                            $iblockImage = CFile::GetByID($discount_iblock['PICTURE'])->fetch(); ?>
                            <img src="<?= $iblockImage['SRC'] ?>" class="<?= $discount_status == 'завершена' ? 'completed_discount' : ''?>">
                        <?php } else { ?>
                            <img src="/local/templates/Oshisha/images/no-photo.gif" class="discount_no_image <?= $discount_status == 'завершена' ? 'completed_discount' : ''?>">
                        <?php } ?>
                        <div class="discount_info_box">
                            <div><?= $discount['NAME'] ?></div>
                            <div><?= $discount_status ?></div>
                        </div>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>