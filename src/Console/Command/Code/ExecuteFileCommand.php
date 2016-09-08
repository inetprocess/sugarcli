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

namespace SugarCli\Console\Command\Code;

use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\Command\AbstractConfigOptionCommand;
use SugarCli\Console\ExitCode;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ExecuteFileCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('code:execute:file')
            ->setDescription('Execute a php file using a SugarCRM loaded context.')
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'PHP file to execute'
            );
    }

    public function requireFile($sugarcli_console_command_code_execute_require_path)
    {
        return require($sugarcli_console_command_code_execute_require_path);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $php_file = $input->getArgument('file');
        if (!$fs->isAbsolutePath($php_file)) {
            $php_file = $this->getService('sugarcrm.entrypoint')->getLastCwd() . '/' . $php_file;
        }
        if (!is_readable($php_file)) {
            $output->writeln("<error>File '$php_file' not found or unreadable!</error>");
            return ExitCode::EXIT_FILE_NOT_FOUND;
        }
        try {
            $entry_point = $this->getService('sugarcrm.entrypoint');
            $entry_point->setCurrentUser($input->getOption('user-id'));
            $this->requireFile($php_file);
        } catch (SugarException $e) {
            $output->writeln('<error>' . $e . '</error>');
            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
