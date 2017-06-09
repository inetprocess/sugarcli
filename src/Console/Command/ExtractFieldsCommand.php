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

use CSanquer\ColibriCsv\CsvWriter;
use Inet\SugarCRM\Bean as BeanManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to create a CSV file that contains fields and relationships for a SugarCRM module
 *
 */
class ExtractFieldsCommand extends AbstractConfigOptionCommand
{
    /**
     * Module Name
     *
     * @var string
     */
    protected $module;

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        // First command : Test the DB Connexion
        $this->setName('extract:fields')
            ->setDescription('Export fields and relationships definitions to CSV files')
            ->setHelp(<<<EOHELP
Extract all fields and relationships defined for a module with their parameters (label, dropdown list, dbType, ...)
and write the data to 2 CSV files.

<comment>Example:</comment>
    <info>sugarcli extract:fields --module Accounts</info>
EOHELP
             )
             ->enableStandardOption('path')
             ->enableStandardOption('user-id')
             ->addOption(
                 'module',
                 'm',
                 InputOption::VALUE_REQUIRED,
                 "Module's name"
             )->addOption(
                 'lang',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'SugarCRM Language',
                 'en_us'
             );
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ep = $this->getService('sugarcrm.entrypoint');
        $bm = new BeanManager($ep);
        $this->module = $input->getOption('module');
        // Get the file as a parameter
        if (empty($this->module)) {
            $moduleList = array_keys($ep->getBeansList());
            $msg = 'You must define the module with --module';
            $msg.= PHP_EOL . PHP_EOL . 'List of Available modules: ' . PHP_EOL;
            $msg.= '    - ' . implode(PHP_EOL . '    - ', $moduleList);
            throw new \InvalidArgumentException($msg);
        }

        ########### FIELDS
        $moduleFields = $bm->getModuleFields($this->module, $input->getOption('lang'), true);

        // Change the lists that are arrays as strings
        foreach ($moduleFields as $key => $moduleProps) {
            if (array_key_exists('options_list', $moduleProps)) {
                $optionsList = '';
                foreach ($moduleFields[$key]['options_list'] as $optK => $optV) {
                    $optionsList.= "$optK<=>$optV" . PHP_EOL;
                }
                $moduleFields[$key]['options_list'] = $optionsList;
            } else {
                $moduleFields[$key]['options_list'] = 'N/A';
            }
        }

        // create the writer
        $writer = new CsvWriter(array(
            'delimiter' => ';',
            'enclosure' => '"',
            'encoding' => 'UTF-8',
            'bom' => false,
            'first_row_header' => true,
            'trim' => true,
        ));
        $file = $this->module . '-Fields.' .  date('Y-m-d') . '.csv';
        //Open the csv file to write
        $writer->open(getcwd() . '/' . $file);
        $writer->writeRows($moduleFields);
        $writer->close();

        $output->writeln("<comment>All fields for {$this->module} written in $file</comment>");

        ########### RELATIONSHIPS
        $moduleRelationships = $bm->getModuleRelationships($this->module);
        // create the writer
        $writer = new CsvWriter(array(
            'delimiter' => ';',
            'enclosure' => '"',
            'encoding' => 'UTF-8',
            'bom' => false,
            'first_row_header' => true,
            'trim' => true,
        ));
        $file = $this->module . '-Relationships.' .  date('Y-m-d') . '.csv';
        //Open the csv file to write
        $writer->open(getcwd() . '/' . $file);
        $writer->writeRows($moduleRelationships);
        $writer->close();

        $output->writeln("<comment>All relationships for {$this->module} written in $file</comment>");
    }
}
