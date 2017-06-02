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
use Symfony\Component\Filesystem\Filesystem;
use Inet\SugarCRM\Database\Metadata;
use Inet\SugarCRM\Exception\SugarException;
use SugarCli\Console\ExitCode;

class DumpCommand extends AbstractMetadataCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('metadata:dumptofile')
            ->setDescription('Dump the contents of the table <info>fields_meta_data</info>'
                .' in a reference file to track modifications')
            ->setHelp(<<<EOH
Update the reference YAML file based on the <info>fields_meta_data</info>. This file should be managed with a VCS.
You can filter which modification you whish to apply with the options <info>--add,--del,--update</info> or by setting
the fields name after the options.

<comment>Examples:</comment>
Write to the file only new fields present in the database:
    <info>sugarcli metadata:dumptofile --add --force</info>
Delete fields in the file which are not present in the database:
    <info>sugarcli metadata:dumptofile --del --force</info>
Only apply modifications for the status_c field in the Accounts module:
    <info>sugarcli metadata:dumptofile Accounts.status_c</info>
EOH
            );
        $descriptions = array(
            'add' => 'Add new fields from the DB to the definition file',
            'del' => 'Delete fields not present in the DB from the metadata file',
            'update' => 'Update the metadata file for modified fields in the DB'
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

            $fs = new Filesystem();
            $base_dir = dirname($metadata_file);
            if (!$fs->exists($base_dir)) {
                $fs->mkdir($base_dir);
            }
            $meta->writeFile($diff_res);
            $output->writeln("Updated file $metadata_file.");
        } catch (SugarException $e) {
            $logger->error('An error occured while dumping the metadata.');
            $logger->error($e->getMessage());

            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
