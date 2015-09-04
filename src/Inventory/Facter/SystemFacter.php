<?php

namespace SugarCli\Inventory\Facter;

class SystemFacter extends ProviderFacter
{
    public function __construct()
    {
        parent::__construct(__DIR__ . '/SystemProvider', __NAMESPACE__ . '\SystemProvider');
    }
}
