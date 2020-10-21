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

namespace SugarCli\Console\Command\Plugins;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\SugarCRM\Database\Plugins;
use SugarCli\Console\ExitCode;

class DumpCommand extends AbstractPluginsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('plugins:dumptofile')
            ->setDescription('Dump the contents of the table <info>upgrade_history</info>'
                .' in a reference file to track modifications')
            ->setHelp(<<<EOH
Update the reference YAML file based on the <info>upgrade_history</info>. This file should be managed with a VCS.
You can filter which modification you whish to apply with the options <info>--add,--del,--update</info> or by setting
the plugins name after the options.

<comment>Examples:</comment>
Write to the file only new plugins present in the database:
    <info>sugarcli plugins:dumptofile --add --force</info>
Delete plugins in the file which are not present in the database:
    <info>sugarcli plugins:dumptofile --del --force</info>
Only apply modifications for the status_c plugin in the Accounts module:
    <info>sugarcli plugins:dumptofile Accounts.status_c</info>
EOH
            );
        $descriptions = array(
            'add' => 'Add new plugins from the DB to the definition file',
            'del' => 'Delete plugins not present in the DB from the plugins file',
            'update' => 'Update the plugins file for modified plugins in the DB'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $plugins_file = $input->getOption('plugins-file');

        $diff_opts = $this->getDiffOptions($input);

        try {
            $meta = new Plugins($logger, $this->getService('sugarcrm.pdo'), $plugins_file);
            $base = array();
            if (is_readable($plugins_file)) {
                $base = $meta->loadFromFile();
            }
            $new = $meta->loadFromDb();
            $diff_res = $meta->diff(
                $base,
                $new,
                $diff_opts['mode'],
                $diff_opts['plugins']
            );
            $logger->info('Plugins loaded from DB.');

            $fs = new Filesystem();
            $base_dir = dirname($plugins_file);
            if (!$fs->exists($base_dir)) {
                $fs->mkdir($base_dir);
            }
            $meta->writeFile($diff_res);
            $output->writeln("Updated file $plugins_file.");
        } catch (SugarException $e) {
            $logger->error('An error occured while dumping the plugins.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
