<?php

namespace SugarCli\Tests\Utils;

use SugarCli\Utils\Utils;
use SugarCli\Tests\TestsUtil\Util;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testGetRelativePath($expected, $config_path, $option_path, $current_directory)
    {
        $actual = Utils::makeConfigPathRelative($config_path, $option_path, $current_directory);
        $this->assertEquals($expected, $actual);
    }

    public function pathProvider()
    {
        return array(
            //set #0
            array('conf/bar', 'conf', 'bar', null),
            //set #1
            array(Util::getRelativePath('/var/www'), '/etc', '/var/www', null),
            //set #2
            array(Util::getRelativePath('/var/www'), 'conf', '/var/www', null),
            //set #3
            array(Util::getRelativePath('/etc/www'), '/etc', 'www', null),
            //set #4
            array('baz', getcwd() . '/', 'baz', null),
            //set #5
            array('toto/tests.php', 'tests/..', 'toto/tests.php', null),
            //set #6
            array('.', 'www', '..', null),
        );
    }
}
