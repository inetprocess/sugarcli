<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.5
 * SugarCRM Versions 6.5 - 7.7
 *
 * @author Joe Cora
 * @copyright 2016 The New York Times
 *
 * @package nyt/sugarcli-nyt
 *
 * @license Apache License 2.0
 */

namespace SugarCli\Console;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TemplateTypeEnum
{
    // Enum definitions
    const MODULE = 1;
    const FIELD = 2;
    const RELATIONSHIP = 3;
}

class Templater
{
    // Class members
    /*
     * @var Twig_Environment $twig The Twig template handler
     */
    protected $twig;

    // Class methods
    /*
     * This is the constructor for the Templater class that takes an optional path to the templates and cache if the
     * default locations need amended.
     *
     * @param string $templatesPath         the path to the Twig templates directory (optional)
     * @param string $cachePath             the path to the Twig cache directory (optional)
     */
    public function __construct($templatesPath = null, $cachePath = null)
    {
        // Check if the default paths need applied
        if ($templatesPath === null) {
            $templatesPath = __DIR__ . '/../../res/code_templates';
        }

        if ($cachePath === null) {
            $cachePath = __DIR__ . '/../../res/code_templates/cache';
        }

        // Load and setup Twig
        $twigLoader = new Twig_Loader_Filesystem($templatesPath);
        $this->twig = new Twig_Environment($twigLoader, array(
            'cache' => $cachePath
        ));
    }

    /*
     * This method takes a template and replaces the template placeholders with their corresponding values from the
     * params array. A string produced from the template replacement is returned.
     *
     * @param string $template              the path to the Twig template
     * @param array $params                 the array of parameters whose value will replace the key name within the
     *      template (optional)
     * @return string                       string produced from parameter replacement in template
     * @throws BadMethodCallException       throw exception when a template is not defined
     */
    public function processTemplate($template, array $params = array()) {
        // Confirm that a template parameter is defined
        if (empty($template)) {
            throw new \BadMethodCallException('You must define a template');
        }

        // Load template, then return result of variable replacement
        $template = $this->twig->loadTemplate($template);

        return $template->render($params);
    }

    // Utility methods
    /*
     * This utility method takes a template name, a template type descriptor, and the string to replace the placeholder
     * for the type. A string produced from the template name replacement along with template file extension (.twig) is
     * returned.
     *
     * @param string $template              the path to the Twig template
     * @param (TemplateTypeEnum) $type      the type of the specified template as an enumeration
     * @param string $replace               the string to use to replace the template type placeholders
     * @return string                       string produced from template type replacement in template path and name
     * @throws BadMethodCallException       throw exception when a template is not defined
     *                                      throw exception when a replacement string is not defined
     *                                      throw exception when the template type is not identified
     */
    public static function replaceTemplateName($template, $type, $replace) {
        // Confirm that a template parameter is defined
        if (empty($template)) {
            throw new \BadMethodCallException('You must define a template');
        }

        // Confirm that a replace parameter is defined
        if (empty($replace)) {
            throw new \BadMethodCallException('You must define a replacement string');
        }

        // Get the placeholder from the template type
        $placeholder = null;

        switch ($type) {
            case TemplateTypeEnum::MODULE:
                $placeholder = '__module__';
                break;
            case TemplateTypeEnum::FIELD:
                $placeholder = '__field__';
                break;
            case TemplateTypeEnum::RELATIONSHIP:
                $placeholder = '__relationship__';
                break;
            default:
                throw new \BadMethodCallException('You must specify a valid template type, e.g., TemplateTypeEnum::MODULE');
        }

        // Replace and return all instances of the placeholder within the template path and the template file extension
        return str_replace('.twig', '', str_replace($placeholder, $replace, $template));
    }
}