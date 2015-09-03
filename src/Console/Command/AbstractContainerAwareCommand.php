<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractContainerAwareCommand extends Command
{
    public function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

    public function getService($service)
    {
        return $this->getContainer()->get($service);
    }

    public function setSugarPath($path)
    {
        $this->getContainer()->setParameter('sugarcrm.path', $path);
        $this->getContainer()->compile();
    }
}
