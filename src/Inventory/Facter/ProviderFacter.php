<?php
/**
 * SugarCLI
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @author Emmanuel Dyan
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/sugarcrm
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Inventory\Facter;

use Symfony\Component\Finder\Finder;

class ProviderFacter implements FacterInterface
{
    /**
     * Directory where providers can be found
     *
     * @var
     */
    protected $providers_dir;
    /**
     * Namespace for the providers in the directory
     *
     * @var
     */
    protected $providers_namespace;

    /**
     * Array of provider objects
     */
    protected $providers;

    /**
     * Array of resulting facts
     */
    protected $facts;

    public function __construct($providers_dir, $providers_namespace)
    {
        $this->facts = null;

        $this->providers_dir = $providers_dir;
        $this->providers_namespace = $providers_namespace;

        $this->providers = array();
        $this->registerProviders();
    }

    /**
     * List the files in the providers dir to create new objects.
     */
    public function registerProviders()
    {
        $finder = new Finder();
        $finder->files()
            ->ignoreUnreadableDirs()
            ->in($this->providers_dir)
            ->sortByName()
            ->name('*.php');
        foreach ($finder as $provider) {
            $this->registerProviderFromFile($provider);
        }
    }

    /**
     * Factory method to create provider classes.
     * Usefull to override this method in child classes to inject into provider classes
     */
    public function factory($class_name)
    {
        return new $class_name();
    }

    /**
     * Instantiate a provider from a file
     */
    public function registerProviderFromFile(\SplFileInfo $provider_file)
    {
        $class_name = $this->providers_namespace . '\\' . $provider_file->getBasename('.php');
        $this->addProvider($this->factory($class_name));
    }

    /**
     * Add a provider object directly
     */
    public function addProvider(FacterInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Fetch the facts from the providers
     */
    public function getFacts($cached = true)
    {
        if (is_null($this->facts)) {
            $this->populateFacts();
        }

        return $this->facts;
    }

    /**
     * Fill from facts from providers for caching.
     */
    public function populateFacts()
    {
        $this->facts = array();
        foreach ($this->providers as $provider) {
            $this->facts = array_replace_recursive($this->facts, $provider->getFacts());
        }
    }
}
