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

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CodeSetupComposerCommand extends AbstractConfigOptionCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('code:setupcomposer')
            ->setDescription('Check that composer is setup to be used with SugarCRM')
            ->addConfigOptionMapping('path', 'sugarcrm.path')
            ->addOption(
                'do',
                null,
                InputOption::VALUE_NONE,
                'Create the files'
            )->addOption(
                'reinstall',
                'r',
                InputOption::VALUE_NONE,
                'Reinstall the files'
            )->addOption(
                'no-quickrepair',
                null,
                InputOption::VALUE_NONE,
                'Do not launch a Quick Repair'
            );
    }

    /**
     * Run the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sugarPath = $this->getConfigOption($input, 'path');
        $this->setSugarPath($sugarPath);

        // first check that composer is available
        exec('/bin/which composer', $ret);
        if (empty($ret)) {
            $output->writeln('<error>Make sure that composer is installed and available in your path</error>');
        }

        // Find the util
        $utilsPath = $sugarPath . '/custom/Extension/application/Ext/Utils';
        if (!is_dir($utilsPath)) {
            mkdir($utilsPath, 0750, true);
        }
        $finder = new Finder();
        $finder->files()->contains('vendor/autoload.php')->in($utilsPath);
        $composerUtil = (count($finder) === 0 ? false : true);

        // then check if we have already initialized something
        $finder = new Finder();
        $finder->name('composer.json')->in($sugarPath . '/custom')->depth('== 0');
        $composerJson = (count($finder) === 0 ? false : true);

        if (!empty($composerUtil) && empty($composerJson)) {
            $msg = "Looks like you don't have a composer.json but you have the Util.";
        } elseif (empty($composerUtil) && !empty($composerJson)) {
            $msg = "Looks like you have a composer.json but you don't have the Util.";
        } elseif (empty($composerUtil) && empty($composerJson)) {
            $msg = 'Looks like you have neither a composer.json nor the Util.';
        } elseif ($input->getOption('reinstall') === true) {
            $msg = 'Everything is installed but will reinstall';
        } else {
            $output->writeln('<info>Everything seems fine ! Used --reinstall to reinstall</info>');

            return;
        }

        $output->writeln("<comment>$msg</comment>");

        if ($input->getOption('reinstall') === false) {
            $output->writeln('<comment>Will install it (require --do to have an effect)</comment>');
        }

        // create the composer Util
        if ($input->getOption('reinstall') === true || (empty($composerUtil) && $input->getOption('do') === true)) {
            $output->writeln(PHP_EOL . $this->createComposerUtil($utilsPath));
        }

        // create the composer.json with the default content
        if ($input->getOption('reinstall') === true || (empty($composerJson) && $input->getOption('do') === true)) {
            $output->writeln(PHP_EOL . $this->createComposerJson($sugarPath));
        }

        $output->writeln('<info>Job done !</info>');
        if ($input->getOption('no-quickrepair') === false) {
            $output->writeln('Launching a quick repair and rebuild</info>');
            $this->doQuickRepair($output);
        }
    }

    /**
     * Create the composer.php util
     *
     * @return string
     */
    protected function createComposerUtil($utilsPath)
    {
        copy(__DIR__ . '/../../../res/code_templates/composer.php', "{$utilsPath}/composer.php");

        return " --> Util installed in $utilsPath/composer.php";
    }

    /**
     * Create the composer.json file
     *
     * @return string
     */
    protected function createComposerJson($sugarPath)
    {
        $phpVersion = explode('.', PHP_VERSION);
        $phpVersion = "{$phpVersion[0]}.{$phpVersion[1]}";

        $jsonContent = file_get_contents(__DIR__ . '/../../../res/code_templates/composer.json');
        // replace the vars
        $jsonContent = str_replace('[[PHP_VERSION]]', $phpVersion, $jsonContent);
        file_put_contents($sugarPath . '/custom/composer.json', $jsonContent);
        $msg = " --> composer.json installed in custom/composer.json for PHP $phpVersion" . PHP_EOL;
        $msg.= '     You can now use composer require package/name to install packages' . PHP_EOL;
        $msg.= '     Put all your classes (with namespaces) in custom/include/lib' . PHP_EOL;
        $msg.= '     Finally, do a composer install to install all the components' . PHP_EOL;

        return $msg;
    }
}
