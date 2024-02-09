<?php

use Bitrix\Main\Loader;
use Enterego\EnteregoSettings;
use Enterego\UserPrice\UserPriceHelperOsh;

require_once(__DIR__ . '/conf.php');

if (SITE_ID === SITE_EXHIBITION) {
    require_once(__DIR__ . '/redefinedClass/component.php');
}

CModule::IncludeModule("iblock");
define("PROP_STRONG_CODE", 'KREPOST_KALYANNOY_SMESI'); //Свойство для отображения крепости

CModule::AddAutoloadClasses("", array(
    '\Enterego\EnteregoHelper' => '/bitrix/php_interface/enterego_class/EnteregoHelper.php',
    '\PriceList' => '/bitrix/php_interface/enterego_class/PriceList.php',
    '\Enterego\ProductDeactivation' => '/bitrix/php_interface/enterego_class/ProductDeactivation.php',
    'DataBase_like' => '/bitrix/modules/osh.like_favorites/lib/DataBase_like.php',
    '\Bitrix\Like\ORM_like_favoritesTable' => '/bitrix/modules/osh.like_favorites/lib/ORM_like_favoritesTable.php',
    '\Enterego\UserPrice\PluginStatic' => '/bitrix/modules/osh.userprice/include.php',
    '\Enterego\UserPrice\UserPriceHelperOsh' => '/bitrix/modules/osh.userprice/include.php',
    '\Enterego\contragents\EnteregoExchange' => '/bitrix/php_interface/enterego_class/contragents/EnteregoExchange.php',
    '\Enterego\EnteregoBasket' => '/bitrix/php_interface/enterego_class/EnteregoBasket.php',
    '\Enterego\EnteregoProcessing' => '/local/php_interface/include/EnteregoProcessing.php',
    '\Bitrix\Sale\Exchange\EnteregoUserExchange' => '/bitrix/modules/sale/lib/exchange/enteregouserexchange.php',
    '\Enterego\EnteregoGiftHandlers' => '/bitrix/php_interface/enterego_class/EnteregoGiftHandlers.php',
    '\Enterego\EnteregoDiscountHitsSelector' => '/bitrix/php_interface/enterego_class/EnteregoDiscountHitsSelector.php',
    '\Enterego\EnteregoHitsHelper' => '/bitrix/php_interface/enterego_class/EnteregoHitsHelper.php',
    '\CatalogAPIService' => '/local/osh-rest/genaral/CatalogAPIService.php',
    '\Enterego\EnteregoSettings' => '/bitrix/php_interface/enterego_class/EnteregoSettings.php',
    '\Enterego\ProductsSubscriptionsTable' => '/bitrix/php_interface/enterego_class/ProductsSubscriptionsTable.php',
    '\Enterego\EnteregoUser' => '/bitrix/php_interface/enterego_class/EnteregoUser.php',
    '\Enterego\AuthTokenTable' => '/bitrix/php_interface/enterego_class/AuthTokenTable.php',
    '\Enterego\EnteregoBitrix24' => '/bitrix/php_interface/enterego_class/EnteregoBitrix24.php',
    '\Enterego\EnteregoActionDiscountPriceType' =>
        '/bitrix/php_interface/enterego_class/EnteregoActionDiscountPriceType.php',
    '\Enterego\EnteregoGroupedProducts' => '/bitrix/php_interface/enterego_class/EnteregoGroupedProducts.php',
    '\Enterego\ORM\EnteregoORMContragentsTable' => '/bitrix/php_interface/enterego_class/ORM/EnteregoORMContragentsTable.php',
    '\Enterego\ORM\EnteregoORMRelationshipUserContragentsTable' => '/bitrix/php_interface/enterego_class/ORM/EnteregoORMRelationshipUserContragentsTable.php',
    '\Enterego\contragents\EnteregoContragents' => '/bitrix/php_interface/enterego_class/contragents/EnteregoContragents.php',
    '\Enterego\contragents\EnteregoTreatmentContrAgents' => '/bitrix/php_interface/enterego_class/contragents/EnteregoTreatmentContrAgents.php',
));

//redefine sale  basket condition
require_once(__DIR__ . '/enterego_class/sale_cond.php');

define("USE_CUSTOM_SALE_PRICE", EnteregoSettings::getParamOnCheckAndPeriod('USE_CUSTOM_SALE_PRICE', 'activation_price_admin'));
define("CHECKED_INFO", EnteregoSettings::getParamOnCheckAndPeriod('CHECKED_INFO', 'activation_info_admin'));
define("CHECKED_EXHIBITION", EnteregoSettings::getParamOnCheckAndPeriod('CHECKED_EXHIBITION', 'exhibition_info_admin_params'));
// add rest api web hook  - validate products without photo
AddEventHandler('rest', 'OnRestServiceBuildDescription',
    array('CatalogAPIService', 'OnRestServiceBuildDescription'));

require(__DIR__ . '/enterego_class/discountcouponsmanagerbase.php');
require(__DIR__ . '/enterego_class/discountcoupon.php');

global $PRICE_TYPE_ID;
global $UserTypeOpt, $arIskCode, $SETTINGS_;
$arIskCode = array(
    'USE_AVAILABLE', 'BLOG_POST_ID', 'GRAMMOVKA_VES_NETTO'
);

global $option_site;
$option_site = json_decode(\Bitrix\Main\Config\Option::get("BBRAIN", 'SETTINGS_SITE'));


//class used in component files before init autoload files
require_once(__DIR__ . '/enterego_class/EnteregoGiftHandlers.php');
require_once(__DIR__ . '/enterego_class/EnteregoBasket.php');
require_once(__DIR__ . '/enterego_class/modules/update_service_likes.php');
require_once(__DIR__ . '/enterego_class/modules/updateMinSortPrice.php');

const LOCATION_ID = 6;
const STATIC_P = '';
//Типы цен на сайте
const SALE_PRICE_TYPE_ID = 3;
const BASIC_PRICE = 2;
const B2B_PRICE = 9;
const RETAIL_PRICE = 4;

AddEventHandler("main", "OnBuildGlobalMenu", "DoBuildGlobalMenu");

AddEventHandler('sale', 'OnCondSaleActionsControlBuildList',
    ['\Enterego\EnteregoActionDiscountPriceType', 'GetControlDescr']);

AddEventHandler("sale", "onSalePaySystemRestrictionsClassNamesBuildList", "onSalePaySystemRestrictionsClassNamesBuildListHandler");

//платежные системы
function onSalePaySystemRestrictionsClassNamesBuildListHandler()
{
    return new \Bitrix\Main\EventResult(
        \Bitrix\Main\EventResult::SUCCESS,
        array(
            'EnteregoSaleRestrictions' => '/bitrix/php_interface/enterego_class/EnteregoSaleRestrictions.php'
        )
    );
}

function setCurrentPriceId($priceId)
{
    $GLOBALS['PRICE_TYPE_ID'] = $priceId;
}

function getCurrentPriceId()
{
    $currentPrice = $GLOBALS['PRICE_TYPE_ID'] ?? BASIC_PRICE;
    return $currentPrice;
}

// custom admin menu

function DoBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
{

    $aModuleMenu[] = array(
        "parent_menu" => "global_menu_content",
        'menu_id' => 'global_menu_osh',
        'text' => 'Настройки сайта',
        'title' => 'Настройки сайта',
        'sort' => 9,
        'items_id' => 'global_menu_osh',
        'icon' => 'imi_corp',
        'url' => '/bitrix/php_interface/enterego_class/site_options.php?lang=' . LANG,

    );

    $arRes = array(
        "global_menu_enterego" => array(
            "menu_id" => "enterego",
            "page_icon" => "services_page_enterego_icon",
            "index_icon" => "services_page_enterego_icon",
            "text" => "Enterego",
            'section' => 'enterego',
            "title" => "Enterego",
            "sort" => 900,
            "items_id" => "global_menu_enterego",
            "help_section" => "enterego",
            "items" => array(
                array(
                    "parent_menu" => "global_menu_enterego",
                    "icon" => "default_menu_icon",
                    "page_icon" => "default_page_icon",
                    "sort" => "100",
                    "text" => "Черная пятница/Распродажа",
                    "title" => "Черная пятница/Распродажа",
                    "url" => "/bitrix/admin/enterego_admin.php?category=init_sale",
                    "parent_page" => "global_menu_enterego",
                    "more_url" => array(
                        "init_sale.php",
                    ),
                    "items" => array(),
                ),

                array(
                    "parent_menu" => "global_menu_enterego",
                    "icon" => "default_menu_icon",
                    "page_icon" => "default_page_icon",
                    "sort" => "100",
                    "text" => "Свойства товара",
                    "title" => "Свойства товара",
                    "url" => "/bitrix/admin/enterego_admin.php?category=product_prop_setting",
                    "parent_page" => "global_menu_enterego",
                    "more_url" => array(
                        "product_prop_setting.php",
                    ),
                    "items" => array(),
                ),
                array(
                    "parent_menu" => "global_menu_enterego",
                    "icon" => "default_menu_icon",
                    "page_icon" => "default_page_icon",
                    "sort" => "200",
                    "text" => "Прайс-лист",
                    "title" => "Прайс-лист",
                    "url" => "/bitrix/admin/enterego_admin.php?category=priceList",
                    "parent_page" => "global_menu_enterego",
                    "more_url" => array(
                        "priceList.php",
                    ),
                    "items" => array(),
                ),
	              array(
		              "parent_menu" => "global_menu_enterego",
		              "icon" => "default_menu_icon",
		              "page_icon" => "default_page_icon",
		              "sort" => "200",
		              "text" => "Строка Информатор",
		              "title" => "Строка Информатор",
		              "url" => "/bitrix/admin/enterego_admin.php?category=informator",
		              "parent_page" => "global_menu_enterego",
		              "more_url" => array(
			              "informator.php",
		              ),
		              "items" => array(),
	              ),
                array(
                    "parent_menu" => "global_menu_enterego",
                    "icon" => "default_menu_icon",
                    "page_icon" => "default_page_icon",
                    "sort" => "200",
                    "text" => "Выставка",
                    "title" => "Выставка",
                    "url" => "/bitrix/admin/enterego_admin.php?category=exhibition",
                    "parent_page" => "global_menu_enterego",
                    "more_url" => array(
                        "exhibition.php",
                    ),
                    "items" => array(),
                ),
            )
        ),
    );


    return $arRes;
}

class BXConstants
{

    private static $_listPriceType;

    /**
     * @return array|string[]
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\LoaderException
     */
    static function PriceCode(): array
    {
        if (self::$_listPriceType !== null) {
            return self::$_listPriceType;
        }

        $priceTypes = array(
            SALE_PRICE_TYPE_ID => "Сайт скидка",
            B2B_PRICE => "b2b",
        );

        if (Loader::includeModule('osh.userprice')) {
            $priceTypes += UserPriceHelperOsh::getUserPricesForCurrentUser();
        }

        self::$_listPriceType = $priceTypes;
        return self::$_listPriceType;
    }

    static function Shared()
    {
        return array("collections", "vkontakte", "odnoklassniki", "telegram", "twitter", "viber", "whatsapp", "skype");
    }

    public static function isPST()
    {
        return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Lighthouse') !== false;
    }

}


AddEventHandler("main", "OnEndBufferContent", "OnEndBufferContentHandler");
function OnEndBufferContentHandler(&$content)
{
    if (BxConstants::isPST()) {
        $pattern = '/<iframe.*?<\/iframe>/is';
        $content = preg_replace($pattern, '', $content);

        $pattern = '/<script.*?<\/script>/is';
        $content = preg_replace($pattern, '', $content);
    }
}

AddEventHandler("main", "OnBeforeUserLogin", array("CUserEx", "OnBeforeUserLogin"));
AddEventHandler("main", "OnBeforeUserRegister", array("CUserEx", "OnBeforeUserRegister"));
AddEventHandler("main", "OnBeforeUserRegister", array("CUserEx", "OnBeforeUserUpdate"));

class CUserEx
{
    static function OnBeforeUserLogin($arFields)
    {
        $filter = array("EMAIL" => $arFields["LOGIN"]);
        $rsUsers = CUser::GetList(($by = "LAST_NAME"), ($order = "asc"), $filter);
        if ($user = $rsUsers->GetNext())
            $arFields["LOGIN"] = $user["LOGIN"];
        /*else $arFields["LOGIN"] = "";*/
    }

    static function OnBeforeUserRegister(&$arFields)
    {
        $arFields["LOGIN"] = $arFields["EMAIL"];
    }
}

AddEventHandler("sale", "OnOrderSave", "OnOrderAddHandlerSave");
function OnOrderAddHandlerSave($ID, $arFields, $arOrder)
{
    //TODO FIX why fix claddr
    return;
    $order = Bitrix\Sale\Order::load($ID);

    $propertyCollection = $order->getPropertyCollection();

    $FIX_KLADR = '7700000000000';
    $propValueKLADR = $propertyCollection->getItemByOrderPropertyId(34);
    $propValueKLADR->setValue($FIX_KLADR);

    $propValueF = $propertyCollection->getItemByOrderPropertyId(19)->getValue();
    $propValueM = $propertyCollection->getItemByOrderPropertyId(22)->getValue();
    $propValueN = $propertyCollection->getItemByOrderPropertyId(24)->getValue();
    $propValuePropusk = $propertyCollection->getItemByOrderPropertyId(37);
    $ResultPropusk = trim($propValueF . ' ' . $propValueM . ' ' . $propValueN);
    $propValuePropusk->setValue($ResultPropusk);

    $order->save();

    //if( $order->getPersonTypeId() == 1 ){}

}

require_once(__DIR__ . '/enterego_class/EnteregoNewProductAssignment.php');
require_once(__DIR__ . '/enterego_class/EnteregoMakeProductsSubscriptionsReport.php');
/**
 * @return string
 */
function price_list(): string
{
    $new = new PriceList();
    return 'price_list();';
}

/**
 * @param $a
 * @param $b
 * @return int
 */
function sort_by_sort($a, $b): int
{
    if ($a["SORT"] == $b["SORT"]) {
        return 0;
    }
    return ($a["SORT"] < $b["SORT"]) ? -1 : 1;
}


// #000018950
// используем события модуля пикпоинт
AddEventHandler("pickpoint.deliveryservice", "onJSHandlersSet", "onJSHandlersSetHandler");
function onJSHandlersSetHandler(&$arHandlers)
{
    if (array_key_exists('onAfterPostamatSelected', $arHandlers))
        $arHandlers['onAfterPostamatSelected'] = 'PPDSExtension.onAfterPostamatSelectedHandler';
}

AddEventHandler("sale", "OnSaleComponentOrderOneStepPersonType", "setAdditionalPPDSJS");
function setAdditionalPPDSJS(&$arResult, &$arUserResult, $arParams)
{
    global $APPLICATION;
    $jsCode = "
        <script type='text/javascript'>
            var PPDSExtension = {onAfterPostamatSelectedHandler: function(data) {
                if (data.zone != '1') {
                    BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, 'Доставка PickPoint работает только в Москве и Московской области!', true);
                    var adr = $('#soa-property-7'); // TODO - получить из списка св-в
                    adr.val('');
                    adr.attr('readonly', 'readonly');
                    throw new Error('Only Moscow'); // чтобы небыло перезагрузки страницы (sendRequest)
                }
            }};
        </script>
        ";
    $APPLICATION->AddHeadString($jsCode);
}

// JWT-token authorization
addEventHandler('main', 'OnPageStart', ['\Enterego\AuthTokenTable', 'getTokenAuth']);
AddEventHandler('main', 'OnAfterUserAuthorize', ['\Enterego\AuthTokenTable', 'getNewToken']);
AddEventHandler('main', 'OnUserLogout', ['\Enterego\AuthTokenTable', 'removeToken']);

// bitrix24 feedback and callback integrations
AddEventHandler('iblock', 'OnAfterIBlockElementAdd', ['\Enterego\EnteregoBitrix24', 'sendToBitrix24']);