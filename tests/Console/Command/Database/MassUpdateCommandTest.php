<?php

namespace SugarCli\Tests\Console\Command\Database;

use SugarCli\Tests\Console\Command\CommandTestCase;

/**
 * @group sugarcrm-path
 */
class MassUpdateCommandTest extends CommandTestCase
{
    public static $cmd_name = 'database:massupdate';

    /** Missing Param module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #You must define the module with --module#
     */
    public function testListMissingParam()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
        ));
    }

    /** Define a wrong module: exception thrown
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessageRegExp #.*Unknown module 'TOTO'.*#
     */
    public function testListWrongParam()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'TOTO',
        ));
    }

    public function testResaveWithoutUpdate()
    {
        $this->getEntryPointInstance()->setCurrentUser('seed_jim_id');
        $query = new \SugarQuery();
        $query->from(\BeanFactory::newBean('Accounts'));
        $query->select(array('id', 'name', 'date_modified', 'modified_user_id'));
        $query->limit(1);
        $query->where()->equals('name', 'White Cross Co');
        $result = $query->execute('array');
        $account = $result[0];
        $account['date_modified'] = date('Y-m-d H:i', strtotime($account['date_modified']));

        \Inet\SugarCRM\BeanFactoryCache::clearCache();
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Accounts',
            '--user-id' => '1',
        ));

        $result = $query->execute('array', false);
        // due to a sugarcrm limitation, dates are actually only the same up to the minute
        $result[0]['date_modified'] = date('Y-m-d H:i', strtotime($result[0]['date_modified']));
        $this->assertEquals($account, $result[0]);
    }

    public function testResaveWithUpdate()
    {
        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Accounts',
            '--user-id' => '1',
            '--update-modified-by' => null,
        ));

        $this->getEntryPointInstance()->setCurrentUser('seed_jim_id');
        $query = new \SugarQuery();
        $query->from(\BeanFactory::newBean('Accounts'));
        $query->select(array('id', 'name', 'date_modified', 'modified_user_id'));
        $query->limit(1);
        $result = $query->execute('array', false);
        $account = $result[0];

        $this->assertEquals('1', $account['modified_user_id']);
        /* $this->assertEquals(gmdate('Y-m-d H:i:s'), $account['date_modified']); */

        $cmd = $this->getCommandTester(self::$cmd_name);
        $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            '--module' => 'Accounts',
            '--update-modified-by' => null,
            '--user-id' => 'seed_jim_id',
        ));

        $result = $query->execute();
        $expected = array_merge(
            $account,
            array(
                'modified_user_id' => 'seed_jim_id',
            )
        );
        $this->assertEquals($expected, $result[0]);
    }
}
