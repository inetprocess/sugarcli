<?php

namespace SugarCli\Tests\Console\Command\Install;

use SugarCli\Tests\Console\Command\CommandTestCase;

class CheckCommandTest extends CommandTestCase
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
        $ret = $this->getCommandTester('install:check')
            ->execute(array(
                '--path' => __DIR__ . '/uninstalled',
            ));
        $this->assertEquals(12, $ret);
    }
}
