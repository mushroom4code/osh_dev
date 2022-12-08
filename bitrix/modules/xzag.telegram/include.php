<?php

use Xzag\Telegram\Container;
use Xzag\Telegram\Service\Logger;
use Xzag\Telegram\Service\Template\TwigService;

require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$container = Container::instance('xzag.telegram');
$container->setLogger(new Logger($container->getModuleId()));
$container->setTemplateService(new TwigService());
