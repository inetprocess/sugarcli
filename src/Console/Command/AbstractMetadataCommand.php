<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use Inet\SugarCRM\Database\Metadata;

abstract class AbstractMetadataCommand extends AbstractDefaultFromConfCommand
{
    const METADATA_PATH = '../db/fields_meta_data.yaml';

    protected function getDefaults()
    {
        return array('path' => 'sugarcrm.path');
    }

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->addOption(
            'metadata-file',
            'm',
            InputOption::VALUE_REQUIRED,
            'Path to the metadata file.' .
            ' <comment>(default: "<sugar_path>/' . self::METADATA_PATH . '")</comment>',
            null
        );
    }

    protected function getMetadataOption(InputInterface $input)
    {
        $metadata = $input->getOption('metadata-file');
        if ($metadata === null) {
            $metadata = $this->getDefaultOption($input, 'path') . '/' . self::METADATA_PATH;
        }
        return $metadata;
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
