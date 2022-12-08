<?php

namespace Xzag\Telegram\Service\Template;

use Twig\Environment;
use Twig\Error\Error;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;

/**
 * Class TwigService
 * @package Xzag\Telegram\Service\Template
 */
class TwigService implements TemplateEngineInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param string $template
     * @param array $context
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(string $template, array $context = []): string
    {
        $loader = new ArrayLoader([
            'template' => $template,
        ]);
        $twig = new Environment($loader);

        // set custom error handler to prevent deprecated errors
        set_error_handler(
            function ($errNo, $errStr) {
                if (!error_reporting() || $errNo === E_USER_DEPRECATED) {
                    return true;
                }

                return false;
            }
        );

        $rendered = $twig->render('template', $context);
        restore_error_handler();

        return $rendered;
    }

    /**
     * @param string $template
     * @return bool
     */
    public function validate(string $template): bool
    {
        try {
            $this->render($template);
            return true;
        } catch (Error $e) {
            $this->addError($e->getMessage());
        }

        return false;
    }

    /**
     * @param string $error
     */
    public function addError(string $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
