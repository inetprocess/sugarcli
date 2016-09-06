<?php

namespace SugarCli\Tests\Console;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\ApplicationTester;

use SugarCli\Console\Application;
use SugarCli\Tests\TestsUtil\TestCommand;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

    public function testGetConfigFilesPaths()
    {
        $app = new Application();
        $paths = $app->getConfigFilesPaths();

        $expected = array(
            '/etc/sugarclirc',
            getenv('HOME') . '/.sugarclirc',
        );
        $cur_path = '';
        foreach (explode('/', trim(getcwd(), '/')) as $part) {
            $cur_path .= '/' . $part;
            $expected[] = $cur_path . '/.sugarclirc';
        }

        // This also asserts for order.
        $this->assertEquals($expected, $paths);
    }

    public function testContainer()
    {
        $app = new Application('test', 'beta');
        $app->configure();
        $this->assertInstanceOf(
            'Symfony\Component\Console\Output\OutputInterface',
            $app->getContainer()->get('console.output')
        );

        $this->assertInstanceOf(
            'Psr\Log\LoggerInterface',
            $app->getContainer()->get('logger')
        );
        $this->assertInstanceOf(
            'SugarCli\Console\Config',
            $app->getContainer()->get('config')
        );
        $this->assertTrue($app->getContainer()->get('config')->isLoaded());
    }

    public function testRun()
    {
        $app = new Application('test', 'beta');
        $app->setAutoExit(false);
        $tester = new ApplicationTester($app);
        $tester->run(array(
            '--version' => null,
        ));
        $this->assertEquals('test version beta' . PHP_EOL, $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testShutdown()
    {
        ob_start();
        $test_print = "Test output.";
        print $test_print;
        $app = new Application('test', 'beta');
        $app->registerShutdownFunction();
        $app->setAutoExit(false);
        $tester = new ApplicationTester($app);
        $tester->run(array(
            '--version' => null,
        ));
        $this->assertEquals('test version beta' . PHP_EOL, $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
        $reflection = new \ReflectionClass($app);
        $runOk = $reflection->getProperty('runOk');
        $runOk->setAccessible(true);
        $this->assertTrue($runOk->getValue($app));
        $runOk->setValue($app, false);
        Application::shutdownFunction($app);
        $this->assertContains('exit() or die() called before the end of the command.', $tester->getDisplay());
        $this->assertContains($test_print, $tester->getDisplay());
    }

    public function testExitCode255()
    {
        $app = new Application('test', 'beta');
        $app->setAutoExit(true);
        $app->add(new TestCommand());
        $tester = new ApplicationTester($app);
        $tester->run(array(
            'command' => 'test:command',
            '--exit-code' => '1024',
            '--output' => 'test output',
        ));
        $this->assertEquals('test output' . PHP_EOL, $tester->getDisplay());
        $this->assertEquals(255, $tester->getStatusCode());
    }

    public function testShutdownToConsoleOutput()
    {
        ob_start();
        $out = new ConsoleOutput();
        $err = new StreamOutput(fopen('php://memory', 'w', false));
        $out->setErrorOutput($err);
        $app = new Application('test', 'beta');
        $app->configure(null, $out);
        Application::shutdownFunction($app);
        rewind($err->getStream());
        $this->assertContains('exit()', stream_get_contents($err->getStream()));
    }

    public function testRunAsRoot()
    {
        $stub = $this->getMock(
            'SugarCli\Console\Application',
            array('isRunByRoot'),
            array('test', 'test')
        );
        $stub->method('isRunByRoot')->willReturn(true);
        $stub->setAutoExit(false);
        $stub->add(new TestCommand());
        $selfupdate_cmd = new TestCommand();
        $selfupdate_cmd->setName('list');
        $stub->add($selfupdate_cmd);
        $tester = new ApplicationTester($stub);
        $tester->run(array(
            'command' => 'test:command',
        ));
        $this->assertEquals(6, $tester->getStatusCode());
        $this->assertEquals('You are not allowed to run this command as root.' . PHP_EOL, $tester->getDisplay());

        $tester = new ApplicationTester($stub);
        $tester->run(array(
            'command' => 'list',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertEquals(PHP_EOL, $tester->getDisplay());

    }
}
