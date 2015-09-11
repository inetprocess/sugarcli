<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use Inet\SugarCRM\DB\SugarPDO;
use SugarCli\Inventory\Facter\AbstractSugarProvider;

class License extends AbstractSugarProvider
{
    public function getFacts()
    {
        $sql = 'SELECT value FROM config WHERE category="license" AND name = ?';
        $facts = array(
            'expire' => 'expire_date',
            'last_validation' => 'last_validation',
            'last_validation_success' => 'last_validation_success',
            'users' => 'users',
            'validation_key_exipire' => 'vk_end_date',
        );
        $self = $this;
        $stmt = $this->getPdo()->prepare($sql);
        array_walk($facts, function (&$name, $fact) use ($self, $stmt) {
            $stmt->bindValue(1, $name);
            $name = $self->queryOne($stmt);
            $stmt->closeCursor();
        });
        $facts['users'] = intval($facts['users']);
        return array('license' => $facts);
    }
}
