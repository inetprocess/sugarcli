<?php

namespace SugarCli\Inventory\Facter;

use Inet\SugarCRM\Application;

class SugarFacter extends ProviderFacter
{
    protected $sugar;

    public function __construct(Application $sugar)
    {
        $this->sugar = $sugar;
        $providers_dir = __DIR__ . '/SugarProvider';
        $providers_namespace = __NAMESPACE__ . '\SugarProvider';
        parent::__construct($providers_dir, $providers_namespace);
    }

    public function registerProvider(\SplFileInfo $provider)
    {
        $class_name = $this->providers_namespace . '\\' . $provider->getBasename('.php');
        require_once($provider->getPathName());
        $this->providers[] = new $class_name($this->sugar);
    }
}
