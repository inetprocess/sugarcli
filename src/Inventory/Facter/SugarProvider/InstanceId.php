<?php

namespace SugarCli\Inventory\Facter\SugarProvider;

use SugarCli\Inventory\Facter\AbstractSugarProvider;

class InstanceId extends AbstractSugarProvider
{
    public function getFacts()
    {
        $user_passwd = posix_getpwuid(posix_getuid());
        return array(
            'instance_id' => $user_passwd['name'] . '@' . gethostname()
        );
    }
}
