<?php

namespace SugarCli\Inventory\FactsProvider;

use SugarCli\Inventory\CommandFactsProvider;

class PuppetFacter extends CommandFactsProvider
{
    protected $cmd = 'facter --json';
    protected $as_json = true;
}
