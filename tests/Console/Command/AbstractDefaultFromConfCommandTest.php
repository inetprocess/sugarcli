<?php

namespace SugarCli\Tests\Console\Command;

use SugarCli\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Config;

class AbstractDefaultFromConfCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $cmd_name = 'test:default';
        $config = new Config(array(__DIR__ . '/../yaml/complete.yaml'));
        $config->load();
        $app = new Application();
        $app->configure();
        $app->setAutoExit(false);
        $app->getContainer()->set('config', $config);
        /* $app->getHelperSet()->set($config); */
        $app->add(new TestFromConfCommand($cmd_name));

        $command = $app->find($cmd_name);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $cmd_name)
        );
        $expected = <<<EOF
path: toto
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
}
