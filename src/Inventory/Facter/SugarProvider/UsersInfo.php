<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class UsersInfo extends AbstractSugarProvider
{
    public function getFacts()
    {
        $queries = array(
            'active' => 'SELECT count(*) FROM users WHERE deleted = 0 AND status = "Active"',
            'admin' => 'SELECT count(*) FROM users WHERE deleted = 0 AND status = "Active" AND is_admin = 1',
            'last_session' => 'SELECT MAX(date_end) FROM tracker_sessions',
        );
        $facts = array();
        foreach ($queries as $key => $sql) {
            $stmt = $this->getPdo()->prepare($sql);
            $facts[$key] = $this->queryOne($stmt);
        }
        $facts['active'] = intval($facts['active']);
        $facts['admin'] = intval($facts['admin']);

        return array('users' => $facts);
    }
}
