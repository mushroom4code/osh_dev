<?php

namespace Xzag\Telegram\Data;

/**
 * Class ProxySettings
 * @package Xzag\Telegram\Data
 */
class ProxySettings extends AbstractStructure
{
    /**
     * @var boolean
     */
    public $enabled;

    /**
     * @var string|null
     */
    public $host;

    /**
     * @var string|null
     */
    public $username;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled === 'on';
    }

    /**
     * @return string
     */
    public function getDSN(): string
    {
        $dsn = '';

        if ($this->isEnabled()) {
            if ($this->username || $this->password) {
                $dsn .= $this->username . ':' . $this->password . '@';
            }

            $dsn .= $this->host;
        }

        return $dsn;
    }
}
