<?php
require $_SERVER["DOCUMENT_ROOT"] .'/vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// here I'll get the subscription endpoint in the POST parameters
// but in reality, you'll get this information in your database
// because you already stored it (cf. push_subscription.php)
$subscription = Subscription::create(json_decode(file_get_contents('php://input'), true));

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

// handle eventual errors here, and remove the subscription from your server if it is expired
$endpoint = $report->getRequest()->getUri()->__toString();

if ($report->isSuccess()) {
    echo "[v] Message sent successfully for subscription {$endpoint}.";
} else {
    echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
}
