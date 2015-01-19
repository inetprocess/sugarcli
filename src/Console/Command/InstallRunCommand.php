<?php
/**
 * Check command to verify that Sugar is present and installed.
 */
namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;

use SugarCli\Sugar\Installer;
use SugarCli\Sugar\InstallerException;

class InstallRunCommand extends DefaultFromConfCommand
{
    protected function getDefaults()
    {
        return array(
            'path' => 'sugarcrm.path',
            'url' => 'sugarcrm.url',
        );
    }

    protected function configure()
    {
        $this->setName("install:run")
            ->setDescription('Extract and install SugarCRM.')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force installer to remove target directory if present.')
            ->addOption(
                'source',
                's',
                InputOption::VALUE_REQUIRED,
                'Path to SugarCRM installation package.',
                'sugar.zip')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'PHP file to use as configuration for the installation.',
                'config_si.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getHelper('logger');
        $force = $input->getOption('force');
        $installer = new Installer(
            $this->getDefaultOption($input, 'path'),
            $this->getDefaultOption($input, 'url'),
            $input->getOption('source'),
            $input->getOption('config'),
            $logger
        );
        try {
            $installer->run($force);
            $output->writeln('Installation was sucessfully completed.');
        } catch (InstallerException $e) {
            $logger->error('An error occured during the installation.');
            $logger->error($e->getMessage());
            return 14;
        }
    }
}

