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

namespace SugarCli\Console\Command\Metadata;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Inet\SugarCRM\Database\Metadata;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Utils\Utils;

abstract class AbstractMetadataCommand extends AbstractConfigOptionCommand
{
    const METADATA_PATH = '../db/fields_meta_data.yaml';

    public function buildMetadataPath($sugarcrm_path, $metadata_path)
    {
        if ($metadata_path === $this->getMetadataFileDefault()) {
            if ($sugarcrm_path !== null) {
                $metadata_path = Utils::makeConfigPathRelative($sugarcrm_path, self::METADATA_PATH);
            }
        }
        return $metadata_path;
    }

    public function getMetadataFileDefault()
    {
        return '<SUGAR_PATH>/' . self::METADATA_PATH;
    }

    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $metadata_option = $this->getDefinition()->getOption('metadata-file');
        $metadata_path = $this->buildMetadataPath(
            $this->getDefinition()->getOption('path')->getDefault(),
            $metadata_option->getDefault()
        );
        $metadata_option->setDefault($metadata_path);
    }

    protected function configure()
    {
        $this->enableStandardOption('path')
        ->addConfigOption(
            'metadata.file',
            'metadata-file',
            'm',
            InputOption::VALUE_REQUIRED,
            'Path to the metadata file',
            $this->getMetadataFileDefault(),
            false,
            function ($option_name, InputInterface $input, Command $command) {
                $metadata_path = $input->getOption($option_name);
                if ($metadata_path === $command->getMetadataFileDefault()) {
                    $metadata_path = $command->buildMetadataPath($input->getOption('path'), $metadata_path);
                    $command->getDefinition()->getOption($option_name)->setDefault($metadata_path);
                }
            }
        );
    }

    protected function setDiffOptions(array $descriptions)
    {
        $this->addOption(
            'add',
            'a',
            InputOption::VALUE_NONE,
            $descriptions['add']
        )
        ->addOption(
            'del',
            'd',
            InputOption::VALUE_NONE,
            $descriptions['del']
        )
        ->addOption(
            'update',
            'u',
            InputOption::VALUE_NONE,
            $descriptions['update']
        )
        ->addArgument(
            'fields',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Filter the command to only apply to this list of fields.'
        );
    }

    protected function getDiffOptions(InputInterface $input)
    {
        $res = Metadata::DIFF_NONE;
        $options_map = array(
            'add' => Metadata::DIFF_ADD,
            'del' => Metadata::DIFF_DEL,
            'update' => Metadata::DIFF_UPDATE
        );
        foreach ($options_map as $opt => $diff_opt) {
            if ($input->getOption($opt)) {
                $res |= $diff_opt;
            }
        }

        return array(
            'mode' => ($res) ?: Metadata::DIFF_ALL,
            'fields' => str_replace('.', '', $input->getArgument('fields'))
        );
    }

    public function getProgramName()
    {
        return $_SERVER['argv'][0];
    }
}
