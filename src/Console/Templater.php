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
     * @param string $template              the path to the Twig templates directory (optional)
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
}