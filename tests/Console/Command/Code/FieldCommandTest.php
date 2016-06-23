<?php

namespace SugarCli\Tests\Console\Command\Code;

use SugarCli\Console\Command\Code\FieldCommand;
use ReflectionMethod;

class FieldCommandTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Tests a valid set of options
     * @see checkOptions
     */
    public function testCheckOptionsValid() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module', 'Tester'),
            array('name', 'Tester'),
            array('type', 'bool')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new FieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\FieldCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - missing module
     * @see checkOptions
     */
    public function testCheckOptionsExceptionModuleMissing() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module', null),
            array('name', 'Tester'),
            array('type', 'bool')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new FieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\FieldCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - missing name
     * @see checkOptions
     */
    public function testCheckOptionsExceptionNameMissing() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module', 'Tester'),
            array('name', null),
            array('type', 'bool')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new FieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\FieldCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - invalid type
     * @see checkOptions
     */
    public function testCheckOptionsExceptionInvalidType() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module', 'Tester'),
            array('name', 'Tester'),
            array('type', 'dummy')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new FieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\FieldCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }
}