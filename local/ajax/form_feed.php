<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$NAME = htmlspecialcharsbx($_REQUEST['NAME']);
$MESSAGE = htmlspecialcharsbx($_REQUEST['MESSAGE']);
$PHONE = htmlspecialcharsbx($_REQUEST['PHONE']);

$message = '';
if (!empty($PHONE) && !empty($MESSAGE)) {
    $message .= 'Новое сообщение от <b>' . PHP_EOL . $NAME . '</b>.' . PHP_EOL . PHP_EOL;
    $message .= 'Комментарий пользователя: <b>' . PHP_EOL . $MESSAGE . '</b>' . PHP_EOL . PHP_EOL;
    $message .= 'Номер телефона пользователя: <b>' . PHP_EOL . $PHONE . '</b>.' . PHP_EOL . PHP_EOL;
    $message .= 'Сайт с которого было отправлено сообщение https://' . $_SERVER['HTTP_HOST'] . '/';
    $headers = 'MIME-Version: 1.0' . "\r\n" .
        'From: oshisha.net' . "\r\n" .
        'Reply-To: oshisha.net' . "\r\n" .
        'Content-type: text/html; charset=utf-8' . "\r\n";

    echo mail('tgilmanov@oshisha.net', 'Новый сайт', $message, $headers);
} else {
    echo 'ошибка';
}