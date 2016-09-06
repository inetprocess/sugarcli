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

namespace SugarCli\Console\Command\Inventory;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SugarCli\Console\Command\AbstractConfigOptionCommand;

abstract class AbstractInventoryCommand extends AbstractConfigOptionCommand
{
    protected function configure()
    {
        $this->addOption(
            'custom-fact',
            'F',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Add or override facts. Format: path.to.fact:value'
        )
        ->enableStandardOption('path');
    }

    /**
     * Return an nested array specified from the input option custom-fact
     * and only for the prefix specified
     */
    public function getCustomFacts(InputInterface $input, $prefix)
    {
        $custom_facts = $input->getOption('custom-fact');
        $facts = array();
        foreach ($custom_facts as $custom_fact) {
            $exploded_fact = explode(':', $custom_fact, 2);
            if (empty($exploded_fact[1])) {
                throw new \InvalidArgumentException("Invalid format for --custom-fact '$custom_fact'");
            }
            list($path, $value) = $exploded_fact;
            if ($path === $prefix) {
                return $value;
            }
            $path_nodes = explode('.', $path);
            if ($path_nodes[0] != $prefix) {
                continue;
            }
            array_shift($path_nodes);
            $fact = array();
            foreach (array_reverse($path_nodes) as $node) {
                $fact = array($node => $value);
                $value = $fact;
            }
            $facts = array_replace_recursive($facts, $fact);
        }

        return $facts;
    }
}
