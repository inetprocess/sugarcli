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
use Symfony\Component\PropertyAccess\PropertyAccess;
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

    public function setRelativePath(&$parsed_conf, $conf_path, $value_path)
    {
        $array_access = PropertyAccess::createPropertyAccessor();
        $value = $array_access->getValue($parsed_conf, $value_path);
        if (!empty($value)) {
            $array_access->setValue($parsed_conf, $value_path, $this->getRelativePath($conf_path, $value));
        }
    }

    /**
     * Read configuration files and merge them in an array.
     */
    public function load()
    {
        $yaml = new Parser();
        $parsed_confs = array();
        $found_files = array();
        foreach ($this->config_files as $conf) {
            if (is_readable($conf)) {
                $found_files[] = $conf;
                try {
                    $parsed_conf = $yaml->parse(file_get_contents($conf));
                    if (empty($parsed_conf)) {
                        continue;
                    }
                } catch (\Exception $e) {
                    throw new \RuntimeException('Unable to read configuration file "'.$conf.'"', 0, $e);
                }
                // Change sugarcrm.path to a relative path from the configfile and current directory.
                $this->setRelativePath($parsed_conf, $conf, '[sugarcrm][path]');
                $this->setRelativePath($parsed_conf, $conf, '[metadata][file]');
                $this->setRelativePath($parsed_conf, $conf, '[rels][file]');
                $this->setRelativePath($parsed_conf, $conf, '[package][project_path]');
                $parsed_confs[] = $parsed_conf;
            }
        }
        //Validate and merge configuration.
        try {
            $processor = new Processor();
            $this->config_data = $processor->processConfiguration($this, $parsed_confs);
            $this->loaded = true;
        } catch (\Exception $e) {
            $msg = 'Error while parsing the configuration files. Please check the syntax.';
            $msg .= "\n \nIncluded configuration files:";
            foreach ($found_files as $f) {
                $msg .= "\n * ".$f;
            }
            throw new \RuntimeException($msg, 0, $e);
        }
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
        $tree_builder = new TreeBuilder('ROOT');
        $rootNode = $tree_builder->getRootNode();
        $rootNode
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
                ->arrayNode('rels')
                    ->children()
                        ->scalarNode('file')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('account')
                    ->children()
                        ->scalarNode('name')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('backup')
                    ->children()
                        ->scalarNode('prefix')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('maintenance')
                    ->children()
                        ->scalarNode('page')->cannotBeEmpty()->end()
                        ->arrayNode('allowed_ips')
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('package')
                    ->children()
                        ->scalarNode('project_path')->cannotBeEmpty()->end()
                        ->arrayNode('ignore')
                            ->prototype('scalar') ->end()
                        ->end()
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
