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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SugarCli\Console\Command\InputConfigOption;

abstract class AbstractConfigOptionCommand extends AbstractContainerAwareCommand
{
    protected $process_callbacks = true;

    public function __construct($name = null)
    {
        // Parent will call $this->configure()
        parent::__construct($name);
    }
    protected function setProcessCallbacks($do_it = true)
    {
        $this->process_callbacks = $do_it;
    }

    protected function shouldProcessCallbacks()
    {
        return $this->process_callbacks;
    }

    protected function enableStandardOption($name)
    {
        $this->enableStandardOptions(array($name));
        return $this;
    }

    protected function enableStandardOptions($options_names)
    {
        $std_options = $this->getStandardOptions();
        foreach ($options_names as $name) {
            if (!array_key_exists($name, $std_options)) {
                throw new \InvalidArgumentException(
                    sprintf('Standard option "%s" doesn\'t exists.', $name)
                );
            }
            call_user_func_array(array($this, 'addConfigOption'), $std_options[$name]);
        }
        return $this;
    }

    protected function getStandardOptions()
    {
        return array(
            'path' => array(
                'sugarcrm.path',
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Path to SugarCRM installation.',
                null,
                true,
                function ($option_name, InputInterface $input, Command $command) {
                    if (!$command->getContainer()->isFrozen()) {
                        $command->getContainer()->setParameter('sugarcrm.path', $input->getOption($option_name));
                    }
                }
            ),
            'user-id' => array(
                'sugarcrm.user_id',
                'user-id',
                null,
                InputOption::VALUE_REQUIRED,
                'SugarCRM user id to impersonate when running the command.',
                '1',
                true,
                function ($option_name, InputInterface $input, Command $command) {
                    if (!$command->getContainer()->isFrozen()) {
                        $command->getContainer()->setParameter(
                            'sugarcrm.user-id',
                            $input->getOption($option_name)
                        );
                    }
                }
            ),
        );
    }

    protected function setRequiredOption($name, $required = true)
    {
        $this->getDefinition()->getOption($name)->setRequired($required);
    }

    protected function addConfigOption(
        $config_path,
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null,
        $required = true,
        $callback = null
    ) {
        $this->getDefinition()->addOption(new InputConfigOption(
            $config_path,
            $name,
            $shortcut,
            $mode,
            $description,
            $default,
            $required,
            $callback
        ));
        return $this;
    }

    protected function getInputConfigOptions()
    {
        return array_filter($this->getDefinition()->getOptions(), function ($option) {
            return $option instanceof InputConfigOption;
        });
    }

    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        foreach ($this->getInputConfigOptions() as $option) {
            $config_path = $option->getConfigPath();
            $config = $this->getService('config');
            if ($config->has($config_path)) {
                $option->setDefault($config->get($config_path));
            }
        }
    }

    protected function processCallbacks(InputInterface $input)
    {
        foreach ($this->getInputConfigOptions() as $option) {
            if (($callback = $option->getCallback()) !== null) {
                call_user_func($callback, $option->getName(), $input, $this);
            }
        }
        if (!$this->getContainer()->isFrozen()) {
            $this->getContainer()->compile();
        }
    }

    /**
     * @override
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // Parse input
        try {
            $this->mergeApplicationDefinition();
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface $e) {
            // ignore invalid options/arguments for now, we are just modifying the input.
        }

        // Validate required options
        foreach ($this->getInputConfigOptions() as $option) {
            if ($option->isRequired() && $input->getOption($option->getName()) === null) {
                throw new \InvalidArgumentException(sprintf(
                    'The "%s" option is not specified and not found in the config "%s"',
                    $option->getName(),
                    $option->getConfigPath()
                ));
            }
        }
        // Process callbacks from options.
        if ($this->shouldProcessCallbacks()) {
            $this->processCallbacks($input);
        }
        return parent::run($input, $output);
    }
}
