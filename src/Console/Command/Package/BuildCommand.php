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

namespace SugarCli\Console\Command\Package;

use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use SugarCli\PackageBuilder;

class BuildCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('package:build')
            ->setDescription('Build a SugarCRM package from a package project')
            ->setHelp(<<<EOHELP
Build a package using a manifest file
EOHELP
            )
            /* ->enableStandardOption('path') */
            ->addConfigOption(
                'package.project_path',
                'project-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Root path of the package project. Normaly where the manifest.php is present',
                null,
                true
            )
            ->addOption(
                'force-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Use this version instead of the default generated one',
                null
            )
            ->addOption(
                'target-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Directory where the zip file will be built, relative to the project-dir',
                'build'
            )
            ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project_dir = $input->getOption('project-dir');
        $target_dir = $project_dir.'/'.$input->getOption('target-dir');

        $builder = new PackageBuilder($project_dir, $target_dir);
        $builder->setIgnore($this->getService('config')->get('package.ignore'));
        if ($input->getOption('force-version')) {
            $builder->setVersion($input->getOption('force-version'));
        }
        $filename = $builder->createZip();
        $output->writeln("Build completed '$filename'");
    }
}
