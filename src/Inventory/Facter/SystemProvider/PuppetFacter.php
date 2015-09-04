<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\CommandProvider;

class PuppetFacter extends CommandProvider
{
    protected $facts_keys = array(
        'architecture',
        'domain',
        'fqdn',
        'ipaddress',
        'memoryfree',
        'memoryfree_mb',
        'memorysize',
        'memorysize_mb',
        'os',
        'processors',
        'swapfree',
        'swapfree_mb',
        'swapsize',
        'swapsize_mb',
        'system_uptime',
        'timezone',
    );

    public function __construct()
    {
        parent::__construct('facter --json', true);
    }

    /**
     * Filter results from facter
     */
    public function getFacts()
    {
        $facts = parent::getFacts();
        return array_intersect_key($facts, array_flip($this->facts_keys));
    }
}
