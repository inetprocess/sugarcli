<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Inet\SugarCRM\System as SugarSystem;

class SystemQuickRepairCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->setName('system:quickrepair')
            ->setDescription('Do a quick repair and rebuild.')
            ->addConfigOptionMapping('path', 'sugarcrm.path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getService('logger');
        $this->setSugarPath($this->getConfigOption($input, 'path'));
        $sugarEP = $this->getService('sugarcrm.entrypoint');

        $progress = new ProgressIndicator($output);
        $progress->start('Starting...');
        $progress->advance(); 
        $sugarSystem = new SugarSystem($sugarEP);
        $progress->setMessage('Working...');
        $sugarSystem->repair();
        $progress->finish('<comment>Repair Done.</comment>');
    }
}
