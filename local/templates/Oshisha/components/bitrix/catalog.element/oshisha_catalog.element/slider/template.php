<div class="md:w-1/2 md:mr-10 mr-0 rounded-xl w-full product_left relative border-borderColor border dark:border-0
<?php if (!$show_price) { ?> blur-lg <?php } ?>">
    <div class="product-item-detail-slider-container h-48-vh <?php if (!empty($taste['VALUE'])) { ?> p-lg-md-25
    <?php } ?>" id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
        <div class="variation_taste absolute top-5 left-5 flex flex-row flex-wrap">
            <?php foreach ($taste['VALUE'] as $key => $nameTaste) {
                foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
                    if ($key === $keys) {
                        $color = explode('#', $value); ?>
                        <span class="taste px-2.5 mr-1 py-1 mb-1 text-sm rounded-full" data-background="<?= '#' . $color[1] ?>" id="<?= $color[0] ?>">
                            <?= $nameTaste ?></span>
                    <?php }
                }
            } ?>
        </div>
        <div class="product-item-detail-slider-block h-full
             <?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '') ?>"
             data-entity="images-slider-block">
            <div class="h-full bg-white rounded-xl flex justify-center items-center">
                <span class="product-item-detail-slider-left carousel_elem_custom"
                      data-entity="slider-control-left"
                      style="display: none;">
                    <i class="fa fa-angle-left" aria-hidden="true"></i>
                </span>
                <span class="product-item-detail-slider-right carousel_elem_custom"
                      data-entity="slider-control-right"
                      style="display: none;">
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                </span>
                <div class="product-item-detail-slider-images-container rounded-xl" data-entity="images-container">
                    <?php if (!empty($actualItem['PICTURE'][0]['SRC'])) {
                        foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
                            <div class="product-item-detail-slider-image rounded-xl <?= ($key == 0 ? ' active' : '') ?>"
                                 data-entity="image" data-id="<?= $photo['ID'] ?>">
                                <img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
                                     title="<?= $title ?>"
                                     class="rounded-xl max-h-96" <?= ($key == 0 ? ' itemprop="image"' : '') ?>>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="product-item-detail-slider-image active rounded-xl" data-entity="image"
                             data-id="1">
                            <img src="/local/templates/Oshisha/images/no-photo.gif" class="rounded-xl max-h-96"
                                 itemprop="image">
                        </div>
                    <?php }
                    if ($arParams['SLIDER_PROGRESS'] === 'Y') { ?>
                        <div class="product-item-detail-slider-progress-bar"
                             data-entity="slider-progress-bar"
                             style="width: 0;"></div>
                    <?php } ?>
                </div>
            </div>
            <div class="box_with_net absolute top-0 right-0" <?php if (empty($taste['VALUE'])) { ?> style="padding: 20px;"<?php } ?>>
                <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                    'templates',
                    array(
                        'ID_PROD' => $arResult['ID'],
                        'F_USER_ID' => $FUser_id,
                        'LOOK_LIKE' => true,
                        'LOOK_FAVORITE' => true,
                        'COUNT_LIKE' => $arResult['COUNT_LIKE'],
                        'COUNT_FAV' => $arResult['COUNT_FAV'],
                        'COUNT_LIKES' => $arResult['COUNT_LIKES'],
                    ),
                    $component,
                    array('HIDE_ICONS' => 'Y')
                ); ?>
                <a href="#" class="delligate shared" title="Поделиться"
                   data-element-id="<?= $arResult['ID'] ?>">
                    <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                    <div class="shared_block">
                        <?php $APPLICATION->IncludeComponent(
                            "arturgolubev:yandex.share",
                            "",
                            array(
                                "DATA_IMAGE" => "",
                                "DATA_RESCRIPTION" => "",
                                "DATA_TITLE" => $arResult['NAME'],
                                "DATA_URL" => 'https://' . SITE_SERVER_NAME . $arResult['DETAIL_PAGE_URL'],
                                "OLD_BROWSERS" => "N",
                                "SERVISE_LIST" => BXConstants::Shared(),
                                "TEXT_ALIGN" => "ar_al_left",
                                "TEXT_BEFORE" => "",
                                "VISUAL_STYLE" => "icons"
                            )
                        ); ?>
                    </div>
                </a>
            </div>
        </div>
        <div class="product-item-detail-slider-controls-block margin_block_element"
             id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
            <?php if (!empty($actualItem['PICTURE']) && count($actualItem['PICTURE']) > 1) {
                foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
                    <div class="product-item-detail-slider-controls-image<?= ($key == 0 ? ' active' : '') ?>"
                         data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
                        <img src="<?= $photo['SRC'] ?>">
                    </div>
                <?php }
            } ?>
        </div>
    </div>
</div>