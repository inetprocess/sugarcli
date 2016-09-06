<?php

namespace SugarCli\Tests\Console\Command\Code;

use Symfony\Component\Filesystem\Filesystem;
use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-path
 */
class SetupComposerCommandTest extends CommandTestCase
{
    protected static $composerJson;
    protected static $composerPhp;
    protected static $custom_utils;

    public static $cmd_name = 'code:setupcomposer';

    public static function setUpBeforeClass()
    {
        self::$composerJson = realpath(getenv('SUGARCLI_SUGAR_PATH')) . '/custom/composer.json';
        self::$composerPhp = realpath(getenv('SUGARCLI_SUGAR_PATH'))
            . '/custom/Extension/application/Ext/Utils/composer.php';
        self::$custom_utils = realpath(getenv('SUGARCLI_SUGAR_PATH'))
            . '/custom/application/Ext/Utils/custom_utils.ext.php';
        $fs = new Filesystem();
        $fs->remove(dirname(self::$composerPhp));
    }

    public static function tearDownAfterClass()
    {
        // Cleanup composer files.
        // remove the composer.json file and composer.php
        if (is_file(self::$composerJson)) {
            unlink(self::$composerJson);
        }
        // remove the composer.json file and composer.php
        if (is_file(self::$composerPhp)) {
            unlink(self::$composerPhp);
        }
    }

    public function testNoFile()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);

        // remove the composer.json file and composer.php
        if (is_file(self::$composerJson)) {
            unlink(self::$composerJson);
        }
        // remove the composer.json file and composer.php
        if (is_file(self::$composerPhp)) {
            unlink(self::$composerPhp);
        }
        $this->assertFileNotExists(self::$composerJson);
        $this->assertFileNotExists(self::$composerPhp);
        // Force display of message for missing composer
        $env_PATH = getenv('PATH');
        putenv('PATH=');
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
        ));
        // Put env back to normal
        putenv('PATH=' . $env_PATH);

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertContains('Make sure that composer is installed and available in your environment PATH.', $output);
        $this->assertContains("Composer Util: ✕", $output);
        $this->assertContains("composer.json: ✕", $output);
        $this->assertContains('Will install it', $output);
        $this->assertFileNotExists(self::$composerJson);
        $this->assertFileNotExists(self::$composerPhp);
    }

    public function testJsonAndPhp()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);

        // remove the composer.json file and composer.php
        if (is_file(self::$composerJson)) {
            unlink(self::$composerJson);
        }
        // remove the composer.json file and composer.php
        if (is_file(self::$composerPhp)) {
            unlink(self::$composerPhp);
        }
        if (is_dir(dirname(self::$composerPhp))) {
            rmdir(dirname(self::$composerPhp));
        }

        $this->assertFileNotExists(self::$composerJson);
        $this->assertFileNotExists(self::$composerPhp);
        $this->assertFileNotExists(dirname(self::$composerPhp));
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists(self::$composerJson);
        $this->assertFileExists(self::$composerPhp);
        $this->assertContains(
            "require_once(__DIR__ . '/../../../vendor/autoload.php');",
            file_get_contents(self::$composerPhp)
        );
        $this->assertContains('"inetprocess/libsugarcrm": "^1-beta"', file_get_contents(self::$composerJson));
        $this->assertJson(file_get_contents(self::$composerJson));
    }

    public function testNoJsonButPhp()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);

        // remove the composer.json file and composer.php
        if (is_file(self::$composerJson)) {
            unlink(self::$composerJson);
        }

        $this->assertFileNotExists(self::$composerJson);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertContains("Composer Util: ✔", $output);
        $this->assertContains("composer.json: ✕", $output);
        $this->assertFileExists(self::$composerJson);
        $this->assertFileExists(self::$composerPhp);
    }

    public function testJsonNoPhp()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);

        // remove the composer.json file and composer.php
        if (is_file(self::$composerPhp)) {
            unlink(self::$composerPhp);
        }

        $this->assertFileNotExists(self::$composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertFileExists(self::$composerJson);
        $this->assertFileExists(self::$composerPhp);
        $this->assertContains(
            "require_once(__DIR__ . '/../../../vendor/autoload.php');",
            file_get_contents(self::$composerPhp)
        );
        $this->assertContains(
            "require_once(__DIR__ . '/../../../vendor/autoload.php');",
            file_get_contents(self::$custom_utils)
        );
        $this->assertContains('"inetprocess/libsugarcrm": "^1-beta"', file_get_contents(self::$composerJson));
        $this->assertJson(file_get_contents(self::$composerJson));
    }

    public function testJsonPhp()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        file_put_contents(self::$composerJson, 'test');
        $this->assertFileExists(self::$composerJson);
        $this->assertFileExists(self::$composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
        ));

        $output = $cmd->getDisplay();
        $this->assertContains("Everything seems fine ! Use --reinstall to reinstall", $output);
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertEquals("test", file_get_contents(self::$composerJson));
    }

    public function testJsonPhpReinstall()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        file_put_contents(self::$composerJson, 'test');
        $this->assertFileExists(self::$composerJson);
        $this->assertFileExists(self::$composerPhp);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--no-quickrepair' => null,
            '--do' => null,
            '--reinstall' => null
        ));

        $output = $cmd->getDisplay();
        $this->assertContains("Composer Util: ✔", $output);
        $this->assertContains("composer.json: ✔", $output);
        $this->assertContains("Will Reinstall (require --do to have an effect)", $output);
        $this->assertEquals(0, $cmd->getStatusCode());
        $this->assertContains('"inetprocess/libsugarcrm": "^1-beta"', file_get_contents(self::$composerJson));
        $this->assertJson(file_get_contents(self::$composerJson));
    }
}
