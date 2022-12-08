<?php

namespace Xzag\Telegram\Service\Notification;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\HttpException;
use Xzag\Telegram\Data\ProxySettings;
use Xzag\Telegram\Exception\SendException;

/**
 * Class TelegramNotification
 * @package Xzag\Telegram\Service\Notification
 */
class TelegramNotification implements SendableInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var BotApi
     */
    private $client;

    /**
     * @var string
     */
    private $chatId;

    /**
     * TelegramNotification constructor.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return BotApi
     */
    private function getClient()
    {
        if (!$this->client) {
            $this->client = new BotApi($this->token);
            $this->client->setCurlOption(CURLOPT_TIMEOUT, 10);
            $this->client->setCurlOption(CURLOPT_CONNECTTIMEOUT, 10);
        }
        return $this->client;
    }

    /**
     * @param ProxySettings $proxy
     * @return TelegramNotification
     */
    public function setProxy(ProxySettings $proxy): TelegramNotification
    {
        if (!$proxy->isEnabled()) {
            return $this;
        }

        $url = parse_url($proxy->host);
        $client = $this->getClient();
        $client->setCurlOption(CURLOPT_PROXY, $url['host'] . ($url['port'] ? ':' . $url['port'] : '') . $url['path']);

        if ($proxy->username || $proxy->password) {
            $client->setCurlOption(CURLOPT_PROXYUSERPWD, $proxy->username . ':' . $proxy->password);
        }

        switch ($url['scheme']) {
            case 'socks':
            case 'socks5':
                $client->setCurlOption(
                    CURLOPT_PROXYTYPE,
                    defined('CURLPROXY_SOCKS5_HOSTNAME') ? CURLPROXY_SOCKS5_HOSTNAME : 7
                );
                break;
            case 'socks4':
                $client->setCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                break;
            case 'https':
                $client->setCurlOption(
                    CURLOPT_PROXYTYPE,
                    defined('CURLPROXY_HTTPS') ? CURLPROXY_HTTPS : 2 //phpcs:ignore
                );
                break;
            default:
                $client->setCurlOption(CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                break;
        }

        return $this;
    }

    /**
     * @param string $chatId
     * @return TelegramNotification
     */
    public function to(string $chatId): TelegramNotification
    {
        $this->chatId = $chatId;
        return $this;
    }

    /**
     * @param string $message
     * @return string
     */
    private function ensureUtf8(string $message): string
    {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($message, 'UTF-8', ['Windows-1251', 'UTF-8', mb_detect_encoding($message)]);
        }

        return $message;
    }

    /**
     * @param string $message
     * @return mixed|void
     * @throws SendException
     */
    public function send(string $message)
    {
        try {
            $this->getClient()->sendMessage($this->chatId, $this->ensureUtf8($message), 'HTML');
        } catch (HttpException $e) {
            $httpCode = $e->getCode();
            // do not send exception for 1xx codes
            if (!in_array($httpCode, [100, 101, 102])) {
                throw new SendException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (Exception $e) {
            throw new SendException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
