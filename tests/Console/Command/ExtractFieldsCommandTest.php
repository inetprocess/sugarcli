<?php

namespace SugarCli\Tests\Console\Command;

use SugarCli\Console\Application;
use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-path
 */
class ExtractFieldsCommandTest extends CommandTestCase
{
    /** Missing Param module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #You must define the module with --module#
     */
    public function testListMissingParam()
    {
        $cmd = $this->getCommandTester('extract:fields');
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
    }

    /** Define a wrong module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #TOTO does not exist in SugarCRM, I cannot retrieve anything#
     */
    public function testListWrongParam()
    {
        $cmd = $this->getCommandTester('extract:fields');
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'TOTO',
        ));
    }

    public function testListHookRightModule()
    {
        $cmd = $this->getCommandTester('extract:fields');
        $result = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Opportunities'
        ));
        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $msg = 'Check that your Sugar instance has the default Hook before_relationship_update for Opportunities';
        $this->assertContains('All fields for Opportunities written in', $output, $msg);
        $this->assertContains('All relationships for Opportunities written in', $output, $msg);
    }
}
