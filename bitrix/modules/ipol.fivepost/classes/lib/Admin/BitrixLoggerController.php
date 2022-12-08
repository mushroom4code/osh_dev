<?php


namespace Ipol\Fivepost\Admin;


use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Ipol\Fivepost\Api\Logger\FileRoute;
use Ipol\Fivepost\Api\Logger\Psr\Log\LogLevel;

/**
 * Class BitrixLoggerController
 * @package Ipol\Fivepost\Admin
 */
class BitrixLoggerController extends \Ipol\Fivepost\Api\Logger\Logger
{
    /**
     * @var array
     */
    protected $arrLogged;

    const FILE_FORMAT = '.txt';

    /**
     * BitrixLoggerController constructor.
     * @param string $name - file prefix (name without extension)
     */
    public function __construct(string $name = 'common_log')
    {
        $name = $name . self::FILE_FORMAT;

        try {
            $arrOpt = Option::getForModule(IPOL_FIVEPOST);
        } catch (ArgumentNullException $e) {
            $arrOpt = []; //though, it will never happen
        }
        $this->arrLogged = [];
        foreach ($arrOpt as $optName => $optVal) {
            if ((strpos($optName, 'log_') === 0) && ($optVal === 'Y')) {
                $this->arrLogged[] = trim(substr($optName,4));
            }
        }

        $path = self::getPath().$name;

        $route = new FileRoute($path);
        $route->enable();
        parent::__construct([$route]);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message = '', array $context = []): void
    {
        if ($level === LogLevel::DEBUG) {
            //TODO may be change logic - now it will log, if log was passed to application. No integrated control
            //if (array_key_exists('method', $context) && in_array($context['method'], $this->arrLogged)) {
                parent::log($level, $this->interpolate(self::getCurlTemplate(), $context), []);
            //}
        } else {
            parent::log($level, $message, $context);
        }
    }

    /**
     * @return string
     */
    public function getLog(): string
    {
        foreach ($this->routes as $route) {
            return $route->read(); //return first existing, if there is one
        }
        return ''; //empty string in case there is no routs for some reason
    }

    protected static function getCurlTemplate(): string
    {
        return '{method}' . ' ' . '{process}' . PHP_EOL . '{content}';
    }

    /**
     * @return string
     * returns directory with logs
     */
    public static function getPath()
    {
        return $_SERVER['DOCUMENT_ROOT'].self::getRelativePath();
    }

    public static function getRelativePath()
    {
        return DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, ['bitrix', 'tools', IPOL_FIVEPOST, 'logs']).DIRECTORY_SEPARATOR;
    }


}