<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;

class InputConfigOption extends InputOption
{
    protected $config_path;
    protected $required;
    protected $callback;

    public function isRequired()
    {
        return $this->required;
    }

    public function setRequired($required)
    {
        $this->required = $required;
    }

    public function getConfigPath()
    {
        return $this->config_path;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function __construct(
        $config_path,
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null,
        $required = true,
        $callback = null
    ) {
        parent::__construct($name, $shortcut, $mode, $description, $default);
        if (empty($config_path)) {
            throw new \InvalidArgumentException(sprintf(
                'The config option "%s" is not mapped to a configuration parameter.',
                $name
            ));
        }
        $this->config_path = $config_path;
        $this->required = $required;
        $this->callback = $callback;
    }
}
