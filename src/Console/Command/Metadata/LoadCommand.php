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

namespace SugarCli\Console\Command\Metadata;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Database\Metadata;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;

class LoadCommand extends AbstractMetadataCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('metadata:loadfromfile')
            ->setDescription('Load into the table <info>fields_meta_data</info> contents from a reference file')
            ->setHelp(<<<EOH
Update the <info>fields_meta_data</info> table to reflect the data in the reference YAML file.
Will not do anything by default. Use <info>--force</info> to actually execute sql queries to impact the database.
You can filter which modification you whish to apply with the options <info>--add,--del,--update</info> or by setting
the fields name after the options.

<comment>Examples:</comment>
Load only new fields:
    <info>sugarcli metadata:loadfromfile --add --force</info>
Only delete fields which are not present in the reference file:
    <info>sugarcli metadata:loadfromfile --del --force</info>
Only apply modifications for the <info>status_c</info> field in the <info>Accounts</info> module:
    <info>sugarcli metadata:loadfromfile Accounts.status_c</info>
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
            'add' => 'Add new fields from the file to the DB',
            'del' => 'Delete fields not present in the metadata file from the DB',
            'update' => 'Update the DB for modified fields in metadata file'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $metadata_file = $input->getOption('metadata-file');

        $diff_opts = $this->getDiffOptions($input);

        if (!is_readable($metadata_file)) {
            $logger->error("Unable to access metadata file {$metadata_file}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} metadata:dump\" first to dump the current table state.");

            return ExitCode::EXIT_METADATA_NOT_FOUND;
        }

        try {
            $meta = new Metadata($logger, $this->getService('sugarcrm.pdo'), $metadata_file);
            $base = $meta->loadFromDb();
            $new = $meta->loadFromFile();
            $diff_res = $meta->diff(
                $base,
                $new,
                $diff_opts['mode'],
                $diff_opts['fields']
            );
            $logger->info("Fields metadata loaded from $metadata_file.");

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
            $logger->error('An error occured while loading the metadata.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
