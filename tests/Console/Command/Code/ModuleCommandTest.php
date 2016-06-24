<?php

namespace SugarCli\Tests\Console\Command\Code;

use ReflectionMethod;

class ModuleCommandTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Tests a valid set of options
     * @see checkOptions
     */
    public function testCheckOptionsValid() {
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
            ->willReturn(array(
                'TestModule1' => 'contents',
                'TestModule2' => 'contents'
            ));
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

    /*
     * Tests that an exception is thrown when a module name matches an existing module (case-insenstive)
     * @see checkOptions
     */
    public function testCheckOptionsExceptionModuleMatch() {
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
            ->willReturn(array(
                'TestModule1' => 'contents',
                'TestModule2' => 'contents'
            ));
        $mockInput->method('getOption')
            ->willReturn('TsT_testmodule1');
        $mockTester->method('getService')
            ->willReturn($mockSugarEntrypoint);

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\ModuleCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($mockTester, $mockInput);
    }
}