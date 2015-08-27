<?php

namespace SugarCli\Tests\Sugar;

use SugarCli\Sugar\Metadata;

use SugarCli\Tests\TestsUtil\DatabaseTestCase;
use SugarCli\Tests\TestsUtil\TestLogger;

/**
 * @group db
 */
class MetadataTest extends DatabaseTestCase
{
    protected $meta = null;
    protected $base = null;
    protected $new = null;

    public function setUp()
    {
        parent::setUp();

        $logger = new TestLogger();
        $this->meta = new Metadata(null, $logger);
        $this->meta->setMetadataFile(__DIR__ . '/metadata/base.yaml');
        $this->base = $this->meta->getFromFile();
        $this->meta->setMetadataFile(__DIR__ . '/metadata/new.yaml');
        $this->new = $this->meta->getFromFile();

    }

    public function testEmptyMetadata()
    {
        $logger = new TestLogger();
        $this->meta = new Metadata(null, $logger);
        $this->meta->setMetadataFile(__DIR__ . '/metadata/empty.yaml');
        $this->assertEmpty($this->meta->getFromFile());
        $this->assertEquals(
            "[warning] No definition found in metadata file.\n",
            $logger->getLines()
        );

    }

    public function testDiffFull()
    {
        $diff = $this->meta->diff($this->base, $this->new);

        $expected[Metadata::ADD]['field4']['id'] = 'field4';
        $expected[Metadata::ADD]['field4']['name'] = 'foobar';

        $expected[Metadata::DEL]['field1']['id'] = 'field1';
        $expected[Metadata::DEL]['field1']['name'] = 'foo';

        $expected[Metadata::UPDATE]['field2'][Metadata::BASE]['id'] = 'field2';
        $expected[Metadata::UPDATE]['field2'][Metadata::BASE]['name'] = 'bar';
        $expected[Metadata::UPDATE]['field2'][Metadata::MODIFIED]['name'] = 'baz';

        $this->assertEquals($expected, $diff);
    }

    public function testDiffMerge()
    {
        $diff = $this->meta->diff($this->base, $this->new);
        $merged_data = $this->meta->getMergedMetadata($this->base, $diff);
        $this->assertEquals($this->new, $merged_data);
    }

    public function testDiffEmpty()
    {
        $diff = $this->meta->diff($this->base, $this->new, false, false, false);
        $expected = array(
            Metadata::ADD => array(),
            Metadata::DEL => array(),
            Metadata::UPDATE => array()
        );
        $this->assertEquals($expected, $diff);
    }

    public function testDiffFilter()
    {
        $diff = $this->meta->diff($this->base, $this->new, true, false, true, array('field4', 'field1'));
        $expected = array(
            Metadata::ADD => array(),
            Metadata::DEL => array(),
            Metadata::UPDATE => array()
        );
        $expected[Metadata::ADD]['field4']['id'] = 'field4';
        $expected[Metadata::ADD]['field4']['name'] = 'foobar';
        $this->assertEquals($expected, $diff);
    }

    public function testSorted()
    {
        $this->meta->setMetadataFile(__DIR__ . '/metadata/unsorted.yaml');
        $unsorted = $this->meta->getFromFile();
        $expected_array = <<<EOA
array (
  'field1' => 
  array (
    'id' => 'field1',
    'name' => 'foo',
  ),
  'field2_test' => 
  array (
    'id' => 'field2_test',
    'name' => 'bar',
  ),
  'field_test' => 
  array (
    'id' => 'field_test',
    'name' => 'bar',
  ),
)
EOA;

        $this->assertEquals($expected_array, var_export($unsorted, true));
    }

    /**
     * @todo Manage this test with a live sugar instance.
     * @group db
     */
    public function todoTestQueryBuilder()
    {
        $meta = new Metadata(__DIR__);
        $meta->setMetadataFile(__DIR__ . '/metadata/base.yaml');
        $base = $meta->getFromFile();
        $meta->setMetadataFile(__DIR__ . '/metadata/new.yaml');
        $new = $meta->getFromFile();

        $diff = $meta->diff($base, $new);
        $sql = $meta->getSqlQueries($diff);

        $expected_sql = <<<SQL
INSERT INTO fields_meta_data (id, name) VALUES('field4', 'foobar');
DELETE FROM fields_meta_data WHERE id = 'field1';
UPDATE fields_meta_data SET name = 'baz' WHERE id = 'field2';

SQL;
        $this->assertEquals($expected_sql, $sql);
    }
}
