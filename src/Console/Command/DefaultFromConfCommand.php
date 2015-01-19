<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use SugarCli\Console\ConfigException;

abstract class DefaultFromConfCommand extends Command
{
    const SHORT = 0;
    const DESCRIPTION = 1;

    /**
     * Return an array with the parameters needed with
     * argument as key and and section.key as value
     * @example array('path' => 'sugarcrm.path')
     */
    abstract protected function getDefaults();

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->configureDefaults();
    }

    protected function getOptionsData($name)
    {
        $options = array(
            'path' => array(
                self::SHORT => 'p',
                self::DESCRIPTION => 'Path to SugarCRM installation.',
            ),
            'url' => array(
                self::SHORT => 'u',
                self::DESCRIPTION => 'Public url of SugarCRM.',
            )
        );

        if (!array_key_exists($name, $options)) {
            throw new \Exception("Couldn't find option data for $name.");
        }
        return $options[$name];
    }

    protected function setDefaultOptions($name)
    {
        $opt_data = $this->getOptionsData($name);
        $this->addOption(
            $name,
            $opt_data[self::SHORT],
            InputOption::VALUE_REQUIRED,
            $opt_data[self::DESCRIPTION],
            null
        );
    }

    protected function getDefaultOption(InputInterface $input, $name)
    {
        $defaults = $this->getDefaults();
        if (!array_key_exists($name, $defaults)) {
            throw new \InvalidArgumentException(sprintf('The "%s" argument does not exist.', $name));
        }
        if ($input->getOption($name) !== null) {
            return $input->getOption($name);
        }
        $config = $this->getHelper('config');
        if (!$config->has($defaults[$name])) {
            throw new \InvalidArgumentException(
                sprintf('The "%s" option is not specified and not found in the config "%s"', $name, $defaults[$name])
            );
        }
        return $config->get($defaults[$name]);
    }

    protected function configureDefaults()
    {
        foreach (array_keys($this->getDefaults()) as $name) {
            $this->setDefaultOptions($name);
        }
    }
}

