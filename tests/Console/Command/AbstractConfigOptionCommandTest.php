<?php

namespace SugarCli\Tests\Console\Command;

use SugarCli\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\CommandTester;
use SugarCli\Tests\TestsUtil\Util;

use SugarCli\Console\Config;

class AbstractConfigOptionCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $config_path = Util::getRelativePath(__DIR__ . '/../yaml');
        $cmd_name = 'test:default';
        $config_cmd = new TestConfigOptionCommand($cmd_name);
        $config = new Config(array($config_path . '/complete.yaml'));
        $config->load();
        $app = new Application();
        $app->configure();
        $app->setAutoExit(false);
        $app->getContainer()->set('config', $config);
        $app->add($config_cmd);

        $command = $app->find($cmd_name);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $cmd_name)
        );
        $sugar_path = $config_path . 'toto/';
        $expected = <<<EOF
path: $sugar_path
url: titi

EOF;
        $this->assertEquals($expected, $commandTester->getDisplay());


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $cmd_name,
                '-p' => 'foo',
                '--url' => 'bar',
            )
        );
        $expected = <<<EOF
path: foo
url: bar

EOF;
        $this->assertEquals($expected, $commandTester->getDisplay());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Standard option "invalid" doesn't exists.
     */
    public function testInvalidStandardOption()
    {
        $cmd = new TestConfigOptionCommand('test');
        $reflex = new \ReflectionClass($cmd);
        $method = $reflex->getMethod('enableStandardOption');
        $method->setAccessible(true);
        $method->invoke($cmd, 'invalid');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp /The "\w+" option is not specified and not found in the config "sugarcrm\.\w+"/
     */
    public function testWrongDefaultOptionsOptionNotFound()
    {
        $cmd_name = 'test:default';
        $config = new Config(array(__DIR__ . '/../yaml/empty.yaml'));
        $config->load();
        $app = new Application();
        $app->configure();
        $app->setAutoExit(false);
        $app->getContainer()->set('config', $config);
        $app->add(new TestConfigOptionCommand($cmd_name));

        $command = $app->find($cmd_name);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $cmd_name,
            )
        );
    }
}
