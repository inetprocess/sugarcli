<?php

namespace SugarCli\Inventory\SugarFactsProvider;

use Inet\SugarCRM\Application;

use SugarCli\Inventory\FactsProvider;

class Version implements FactsProvider
{
    protected $sugarApp;

    public function __construct(Application $sugarApp)
    {
        $this->sugarApp = $sugarApp;
    }
    public function getFacts()
    {
        return $this->sugarApp->getVersion();
    }
}
