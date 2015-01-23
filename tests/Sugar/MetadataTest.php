<?php

namespace SugarCli\Sugar;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testDiff()
    {
        $meta = new Metadata();
        $meta->setDumpFile(__DIR__ . '/metadata/base.yaml');
        $base = $meta->getFromFile();
        $meta->setDumpFile(__DIR__ . '/metadata/new.yaml');
        $new = $meta->getFromFile();

        $diff = $meta->diff($base, $new);

        $expected[Metadata::ADD]['field4']['id'] = 'field4';
        $expected[Metadata::ADD]['field4']['name'] = 'foobar';

        $expected[Metadata::DEL]['field1']['id'] = 'field1';
        $expected[Metadata::DEL]['field1']['name'] = 'foo';

        $expected[Metadata::UPDATE]['field2'][Metadata::BASE]['id'] = 'field2';
        $expected[Metadata::UPDATE]['field2'][Metadata::BASE]['name'] = 'bar';
        $expected[Metadata::UPDATE]['field2'][Metadata::REMOTE]['name'] = 'baz';

        $this->assertEquals($expected, $diff);
    }
}

