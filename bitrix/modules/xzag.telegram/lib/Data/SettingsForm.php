<?php

namespace Xzag\Telegram\Data;

/**
 * Class SettingsForm
 * @package Xzag\Telegram\Data
 */
class SettingsForm extends ProxySettings
{
    /**
     * @var string
     */
    public $chat_id;

    /**
     * @var string
     */
    public $token;

    /**
     * @return ProxySettings
     */
    public function getProxySettings(): ProxySettings
    {
        return parent::make(parent::toArray());
    }
}
