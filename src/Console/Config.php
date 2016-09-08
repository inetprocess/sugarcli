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
 * @license Apache License 2.0
 *
 * @link http://www.inetprocess.com
 */

namespace SugarCli\Console;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Parser;
use Webmozart\PathUtil\Path;
use SugarCli\Utils\Utils;

class Config implements ConfigurationInterface
{
    protected $config_data = array();

    protected $loaded = false;

    protected $fs;

    public $config_files = array();

    public function __construct(array $config_files = array())
    {
        $this->config_files = $config_files;
    }

    public function getRelativePath($conf_path, $sugar_path)
    {
        $conf_path = Path::getDirectory($conf_path);
        return Utils::makeConfigPathRelative($conf_path, $sugar_path);
    }

    /**
     * Read configuration files and merge them in an array.
     */
    public function load()
    {
        $yaml = new Parser();
        $parsed_confs = array();
        foreach ($this->config_files as $conf) {
            if (is_readable($conf)) {
                $parsed_conf = $yaml->parse(file_get_contents($conf));
                // Change sugarcrm.path to a relative path from the configfile and current directory.
                if (isset($parsed_conf['sugarcrm']['path'])) {
                    $parsed_conf['sugarcrm']['path'] = $this->getRelativePath($conf, $parsed_conf['sugarcrm']['path']);
                }
                if (isset($parsed_conf['metadata']['file'])) {
                    $parsed_conf['metadata']['file'] = $this->getRelativePath($conf, $parsed_conf['metadata']['file']);
                }
                if (isset($parsed_conf['rels']['file'])) {
                    $parsed_conf['rels']['file'] = $this->getRelativePath($conf, $parsed_conf['rels']['file']);
                }
                $parsed_confs[] = $parsed_conf;
            }
        }
        //Validate and merge configuration.
        $processor = new Processor();
        $this->config_data = $processor->processConfiguration($this, $parsed_confs);
        $this->loaded = true;
    }

    public function isLoaded()
    {
        return $this->loaded;
    }

    /**
     * Used to validate the configuration.
     */
    public function getConfigTreeBuilder()
    {
        $tree_builder = new TreeBuilder();
        $tree_builder->root('sugarcli')
            ->children()
                ->arrayNode('sugarcrm')
                    ->children()
                        ->scalarNode('path')->cannotBeEmpty()->end()
                        ->scalarNode('url')->cannotBeEmpty()->end()
                        ->scalarNode('user_id')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('metadata')
                    ->children()
                        ->scalarNode('file')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('account')
                    ->children()
                        ->scalarNode('name')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $tree_builder;
    }

    /**
     * Return a config value from it's path seperated by dots.
     */
    public function get($path = '', $test_only = false)
    {
        if (!$this->isLoaded()) {
            throw new ConfigException('Load configuration files before accessing the data.');
        }
        $data = $this->config_data;
        $nodes = explode('.', $path);
        foreach ($nodes as $node) {
            if ($node === '') {
                continue;
            }
            if (is_array($data) && array_key_exists($node, $data)) {
                $data = $data[$node];
            } else {
                if ($test_only) {
                    return false;
                }
                throw new ConfigException("Unknown config node $node in path $path.");
            }
        }
        if ($test_only) {
            return true;
        }

        return $data;
    }

    /**
     * Test if path exists
     */
    public function has($path = '')
    {
        return $this->get($path, true);
    }
}
