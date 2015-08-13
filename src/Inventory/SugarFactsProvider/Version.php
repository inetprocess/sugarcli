<?php

namespace SugarCli\Inventory\SugarFactsProvider;

use SugarCli\Inventory\FactsProvider;
use SugarCli\Sugar\Sugar;

class Version implements FactsProvider
{
    protected $sugar;

    public function __construct(Sugar $sugar)
    {
        $this->sugar = $sugar;
    }
    public function getFacts()
    {
        return $this->sugar->getVersion();
    }
}
