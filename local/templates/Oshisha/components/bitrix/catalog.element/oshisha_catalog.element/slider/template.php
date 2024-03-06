<div class="md:w-1/2 md:mr-10 mr-0 rounded-xl w-full product_left relative
<?php if (!$show_price) { ?> blur-lg <?php } ?>
<?php if ($actualItem['PRODUCT']['QUANTITY'] == '0') { ?> opacity-50 <?php } ?>">
    <div class="product-item-detail-slider-container <?php if (!empty($taste['VALUE'])) { ?> p-lg-md-25 <?php } ?>"
         id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
        <span class="product-item-detail-slider-close absolute top-16 right-10 hidden z-40" data-entity="close-popup">
            <svg width="25" height="25" viewBox="0 0 9 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 7.5L8 0.5M1 0.5L8 7.5" class="stroke-textDarkLightGray"
                      stroke-linecap="round" stroke-linejoin="round"></path></svg>
        </span>
        <div class="product-item-detail-slider-block relative
             <?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '') ?>"
             data-entity="images-slider-block">
            <div class="variation_taste absolute top-5 left-5 flex flex-row flex-wrap z-10">
                <?php foreach ($taste['VALUE'] as $key => $nameTaste) {
                    foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
                        if ($key === $keys) {
                            $color = explode('#', $value); ?>
                            <span class="taste px-2.5 mr-1 py-1 mb-1 text-sm rounded-full"
                                  data-background="<?= '#' . $color[1] ?>" id="<?= $color[0] ?>">
                            <?= $nameTaste ?></span>
                        <?php }
                    }
                } ?>
            </div>
            <?php if (($newProduct['VALUE'] == 'Да') && ($hitProduct['VALUE'] != 'Да')) { ?>
                <span class="taste bg-greenLight dark:bg-greenButton text-white absolute left-5 top-20 md:py-5 py-1.5 px-1
                md:px-3 rounded-full text-sm z-10 font-medium">NEW</span>
            <?php }
            if ($hitProduct['VALUE'] === 'Да') { ?>
                <span class="taste bg-yellowSt text-black absolute font-semibold left-5 top-20 md:py-5 py-1.5 px-1
                md:px-4 rounded-full text-sm z-10">ХИТ</span>
            <?php } ?>
            <div class="xl:max-h-[650px] md:max-h-[460px] md:min-h-[460px] xl:min-h-[650px] min-h-[300px]
            max-h-[320px] h-full bg-white rounded-xl flex justify-center items-center border-borderColor border dark:border-0">
                <span class="product-item-detail-slider-left carousel_elem_custom absolute top-1/2 md:-left-5 -left-3
                md:py-4 md:px-5 px-3.5 py-3 rounded-full bg-lightGrayBg hover:bg-light-red dark:hover:bg-dark-red z-10 cursor-pointer"
                      data-entity="slider-control-left"
                      style="display: none;">
                    <svg width="10" height="17" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 14.3333L1.33333 7.66667L8 1" stroke="white" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="product-item-detail-slider-right carousel_elem_custom absolute top-1/2 md:-right-5 -right-3
                md:py-4 md:px-5 px-3.5 py-3 rounded-full bg-lightGrayBg hover:bg-light-red dark:hover:bg-dark-red z-10 cursor-pointer"
                      data-entity="slider-control-right"
                      style="display: none;">
                    <svg width="10" height="17" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 14.3333L7.66667 7.66667L1 1" stroke="white" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round"/></svg>
                </span>
                <div class="product-item-detail-slider-images-container rounded-xl h-full flex justify-center items-center"
                     data-entity="images-container">
                    <?php if (!empty($actualItem['PICTURE'][0]['SRC'])) {
                        foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
                            <div class="product-item-detail-slider-image rounded-xl <?= ($key == 0 ? ' active' : '') ?>"
                                 data-entity="image" data-id="<?= $photo['ID'] ?>"
                                 style="display: <?= ($key == 0 ? '  block' : ' none') ?>">
                                <img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
                                     title="<?= $title ?>"
                                     class="rounded-xl lg:max-h-96 xl:max-h-[512px] md:max-h-[320px] max-h-[250px] h-full" <?= ($key == 0 ? ' itemprop="image"' : '') ?>>
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
            <div class="box_with_net absolute top-0 right-0 p-4">
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
                    <svg width="34" height="30" class="stroke-black"
                         onclick="document.querySelector('.shared_block').classList.toggle('hidden')"
                         viewBox="0 0 38 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.6485 21.4484L10.4487 22.4787L10.4326 20.8763L12.5568 19.4565L11.2058 20.53C10.9165 20.76 10.7189 21.0857 10.6485 21.4484ZM13.7071 24.922L13.2624 25.4431L13.5355 24.8674L13.7071 24.922ZM13.4107 24.8277L13.411 24.8278L13.4107 24.8277ZM27.0862 6.44541L9.11228 18.3554L3.0512 13.9528L27.0862 6.44541ZM25.1881 30.0328L14.4322 22.2155L32.4868 6.46121L25.1881 30.0328Z"
                              stroke-width="1"/>
                    </svg>
                    <div class="shared_block hidden">
                        <?php $APPLICATION->IncludeComponent(
                            "arturgolubev:yandex.share",
                            "oshisha",
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
        <div class="product-item-detail-slider-controls-block my-5 flex flex-row"
             id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
            <?php if (!empty($actualItem['PICTURE']) && count($actualItem['PICTURE']) > 1) {
                foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
                    <div class="product-item-detail-slider-controls-image mr-2 p-3 rounded-lg border border-textDark bg-white <?= ($key == 0 ? ' active ' : ' opacity-50') ?>"
                         data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
                        <img src="<?= $photo['SRC'] ?>" class="w-16 h-16">
                    </div>
                <?php }
            } ?>
        </div>
    </div>
</div>