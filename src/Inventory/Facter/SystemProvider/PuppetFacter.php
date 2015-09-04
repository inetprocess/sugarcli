<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\CommandProvider;

/**
 * @todo Filter on return keys.
 */
class PuppetFacter extends CommandProvider
{
    public function __construct()
    {
        parent::__construct('facter --json', true);
    }
}
