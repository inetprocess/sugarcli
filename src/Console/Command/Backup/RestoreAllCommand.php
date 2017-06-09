<?php

namespace SugarCli\Console\Command\Backup;

use SugarCli\Console\Command\CompoundCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreAllCommand extends CompoundCommand
{
    protected $extensions = array(
        'files' => array(
            '.tar.gz',
            '.tgz',
            '.taz',
            '.tar.bz2',
            '.tz2',
            '.tbz',
            '.tbz2',
        ),
        'db' => array(
            '.sql.gz',
            '.sql.bz2',
        ),
    );
    protected function configure()
    {
        $this->setName('backup:restore:all')
            ->setDescription('Restore both the database and files of a SugarCRM instance')
            ->setHelp(<<<EOHELP
Restore a complete SugarCRM instance from archive files.
The <info>--archive</info> file must point to the files dump and the database dump must start with the same name.
EOHELP
            )
            ->setSubCommands(array(
                'backup:restore:files',
                'backup:restore:database',
            ))
            ;
    }

    protected function findRelatedDatabaseArchive($filename)
    {
        foreach ($this->extensions['files'] as $ext) {
            if (substr($filename, -strlen($ext)) === $ext) {
                $prefix = substr($filename, 0, -strlen($ext));
                foreach ($this->extensions['db'] as $ext_db) {
                    $db_name = $prefix . $ext_db;
                    if (file_exists($db_name)) {
                        return $db_name;
                    }
                }
                throw new \RuntimeException(
                    'Could not find a database archive file with the same name as ' . $filename
                );
            }
        }
        throw new \RuntimeException('Enable to extract dump name from ' . $filename);
    }

    protected function beforeCommandRun(Command $cmd, InputInterface $input, OutputInterface $output, array &$args)
    {
        if ($cmd->getName() == 'backup:restore:database') {
            $filename = $input->getOption('archive');
            $args['--archive'] =  $this->findRelatedDatabaseArchive($filename);
        }
    }
}
