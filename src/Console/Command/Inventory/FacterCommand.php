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

namespace SugarCli\Console\Command\Inventory;

use Inet\Inventory\Facter\ArrayFacter;
use Inet\Inventory\Facter\MultiFacterFacter;
use Inet\Inventory\Facter\SugarFacter;
use Inet\Inventory\Facter\SystemFacter;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\SerializerBuilder;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FacterCommand extends AbstractInventoryCommand
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
            )
            ->setRequiredOption('path', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all_facts = array(
            'system' => array(),
            'sugarcrm' => array()
        );
        $source = $input->getArgument('source');
        if (in_array('all', $source) || in_array('system', $source)) {
            $facter = new MultiFacterFacter(array(
                new SystemFacter(),
                new ArrayFacter($this->getCustomFacts($input, 'system'))
            ));
            $all_facts['system'] = $facter->getFacts();
        }
        if (in_array('all', $source) || in_array('sugarcrm', $source)) {
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
