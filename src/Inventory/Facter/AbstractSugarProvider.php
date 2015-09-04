<?php

namespace SugarCli\Inventory\Facter;

use Inet\SugarCRM\Application;

abstract class AbstractSugarProvider implements FacterInterface
{
    protected $sugarApp;

    public function __construct(Application $sugarApp)
    {
        $this->sugarApp = $sugarApp;
    }

    public function getApplication()
    {
        return $this->sugarApp;
    }

    abstract public function getFacts();
}
