<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\FacterInterface;

class Hostname implements FacterInterface
{
    public function getFacts()
    {
        return array('hostname' => gethostname());
    }
}
