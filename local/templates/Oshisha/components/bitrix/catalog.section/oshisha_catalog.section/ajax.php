<?php
/** @global \CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId)) {
    define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application,
    Bitrix\Main\Web\Cookie;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
    return;

$signer = new \Bitrix\Main\Security\Sign\Signer;
try {
    $template = $signer->unsign($request->get('template') ?: '', 'catalog.section') ?: '.default';
    $paramString = $signer->unsign($request->get('parameters') ?: '', 'catalog.section');
} catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {
    die();
}

$parameters = unserialize(base64_decode($paramString));

if ($request->getPost('hide_not_available') == "Y") {
    $parameters['HIDE_NOT_AVAILABLE'] = $request->getPost('hide_not_available');
} else {
    $parameters['HIDE_NOT_AVAILABLE'] = "L";
}

//if ($request->getPost('sort_without_filter_data') == 'Y') {
//    if (!empty($_SESSION[$parameters["FILTER_NAME"]][$_SESSION['GLOBAL_CURRENT_SECTION']['ID']])) {
//        $_REQUEST = array_merge($_REQUEST, $_SESSION[$parameters["FILTER_NAME"]][$_SESSION['GLOBAL_CURRENT_SECTION']['ID']]);
//    }
//}

if (isset($parameters['PARENT_NAME'])) {
    $parent = new CBitrixComponent();
    $parent->InitComponent($parameters['PARENT_NAME'], $parameters['PARENT_TEMPLATE_NAME']);
    $parent->InitComponentTemplate($parameters['PARENT_TEMPLATE_PAGE']);
} else {
    $parent = false;
}

if ($parameters) {
    if (!empty($_REQUEST['subcat'])) {
        $parameters["SECTION_CODE"] = trim($_REQUEST['subcat']);
    }

    $arFilter = array(
        "IBLOCK_ID" => $parameters["IBLOCK_ID"],
        "ACTIVE" => "Y",
        "GLOBAL_ACTIVE" => "Y",
        "=CODE" => $parameters["SECTION_CODE"]
    );

    $obCache = new CPHPCache();
    if ($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog")) {
        $arCurSection = $obCache->GetVars();
    } elseif ($obCache->StartDataCache()) {
        $arCurSection = array();
        if (\Bitrix\Main\Loader::includeModule("iblock")) {
            $dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));
            if (defined("BX_COMP_MANAGED_CACHE")) {
                global $CACHE_MANAGER;
                $CACHE_MANAGER->StartTagCache("/iblock/catalog");

                if ($arCurSection = $dbRes->Fetch())
                    $CACHE_MANAGER->RegisterTag("iblock_id_" . $parameters["IBLOCK_ID"]);

                $CACHE_MANAGER->EndTagCache();
            } else {
                if (!$arCurSection = $dbRes->Fetch())
                    $arCurSection = array();
            }
        }
        $obCache->EndDataCache($arCurSection);
    }
    if (!isset($arCurSection))
        $arCurSection = array();
}

ob_start();
//region Filter
$APPLICATION->IncludeComponent("bitrix:catalog.smart.filter", $template, array(
    "IBLOCK_TYPE" => $parameters["IBLOCK_TYPE"],
    "IBLOCK_ID" => $parameters["IBLOCK_ID"],
    //TODO static parameters
    "SECTION_ID" => $arCurSection['ID'],
    "FILTER_NAME" => $parameters["FILTER_NAME"],
    "PRICE_CODE" => $parameters["PRICE_CODE"],
    "CACHE_TYPE" => $parameters["CACHE_TYPE"],
    "CACHE_TIME" => $parameters["CACHE_TIME"],
    "CACHE_GROUPS" => $parameters["CACHE_GROUPS"],
    "SAVE_IN_SESSION" => "N",
    "FILTER_VIEW_MODE" => $parameters["FILTER_VIEW_MODE"],
    "XML_EXPORT" => "N",
    "SECTION_TITLE" => "NAME",
    "SECTION_DESCRIPTION" => "DESCRIPTION",
    'HIDE_NOT_AVAILABLE' => $parameters["HIDE_NOT_AVAILABLE"],
    "TEMPLATE_THEME" => $parameters["TEMPLATE_THEME"],
    'CONVERT_CURRENCY' => $parameters['CONVERT_CURRENCY'],
    'CURRENCY_ID' => $parameters['CURRENCY_ID'],
    "SEF_MODE" => $parameters["SEF_MODE"],
    //TODO static parameters
    "SEF_RULE" => '/catalog/#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/',
    "SMART_FILTER_PATH" => $parameters["VARIABLES"]["SMART_FILTER_PATH"],
    "PAGER_PARAMS_NAME" => $parameters["PAGER_PARAMS_NAME"],
    "INSTANT_RELOAD" => $parameters["INSTANT_RELOAD"],
),
    null,
    array('HIDE_ICONS' => 'Y')
);
ob_get_contents();
//endregion

$application = Application::getInstance();
$session = $application->getSession();
$context = $application->getContext();
if (!$session->has('initial_load_' . $parameters["FILTER_NAME"] . '_' . $_SESSION['GLOBAL_CURRENT_SECTION']['ID'])) {
    $session->set('initial_load_' . $parameters["FILTER_NAME"] . '_' . $_SESSION['GLOBAL_CURRENT_SECTION']['ID'], true);
}
$sessionFilter = array();
foreach ($_SESSION[$parameters["FILTER_NAME"]][$_SESSION['GLOBAL_CURRENT_SECTION']['ID']] as $key => $value) {
    if (strpos($key, $parameters["FILTER_NAME"]) === 0) {
        $sessionFilter[$key] = $value;
    }
}
$cookie = new  Cookie($parameters["FILTER_NAME"] . '_' . $_SESSION['GLOBAL_CURRENT_SECTION']['ID'], serialize($sessionFilter), time() + 60 * 60 * 24 * 60);
$cookie->setDomain($context->getServer()->getHttpHost());
$cookie->setHttpOnly(false);
$context->getResponse()->addCookie($cookie);
$context->getResponse()->writeHeaders();


$hitRestriction = isset($parameters['GLOBAL_FILTER']['PROPERTY_HIT_VALUE']) ? true : false;
$merchRestriction = $parameters['GLOBAL_FILTER']['!IBLOCK_SECTION_ID'] ?? false;
$parameters['GLOBAL_FILTER'] = $GLOBALS[$parameters["FILTER_NAME"]];
if ($hitRestriction) {
    $parameters['GLOBAL_FILTER']['PROPERTY_HIT_VALUE'] = "да";
}
if ($merchRestriction) {
    $parameters['GLOBAL_FILTER']['!IBLOCK_SECTION_ID'] = $merchRestriction;
}


//enterego static filter for special group
$staticFilter = $request->get('staticFilter');
if (!empty($staticFilter)) {
    $parameters['GLOBAL_FILTER'] = array_merge($parameters['GLOBAL_FILTER'], $staticFilter);
}

$parameters['PAGER_BASE_LINK'] = $_SERVER["HTTP_REFERER"];
$parameters['PAGER_BASE_LINK_ENABLE'] = 'Y';

if (isset($_REQUEST['sort_by'])) {
    $parameters['ELEMENT_SORT_FIELD'] = $request->get('sort_by');
    $parameters['ELEMENT_SORT_ORDER'] = $request->get('sort_order');
    $parameters['ELEMENT_SORT_FIELD2'] = 'NAME';
    $parameters['ELEMENT_SORT_ORDER2'] = 'ASC';
}

$APPLICATION->IncludeComponent(
    'bitrix:catalog.section',
    $template,
    $parameters,
    $parent
);