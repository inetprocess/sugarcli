<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use Inet\SugarCRM\DB\SugarPDO;
use SugarCli\Inventory\Facter\AbstractSugarProvider;

class UsersInfo extends AbstractSugarProvider
{
    public function getFacts()
    {
        $facts = array(
            'active' => 'SELECT count(*) FROM users WHERE deleted = 0 AND status = "Active"',
            'admin' => 'SELECT count(*) FROM users WHERE deleted = 0 AND status = "Active" AND is_admin = 1',
            'last_session' => 'SELECT MAX(date_end) FROM tracker_sessions',
        );
        array_walk($facts, function (&$sql, $fact) {
            $sql = $this->queryOne($sql);
        });
        return array('users' => $facts);
    }
}
