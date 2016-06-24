<?php

namespace SugarCli\Tests\Console\Command\Code;

use SugarCli\Console\Command\Code\RelationshipCommand;
use ReflectionMethod;

class RelationshipCommandTest extends \PHPUnit_Framework_TestCase
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
            array('module-left', 'Tester1'),
            array('module-right', 'Tester2'),
            array('type', 'one-to-one')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new RelationshipCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\RelationshipCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - missing left module
     * @see checkOptions
     */
    public function testCheckOptionsExceptionLeftModuleMissing() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module-left', null),
            array('module-right', 'Tester2'),
            array('type', 'one-to-one')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new RelationshipCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\RelationshipCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - missing right module
     * @see checkOptions
     */
    public function testCheckOptionsExceptionRightModuleMissing() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module-left', 'Tester1'),
            array('module-right', null),
            array('type', 'one-to-one')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new RelationshipCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\RelationshipCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }

    /*
     * Tests an invalid set of options - left module and right module are the same
     * @see checkOptions
     */
    public function testCheckOptionsExceptionModuleNotUnique() {
        // Created mocked dependencies
        $mockInput = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->getMock();

        // Configure mocks
        $arrInputMap = array(
            array('path', __DIR__),
            array('module-left', 'Tester2'),
            array('module-right', 'Tester2'),
            array('type', 'one-to-one')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new RelationshipCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\RelationshipCommand', 'checkOptions');
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
            array('module-left', null),
            array('module-right', 'Tester2'),
            array('type', 'dummy')
        );
        $mockInput->method('getOption')
            ->will($this->returnValueMap($arrInputMap));

        // Setup tester
        $tester = new RelationshipCommand();

        // Use reflection to prepare protected method for testing
        $reflectionMethod = new ReflectionMethod('SugarCli\Console\Command\Code\RelationshipCommand', 'checkOptions');
        $reflectionMethod->setAccessible(true);

        // Perform the test with reflection
        $this->setExpectedException('InvalidArgumentException');

        $reflectionMethod->invoke($tester, $mockInput);
    }
}