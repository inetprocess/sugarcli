<?php

namespace SugarCli\Tests\Console\Command;

use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-path
 */
class HooksListCommandTest extends CommandTestCase
{
    public static $cmd_name = 'hooks:list';

    /** Missing Param module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #You must define the module with --module#
     */
    public function testListMissingParam()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
    }

    /** Define a wrong module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #.*Unknown module 'TOTO'.*#
     */
    public function testListWrongParam()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'TOTO',
        ));
    }

    public function testListHookRightModule()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Opportunities'
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has the default Hook before_relationship_update for Opportunities';
        $this->assertContains('Hooks definition for Opportunities', $output, $msg);
        $this->assertContains('before_save', $output, $msg);
    }

    public function testListHookCompactRightModule()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Meetings',
            '--compact' => null,
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has the default Hook before_relationship_update for Meetings';
        $this->assertContains('Hooks definition for Meetings', $output, $msg);
        $this->assertContains('before_relationship_update', $output, $msg);
        $this->assertNotContains('| Class', $output, $msg);
    }

    public function testListHookRightModuleEmptyHooks()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Leads'
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has no Hooks for Leads';
        $this->assertContains('Hooks definition for Leads', $output, $msg);
        $this->assertContains('No Hooks for that module', $output, $msg);
    }
}
