<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SugarCli\Console\Command\AbstractConfigOptionCommand;

class TestConfigOptionCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->enableStandardOption('path')
            ->enableStandardOption('user-id')
            ->addConfigOption(
                'sugarcrm.url',
                'url',
                'U',
                InputOption::VALUE_REQUIRED,
                'Public url of SugarCRM.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('path');
        $output->writeln('path: ' . $path);
        $url = $input->getOption('url');
        $output->writeln('url: ' . $url);
    }
}
