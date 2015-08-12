<?php

namespace SugarCli\Inventory\FactsProvider;

use SugarCli\Inventory\FactsProvider;

class Hostname implements FactsProvider
{
    public function getFacts()
    {
        return array('hostname' => gethostname());
    }
}
