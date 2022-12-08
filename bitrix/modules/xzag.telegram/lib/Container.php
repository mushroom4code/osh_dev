<?php

namespace Xzag\Telegram;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Xzag\Telegram\Service\Template\TemplateEngineInterface;

class Container implements LoggerAwareInterface
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var string|null
     */
    private $moduleId;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TemplateEngineInterface
     */
    private $templateService;

    private $repository;

    /**
     * Container constructor.
     * @param string|null $moduleId
     */
    protected function __construct(string $moduleId = null)
    {
        $this->moduleId = $moduleId;
        $this->refreshOptions();
    }

    /**
     * @return string|null
     */
    public function getModuleId(): string
    {
        return (string)$this->moduleId;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param TemplateEngineInterface $templateService
     */
    public function setTemplateService(TemplateEngineInterface $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * @return TemplateEngineInterface
     */
    public function getTemplateService(): TemplateEngineInterface
    {
        return $this->templateService;
    }

    /**
     * @param string|null $moduleId
     * @return Container
     */
    public static function instance(string $moduleId = null): Container
    {
        if (!static::$instance) {
            static::$instance = new static($moduleId);
        }

        return static::$instance;
    }

    public function getDependency($class)
    {
        if (!isset($this->repository[$class])) {
            $this->repository[$class] = new $class();
        }

        return $this->repository[$class];
    }

    /**
     * @param $class
     * @return mixed
     */
    public static function get($class)
    {
        return static::instance()->getDependency($class);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function refreshOptions()
    {
        $this->options = [];
        if ($this->moduleId) {
            try {
                $this->options = Option::getForModule($this->moduleId);
            } catch (ArgumentNullException $e) {
                $this->options = [];
            }
        }
    }
}
