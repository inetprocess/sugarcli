<?php

namespace SugarCli\Tests\Utils;

use SugarCli\Utils\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{

    public function humanizeProvider()
    {
        return array(
            array('1.00 KB', 1024),
            array('1.00 B', 1),
            array('0 B', 0),
            array('1.02 KB', 1045),
            array('1.00 MB', 1024 * 1024),
            array('1.00 GB', 1024 * 1024 * 1024),
            array('1.00 TB', 1024 * 1024 * 1024 * 1024),
        );
    }

    /**
     * @dataProvider humanizeProvider
     */
    public function testHumanize($expected, $input)
    {
        $this->assertEquals($expected, Utils::humanize($input));
    }
}
