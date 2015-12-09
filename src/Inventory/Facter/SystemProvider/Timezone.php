<?php
/**
 * Inventory
 *
 * PHP Version 5.3 -> 5.4
 * SugarCRM Versions 6.5 - 7.6
 *
 * @author Rémi Sauvat
 * @copyright 2005-2015 iNet Process
 *
 * @package inetprocess/inventory
 *
 * @license GNU General Public License v2.0
 *
 * @link http://www.inetprocess.com
 */

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
