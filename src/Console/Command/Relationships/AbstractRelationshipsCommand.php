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

namespace SugarCli\Console\Command\Relationships;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Inet\SugarCRM\Database\Relationship;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

abstract class AbstractRelationshipsCommand extends AbstractConfigOptionCommand
{
    const RELS_PATH = '../db/relationships.yaml';

    protected function getRelsOption(InputInterface $input)
    {
        try {
            $metadata = $this->getConfigOption($input, 'file');
        } catch (\InvalidArgumentException $e) {
            $metadata = $this->getConfigOption($input, 'path') . '/' . self::RELS_PATH;
        }

        return $metadata;
    }

    protected function configure()
    {
        $this->addConfigOptionMapping('path', 'sugarcrm.path')
        ->addConfigOptionMapping('file', 'rels.file')
        ->addConfigOption(
            'file',
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the rels file.' .
            ' <comment>(default: "<sugar_path>/' . self::RELS_PATH . '")</comment>'
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
        );
    }

    protected function getDiffMode(InputInterface $input)
    {
        $res = Relationship::DIFF_NONE;
        $optionsMap = array(
            'add' => Relationship::DIFF_ADD,
            'del' => Relationship::DIFF_DEL,
            'update' => Relationship::DIFF_UPDATE
        );
        foreach ($optionsMap as $opt => $diffOpt) {
            if ($input->getOption($opt)) {
                $res |= $diffOpt;
            }
        }

        return ($res) ?: Relationship::DIFF_ALL;
    }

    public function getProgramName()
    {
        return $_SERVER['argv'][0];
    }
}
