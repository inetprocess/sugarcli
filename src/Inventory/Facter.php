<?php

namespace SugarCli\Inventory;

use Symfony\Component\Finder\Finder;

class Facter
{
    protected $facts;

    protected $providers;

    public function __construct()
    {
        $this->facts = null;

        $this->providers = array();
        $this->registerProviders();
    }

    public function registerProviders()
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreUnreadableDirs()
            ->in(__DIR__ . '/FactsProvider')
            ->name('*.php');
        foreach ($finder as $provider) {
            $this->registerProvider($provider);
        }
    }

    public function registerProvider(\SplFileInfo $provider)
    {
        $class_name = __NAMESPACE__ . '\FactsProvider\\' . $provider->getBasename('.php');
        require_once($provider->getPathName());
        $this->providers[] = new $class_name();
    }

    public function getFacts($cached = true)
    {
        if (is_null($this->facts)) {
            $this->populateFacts();
        }
        return $this->facts;
    }

    public function populateFacts()
    {
        $this->facts = array();
        foreach ($this->providers as $provider) {
            $this->facts = array_merge($this->facts, $provider->getFacts());
        }
    }
}
