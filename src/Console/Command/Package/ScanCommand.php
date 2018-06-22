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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ScanCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('package:scan')
            ->setDescription('Scan a package for SugarCRM Cloud compatibility')
            ->setHelp(<<<EOHELP
Use the SugarCRM package scanner to find incompatibilities with SugarCRM Cloud hosting.
EOHELP
            )
            ->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addArgument(
                'package',
                InputArgument::REQUIRED,
                'Package file to scan'
            );
    }

    protected function extractZip($file)
    {
        $fs = new Filesystem();
        $tmp_dir = sys_get_temp_dir() . '/package_scanner_' . md5_file($file);
        $fs->mkdir($tmp_dir);
        $zip = new \ZipArchive();
        if ($zip->open($file) === true) {
            $zip->extractTo($tmp_dir);
            $zip->close();
        } else {
            throw new RuntimeException('Unable to open file "$file"');
        }
        return $tmp_dir;
    }

    protected function scanPackageDir($path)
    {
        $this->getService('sugarcrm.entrypoint');
        global $mod_strings;
        require_once('ModuleInstall/ModuleScanner.php');
        $mod_strings = return_module_language('en_us', 'Administration');

        $module_scanner = new \ModuleScanner();
        $module_scanner->scanPackage($path);
        $fs = new Filesystem();
        $fs->remove($path);
        if ($module_scanner->hasIssues() === true) {
            return $module_scanner->getIssues();
        }
        return false;
    }

    protected function printIssues($path, $issues_data, $output)
    {
        foreach ($issues_data as $type => $issues) {
            $output->writeln(' → <comment>'.ucfirst($type).'</comment>:');
            foreach ($issues as $file_path => $issue) {
                $relative_path = str_replace($path . '/', '', $file_path);
                $output->write('    • <comment>'.$relative_path.'</comment>: ');
                if (is_array($issue)) {
                    $output->writeln('');
                    foreach ($issue as $reason) {
                        $output->writeln('       - '.$reason);
                    }
                } else {
                    $output->writeln($issue);
                }
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $zip_file = $input->getArgument('package');
        if (!is_readable($zip_file)) {
            $output->writeln("<error>File '$php_file' not found or unreadable!</error>");
            return ExitCode::EXIT_FILE_NOT_FOUND;
        }
        $tmp_dir = $this->extractZip($zip_file);
        try {
            $issues_data = $this->scanPackageDir($tmp_dir);
            if (!empty($issues_data)) {
                $this->printIssues($tmp_dir, $issues_data, $output);
                return ExitCode::EXIT_PACKAGE_SCAN_ERROR;
            }
            $output->writeln('Your package is compatible with SugarCRM Cloud');
        } catch (SugarException $e) {
            $output->writeln('<error>' . $e . '</error>');
            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
