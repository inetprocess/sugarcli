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

class RelationshipCommand extends AbstractConfigOptionCommand
{
    // Class members /////////////////////////////////////////////////////
    /**
     * Static list of available relationship types
     *
     * @var array $relationshiptypes
     */
    public static $relationshiptypes = array(
        'one-to-one',
        'one-to-many'
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
        $this->setName('code:relationship')
            ->setDescription('Add the skeleton code for a custom relationship')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'module-left',
                'l',
                InputOption::VALUE_REQUIRED,
                'Left Module Name'
            )
            ->addOption(
                'module-right',
                'r',
                InputOption::VALUE_REQUIRED,
                'Right Module Name'
            )
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Relationship Type; Available values: '. join(', ', self::$relationshiptypes)
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
            'module-left' => $this->options['module-left'],
            'module-right' => $this->options['module-right'],
            'type' => $this->options['type']
        );

        // Retrieve the templater service from app container
        /** @var Templater $templater */
        $templater = $this->getContainer()->get('templater');

        // Process and write the files from the templates for a relationship (each relationship component will have
        // it's own templates)
        $templateWriter = new CodeCommandsUtility($templater);

        $templateWriter->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::RELATIONSHIP,
            $this->getService('sugarcrm.entrypoint')->getPath());
        $templateWriter->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::RELATIONSHIP_LEFT,
            $this->getService('sugarcrm.entrypoint')->getPath());
        $templateWriter->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::RELATIONSHIP_RIGHT,
            $this->getService('sugarcrm.entrypoint')->getPath());

        // Output success message
        $output->writeln('Files for custom '. $this->options['type']. ' relationship between modules '. $this->options['module-left']. ' and '. $this->options['module-right']. ' added.');

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
        // Confirm that the left module name exists
        $this->options['module-left'] = $input->getOption('module-left');

        if (empty($this->options['module-left'])) {
            throw new \InvalidArgumentException('You must define the left module\'s name');
        }

        // Confirm that the right module name exists
        $this->options['module-right'] = $input->getOption('module-right');

        if (empty($this->options['module-right'])) {
            throw new \InvalidArgumentException('You must define the right module\'s name');
        }

        // Confirm that the left and right module names are not the same
        if (strcasecmp($this->options['module-left'], $this->options['module-right']) == 0) {
            throw new \InvalidArgumentException('The left and right modules in the relationship must be different');
        }

        // Confirm that the field type is available
        $this->options['type'] = $input->getOption('type');

        if (!in_array($this->options['type'], self::$relationshiptypes)) {
            $errorMsg  = 'You must define a valid relationship type';
            $errorMsg .= PHP_EOL . PHP_EOL . 'Available relationship types are:';
            $errorMsg .= PHP_EOL. "\t". join(PHP_EOL. "\t", self::$relationshiptypes);

            throw new \InvalidArgumentException($errorMsg);
        }
    }
}
