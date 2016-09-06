<?php
namespace SugarCli\Tests\Console\Command\Inventory;

use Symfony\Component\Yaml\Yaml;
use SugarCli\Tests\Console\Command\CommandTestCase;

class FacterCommandTest extends CommandTestCase
{
    public static $cmd_name = 'inventory:facter';

    public function getFakeSugarPath()
    {
        return __DIR__ . '/metadata/fake_sugar';
    }

    /**
     * @group sugarcrm-path
     */
    public function testJsonFormat()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--format' => 'json',
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));

        $output = $cmd->getDisplay();
        $json = json_decode($output, true);
        $this->assertArrayHasKey('system', $json);
        $this->assertNotEmpty($json['system']);
        $this->assertArrayHasKey('sugarcrm', $json);
    }

    public function testInvalidFormat()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(
            array(
                '--format' => 'abc',
                'source' => array('system'),
                '--path' => 'invalid',
            )
        );
        $this->assertEquals(3, $cmd->getStatusCode());
    }

    /**
     * @group sugarcrm-path
     */
    public function testSugarcrmOnly()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--format' => 'json',
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'source' => array('sugarcrm')
        ));

        $output = $cmd->getDisplay();
        $json = json_decode($output, true);
        $this->assertArrayHasKey('system', $json);
        $this->assertArrayHasKey('sugarcrm', $json);
        $this->assertEmpty($json['system']);
        $this->assertNotEmpty($json['sugarcrm']);
    }

    public function testXmlFormat()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array('--format' => 'xml', 'source' => array('system')));

        $output = $cmd->getDisplay();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $output);
    }

    public function testDefaultYmlFormat()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            'source' => array('system'),
            '--custom-fact' => array('system.context:dev'),
        ));

        $output = $cmd->getDisplay();
        $yml = Yaml::parse($output);
        $this->assertArrayHasKey('system', $yml);
        $this->assertArrayHasKey('sugarcrm', $yml);
        $this->assertNotEmpty($yml['system']);
        $this->assertEquals('dev', $yml['system']['context']);
    }
}
