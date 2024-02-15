<?php

/** @var $arResult array */
/** @var $showDescription string */
/** @var $name string */
/** @var $arIskCode */

?>

<div class="tab-pane  md:mt-8 mt-5 <?php if (!$showDescription): ?>  block active<?php endif; ?>" id="pills-profile">
    <h6 class="xl:text-xl text-lg mb-4 text-lightGrayBg font-semibold dark:font-light dark:text-textDarkLightGray">
        <?= $arResult['NAME'] ?>
    </h6>
    <?php if (!empty($arResult['PROPERTIES'])) { ?>
        <ul class="product-item-detail-properties">
            <?php foreach ($arResult['PROPERTIES'] as $property) {
                if ($property['HINT'] === "DON'T SHOW") {
                    continue;
                }
                if (in_array($property['CODE'], $arIskCode)) continue;

                if ((is_array($property['VALUE']) && count($property['VALUE']) == 0) || $property['VALUE'] == '')
                    continue;
                if ($property['CODE'] == 'BREND') {
                    if (isset($arResult['DISPLAY_PROPERTIES'][$property['CODE']])) {
                        $property['VALUE'] = $arResult['DISPLAY_PROPERTIES'][$property['CODE']]['DISPLAY_VALUE'];
                    }
                } ?>
                <li class="product-item-detail-properties-item mb-3  <?= $property['CODE'] ?>">
                    <span class="product-item-detail-properties-value font-light dark:text-grayIconLights text-textLight">
                        <span class="font-normal dark:font-light dark:text-grayIconLights text-textLight text-sm">
                            <?= $property['NAME'] ?>
                        </span>
                        &nbsp&nbsp-&nbsp&nbsp
                    </span>
                    <span class="product-item-detail-properties-value font-light dark:text-grayIconLights text-textLight text-sm">
                        <?php if (is_array($property['VALUE'])) {
                            echo implode(", ", $property['VALUE']);
                        } else {
                            echo $property['VALUE'];
                        } ?>
                    </span>
                </li>
            <?php }
            unset($property); ?>
        </ul>
    <?php }
    if ($arResult['SHOW_OFFERS_PROPS']) { ?>
        <ul class="product-item-detail-properties" id="<?= $itemIds['DISPLAY_PROP_DIV'] ?>"></ul>
    <?php } ?>
</div>