<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.5
 * SugarCRM Versions 6.5 - 7.7
 *
 * @author Joe Cora
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2016 The New York Times
 * @copyright 2005-2015 iNet Process
 *
 * @package nyt/sugarcli-nyt
 *
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 *
 * @since 1.11.1 Used ButtonCommand class as a template
 */

namespace SugarCli\Console\Command\Code;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\MetadataParser;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Utils\Utils;
use SugarCli\Console\Templater;

class ModuleCommand extends AbstractConfigOptionCommand
{
    /**
     * Store Options values
     *
     * @var array $options
     */
    protected $options = array();

    /**
     * Configure the command
     */
    protected function configure()
    {
        // Configure the command with its name and options
        $this->setName('code:module')
            ->setDescription('Add the skeleton code for a custom module')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Module Name'
            );
    }

    /**
     * Run the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the Sugar path from one of the specified locations and verify commandline options
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $this->checkOptions($input);

        // Retrieve the templater service from app container
        $templater = $this->getContainer()->get('templater');

        // Use module name to replace placeholder values in templates
        $params = array(
            'module' => $this->options['name']
        );

        $script = $templater->processTemplate('module/modules/__module__/__module___sugar.php.twig', $params);
        echo PHP_EOL. PHP_EOL. $script;
    }

    /**
     * Check required options and their values
     *
     * @param InputInterface $input
     */
    protected function checkOptions(InputInterface $input)
    {
        // Confirm that the module name exists
        $this->options['name'] = $input->getOption('name');

        if (empty($this->options['name'])) {
            throw new \InvalidArgumentException('You must define the new module\'s name');
        }
        
        // Get the base module name for the new module
        $newModuleBase = Utils::baseModuleName($this->options['name']);

        // Check that the module name or the prefix removed variant are not already defined
        $moduleList = array_keys($this->getService('sugarcrm.entrypoint')->getBeansList());
        
        foreach ($moduleList as $moduleName) {
            // Get the base module name for the current module and throw exception if match is found
            if ($newModuleBase == Utils::baseModuleName($moduleName)) {
                $msg  = 'You must define a unique name for the module';
                $msg .= PHP_EOL . PHP_EOL . 'New module name, '. $this->options['name']. ', matched the module '. $moduleName. ' with the prefixes removed.';

                throw new \InvalidArgumentException($msg);
            }
        }
    }
}
