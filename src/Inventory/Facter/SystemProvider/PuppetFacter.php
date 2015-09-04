<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\CommandProvider;

class PuppetFacter extends CommandProvider
{
    public function __construct()
    {
        parent::__construct('facter --json', true);
    }
}
