<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use SugarCli\Inventory\Facter\FacterInterface;

class Timezone implements FacterInterface
{
    public function getFacts()
    {
        $d = new \DateTime();
        $tz = $d->getTimezone();

        return array(
            'timezone' => $tz->getName(),
        );
    }
}
