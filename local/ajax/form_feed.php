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
            'USER_FILES' => $_FILES,
        ],
    ];
    $elemId = $el->add($arElement);

    // Получение свойств елемента ИБ
    $elemProps = getElemProps(IBLOCK_NEW_SITE_COMMENTS, $elemId);
    // Формирование массива ИД файлов для аттача в емейл
    $filesForEmail = getFilesIds($elemProps, 'USER_FILES');

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
        'duplicate' => 'Y',
        '',
        'FILE' => $filesForEmail
    ));
    echo 1;
} else {
    echo 'ошибка';
}


// Метод получает значения полей елемента
function getElemProps($iblockID, $elementID) {
    $result = [];
    $rsProps = CIBlockElement::GetProperty($iblockID, $elementID, [], []);
    while($arrProps = $rsProps->Fetch()) {
        if ($arrProps['PROPERTY_TYPE'] == 'F') {
            $result[$arrProps['CODE']][] = [
                'id' =>$arrProps['VALUE'],
                'path' => CFile::GetPath($arrProps['VALUE'])
            ];
        } else {
            $result[$arrProps['CODE']][] = $arrProps['VALUE'];
        }
    }
    foreach ($result as $name => $value) {
        if (count($value) == 1) {
            $result[$name] = $value[0];
        }
    }
    return $result;
}

// Возвращает массив ИД файлов
function getFilesIds($props, $code) {
    $ids = [];

    foreach($props[$code] as $file) {
        $ids[] = $file['id'];
    }

    return $ids;
}
