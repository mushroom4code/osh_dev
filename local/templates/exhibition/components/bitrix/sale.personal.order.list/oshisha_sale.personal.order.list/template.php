<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/script.js");
Asset::getInstance()->addCss("/bitrix/components/bitrix/sale.order.payment.change/templates/bootstrap_v4/style.css");
CJSCore::Init(array('clipboard', 'fx'));

Loc::loadMessages(__FILE__);


/**
 * @param array $filter
 * @return array
 */
global $USER;
$textError = '<div>Заказов, соответсвующих выбранной категории нет</div>';
function get_orders(array $filter = []): array
{

    $listOrders = [];

    $products = CSaleOrder::GetList(array('ID' => 'ASC'), $filter);

    while ($res = $products->Fetch()) {
        $listOrders[] = $res;
    }

    foreach ($listOrders as &$itemOrder) {
        $itemOrder['IS_NOT_ACTIVE_ITEMS_PRESENT'] = false;
        $ordersBasket = CSaleBasket::GetList(array(), array('ORDER_ID' => $itemOrder['ID']), false, ['nTopCount' => 5]);
        if (!empty($ordersBasket)) {
            while ($result = $ordersBasket->Fetch()) {
                $my_elements = CIBlockElement::GetList(
                    array(),
                    array("ID" => $result['PRODUCT_ID']),
                    false,
                    false,
                    array('ID', 'NAME', 'ACTIVE','DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
                );
                $ar_fields = $my_elements->GetNext();
                if($ar_fields['ACTIVE'] == 'N') {
                    $itemOrder['IS_NOT_ACTIVE_ITEMS_PRESENT'] = true;
                }
                $itemOrder['PICTURE'][] = CFile::GetPath($ar_fields['PREVIEW_PICTURE']);
            }
        }
    }
    return $listOrders;
}

require_once('show_order_block.php');


if (!empty($arResult['ERRORS']['FATAL'])) {
    foreach ($arResult['ERRORS']['FATAL'] as $code => $error) {
        if ($code !== $component::E_NOT_AUTHORIZED)
            ShowError($error);
    }
    $component = $this->__component;
    if ($arParams['AUTH_FORM_IN_TEMPLATE'] && isset($arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED])) {
        ?>
        <div class="row">
            <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                <div class="alert alert-danger"><?= $arResult['ERRORS']['FATAL'][$component::E_NOT_AUTHORIZED] ?></div>
            </div>
            <? $authListGetParams = array();?>
            <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                <? $APPLICATION->AuthForm('', false, false, 'N', false); ?>
            </div>
        </div>
        <?
    }
} else {
    $listOrders = [];
    $filter = array('USER_ID' => $USER->GetID());

    if ($_REQUEST["filter_history"] !== 'Y' && $_REQUEST["show_canceled"] !== 'Y' && $_REQUEST['show_delivery'] !== 'Y') {
        $filter = array('USER_ID' => $USER->GetID(), 'STATUS_ID' => ['N', 'P']);
    } else if ($_REQUEST['show_canceled'] === 'Y') {
        $filter = array('STATUS_ID' => "F", 'USER_ID' => $USER->GetID());

    } else if ($_REQUEST['show_delivery'] === 'Y') {
        $filter = array('RESERVED' => "Y", 'USER_ID' => $USER->GetID());
    }
    $filter['LID']= SITE_EXHIBITION;
    $listOrders = get_orders($filter);

    if (count($listOrders) === 0) {
        echo $textError;
        return;
    }

    showOrderBlock($arResult['INFO'], $listOrders);
}
?>