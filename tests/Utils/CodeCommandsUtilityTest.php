<?php

namespace SugarCli\Tests\Utils;

use ArrayIterator;
use Symfony\Component\Finder\Tests\Iterator\MockSplFileInfo;
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

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

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
        $replacements = array(
            'module' => 'Tester'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::MODULE, '/tmp');
    }

    /*
     * Tests writing files from templates for a field with mocked dependencies
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeField() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);
        $mockFs->method('exists')
            ->willReturn(true);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $replacements = array(
            'module' => 'Tester',
            'field' => 'Tester',
            'type' => 'date'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::FIELD, '/tmp');
    }

    /*
     * Tests writing files from templates for a left relationship component with mocked dependencies
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeRelationshipLeft() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);
        $mockFs->method('exists')
            ->willReturn(true);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $replacements = array(
            'moduleLeft' => 'Left_Tester',
            'moduleRight' => 'Right_Tester',
            'type' => 'one-to-many'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::RELATIONSHIP_LEFT, '/tmp');
    }

    /*
     * Tests writing files from templates for a right relationship component with mocked dependencies
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeRelationshipRight() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);
        $mockFs->method('exists')
            ->willReturn(true);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $replacements = array(
            'moduleLeft' => 'Left_Tester',
            'moduleRight' => 'Right_Tester',
            'type' => 'one-to-many'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::RELATIONSHIP_RIGHT, '/tmp');
    }

    /*
     * Tests writing files from templates for a relationship with mocked dependencies
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeNondbField() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);
        $mockFs->method('exists')
            ->willReturn(true);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $replacements = array(
            'module' => 'ModuleTest',
            'relatedModule' => 'RelatedModule',
            'relatedField' => 'FieldTest'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::NONDB_FIELD, '/tmp');
    }

    /*
     * Tests writing files from templates for an index with mocked dependencies
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeIndex() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);
        $mockFs->method('exists')
            ->willReturn(true);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $replacements = array(
            'module' => 'Tester',
            'fields' => 'test1,test2',
            'index' => 'indexer'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::INDEX, '/tmp');
    }

    /*
     * Tests file path missing for type that doesn't create path
     * @see writeFilesFromTemplatesForType
     */
    public function testWriteFilesFromTemplatesForTypeExceptionPathMissing() {
        // Created mocked dependencies
        $mockTemplater = $this->getMockBuilder('SugarCli\Console\Templater')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();
        $mockFinder = $this->getMockBuilder('Symfony\Component\Finder\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFile1 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy1.php.twig',
        ));
        $mockFile2 = new MockSplFileInfo(array(
            'relativePath' => __DIR__,
            'relativePathname' => 'dummy2.php.twig',
        ));
        $mockFinderIterator = new ArrayIterator(array($mockFile1, $mockFile2));

        // Configure mocks
        $mockFinder->method('files')
            ->willReturn($mockFinder);
        $mockFinder->method('in')
            ->willReturn($mockFinder);
        $mockFinder->method('name')
            ->willReturn($mockFinder);
        $mockFinder->method('getIterator')
            ->willReturn($mockFinderIterator);
        $mockFs->method('exists')
            ->willReturn(false);

        // Create tester with mocks
        $tester = new CodeCommandsUtility($mockTemplater, $mockFs, $mockFinder);

        // Perform the test
        $this->setExpectedException('DomainException');

        $replacements = array(
            'module' => 'Tester',
            'field' => 'Tester',
            'type' => 'date'
        );

        $tester->writeFilesFromTemplatesForType($replacements, TemplateTypeEnum::FIELD, '/tmp');
    }
}
