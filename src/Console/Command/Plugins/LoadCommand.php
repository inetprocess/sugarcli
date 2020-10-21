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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\SugarCRM\Database\Plugins;
use SugarCli\Console\ExitCode;

class LoadCommand extends AbstractPluginsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('plugins:loadfromfile')
            ->setDescription('Load into the table <info>upgrade_history</info> contents from a reference file')
            ->setHelp(<<<EOH
Update the <info>upgrade_history</info> table to reflect the data in the reference YAML file.
Will not do anything by default. Use <info>--force</info> to actually execute sql queries to impact the database.
You can filter which modification you whish to apply with the options <info>--add,--del,--update</info> or by setting
the plugins name after the options.

<comment>Examples:</comment>
Load only new plugins:
    <info>sugarcli plugins:loadfromfile --add --force</info>
Only delete plugins which are not present in the reference file:
    <info>sugarcli plugins:loadfromfile --del --force</info>
EOH
            )
            ->addOption(
                'sql',
                's',
                InputOption::VALUE_NONE,
                'Print the sql queries that would have been executed'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Really execute the SQL queries to modify the database'
            );
        $descriptions = array(
            'add' => 'Add new plugins from the file to the DB',
            'del' => 'Delete plugins not present in the plugins file from the DB',
            'update' => 'Update the DB for modified plugins in plugins file'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $plugins_file = $input->getOption('plugins-file');

        $diff_opts = $this->getDiffOptions($input);

        if (!is_readable($plugins_file)) {
            $logger->error("Unable to access plugins file {$plugins_file}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} plugins:dump\" first to dump the current table state.");

            return ExitCode::EXIT_PLUGINS_NOT_FOUND;
        }

        try {
            $meta = new Plugins($logger, $this->getService('sugarcrm.pdo'), $plugins_file);
            $base = $meta->loadFromDb();
            $new = $meta->loadFromFile();
            $diff_res = $meta->diff(
                $base,
                $new,
                $diff_opts['mode'],
                $diff_opts['plugins']
            );
            $logger->info("Plugin plugins loaded from $plugins_file.");

            if ($input->getOption('sql')) {
                $output->writeln($meta->generateSqlQueries($diff_res));
            }

            if ($input->getOption('force')) {
                $meta->executeQueries($diff_res);
                $output->writeln('DB updated successfuly.');
            } else {
                $output->writeln('No action done. Use --force to execute the queries.');
            }
        } catch (SugarException $e) {
            $logger->error('An error occured while loading the plugins.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
