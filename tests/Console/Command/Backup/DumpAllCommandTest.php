<?php

namespace SugarCli\Tests\Console\Command\Backup;

use SugarCli\Tests\Console\Command\CommandTestCase;

class DumpAllCommandTest extends CommandTestCase
{
    public static $cmd_name = 'backup:dump:all';

    public function testCommandLine()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--prefix' => 'test',
            '--destination-dir' => __DIR__,
            '--dry-run' => null,
        ));
        $this->assertEquals(0, $ret);
        $this->assertStringMatchesFormat("'mysqldump'%a\n'tar'%a", rtrim($cmd->getDisplay(), "\n"));
    }
}
