<?php

namespace SugarCli\Tests\Console;

use Symfony\Component\Console\Tester\ApplicationTester;

use SugarCli\Console\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

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
}
