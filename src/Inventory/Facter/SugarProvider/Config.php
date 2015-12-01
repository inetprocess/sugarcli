<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class Config extends AbstractSugarProvider
{
    public function getFacts()
    {
        $sugar_config = $this->getApplication()->getSugarConfig();
        $facts = array(
            'url' => $sugar_config['site_url'],
            'unique_key' => $sugar_config['unique_key'],
            'log_level' => $sugar_config['logger']['level'],
        );

        return $facts;
    }
}
