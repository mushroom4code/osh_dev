<?php
define('BX_SESSION_ID_CHANGE', false);
define('BX_SKIP_POST_UNQUOTE', true);
define('NO_AGENT_CHECK', true);
define("STATISTIC_SKIP_ACTIVITY_CHECK", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $USER;

if (!$USER->IsAuthorized()) {
    exit("not auth");
} else {
    $act = $_POST['action'];
    if ($act === 'SetParamSale') {
        COption::SetOptionString('activation_price_admin', 'USE_CUSTOM_SALE_PRICE', $_POST['param']);
        if (!empty($_POST['date_start']) && !empty($_POST['date_end'])) {
            COption::SetOptionString('activation_price_admin', 'PERIOD',
                json_encode(['start' => $_POST['date_start'], 'end' => $_POST['date_end']]));
        }
        exit($_POST['param']);//управление использования специального вида цен для скидок
    } elseif ($act === 'SetParamPriceList') {
        COption::SetOptionString('priceList_xlsx', 'priceListArrayCustom', $_POST['param']);
    } else {
        exit('not correct request');
    }

}