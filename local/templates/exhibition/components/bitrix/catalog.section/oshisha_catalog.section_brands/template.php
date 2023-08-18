<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 *
 *  _________________________________________________________________________
 * |    Attention!
 * |    The following comments are for system use
 * |    and are required for the component to work correctly in ajax mode:
 * |    <!-- items-container -->
 * |    <!-- pagination-container -->
 * |    <!-- component-end -->
 */
$this->setFrameMode(true);

if (!empty($arResult['NAV_RESULT'])) {
    $navParams = array(
        'NavPageCount' => $arResult['NAV_RESULT']->NavPageCount,
        'NavPageNomer' => $arResult['NAV_RESULT']->NavPageNomer,
        'NavNum' => $arResult['NAV_RESULT']->NavNum
    );
} else {
    $navParams = array(
        'NavPageCount' => 1,
        'NavPageNomer' => 1,
        'NavNum' => $this->randString()
    );
}

$showTopPager = false;
$showBottomPager = false;
$showLazyLoad = false;

if ($arParams['PAGE_ELEMENT_COUNT'] > 0 && $navParams['NavPageCount'] > 1) {
    $showTopPager = $arParams['DISPLAY_TOP_PAGER'];
    $showBottomPager = $arParams['DISPLAY_BOTTOM_PAGER'];
    $showLazyLoad = $arParams['LAZY_LOAD'] === 'Y' && $navParams['NavPageNomer'] != $navParams['NavPageCount'];
}

$templateLibrary = array('popup', 'ajax', 'fx');
$currencyList = '';

if (!empty($arResult['CURRENCIES'])) {
    $templateLibrary[] = 'currency';
    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$templateData = array(
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES' => $currencyList
);
unset($currencyList, $templateLibrary);

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));

$positionClassMap = array(
    'left' => 'product-item-label-left',
    'center' => 'product-item-label-center',
    'right' => 'product-item-label-right',
    'bottom' => 'product-item-label-bottom',
    'middle' => 'product-item-label-middle',
    'top' => 'product-item-label-top'
);

$discountPositionClass = '';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y' && !empty($arParams['DISCOUNT_PERCENT_POSITION'])) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$labelPositionClass = '';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' ' . $positionClassMap[$pos] : '';
    }
}

$arParams['~MESS_BTN_BUY'] = $arParams['~MESS_BTN_BUY'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_BUY');
$arParams['~MESS_BTN_DETAIL'] = $arParams['~MESS_BTN_DETAIL'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_DETAIL');
$arParams['~MESS_BTN_COMPARE'] = $arParams['~MESS_BTN_COMPARE'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_COMPARE');
$arParams['~MESS_BTN_SUBSCRIBE'] = $arParams['~MESS_BTN_SUBSCRIBE'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_SUBSCRIBE');
$arParams['~MESS_BTN_ADD_TO_BASKET'] = $arParams['~MESS_BTN_ADD_TO_BASKET'] ?: Loc::getMessage('CT_BCS_TPL_MESS_BTN_ADD_TO_BASKET');
$arParams['~MESS_NOT_AVAILABLE'] = $arParams['~MESS_NOT_AVAILABLE'] ?: Loc::getMessage('CT_BCS_TPL_MESS_PRODUCT_NOT_AVAILABLE');
$arParams['~MESS_SHOW_MAX_QUANTITY'] = $arParams['~MESS_SHOW_MAX_QUANTITY'] ?: Loc::getMessage('CT_BCS_CATALOG_SHOW_MAX_QUANTITY');
$arParams['~MESS_RELATIVE_QUANTITY_MANY'] = $arParams['~MESS_RELATIVE_QUANTITY_MANY'] ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['~MESS_RELATIVE_QUANTITY_FEW'] = $arParams['~MESS_RELATIVE_QUANTITY_FEW'] ?: Loc::getMessage('CT_BCS_CATALOG_RELATIVE_QUANTITY_FEW');

$arParams['MESS_BTN_LAZY_LOAD'] = $arParams['MESS_BTN_LAZY_LOAD'] ?: Loc::getMessage('CT_BCS_CATALOG_MESS_BTN_LAZY_LOAD');

$generalParams = array(
    'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
    'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
    'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
    'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
    'MESS_SHOW_MAX_QUANTITY' => $arParams['~MESS_SHOW_MAX_QUANTITY'],
    'MESS_RELATIVE_QUANTITY_MANY' => $arParams['~MESS_RELATIVE_QUANTITY_MANY'],
    'MESS_RELATIVE_QUANTITY_FEW' => $arParams['~MESS_RELATIVE_QUANTITY_FEW'],
    'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
    'USE_PRODUCT_QUANTITY' => $arParams['USE_PRODUCT_QUANTITY'],
    'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
    'ADD_TO_BASKET_ACTION' => $arParams['ADD_TO_BASKET_ACTION'],
    'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
    'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
    'SHOW_CLOSE_POPUP' => $arParams['SHOW_CLOSE_POPUP'],
    'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
    'COMPARE_PATH' => $arParams['COMPARE_PATH'],
    'COMPARE_NAME' => $arParams['COMPARE_NAME'],
    'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
    'PRODUCT_BLOCKS_ORDER' => $arParams['PRODUCT_BLOCKS_ORDER'],
    'LABEL_POSITION_CLASS' => $labelPositionClass,
    'DISCOUNT_POSITION_CLASS' => $discountPositionClass,
    'SLIDER_INTERVAL' => $arParams['SLIDER_INTERVAL'],
    'SLIDER_PROGRESS' => $arParams['SLIDER_PROGRESS'],
    '~BASKET_URL' => $arParams['~BASKET_URL'],
    '~ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
    '~BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
    '~COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
    '~COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
    'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
    'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
    'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY'],
    'MESS_BTN_BUY' => $arParams['~MESS_BTN_BUY'],
    'MESS_BTN_DETAIL' => $arParams['~MESS_BTN_DETAIL'],
    'MESS_BTN_COMPARE' => $arParams['~MESS_BTN_COMPARE'],
    'MESS_BTN_SUBSCRIBE' => $arParams['~MESS_BTN_SUBSCRIBE'],
    'MESS_BTN_ADD_TO_BASKET' => $arParams['~MESS_BTN_ADD_TO_BASKET'],
    'MESS_NOT_AVAILABLE' => $arParams['~MESS_NOT_AVAILABLE']
);

$obName = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($navParams['NavNum']));
$containerName = 'container-' . $navParams['NavNum'];

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';
$ar_result = [];
$ar_result = CIBlockSection::GetList(array("SORT" => "ASC"), array("IBLOCK_ID" => $arResult['IBLOCK_ID'],
    "ID" => $arResult['ID']), false, array("UF_FILES", "UF_TEXT_BLOCK", "DESCRIPTION", "UF_BIG_FILES", "UF_BIG_FILES_BOTTOM"));

?>

<div class="row<?= $themeClass ?>">
    <div class="col">
        <?php
        //region Pagination
        if ($showTopPager) {
            ?>
            <div class="row mb-4">
                <div class="col text-center" data-pagination-num="<?= $navParams['NavNum'] ?>">
                    <!-- pagination-container -->
                    <?= $arResult['NAV_STRING'] ?>
                    <!-- pagination-container -->
                </div>
            </div>
        <?php } ?>
        <div class="d-flex flex-row  align-items-center mb-5">
            <?php if (!empty($arResult['PICTURE']['SRC'])) {
                ?>
                <div class="mr-5 box_with_image"
                     style="background-image: url('<?= $arResult['PICTURE']['SRC'] ?>');"></div>
            <? } ?>
            <h1 id="pagetitle" class="mb-0"><b><?php $APPLICATION->ShowTitle(false); ?></b></h1>
        </div>

        <?php
        if (($arParams['HIDE_SECTION_DESCRIPTION'] !== 'Y') && !empty($arResult['DESCRIPTION'])) {
            ?>
            <div class="row mb-5">
                <div class="col catalog-section-description">
                    <p><?= $arResult['DESCRIPTION'] ?></p>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="mb-4 catalog-section  row d-flex justify-content-between" data-entity="">


            <?php
            while ($res = $ar_result->Fetch()) {
                if ($res['UF_TEXT_BLOCK']) { ?>
                    <h5 class="mb-4 col-12"><b><?= $res['UF_TEXT_BLOCK'] ?></b></h5>
                <?php }
                foreach ($res['UF_BIG_FILES'] as $bigImageTop) {
                    $ImageOne = CFile::GetPath($bigImageTop);
                    ?>
                    <div class="mb-5 col-12">
                        <img style="max-height: 500px;border-radius: 20px" src="<?= $ImageOne ?>"/>
                    </div>
                <?php } ?>
                <?php foreach ($res["UF_FILES"] as $image) {
                    $ImageOne = CFile::GetPath($image); ?>
                    <div class="mb-5 col-3 ">
                        <img style="max-height: 369px;max-width: fit-content;border-radius: 20px"
                             src="<?= $ImageOne ?>"/>
                    </div>
                <?php } ?>
                <?php
                if ($res['UF_BIG_FILES_BOTTOM']) {
                    $ImageOne = CFile::GetPath($res['UF_BIG_FILES_BOTTOM']);
                    ?>
                    <div class="mb-5 col-12">
                        <img style="max-height: 500px;border-radius: 20px" src="<?= $ImageOne ?>"/>
                    </div>
                    <?php
                }
            } ?>
<!--            <div class="d-flex justify-content-end col-12 mb-5">-->
<!--                <a href="#" class="btn_brand link_red_button">Посмотреть все вкусы</a>-->
<!--            </div>-->
            <h5 class="mt-4 mb-3"><b>Обсуждение бренда</b></h5>
            <div class="comments_box_news mt-3 mb-5">
                <?php

                $APPLICATION->IncludeComponent(
                    'bitrix:catalog.comments',
                    'oshisha_catalog.commets',
                    array(
                        "ELEMENT_ID" => $arResult['ID'],
                        "ELEMENT_CODE" => "",
                        "IBLOCK_ID" => $arParams['IBLOCK_ID'],
                        "SHOW_DEACTIVATED" => $arParams['SHOW_DEACTIVATED'],
                        "URL_TO_COMMENT" => "",
                        "WIDTH" => "",
                        "COMMENTS_COUNT" => "5",
                        "BLOG_USE" => $arParams['BLOG_USE'],
                        "FB_USE" => $arParams['FB_USE'],
                        "FB_APP_ID" => $arParams['FB_APP_ID'],
                        "VK_USE" => $arParams['VK_USE'],
                        "VK_API_ID" => $arParams['VK_API_ID'],
                        "CACHE_TYPE" => $arParams['CACHE_TYPE'],
                        "CACHE_TIME" => $arParams['CACHE_TIME'],
                        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                        "BLOG_TITLE" => "",
                        "BLOG_URL" => $arParams['BLOG_URL'],
                        "PATH_TO_SMILE" => "",
                        "EMAIL_NOTIFY" => $arParams['BLOG_EMAIL_NOTIFY'],
                        "AJAX_POST" => "Y",
                        "SHOW_SPAM" => "Y",
                        "SHOW_RATING" => "N",
                        "FB_TITLE" => "",
                        "FB_USER_ADMIN_ID" => "",
                        "FB_COLORSCHEME" => "light",
                        "FB_ORDER_BY" => "reverse_time",
                        "VK_TITLE" => "",
                        "TEMPLATE_THEME" => $arParams['~TEMPLATE_THEME']
                    ),
                    $component,
                    array('HIDE_ICONS' => 'Y')
                ); ?>
            </div>
        </div>
        <?php

        $signer = new \Bitrix\Main\Security\Sign\Signer;
        $signedTemplate = $signer->sign($templateName, 'catalog.section');
        $signedParams = $signer->sign(base64_encode(serialize($arResult['ORIGINAL_PARAMETERS'])), 'catalog.section');
        ?>
        <script>
            BX.message({
                BTN_MESSAGE_BASKET_REDIRECT: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>',
                BASKET_URL: '<?=$arParams['BASKET_URL']?>',
                ADD_TO_BASKET_OK: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
                TITLE_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_TITLE_ERROR')?>',
                TITLE_BASKET_PROPS: '<?=GetMessageJS('CT_BCS_CATALOG_TITLE_BASKET_PROPS')?>',
                TITLE_SUCCESSFUL: '<?=GetMessageJS('ADD_TO_BASKET_OK')?>',
                BASKET_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_BASKET_UNKNOWN_ERROR')?>',
                BTN_MESSAGE_SEND_PROPS: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_SEND_PROPS')?>',
                BTN_MESSAGE_CLOSE: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE')?>',
                BTN_MESSAGE_CLOSE_POPUP: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>',
                COMPARE_MESSAGE_OK: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_OK')?>',
                COMPARE_UNKNOWN_ERROR: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>',
                COMPARE_TITLE: '<?=GetMessageJS('CT_BCS_CATALOG_MESS_COMPARE_TITLE')?>',
                PRICE_TOTAL_PREFIX: '<?=GetMessageJS('CT_BCS_CATALOG_PRICE_TOTAL_PREFIX')?>',
                RELATIVE_QUANTITY_MANY: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_MANY'])?>',
                RELATIVE_QUANTITY_FEW: '<?=CUtil::JSEscape($arParams['MESS_RELATIVE_QUANTITY_FEW'])?>',
                BTN_MESSAGE_COMPARE_REDIRECT: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>',
                BTN_MESSAGE_LAZY_LOAD: '<?=CUtil::JSEscape($arParams['MESS_BTN_LAZY_LOAD'])?>',
                BTN_MESSAGE_LAZY_LOAD_WAITER: '<?=GetMessageJS('CT_BCS_CATALOG_BTN_MESSAGE_LAZY_LOAD_WAITER')?>',
                SITE_ID: '<?=CUtil::JSEscape($component->getSiteId())?>'
            });
            let <?=$obName?> = new JCCatalogSectionComponent({
                siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
                componentPath: '<?=CUtil::JSEscape($componentPath)?>',
                navParams: <?=CUtil::PhpToJSObject($navParams)?>,
                deferredLoad: false, // enable it for deferred load
                initiallyShowHeader: '<?=!empty($arResult['ITEM_ROWS'])?>',
                bigData: <?=CUtil::PhpToJSObject($arResult['BIG_DATA'])?>,
                lazyLoad: !!'<?=$showLazyLoad?>',
                loadOnScroll: !!'<?=($arParams['LOAD_ON_SCROLL'] === 'Y')?>',
                template: '<?=CUtil::JSEscape($signedTemplate)?>',
                ajaxId: '<?=CUtil::JSEscape($arParams['AJAX_ID'])?>',
                parameters: '<?=CUtil::JSEscape($signedParams)?>',
                container: '<?=$containerName?>'
            });
        </script>
    </div>
</div>
