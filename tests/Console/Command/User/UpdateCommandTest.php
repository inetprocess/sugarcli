<?php
namespace SugarCli\Tests\Console\Command\User;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\Database\SugarPDO;
use Inet\SugarCRM\EntryPoint;

use SugarCli\Tests\TestsUtil\ArrayDataSet;
use SugarCli\Tests\TestsUtil\DatabaseTestCase;
use SugarCli\Console\Application;
use SugarCli\Console\Command\User\UpdateCommand;

/**
 * @group sugarcrm-db
 * @group sugarcrm-path
 */
class UpdateCommandTest extends DatabaseTestCase
{
    const USERNAME = 'Test PHPUnit sugarcli';

    protected $command = null;

    public static function deleteTestUsers()
    {
        $sql = "DELETE FROM users WHERE user_name=?";
        $stmt = static::getPdo()->prepare($sql);
        $stmt->execute(array(self::USERNAME));
    }

    public static function setUpBeforeClass()
    {
        static::deleteTestUsers();
    }

    public static function tearDownAfterClass()
    {
        static::deleteTestUsers();
    }

    public function getUserQueryTable(array $fields)
    {
        $sql = "SELECT `" . implode('`, `', $fields) . '` FROM users WHERE user_name="' . self::USERNAME . '"';
        return $this->getConnection()->createQueryTable('user', $sql);
    }

    public function getEntryPointInstance()
    {
        if (!EntryPoint::isCreated()) {
            $logger = new NullLogger;
            EntryPoint::createInstance(
                new SugarApp($logger, getenv('SUGARCLI_SUGAR_PATH')),
                '1'
            );
            $this->assertInstanceOf('Inet\SugarCRM\EntryPoint', EntryPoint::getInstance());
        }
        return EntryPoint::getInstance();
    }

    public function assertUserEquals($expected_user_data)
    {
        $expected = new ArrayDataSet(array('user' => array($expected_user_data)));
        $this->assertTablesEqual(
            $expected->getTable('user'),
            $this->getUserQueryTable(array_keys($expected_user_data))
        );
    }

    public function getCommandTester($cmd_name = 'user:update')
    {
        $app = new Application();
        $app->configure(
            new ArrayInput(array()),
            new StreamOutput(fopen('php://memory', 'w', false))
        );
        $app->setEntryPoint($this->getEntryPointInstance());
        $app->registerAllCommands();
        $this->command = $app->find($cmd_name);
        return new CommandTester($this->command);
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getInputStream($input)
    {
        $stream = fopen('php://memory', 'w+', false);
        fputs($stream, $input);
        rewind($stream);
        return $stream;
    }

    /**********************
     * Start tests
     **********************/

    public function testCreateUser()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--create' => null,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'username' => self::USERNAME
        ));
        $this->assertEquals(0, $ret);
        $this->assertUserEquals(array(
            'user_name' => self::USERNAME,
            'status' => 'Active',
        ));
    }

    public function testCreateUserAlias()
    {
        self::deleteTestUsers();
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            'command' => 'user:create',
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'username' => self::USERNAME
        ));
        $this->assertEquals(0, $ret);
        $this->assertUserEquals(array(
            'user_name' => self::USERNAME,
            'status' => 'Active',
        ));
    }

    public function testCreateUserInactive()
    {
        self::deleteTestUsers();
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            'command' => 'user:create',
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'username' => self::USERNAME,
            '--active' => 'no',
        ));
        $this->assertEquals(0, $ret);
        $this->assertUserEquals(array(
            'user_name' => self::USERNAME,
            'status' => 'Inactive',
        ));
    }

    public function booleanProvider()
    {
        return array(
            array(true, '1'),
            array(true, 'on'),
            array(true, 'true'),
            array(true, 'yes'),
        );
    }

    /**
     * @dataProvider booleanProvider
     */
    public function testGetBoolean($expected, $actual)
    {
        $cmd = new UpdateCommand();
        $this->assertEquals($expected, $cmd->getBoolean($actual));
    }

    public function testUpdateUser()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'username' => self::USERNAME,
            '--first-name' => 'foo',
            '--last-name' => 'bar',
            '--password' => 'test',
            '--active' => 'no',
            '--admin' => 'yes'
        ));
        $this->assertEquals(0, $ret);
        $this->assertUserEquals(array(
            'user_name' => self::USERNAME,
            'status' => 'Inactive',
            'first_name' => 'foo',
            'last_name' => 'bar',
            'is_admin' => 1,
        ));
    }

    public function testUpdateUserNotFound()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'username' => 'Invalid user name',
            '--active' => 'no',
        ));
        $this->assertEquals(22, $ret);
    }

    public function testAskPassword()
    {
        $before_password = $this->getUserQueryTable(array('user_hash'))->getValue(0, 'user_hash');
        $cmd = $this->getCommandTester('user:update');
        $this->getCommand()
            ->getHelper('question')
            ->setInputStream($this->getInputStream("testpassword\n"));
        $ret = $cmd->execute(
            array(
                '--path' => getenv('SUGARCLI_SUGAR_PATH'),
                'username' => self::USERNAME,
                '--ask-password' => null,
            ),
            array('interactive' => true)
        );
        $after_password = $this->getUserQueryTable(array('user_hash'))->getValue(0, 'user_hash');
        $this->assertNotEquals($before_password, $after_password);
    }
}
