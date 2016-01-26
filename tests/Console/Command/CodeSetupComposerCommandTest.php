<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\EntryPoint;
use SugarCli\Console\Application;

/**
 * @group sugarcrm-path
 */
class CodeSetupComposerCommandTest extends \PHPUnit_Framework_TestCase
{
    protected $composerJson;
    protected $composerPhp;

    public function getEntryPointInstance()
    {
        if (!EntryPoint::isCreated()) {
            $logger = new NullLogger;
            EntryPoint::createInstance(
                new SugarApp($logger, getenv('SUGARCLI_SUGAR_PATH')),
                '1'
            );
            $this->assertInstanceOf('Inet\SugarCRM\EntryPoint', EntryPoint::getInstance());
        }
        return EntryPoint::getInstance();
    }

    public function getCommandTester($cmd_name = 'code:setupcomposer')
    {
        $app = new Application();
        $app->configure(
            new ArrayInput(array()),
            new StreamOutput(fopen('php://memory', 'w', false))
        );
        $app->setEntryPoint($this->getEntryPointInstance());
        $cmd = $app->find($cmd_name);

        $this->composerJson = getenv('SUGARCLI_SUGAR_PATH') . '/custom/composer.json';
        $this->composerPhp = getenv('SUGARCLI_SUGAR_PATH') . '/custom/Extension/application/Ext/Utils/composer.php';

        return new CommandTester($cmd);
    }

    public function testNoFile()
    {
        $cmd = $this->getCommandTester();

        // remove the composer.json file and composer.php
        if (is_file($this->composerJson)) {
            unlink($this->composerJson);
        }
        // remove the composer.json file and composer.php
        if (is_file($this->composerPhp)) {
            unlink($this->composerPhp);
        }
        $this->assertFileNotExists($this->composerJson);
        $this->assertFileNotExists($this->composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertContains("Looks like you have neither a composer.json nor the Util", $output);
        $this->assertContains('Will install it', $output);
        $this->assertFileNotExists($this->composerJson);
        $this->assertFileNotExists($this->composerPhp);
    }

    public function testJsonAndPhp()
    {
        $cmd = $this->getCommandTester();

        // remove the composer.json file and composer.php
        if (is_file($this->composerJson)) {
            unlink($this->composerJson);
        }
        // remove the composer.json file and composer.php
        if (is_file($this->composerPhp)) {
            unlink($this->composerPhp);
        }

        $this->assertFileNotExists($this->composerJson);
        $this->assertFileNotExists($this->composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->composerJson);
        $this->assertFileExists($this->composerPhp);
        $this->assertContains(
            "require_once(__DIR__ . '/../../../vendor/autoload.php');",
            file_get_contents($this->composerPhp)
        );
        $this->assertContains('"inetprocess/libsugarcrm": "^1-beta"', file_get_contents($this->composerJson));
        $this->assertJson(file_get_contents($this->composerJson));
    }

    public function testNoJsonButPhp()
    {
        $cmd = $this->getCommandTester();

        // remove the composer.json file and composer.php
        if (is_file($this->composerJson)) {
            unlink($this->composerJson);
        }

        $this->assertFileNotExists($this->composerJson);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertContains("Looks like you don't have a composer.json but you have the Util.", $output);
        $this->assertFileExists($this->composerJson);
        $this->assertFileExists($this->composerPhp);
    }

    public function testJsonNoPhp()
    {
        $cmd = $this->getCommandTester();

        // remove the composer.json file and composer.php
        if (is_file($this->composerPhp)) {
            unlink($this->composerPhp);
        }

        $this->assertFileNotExists($this->composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists($this->composerJson);
        $this->assertFileExists($this->composerPhp);
        $this->assertContains(
            "require_once(__DIR__ . '/../../../vendor/autoload.php');",
            file_get_contents($this->composerPhp)
        );
        $this->assertContains('"inetprocess/libsugarcrm": "^1-beta"', file_get_contents($this->composerJson));
        $this->assertJson(file_get_contents($this->composerJson));
    }

    public function testJsonPhp()
    {
        $cmd = $this->getCommandTester();
        file_put_contents($this->composerJson, 'test');
        $this->assertFileExists($this->composerJson);
        $this->assertFileExists($this->composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertContains("Everything seems fine ! Used --reinstall to reinstall", $output);
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertEquals("test", file_get_contents($this->composerJson));
    }

    public function testJsonPhpReinstall()
    {
        $cmd = $this->getCommandTester();
        file_put_contents($this->composerJson, 'test');
        $this->assertFileExists($this->composerJson);
        $this->assertFileExists($this->composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
            '--reinstall' => null
        ));

        $output = $cmd->getDisplay();
        $this->assertContains("Everything is installed but will reinstall", $output);
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertContains('"inetprocess/libsugarcrm": "^1-beta"', file_get_contents($this->composerJson));
        $this->assertJson(file_get_contents($this->composerJson));
    }
}
