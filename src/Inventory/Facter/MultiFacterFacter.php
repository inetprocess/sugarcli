<?php

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
