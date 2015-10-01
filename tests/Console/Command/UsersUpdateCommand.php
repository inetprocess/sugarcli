<?php
namespace SugarCli\Tests\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

use SugarCli\Console\Application;
use SugarCli\Console\Command\InventoryFacterCommand;

use Inet\SugarCRM\Application as SugarApp;
use Inet\SugarCRM\Database\SugarPDO;
use Psr\Log\NullLogger;

/**
 * @group sugarcrm
 */
class UsersUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    const USERNAME = 'Test PHPUnit sugarcli';

    public static function tearDownAfterClass()
    {
        $pdo = new SugarPDO(new SugarApp(new NullLogger(), getenv('SUGARCLI_SUGAR_PATH')));
        $sql = "DELETE FROM users WHERE user_name=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(self::USERNAME));
    }

    public function getCommandTester()
    {
        $app = new Application();
        $app->configure();
        $cmd = $app->find('users:update');
        return new CommandTester($cmd);
    }

    public function testCreateUser()
    {
        $cmd = $this->getCommandTester();
        $ret = $cmd->execute(array(
            '--create' => null,
            '--path' => getenv('SUGARCLI_SUGAR_PATH'),
            'username' => self::USERNAME
        ));
        $this->assertEquals(0, $ret);
    }
}
