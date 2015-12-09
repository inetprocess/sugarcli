<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.6
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcli
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractConfigOptionCommand extends AbstractContainerAwareCommand
{
    protected $config_options_mapping = array();
    protected $config_options = array();

    protected function getConfigOptionMapping()
    {
        return $this->config_options_mapping;
    }

    protected function getConfigOptions()
    {
        return $this->config_options;
    }

    protected function addConfigOptionMapping($name, $path)
    {
        $this->config_options_mapping[$name] = $path;

        return $this;
    }

    protected function addConfigOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->config_options[$name] = new InputOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    public function __construct($name = null)
    {
        // Parent will call $this->configure()
        parent::__construct($name);
        $this->addConfigOption(
            'path',
            'p',
            InputOption::VALUE_REQUIRED,
            'Path to SugarCRM installation.'
        );
        $this->configureConfigOptions();
    }

    protected function configureConfigOptions()
    {
        $options = $this->getConfigOptions();
        foreach (array_keys($this->getConfigOptionMapping()) as $name) {
            if (isset($options[$name])) {
                $this->getDefinition()->addOption($options[$name]);
            }
        }
    }

    protected function getConfigOption(InputInterface $input, $name)
    {
        $defaults = $this->getConfigOptionMapping();
        if (!array_key_exists($name, $defaults)) {
            throw new \InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        if ($input->getOption($name) !== null) {
            return $input->getOption($name);
        }
        $config = $this->getService('config');
        if (!$config->has($defaults[$name])) {
            throw new \InvalidArgumentException(
                sprintf('The "%s" option is not specified and not found in the config "%s"', $name, $defaults[$name])
            );
        }

        return $config->get($defaults[$name]);
    }
}
