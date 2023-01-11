<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


use B01110011ReCaptcha\BitrixCaptcha;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Xzag\Telegram\Container;
use Xzag\Telegram\Data\ProxySettings;
use Xzag\Telegram\Service\Notification\TelegramNotification;
use Xzag\Telegram\Data\SettingsForm;
use Xzag\Telegram\Service\NotificationService;
use Xzag\Telegram\Exception\SendException;
use Xzag\Telegram\Event\SampleEvent;

CModule::IncludeModule("iblock");
Loader::includeModule('main');

$NAME = htmlspecialcharsbx($_REQUEST['NAME']);
$MESSAGE = htmlspecialcharsbx($_REQUEST['MESSAGE']);
$PHONE = htmlspecialcharsbx($_REQUEST['PHONE']);
$EMAIL = htmlspecialcharsbx($_REQUEST['EMAIL']);
$message = '';

if (class_exists('B01110011ReCaptcha\BitrixCaptcha')) {
    $res = BitrixCaptcha::checkSpam();
    if ($res === false) {
        echo 'Ошибка CAPTCHA';
        die;
    }
}

if ($NAME != '' && $PHONE != '') {
    $MESAGE_EMAIL = '
		Имя: ' . $NAME . '<br>
		Телефон: ' . $PHONE . '<br>
		EMAIL: ' . $EMAIL . '<br>
		Сообщение: ' . $MESSAGE . '<br>';

    if (!empty($PHONE)) {
        $arEventFields = array(
            'MESAGE' => $MESAGE_EMAIL,
            'EMAIL' => $EMAIL,
            'TITLE' => 'Ваше сообщение принято',
            'PHONE' => $PHONE,
            'MESSAGE' => $MESSAGE,
            'NAME' => $NAME,
        );
        $moduleId = 'xzag.telegram';
        if (Loader::includeModule($moduleId)) {

            $response = Context::getCurrent()->getResponse();
            $response->addHeader('Content-Type', 'application/json');
            $set = new ProxySettings();
            try {
                $newSet = SettingsForm::make([
                    'token' => COption::GetOptionString($moduleId, 'token'),
                    'chat_id' => COption::GetOptionString($moduleId, 'chat_id')]
                    ?? []);
                $notification = (new TelegramNotification(COption::GetOptionString($moduleId, 'token')))
                    ->to(COption::GetOptionString($moduleId, 'chat_id'));

                /**
                 * @var $notificator NotificationService
                 */
                $notificator = Container::get(NotificationService::class);

                $sampleEvent = SampleEvent::make([
                    'CHAT_ID' => COption::GetOptionString($moduleId, 'chat_id'),
                    'PROXY' => $set->getDSN(),
                ]);
                //Шаблон сообщения
                $message .= 'Новое уведомление от <b>' . PHP_EOL . $NAME . '</b>.' . PHP_EOL . PHP_EOL;
                $message .= 'Комментарий пользователя: <b>' . PHP_EOL . $MESSAGE . '</b>' . PHP_EOL . PHP_EOL;
                $message .= 'Номер телефона пользователя: <b>' . PHP_EOL . $PHONE . '</b>.' . PHP_EOL . PHP_EOL;
                $message .= 'Сайт с которого было отправлено сообщение https://' . $_SERVER['HTTP_HOST'] . '/';
                $messages = $sampleEvent->convertNew($message);

                $notificator->with($notification)->send($messages);


            } catch (SendException $e) {
                echo 'Ошибка отправки сообщения из-за некорректных настроек модуля';
            } catch (\Throwable $e) { //print_r($e);
                echo 'Ошибка отправки сообщения';
            }
        }

        //CEvent::SendImmediate("SEND", SITE_ID, $arEventFields);
        //CEvent::SendImmediate("SEND_USER", SITE_ID, $arEventFields);
        echo 1;
    }
} else {
    echo 'Ошибка';
}
