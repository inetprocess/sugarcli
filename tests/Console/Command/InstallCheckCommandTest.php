<?php

namespace SugarCli\Tests\Console\Command;

class InstallCheckCommandTest extends CommandTestCase
{
    public function testInstalled()
    {
        $ret = $this->getCommandTester('install:check')
            ->execute(array(
                '--path' => __DIR__ . '/install/fake_sugar',
            ));
        $this->assertEquals(0, $ret);
    }

    public function testNotExtracted()
    {
        $ret = $this->getCommandTester('install:check')
            ->execute(array(
                '--path' => __DIR__,
            ));
        $this->assertEquals(11, $ret);
    }

    public function testNotInstalled()
    {
        @unlink(__DIR__ . '/metadata/fake_sugar/config.php');
        $ret = $this->getCommandTester('install:check')
            ->execute(array(
                '--path' => __DIR__ . '/metadata/fake_sugar',
            ));
        $this->assertEquals(12, $ret);
    }
}
