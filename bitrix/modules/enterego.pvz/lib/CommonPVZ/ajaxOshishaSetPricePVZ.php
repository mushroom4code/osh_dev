<?
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!Bitrix\Main\Loader::includeModule('enterego.pvz'))
    return;

use Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Web\Json,
    \CommonPVZ\OshishaSavedDeliveriesTable;

$oRequest = Context::getCurrent()->getRequest();

Loc::loadMessages(__FILE__);

if($_SERVER["REQUEST_METHOD"] == 'POST' && check_bitrix_sessid() && !empty($oRequest->getPost('address')) && (!empty($oRequest->getPost('price')) || $oRequest->getPost('price') == 0)){

    $_SESSION['Osh']['delivery_address_info']['address'] = $oRequest->getPost('address');
    $_SESSION['Osh']['delivery_address_info']['price'] = $oRequest->getPost('price');
    $_SESSION['Osh']['delivery_address_info']['delivery_id'] = $oRequest->getPost('delivery_id');
    $_SESSION['Osh']['delivery_address_info']['distance'] = $oRequest->getPost('distance');
    $_SESSION['Osh']['delivery_address_info']['no_markup'] = $oRequest->getPost('no_markup');
    $_SESSION['Osh']['delivery_address_info']['latitude'] = $oRequest->getPost('latitude');
    $_SESSION['Osh']['delivery_address_info']['longitude'] = $oRequest->getPost('longitude');
    $_SESSION['Osh']['delivery_address_info']['zone'] = $oRequest->getPost('zone');

    $dbResultError = false;
    if (!OshishaSavedDeliveriesTable::getRow(array('filter' => array('LATITUDE' => $oRequest->getPost('latitude'),
        'LONGITUDE' => $oRequest->getPost('longitude'))))) {
        $result = OshishaSavedDeliveriesTable::add(array('fields' => array(
            'LATITUDE' => $oRequest->getPost('latitude'),
            'LONGITUDE' => $oRequest->getPost('longitude'),
            'ZONE' => $oRequest->getPost('zone'),
            'DISTANCE' => $oRequest->getPost('distance')
        )));
        if (!$result->isSuccess()) {
            $dbResultError = true;
        }
    }
    if ($dbResultError) {
        $arResult['success'] = false;
        $arResult['message'] = Loc::getMessage("ST_OSH_SELECT_PVZ_FAIL");
    } else {
        $arResult['success'] = true;
        $arResult['message'] = Loc::getMessage("ST_OSH_SELECT_PVZ_SUCCESS", array("#delivery_address#" => $oRequest->getPost('address')));
    }
}else{
    $arResult['success'] = false;
    $arResult['message'] = Loc::getMessage("ST_OSH_SELECT_PVZ_FAIL");
}
die(Json::encode($arResult));