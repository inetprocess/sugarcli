<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class Timestamp extends AbstractSugarProvider
{
    public function getFacts()
    {
        return array('facts_timestamp' => (new \DateTime())->getTimestamp());
    }
}
