<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\UI\Extension;
use Bitrix\Sale\Exchange\EnteregoUserExchange;
use Bitrix\Sale\Location\TypeTable;
use Bitrix\Sale\PropertyValueCollection;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $templateFolder
 */
$context = Application::getInstance()->getContext();
$request = $context->getRequest();

if (!isset($arParams['SHOW_ORDER_BUTTON'])) {
    $arParams['SHOW_ORDER_BUTTON'] = 'final_step';
}

//enterego - проставляем выбранный регион из шапки в соответсвующее свойство
foreach ($arResult['JS_DATA']['ORDER_PROP']['properties'] as &$propItems) {
    if ($propItems['CODE'] === 'LOCATION') {
        $propItems['VALUE'][0] = $_SESSION['code_region'];
    } elseif ($propItems['CODE'] === 'CITY') {
        $propItems['VALUE'][0] = $_SESSION['city_of_user'];
    }
}

$arParams['HIDE_ORDER_DESCRIPTION'] = isset($arParams['HIDE_ORDER_DESCRIPTION']) && $arParams['HIDE_ORDER_DESCRIPTION'] === 'Y' ? 'Y' : 'N';
$arParams['SHOW_TOTAL_ORDER_BUTTON'] = $arParams['SHOW_TOTAL_ORDER_BUTTON'] === 'Y' ? 'Y' : 'N';
$arParams['SHOW_PAY_SYSTEM_LIST_NAMES'] = $arParams['SHOW_PAY_SYSTEM_LIST_NAMES'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_PAY_SYSTEM_INFO_NAME'] = $arParams['SHOW_PAY_SYSTEM_INFO_NAME'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_DELIVERY_LIST_NAMES'] = $arParams['SHOW_DELIVERY_LIST_NAMES'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_DELIVERY_INFO_NAME'] = $arParams['SHOW_DELIVERY_INFO_NAME'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_DELIVERY_PARENT_NAMES'] = $arParams['SHOW_DELIVERY_PARENT_NAMES'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_STORES_IMAGES'] = $arParams['SHOW_STORES_IMAGES'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['BASKET_POSITION']) || !in_array($arParams['BASKET_POSITION'], array('before', 'after'))) {
    $arParams['BASKET_POSITION'] = 'after';
}

$arParams['EMPTY_BASKET_HINT_PATH'] = isset($arParams['EMPTY_BASKET_HINT_PATH']) ? (string)$arParams['EMPTY_BASKET_HINT_PATH'] : '/';
$arParams['SHOW_BASKET_HEADERS'] = $arParams['SHOW_BASKET_HEADERS'] === 'Y' ? 'Y' : 'N';
$arParams['HIDE_DETAIL_PAGE_URL'] = isset($arParams['HIDE_DETAIL_PAGE_URL']) && $arParams['HIDE_DETAIL_PAGE_URL'] === 'Y' ? 'Y' : 'N';
$arParams['DELIVERY_FADE_EXTRA_SERVICES'] = $arParams['DELIVERY_FADE_EXTRA_SERVICES'] === 'Y' ? 'Y' : 'N';

$arParams['SHOW_COUPONS'] = isset($arParams['SHOW_COUPONS']) && $arParams['SHOW_COUPONS'] === 'N' ? 'N' : 'Y';

if ($arParams['SHOW_COUPONS'] === 'N') {
    $arParams['SHOW_COUPONS_BASKET'] = 'N';
    $arParams['SHOW_COUPONS_DELIVERY'] = 'N';
    $arParams['SHOW_COUPONS_PAY_SYSTEM'] = 'N';
} else {
    $arParams['SHOW_COUPONS_BASKET'] = isset($arParams['SHOW_COUPONS_BASKET']) && $arParams['SHOW_COUPONS_BASKET'] === 'N' ? 'N' : 'Y';
    $arParams['SHOW_COUPONS_DELIVERY'] = isset($arParams['SHOW_COUPONS_DELIVERY']) && $arParams['SHOW_COUPONS_DELIVERY'] === 'N' ? 'N' : 'Y';
    $arParams['SHOW_COUPONS_PAY_SYSTEM'] = isset($arParams['SHOW_COUPONS_PAY_SYSTEM']) && $arParams['SHOW_COUPONS_PAY_SYSTEM'] === 'N' ? 'N' : 'Y';
}

$arParams['SHOW_NEAREST_PICKUP'] = $arParams['SHOW_NEAREST_PICKUP'] === 'Y' ? 'Y' : 'N';
$arParams['DELIVERIES_PER_PAGE'] = isset($arParams['DELIVERIES_PER_PAGE']) ? intval($arParams['DELIVERIES_PER_PAGE']) : 9;
$arParams['PAY_SYSTEMS_PER_PAGE'] = isset($arParams['PAY_SYSTEMS_PER_PAGE']) ? intval($arParams['PAY_SYSTEMS_PER_PAGE']) : 9;
$arParams['PICKUPS_PER_PAGE'] = isset($arParams['PICKUPS_PER_PAGE']) ? intval($arParams['PICKUPS_PER_PAGE']) : 5;
$arParams['SHOW_PICKUP_MAP'] = $arParams['SHOW_PICKUP_MAP'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_MAP_IN_PROPS'] = $arParams['SHOW_MAP_IN_PROPS'] === 'Y' ? 'Y' : 'N';
$arParams['USE_YM_GOALS'] = $arParams['USE_YM_GOALS'] === 'Y' ? 'Y' : 'N';
$arParams['USE_ENHANCED_ECOMMERCE'] = isset($arParams['USE_ENHANCED_ECOMMERCE']) && $arParams['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
$arParams['DATA_LAYER_NAME'] = isset($arParams['DATA_LAYER_NAME']) ? trim($arParams['DATA_LAYER_NAME']) : 'dataLayer';
$arParams['BRAND_PROPERTY'] = isset($arParams['BRAND_PROPERTY']) ? trim($arParams['BRAND_PROPERTY']) : '';

$useDefaultMessages = !isset($arParams['USE_CUSTOM_MAIN_MESSAGES']) || $arParams['USE_CUSTOM_MAIN_MESSAGES'] != 'Y';

if ($useDefaultMessages || !isset($arParams['MESS_AUTH_BLOCK_NAME'])) {
    $arParams['MESS_AUTH_BLOCK_NAME'] = Loc::getMessage('AUTH_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_REG_BLOCK_NAME'])) {
    $arParams['MESS_REG_BLOCK_NAME'] = Loc::getMessage('REG_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_BASKET_BLOCK_NAME'])) {
    $arParams['MESS_BASKET_BLOCK_NAME'] = Loc::getMessage('BASKET_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_REGION_BLOCK_NAME'])) {
    $arParams['MESS_REGION_BLOCK_NAME'] = Loc::getMessage('REGION_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_PAYMENT_BLOCK_NAME'])) {
    $arParams['MESS_PAYMENT_BLOCK_NAME'] = Loc::getMessage('PAYMENT_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_DELIVERY_BLOCK_NAME'])) {
    $arParams['MESS_DELIVERY_BLOCK_NAME'] = Loc::getMessage('DELIVERY_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_BUYER_BLOCK_NAME'])) {
    $arParams['MESS_BUYER_BLOCK_NAME'] = Loc::getMessage('BUYER_BLOCK_NAME_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_BACK'])) {
    $arParams['MESS_BACK'] = Loc::getMessage('BACK_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_FURTHER'])) {
    $arParams['MESS_FURTHER'] = Loc::getMessage('FURTHER_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_EDIT'])) {
    $arParams['MESS_EDIT'] = Loc::getMessage('EDIT_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_ORDER'])) {
    $arParams['MESS_ORDER'] = $arParams['~MESS_ORDER'] = Loc::getMessage('ORDER_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_PRICE'])) {
    $arParams['MESS_PRICE'] = Loc::getMessage('PRICE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_PERIOD'])) {
    $arParams['MESS_PERIOD'] = Loc::getMessage('PERIOD_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_NAV_BACK'])) {
    $arParams['MESS_NAV_BACK'] = Loc::getMessage('NAV_BACK_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_NAV_FORWARD'])) {
    $arParams['MESS_NAV_FORWARD'] = Loc::getMessage('NAV_FORWARD_DEFAULT');
}

$useDefaultMessages = !isset($arParams['USE_CUSTOM_ADDITIONAL_MESSAGES']) || $arParams['USE_CUSTOM_ADDITIONAL_MESSAGES'] != 'Y';

if ($useDefaultMessages || !isset($arParams['MESS_PRICE_FREE'])) {
    $arParams['MESS_PRICE_FREE'] = Loc::getMessage('PRICE_FREE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_ECONOMY'])) {
    $arParams['MESS_ECONOMY'] = Loc::getMessage('ECONOMY_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_REGISTRATION_REFERENCE'])) {
    $arParams['MESS_REGISTRATION_REFERENCE'] = Loc::getMessage('REGISTRATION_REFERENCE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_AUTH_REFERENCE_1'])) {
    $arParams['MESS_AUTH_REFERENCE_1'] = Loc::getMessage('AUTH_REFERENCE_1_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_AUTH_REFERENCE_2'])) {
    $arParams['MESS_AUTH_REFERENCE_2'] = Loc::getMessage('AUTH_REFERENCE_2_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_AUTH_REFERENCE_3'])) {
    $arParams['MESS_AUTH_REFERENCE_3'] = Loc::getMessage('AUTH_REFERENCE_3_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_ADDITIONAL_PROPS'])) {
    $arParams['MESS_ADDITIONAL_PROPS'] = Loc::getMessage('ADDITIONAL_PROPS_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_USE_COUPON'])) {
    $arParams['MESS_USE_COUPON'] = Loc::getMessage('USE_COUPON_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_COUPON'])) {
    $arParams['MESS_COUPON'] = Loc::getMessage('COUPON_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_PERSON_TYPE'])) {
    $arParams['MESS_PERSON_TYPE'] = Loc::getMessage('PERSON_TYPE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_SELECT_PROFILE'])) {
    $arParams['MESS_SELECT_PROFILE'] = Loc::getMessage('SELECT_PROFILE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_REGION_REFERENCE'])) {
    $arParams['MESS_REGION_REFERENCE'] = Loc::getMessage('REGION_REFERENCE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_PICKUP_LIST'])) {
    $arParams['MESS_PICKUP_LIST'] = Loc::getMessage('PICKUP_LIST_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_NEAREST_PICKUP_LIST'])) {
    $arParams['MESS_NEAREST_PICKUP_LIST'] = Loc::getMessage('NEAREST_PICKUP_LIST_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_SELECT_PICKUP'])) {
    $arParams['MESS_SELECT_PICKUP'] = Loc::getMessage('SELECT_PICKUP_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_INNER_PS_BALANCE'])) {
    $arParams['MESS_INNER_PS_BALANCE'] = Loc::getMessage('INNER_PS_BALANCE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_ORDER_DESC'])) {
    $arParams['MESS_ORDER_DESC'] = Loc::getMessage('ORDER_DESC_DEFAULT');
}

$useDefaultMessages = !isset($arParams['USE_CUSTOM_ERROR_MESSAGES']) || $arParams['USE_CUSTOM_ERROR_MESSAGES'] != 'Y';

if ($useDefaultMessages || !isset($arParams['MESS_PRELOAD_ORDER_TITLE'])) {
    $arParams['MESS_PRELOAD_ORDER_TITLE'] = Loc::getMessage('PRELOAD_ORDER_TITLE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_SUCCESS_PRELOAD_TEXT'])) {
    $arParams['MESS_SUCCESS_PRELOAD_TEXT'] = Loc::getMessage('SUCCESS_PRELOAD_TEXT_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_FAIL_PRELOAD_TEXT'])) {
    $arParams['MESS_FAIL_PRELOAD_TEXT'] = Loc::getMessage('FAIL_PRELOAD_TEXT_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_DELIVERY_CALC_ERROR_TITLE'])) {
    $arParams['MESS_DELIVERY_CALC_ERROR_TITLE'] = Loc::getMessage('DELIVERY_CALC_ERROR_TITLE_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_DELIVERY_CALC_ERROR_TEXT'])) {
    $arParams['MESS_DELIVERY_CALC_ERROR_TEXT'] = Loc::getMessage('DELIVERY_CALC_ERROR_TEXT_DEFAULT');
}

if ($useDefaultMessages || !isset($arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR'])) {
    $arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR'] = Loc::getMessage('PAY_SYSTEM_PAYABLE_ERROR_DEFAULT');
}

$scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'];

$this->addExternalJs($templateFolder . '/order_ajax.js');
PropertyValueCollection::initJs();
$this->addExternalJs($templateFolder . '/script.js');
$showUserContent = Enterego\PWA\EnteregoMobileAppEvents::getUserRulesForContent();
?>
    <NOSCRIPT>
        <div style="color:red"><?= Loc::getMessage('SOA_NO_JS') ?></div>
    </NOSCRIPT>
<?php

if ($request->get('ORDER_ID') <> '') {
    include(Application::getDocumentRoot() . $templateFolder . '/confirm.php');

} elseif ($arParams['DISABLE_BASKET_REDIRECT'] === 'Y' && $arResult['SHOW_EMPTY_BASKET']) {
    include(Application::getDocumentRoot() . $templateFolder . '/empty.php');
} else {
    Extension::load('phone_auth');

    $themeClass = !empty($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';
    $hideDelivery = empty($arResult['DELIVERY']);

    if ($USER->IsAuthorized()) {
        $user_object = new EnteregoUserExchange();
        $user_object->USER_ID = $USER->GetID();
        $user_object->GetActiveContrAgentForUserForOrder();
        $savedDeliveryProfiles = \CommonPVZ\SavedDeliveryProfiles::getAll($user_object->USER_ID);
    } else {
        $savedDeliveryProfiles = false;
    }
    ?>

    <form action="<?= POST_FORM_ACTION_URI ?>" method="POST" name="ORDER_FORM"
          class="bx-soa-wrapper mb-4<?= $themeClass ?>" id="bx-soa-order-form" enctype="multipart/form-data">
        <?php
        echo bitrix_sessid_post();

        if ($arResult['PREPAY_ADIT_FIELDS'] <> '') {
            echo $arResult['PREPAY_ADIT_FIELDS'];
        }
        ?>
        <input type="hidden" name="<?= $arParams['ACTION_VARIABLE'] ?>" value="saveOrderAjax">
        <input type="hidden" name="location_type" value="code">
        <input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult['BUYER_STORE'] ?>">
        <!--	BUYER PROPS BLOCK	-->
        <div id="bx-soa-order" class="row" style="opacity: 0">
            <div id="bx-soa-main-notifications">
                <div class="alert alert-danger" style="display:none"></div>
                <div data-type="informer" style="display:none"></div>
            </div>
            <div class="col-lg-8 col-md-7">
                <h5 class="mb-4"><b>Покупатель</b><i class="fa fa-pencil" aria-hidden="true"></i></h5>
                <div class="bx-soa">
                    <div id="bx-soa-properties" data-visited="true" class="bx-soa-section mb-4 bx-active">
                        <div class="bx-soa-section-title-container">
                            <div class="width_100 mb-4 d-flex align-items-center userCheck" id="userCheck"></div>
                            <?php if ($USER->IsAuthorized() && !empty($user_object->company_user) && !empty($user_object->contragents_user)) { ?>
                                <input value='<?= json_encode($user_object->contragents_user) ?>' type="hidden"
                                       id="connection_company_contragent"/>
                                <div class="width_100 user_select" id="user_select">
                                    <label for="soa-property-9" class="bx-soa-custom-label font_weight_600">
                                        Выбор компании</label>
                                    <select class="company_user_order mb-3" id="company_user_order"
                                            style="display: none;">
                                        <?php if (!empty($user_object->company_user['ADMIN'])) {
                                            foreach ($user_object->company_user['ADMIN'] as $company) {
                                                if ($company['ARCHIVED'] === '0') { ?>
                                                    <option value="<?= $company['COMPANY_ID']; ?>">
                                                        <?= $company['NAME_COMP']; ?>
                                                    </option>
                                                <?php }
                                            }
                                        }
                                        if (!empty($user_object->company_user['USER'])) {
                                            foreach ($user_object->company_user['USER'] as $company) {
                                                if ($company['ARCHIVED'] === '0') { ?>
                                                    <option value="<?= $company['COMPANY_ID']; ?>">
                                                        <?= $company['NAME_COMP']; ?>
                                                    </option>
                                                <?php }
                                            }
                                        } ?>
                                    </select>
                                    <label for="soa-property-10" class="bx-soa-custom-label font_weight_600">
                                        Выбор контрагента</label>
                                    <select class="contragent_user mb-3" id="contragent_user" style="display: none;">
                                        <?php foreach ($user_object->contragents_user as $key => $contragent) {
                                            if ($contragent['ARCHIVED'] === '0' && $contragent['CONTR_AGENT_ACTIVE'] == '1') { ?>
                                                <option value="<?= $contragent['CONTR_AGENT_ID']; ?>">
                                                    <?= $contragent['NAME_CONT']; ?>
                                                </option>
                                            <?php }
                                        } ?>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="bx-soa-section-content"></div>
                    </div>
                    <!--	AUTH BLOCK	-->
                    <div id="bx-soa-auth" class="bx-soa-section mb-4  bx-soa-auth" style="display: none;">
                        <div class="bx-soa-section-title-container">
                            <div class="bx-soa-section-title" data-entity="section-title">
                                <?= $arParams['MESS_AUTH_BLOCK_NAME'] ?>
                            </div>
                        </div>
                        <div class="bx-soa-section-content"></div>
                    </div>

                    <!--	REGION BLOCK	-->
                    <div id="bx-soa-region" data-visited="false" class="bx-soa-section bx-active">
                        <div class="bx-soa-section-title-container">
                            <h2 class="bx-soa-section-title col-sm-9">
                                <span class="bx-soa-section-title-count"></span><?= $arParams['MESS_REGION_BLOCK_NAME'] ?>
                            </h2>
                            <div class="col-xs-12 col-sm-3 text-right"><a href=""
                                                                          class="bx-soa-editstep"><?= $arParams['MESS_EDIT'] ?></a>
                            </div>
                        </div>
                        <div class="bx-soa-section-content container-fluid"></div>
                    </div>

                    <?php if ($arParams['DELIVERY_TO_PAYSYSTEM'] === 'p2d'): ?>
                        <!--	PAY SYSTEMS BLOCK	-->
                        <div id="bx-soa-paysystem" data-visited="false" class="bx-soa-section mb-4  bx-active">
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                    <?= $arParams['MESS_PAYMENT_BLOCK_NAME'] ?>
                                </div>
                            </div>
                            <div class="bx-soa-section-content"></div>
                        </div>
                        <!--	DELIVERY BLOCK	-->
                        <div id="bx-soa-delivery" data-visited="true"
                             class="bx-soa-section mb-4  bx-active" <?= ($hideDelivery ? 'style="display:none"' : '') ?>>
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                    <?= $arParams['MESS_DELIVERY_BLOCK_NAME'] ?>
                                </div>
                            </div>
                            <div class="bx-soa-section-content"></div>
                        </div>
                        <div id="bx-soa-region" data-visited="false" class="bx-soa-section mb-4 d-none">
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                    <?= $arParams['MESS_REGION_BLOCK_NAME'] ?>
                                </div>
                            </div>
                            <div class="bx-soa-section-content"></div>
                        </div>
                        <!--	PICKUP BLOCK	-->
                        <div id="bx-soa-pickup" data-visited="false" class="bx-soa-section mb-4 " style="display:none">
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                </div>
                            </div>
                            <div class="bx-soa-section-content"></div>
                        </div>
                    <?php else: ?>
                        <!--	DELIVERY BLOCK	-->
                        <div id="bx-soa-delivery" data-visited="false"
                             class="bx-soa-section mb-4  bx-active" <?= ($hideDelivery ? 'style="display:none"' : '') ?>>
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                    <?= $arParams['MESS_DELIVERY_BLOCK_NAME'] ?>
                                </div>
                            </div>
                            <div class="box_with_delivery_type">
                                <div class="bx-soa-section-content"></div>
                                <div id="bx-soa-region" data-visited="false"
                                     class="bx-soa-section mb-4 d-none">
                                    <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                        <div class="bx-soa-section-title" data-entity="section-title">
                                            <?= $arParams['MESS_REGION_BLOCK_NAME'] ?>
                                        </div>
                                    </div>
                                    <div class="bx-soa-section-content"></div>
                                </div>
                            </div>
                        </div>

                        <script type="text/template" id="osh-pickup-template">
                            <div class="row">
                                <div class="col-lg-6 col-12">
                                    <div id="pickup-address">
                                        <span></span> <span></span>
                                        <address></address>
                                    </div>
                                    <div id="pickup-station">
                                        <div class="pickup-station-img"></div>
                                    </div>
                                    <div id="pickup-time">
                                        <div class="pickup-time-img"></div>
                                    </div>
                                    <div id="pickup-info">
                                        <div class="pickup-info-img"></div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-12">
                                    <div id="map-pick-up"></div>
                                </div>
                                <div class="pickup-pass-data">
                                    Данные для пропуска
                                </div>
                            </div>
                        </script>


                        <!--	PICKUP BLOCK	-->
                        <div id="bx-soa-pickup" data-visited="false" class="bx-soa-section mb-4 " style="display:none">
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                </div>
                            </div>
                            <div class="bx-soa-section-content"></div>
                        </div>
                        <!--	PAY SYSTEMS BLOCK	-->
                        <div id="bx-soa-paysystem" data-visited="false" class="bx-soa-section mb-4  bx-active">
                            <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
                                <div class="bx-soa-section-title" data-entity="section-title">
                                    <?= $arParams['MESS_PAYMENT_BLOCK_NAME'] ?>
                                </div>
                            </div>
                            <div class="bx-soa-section-content"></div>
                        </div>
                        <div class="p-3">
                            <p class="d-flex flex-row align-items-center font-14">
                                <input type="checkbox" required
                                       class="check_input form-check-input mr-2 mt-0 checked_active_button"
                                       id="soa-property-USER_RULES" checked name="USER_RULES"/>
                                <label class="bx-soa-custom-label m-0">
                                    Я принимаю условия
                                    <?php if ($showUserContent) { ?>
                                        <a class="color-redLight text-decoration-underline" href="/about/users_rules/">
                                            Пользовательского соглашения
                                        </a>
                                    <?php } else { ?>
                                        <a class="color-redLight text-decoration-underline"
                                           href="/about/cookie/">
                                            Политики обработки Cookie
                                        </a>
                                    <?php } ?>
                                </label>
                            </p>
                            <p class="d-flex flex-row align-items-center font-14">
                                <input type="checkbox" required checked
                                       class="check_input form-check-input mr-2 mt-0 checked_active_button"
                                       name="USER_POLITICS"/>
                                <label class="bx-soa-custom-label m-0">
                                    Я принимаю условия
                                    <a class="color-redLight text-decoration-underline" href="/about/politics/">
                                        Политики конфиденциальности
                                    </a>
                                </label>
                            </p>
                        </div>
                        <div class="new_block_with_comments mt-0" id="new_block_with_comments">
                            <div id="new_block_with_comment_box"></div>
                        </div>
                    <?php endif ?>

                    <!--	ORDER SAVE BLOCK	-->
                    <div id="bx-soa-orderSave">
                        <div class="checkbox">
                            <?php
                            if ($arParams['USER_CONSENT'] === 'Y') {
                                $APPLICATION->IncludeComponent(
                                    'bitrix:main.userconsent.request',
                                    '',
                                    array(
                                        'ID' => $arParams['USER_CONSENT_ID'],
                                        'IS_CHECKED' => $arParams['USER_CONSENT_IS_CHECKED'],
                                        'IS_LOADED' => $arParams['USER_CONSENT_IS_LOADED'],
                                        'AUTO_SAVE' => 'N',
                                        'SUBMIT_EVENT_NAME' => 'bx-soa-order-save',
                                        'REPLACE' => array(
                                            'button_caption' => isset($arParams['~MESS_ORDER']) ? $arParams['~MESS_ORDER'] : $arParams['MESS_ORDER'],
                                            'fields' => $arResult['USER_CONSENT_PROPERTY_DATA']
                                        )
                                    )
                                );
                            }
                            ?>
                        </div>
                    </div>

                    <div style="display: none;">
                        <div id='bx-soa-region-hidden' class="bx-soa-section"></div>
                        <div id='bx-soa-paysystem-hidden' class="bx-soa-section"></div>
                        <div id='bx-soa-delivery-hidden' class="bx-soa-section"></div>
                        <div id='bx-soa-pickup-hidden' class="bx-soa-section"></div>
                        <div id="bx-soa-properties-hidden" class="bx-soa-section"></div>
                        <div id="bx-soa-auth-hidden" class="bx-soa-section">
                            <div class="bx-soa-section-content reg"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--	SIDEBAR BLOCK	-->
            <div class="col-lg-4 col-md-5 ">
                <h5 class="order_text"><b>Оформление заказа</b></h5>
                <div class="d-flex flex-lg-row flex-md-row flex-column align-items-center mb-3">
                    <p class="font-13 m-0 mr-1">
                        При получении заказа, возможно, потребуется предъявить документ, подтверждающий ваш возраст.
                    </p>
                    <span class="confidintial font-15">18+</span>
                </div>
                <div id="bx-soa-total" class="mb-5 bx-soa-sidebar">
                    <div class="bx-soa-cart-total-ghost"></div>
                    <div class="bx-soa-cart-total"></div>
                </div>
            </div>
        </div>
    </form>

    <div id="bx-soa-saved-files" style="display:none"></div>
    <div id="bx-soa-soc-auth-services" style="display:none">
        <?php
        $arServices = false;
        $arResult['ALLOW_SOCSERV_AUTHORIZATION'] = Option::get('main', 'allow_socserv_authorization', 'Y') != 'N' ? 'Y' : 'N';
        $arResult['FOR_INTRANET'] = false;

        if (ModuleManager::isModuleInstalled('intranet') || ModuleManager::isModuleInstalled('rest'))
            $arResult['FOR_INTRANET'] = true;

        if (Loader::includeModule('socialservices') && $arResult['ALLOW_SOCSERV_AUTHORIZATION'] === 'Y') {
            $oAuthManager = new CSocServAuthManager();
            $arServices = $oAuthManager->GetActiveAuthServices(array(
                'BACKURL' => $this->arParams['~CURRENT_PAGE'],
                'FOR_INTRANET' => $arResult['FOR_INTRANET'],
            ));

            if (!empty($arServices)) {
                $APPLICATION->IncludeComponent(
                    'bitrix:socserv.auth.form',
                    'flat',
                    array(
                        'AUTH_SERVICES' => $arServices,
                        'AUTH_URL' => $arParams['~CURRENT_PAGE'],
                        'POST' => $arResult['POST'],
                    ),
                    $component,
                    array('HIDE_ICONS' => 'Y')
                );
            }
        }
        ?>
    </div>

    <div style="display: none">
        <?php
        // we need to have all styles for sale.location.selector.steps, but RestartBuffer() cuts off document head with styles in it
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.steps',
            '.default',
            array(),
            false
        );
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '.default',
            array(),
            false
        );
        ?>
    </div>
    <?php
    $signer = new Signer;
    $signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.order.ajax');
    $messages = Loc::loadLanguageFile(__FILE__);

    $PeriodDelivery = [];
    $start_json_day = Option::get('osh.shipping', 'osh_timeDeliveryStartDay');
    $end_json_day = Option::get('osh.shipping', 'osh_timeDeliveryEndDay');
    $start_json_night = Option::get('osh.shipping', 'osh_timeDeliveryStartNight');
    $end_json_night = Option::get('osh.shipping', 'osh_timeDeliveryEndNight');
    $start_day = json_decode($start_json_day);
    $end_day = json_decode($end_json_day);
    $start_night = json_decode($start_json_night);
    $end_night = json_decode($end_json_night);

    if (!empty($start_day) && !empty($end_day)) {
        foreach ($start_day as $key => $elems_start) {
            $PeriodDelivery[] = $elems_start . '-' . $end_day[$key];
        }
    }

    if (!empty($start_night) && !empty($end_night)) {
        foreach ($start_night as $keys => $elems_start_night) {
            $PeriodDelivery[] .= $elems_start_night . '-' . $end_night[$keys];
        }
    }

    $arParams['AR_DELIVERY_PICKUP'] = AR_DELIVERY_PICKUP;
    ?>
    <script>
        <?php if ($USER->IsAuthorized()) {?>
        let bool_contrs = $('input').is('#connection_company_contragent');
        if (bool_contrs) {
            let contragent_json = $('#connection_company_contragent').val();
            if (contragent_json !== '') {
                let contragent_array = JSON.parse(contragent_json);
                let document_container = $('#company_user_order');

                function showContrsAccess(that) {
                    let id = $(that).val();
                    let box_contrs = $('#contragent_user');
                    let new_array = [];
                    let bool;
                    $.each(contragent_array, function (key, value) {
                        $.each(value.COMPANY, function (key_company) {
                            bool = key_company === id ? true : false;
                        });
                        if (bool) {
                            new_array.push(value);
                        }
                        $(box_contrs).html('');
                        $.each(new_array, function (keys, val) {
                            $(box_contrs).append('<option value="' + val.CONTR_AGENT_ID + '">' + val.NAME_CONT + '</option>')
                        });
                    });
                }

                showContrsAccess(document_container);

                $(document_container).on('change', function () {
                    showContrsAccess(this)
                });
            }
        }
        <?php }?>
        // END Enterego
        BX.message(<?=CUtil::PhpToJSObject($messages)?>);
        BX.Sale.OrderAjaxComponent.init({
            result: <?=CUtil::PhpToJSObject($arResult['JS_DATA'])?>,
            deliveryOptions: <?=CUtil::PhpToJSObject($arResult['DELIVERY_OPTIONS'])?>,
            locations: <?=CUtil::PhpToJSObject($arResult['LOCATIONS'])?>,
            savedDeliveryProfiles: <?=CUtil::PhpToJSObject($savedDeliveryProfiles)?>,
            params: <?=CUtil::PhpToJSObject($arParams)?>,
            signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
            siteID: '<?=CUtil::JSEscape($component->getSiteId())?>',
            ajaxUrl: '<?=CUtil::JSEscape($component->getPath() . '/ajax.php')?>',
            templateFolder: '<?=CUtil::JSEscape($templateFolder)?>',
            propertyValidation: true,
            showWarnings: true,
            pickUpMap: {
                defaultMapPosition: {
                    lat: 55.76,
                    lon: 37.64,
                    zoom: 7
                },
                secureGeoLocation: false,
                geoLocationMaxTime: 5000,
                minToShowNearestBlock: 3,
                nearestPickUpsToShow: 3
            },
            propertyMap: {
                defaultMapPosition: {
                    lat: 55.76,
                    lon: 37.64,
                    zoom: 7
                }
            },
            orderBlockId: 'bx-soa-order',
            authBlockId: 'bx-soa-auth',
            regionBlockId: 'bx-soa-region',
            paySystemBlockId: 'bx-soa-paysystem',
            deliveryBlockId: 'bx-soa-delivery',
            pickUpBlockId: 'bx-soa-pickup',
            propsBlockId: 'bx-soa-properties',
            newBlockId: 'new_block_with_comments',
            totalBlockId: 'bx-soa-total',
            userCheck: 'userCheck'
        });
        BX.ready(function () {
            var wait = BX.showWait('bx-soa-order-form');  // показываем прелоадер в правом верхнем углу контейнер
            var deferreds = [];

            BX.closeWait('bx-soa-order-form', wait); // прячем прелоадер
        });
        $(document).on('click', 'input.checked_active_button', function () {
            if ($('input[name="USER_RULES"]').prop('checked') === true
                && $('input[name="USER_POLITICS"]').prop('checked') === true) {
                $(document).find('.btn-order-save').removeAttr('style');
            } else {
                $(document).find('.btn-order-save').attr('style','opacity: 0.65');
            }
        });
    </script>
    <script>
        <?php
        // spike: for children of cities we place this prompt
        $city = TypeTable::getList(array('filter' => array('=CODE' => 'CITY'), 'select' => array('ID')))->fetch();
        ?>
        BX.saleOrderAjax.init(<?=CUtil::PhpToJSObject(array(
            'source' => $component->getPath() . '/get.php',
            'cityTypeId' => intval($city['ID']),
            'messages' => array(
                'otherLocation' => '--- ' . Loc::getMessage('SOA_OTHER_LOCATION'),
                'moreInfoLocation' => '--- ' . Loc::getMessage('SOA_NOT_SELECTED_ALT'), // spike: for children of cities we place this prompt
                'notFoundPrompt' => '<div class="-bx-popup-special-prompt">' . Loc::getMessage('SOA_LOCATION_NOT_FOUND') . '.<br />' . Loc::getMessage('SOA_LOCATION_NOT_FOUND_PROMPT', array(
                        '#ANCHOR#' => '<a href="javascript:void(0)" class="-bx-popup-set-mode-add-loc">',
                        '#ANCHOR_END#' => '</a>'
                    )) . '</div>'
            )
        ))?>);
    </script>
    <?php

    if ($arParams['USE_YM_GOALS'] === 'Y') {
        ?>
        <script>
            (function bx_counter_waiter(i) {
                i = i || 0;
                if (i > 50)
                    return;

                if (typeof window['yaCounter<?=$arParams['YM_GOALS_COUNTER']?>'] !== 'undefined')
                    BX.Sale.OrderAjaxComponent.reachGoal('initialization');
                else
                    setTimeout(function () {
                        bx_counter_waiter(++i)
                    }, 100);
            })();
        </script>
        <?php
    }
}
