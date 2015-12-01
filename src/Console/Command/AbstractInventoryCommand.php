<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
        ->addConfigOptionMapping('path', 'sugarcrm.path');
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
            list($path, $value) = explode(':', $custom_fact, 2);
            if (empty($value)) {
                throw new \InvalidArgumentException("Invalid format for --custom-fact '$custom_fact'");
            }
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
