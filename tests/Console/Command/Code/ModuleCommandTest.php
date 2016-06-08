<?php

namespace SugarCli\Tests\Console\Command\Code;

use ReflectionMethod;

class ModuleCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckOptions() {
        // Created mocked dependencies
        $mockSugarEntrypoint = $this->getMockBuilder('Inet\SugarCRM\EntryPoint')
            ->disableOriginalConstructor()
            ->getMock();
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();
        $mockTester = $this->getMockBuilder('SugarCli\Console\Command\Code\ModuleCommand')
            ->setMethods(array('getService'))
            ->getMock();

        // Configure mocks
        $mockSugarEntrypoint->method('getBeansList')
            ->willReturn(array('TestModule1', 'TestModule2'));
        $mockInput->method('getOption')
            ->willReturn('Tester');
        $mockTester->method('getService')
            ->willReturn($mockSugarEntrypoint);

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\ModuleCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $reflectionMethod->invoke($mockTester, $mockInput);
    }
}