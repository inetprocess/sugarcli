<?php

namespace SugarCli\Inventory\Facter;

use Symfony\Component\Finder\Finder;

class ProviderFacter implements FacterInterface
{
    protected $providers_dir;
    protected $providers_namespace;

    protected $providers;

    protected $facts;

    public function __construct($providers_dir, $providers_namespace)
    {
        $this->facts = null;

        $this->providers_dir = $providers_dir;
        $this->providers_namespace = $providers_namespace;

        $this->providers = array();
        $this->registerProviders();
    }

    public function registerProviders()
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreUnreadableDirs()
            ->in($this->providers_dir)
            ->name('*.php');
        foreach ($finder as $provider) {
            $this->registerProvider($provider);
        }
    }

    public function registerProvider(\SplFileInfo $provider)
    {
        $class_name = $this->providers_namespace . '\\' . $provider->getBasename('.php');
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
