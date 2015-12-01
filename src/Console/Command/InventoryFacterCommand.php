<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Inet\SugarCRM\Application;
use SugarCli\Console\ExitCode;
use SugarCli\Inventory\Facter\ArrayFacter;
use SugarCli\Inventory\Facter\MultiFacterFacter;
use SugarCli\Inventory\Facter\SugarFacter;
use SugarCli\Inventory\Facter\SystemFacter;

class InventoryFacterCommand extends AbstractInventoryCommand
{
    protected function configure()
    {
        parent::configure();
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
                'yml'
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
            $facter = new MultiFacterFacter(array(
                new SystemFacter(),
                new ArrayFacter($this->getCustomFacts($input, 'system'))
            ));
            $all_facts['system'] = $facter->getFacts();
        }
        if (in_array('all', $source) or in_array('sugarcrm', $source)) {
            $this->setSugarPath($this->getConfigOption($input, 'path'));
            $sugar_facter = new MultiFacterFacter(array(
                new SugarFacter(
                    $this->getService('sugarcrm.application'),
                    $this->getService('sugarcrm.pdo')
                ),
                new ArrayFacter($this->getCustomFacts($input, 'sugarcrm'))
            ));
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
