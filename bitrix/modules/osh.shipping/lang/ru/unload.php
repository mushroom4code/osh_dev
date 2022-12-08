<?
$MESS["OSH_TITLE"] = "Доставки Osh";
$MESS["OSH_DESCRIPTION"] = "Доставки Osh";
$MESS["OSH_ORDERS"] = "Заказы Osh";
$MESS["OSH_COLLECTOR"] = "Заборы посылок";
$MESS["OSH_UNLOAD"] = "Выгрузка данных";
$MESS["OSH_TRACKING"] = "Отслеживание";
$MESS["OSH_SEND_TO"] = "Отправить в Osh";
$MESS["OSH_UPDATE_STATUS"] = "Обновить статус";
$MESS["OSH_SEND_FULLFILL"] = "Отправить в Osh";
$MESS["OSH_DELETE"] = "Удалить посылку из Osh";
$MESS['OSH_LABEL'] = 'Получить штрихкод';
$MESS['OSH_PRINT_LABEL'] = 'Штрихкод #ORDER# отправлен на печать';
$MESS["OSH_ADD_SERVICE_NAME"] = "Услуга доставки посылки";
$MESS["OSH_CARRIAGE_STATUS"] = "Статус посылки в Osh";
$MESS["OSH_ERROR_CANT_UPLOAD_NALOZH_PLATEZH_PS"] = "Не удалось выгрузить не оплаченный заказ, услуга «Наложенный платеж» не подключена для платежной системы заказа!";
$MESS["OSH_ERROR_CANT_UPLOAD_NO_NALOZH_PLATEZH"] = "Не удалось выгрузить не оплаченный заказ, услуга «Наложенный платеж» не подключена!";
$MESS["OSH_FATAL_ERROR_NO_PVZ"] = "Ошбика! В заказе отсутствует ПВЗ!";
$MESS["OSH_FATAL_ERROR_SETTINGS"] = "Ошибка! Не удалось получить настройки Агрегатора служб доставки Osh!";
$MESS["OSH_ERROR_STATUS"] = "Посылка #ID# заказа №#SHIPMENT_NUM# имеет статус «Не отгружено»!";
$MESS["OSH_ERROR_GET_STATUS"] = "Не удалось получить статус посылки #ID# заказа №#SHIPMENT_NUM#: #OSH_MESSAGE#";
$MESS['OSH_ERROR_GET_LABEL'] = 'не удалось получить штрихкод для заказа №#ORDER#: #OSH_MESSAGE#';
$MESS["OSH_ERROR_TRACK_NO"] = "У посылки #ID# заказа №#SHIPMENT_NUM# нет трек-номера!";
$MESS["OSH_ERROR_TRACK_ALREADY"] = "Посылка #ID# заказа №#SHIPMENT_NUM# уже имеет Трек-номер: #TRACK_NUM#";
$MESS["OSH_ERROR_SENT_ALREADY"] = "Посылка #ID# заказа №#SHIPMENT_NUM# уже имеет статус «Отгружено» #TRACK_NUM#";
$MESS["OSH_ERROR_SEND_NOT_ALLOWED"] = "Отправка посылки #ID# заказа №#SHIPMENT_NUM# не разрешена!";
$MESS["OSH_ERROR_SENT_WRONG"] = "Не удалось отправить посылку #ID# заказа №#SHIPMENT_NUM#: #OSH_MESSAGE#";
$MESS["OSH_ERROR_PRODUCT_ADD_WRONG"] = "Не удалось передать товар #NAME# посылки #ID# заказа №#SHIPMENT_NUM#: #OSH_MESSAGE#";
$MESS["OSH_ERROR_PRODUCT_ADD_WRONG_USUAL_SENT"] = "Не удалось передать товары посылки #ID# заказа №#SHIPMENT_NUM#, посылка передана без товаров!";
$MESS["OSH_REMOVE_SUCCESS"] = "Заказ #ORDER# успешно удален из Osh";
$MESS["OSH_REMOVE_ERROR"] = "Не удалось удалить #ORDER#: #OSH_MESSAGE#";
$MESS["OSH_SEND_SUCCESS"] = "Заказ #ORDER# успешно выгружен в Osh";
$MESS["OSH_SEND_SUCCESS_MULTY"] = "Успешно выгружены в Osh заказы: #ORDER_IDS#";
$MESS["OSH_SEND_ERROR_MULTY"] = "Не были выгружны в Osh заказы: #ORDER_IDS#";

$MESS["OSH_BTN_SETTINGS"] = "Настройки";
$MESS["OSH_BTN_SETTINGS_TEXT"] = "Перейти в настройки модуля";

$MESS["OSH_BTN_CREATE_AGENT"] = "Автоматическая проверка и отправка";
$MESS["OSH_BTN_CREATE_AGENT_TEXT"] = "Создать агента для автоматической проверки статусов и отправки заказов";
$MESS["OSH_WAIT"] = "Пожалуйста подождите";
$MESS["OSH_WRONG"] = "Что-то пошло не так!";
$MESS["OSH_AGENT_CREATED"] = "Агент успешно создан! <a href='/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru' target='_blank'>Перейти к агенту</a>";
$MESS["OSH_AGENT_ALREADY_WORKING"] = "Агент уже создан! <a href='/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru' target='_blank'>Перейти к агенту</a>";
$MESS["OSH_AGENT_INACTIVE"] = "Агент создан, но не активен! <a href='/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru' target='_blank'>Перейти к агенту</a>";

$MESS["OSH_BTN_SEND_WARES"] = "Передать товары";
$MESS["OSH_BTN_SEND_WARES_TEXT"] = "Передать все товары в ЛК Osh";
$MESS["OSH_SALE_ORDER_MARKED"] = "С маркировкой";

$MESS["OSH_DELIVERY_TYPE"] = "Тип доставки";
$MESS["OSH_DELIVERY_TYPE_COMMON"] = "Обычные профили";
$MESS["OSH_DELIVERY_TYPE_DIRECT"] = "Сквозные профили";