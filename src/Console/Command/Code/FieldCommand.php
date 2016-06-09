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

class FieldCommand extends AbstractConfigOptionCommand
{
    // Class members /////////////////////////////////////////////////////
    /**
     * Static list of available field types for a field
     *
     * @var array $fieldtypes
     */
    public static $fieldtypes = array(
        'bool',
        'varchar'
    );
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
        $this->setName('code:field')
            ->setDescription('Add the skeleton code for a custom field')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'module',
                'm',
                InputOption::VALUE_REQUIRED,
                'Module Name'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Field Name'
            )
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Field Type; Available values: '. join(', ', self::$fieldtypes)
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
            'field' => $this->options['name'],
            'type' => $this->options['type']
        );

        // Retrieve the templater service from app container
        /** @var Templater $templater */
        $templater = $this->getContainer()->get('templater');

        // Process and write the files from the templates for a field
        $templateWriter = new CodeCommandsUtility($templater);

        $templateWriter->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::FIELD,
            $this->getService('sugarcrm.entrypoint')->getPath());

        // Output success message
        $output->writeln('Files for custom '. $this->options['type']. ' field, '. $this->options['name']. ', for module, '. $this->options['module']. ', added.');
        
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
            throw new \InvalidArgumentException('You must define the module\'s name');
        }

        // Confirm that the field name exists
        $this->options['name'] = $input->getOption('name');

        if (empty($this->options['name'])) {
            throw new \InvalidArgumentException('You must define the new field\'s name');
        }

        // Confirm that the field type is available
        $this->options['type'] = $input->getOption('type');

        if (!in_array($this->options['type'], self::$fieldtypes)) {
            $errorMsg  = 'You must define a valid field type';
            $errorMsg .= PHP_EOL . PHP_EOL . 'Available field types are:';
            $errorMsg .= PHP_EOL. "\t". join(PHP_EOL. "\t", self::$fieldtypes);

            throw new \InvalidArgumentException($errorMsg);
        }
    }
}
