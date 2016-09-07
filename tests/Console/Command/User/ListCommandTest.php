<?php

namespace SugarCli\Tests\Console\Command\User;

use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-path
 */
class ListCommandTest extends CommandTestCase
{
    public static $cmd_name = 'user:list';

    public function testList()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
    }

    public function testListOne()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--username' => 'admin',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
    }

    public function testListOneNotFound()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--username' => 'Invalid user',
        ));
        $this->assertEquals(22, $cmd->getStatusCode());
    }

    public function testListJson()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'json',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
    }

    public function testListInvalidFormat()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'invalid format',
        ));
        $this->assertEquals(3, $cmd->getStatusCode());
    }

    public function testRawOutput()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'json',
            '--raw' => null,
            '--username' => 'admin',
            '-F' => 'id,user_name',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $data = json_decode($cmd->getDisplay(), true);
        $expected_data = array(array(
            'id' => '1',
            'user_name' => 'admin',
        ));
        $this->assertEquals($expected_data, $data);
    }

    public function testPrettyOutput()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--format' => 'json',
            '--lang' => 'fr_FR',
            '--username' => 'admin',
            '-F' => 'id,user_name',
        ));
        $this->assertEquals(0, $cmd->getStatusCode());
        $data = json_decode($cmd->getDisplay(), true);
        $expected_data = array(array(
            'ID' => '1',
            'Login' => 'admin',
        ));
        $this->assertEquals($expected_data, $data);
    }
}
