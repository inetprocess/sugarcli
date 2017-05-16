<?php

namespace SugarCli\Console\Command\Backup;

use SugarCli\Console\Command\CompoundCommand;

class DumpAllCommand extends CompoundCommand
{
    protected function configure()
    {
        $this->setName('backup:dump:all')
            ->setDescription('Create backups of files and database of SugarCRM')
            ->setSubCommands(array(
                'backup:dump:files',
                'backup:dump:database',
            ))
            ;
    }
}
