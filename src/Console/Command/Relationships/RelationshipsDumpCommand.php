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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Database\Relationship;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;

class RelationshipsDumpCommand extends AbstractRelationshipsCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('rels:dumptofile')
            ->setDescription('Dump the contents of the table relationships for db migrations.')
            ->setHelp(<<<EOH
Manage the of the dump file based on the relationships table.
EOH
            );
        $descriptions = array(
            'add' => 'Add new relationships from the DB to the definition file.',
            'del' => 'Delete relationships not present in the DB',
            'update' => 'Update the relationships in the DB.'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $relsFile = $input->getOption('file');
        $diffMode = $this->getDiffMode($input);

        try {
            $meta = new Relationship($logger, $this->getService('sugarcrm.pdo'), $relsFile);
            $relsFromFile = array();
            if (is_readable($relsFile)) {
                $relsFromFile = $meta->loadFromFile();
            }
            $relsFromDb = $meta->loadFromDb();
            $diffRes = $meta->diff($relsFromFile, $relsFromDb, $diffMode);
            $logger->info('Fields metadata loaded from DB.');

            $meta->writeFile($diffRes);
            $output->writeln("Updated file $relsFile.");
        } catch (SugarException $e) {
            $logger->error('An error occured while dumping the metadata.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
