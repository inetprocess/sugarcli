<?php

namespace SugarCli\Inventory;

use Inet\SugarCRM\Application;

class SugarFacter extends Facter
{
    protected $sugar;

    public function __construct(Application $sugar)
    {
        $this->sugar = $sugar;
        $providers_dir = __DIR__ . '/SugarFactsProvider';
        $providers_namespace = __NAMESPACE__ . '\SugarFactsProvider';
        parent::__construct($providers_dir, $providers_namespace);
    }

    public function registerProvider(\SplFileInfo $provider)
    {
        $class_name = $this->providers_namespace . '\\' . $provider->getBasename('.php');
        require_once($provider->getPathName());
        $this->providers[] = new $class_name($this->sugar);
    }
}
