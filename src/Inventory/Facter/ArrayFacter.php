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

namespace SugarCli\Inventory\Facter;

class ArrayFacter implements FacterInterface
{
    protected $facts;

    public function __construct(array $facts)
    {
        $this->facts = $facts;
    }

    public function getFacts()
    {
        return $this->facts;
    }
}
