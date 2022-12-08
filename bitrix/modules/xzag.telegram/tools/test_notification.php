<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Xzag\Telegram\Container;
use Xzag\Telegram\Service\Notification\TelegramNotification;
use Xzag\Telegram\Data\SettingsForm;
use Xzag\Telegram\Service\NotificationService;
use Xzag\Telegram\Exception\SendException;
use Bitrix\Main\Web\Json;
use Xzag\Telegram\Event\SampleEvent;

$webRoot = Loader::getDocumentRoot() . DIRECTORY_SEPARATOR;
require_once($webRoot . 'bitrix/modules/main/include/prolog_admin_before.php');

$moduleId = 'xzag.telegram';
$moduleAccessLevel = $APPLICATION->GetGroupRight($moduleId);
if ($moduleAccessLevel >= 'R') {
    if (Loader::includeModule($moduleId)) {
        $response = Context::getCurrent()->getResponse();
        $response->addHeader('Content-Type', 'application/json');

        try {
            $settingsForm = SettingsForm::make($_POST + $_POST['proxy'] ?? []);
            $notification = (new TelegramNotification($settingsForm->token))
                ->to($settingsForm->chat_id)
                ->setProxy($settingsForm->getProxySettings());

            /**
             * @var $notificator NotificationService
             */
            $notificator = Container::get(NotificationService::class);

            $sampleEvent = SampleEvent::make([
                'CHAT_ID' => $settingsForm->chat_id,
                'PROXY' => $settingsForm->getDSN(),
            ]);

            $message = $sampleEvent->convert();
            $notificator->with($notification)->send($message);

            echo Json::encode(['result' => true]);
        } catch (SendException $e) {
            echo Json::encode([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'proxy' => $settingsForm->getDSN()
                ]
            ]);
        } catch (\Throwable $e) {
            echo Json::encode(['error' => ['code' => $e->getCode(), 'message' => $e->getMessage()]]);
        }
    }
}


require_once($webRoot . 'bitrix/modules/main/include/epilog_admin_after.php');
