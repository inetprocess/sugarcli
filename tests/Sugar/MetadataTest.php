<?php

namespace SugarCli\Sugar;

use SugarCli\Util\TestLogger;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testDiff()
    {
        $logger = new TestLogger();
        $meta = new Metadata(null, $logger);
        $meta->setMetadataFile(__DIR__ . '/metadata/base.yaml');
        $base = $meta->getFromFile();
        $meta->setMetadataFile(__DIR__ . '/metadata/new.yaml');
        $new = $meta->getFromFile();

        // Test 1
        $diff = $meta->diff($base, $new);

        $expected[Metadata::ADD]['field4']['id'] = 'field4';
        $expected[Metadata::ADD]['field4']['name'] = 'foobar';

        $expected[Metadata::DEL]['field1']['id'] = 'field1';
        $expected[Metadata::DEL]['field1']['name'] = 'foo';

        $expected[Metadata::UPDATE]['field2'][Metadata::BASE]['id'] = 'field2';
        $expected[Metadata::UPDATE]['field2'][Metadata::BASE]['name'] = 'bar';
        $expected[Metadata::UPDATE]['field2'][Metadata::MODIFIED]['name'] = 'baz';

        $this->assertEquals($expected, $diff);

        // Test merged data
        $merged_data = $meta->getMergedMetadata($base, $diff);
        $this->assertEquals($new, $merged_data);


        //Test 2
        $diff = $meta->diff($base, $new, false, false, false);
        $expected = array(
            Metadata::ADD => array(),
            Metadata::DEL => array(),
            Metadata::UPDATE => array()
        );
        $this->assertEquals($expected, $diff);

        //Test 3
        $diff = $meta->diff($base, $new, true, false, true, array('field4', 'field1'));
        $expected = array(
            Metadata::ADD => array(),
            Metadata::DEL => array(),
            Metadata::UPDATE => array()
        );
        $expected[Metadata::ADD]['field4']['id'] = 'field4';
        $expected[Metadata::ADD]['field4']['name'] = 'foobar';
        $this->assertEquals($expected, $diff);
    }

    /**
     * @todo Manage this test with a live sugar instance.
    */
    public function totestQueryBuilder()
    {
        $sugar_path = 'XXXX';
        $meta = new Metadata($sugar_path);
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

