<?php

namespace SugarCli\Tests\TestsUtil;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this->setName('test:command')
            ->addOption(
                'exit-code',
                'c',
                InputOption::VALUE_REQUIRED,
                '',
                0
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                '',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($input->getOption('output'));
        return (int) $input->getOption('exit-code');
    }
}
