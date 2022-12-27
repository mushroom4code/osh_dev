<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$NAME = htmlspecialcharsbx($_REQUEST['NAME']);
$MESSAGE = htmlspecialcharsbx($_REQUEST['MESSAGE']);
$PHONE = htmlspecialcharsbx($_REQUEST['PHONE']);

$message = '';
if (!empty($PHONE) && !empty($MESSAGE)) {
    $message .= 'Новое сообщение от ' . PHP_EOL . $NAME . PHP_EOL . PHP_EOL;
    $message .= 'Комментарий пользователя: ' . PHP_EOL . $MESSAGE . PHP_EOL . PHP_EOL;
    $message .= 'Номер телефона пользователя: ' . PHP_EOL . $PHONE . PHP_EOL . PHP_EOL;
    $message .= 'Сайт с которого было отправлено сообщение https://' . $_SERVER['HTTP_HOST'] . '/';

    echo mail('rodionova@enterego.ru', 'Новый сайт', $message);
} else {
    echo 'ошибка';
}