<?php

namespace SugarCli\Tests\Sugar;

use SugarCli\Tests\TestsUtil\DatabaseTestCase;
use SugarCli\Sugar\QueryFactory;

/**
 * @group db
 */
class QueryFactoryTest extends DatabaseTestCase
{
    public function testGetters()
    {
        $qf = new QueryFactory(static::getPdo());
        $this->assertInstanceOf('PDO', $qf->getPdo());
    }

    public function testInsert()
    {
        $expected_sql = "INSERT INTO test (foo, bar) VALUES (1, 'baz')";
        $qf = new QueryFactory(static::getPdo());
        $query = $qf->createInsertQuery('test', array('foo' => 1, 'bar' => 'baz'));
        $this->assertEquals($expected_sql, $query->getRawSql());
    }

    public function testDelete()
    {
        $expected_sql = "DELETE FROM test WHERE id = '1'";
        $qf = new QueryFactory(static::getPdo());
        $query = $qf->createDeleteQuery('test', '1');
        $this->assertEquals($expected_sql, $query->getRawSql());
    }

    public function testUpdate()
    {
        $expected_sql = "UPDATE test SET foo = 1, bar = 'baz' WHERE id = '1'";
        $qf = new QueryFactory(static::getPdo());
        $query = $qf->createUpdateQuery('test', '1', array('foo' => 1, 'bar' => 'baz'));
        $this->assertEquals($expected_sql, $query->getRawSql());
    }
}
