<?php

namespace SugarCli\Tests\Sugar;

use SugarCli\Tests\TestsUtil\DatabaseTestCase;
use SugarCli\Sugar\Query;

/**
 * @group db
 */
class QueryTest extends DatabaseTestCase
{
    public function testGetters()
    {
        $sql = 'SELECT * FROM users where id = :id and deleted = ?';
        $params = array(
            ':id' => '1',
            1 => 0,
        );
        $query = new Query(static::getPdo(), $sql, $params);
        $this->assertInstanceOf('PDO', $query->getPdo());
        $this->assertEquals($sql, $query->getSql());
        $this->assertEquals($params, $query->getParams());
        $raw_sql = "SELECT * FROM users where id = '1' and deleted = 0";
        $this->assertEquals($raw_sql, $query->getRawSql());
    }

    public function testExecute()
    {
        $sql = 'SELECT * FROM users where id = :id';
        $query = new Query(static::getPdo(), $sql, array(':id' => '1'));
        $res = $query->execute();
        $row = $res->fetch();
        $this->assertEquals('1', $row['id']);
    }
}
