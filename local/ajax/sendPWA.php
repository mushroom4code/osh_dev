<?php
require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Enterego\PWA\EnteregoMobileAppEvents;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

CModule::IncludeModule("iblock");
Loader::includeModule('main');

$request = Context::getCurrent()->getRequest();
$action = $request->get('action');

if (!empty($_POST['action']) && $_POST['action'] === 'sendPWA') {
    /**
     * @var CUser $USER
     */
    $userId = $_POST['userId'];

    $subscription = Subscription::create(json_decode($_POST['jsonSubscription'], true));
    $resultSelect = Enterego\PWA\EnteregoDBPushNotificationPWATable::getList(
        array(
            'select' => array('*'),
            'filter' => array(
                'USER_ID' => $userId,
            ),
        )
    );

    if (!$resultSelect->Fetch()) {
        Enterego\PWA\EnteregoDBPushNotificationPWATable::add(
            array(
                'USER_ID' => $userId,
                'AUTH_TOKEN' => $subscription->getAuthToken(),
                'PUBLIC_KEY' => $subscription->getPublicKey(),
                'CONTENT_ENCODING' => $subscription->getContentEncoding(),
                'END_POINT' => $subscription->getEndpoint(),

            ),
        );
    } else {
        Enterego\PWA\EnteregoDBPushNotificationPWATable::update(
            array(
                'I_BLOCK_ID' => $product_id,
                'F_USER_ID' => $FUser_id
            ),
            array(
                $METHOD => $value
            )
        );
    }

    if (strripos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        $_SERVER['HTTP_HOST'] = 'oshisha.net';
    }

    $auth = array(
        'VAPID' => array(
            'subject' => 'https://' . $_SERVER['HTTP_HOST'] . '/',
            'publicKey' => file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/local/templates/Oshisha/pwa/keys/public_key.txt'), // don't forget that your public key also lives in app.js
            'privateKey' => file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/local/templates/Oshisha/pwa/keys/private_key.txt'), // in the real world, this would be in a secret file
        ),
    );

    $webPush = new WebPush($auth);

    $report = $webPush->sendOneNotification($subscription, 'Hello! 👋');
    $endpoint = $report->getRequest()->getUri()->__toString();

    if ($report->isSuccess()) {
        $result = "[v] Message sent successfully for subscription {$endpoint}.";
    } else {
        $result = "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
    }

    exit($result);
}

if ($action === 'sendMobileRemoveUser') {
    global $USER;
    if ($USER->IsAuthorized()) {
        $result = EnteregoMobileAppEvents::setDeactiveUserForCordova($USER->GetID());
        if ($result) {
            $USER->Logout();
            echo 'true';
        }
    }
}