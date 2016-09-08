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

namespace SugarCli\Console\Command\Relationships;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Database\Relationship;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;

class RelationshipsLoadCommand extends AbstractRelationshipsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rels:loadfromfile')
            ->setDescription('Load the contents of the table relationships from a file.')
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
            'del' => 'Delete fields not present in the relationships file from the DB.',
            'update' => 'Update the DB for modified fields in relationships file.'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $relsFile = $input->getOption('file');
        $diffMode = $this->getDiffMode($input);

        if (!is_readable($relsFile)) {
            $logger->error("Unable to access relationships file {$relsFile}.");
            $output->writeln('');
            $output->writeln("Use \"{$this->getProgramName()} rels:dump\" first to dump the current table state.");

            return ExitCode::EXIT_RELS_NOT_FOUND;
        }

        try {
            $meta = new Relationship($logger, $this->getService('sugarcrm.pdo'), $relsFile);
            $relsFromFile = array();
            if (is_readable($relsFile)) {
                $relsFromFile = $meta->loadFromFile();
            }
            $relsFromDb = $meta->loadFromDb();
            $diffRes = $meta->diff($relsFromDb, $relsFromFile, $diffMode);
            $logger->info("Fields relationships loaded from $relsFile.");

            if ($input->getOption('sql')) {
                $output->writeln($meta->generateSqlQueries($diffRes));
            }

            if ($input->getOption('force')) {
                $meta->executeQueries($diffRes);
                $output->writeln('DB updated successfuly.');
            } else {
                $output->writeln('No action done. Use --force to execute the queries.');
            }
        } catch (SugarException $e) {
            $logger->error('An error occured while loading the relationships.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
