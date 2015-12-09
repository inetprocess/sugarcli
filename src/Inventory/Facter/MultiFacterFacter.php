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

class MultiFacterFacter implements FacterInterface
{
    protected $facters;

    public function __construct(array $facters = array())
    {
        $this->facters = $facters;
    }

    public function addFacter(FacterInterface $facter)
    {
        $this->facters[] = $facter;
    }

    public function getFacts()
    {
        $facts = array();
        foreach ($this->facters as $facter) {
            $facts = array_replace_recursive($facts, $facter->getFacts());
        }

        return $facts;
    }
}
