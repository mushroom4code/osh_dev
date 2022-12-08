<?php

namespace Xzag\Telegram\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Json;
use CEventLog;
use Psr\Log\LoggerInterface;

/**
 * Class LogService
 * @package Xzag\Telegram\services
 */
class Logger implements LoggerInterface
{
    const BITRIX_LEVEL_ERROR   = 'ERROR';
    const BITRIX_LEVEL_INFO    = 'INFO';
    const BITRIX_LEVEL_WARNING = 'WARNING';
    const BITRIX_LEVEL_DEBUG   = 'DEBUG';

    /**
     *
     */
    const BITRIX_LEVEL_MAP = [
        CEventLog::SEVERITY_ERROR   => self::BITRIX_LEVEL_ERROR,
        CEventLog::SEVERITY_INFO    => self::BITRIX_LEVEL_INFO,
        CEventLog::SEVERITY_WARNING => self::BITRIX_LEVEL_WARNING,
        CEventLog::SEVERITY_DEBUG   => self::BITRIX_LEVEL_DEBUG
    ];

    /**
     * @var string|null
     */
    private $moduleId;

    /**
     * LogService constructor.
     * @param string|null $moduleId
     */
    public function __construct(string $moduleId = null)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_SECURITY, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_WARNING, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_INFO, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_INFO, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = array())
    {
        $this->log(CEventLog::SEVERITY_DEBUG, $message, $context);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        try {
            $formatted = nl2br(
                is_array($context)
                    ? Json::encode($context['data'] ?? null, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                    : $context
            );
        } catch (ArgumentException $e) {
            $formatted = 'Failed to serialize context: ' . $e->getMessage();
        }

        $itemId = is_array($context) ? $context['itemId'] ?? null : null;
        CEventLog::Log(
            self::BITRIX_LEVEL_MAP[$level] ?? self::BITRIX_LEVEL_ERROR,
            $message,
            $this->moduleId,
            $itemId,
            $formatted
        );
    }
}
