<?php

namespace SugarCli\tests\Utils;

use Symfony\Component\Finder\Tests\Iterator\MockFileListIterator;
use SugarCli\Utils\CodeCommandsUtility;
use SugarCli\Console\TemplateTypeEnum;

class CodeCommandsUtilityTest extends \PHPUnit_Framework_TestCase
{

    /*
     * Tests writing files from templates for a module with mocked dependencies
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeModule() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFinderIterator = new MockFileListIterator(array('Dummy1.php.twig', 'Dummy2.php.twig'));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $tester->writeFilesFromTemplatesForType('Tester', TemplateTypeEnum::MODULE, '/tmp');
    }
}
