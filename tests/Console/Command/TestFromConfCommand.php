<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use SugarCli\Console\Command\AbstractDefaultFromConfCommand;

class TestFromConfCommand extends AbstractDefaultFromConfCommand
{
    protected function getConfigOptions()
    {
        $options = parent::getConfigOptions();
        $options['url'] = new InputOption(
            'url',
            'u',
            InputOption::VALUE_REQUIRED,
            'Public url of SugarCRM.'
        );
        return $options;
    }

    protected function getConfigOptionMapping()
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
