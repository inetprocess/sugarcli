<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class Timestamp extends AbstractSugarProvider
{
    public function getFacts()
    {
        $date = new \DateTime();
        return array('facts_timestamp' => $date->getTimestamp());
    }
}
