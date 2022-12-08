<?php
// Error messages
$MESS['PP_MODULE_OPTIONS_NOT_SET'] = 'Внимание!';
$MESS['PP_MODULE_OPTIONS_NOT_SET_TEXT'] = '<p><b>Модуль не настроен. Отправка заказов в PickPoint недоступна.</b></p><p>Перейдите на <a href="/bitrix/admin/settings.php?lang=ru&mid=pickpoint.deliveryservice&mid_menu=1">страницу модуля</a> для его настройки.</p>';

$MESS['PICKPOINT_DELIVERYSERVICE_ADMIN_EXPORT_TITLE'] = 'Экспорт заказов';

$MESS['PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_LAST_SYNC_TEXT'] = 'Последняя синхронизация статусов:';
$MESS['PICKPOINT_DELIVERYSERVICE_EXPORT_STATUS_SYNC_DISABLED'] = '<b>Внимание!</b> Синхронизация статусов заказов отключена. <br />Проверьте настройки модуля "Интеграция с PickPoint", раздел "Статусы".';

$MESS['PICKPOINT_DELIVERYSERVICE_EXPORT_ACCESSED_COST'] = 'Оценочная стоимость';
$MESS['PICKPOINT_DELIVERYSERVICE_HELPER_export_accessedCost'] = 'От оценочной стоимости зависит страховка заказа.<br /><br /><b>Внимание!</b> Подставляемое по умолчанию значение зависит от опции модуля "Передавать оценочную стоимость заказа".<br /><br />Если необходимо изменить ОЦ перед экспортом, используйте редактирование заказа через "Изменить заказ".';

$MESS['PICKPOINT_DELIVERYSERVICE_EXPORT_GETTING_TYPE'] = 'Вид приема';
$MESS['PICKPOINT_DELIVERYSERVICE_HELPER_export_gettingType'] = 'Выберите желаемый вид сдачи отправления в компанию PickPoint.<br /><br /><b>Внимание!</b> Доступные для выбора варианты зависят от опции модуля "Допустимые виды сдачи отправлений".';

$MESS['PICKPOINT_DELIVERYSERVICE_EXPORT_ACTION_GETBARCODES'] = 'Этикетка с штрихкодом';
$MESS['PICKPOINT_DELIVERYSERVICE_HELPER_export_actionGetBarcodes'] = '<b>Внимание!</b> Получение этикеток возможно только при статусах заказа 101, 102, 103 или 104.<br /><br />Если для текущего статуса заказа получение этикетки невозможно, кнопка получения этикетки будет скрыта.';
$MESS['PICKPOINT_DELIVERYSERVICE_GETBARCODES_LINK'] = 'Получить';
$MESS['PICKPOINT_DELIVERYSERVICE_GETBARCODES_ERROR'] = 'Ошибка: не удалось получить ссылку на файл этикетки.';

$MESS['PP_EXPORT'] = 'Экспорт заказов';
$MESS['PP_EXPORT_BUTTON'] = 'Отправить';
$MESS['PP_UPDATE_BUTTON'] = 'Обновить статусы';
$MESS['PP_SHOW_ORDER'] = 'Выводить по:';
$MESS['PP_SAVE'] = 'Сохранить';
$MESS['PP_ORDER_NUMBER'] = '№ заказа';
$MESS['PP_PAYED_BY_PP'] = 'Оплата через PickPoint';
$MESS['PP_SHOW_MORE'] = 'Показать все заказы';
$MESS['PP_ADDRESS'] = 'Пункт выдачи';
$MESS['PP_SERVICE_TYPE'] = 'Тип услуги';

$MESS['PP_ARCHIVE_ADD'] = 'В архив';
$MESS['PP_ARCHIVE_DELETE'] = 'Удалить из архива';
$MESS['PP_STATUS'] = 'Статус';
$MESS['PP_ACTION_CANCEL'] = 'Удалить заказ';
$MESS['PP_ACTION_EDIT'] = 'Изменить заказ';
$MESS['PP_CANCEL_NOT_EXPORT'] = 'Заказ еще не экспортирован';
$MESS['PP_CANCEL_SUCCESS'] = 'Заказ отменен';
$MESS['PP_SIZE'] = 'Габариты';
$MESS['PP_FROM'] = 'от';
$MESS['PP_NO'] = 'нет';
$MESS['PP_ALL'] = 'Все';
$MESS['PP_SUMM'] = 'Сумма';
$MESS['PP_N'] = '№';
$MESS['NO_ORDER'] = 'Не найден заказ №#ORDER_ID#';
$MESS['PP_PRICE_ERROR'] = 'Заказ №#ORDER_ID#: оплата через PickPoint должна быть более 0 и не более #PRICE# руб.';
$MESS['PP_NEW_INVOICE'] = 'Отправление создано';
$MESS['PP_SAVE_SETTINGS'] = 'Настройки сохранены';
$MESS['PP_UPDATED'] = 'Заказы обновлены';
$MESS['PP_DELETE'] = 'Заказы удалены';
$MESS['PP_ARCHIVE'] = 'Заказы перемещены в архив';
$MESS['PP_FROMARCHIVE'] = 'Заказы извлечены из архива';
$MESS['PP_INVOICE_ID'] = 'Номер отправления в PickPoint';
$MESS['PP_EDIT_LINK'] = 'Изменить';
$MESS['PP_WIDTH'] = 'Ширина';
$MESS['PP_HEIGHT'] = 'Высота';
$MESS['PP_DEPTH'] = 'Глубина';
$MESS['PP_EXPORT_NEW'] = 'Новые заказы';
$MESS['PP_EXPORT_REVERT'] = 'Заказы на возврат';
$MESS['PP_EXPORT_FORWARDED'] = 'Отправленные заказы';
$MESS['PP_EXPORT_READY'] = 'Готовые заказы';
$MESS['PP_EXPORT_CANCELED'] = 'Отмененные заказы';
$MESS['PP_EXPORT_ARCHIVE'] = 'Архив заказов';
$MESS['PP_ERROR_IN_ORDER'] = 'Ошибка в заказе №';
$MESS['PP_ORDER_WITH_NUMBER'] = 'Заказ №';
$MESS['PP_ORDER_WITH_NUMBER_IS_SUCCESS'] = ' успешно отправлен.';