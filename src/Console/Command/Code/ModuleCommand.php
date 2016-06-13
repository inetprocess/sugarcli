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

namespace SugarCli\Console\Command\Code;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\EntryPoint;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\Templater;
use SugarCli\Console\TemplateTypeEnum;
use SugarCli\Utils\CodeCommandsUtility;
use SugarCli\Utils\Utils;

class ModuleCommand extends AbstractConfigOptionCommand
{
    // Class members /////////////////////////////////////////////////////
    /**
     * Store Options values
     *
     * @var array $options
     */
    protected $options = array();

    // Class methods /////////////////////////////////////////////////////
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
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the Sugar path from one of the specified locations and verify commandline options
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $this->checkOptions($input);

        // Prepare replacement values array for template writing
        $replacements = array(
            'module' => $this->options['name']
        );

        // Retrieve the templater service from app container
        /** @var Templater $templater */
        $templater = $this->getContainer()->get('templater');

        // Process and write the files from the templates for a module
        $templateWriter = new CodeCommandsUtility($templater);

        $templateWriter->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::MODULE,
            $this->getService('sugarcrm.entrypoint')->getPath());

        // Output success message
        $output->writeln('Directory structure and files for custom module, '. $this->options['name']. ', added.');
        
        // Everything went fine
        return 0;
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
        /** @var EntryPoint $sugarEntryPoint */
        $sugarEntryPoint = $this->getService('sugarcrm.entrypoint');
        $moduleList = array_keys($sugarEntryPoint->getBeansList());

        foreach ($moduleList as $moduleName) {
            // Get the base module name for the current module and throw exception if match is found
            if (strcasecmp($newModuleBase, Utils::baseModuleName($moduleName)) == 0) {
                $errorMsg  = 'You must define a unique name for the module';
                $errorMsg .= PHP_EOL . PHP_EOL . 'New module name, '. $this->options['name']. ', matched the module ';
                $errorMsg .= $moduleName. ' with the prefixes removed.';

                throw new \InvalidArgumentException($errorMsg);
            }
        }
    }
}
