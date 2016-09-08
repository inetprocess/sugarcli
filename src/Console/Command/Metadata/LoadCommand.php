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
            ->setDescription('Load the contents of the table fields_meta_data from a file.')
            ->setHelp(<<<EOH
This command modify the database based on a dump file.
Will not do anything by default. Use --force to actually
execute sql queries to impact the database.
EOH
            )
            ->addOption(
                'sql',
                's',
                InputOption::VALUE_NONE,
                'Print the sql queries that would have been executed.'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Really execute the SQL queries to modify the database.'
            );
        $descriptions = array(
            'add' => 'Add new fields from the file to the DB.',
            'del' => 'Delete fields not present in the metadata file from the DB.',
            'update' => 'Update the DB for modified fields in metadata file.'
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
