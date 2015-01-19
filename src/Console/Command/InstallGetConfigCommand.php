<?php
namespace SugarCli\Console\Command;
/**
 * Check command to verify that Sugar is present and installed.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Filesystem\Filesystem;

class InstallGetConfigCommand extends Command
{
    protected function configure()
    {
        $this->setName("install:config:get")
            ->setDescription('Write a default config_si.php file in the current folder')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Write to this file instead of config_si.php.',
                'config_si.php')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Overwrite existing file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $fs->copy(
            __DIR__ . '/../../res/config_si.php',
            $input->getOption('config'),
            $input->getOption('force')
        );
    }
}

