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
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\Templater;
use SugarCli\Console\TemplateTypeEnum;
use SugarCli\Utils\CodeCommandsUtility;
use SugarCli\Utils\Utils;

class IndexCommand extends AbstractConfigOptionCommand
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
        $this->setName('code:index')
            ->setDescription('Add an index for a field or list of fields within a module')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_REQUIRED,
                'Module Name for Index'
            )
            ->addOption(
                'fields',
                'f',
                InputOption::VALUE_REQUIRED,
                'Field or Fields Indexed (Comma-Separated)'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Index Name'
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
            'module' => $this->options['module'],
            'moduleBase' => Utils::baseModuleName($this->options['module']),
            'fields' => $this->options['fields'],
            'index' => $this->options['name']
        );

        // Retrieve the templater service from app container
        /** @var Templater $templater */
        $templater = $this->getContainer()->get('templater');

        // Process and write the files from the templates for a field
        $templateWriter = new CodeCommandsUtility($templater);

        $templateWriter->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::INDEX,
            $this->getService('sugarcrm.entrypoint')->getPath());

        // Output success message
        $output->writeln('Files for index, '. $this->options['name']. ', of fields, '. $this->options['fields']. ', in module, '. $this->options['module']. ', added.');
        
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
        $this->options['module'] = $input->getOption('module');

        if (empty($this->options['module'])) {
            throw new \InvalidArgumentException('You must define the module\'s name for the index');
        }

        // Confirm that the index field(s) exists
        $this->options['fields'] = $input->getOption('fields');

        if (empty($this->options['fields'])) {
            throw new \InvalidArgumentException('You must define the field or fields to index (comma-separated)');
        }

        // Confirm that the index name exists
        $this->options['name'] = $input->getOption('name');

        if (empty($this->options['name'])) {
            throw new \InvalidArgumentException('You must define the index name');
        }
    }
}
