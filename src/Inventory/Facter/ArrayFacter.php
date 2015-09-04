<?php

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
