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

    /*
     * Tests a Sugar core module with separate bean name
     * @see Utils::baseModuleName
     */
    public function testModuleBeanNameWithCoreModule()
    {
        $module_name = 'Contacts';
        $expected_bean = 'Contact';

        $actual_bean = Utils::moduleBeanName($module_name);

        $this->assertEquals($expected_bean, $actual_bean);
    }

    /*
     * Tests a non-Sugar core module with same bean name
     * @see Utils::baseModuleName
     */
    public function testModuleBeanNameWithNonCoreModule()
    {
        $module_name = 'Modules';
        $expected_bean = 'Modules';

        $actual_bean = Utils::moduleBeanName($module_name);

        $this->assertEquals($expected_bean, $actual_bean);
    }

    /*
     * Tests the generation of a conventional relationship name
     * @see Utils::conventionalRelationshipName
     */
    public function testConventionalRelationshipName()
    {
        $lmodule_name = 'Lefty_Module';
        $rmodule_name = 'rightY_ModulE';
        $expected_relationship = 'lefty_module_to_righty_module';

        $actual_relationship = Utils::conventionalRelationshipName($lmodule_name, $rmodule_name);

        $this->assertEquals($expected_relationship, $actual_relationship);
    }
}
