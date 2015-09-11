<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class License extends AbstractSugarProvider
{
    public function getFacts()
    {
        $sql = 'SELECT value FROM config WHERE category="license" AND name = ?';
        $configs = array(
            'expire' => 'expire_date',
            'last_validation' => 'last_validation',
            'last_validation_success' => 'last_validation_success',
            'users' => 'users',
            'validation_key_expire' => 'vk_end_date',
        );
        $stmt = $this->getPdo()->prepare($sql);
        $facts = array();
        foreach ($configs as $key => $name) {
            $stmt->bindValue(1, $name);
            $facts[$key] = $this->queryOne($stmt);
            $stmt->closeCursor();
        }
        $facts['users'] = intval($facts['users']);
        return array('license' => $facts);
    }
}
