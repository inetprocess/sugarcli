<?php

namespace SugarCli\Console\Command\Backup;

use SugarCli\Console\Command\CompoundCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpAllCommand extends CompoundCommand
{
    protected $now;

    protected function configure()
    {
        $this->setName('backup:dump:all')
            ->setDescription('Create backups of files and database of SugarCRM')
            ->setHelp(<<<EOHELP
See help of commands <info>backup:dump:database</info> and <info>backup:dump:files</info> for more information.
EOHELP
            )
            ->setSubCommands(array(
                'backup:dump:database',
                'backup:dump:files',
            ))
            ;
        $this->now = new \DateTime();
    }

    protected function beforeCommandRun(Command $cmd, InputInterface $input, OutputInterface $output, array &$args)
    {
        $cmd->setDateTime($this->now);
    }
}
