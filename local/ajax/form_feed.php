<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$NAME = htmlspecialcharsbx($_REQUEST['NAME']);
$MESSAGE = htmlspecialcharsbx($_REQUEST['MESSAGE']);
$PHONE = htmlspecialcharsbx($_REQUEST['PHONE']);

use Bitrix\Main\Mail\Event;
$message = '';
if (!empty($PHONE) && !empty($MESSAGE)) {
    $message .= 'Новое сообщение от <b>' . PHP_EOL . $NAME . '</b>.' . PHP_EOL . PHP_EOL;
    $message .= 'Комментарий пользователя: <b>' . PHP_EOL . $MESSAGE . '</b>' . PHP_EOL . PHP_EOL;
    $message .= 'Номер телефона пользователя: <b>' . PHP_EOL . $PHONE . '</b>.' . PHP_EOL . PHP_EOL;
    $message .= 'Сайт с которого было отправлено сообщение https://' . $_SERVER['HTTP_HOST'] . '/';

    Event::send(array(
        "EVENT_NAME" => "FEEDBACK_FORM",
        "LID" => "s1",
        "C_FIELDS" => array(
            "EMAIL" => "rodionova@enterego.ru",
            "AUTHOR" => $NAME,
            "AUTHOR_PHONE" => $PHONE,
            "TEXT" => $MESSAGE,
            "USER_ID" => 42
        ),
    ));

    echo mail('rodionova@enterego.ru', 'Новый сайт', $message);
} else {
    echo 'ошибка';
}