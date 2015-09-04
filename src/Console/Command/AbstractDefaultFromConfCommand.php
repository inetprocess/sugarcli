<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use SugarCli\Console\ConfigException;

abstract class AbstractDefaultFromConfCommand extends AbstractContainerAwareCommand
{
    /**
     * Return an array with the parameters needed with
     * argument as key and and section.key as value
     * @example array('path' => 'sugarcrm.path')
     */
    abstract protected function getConfigOptionMapping();

    public function __construct($name = null)
    {
        parent::__construct($name);
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

    protected function getConfigOptions()
    {
        return array(
            'path' => new InputOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Path to SugarCRM installation.'
            ),
        );
    }

    protected function getDefaultOption(InputInterface $input, $name)
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
