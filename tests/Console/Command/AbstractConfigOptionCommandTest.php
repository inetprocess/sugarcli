<?php

namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Config;
use SugarCli\Console\Application;
use SugarCli\Console\Command\InputConfigOption;

use SugarCli\Tests\TestsUtil\Util;

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
        $sugar_path = dirname(Util::getRelativePath(__DIR__)) . '/yaml/toto';
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The config option "test" is not mapped to a configuration parameter.
     */
    public function testInputConfigOptionWithoutMapping()
    {
        new InputConfigOption('', 'test');
    }
}
