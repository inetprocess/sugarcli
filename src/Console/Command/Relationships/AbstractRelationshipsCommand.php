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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Inet\SugarCRM\Database\Relationship;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Utils\Utils;

abstract class AbstractRelationshipsCommand extends AbstractConfigOptionCommand
{
    const RELS_PATH = '../db/relationships.yaml';

    public function buildRelsPath($sugarcrm_path, $rels_path)
    {
        if ($rels_path === $this->getRelsFileDefault()) {
            if ($sugarcrm_path !== null) {
                $rels_path = Utils::makeConfigPathRelative($sugarcrm_path, self::RELS_PATH);
            }
        }
        return $rels_path;
    }

    public function getRelsFileDefault()
    {
        return '<SUGAR_PATH>/' . self::RELS_PATH;
    }

    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $rels_option = $this->getDefinition()->getOption('file');
        $rels_path = $this->buildRelsPath(
            $this->getDefinition()->getOption('path')->getDefault(),
            $rels_option->getDefault()
        );
        $rels_option->setDefault($rels_path);
    }

    protected function configure()
    {
        $this->enableStandardOption('path')
        ->addConfigOption(
            'rels.file',
            'file',
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the rels file.',
            $this->getRelsFileDefault(),
            false,
            function ($option_name, InputInterface $input, Command $command) {
                $rels_path = $input->getOption($option_name);
                if ($rels_path === $command->getRelsFileDefault()) {
                    $rels_path = $command->buildRelsPath($input->getOption('path'), $rels_path);
                    $command->getDefinition()->getOption($option_name)->setDefault($rels_path);
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
