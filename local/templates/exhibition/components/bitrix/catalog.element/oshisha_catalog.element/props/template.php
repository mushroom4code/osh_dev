<?php

/** @var $arResult array */
/** @var $showDescription string */
/** @var $name string */
/** @var $arIskCode  */

?>

<div class="tab-pane fade <? if (!$showDescription): ?>show active<? endif; ?>" id="pills-profile"
     role="tabpanel" aria-labelledby="pills-profile-tab">
    <h6 class="mb-3"><b><?= $name ?></b></h6>
    <?php if (!empty($arResult['PROPERTIES'])) { ?>
        <ul class="product-item-detail-properties">
            <?php

            foreach ($arResult['PROPERTIES'] as $property) {
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
                }
                ?>
                <li class="product-item-detail-properties-item  <?= $property['CODE'] ?>">
                                    <span class="product-item-detail-properties-value"> - &nbsp<b>
                                            <?= $property['NAME'] ?> </b> &nbsp&nbsp-&nbsp&nbsp </span>
                    <span class="product-item-detail-properties-value"> <?php
                        if (is_array($property['VALUE'])) {
                            echo implode(", ", $property['VALUE']);
                        } else {
                            echo $property['VALUE'];
                        } ?>
										</span>
                </li>
                <?php
            }
            unset($property); ?>
        </ul>
        <?php
    }
    if ($arResult['SHOW_OFFERS_PROPS']) { ?>
        <ul class="product-item-detail-properties" id="<?= $itemIds['DISPLAY_PROP_DIV'] ?>"></ul>
    <?php } ?>
</div>