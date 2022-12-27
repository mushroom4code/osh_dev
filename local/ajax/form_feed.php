<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$NAME = htmlspecialcharsbx($_REQUEST['NAME']);
$MESSAGE = htmlspecialcharsbx($_REQUEST['MESSAGE']);
$PHONE = htmlspecialcharsbx($_REQUEST['PHONE']);

use Bitrix\Main\Mail\Event;
$message = '';
if (!empty($PHONE) && !empty($MESSAGE)) {
    $message .= 'Новое сообщение от' . PHP_EOL . $NAME  . PHP_EOL . PHP_EOL;
    $message .= 'Комментарий пользователя:' . PHP_EOL . $MESSAGE  . PHP_EOL . PHP_EOL;
    $message .= 'Номер телефона пользователя:' . PHP_EOL . $PHONE . PHP_EOL . PHP_EOL;
    $message .= 'Сайт с которого было отправлено сообщение https://' . $_SERVER['HTTP_HOST'] . '/';

    Event::send(array(
        "EVENT_NAME" => "FEEDBACK_FORM",
        "LID" => "s1",
        "C_FIELDS" => array(
            "AUTHOR" => $NAME,
            "AUTHOR_PHONE" => $PHONE,
            "TEXT" => $MESSAGE,
        ),
    ));
} else {
    echo 'ошибка';
}