<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\FacterInterface;

class Timestamp implements FacterInterface
{
    public function getFacts()
    {
        $date = new \DateTime();

        return array('facts_timestamp' => $date->getTimestamp());
    }
}
