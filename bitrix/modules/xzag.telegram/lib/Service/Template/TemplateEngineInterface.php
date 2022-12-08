<?php

namespace Xzag\Telegram\Service\Template;

/**
 * Interface TemplateEngineInterface
 * @package Xzag\Telegram\Service\Template
 */
interface TemplateEngineInterface
{
    /**
     * @param string $template
     * @param array  $context
     *
     * @return string
     */
    public function render(string $template, array $context = []): string;

    /**
     * @param string $template
     *
     * @return boolean
     */
    public function validate(string $template): bool;

    /**
     * @param string $error
     *
     * @return mixed
     */
    public function addError(string $error);

    /**
     * @return array
     */
    public function getErrors(): array;
}
