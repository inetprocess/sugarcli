<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Exception\UnsupportedFormatException;

use SugarCli\Console\ExitCode;
use SugarCli\Inventory\Facter;
use SugarCli\Inventory\SugarFacter;
use SugarCli\Sugar\Sugar;

class InventoryFacterCommand extends AbstractDefaultFromConfCommand
{
    protected function getDefaults()
    {
        return array('path' => 'sugarcrm.path');
    }

    protected function configure()
    {
        $this->setName('inventory:facter')
            ->setDescription('Get facts from system and a Sugar instance')
            ->addArgument(
                'source',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Specify facts source (all|system|sugarcrm)',
                array('all')
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Specify the output format. (json|yml|xml).',
                'json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all_facts = array(
            'system' => array(),
            'sugarcrm' => array()
        );
        $source = $input->getArgument('source');
        if (in_array('all', $source) or in_array('system', $source)) {
            $facter = new Facter();
            $all_facts['system'] = $facter->getFacts();
        }
        if (in_array('all', $source) or in_array('sugarcrm', $source)) {
            $sugar = new Sugar($this->getDefaultOption($input, 'path'));
            $sugar_facter = new SugarFacter($sugar);
            $all_facts['sugarcrm'] = $sugar_facter->getFacts();
        }


        $format = $input->getOption('format');
        $serial = SerializerBuilder::create()->build();
        try {
            $output->write($serial->serialize($all_facts, $format));
        } catch (UnsupportedFormatException $e) {
            $output->write("<comment>Format $format is not supported.</comment>\n");
            return ExitCode::EXIT_FORMAT_ERROR;
        }
    }
}
