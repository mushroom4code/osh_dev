<?php

$MESS['XZAG_TELEGRAM_SETTINGS'] = 'Settings';
$MESS['XZAG_TELEGRAM_SETTINGS_TITLE'] = 'Telegram API Settings';
$MESS['XZAG_TELEGRAM_NOTIFICATIONS_SETTINGS'] = 'Notifications';
$MESS['XZAG_TELEGRAM_NOTIFICATIONS_SETTINGS_TITLE'] = 'Toggle notifications';
$MESS['XZAG_TELEGRAM_TEMPLATES_SETTINGS'] = 'Templates';
$MESS['XZAG_TELEGRAM_TEMPLATES_SETTINGS_TITLE'] = 'Notifications templates';
$MESS['XZAG_TELEGRAM_DEV_SETTINGS'] = 'Advanced settings';
$MESS['XZAG_TELEGRAM_DEV_SETTINGS_TITLE'] = 'Advanced settings';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_SAVE'] = 'Save';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_SAVE_TITLE'] = 'Save';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESET'] = 'Reset';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESET_TITLE'] = 'Reset';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESTORE_DEFAULT'] = 'Restore default';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESTORE_DEFAULT_TITLE'] = 'Restore default';

$MESS['XZAG_TELEGRAM_TOKEN'] = 'Telegram BOT access token';
$MESS['XZAG_TELEGRAM_CHAT_ID'] = 'Chat ID';

$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_SALE_ORDER_CREATED'] = 'New order';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_SALE_ORDER_PAYED'] = 'Order payed';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_MAIN_USER_REGISTERED'] = 'New signup';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_FORM_RESULT_CREATED'] = 'New form response';

$MESS['XZAG_TELEGRAM_DEBUG_TITLE'] = 'Debug mode';
$MESS['XZAG_TELEGRAM_PROXY_SETTINGS'] = 'Proxy';
$MESS['XZAG_TELEGRAM_PROXY_SETTINGS_TITLE'] = 'Proxy settings';
$MESS['XZAG_TELEGRAM_PROXY_PROTOCOL'] = 'Protocol';
$MESS['XZAG_TELEGRAM_PROXY_HOST'] = 'Host';
$MESS['XZAG_TELEGRAM_PROXY_USERNAME'] = 'Username';
$MESS['XZAG_TELEGRAM_PROXY_PASSWORD'] = 'Password';
$MESS['XZAG_TELEGRAM_PROXY_ENABLED'] = 'Enable proxy';
$MESS['XZAG_TELEGRAM_PROXY_HOST_HINT'] = <<<'TEXT'
Specify proxy host. Available protocols are: <strong>http, https, socks(socks5), socks4</strong>
<br /><br />
Examples: <br />
With protocol: <strong>socks://my-test-proxy.example.com:1234</strong>, <strong>https://123.123.5.6</strong><br />
Without protocol (assumed http): <strong>192.168.23.44:8765</strong> <br />
TEXT;

$MESS['XZAG_TELEGRAM_OPTIONS_BTN_TEST_NOTIFICATION'] = 'Send test notification';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_TEST_NOTIFICATION_TITLE'] = 'Send test notification';

$MESS['XZAG_TELEGRAM_TEST_SUCCESS'] = 'Test notification successfully sent';
$MESS['XZAG_TELEGRAM_TEST_ERROR'] = 'Failed while sending test notification';
$MESS['XZAG_TELEGRAM_PROXY_NOTE'] = <<<'TEXT'
If you encountering problems with notifications you may want to use proxy. 
TEXT;

$MESS['XZAG_TELEGRAM_FORM_ERROR'] = 'Required fields are empty';
$MESS['XZAG_TELEGRAM_SETTINGS_UPDATE_FAILED'] = 'Settings update failed';
$MESS['XZAG_TELEGRAM_REQUIREMENTS_FAILED'] = 'Module requirements are not met';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TEMPLATE_DEFAULT'] = 'Default template';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TEMPLATE_CUSTOM'] = 'Custom template';
$MESS['XZAG_TELEGRAM_SETTINGS_TEMPLATES_ERROR'] = 'Failed while saving template';
$MESS['XZAG_TELEGRAM_TEMPLATES_HINT'] = <<<'TEXT'
<a href="https://twig.symfony.com/" target="_blank">Twig</a> is used to render templates. 
You can customize notification templates. <br />
<p style="text-align: left;">Available params: </p>
<ul style="text-align: left;">
<li>
    <strong>USER</strong> - user. Array of fields of 
    <a 
        href="https://dev.1c-bitrix.ru/api_help/main/reference/cuser/index.php" 
        target="_blank">Bitrix\Main\UserTable
    </a>
</li>
<li><strong>ORDER</strong> - order. Array of fields of 
    <a 
        href="https://dev.1c-bitrix.ru/api_help/sale/classes/csaleorder/csaleorder__getbyid.5cbe0078.php" 
        target="_blank">Bitrix\Sale\Order
    </a>
</li>
<li>
    <strong>DELIVERY_METHODS</strong>
     - list of delivery services linked to current order. Each item - array of fields of 
    <a 
        href="https://dev.1c-bitrix.ru/api_help/sale/classes/csaledelivery/csaledelivery__getbyid.d44054be.php" 
        target="_blank">Bitrix\Sale\Delivery\Services\Table
    </a>
</li>
<li>
    <strong>PAYMENT_METHODS</strong> - list of payment systems linked to current order. Each item - array of fields of 
    <a 
        href="https://dev.1c-bitrix.ru/api_help/sale/classes/csalepaysystem/csalepaysystem__getbyid.c3560000.php" 
        target="_blank">Bitrix\Sale\Internals\PaySystemActionTable
    </a>
</li>
<li>
    <strong>SHIPMENTS</strong> - list of shipments for current order. Each item - array of fields of 
    <a 
        href="https://dev.1c-bitrix.ru/api_d7/bitrix/sale/classes/shipment/index.php" 
        target="_blank">Bitrix\Sale\Shipment
    </a>
</li>
<li>
    <strong>PAYMENTS</strong> - list of payments for current order. Each item - array of fields of 
    <a href="https://dev.1c-bitrix.ru/api_d7/bitrix/sale/classes/payment/index.php" target="_blank">
    Bitrix\Sale\Payment
    </a>
</li>
<br />
<li><strong>LINK</strong> - admin link to the main notification object (user or order)</li>
<li><strong>SERVER</strong> - current server environment. Array of fields of 
<a href="https://dev.1c-bitrix.ru/api_d7/bitrix/main/server/index.php" target="_blank">Bitrix\Main\Server</a></li>
<li>
    <strong>SITE</strong> - site where notification is from. Array of fields of 
    <a href="https://dev.1c-bitrix.ru/api_help/main/reference/csite/index.php" target="_blank">
    Bitrix\Main\SiteTable
    </a>
</li>
</ul>   
TEXT;
