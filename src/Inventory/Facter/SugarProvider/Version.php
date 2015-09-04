<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class Version extends AbstractSugarProvider
{
    public function getFacts()
    {
        return $this->getApplication()->getVersion();
    }
}
