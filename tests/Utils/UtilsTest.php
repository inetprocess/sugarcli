<?php

namespace SugarCli\Tests\Utils;

use SugarCli\Utils\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Tests a base module name with a 5-letter prefix is correct
     * @see Utils::baseModuleName
     */
    public function testBaseModuleNameWithFullPrefix()
    {
        $module_name = 'TESTT_Module';
        $expected_base = 'Module';

        $actual_base = Utils::baseModuleName($module_name);

        $this->assertEquals($expected_base, $actual_base);
    }

    /*
     * Tests a base module name with a 3-letter prefix is correct
     * @see Utils::baseModuleName
     */
    public function testBaseModuleNameWithThreePrefix()
    {
        $module_name = 'TST_Module';
        $expected_base = 'Module';

        $actual_base = Utils::baseModuleName($module_name);

        $this->assertEquals($expected_base, $actual_base);
    }

    /*
     * Tests a base module name with a 1-letter prefix is correct
     * @see Utils::baseModuleName
     */
    public function testBaseModuleNameWithSmallPrefix()
    {
        $module_name = 'T_Module';
        $expected_base = 'Module';

        $actual_base = Utils::baseModuleName($module_name);

        $this->assertEquals($expected_base, $actual_base);
    }

    /*
     * Tests a base module name without a prefix is correct
     * @see Utils::baseModuleName
     */
    public function testBaseModuleNameWithPrefix()
    {
        $module_name = 'Module';
        $expected_base = 'Module';

        $actual_base = Utils::baseModuleName($module_name);

        $this->assertEquals($expected_base, $actual_base);
    }

}
