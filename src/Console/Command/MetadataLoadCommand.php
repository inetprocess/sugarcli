<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use SugarCli\Sugar\Metadata;
use SugarCli\Sugar\SugarException;

class MetadataLoadCommand extends DefaultFromConfCommand
{
    protected function getDefaults()
    {
        return array('path' => 'sugarcrm.path');
    }

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
                'dump-file',
                'd',
                InputOption::VALUE_REQUIRED,
                'Path from where fields metadata can be loaded. Can be relative to sugarcrm path.',
                '../db/fields_meta_data.yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getHelper('logger');

        $path = $this->getDefaultOption($input, 'path');
        $dump_file = $input->getOption('dump-file');

        // Manage absolute or relative path.
        $fs = new FileSystem();
        if (!$fs->isAbsolutePath($dump_file)) {
            $dump_file = $path . '/' . $dump_file;
        }

        try {
            $meta = new Metadata($path, $logger);
            $meta->replace($dump_file);
            $output->writeln("Fields metadata loaded from $dump_file.");
        } catch (SugarException $e) {
            $logger->error('An error occured during the installation.');
            $logger->error($e->getMessage());
            return 15;
        }
    }
}

