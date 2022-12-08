<?
$osh_shipping_default_option = array(
//    'osh_adminApiKey' => '',
//    'osh_publicApiKey' => '',
//    'osh_apiUrl' => 'http://api.osh.ru',
//    'osh_syncAll' => '0',
    'osh_checkout' => SITE_DIR.'personal/order/make/',
//    'osh_ok' => '0',
//    'osh_debug' => '0',
//    'osh_include_yamaps' => "1",
//    'osh_pvzStrict' => "N",
//    'osh_productArticle' => "ID",
//    'osh_xmlIdComplex' => '1',
//    'osh_articleProperty' => '',
//    'osh_cashPaymentsIds' => "1",
//    'osh_direct' => '0',
//    'osh_departure_type' => 'man',
//    'osh_rounding_type' => 'n',
//    'osh_rounding_precision' => 0.01,
//    'osh_check_pvz' => '1',
//    'osh_cache_mec' => 'nat',
//    'osh_is_pvz_haunt' => '1',
//    'osh_is_date_time_mirror' => '0',
//    'osh_default_stock' => '0',
//    'osh_deduct' => '1',
//    'osh_quantity_override' => '0'
);
$osh_shipping_default_option['osh_checkout'] = (strpos($_SERVER['REQUEST_URI'], '/mobileapp/') !== false) ? SITE_DIR.'mobileapp/personal/order/make/' : SITE_DIR.'personal/order/make/';