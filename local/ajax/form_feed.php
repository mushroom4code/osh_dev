<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Mail\Event;
use Bitrix\Main\Loader;
use B01110011ReCaptcha\BitrixCaptcha;

CModule::IncludeModule("iblock");
Loader::includeModule('main');

$NAME = htmlspecialcharsbx($_REQUEST['NAME']);
$MESSAGE = htmlspecialcharsbx($_REQUEST['MESSAGE']);
$PHONE = htmlspecialcharsbx($_REQUEST['PHONE']);
$message = '';

if (class_exists('B01110011ReCaptcha\BitrixCaptcha')) {
    $res = BitrixCaptcha::checkSpam();
    if ($res === false) {
        echo 'Ошибка CAPTCHA';
        die;
    }
}

if (!empty($PHONE) && !empty($MESSAGE)) {

    // Создание элемента в ИБ
    $el = new CIBlockElement();
    $arElement = [
        'ACTIVE' => 'Y',
        'IBLOCK_ID' => IBLOCK_NEW_SITE_COMMENTS,
        'NAME' => $NAME,
        'DETAIL_TEXT' => $MESSAGE,
        'PROPERTY_VALUES' => [
            'USER_NAME' => $NAME,
            'PHONE' => $PHONE,
            'USER_FILES' => !empty($_FILES) ? $_FILES : []
        ],
    ];
    $elemId = $el->add($arElement);

    // Получение ИД изображений елемента ИБ
    if (!empty($_FILES)) {
        $elemFiles = [];
        $tmpProps = CIBlockElement::GetProperty(
            IBLOCK_NEW_SITE_COMMENTS,
            $elemId,
            [],
            ['CODE' => 'USER_FILES']
        );

        while ($arrProps = $tmpProps->Fetch()) {
            $elemFiles[] = $arrProps['VALUE'];
        }
    }

    $message .= 'Новое сообщение от' . PHP_EOL . $NAME  . PHP_EOL . PHP_EOL;
    $message .= 'Комментарий пользователя:' . PHP_EOL . $MESSAGE  . PHP_EOL . PHP_EOL;
    $message .= 'Номер телефона пользователя:' . PHP_EOL . $PHONE . PHP_EOL . PHP_EOL;
    $message .= 'Сайт с которого было отправлено сообщение https://' . $_SERVER['HTTP_HOST'] . '/';

    Event::send(array(
        "EVENT_NAME" => "FEEDBACK_FORM",
        "LID" => SITE_ID,
        "C_FIELDS" => array(
            "AUTHOR" => $NAME,
            "AUTHOR_PHONE" => $PHONE,
            "TEXT" => $MESSAGE,
        ),
        'duplicate' => 'Y',
        '',
        'FILE' => !empty($_FILES) ? $elemFiles : []
    ));
    echo 1;
} else {
    echo 'ошибка';
}
