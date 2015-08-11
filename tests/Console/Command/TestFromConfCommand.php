<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use SugarCli\Console\Command\DefaultFromConfCommand;

class TestFromConfCommand extends DefaultFromConfCommand
{
    protected function getDefaults()
    {
        return array(
            'path' => 'sugarcrm.path',
            'url' => 'sugarcrm.url'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getDefaultOption($input, 'path');
        $output->writeln('path: ' . $path);
        $url = $this->getDefaultOption($input, 'url');
        $output->writeln('url: ' . $url);
    }
}
