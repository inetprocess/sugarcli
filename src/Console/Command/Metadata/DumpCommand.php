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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Inet\SugarCRM\Database\Metadata;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;

class DumpCommand extends AbstractMetadataCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('metadata:dumptofile')
            ->setDescription('Dump the contents of the table fields_meta_data for db migrations.')
            ->setHelp(<<<EOH
Manage the of the dump file based on the fields_meta_data table.
EOH
            );
        $descriptions = array(
            'add' => 'Add new fields from the DB to the definition file.',
            'del' => 'Delete fields not present in the DB from the metadata file.',
            'update' => 'Update the metadata file for modified fields in the DB.'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');

        $metadata_file = $input->getOption('metadata-file');

        $diff_opts = $this->getDiffOptions($input);

        try {
            $meta = new Metadata($logger, $this->getService('sugarcrm.pdo'), $metadata_file);
            $base = array();
            if (is_readable($metadata_file)) {
                $base = $meta->loadFromFile();
            }
            $new = $meta->loadFromDb();
            $diff_res = $meta->diff(
                $base,
                $new,
                $diff_opts['mode'],
                $diff_opts['fields']
            );
            $logger->info('Fields metadata loaded from DB.');

            $meta->writeFile($diff_res);
            $output->writeln("Updated file $metadata_file.");
        } catch (SugarException $e) {
            $logger->error('An error occured while dumping the metadata.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
