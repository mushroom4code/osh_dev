<?php

$MESS['XZAG_TELEGRAM_SETTINGS'] = 'Настройки';
$MESS['XZAG_TELEGRAM_SETTINGS_TITLE'] = 'Настройки API Telegram';
$MESS['XZAG_TELEGRAM_NOTIFICATIONS_SETTINGS'] = 'Уведомления';
$MESS['XZAG_TELEGRAM_NOTIFICATIONS_SETTINGS_TITLE'] = 'Включить/выключить уведомления';
$MESS['XZAG_TELEGRAM_TEMPLATES_SETTINGS'] = 'Шаблоны';
$MESS['XZAG_TELEGRAM_TEMPLATES_SETTINGS_TITLE'] = 'Текст уведомлений';
$MESS['XZAG_TELEGRAM_DEV_SETTINGS'] = 'Расширенные настройки';
$MESS['XZAG_TELEGRAM_DEV_SETTINGS_TITLE'] = 'Дополнительные настройки';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_SAVE'] = 'Сохранить';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_SAVE_TITLE'] = 'Сохранить';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESET'] = 'Сбросить';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESET_TITLE'] = 'Сбросить';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESTORE_DEFAULT'] = 'Вернуть значения по умолчанию';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_RESTORE_DEFAULT_TITLE'] = 'Вернуть значения по умолчанию';

$MESS['XZAG_TELEGRAM_TOKEN'] = 'Токен доступа Телеграм-бота';
$MESS['XZAG_TELEGRAM_CHAT_ID'] = 'ID пользователя, канала или группы';

$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_SALE_ORDER_CREATED'] = 'Новый заказ';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_SALE_ORDER_PAYED'] = 'Заказ оплачен';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_MAIN_USER_REGISTERED'] = 'Регистрация пользователя';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TITLE_FORM_RESULT_CREATED'] = 'Обращение в веб-форму';

$MESS['XZAG_TELEGRAM_DEBUG_TITLE'] = 'Режим отладки';
$MESS['XZAG_TELEGRAM_PROXY_SETTINGS'] = 'Прокси-сервер';
$MESS['XZAG_TELEGRAM_PROXY_SETTINGS_TITLE'] = 'Настройки Прокси-соединения';
$MESS['XZAG_TELEGRAM_PROXY_PROTOCOL'] = 'Протокол прокси-сервера';
$MESS['XZAG_TELEGRAM_PROXY_HOST'] = 'Хост';
$MESS['XZAG_TELEGRAM_PROXY_USERNAME'] = 'Имя пользователя';
$MESS['XZAG_TELEGRAM_PROXY_PASSWORD'] = 'Пароль';
$MESS['XZAG_TELEGRAM_PROXY_ENABLED'] = 'Использовать прокси-сервер';
$MESS['XZAG_TELEGRAM_PROXY_HOST_HINT'] = <<<'TEXT'
Укажите адрес прокси-сервера. Доступные протоколы прокси: <strong>http, https, socks(socks5), socks4</strong>
<br /><br />
Примеры возможных значений поля: <br />
Явное указание протокола: <strong>socks://my-test-proxy.example.com:1234</strong>, <strong>https://123.123.5.6</strong>
<br />
Если протокол не указан, то используется http: <strong>192.168.23.44:8765</strong> <br />
TEXT;

$MESS['XZAG_TELEGRAM_OPTIONS_BTN_TEST_NOTIFICATION'] = 'Отправить тестовое уведомление';
$MESS['XZAG_TELEGRAM_OPTIONS_BTN_TEST_NOTIFICATION_TITLE'] = 'Отправить тестовое уведомление';

$MESS['XZAG_TELEGRAM_TEST_SUCCESS'] = 'Тестовое уведомление отправлено';
$MESS['XZAG_TELEGRAM_TEST_ERROR'] = 'Произошла ошибка при отправке тестового уведомления';
$MESS['XZAG_TELEGRAM_PROXY_NOTE'] = <<<'TEXT'
Проблема с отправкой уведомлений? - Попробуйте использовать прокси
TEXT;

$MESS['XZAG_TELEGRAM_FORM_ERROR'] = 'Не заполнены обязательные поля';
$MESS['XZAG_TELEGRAM_SETTINGS_UPDATE_FAILED'] = 'Ошибка сохранения настроек';
$MESS['XZAG_TELEGRAM_REQUIREMENTS_FAILED'] = 'Для корректной работы модуля нужно устранить неисправности';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TEMPLATE_DEFAULT'] = 'Шаблон по умолчанию';
$MESS['XZAG_TELEGRAM_NOTIFICATION_TEMPLATE_CUSTOM'] = 'Пользовательский шаблон';
$MESS['XZAG_TELEGRAM_SETTINGS_TEMPLATES_ERROR'] = 'Ошибка при сохранении шаблона';
$MESS['XZAG_TELEGRAM_TEMPLATES_HINT'] = <<<'TEXT'
Для формирования текста уведомлений используется шаблонизатор
<a href="https://twig.symfony.com/" target="_blank">Twig</a>. 
Вы можете изменить шаблон уведомлений, исходя из ваших пожеланий. <br />
<p style="text-align: left;">В качестве параметров в шаблоны передаются: </p>
<ul style="text-align: left;">
<li><strong>USER</strong> - пользователь. Массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_help/main/reference/cuser/index.php" 
target="_blank">Bitrix\Main\UserTable</a></li>
<li><strong>ORDER</strong> - заказ. Массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_help/sale/classes/csaleorder/csaleorder__getbyid.5cbe0078.php" 
target="_blank">Bitrix\Sale\Order</a></li>
<li><strong>DELIVERY_METHODS</strong> - список связанных с текущим заказом методов доставки. 
Каждый элемент - массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_help/sale/classes/csaledelivery/csaledelivery__getbyid.d44054be.php" 
target="_blank">Bitrix\Sale\Delivery\Services\Table</a></li>
<li><strong>PAYMENT_METHODS</strong> - список связанных с текущим заказом методов оплаты. 
Каждый элемент - массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_help/sale/classes/csalepaysystem/csalepaysystem__getbyid.c3560000.php" 
target="_blank">Bitrix\Sale\Internals\PaySystemActionTable</a></li>
<li><strong>SHIPMENTS</strong> - список связанных с текущим заказом отгрузок. 
Каждый элемент - массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_d7/bitrix/sale/classes/shipment/index.php" 
target="_blank">Bitrix\Sale\Shipment</a></li>
<li><strong>PAYMENTS</strong> - список связанных с текущим заказом оплат. Каждый элемент - массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_d7/bitrix/sale/classes/payment/index.php" 
target="_blank">Bitrix\Sale\Payment</a></li>
<br />
<li>
<strong>LINK</strong> - ссылка на основной объект уведомления (пользователь или заказ) в административной панели
</li>
<li><strong>SERVER</strong> - текущее окружение. Массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_d7/bitrix/main/server/index.php" target="_blank">Bitrix\Main\Server</a></li>
<li><strong>SITE</strong> - текущий сайт, на котором произошло событие. Массив с данными объекта 
<a href="https://dev.1c-bitrix.ru/api_help/main/reference/csite/index.php" target="_blank">Bitrix\Main\SiteTable</a>
</li>
</ul>   
TEXT;
