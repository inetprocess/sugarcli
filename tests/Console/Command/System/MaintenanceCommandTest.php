<?php

namespace SugarCli\Tests\Console\Command\System;

use SugarCli\Console\Command\System\MaintenanceCommand;
use SugarCli\Tests\Console\Command\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group sugarcrm-path
 */
class MaintenanceCommandTest extends CommandTestCase
{
    public function getHtaccessPath()
    {
        return __DIR__.'/fake_sugar/.htaccess';
    }

    public function setUp()
    {
        $fs = new Filesystem();
        $fs->remove($this->getHtaccessPath());
    }

    public function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->getHtaccessPath());
    }

    public function assertHtaccessMatches($expected)
    {
        $this->assertFileExists($this->getHtaccessPath());
        $this->assertStringMatchesFormat(
            $expected,
            file_get_contents($this->getHtaccessPath())
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown action argument 'foo'. Possible values are 'on' or 'off'
     */
    public function testInvalidAction()
    {
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'foo',
            ));
    }

    public function testInvalidSugar()
    {
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__,
                'action' => 'on',
            ));
        $this->assertEquals(11, $ret);
    }

    public function testNoFile()
    {
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'on',
            ));
        $this->assertHtaccessMatches(
            MaintenanceCommand::CONFIG_BEGIN_BLOCK.'%a'.MaintenanceCommand::CONFIG_END_BLOCK."\n\n"
        );
    }

    public function testExistingFileWithoutMaintenance()
    {
        $content = "Test content\n";
        file_put_contents($this->getHtaccessPath(), $content);
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'on',
            ));
        $this->assertHtaccessMatches(
            MaintenanceCommand::CONFIG_BEGIN_BLOCK.'%a'.MaintenanceCommand::CONFIG_END_BLOCK."\n\n".$content
        );
    }

    public function testExistingFileWithMaintenance()
    {
        $content = MaintenanceCommand::CONFIG_BEGIN_BLOCK."\nfoobar\n".MaintenanceCommand::CONFIG_END_BLOCK;
        file_put_contents($this->getHtaccessPath(), $content);
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'on',
            ));
        $this->assertHtaccessMatches(
            MaintenanceCommand::CONFIG_BEGIN_BLOCK.'%a'.MaintenanceCommand::CONFIG_END_BLOCK."\n\n"
        );
        $this->assertNotContains('foobar', file_get_contents($this->getHtaccessPath()));
    }

    public function testNoFileOff()
    {
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'off',
            ));
        $this->assertHtaccessMatches(
            ''
        );
    }

    public function testOnAndOff()
    {
        $content = "\n\nTest content\n";
        file_put_contents($this->getHtaccessPath(), $content);
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'on',
            ));
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                'action' => 'off',
            ));
        $this->assertFileExists($this->getHtaccessPath());
        $this->assertEquals($content, file_get_contents($this->getHtaccessPath()));
    }

    public function testPageFromFile()
    {
        $page_path = __DIR__.'/page.html';
        file_put_contents($page_path, "foobar\n");
        $ret = $this->getCommandTester('system:maintenance')
            ->execute(array(
                '--path' => __DIR__.'/fake_sugar',
                '--page' => $page_path,
                'action' => 'on',
            ));

        $this->assertHtaccessMatches('%afoobar%a');
        $fs = new Filesystem();
        $fs->remove($page_path);
        $this->assertFileNotExists($page_path);
    }
}
