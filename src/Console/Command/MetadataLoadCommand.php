<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use SugarCli\Console\ExitCode;
use SugarCli\Sugar\Metadata;
use SugarCli\Sugar\SugarException;

class MetadataLoadCommand extends AbstractMetadataCommand
{
    protected function configure()
    {
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
        $logger = $this->getApplication()->getContainer()->get('logger');

        $path = $this->getDefaultOption($input, 'path');
        $metadata_file = $this->getMetadataOption($input);

        $diff_opts = $this->getDiffOptions($input);

        if (!is_readable($metadata_file)) {
            $logger->error("Unable to access metadata file {$metadata_file}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} metadata:dump\" first to dump the current table state.");
            return ExitCode::EXIT_METADATA_NOT_FOUND;
        }

        try {
            $meta = new Metadata($path, $logger, $metadata_file);
            $base = $meta->getFromDb();
            $new = $meta->getFromFile();
            $diff_res = $meta->diff(
                $base,
                $new,
                $diff_opts['add'],
                $diff_opts['del'],
                $diff_opts['update'],
                $diff_opts['fields']
            );
            $logger->info("Fields metadata loaded from $metadata_file.");

            if ($input->getOption('sql')) {
                $output->writeln($meta->getSqlQueries($diff_res));
            }

            if ($input->getOption('force')) {
                $meta->executeQueries($diff_res);
                $output->writeln("DB updated successfuly.");
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

