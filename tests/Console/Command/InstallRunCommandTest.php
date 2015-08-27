<?php
namespace SugarCli\Tests\Console\Command;

class InstallRunCommandTest extends CommandTestCase
{
    public function testFailedRun()
    {
        $ret = $this->getCommandTester('install:run')
            ->execute(array(
                '--path' => '',
                '--source' => '',
                '--config' => '',
                '--url' => '',
            ));
        $this->assertEquals(13, $ret);
    }
}
