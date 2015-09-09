<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\FacterInterface;

class Timestamp implements FacterInterface
{
    public function getFacts()
    {
        return array('facts_timestamp' => (new \DateTime())->getTimestamp());
    }
}
