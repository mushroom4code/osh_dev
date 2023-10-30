<?php
require $_SERVER["DOCUMENT_ROOT"] .'/vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

if ($_POST['action'] === 'sendPWA') {
    $subscription = Subscription::create([
        'contentEncoding' => 'aesgcm',
        'authToken'=>'wBKe2NgQ6gQusm4M8oKLDg',
        'publicKey' => 'BG4TZTLeSclPt2R0KCrU5_Map3wDmwl99HFy23r4YxEPfNsDPSE8SIZL_brmD59E4sOmplv9_gO7NlBG0FzdT0g',
        'endpoint' => 'https://web.push.apple.com/QJSGFBKhy6ZB_0_OA3Jgkn9ZbWxLjSJu8bMEA5iGs6cU8Wgqj05OIB4Py_WWtl5qoPEDKIzG1cFQ4KfL2YpL5n6XJ6y0RSH-Utx6Usjhj3zDad6ogz0ee8LONrW0d2SgorgiJEumTXEv-oIUHmzrIq3-CV7Pp1lJV9Qt-AK8UjA',
    ]);

    $auth = array(
        'VAPID' => array(
            'subject' => 'https://er.docker.oblako-1c.ru/',
            'publicKey' => file_get_contents($_SERVER['DOCUMENT_ROOT'].'/local/templates/Oshisha/pwa/keys/public_key.txt'), // don't forget that your public key also lives in app.js
            'privateKey' => file_get_contents($_SERVER['DOCUMENT_ROOT'].'/local/templates/Oshisha/pwa/keys/private_key.txt'), // in the real world, this would be in a secret file
        ),
    );
    $webPush = new WebPush($auth);

    $report = $webPush->sendOneNotification(
        $subscription,
        'Hello! 👋',
    );
    $endpoint = $report->getRequest()->getUri()->__toString();
    $result = 'test';
    exit($result);
};
