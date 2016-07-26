<?php

namespace SugarCli\Tests\Console\Command\Code;

use SugarCli\Console\Command\Code\NondbFieldCommand;
use ReflectionMethod;

class NondbFieldCommandTest extends \PHPUnit_Framework_TestCase
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
            array('related', 'RelateModule'),
            array('name', 'TestField')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new NondbFieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\NondbFieldCommand', 'checkOptions');
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
            array('related', 'RelateModule'),
            array('name', 'TestField')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new NondbFieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\NondbFieldCommand', 'checkOptions');
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
            array('related', 'RelateModule'),
            array('name', null)
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new NondbFieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\NondbFieldCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - invalid type
     * @see checkOptions
     */
    public function testCheckOptionsExceptionRelatedModuleMissing() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module', 'Tester'),
            array('related', null),
            array('name', 'TestField')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new NondbFieldCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\NondbFieldCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }
}